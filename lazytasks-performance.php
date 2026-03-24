<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://lazytasks.com
 * @since             1.0.0
 * @package           Lazytask_Performance
 *
 * @wordpress-plugin
 * Plugin Name:       LazyTasks Performance Scorer
 * Plugin URI:        https://lazytasks.com
 * Description:       Adds gamification, scoring, and performance analytics to LazyTasks via a nightly cron job over the activity log.
 * Version:           1.0.0
 * Author:            LazyTasks
 * Author URI:        https://lazytasks.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       lazytasks-performance
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define constants
 */
define( 'LAZYTASK_PERFORMANCE_VERSION', '1.0.0' );
define( 'LAZYTASK_PERFORMANCE_DB_VERSION', '1.0.1' );
define( 'LAZYTASK_PERFORMANCE_PATH', plugin_dir_path( __FILE__ ) );
define( 'LAZYTASK_PERFORMANCE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader
 */
if ( file_exists( LAZYTASK_PERFORMANCE_PATH . 'vendor/autoload.php' ) ) {
	require_once LAZYTASK_PERFORMANCE_PATH . 'vendor/autoload.php';
}

/**
 * The code that runs during plugin activation.
 */
function activate_lazytask_performance() {
	\Lazytask_Performance\Includes\Lazytask_Performance_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_lazytask_performance() {
	\Lazytask_Performance\Includes\Lazytask_Performance_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_lazytask_performance' );
register_deactivation_hook( __FILE__, 'deactivate_lazytask_performance' );

/**
 * Initialize the core plugin class.
 */
function run_lazytask_performance() {
	if(!defined('LAZYTASK_TABLE_PREFIX')){
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>'
				. esc_html__( 'LazyTasks Performance Scorer requires LazyTasks to be installed and active.', 'lazytasks-performance' )
				. '</p></div>';
		} );
		return;
	}

	$plugin = new \Lazytask_Performance\Includes\Lazytask_Performance_Bootstrap();
	$plugin->run();
}

// Start the plugin safely within the plugins_loaded hook.
// Ensures main lazytasks plugin is fully loaded first.
add_action('plugins_loaded', 'run_lazytask_performance', 15);
