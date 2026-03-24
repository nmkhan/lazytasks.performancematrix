<?php

namespace Lazytask_Performance\Includes;

class Lazytask_Performance_Admin {

	private $version;

	public function __construct( $version ) {
		$this->version     = $version;
	}

	public function enqueue_scripts() {
		if ( ! isset( $_REQUEST['page'] ) || ! str_contains( $_REQUEST['page'], 'lazytasks-page' ) ) {
			return;
		}
		$this->do_enqueue();
	}

	public function enqueue_scripts_public() {
		$lazytask_page_id = get_option( 'lazytask_page_id' );
		if ( ! $lazytask_page_id || ! is_page( $lazytask_page_id ) ) {
			return;
		}
		$this->do_enqueue();
	}

	public function maybe_run_migrations() {
		$stored = get_option( 'lazytask_performance_db_version', '0' );
		if ( version_compare( $stored, LAZYTASK_PERFORMANCE_DB_VERSION, '<' ) ) {
			\Lazytask_Performance\Helper\Performance_DBMigrator::migrate();
			update_option( 'lazytask_performance_db_version', LAZYTASK_PERFORMANCE_DB_VERSION );
		}
	}

	private function do_enqueue() {
		$asset_file = LAZYTASK_PERFORMANCE_PATH . 'admin/frontend/build/index.asset.php';
		$asset      = file_exists( $asset_file ) ? require $asset_file : array( 'version' => $this->version );

		wp_enqueue_script(
			'lazytasks-performance-script',
			LAZYTASK_PERFORMANCE_URL . 'admin/frontend/build/index.js',
			array( 'lazytasks-script', 'wp-element' ),
			$asset['version'],
			true
		);

		if ( file_exists( LAZYTASK_PERFORMANCE_PATH . 'admin/frontend/build/index.css' ) ) {
			wp_enqueue_style(
				'lazytasks-performance-style',
				LAZYTASK_PERFORMANCE_URL . 'admin/frontend/build/index.css',
				array(),
				$asset['version']
			);
		}

		wp_localize_script( 'lazytasks-performance-script', 'appLocalizerPerformance', array(
			'apiUrl'  => home_url( '/wp-json' ),
			'homeUrl' => home_url( '' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );
	}
}
