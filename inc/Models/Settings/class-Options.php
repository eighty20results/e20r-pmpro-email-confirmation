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

namespace E20R\PMPro\Addon\Email_Confirmation\Models\Settings;

use E20R\Utilities\Utilities;

/**
 * Class Options
 * @package E20R\PMPro\Addon\Email_Confirmation\Models\Settings\
 *
 */
class Options {
	
	/**
	 * @var null|Options
	 */
	private static $instance = null;
	
	/**
	 * The settings for this plugin
	 *
	 * @var array $settings
	 */
	private $settings = null;
	
	/**
	 * Options constructor.
	 */
	public function __construct() {
		
		$this->load();
	}
	
	/**
	 * Load settings for plugin
	 *
	 * @return array
	 */
	public function load() {
		
		$utils = Utilities::get_instance();
		
		if ( ! empty( $this->settings ) ) {
			return $this->settings;
		}
		
		$option_name    = self::getOptionName();
		$this->settings = get_option( $option_name, $this->defaults() );
		
		$utils->log( "Loading {$option_name} options" );
		
		// Merge defaults & saved settings
		$this->settings = shortcode_atts( $this->defaults(), $this->settings );
		
		return $this->settings;
	}
	
	/**
	 * The name of the option
	 *
	 * @return string
	 */
	public static function getOptionName() {
		return 'e20r_pec_opts';
	}
	
	/**
	 * Default settings for the plugin
	 *
	 * @return array
	 *
	 * @since v1.1 - Add settings
	 */
	public function defaults() {
		
		return array(
			'redirect_if_not_verified' => false,
			'pec_redirect_target_page' => - 1,
		);
	}
	
	/**
	 * Fetch setting (named) or settings from class
	 *
	 * @param null|string $setting_name
	 *
	 * @return array|mixed|null
	 */
	public function get( $setting_name = null ) {
		
		if ( is_null( $setting_name ) ) {
			return $this->settings;
		}
		
		if ( ! isset( $this->settings[ $setting_name ] ) ) {
			return null;
		}
		
		return $this->settings[ $setting_name ];
	}
	
	/**
	 * Fetch settings from class
	 *
	 * @return array
	 */
	public function getOptions() {
		
		if ( empty ( $this->settings ) ) {
			$this->settings = $this->load();
		}
		
		return $this->settings;
	}
	
	/**
	 * Save setting(s) to class. Setting the $setting_name to null and passing an array of settings will save them all
	 * as the new settings.
	 *
	 * @param null|string $setting_name
	 * @param null|mixed  $value
	 *
	 * @return bool
	 */
	public function set( $setting_name = null, $value = null ) {
		
		if ( is_null( $setting_name ) && is_null( $value ) ) {
			return false;
		}
		
		if ( is_null( $setting_name ) && ! empty( $value ) && is_array( $value ) ) {
			$this->settings[ $setting_name ] = $value;
		}
		
		if ( ! is_null( $setting_name ) && isset( $this->settings[ $setting_name ] ) ) {
			$this->settings[ $setting_name ] = $value;
		}
		
		return $this->saveOptions();
	}
	
	/**
	 * Save new settings to class
	 *
	 * @param array $new_settings
	 *
	 * @return bool
	 */
	public function saveOptions( $new_settings = null ) {
		
		if ( empty( $new_settings ) ) {
			$new_settings = $this->settings;
		}
		
		$this->settings = shortcode_atts( $this->defaults(), $new_settings );
		
		return update_option( self::getOptionName(), $this->settings, 'no' );
	}
	
	/**
	 * Save the (possibly supplied) settings for the plugin
	 *
	 * @param null|array $settings
	 *
	 * @return bool
	 */
	public function save( $settings = null ) {
		
		$option_name = self::getOptionName();
		
		$utils = Utilities::get_instance();
		$utils->log( "Received settings to save: " . print_r( $settings, true ) );
		
		if ( empty ( $this->settings ) ) {
			$this->settings = $this->load();
		}
		
		// Merge defaults & saved settings
		if ( ! empty( $settings ) ) {
			$this->settings = shortcode_atts( $this->defaults(), $settings );
		}
		
		$utils->log( "Saving settings to {$option_name}: " . print_r( $this->settings, true ) );
		
		return update_option( $option_name, $this->settings );
	}
}
