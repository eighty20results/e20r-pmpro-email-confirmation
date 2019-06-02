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
		
		$utils = Utilities::get_instance();
		
		if ( false === check_ajax_referer( 'e20r_send_confirmation', 'e20r_email_conf', false ) ) {
			$utils->log( "Unable to verify nonce!" );
			wp_send_json_error(
				__(
					'Error: Insecure request!',
					Email_Confirmation_Shortcode::plugin_slug
				)
			);
			exit();
		};
		
		// Force user to log in (redirect to login page w/redirect_to configured)
		if ( ! is_user_logged_in() ) {
			
			$utils->log( "Invalid user!" );
			wp_send_json_error( __( 'Invalid user', Email_Confirmation_Shortcode::plugin_slug ) );
			exit();
		}
		
		if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
			
			$utils->log( "Error: The Paid Memberships Pro plugin is not active!" );
			wp_send_json_error(
				__( 'Error: The Paid Memberships Pro plugin is not active!', Email_Confirmation_Shortcode::plugin_slug )
			);
			exit();
		}
		
		if ( ! function_exists( 'pmproec_resend_confirmation_email' ) ) {
			
			$utils->log( "Error: The PMPro Email Confirmation add-on is not active!" );
			wp_send_json_error(
				__(
					'Error: The PMPro Email Confirmation add-on is not active!',
					Email_Confirmation_Shortcode::plugin_slug
				)
			);
			exit();
		}
		
		
		// Handle sending confirmation email and return success/failure message
		$user_email      = $utils->get_variable( 'e20r_email_address', null );
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
			$utils->log( "Error: Invalid values supplied!" );
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
		
		if ( ! isset( $primary_level->id ) ) {
			wp_send_json_error(
				__( 'Error: Not an active member!', Email_Confirmation_Shortcode::plugin_slug )
			);
			exit();
		}
		
		// Quietly return success if the user's level isn't a confirmation level
		if ( false === pmproec_isEmailConfirmationLevel( $primary_level->id ) && false === $has_confirmation_level ) {
			wp_send_json_success();
			exit();
		}
		
		// Send the user an SMS with the confirmation link info
		if ( true === $send_sms ) {
			$utils->log("Trigger SMS message with confirmation link");
			$sms_success = $this->sendSMSTo( $user, $user_sms_number );
		}
		
		// Something failed when sending the SMS message (text message)
		if ( true === $send_sms && false === $sms_success ) {
			$utils->log( "Error while sending SMS!" );
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
			
			$utils->log("Sending confirmation email for {$user->ID}/{$user_email}");
			
			// Send the confirmation email for this user/level
			pmproec_resend_confirmation_email( $user->ID );
		}
		
		// Return success to caller
		wp_send_json_success();
		exit();
	}
	
	/**
	 * SMS (text message) integration for this plugin
	 *
	 * @param \WP_User $user
	 * @param string   $number
	 *
	 * @return bool
	 */
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
	
	/**
	 * Hidden clone method
	 */
	private function __clone() {
	}
}
