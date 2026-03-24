<?php

namespace Lazytask_Performance\Helper;

class Performance_DatabaseTableSchema {

    public static function get_global_wp_db($wpdb_instance = null) {
        if ($wpdb_instance !== null) {
            return $wpdb_instance;
        }
        global $wpdb;
        return $wpdb;
    }

    public static function tbl_performance_rules() {
        return "
        CREATE TABLE IF NOT EXISTS `" . LAZYTASK_TABLE_PREFIX . "performance_rules` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `rule_key` VARCHAR(100) NOT NULL UNIQUE,
            `description` TEXT NULL,
            `points` INT(11) NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

    public static function tbl_performance_scores() {
        return "
        CREATE TABLE IF NOT EXISTS `" . LAZYTASK_TABLE_PREFIX . "performance_scores` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `project_id` BIGINT(20) UNSIGNED NULL,
            `task_id` BIGINT(20) UNSIGNED NULL,
            `rule_key` VARCHAR(100) NOT NULL,
            `points` INT(11) NOT NULL,
            `action_date` DATE NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_user_project` (`user_id`, `project_id`),
            INDEX `idx_rule_key` (`rule_key`),
            INDEX `idx_action_date` (`action_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }

}
