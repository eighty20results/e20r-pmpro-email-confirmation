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

class HTML_Generator {
	
	/**
	 * Generate "resend email with confirmation link" email form/content
	 *
	 * @param array    $attributes
	 * @param \WP_User $wp_user
	 *
	 * @return null|string
	 */
	public static function createResendForm( $attributes, $wp_user = null ) {
		
		$utils = Utilities::get_instance();
		
		if ( empty( $attributes ) ) {
			$utils->log( "No settings for form!" );
			
			return null;
		}
		
		if ( empty( $wp_user ) ) {
			global $current_user;
			$wp_user = $current_user;
		}
		
		$html    = array();
		$use_sms = (bool) $attributes['allow_sms'];
		$hide_full = !(bool) $attributes['show_full_form'];
		
		$html[] = sprintf( '<div class="e20r-email-confirmation-form">' );
		$html[] = sprintf( '<div class="e20r-warnings e20r-start-hidden">' );
		$html[] = sprintf( '<p class="e20r-warning-message"></p>' );
		$html[] = sprintf( '</div>' );
		$html[] = self::maybeAddLoginWarning( $attributes['not_logged_in_msg'] );
		$html[] = sprintf( '<h2 class="">%1$s</h2>', esc_html( $attributes['header'] ) );
		$html[] = sprintf( '<form action="" id="e20r-email-confirmation-form" enctype="multipart/form-data">' );
		$html[] = wp_nonce_field( 'e20r_send_confirmation', 'e20r_email_conf', true, false );
		
		$html[] = sprintf( '<div class="e20r-email-input %1$s">', ( $hide_full ? 'e20r-start-hidden' : null ) );
		$html[] = sprintf( '<input type="hidden" name="e20r-user-id" value="%1$d" />', $wp_user->ID );
		$html[] = sprintf(
			'<input type="hidden" id="e20r-redirect-slug" value="%1$s" />',
			( empty( $attributes['confirmation_page_slug'] ) ? '' : urlencode( $attributes['confirmation_page_slug'] ) )
		);
		$html[] = sprintf(
			'<input type="hidden" id="e20r-confirmation-msg" value="%1$s" />',
			( empty( $attributes['confirmation_msg'] ) ? '' : $attributes['confirmation_msg'] )
		);
		// $html[] = sprintf( '<label for="e20r-recipient-email">%1$s</label>', __( 'Email address:', Email_Confirmation_Shortcode::plugin_slug ) );
		$html[] = sprintf(
			'<input type="email" class="e20r-recipient-email" name="e20r-recipient-email" placeholder="%2$s" id="e20r-recipient-email" value="%1$s" />',
			$wp_user->user_email,
			__( 'Email address:', Email_Confirmation_Shortcode::plugin_slug )
		);
		$html[] = sprintf( '</div>' );
		
		if ( true === $use_sms ) {
			$html[] = sprintf( '<div class="e20r-sms-prompt %1$s">', ( $hide_full ? 'e20r-start-hidden' : null ) );
			$html[] = sprintf(
				'<label for="e20r-sms-checkbox" class="e20r-use-sms">%1$s</label>',
				__( 'Send as text message (cellphone)?', Email_Confirmation_Shortcode::plugin_slug )
			);
			$html[] = sprintf( '<input type="checkbox" id="e20r-sms-checkbox" value="1" class="e20r-use-sms" />' );
			$html[] = sprintf( '</div>' );
			$html[] = sprintf( '<div class="e20r-sms-input e20r-start-hidden">' );
			$html[] = self::addSMSFields();
			$html[] = sprintf( '</div>' );
		}
		
		$html[] = sprintf( '<input type="submit" class="e20r-email-submit" value="%1$s" />', esc_html( $attributes['button_text'] ) );
		/*
		$html[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url_raw( $send_to ),
			__('User profile', Email_Confirmation_Shortcode::plugin_slug )
		);
		*/
		$html[] = sprintf( '</form>' );
		$html[] = sprintf( '</div>' );
		
		return empty( $html ) ? null : implode( "\n", $html );
	}
	
	/**
	 * Add reminder to log in if the user isn't already logged in
	 *
	 * @param string $message
	 *
	 * @return null|string
	 */
	private static function maybeAddLoginWarning( $message ) {
		
		if ( is_user_logged_in() ) {
			return null;
		}
		
		return self::notLoggedIn( $message );
	}
	
	/**
	 * Generate the HTML to use when displaying the "User must be logged in" message/text
	 *
	 * @param string $msg
	 *
	 * @return string
	 */
	public static function notLoggedIn( $msg ) {
		
		if ( empty( $msg ) ) {
			return null;
		}
		
		$current_page_url = get_permalink();
		
		$html   = array();
		$html[] = sprintf( '<div class="e20r-ecs-not-logged-in">' );
		$html[] = sprintf( '<p class="e20r-ecs-not-logged-in-text">%1$s</p>', $msg );
		$html[] = sprintf(
			'<a class="e20r-ecs-login-link" href="%1$s">%2$s</a>',
			wp_login_url( $current_page_url ),
			__( "Log in and return", Email_Confirmation_Shortcode::plugin_slug )
		);
		
		$html[] = sprintf( '</div>' );
		
		return implode( "\n", $html );
	}
	
	/**
	 * Add form fields to support sending the reminder as an SMS
	 *
	 * @return null|string
	 */
	private static function addSMSFields() {
		$html = null;
		
		/*
		$html[] = sprintf(
			'<label for="e20r-recipient-phone" class="e20r-recipient-phone">%1$s</label>',
			__( 'Phone number:', Email_Confirmation_Shortcode::plugin_slug )
		);
		*/
		$html[] = sprintf(
			'<input type="text" name="e20r-recipient-phone" placeholder="%1$s" id="e20r-recipient-phone" class="e20r-recipient-phone" value="" />',
			__( 'a phone number', Email_Confirmation_Shortcode::plugin_slug )
		);
		
		return empty( $html ) ? null : implode( "\n", $html );
	}
}
