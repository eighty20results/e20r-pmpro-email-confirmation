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

namespace E20R\Utilities\Licensing;


class WC_License_Client {
	
	/**
	 * Settings received from license server
	 *
	 * @var array
	 */
	protected $license_data = array();
	
	/**
	 * URL to the license server (eighty20results.com)
	 * @var string
	 */
	protected $license_server_url = '';
	
	/**
	 * The license action to execute
	 *
	 * @var string
	 */
	protected $action = 'license_key_validate';
	
	/**
	 * The key for the store (saved)
	 *
	 * @var null|string
	 */
	protected $store_code = null;
	
	/**
	 * Product SKU
	 *
	 * @var null|string
	 */
	protected $sku = null;
	
	/**
	 * License key for the user/instance
	 * @var null|string
	 */
	protected $license_key = null;
	
	/**
	 * DNS Domain the license is registered against
	 *
	 * @var string|null
	 */
	protected $domain = null;
	
	/**
	 * Activation ID for the license/domain
	 *
	 * @var null|string
	 */
	protected $activation_id = null;
	
	/**
	 * WC_License_Client constructor.
	 */
	public function __construct() {
		
		$this->license_server_url = apply_filters( 'e20r-license-remote-server-url', 'https://eighty20results.com/wp-admin/admin-ajax.php' );
		
		$this->domain = get_clean_basedomain();
		$this->license_data = array(
			'expire' => null,
			'activation_id' => null,
			'expire_date' => '',
			'timezone' => 'UTC',
			'the_key' => '',
			'url' => null,
			'has_expired' => false,
			'status' => '',
			'allow_offline' => true,
			'offline_interval' => 'days',
			'offline_value' => 1,
		);
	}
	
	public static function is_licensed( $product_stub = null, $force = false ) {
	
	}
}
