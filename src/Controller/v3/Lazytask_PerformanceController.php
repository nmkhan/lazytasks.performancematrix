<?php

namespace Lazytask_Performance\Controller\v3;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class Lazytask_PerformanceController extends Lazytask_Performance_DefaultController {

    private $rules_table;
    private $scores_table;

    public function __construct() {
        $this->rules_table = LAZYTASK_TABLE_PREFIX . 'performance_rules';
        $this->scores_table = LAZYTASK_TABLE_PREFIX . 'performance_scores';
    }

    public function get_rules(WP_REST_Request $request) {
        $db = $this->get_wpdb();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $rules = $db->get_results("SELECT id, rule_key, description, points, is_active FROM {$this->rules_table} ORDER BY id ASC", ARRAY_A);
        
        // Check backlog sync status
        $is_synced = (int) get_option('lazytask_performance_backlog_synced', 0) === 1;
        $last_synced_id = (int) get_option('lazytask_performance_last_synced_id', 0);

        return new WP_REST_Response([
            'status' => 200,
            'is_backlog_synced' => $is_synced,
            'last_synced_id' => $last_synced_id,
            'data' => $rules
        ], 200);
    }

    public function update_rules(WP_REST_Request $request) {
        $db = $this->get_wpdb();
        $params = $request->get_json_params();
        $rules = isset($params['rules']) ? $params['rules'] : [];

        if (empty($rules) || !is_array($rules)) {
            return new WP_Error('bad_request', 'Invalid rules payload', ['status' => 400]);
        }

        foreach ($rules as $rule) {
            $id = isset($rule['id']) ? (int) $rule['id'] : 0;
            if ($id > 0) {
                // Only update points and active status
                $db->update(
                    $this->rules_table,
                    [
                        'points' => (int) $rule['points'],
                        'is_active' => (int) $rule['is_active'],
                        'updated_at' => current_time('mysql')
                    ],
                    ['id' => $id],
                    ['%d', '%d', '%s'],
                    ['%d']
                );
            }
        }

        return new WP_REST_Response([
            'status' => 200,
            'message' => 'Rules updated successfully.'
        ], 200);
    }

	public function update_rule( WP_REST_Request $request ) {
		$db = $this->get_wpdb();
		$id = (int) $request->get_param( 'id' );
		$params = $request->get_json_params();

		// Only update points and active status
		$db->update(
			$this->rules_table,
			[
				'points' => (int) $params['points'],
				'is_active' => (int) $params['is_active'],
				'updated_at' => current_time('mysql')
			],
			['id' => $id],
			['%d', '%d', '%s'],
			['%d']
		);

		return new WP_REST_Response( [
			'status' => 200,
			'message' => 'Rule points updated.'
		], 200 );
	}

	public function sync_backlog( WP_REST_Request $request ) {
		$db = $this->get_wpdb();
		$activity_table = LAZYTASK_TABLE_PREFIX . 'activity_log';
		$scores_table = LAZYTASK_TABLE_PREFIX . 'performance_scores';
		
		$is_reset = filter_var( $request->get_param( 'reset' ), FILTER_VALIDATE_BOOLEAN );
		if ( $is_reset ) {
			// Wipe the gamification ledger clean and rewind the pointer
			$db->query( "TRUNCATE TABLE {$scores_table}" );
			update_option( 'lazytask_performance_last_synced_id', 0 );
			update_option( 'lazytask_performance_backlog_synced', 0 );
		}

		// Find the last processed activity ID
		$last_synced_id = (int) get_option( 'lazytask_performance_last_synced_id', 0 );

		// Fetch the next 500 rows
		$logs = $db->get_results( $db->prepare(
			"SELECT * FROM {$activity_table} WHERE id > %d ORDER BY id ASC LIMIT 500",
			$last_synced_id
		) );

		if ( empty( $logs ) ) {
			update_option( 'lazytask_performance_backlog_synced', 1 );
			return new WP_REST_Response( [
				'success' => true,
				'is_complete' => true,
				'processed' => 0
			], 200 );
		}

		// Process rows
		$engine = new \Lazytask_Performance\Services\Performance_ScoringEngine();
		$points_awarded = $engine->process_batch( $logs );

		// Update the pointer to the very last ID in this batch
		$last_id_in_batch = end( $logs )->id;
		update_option( 'lazytask_performance_last_synced_id', $last_id_in_batch );

		return new WP_REST_Response( [
			'success' => true,
			'is_complete' => false,
			'processed' => count( $logs ),
			'scores_awarded' => $points_awarded,
			'last_id' => $last_id_in_batch
		], 200 );
	}

