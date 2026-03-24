<?php

namespace Lazytask_Performance\Services;

class Performance_ScoringEngine {

	private $rules_cache = [];
	private $db;

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
		$this->load_rules();
	}

	private function load_rules() {
		$table = LAZYTASK_TABLE_PREFIX . 'performance_rules';
		// Fallback to avoid fatal errors if table doesn't exist yet
		$rules = $this->db->get_results( "SELECT rule_key, points, is_active FROM {$table}" );
		if ( ! $rules ) {
			return;
		}

		foreach ( $rules as $rule ) {
			if ( (int) $rule->is_active === 1 ) {
				$this->rules_cache[ $rule->rule_key ] = (int) $rule->points;
			}
		}
	}

	/**
	 * Process a batch of raw activity log rows into scores.
	 *
	 * @param array $logs Array of objects from `wp_pms_activity_log`.
	 * @return int Number of rows successfully scored.
	 */
	public function process_batch( array $logs ) {
		if ( empty( $logs ) ) {
			return 0;
		}

		$scores_table = LAZYTASK_TABLE_PREFIX . 'performance_scores';
		$inserts      = [];
		$count        = 0;

		foreach ( $logs as $log ) {
			$rule_triggered = $this->evaluate_log( $log );

			if ( $rule_triggered && isset( $this->rules_cache[ $rule_triggered ] ) ) {
				// We need to resolve the project_id. The activity log doesn't store project_id securely on all rows.
				// We'll derive it by looking up the task/comment if possible.
				$project_id = $this->resolve_project_id( $log );

				$inserts[] = [
					'user_id' => $log->user_id,
					'project_id' => $project_id,
					'task_id' => in_array($log->subject_type, ['task', 'sub-task']) ? $log->subject_id : null,
					'rule_key' => $rule_triggered,
					'points' => $this->rules_cache[ $rule_triggered ],
					'action_date' => date('Y-m-d', strtotime($log->created_at)),
					'created_at' => current_time( 'mysql' )
				];
				$count++;
			}
		}

		// Batch insert
		if ( ! empty( $inserts ) ) {
			foreach ( $inserts as $data ) {
				$this->db->insert( $scores_table, $data );
			}
		}

		return $count;
	}

	/**
	 * Map a raw activity log row to a performance rule key.
	 */
	private function evaluate_log( $log ) {
		$type  = $log->subject_type;
		$event = $log->event;

		// 1. Task Creation
		if ( $type === 'task' && $event === 'created' ) {
			return 'task_created';
		}

		// 2. Comment Posted
		if ( $type === 'comment' && $event === 'created' ) {
			return 'comment_posted';
		}

		// 3. Task Status Updates (Completed, Closed, Reopened)
		if ( in_array($type, ['task', 'sub-task']) && $event === 'updated' ) {
			$props = json_decode( $log->properties, true );
			if ( ! $props ) {
				return false;
			}

			$old_status = $props['old']['status_name'] ?? $props['old']['status'] ?? null;
			$new_status = $props['attributes']['status_name'] ?? $props['attributes']['status'] ?? null;

			// Did status explicitly change?
			if ( $new_status && $old_status !== $new_status ) {
				$new_lower = strtolower( $new_status );
				$old_lower = $old_status ? strtolower( $old_status ) : '';

				if ( $new_lower === 'completed' || $new_lower === 'done' ) {
					return $type === 'sub-task' ? 'subtask_completed' : 'task_completed';
				}

				if ( $new_lower === 'closed' || $new_lower === 'archived' ) {
					return 'task_closed';
				}

				if ( ( $old_lower === 'completed' || $old_lower === 'closed' || $old_lower === 'done' ) && $new_lower !== 'completed' && $new_lower !== 'closed' ) {
					return 'task_reopened';
				}
			}
		}

		return false; // No rule match
	}

	/**
	 * Best effort to find the project_id assigned to this activity.
	 */
	private function resolve_project_id( $log ) {
		// If subject is a task, look up the task's project
		if ( in_array($log->subject_type, ['task', 'sub-task']) ) {
			return $this->db->get_var( $this->db->prepare( "SELECT project_id FROM " . LAZYTASK_TABLE_PREFIX . "tasks WHERE id = %d", $log->subject_id ) );
		}
		// If subject is comment, look up comment's task, then task's project
		if ( $log->subject_type === 'comment' ) {
			$task_id = $this->db->get_var( $this->db->prepare( "SELECT task_id FROM " . LAZYTASK_TABLE_PREFIX . "task_comments WHERE id = %d", $log->subject_id ) );
			if ( $task_id ) {
				return $this->db->get_var( $this->db->prepare( "SELECT project_id FROM " . LAZYTASK_TABLE_PREFIX . "tasks WHERE id = %d", $task_id ) );
			}
		}

		return null;
	}
}
