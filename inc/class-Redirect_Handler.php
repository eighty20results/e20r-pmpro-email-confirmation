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


use E20R\Utilities\Licensing\Licensing;
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
		
		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
			Utilities::get_instance()->log( "No login to redirect for - AJAX operation" );
			
			return;
		}
		
		add_filter( 'login_redirect', array( $this, 'loginRedirectHandler' ), 9999, 3 );
		add_action( 'wp_login', array( $this, 'wpLoginHandler' ), - 1, 2 );
	}
	
	/**
	 * Set the redirect URL if necessary (when processing login_redirect action)
	 *
	 * @param string             $redirect_to
	 * @param string             $requested_redirect_to
	 * @param \WP_User|\WP_Error $user
	 *
	 * @return string
	 */
	public function loginRedirectHandler( $redirect_to, $requested_redirect_to, $user ) {
		
		$utils = Utilities::get_instance();
		
		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
			Utilities::get_instance()->log( "No login to redirect for - AJAX operation" );
			
			return $redirect_to;
		}
		
		if ( is_wp_error( $user ) ) {
			$utils->log( "WP Error: " . $user->get_error_message() );
			
			return $redirect_to;
		}
		
		$old_redirect_to = $redirect_to;
		$redirect_to     = $this->maybeRedirect( $user, null, true );
		
		$utils->log( "Do we need to redirect? " . $redirect_to );
		
		if ( false === $redirect_to ) {
			$utils->log( "Restoring original value: {$old_redirect_to}" );
			$redirect_to = $old_redirect_to;
		}
		
		return $redirect_to;
	}
	
	/**
	 * Handler for the WP Login action (wp_login)
	 *
	 * @param string|null $user_login
	 * @param \WP_User    $user
	 * @param bool        $from_redirect
	 *
	 * @return string|False
	 */
	public function maybeRedirect( $user, $user_login = null, $from_redirect = false ) {
		
		global $post;
		$should_redirect = (bool) Settings::get( 'redirect_if_not_verified' );
		$utils           = Utilities::get_instance();
		
		if ( false === $should_redirect ) {
			$utils->log( "Redirect not configured" );
			
			return false;
		}
		
		if ( false === Licensing::is_licensed( 'e20r_pmpec' ) ) {
			$utils->log( "Redirect not allowed (inactive license)" );
			
			return false;
		}
		
		$tml_login = true;
		
		if ( function_exists( 'tml_is_action' ) ) {
			$utils->log( "TML is active" );
			$tml_login = tml_is_action( 'login' );
		}
		
		if ( false === $tml_login ) {
			$utils->log( "TML: Not processing login action" );
			
			return false;
		}
		
		if ( empty( $user ) && ! empty( $user_login ) ) {
			$utils->log( "User not provided and trying to find user from: {$user_login}" );
			$user = get_user_by( 'login', $user_login );
		}
		
		if ( true === $this->isValidated( $user ) ) {
			$utils->log( "User is validated already..." );
			
			return false;
		}
		
		$utils->log( "Fetch the page ID to redirect to..." );
		$redirect_to_page_id = (int) Settings::get( 'pec_redirect_target_page' );
		
		if ( - 1 === $redirect_to_page_id || empty( $redirect_to_page_id ) ) {
			$utils->log( "No target to redirect to" );
			
			return false;
		}
		
		if ( isset( $post->ID ) && $redirect_to_page_id == $post->ID ) {
			$utils->log( "Trying to redirect to self..." );
			
			return false;
		}
		
		$redirect_to = get_permalink( $redirect_to_page_id );
		
		if ( false === $from_redirect ) {
			$utils->log( "Attempting to do a safe redirect to {$redirect_to_page_id} and exiting ({$redirect_to})" );
			wp_safe_redirect( $redirect_to );
			exit();
		}
		
		return $redirect_to;
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
		
		$utils = Utilities::get_instance();
		
		if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ||
		     ! function_exists( 'pmproec_isEmailConfirmationLevel' ) ) {
			$utils->log( "None of the PMPro functions we need are active!" );
			
			return true;
		}
		
		if ( empty( $user ) ) {
			$utils->log( "No user object found!" );
			
			return true;
		}
		
		// Grab their membership level
		$user_membership_level = pmpro_getMembershipLevelForUser( $user->ID );
		
		// Not a member so is actually validated.
		if ( empty( $user_membership_level ) ) {
			$utils->log( "This user doesn't have a membership ID: {$user->ID}" );
			
			return true;
		}
		
		// Not a level requiring validation
		if ( false === pmproec_isEmailConfirmationLevel( $user_membership_level->id ) ) {
			$utils->log( "Not a membership level that requires confirmation (id: {$user_membership_level->id})!" );
			
			return true;
		}
		
		// Get the validation key for the user
		$validation_key = get_user_meta( $user->ID, 'pmpro_email_confirmation_key', true );
		
		if ( ! empty( $validation_key ) && "validated" == $validation_key ) {
			$utils->log( "User {$user->ID} has a validation key and is validated!" );
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Handle possible redirects if coming from wp-login.php
	 *
	 * @param string   $user_login
	 * @param \WP_User $user
	 *
	 */
	public function wpLoginHandler( $user_login, $user ) {
		
		$utils = Utilities::get_instance();
		
		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
			$utils->log( "No login to redirect for - AJAX operation" );
			return;
		}
		
		$utils->log( "Trigger the redirect handler (if we need it)" );
		
		$this->maybeRedirect( $user, $user_login, false );
	}
}
