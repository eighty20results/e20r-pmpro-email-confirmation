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

class Shortcode {
	
	private static $instance = null;
	
	private $header = null;
	
	private $button_text = null;
	
	private $confirmation_msg = null;
	
	private $allow_sms = false;
	
	private $not_logged_in_msg = null;
	
	private $target_page_slug = null;
	
	private $full_form = false;
	
	/**
	 * Get or instantiate and return the class (singleton)
	 *
	 * @return Shortcode|null
	 */
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
		
		$attributes = $this->processAttributes( $attrs );
		
		// Default if user isn't logged in
		if ( ! is_user_logged_in() ) {
			return HTML_Generator::notLoggedIn( $attributes['not_logged_in_msg'] );
		}
		
		// Generate HTML for the Resend Validation Message form
		return HTML_Generator::createResendForm( $attributes );
	}
	
	/**
	 * Process the short-code attributes (and save to class)
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	private function processAttributes( $attributes ) {
		
		$attrs = shortcode_atts( array(
			'header'            => __( 'Please (re)send the Validation EMail for my user', Email_Confirmation_Shortcode::plugin_slug ),
			'button_text'       => __( 'Re-send validation link', Email_Confirmation_Shortcode::plugin_slug ),
			'confirmation_msg'  => __( 'If you do not receive the email message, please search through your Junk Mail/Spam folder(s)', Email_Confirmation_Shortcode::plugin_slug ),
			'allow_sms'         => false,
			'not_logged_in_msg' => __( 'Please log in to the system', Email_Confirmation_Shortcode::plugin_slug ),
			'target_page_slug'  => null,
			'full_form'         => 'yes',
		), $attributes );
		
		// Process the class variables and configure them based on the Shortcode attributes (if applicable)
		foreach ( get_object_vars( $this ) as $member_var => $value ) {
			
			// Variable not configured/set!
			if ( ! isset( $attrs[ $member_var ] ) ) {
				continue;
			}
			
			$this->{$member_var} = $attrs[ $member_var ];
		}
		
		return $this->returnAttrs();
	}
	
	/**
	 * Returns the class variables as an array of variable name keys and associated values
	 *
	 * @return array
	 */
	private function returnAttrs() {
		$attributes = array();
		
		foreach( get_object_vars( $this ) as $key => $value ) {
			$attributes[$key] = $value;
		}
		
		return $attributes;
	}
}
