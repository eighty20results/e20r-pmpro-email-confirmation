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

use E20R\Utilities\Utilities;
use E20R\PMPro\Addon\Email_Confirmation\Models\Settings\Options;

class Sanitize {
	
	/**
	 * Class instance
	 *
	 * @var null|Sanitize
	 */
	private static $instance = null;
	
	/**
	 * Sanitize constructor.
	 */
	private function __construct() {
	}
	
	/**
	 * Get or create instance of this class (Settings)
	 *
	 * @return Sanitize
	 */
	static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	/**
	 * Sanitize the Directory settings on save
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function sanitizeSettings( $settings ) {
		
		$model       = new Options();
		$utils       = Utilities::get_instance();
		$option_name = $model::getOptionName();
		
		$utils->log( "Settings to sanitize for {$option_name}: " . print_r( $settings, true ) );
		
		foreach ( $settings as $setting_key => $value ) {
			$settings[ $setting_key ] = $utils->_sanitize( $value );
		}
		
		return $settings;
	}
}
