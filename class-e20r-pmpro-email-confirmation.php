<?php
/*
Plugin Name: E20R - Email Confirmation Shortcode plugin
Plugin URI: http://eighty20results.com
Description: A brief description of the Plugin.
Version: 1.0
Author: Thomas at Eighty/20 Results by Wicked Strong Chicks, LLC <thomas@eighty20results.com>
Author URI: https://eighty20results.com/thomas-sjolshagen/
License: GPL2
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

namespace E20R\PMPro\Addon;

use E20R\PMPro\Addon\Email_Confirmation\Shortcode;

/**
 * Class Email_Confirmation_Shortcode
 *
 * @package E20R\PMPro\Addon
 */
class Email_Confirmation_Shortcode {
	
	/**
	 * Singleton instance of this class
	 *
	 * @var null|Email_Confirmation_Shortcode
	 */
	private static $instance = null;
	
	/**
	 * Email_Confirmation_Shortcode constructor.
	 *
	 * @access private
	 */
	private function __construct() {
	}
	
	/**
	 * @return Email_Confirmation_Shortcode|null
	 */
	public static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function loadHooks() {
		
		add_shortcode( 'e20r_confirmation_form', array( Shortcode::getInstance(), 'loadShortcode' ) );
		
		add_action( 'admin_init', array( $this, 'checkDependencies' ) );
	}
	
	public function checkDependencies() {
		
		// TODO: Add plugin check for the PMPro Email Validation plugin
	}
	
	/**
	 * Hide the clone() magic method
	 *
	 * @access private
	 */
	private function __clone() {
		// TODO: Implement __clone() method.
	}
}

add_action( 'plugins_loaded', array( Email_Confirmation_Shortcode::getInstance(), 'loadHooks' ) );