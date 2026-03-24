<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    Lazytask_Performance
 * @subpackage Lazytask_Performance/includes
 */

namespace Lazytask_Performance\Includes;

class Lazytask_Performance_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {


		\Lazytask_Performance\Helper\Performance_DBMigrator::migrate();

	}

}