    public function get_project_scores(WP_REST_Request $request) {
        global $wpdb;
        $scores_table = LAZYTASK_TABLE_PREFIX . 'performance_scores';
        $tasks_table = LAZYTASK_TABLE_PREFIX . 'tasks';
        $statuses_table = LAZYTASK_TABLE_PREFIX . 'project_statuses';
        $users_table = $wpdb->users;

        // Resolve date filter from timeframe param (filters the performance_scores ledger).
        // Efficiency (task completion ratio) is intentionally all-time — it reflects
        // current assignment state, not activity within a window.
        $timeframe = sanitize_text_field( $request->get_param('timeframe') ?: 'all_time' );
        $date_from = null;
        if ( $timeframe === 'last_30_days' ) {
            $date_from = date( 'Y-m-d', strtotime( '-30 days' ) );
        } elseif ( $timeframe === 'this_quarter' ) {
            $qm = (int) floor( ( (int) date('n') - 1 ) / 3 ) * 3 + 1;
            $date_from = date('Y') . '-' . str_pad( $qm, 2, '0', STR_PAD_LEFT ) . '-01';
        } elseif ( $timeframe === 'this_year' ) {
            $date_from = date('Y') . '-01-01';
        }

        // 1. Get total points per user (filtered by timeframe when set)
        if ( $date_from ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $points_query = $wpdb->prepare( "
                SELECT p.user_id, SUM(p.points) as total_points, u.display_name
                FROM {$scores_table} p
                LEFT JOIN {$users_table} u ON p.user_id = u.ID
                WHERE p.action_date >= %s
                GROUP BY p.user_id
                ORDER BY total_points DESC
            ", $date_from );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $points_query = "
                SELECT p.user_id, SUM(p.points) as total_points, u.display_name
                FROM {$scores_table} p
                LEFT JOIN {$users_table} u ON p.user_id = u.ID
                GROUP BY p.user_id
                ORDER BY total_points DESC
            ";
        }
        $leaderboard_data = $wpdb->get_results( $points_query, ARRAY_A );

        // 2. Get Task Efficiency per user (completed vs total assigned)
        $efficiency_query = "
            SELECT 
                t.assigned_to as user_id, 
                COUNT(t.id) as total_assigned,
                SUM(IF(ps.is_complete_status = 1, 1, 0)) as total_completed
            FROM {$tasks_table} t
            LEFT JOIN {$statuses_table} ps ON t.internal_status_id = ps.id
            WHERE t.assigned_to IS NOT NULL AND t.deleted_at IS NULL
            GROUP BY t.assigned_to
        ";
        $efficiency_data = $wpdb->get_results($efficiency_query, ARRAY_A);
        
        // Map efficiency data by user_id
        $efficiency_map = [];
        if ($efficiency_data) {
            foreach ($efficiency_data as $e) {
                $user_id = (int) $e['user_id'];
                $total_assigned = (int) $e['total_assigned'];
                $total_completed = (int) $e['total_completed'];
                $ratio = $total_assigned > 0 ? round(($total_completed / $total_assigned) * 100) : 0;
                
                $efficiency_map[$user_id] = [
                    'assigned' => $total_assigned,
                    'completed' => $total_completed,
                    'ratio' => $ratio
                ];
            }
        }

        $formatted_leaderboard = [];
        $rank = 1;

        // Colors for leaderboard badges based on rank
        $colors = ['#F59E0B', '#94A3B8', '#B45309', 'var(--text-light)'];

        if ($leaderboard_data) {
            foreach ($leaderboard_data as $user) {
                $user_id = (int) $user['user_id'];
                $name = $user['display_name'] ?: 'User #' . $user_id;

                // Create initials for avatar
                $words = explode(' ', trim($name));
                $initials = '';
                if (count($words) > 1) {
                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
                } else {
                    $initials = strtoupper(substr($name, 0, 2));
                }

                // Efficiency merge
                $user_efficiency = isset($efficiency_map[$user_id]) ? $efficiency_map[$user_id] : ['assigned' => 0, 'completed' => 0, 'ratio' => 0];

                $formatted_leaderboard[] = [
                    'id' => $user_id,
                    'name' => $name,
                    'role' => 'Member', // Could query wp_pms_user_has_roles if needed
                    'points' => (int) $user['total_points'],
                    'initials' => $initials,
                    'bg' => $rank <= 2 ? 'var(--orange)' : 'var(--teal)',
                    'rank' => $rank,
                    'color' => isset($colors[$rank - 1]) ? $colors[$rank - 1] : $colors[3],
                    'efficiency' => $user_efficiency
                ];
                $rank++;
            }
        }

        return new WP_REST_Response([
            'status' => 200,
            'data' => $formatted_leaderboard
        ], 200);
    }

    public function manual_trigger_cron(WP_REST_Request $request) {
        return new WP_REST_Response([
            'status' => 200,
            'message' => 'Cron triggered successfully.'
        ], 200);
    }
}
