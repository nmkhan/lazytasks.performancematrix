<?php

namespace Lazytask_Performance\Includes;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Lazytask_Performance_Bootstrap {

	protected $loader;
	protected $version;

	public function __construct() {
		$this->version = LAZYTASK_PERFORMANCE_VERSION;
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'lazytasks-performance',
			false,
			dirname( plugin_basename( LAZYTASK_PERFORMANCE_PATH . 'lazytasks-performance.php' ) ) . '/languages/'
		);
	}

	private function load_dependencies() {
		$this->loader = new Lazytask_Performance_Loader();
	}

	private function define_admin_hooks() {
		$plugin_routes = new \Lazytask_Performance\Routes\Lazytask_PerformanceApi_V3();
		$this->loader->add_action( 'rest_api_init', $plugin_routes, 'register_routes' );

		$plugin_admin = new \Lazytask_Performance\Includes\Lazytask_Performance_Admin( $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'maybe_run_migrations' );

		// Register the Cron Handler
		$this->loader->add_action( 'lazytask_performance_daily_cron', $this, 'handle_daily_cron' );
		$this->loader->add_action( 'init', $this, 'schedule_cron' );
	}

	public function schedule_cron() {
		if ( ! wp_next_scheduled( 'lazytask_performance_daily_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'lazytask_performance_daily_cron' );
		}
	}

	public function handle_daily_cron() {
		global $wpdb;
		$activity_table = LAZYTASK_TABLE_PREFIX . 'activity_log';
		$last_synced_id = (int) get_option( 'lazytask_performance_last_synced_id', 0 );

		// Process max 2000 logs per cron run to avoid timeouts
		$logs = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$activity_table} WHERE id > %d ORDER BY id ASC LIMIT 2000",
			$last_synced_id
		) );

		if ( ! empty( $logs ) ) {
			$engine = new \Lazytask_Performance\Services\Performance_ScoringEngine();
			$engine->process_batch( $logs );

			// Update pointer
			$last_id_in_batch = end( $logs )->id;
			update_option( 'lazytask_performance_last_synced_id', $last_id_in_batch );
		} else {
			// Backlog is entirely complete
			update_option( 'lazytask_performance_backlog_synced', 1 );
		}
	}

	private function define_public_hooks() {
		$plugin_public = new \Lazytask_Performance\Includes\Lazytask_Performance_Admin( $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts_public' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_version() {
		return $this->version;
	}

    public function get_loader() {
        return $this->loader;
    }

}
