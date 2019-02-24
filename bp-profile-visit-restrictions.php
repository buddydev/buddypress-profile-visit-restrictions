<?php
/**
 * Plugin Name: BuddyPress Profile Visit Restrictions
 * Version: 1.0.0
 * Plugin URI: https://buddydev.com/
 * Description: Poor man's role based daily profile visit quota.
 * Author: BuddyDev
 * Author URI: https://buddydev.com/
 * Requires PHP: 5.3
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  bp-profile-visit-restrictions
 * Domain Path:  /languages
 *
 * @package BP_Profile_Visit_Restrictions
 **/

use BP_Profile_Visit_Restrictions\Bootstrap\Autoloader;
use BP_Profile_Visit_Restrictions\Bootstrap\Bootstrapper;

// No direct access over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BP_Profile_Visit_Restrictions
 *
 * @property-read $path     string Absolute path to the plugin directory.
 * @property-read $url      string Absolute url to the plugin directory.
 * @property-read $basename string Plugin base name.
 * @property-read $version  string Plugin version.
 */
class BP_Profile_Visit_Restrictions {

	/**
	 * Plugin Version.
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Class instance
	 *
	 * @var BP_Profile_Visit_Restrictions
	 */
	private static $instance = null;

	/**
	 * Plugin absolute directory path
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Plugin absolute directory url
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin Basename.
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Protected properties. These properties are inaccessible via magic method.
	 *
	 * @var array
	 */
	private $secure_properties = array( 'instance' );

	/**
	 * BP_Profile_Visit_Restrictions constructor.
	 */
	private function __construct() {
		$this->bootstrap();
	}

	/**
	 * Get Singleton Instance
	 *
	 * @return BP_Profile_Visit_Restrictions
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Bootstrap the core.
	 */
	private function bootstrap() {
		$this->path     = plugin_dir_path( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );

		// Load autoloader.
		require_once $this->path . 'src/bootstrap/class-autoloader.php';

		$autoloader = new Autoloader( 'BP_Profile_Visit_Restrictions\\', __DIR__ . '/src/' );

		spl_autoload_register( $autoloader );

		Bootstrapper::boot();
	}

	/**
	 * Magic method for accessing property as readonly(It's a lie, references can be updated).
	 *
	 * @param string $name property name.
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {

		if ( ! in_array( $name, $this->secure_properties, true ) && property_exists( $this, $name ) ) {
			return $this->{$name};
		}

		return null;
	}
	/**
	 * Magic method for accessing property as readonly(It's a lie, references can be updated).
	 *
	 * @param string $name property name.
	 *
	 * @return bool
	 */
	public function __isset( $name ) {

		if ( ! in_array( $name, $this->secure_properties, true ) && property_exists( $this, $name ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get limit.
	 *
	 * @return array
	 */
	public function get_settings() {
		return get_option(
			'profile-visit-restrictions',
			$this->get_default_limits()
		);
	}

	/**
	 * Get default limits.
	 *
	 * @return array
	 */
	public function get_default_limits() {
		return array(
			'subscriber'    => 2,
			'contributor'   => 5,
			'author'        => 10,
			'editor'        => 100,
			'administrator' => 100000,
		);
	}
}

/**
 * Helper to access singleton instance
 *
 * @return BP_Profile_Visit_Restrictions
 */
function bp_profile_visit_restrictions() {
	return BP_Profile_Visit_Restrictions::get_instance();
}

bp_profile_visit_restrictions();
