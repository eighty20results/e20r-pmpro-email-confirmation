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
use E20R\Utilities\Licensing\License_Client;
use E20R\Utilities\Licensing\Licensing;
use E20R\Utilities\Utilities;

class PMPEC_License extends License_Client {
	
	/**
	 * The current (only) instance of this License_Client class
	 *
	 * @var null|PMPEC_License
	 */
	private static $instance = null;
	
	/**
	 * PMPEC_License constructor.
	 */
	private function __construct() {
	}
	
	/**
	 * Return, or create, instance of PMPEC_License class
	 *
	 * @return PMPEC_License|null
	 */
	public static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Load action hooks & filters for Client License handler
	 */
	public function load_hooks() {
		
		if ( is_admin() ) {
			add_filter( 'e20r-license-add-new-licenses', array( $this, 'add_new_license_info', ), 10, 2 );
			add_action( 'admin_init', array( $this, 'check_licenses' ) );
		}
	}
	
	/**
	 * Configure settings for the E20R Email Confirmation Reminder Shortcode for PMPro license (must match upstream license info)
	 *
	 * @param array $license_settings
	 * @param array $plugin_settings
	 *
	 * @return array
	 */
	public function add_new_license_info( $license_settings, $plugin_settings = array() ) {
		
		$utils = Utilities::get_instance();
		
		if ( ! is_array( $plugin_settings ) ) {
			$plugin_settings = array();
		}
		
		$utils->log( "Load settings for the E20R Email Confirmation Reminder Shortcode for PMPro" );
		$plugin_settings['e20r_pmpec'] = array(
			'key_prefix'  => 'e20r_pmpec',
			'stub'        => 'e20r_pmpec',
			'product_sku' => 'E20R_PMPEC',
			'label'       => __( 'E20R Email Confirmation Reminder Shortcode for PMPro', Email_Confirmation_Shortcode::plugin_slug ),
		);
		
		$license_settings = parent::add_new_license_info( $license_settings, $plugin_settings['e20r_pmpec'] );
		
		return $license_settings;
	}
	
	/**
	 * Load a custom license warning on init
	 */
	public function check_licenses() {
		
		$utils = Utilities::get_instance();
		
		switch ( Licensing::is_license_expiring( 'e20r_pmpec' ) ) {
			
			case true:
				$utils->add_message(
					sprintf(
						__(
							'The license for \'%1$s\' will renew soon. As this is an automatic payment, you will not have to do anything. To change %2$syour license%3$s, please go to %4$syour account page%5$s',
							Email_Confirmation_Shortcode::plugin_slug
						),
						__(
							'E20R Email Confirmation Reminder Shortcode for PMPro (with Support &amp; Updates)',
							Email_Confirmation_Shortcode::plugin_slug
						),
						'<a href="https://eighty20results.com/shop/licenses/" target="_blank">',
						'</a>',
						'<a href="https://eighty20results.com/account/" target="_blank">',
						'</a>'
					),
					'info',
					'backend'
				);
				break;
			case - 1:
				$utils->add_message(
					sprintf(
						__(
							'Your \'%1$s\' license has expired. To continue to get updates and support for this plugin, you will need to %2$srenew and install your license%3$s.',
							Email_Confirmation_Shortcode::plugin_slug
						),
						__(
							'E20R Email Confirmation Reminder Shortcode for PMPro',
							Email_Confirmation_Shortcode::plugin_slug
						),
						'<a href="https://eighty20results.com/shop/licenses/" target="_blank">', '</a>'
					),
					'error',
					'backend'
				);
				break;
		}
	}
	
	/**
	 * Deactivate the __clone() magic method since this is a singleton class
	 */
	private function __clone() {
	}
}
