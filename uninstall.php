<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package    Lazytask_Performance
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Optional: Drop the performance tables here if the user wants a full wipe.
// For safety, preserving historical performance data is recommended unless explicitly purged.
