<?php

namespace Lazytask_Performance\Helper;

class Performance_DBMigrator {

    public static function migrate() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $queries = [
            Performance_DatabaseTableSchema::tbl_performance_rules(),
            Performance_DatabaseTableSchema::tbl_performance_scores()
        ];
        
        foreach ($queries as $query) {
            dbDelta($query);
        }

        self::seed_default_rules();
        self::seed_performance_permissions();
        self::fix_permission_groups();
    }

    private static function seed_default_rules() {
        global $wpdb;
        $rulesTable = LAZYTASK_TABLE_PREFIX . 'performance_rules';

        $defaultRules = [
            ['rule_key' => 'task_created', 'description' => 'Points awarded for creating a new task.', 'points' => 3],
            ['rule_key' => 'task_completed', 'description' => 'Points awarded for marking a task complete.', 'points' => 5],
            ['rule_key' => 'task_closed', 'description' => 'Points awarded for permanently closing a task.', 'points' => 5],
            ['rule_key' => 'task_reopened', 'description' => 'Penalty for reopening a closed task.', 'points' => -10],
            ['rule_key' => 'comment_posted', 'description' => 'Points for actively discussing on a task.', 'points' => 1],
            ['rule_key' => 'project_joined', 'description' => 'Points for joining or being assigned to a project.', 'points' => 2],
            ['rule_key' => 'subtask_completed', 'description' => 'Points for completing subtasks within a larger task.', 'points' => 3],
            ['rule_key' => 'task_overdue', 'description' => 'Penalty if a task remains incomplete past its due date.', 'points' => -2],
            ['rule_key' => 'on_time_bonus', 'description' => 'Bonus multiplier for completing tasks before or on the due date.', 'points' => 5],
            ['rule_key' => 'estimation_accurate', 'description' => 'Bonus points if tracked time is less than or closely matches estimated time.', 'points' => 10],
        ];

        foreach ($defaultRules as $rule) {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$rulesTable} WHERE rule_key = %s", $rule['rule_key']));
            if (!$exists) {
                $wpdb->insert(
                    $rulesTable,
                    [
                        'rule_key' => $rule['rule_key'],
                        'description' => $rule['description'],
                        'points' => $rule['points'],
                        'is_active' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    ['%s', '%s', '%d', '%d', '%s', '%s']
                );
            }
        }
    }

    private static function seed_performance_permissions() {
        global $wpdb;
        $permissions_table        = LAZYTASK_TABLE_PREFIX . 'permissions';
        $role_has_permissions_table = LAZYTASK_TABLE_PREFIX . 'role_has_permissions';

        // 1. Manage Rules (Global - Admin/Owner Only)
        $perm_manage_rules = 'performance-manage-rules';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$permissions_table} WHERE name = %s", $perm_manage_rules));
        if (!$exists) {
            $wpdb->insert(
                $permissions_table,
                [
                    'name'                => $perm_manage_rules,
                    'description'         => 'Manage Performance Scoring Rules',
                    'permission_type'     => 'global',
                    'permission_group'    => 'Setting',
                    'permission_sub_group' => 'Others',
                    'order_id'            => 1,
                ],
                ['%s', '%s', '%s', '%s', '%s', '%d']
            );
            $permission_id = (int) $wpdb->insert_id;

            // Assign to global admins/owners
            $roles = $wpdb->get_results("SELECT id, slug FROM " . LAZYTASK_TABLE_PREFIX . "roles WHERE slug IN ('admin', 'owner')");
            foreach ($roles as $role) {
                $wpdb->insert($role_has_permissions_table, ['role_id' => $role->id, 'permission_id' => $permission_id], ['%d', '%d']);
            }
        }

        // 2. Performance Matrix nav tab access (Global - Everyone)
        $perm_access = 'performance-access';
        $exists2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$permissions_table} WHERE name = %s", $perm_access));
        if (!$exists2) {
            $wpdb->insert(
                $permissions_table,
                [
                    'name'                => $perm_access,
                    'description'         => 'Performance Matrix',
                    'permission_type'     => 'global',
                    'permission_group'    => 'Setting',
                    'permission_sub_group' => 'Main Settings Menu',
                    'order_id'            => 50,
                ],
                ['%s', '%s', '%s', '%s', '%s', '%d']
            );
            $permission_id2 = (int) $wpdb->insert_id;

            // Assign to all roles
            $roles = $wpdb->get_results("SELECT id FROM " . LAZYTASK_TABLE_PREFIX . "roles");
            foreach ($roles as $role) {
                $wpdb->insert($role_has_permissions_table, ['role_id' => $role->id, 'permission_id' => $permission_id2], ['%d', '%d']);
            }
        }
    }

    // Migration 1.0.1 — move performance permissions out of AddOn Permissions group
    private static function fix_permission_groups() {
        global $wpdb;
        $permissions_table = LAZYTASK_TABLE_PREFIX . 'permissions';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            $permissions_table,
            [
                'permission_group'     => 'Setting',
                'permission_sub_group' => 'Main Settings Menu',
                'description'          => 'Performance Matrix',
                'permission_type'      => 'global',
                'order_id'             => 50,
            ],
            [ 'name' => 'performance-access' ],
            [ '%s', '%s', '%s', '%s', '%d' ],
            [ '%s' ]
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            $permissions_table,
            [
                'permission_group'     => 'Setting',
                'permission_sub_group' => 'Others',
                'description'          => 'Manage Performance Scoring Rules',
            ],
            [ 'name' => 'performance-manage-rules' ],
            [ '%s', '%s', '%s' ],
            [ '%s' ]
        );
    }
}
