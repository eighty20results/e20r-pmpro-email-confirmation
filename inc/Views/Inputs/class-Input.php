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

use E20R\PMPro\Addon\Email_Confirmation\Settings;

class Input {
	
	/**
	 * Render a standard <input> HTML element
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	public static function render( $settings ) {
		
		$value         = Settings::get( $settings['option_name'] );
		$category      = esc_attr( $settings['setting_category'] );
		$setting_name  = esc_attr( $settings['option_name'] );
		$id_label      = ! empty( $settings['id'] ) ? sprintf( 'id="%1$s"', $settings['id'] ) : null;
		$type          = empty( $settings['type'] ) ? 'text' : $settings['type'];
		$placeholder   = ! empty( $settings['placeholder'] ) ? $settings['placeholder'] : null;
		$input_classes = Settings::fixClasses( ( isset( $settings['input_css_classes'] ) ? $settings['input_css_classes'] : null ) );
		$html          = sprintf(
			'<input type="%1$s" %2$s name="%3$s[%4$s]" value="%5$s" %6$s %7$s style="min-width: 300px; max-width: 600px;"/>',
			$type,
			$id_label,
			$category,
			$setting_name,
			esc_attr( $value ),
			( ! empty( $input_classes ) ? sprintf( 'class="%1$s"', implode( ' ', $input_classes ) ) : null ),
			$placeholder
		);
		
		echo $html;
	}
}
