<?php
/**
 * Core functions file
 *
 * @package BP_Profile_Visit_Restrictions
 */

namespace BP_Profile_Visit_Restrictions\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages visits.
 */
class User_Visits_Manager {

	/**
	 * Allow or not?
	 *
	 * @param int $user_id user id.
	 * @param int $visited_id visitor id.
	 *
	 * @return bool
	 */
	public static function allow( $user_id, $visited_id ) {

		if ( $user_id == $visited_id || is_super_admin( $visited_id ) ) {
			return true;
		}

		// if user is not restricted, no need to proceed further.
		if ( ! self::is_user_restricted( $user_id ) ) {
			return true;
		}

		$allowed_count = self::get_allowed_count( $user_id );

		if ( ! $allowed_count ) {
			return false;// not allowed for '0'.
		}

		$visits = self::get_records( $user_id );
		if ( empty( $visits ) ) {
			self::record( $user_id, $visited_id );

			return true;
		}

		$today_key = self::get_today_key();

		// we have records but those are not from today, let us clear them.
		if ( empty( $visits[ $today_key ] ) ) {
			self::clear_old_records( $user_id );
			self::record( $user_id, $visited_id );

			return true;
		}
		// today's visits.
		$today = $visits[ $today_key ];
		// already visited.
		if ( in_array( $user_id, $today ) ) {
			return true;// allow visiting again.
		}

		// if we are here, It is a new profile visit.
		$today_visit_count = count( $today );
		if ( $today_visit_count < $allowed_count ) {
			self::record( $user_id, $visited_id );

			return true;
		}

		return false;
	}

	/**
	 * Record a visit.
	 *
	 * @param int $user_id user id.
	 * @param int $visited_id visitor id.
	 *
	 * @return bool
	 */
	public static function record( $user_id, $visited_id ) {
		if ( ! $user_id || ! $visited_id ) {
			return false;
		}

		$today_key = self::get_today_key();
		$records   = self::get_records( $user_id );
		if ( empty( $records ) ) {
			$records = array(
				$today_key => array(),
			);
		}

		array_push( $records[ $today_key ], $visited_id );
		self::set_records( $user_id, $records );

		return true;
	}

	/**
	 * How many visits per day this user is allowed.
	 *
	 * @param int $user_id id.
	 *
	 * @return int
	 */
	public static function get_allowed_count( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user || empty( $user->roles ) ) {
			return 0;
		}

		$role_limits = self::get_role_based_limits();
		$max         = 0;
		foreach ( $user->roles as $role ) {
			if ( isset( $role_limits[ $role ] ) && $role_limits[ $role ] > $max ) {
				$max = $role_limits[ $role ];
			}
		}
		return $max;
	}

	/**
	 * Is it the first record for new day?
	 *
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function is_new_day( $user_id ) {
		$key     = self::get_today_key();
		$records = self::get_records( $user_id );

		return empty( $records ) || ! isset( $records[ $key ] );
	}

	/**
	 * Get all records.
	 *
	 * @param int $user_id user id.
	 *
	 * @return array
	 */
	public static function get_records( $user_id ) {
		$visited = get_user_meta( $user_id, '_bppvr_visited_profiles', true );

		return $visited;
	}

	/**
	 * Set visit records.
	 *
	 * @param int   $user_id user id.
	 * @param array $records records.
	 *
	 * @return bool|int
	 */
	public static function set_records( $user_id, $records ) {
		return update_user_meta( $user_id, '_bppvr_visited_profiles', $records );

	}

	/**
	 * Clear old records if any(preservs today's records).
	 *
	 * @param int $user_id user id.
	 */
	private static function clear_old_records( $user_id ) {
		$records = self::get_records( $user_id );

		if ( empty( $records ) ) {
			return; // No Need to clear.
		}

		$key = self::get_today_key();
		if ( empty( $records[ $key ] ) ) {
			self::set_records( $user_id, array() );
		} else {
			$today = $records[ $key ];
			self::set_records( $user_id, array( $key => $today ) );
		}
	}

	/**
	 * A key for today.
	 *
	 * @return string
	 */
	public static function get_today_key() {
		return date( 'F-j-Y' );
	}

	/**
	 * Get roe based profile visit limits.
	 *
	 * @return array
	 */
	public static function get_role_based_limits() {
		return bp_profile_visit_restrictions()->get_settings();
	}

	/**
	 * Check if a user is restricted.
	 *
	 * @param int $user_id user id.
	 *
	 * @return bool
	 */
	public static function is_user_restricted( $user_id ) {

		$user = get_user_by( 'id', $user_id );

		if ( ! $user || empty( $user->roles ) ) {
			return true;
		}

		if ( is_super_admin( $user_id ) ) {
			return false;
		}

		$role_limits = self::get_role_based_limits();

		foreach ( $user->roles as $role ) {
			if ( isset( $role_limits[ $role ] ) && $role_limits[ $role ] < 0 ) {
				return false;// not restricted.
			}
		}
		return true;
	}
}
