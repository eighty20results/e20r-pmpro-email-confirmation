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

use E20R\PMPro\Addon\Email_Confirmation\HTML_Generator;

class Shortcode {
	
	private static $instance = null;
	
	public static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Generate the Form/HTML for the "Please resend the validation email" message
	 *
	 * @param array $attrs
	 *
	 * @return null|string
	 */
	public function loadShortcode( $attrs ) {
		
		$html = null;
		
		$attributes = shortcode_atts( array(
			'header'            => __( 'Please send the Validation EMail for my user', 'e20r-pmpro-email-confirmation' ),
			'button_text'       => __( 'Send email with link', 'e20r-pmpro-email-confirmation' ),
			'confirmation_msg'  => __( 'If you do not receive the email, please check your Junk Mail/Spam folder(s)', 'e20r-pmpro-email-confirmation' ),
			'allow_sms'         => false,
			'not_logged_in_msg' => null,
		), $attrs );
		
		
		if ( ! is_user_logged_in() ) {
			return HTML_Generator::notLoggedIn( $attributes['not_logged_in_msg'] );
		}
		
		$html = HTML_Generator::createResendForm( $attributes );
		
		return $html;
	}
}
