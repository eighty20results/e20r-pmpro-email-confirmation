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


use E20R\PMPro\Addon\Email_Confirmation_Shortcode;
use E20R\Utilities\Utilities;

class AJAX_Handler {
	
	/**
	 * @var null|AJAX_Handler
	 */
	private static $instance = null;
	
	private function __construct() {
	}
	
	private function __clone() {
	}
	
	/**
	 * Get or instantiate and return the class (singleton)
	 *
	 * @return AJAX_Handler|null
	 */
	public static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Process resending email confirmation for user
	 *
	 * Should also handle cases where the user has more than one level (ie using MMPU)
	 */
	public function sendConfirmation() {
		
		if ( false === wp_verify_nonce( 'e20r_email_conf', 'e20r_send_confirmation' ) ) {
			wp_send_json_error(
				__(
					'Error: Invalid NONCE for request',
					Email_Confirmation_Shortcode::plugin_slug
				)
			);
			exit();
		};
		
		// Force user to log in (redirect to login page w/redirect_to configured)
		if ( ! is_user_logged_in() ) {
			
			wp_send_json_error( 10000 );
			exit();
		}
		
		if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
			
			wp_send_json_error(
				__( 'Error: The Paid Memberships Pro plugin is not active!', Email_Confirmation_Shortcode::plugin_slug )
			);
			exit();
		}
		
		if ( ! function_exists( 'pmproec_resend_confirmation_email' ) ) {
			
			wp_send_json_error(
				__(
					'Error: The PMPro Email Confirmation plugin is not active!',
					Email_Confirmation_Shortcode::plugin_slug
				)
			);
			exit();
		}
		
		
		// TODO: Handle sending confirmation email and return success/failure message
		$utils           = Utilities::get_instance();
		$user_email      = $utils->get_variable( 'e20r_email_address', '' );
		$user_sms_number = $utils->get_variable( 'e20r_phone_number', null );
		$user            = get_user_by( 'email', $user_email );
		$primary_level   = pmpro_getMembershipLevelForUser( $user->ID, true );
		$user_levels     = pmpro_getMembershipLevelsForUser( $user->ID, false );
		$send_sms        = ! empty( $user_sms_number );
		$sms_success     = false;
		
		// Did they supply an email address and does that user exists
		if ( empty( $user ) ) {
			global $current_user;
			$user = $current_user;
		}
		
		// Doesn't exist. Return error
		if ( empty( $user ) ) {
			wp_send_json_error(
				__( 'Error: Invalid values supplied!', Email_Confirmation_Shortcode::plugin_slug )
			);
			exit();
		}
		
		// Assume we don't need a confirmation link...
		$has_confirmation_level = false;
		
		// Process all member levels to verify whether they require email confirmation(s)
		foreach ( $user_levels as $level ) {
			$has_confirmation_level = $has_confirmation_level || pmproec_isEmailConfirmationLevel( $level->id );
		}
		
		// Quietly return success if the user's level isn't a confirmation level
		if ( false === pmproec_isEmailConfirmationLevel( $primary_level->id ) && false === $has_confirmation_level ) {
			wp_send_json_success();
			exit();
		}
		
		// Send the user an SMS with the confirmation link info
		if ( true === $send_sms ) {
			$sms_success = $this->sendSMSTo( $user, $user_sms_number );
		}
		
		// Something failed when sending the SMS message (text message)
		if ( true === $send_sms && false === $sms_success ) {
			wp_send_json_error(
				sprintf(
					__( 'Error while sending SMS to %s', Email_Confirmation_Shortcode::plugin_slug ),
					$user_sms_number
				)
			);
			exit();
		}
		
		// Send an email confirmation message to the user
		if ( ! empty( $user_email ) ) {
			
			// Allow us to log errors/failures during email transmission
			add_action( 'wp_mail_failed', array( $this, 'onEmailError' ) );
			
			// Send the confirmation email for this user/level
			pmproec_resend_confirmation_email( $user->ID );
		}
		
		// Return success to caller
		wp_send_json_success();
		exit();
	}
	
	public function sendSMSTo( $user, $number ) {
		
		// TODO: Implement SMS handler for this plugin
		
		return true;
	}
	
	/**
	 * Handler for error during email transmission
	 *
	 * @param \WP_Error $wp_error
	 */
	public function onEmailError( $wp_error ) {
		
		if ( ! is_wp_error( $wp_error ) ) {
			return;
		}
		
		error_log(
			sprintf(
				__(
					'Error sending email message: %s',
					Email_Confirmation_Shortcode::plugin_slug
				),
				$wp_error->get_error_message()
			)
		);
		
		return;
	}
}
