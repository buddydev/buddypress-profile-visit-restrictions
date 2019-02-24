<?php
/**
 * Action handler class
 *
 * @package BP_Profile_Visit_Restrictions
 */

namespace BP_Profile_Visit_Restrictions\Handlers;

// Exit if accessed directly.
use BP_Profile_Visit_Restrictions\Core\User_Visits_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Actions_Handler
 */
class Actions_Handler {

	/**
	 * Class self boot
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup
	 */
	private function setup() {
		add_action( 'bp_template_redirect', array( $this, 'gate' ) );
	}

	/**
	 * Gate.
	 */
	public function gate() {
		if ( ! is_user_logged_in() || ! bp_is_user() || bp_is_my_profile() ) {
			// do something for non logged.
			return;// we don't deal with it yet.
		}

		if ( ! User_Visits_Manager::allow( get_current_user_id(), bp_displayed_user_id() ) ) {
			$settings = bp_profile_visit_restrictions()->get_settings();
			$url      = isset( $settings['redirect_url'] ) ? $settings['redirect_url'] : '';

			if ( ! empty( $settings['message'] ) ) {
				bp_core_add_message( $settings['message'], 'error' );
			}

			if ( $url ) {
				$url = str_replace(
					array( '[site-url]', '[visitor-user]' ),
					array(
						site_url( '/' ),
						bp_loggedin_user_domain(),
					),
					$url
				);
			} else {
				$url = bp_loggedin_user_domain();
			}
			bp_core_redirect( $url );
		}
	}

}

