<?php
/**
 * Admin Settings Profile visit restrictions
 *
 * @package    BP_Profile_Visit_Restrictions
 * @subpackage Admin
 * @copyright  Copyright (c) 2018, BuddyDev.Com
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     BuddyDev
 * @since      1.0.0
 */

namespace BP_Profile_Visit_Restrictions\Admin;

use \Press_Themes\PT_Settings\Page;

// Exit if file accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Settings
 */
class Admin_Settings {

	/**
	 * Admin Menu slug
	 *
	 * @var string
	 */
	private $menu_slug;

	/**
	 * Used to keep a reference of the Page, It will be used in rendering the view.
	 *
	 * @var \Press_Themes\PT_Settings\Page
	 */
	private $page;

	/**
	 * Boot settings
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup settings
	 */
	public function setup() {

		$this->menu_slug = 'profile-visit-restrictions';

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * Show/render the setting page
	 */
	public function render() {
		$this->page->render();
	}

	/**
	 * Is it the setting page?
	 *
	 * @return bool
	 */
	private function needs_loading() {

		global $pagenow;

		// We need to load on options.php otherwise settings won't be reistered.
		if ( 'options.php' === $pagenow ) {
			return true;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] === $this->menu_slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Initialize the admin settings panel and fields
	 */
	public function init() {

		if ( ! $this->needs_loading() ) {
			return;
		}

		$page = new Page( 'profile-visit-restrictions', __( 'Profile Visit Restrictions', 'bp-profile-visit-restrictions' ) );

		// General settings tab.
		$panel = $page->add_panel( 'general', _x( 'General', 'Admin settings panel title', 'bp-profile-visit-restrictions' ) );

		$section = $panel->add_section( 'general', __( 'General settings', 'bp-profile-visit-restrictions' ) );
		$section->add_fields(
			array(
				array(
					'name'    => 'message',
					'label'   => __( 'Restriction Message.', 'bp-profile-visit-restrictions' ),
					'default' => __( 'You have reached the daily limit', 'bp-profile-visit-restrictions' ),
					'type'    => 'rawtext',
				),
				array(
					'name'    => 'redirect_url',
					'label'   => __( 'Redirect to?', 'bp-profile-visit-restrictions' ),
					'desc'    => __( 'Where to redirect to when a user reaches the limit. Allowed tags[site-url], [visitor-url]', 'bp-profile-visit-restrictions' ),
					'default' => '',
					'type'    => 'text',
				),
			)
		);

		$section = $panel->add_section( 'settings', _x( 'Role based limits', 'Admin settings section title', 'bp-profile-visit-restrictions' ), __( 'Set per day limits for each role.', 'bp-profile-visit-restrictions' ) );

		$roles = get_editable_roles();
		$roles = wp_list_pluck( $roles, 'name' );

		$defaults = bp_profile_visit_restrictions()->get_default_limits();
		foreach ( $roles as $key => $role ) {
			$section->add_field(
				array(
					'name'    => $key,
					'label'   => $role,
					'desc'    => __( 'Put -1 to not restrict.', 'bp-profile-visit-restrictions' ),
					'type'    => 'text', // not specifying number as browser kills it.
					'default' => isset( $defaults[ $key ] ) ? $defaults[ $key ] : 0,
				)
			);
		}

		do_action( 'bp_profile_visit_restrictions_admin_settings_page', $page );

		$this->page = $page;

		// allow enabling options.
		$page->init();
	}

	/**
	 * Add Menu
	 */
	public function add_menu() {

		add_options_page(
			_x( 'Profile Visit Restrictions', 'Admin settings page title', 'bp-profile-visit-restrictions' ),
			_x( 'Profile Visit Restrictions', 'Admin settings menu label', 'bp-profile-visit-restrictions' ),
			'manage_options',
			$this->menu_slug,
			array( $this, 'render' )
		);
	}
}
