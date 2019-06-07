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


class Redirect_Handler {
	
	/**
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
	}
	
	/**
	 * Filter handler for the WP Redirect target
	 *
	 * @param string   $redirect_to
	 * @param string   $requested_redirect_to
	 * @param \WP_User $user
	 *
	 * @return string
	 */
	public function maybeRedirect( $redirect_to, $requested_redirect_to, $user ) {
		
		$should_redirect = Settings::get( 'redirect_if_not_verified' );
		
		if ( false === $should_redirect ) {
			return $redirect_to;
		}
		
		if ( true === $this->isValidated( $user ) ) {
			return $redirect_to;
		}
		
		$redirect_to_page_id = Settings::get( 'pec_redirect_target_page' );
		
		if ( -1 === $redirect_to_page_id ) {
			return $redirect_to;
		}
		
		$redirect_to = get_permalink( $redirect_to_page_id );
		
		return $redirect_to;
	}
	
	/**
	 * Is the user a validated member (i.e. did they click their email validation link)
	 *
	 * @param \WP_User $user
	 *
	 * @return bool
	 */
	private function isValidated( $user ) {
		
		if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ||
		     ! function_exists( 'pmproec_isEmailConfirmationLevel' ) ) {
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
		$validation_key = get_user_meta( $user->ID, "pmpro_email_confirmation_key", true );
		
		if ( ! empty( $validation_key ) && "validated" == $validation_key ) {
			return true;
		}
		
		return false;
	}
}
