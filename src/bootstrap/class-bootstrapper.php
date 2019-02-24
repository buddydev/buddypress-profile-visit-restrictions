<?php
/**
 * Bootstrapper. Initializes the plugin.
 *
 * @package    BP_Profile_Visit_Restrictions
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace BP_Profile_Visit_Restrictions\Bootstrap;

use BP_Profile_Visit_Restrictions\Admin\Admin_Settings;
use BP_Profile_Visit_Restrictions\Admin\PMPro_Admin_Settings;
use BP_Profile_Visit_Restrictions\Handlers\Actions_Handler;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Bootstrapper.
 */
class Bootstrapper {

	/**
	 * Setup the bootstrapper.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Bind hooks
	 */
	private function setup() {
		add_action( 'bp_loaded', array( $this, 'load' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'load_admin' ), 9996 ); // pt settings 1.0.4.
		add_action( 'bp_init', array( $this, 'load_translations' ) );
	}

	/**
	 * Load core functions/template tags.
	 * These are non auto loadable constructs.
	 */
	public function load() {
		Actions_Handler::boot();
	}

	/**
	 * Load pt-settings framework
	 */
	public function load_admin() {

		if ( function_exists( 'buddypress' ) && is_admin() && ! defined( 'DOING_AJAX' ) ) {
			require_once bp_profile_visit_restrictions()->path . 'src/admin/pt-settings/pt-settings-loader.php';
			Admin_Settings::boot();
		}
	}

	/**
	 * Load translations.
	 */
	public function load_translations() {
		load_plugin_textdomain( 'bp-profile-visit-restrictions', false, basename( bp_profile_visit_restrictions()->path ) . '/languages' );
	}
}
