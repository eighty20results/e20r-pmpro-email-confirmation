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

class TextArea {
	
	/**
	 * Generate a TextArea HTML element
	 *
	 * @param $settings
	 *
	 * @return string
	 */
	public static function render( $settings ) {
		
		$value        = Settings::get( $settings['option_name'] );
		$category     = esc_attr( $settings['setting_category'] );
		$setting_name = esc_attr( $settings['option_name'] );
		$id_label     = ! empty( $settings['id'] ) ? sprintf( 'id="%1$s"', $settings['id'] ) : null;
		$placeholder  = ! empty( $settings['placeholder'] ) ? $settings['placeholder'] : null;
		
		$html = sprintf(
			'<textarea name="%1$s[%2$s]" rows="%3$d" cols="%4$d" %5$s >%6$s</textarea>',
			$category,
			$setting_name,
			$id_label,
			$settings['textarea_cols'],
			$settings['textarea_rows'],
			( ! empty( $placeholder ) ? sprintf( 'placeholder="%1$s"', esc_attr( $settings['placeholder'] ) ) : null ),
			( ! empty( $value ) ? wpautop( wp_unslash( trim( $value ) ) ) : null )
		);
		
		echo $html;
	}
}
