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

namespace E20R\PMPro\Addon\Email_Confirmation\Views\Inputs;

use E20R\PMPro\Addon\Email_Confirmation_Shortcode;
use E20R\Utilities\Utilities;
use E20R\PMPro\Addon\Email_Confirmation\Settings;

/**
 * Class Select
 * @package E20R\PMPro\Addon\Email_Confirmation\Views\Inputs
 */
class Select {
	
	/**
	 * Filter handler for '' - Loads JavaScript file paths for admin_enqueue_scripts action
	 *
	 * @param string[] $paths - Path to JavaScript file to load in admin
	 *
	 * @return string[]
	 */
	public static function jsPath( $paths ) {
		
		$utils = Utilities::get_instance();
		$utils->log( "Loading Select2 Libraries & sources" );
		
		wp_enqueue_script( 'select2',
			Email_Confirmation_Shortcode::$LIBRARY_URL . "/select2/dist/js/select2.js",
			array( 'jquery' ),
			Email_Confirmation_Shortcode::VERSION
		);
		wp_enqueue_style( 'select2',
			Email_Confirmation_Shortcode::$LIBRARY_URL . "/select2/dist/css/select2.css",
			null,
			Email_Confirmation_Shortcode::VERSION
		);
		
		$paths['e20r-input-select'] = plugins_url( 'js/input-select.js', __FILE__ );
		
		return $paths;
	}
	
	/**
	 * Generate
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	public static function render( $settings ) {
		
		$utils         = Utilities::get_instance();
		$saved_value   = Settings::get( $settings['option_name'] );
		$category      = esc_attr( $settings['setting_category'] );
		$setting_name  = esc_attr( $settings['option_name'] );
		$id_label      = ! empty( $settings['id'] ) ? sprintf( 'id="%1$s"', $settings['id'] ) : null;
		$is_select2    = ( isset( $settings['type'] ) && 'select2' === $settings['type'] ) ? true : false;
		$placeholder   = ! empty( $settings['placeholder'] ) ? $settings['placeholder'] : null;
		
		if ( empty( $saved_value ) ) {
			$saved_value = $settings['default_value'];;
		}
		
		$input_classes = Settings::fixClasses( ( isset( $settings['input_css_classes'] ) ? $settings['input_css_classes'] : null ) );
		
		$multiple   = isset( $settings['multi_select'] ) && true === $settings['multi_select'] ? 'multiple="multiple"' : null;
		$options    = ! empty( $settings['select_options'] ) ? $settings['select_options'] : array();
		$name_field = ! empty( $multiple ) ?
			sprintf( 'name="%1$s[%2$s][]"', $category, $setting_name ) :
			sprintf( 'name="%1$s[%2$s]"', $category, $setting_name );
		
		if ( true === $is_select2 ) {
			$input_classes[] = 'select2';
			$input_classes[] = 'e20r-select2';
		}
		
		$html = sprintf(
			'<select %1$s %2$s %3$s %4$s %5$s style="min-width: 300px; max-width: 600px;">',
			$id_label,
			$name_field,
			$multiple,
			$placeholder,
			( ! empty( $input_classes ) ? sprintf( 'class="%1$s"', implode( ' ', $input_classes ) ) : null )
		);
		
		$html .= sprintf( '<option value="-1" %1$s></option>', ( ! is_array( $saved_value ) ? selected( $saved_value, "-1", false ) : $utils->selected( "-1", $saved_value, false ) ) );
		
		foreach ( $options as $option_key => $option_value ) {
			
			$selected = ! is_array( $saved_value ) ? selected( $saved_value, $option_key, false ) : $utils->selected( $option_key, $saved_value, false );
			
			$html .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $option_key ),
				$selected,
				esc_attr( $option_value )
			);
		}
		
		$html .= sprintf( '</select>' );
		
		echo $html;
	}
}
