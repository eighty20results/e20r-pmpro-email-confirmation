<?php
/**
 *  Copyright (c) 2019. - Eighty / 20 Results by Wicked Strong Chicks.
 *  ALL RIGHTS RESERVED
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  You can contact us at mailto:info@eighty20results.com
 */

namespace E20R\PMPro\Addon\Email_Confirmation;


use E20R\Utilities\Utilities;

/**
 * Class Redirect_Handler
 * @package E20R\PMPro\Addon\Email_Confirmation
 */
class Redirect_Handler {
	
	/**
	 * Instance of this class
	 *
	 * @var null|Redirect_Handler
	 */
	private static $instance = null;
	
	/**
	 * Get or instantiate and return the class
	 *
	 * @return Redirect_Handler|null
	 */
	public static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Load required action/filter handlers
	 */
	public function loadHooks() {
		
		add_filter( 'login_redirect', array( $this, 'maybeRedirect' ), 9999, 3 );
		add_action( 'wp_login', array( $this, 'maybeRedirect' ), - 1, 2 );
	}
	
	/**
	 * Handler for the WP Login action (wp_login)
	 *
	 * @param string   $user_login
	 * @param \WP_User $user
	 *
	 * @return string
	 */
	public function maybeRedirect( $user_login, $user ) {
		
		global $post;
		$should_redirect = (bool) Settings::get( 'redirect_if_not_verified' );
		$utils = Utilities::get_instance();
		
		if ( false === $should_redirect ) {
			$utils->log("Redirect not configured");
			return;
		}
		
		$tml_login = true;
		
		if ( function_exists( 'tml_is_action' ) ) {
			$utils->log("TML is active");
			$tml_login = tml_is_action( 'login' );
		}
		
		if ( false === $tml_login ) {
			$utils->log("TML: Not processing login action");
			return;
		}
		
		if ( empty( $user ) && ! empty( $user_login ) ) {
			$user = get_user_by( 'login', $user_login );
		}
		
		if ( true === $this->isValidated( $user ) ) {
			$utils->log("User is validated already...");
			return;
		}
		
		$redirect_to_page_id = (int) Settings::get( 'pec_redirect_target_page' );
		
		if ( - 1 === $redirect_to_page_id || empty( $redirect_to_page_id ) ) {
			$utils->log("No target to redirect to");
			return;
		}
		
		if ( isset( $post->ID ) && $redirect_to_page_id == $post->ID ) {
			$utils->log("Trying to redirect to self...");
			return;
		}
		
		$redirect_to = get_permalink( $redirect_to_page_id );
		
		wp_safe_redirect( $redirect_to );
		exit();
	}
	
	/**
	 * Is the user a validated member (i.e. did they click their email validation link)
	 *
	 * @param \WP_User $user
	 *
	 * @return bool
	 *
	 * @access private
	 */
	private function isValidated( $user ) {
		
		if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ||
		     ! function_exists( 'pmproec_isEmailConfirmationLevel' ) ) {
			return true;
		}
		
		if ( empty( $user ) ) {
			return true;
		}
		
		// Grab their membership level
		$user_membership_level = pmpro_getMembershipLevelForUser( $user->ID );
		
		// Not a member so is actually validated.
		if ( empty( $user_membership_level ) ) {
			return true;
		}
		
		// Not a level requiring validation
		if ( false === pmproec_isEmailConfirmationLevel( $user_membership_level->id ) ) {
			return true;
		}
		
		// Get the validation key for the user
		$validation_key = get_user_meta( $user->ID, 'pmpro_email_confirmation_key', true );
		
		if ( ! empty( $validation_key ) && "validated" == $validation_key ) {
			return true;
		}
		
		return false;
	}
}
