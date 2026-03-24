<?php

namespace Lazytask_Performance\Routes;

use Lazytask_Performance\Controller\v3\Lazytask_Performance_DefaultController;
use Lazytask_Performance\Controller\v3\Lazytask_PerformanceController;

class Lazytask_PerformanceApi_V3 {

    public function register_routes() {
        $namespace = 'lazytasks/api/v3/performance';
        $auth = new Lazytask_Performance_DefaultController();
        $controller = new Lazytask_PerformanceController();

        // Admin: Make sure the requester has global performance-manage-rules
        $admin_permission_check = function ($request) use ($auth) {
            return $auth->permission_check($request, ['performance-manage-rules']);
        };

        // Get All Rules (Admin Settings)
        register_rest_route($namespace, '/rules', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'get_rules'],
            'permission_callback' => $admin_permission_check
        ]);

        // Update Rules
        register_rest_route($namespace, '/rules', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'update_rules'],
            'permission_callback' => $admin_permission_check
        ]);

        // Update a specific rule (Admin Settings)
		register_rest_route( $namespace, '/rules/(?P<id>[\d]+)', [
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => [ $controller, 'update_rule' ],
			'permission_callback' => $admin_permission_check
		] );

        // Sync Backlog (Admin Settings)
		register_rest_route( $namespace, '/sync', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $controller, 'sync_backlog' ],
			'permission_callback' => $admin_permission_check
		] );

        // Get Scores (User/Project Level - Auth Only, frontend respects project access)
        register_rest_route($namespace, '/scores/(?P<project_id>\d+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'get_project_scores'],
            'permission_callback' => function($request) use ($auth) {
                return $auth->permission_check($request, []);
            }
        ]);
        
        // Manual Cron Trigger (Superadmin testing only)
        register_rest_route($namespace, '/trigger-cron', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'manual_trigger_cron'],
            'permission_callback' => $admin_permission_check
        ]);
    }
}
