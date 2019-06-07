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
use E20R\PMPro\Addon\Email_Confirmation\Inputs\Input_Setting;
use E20R\PMPro\Addon\Email_Confirmation\Models\Settings\Options;
use E20R\Utilities\Utilities;

class Settings {
	
	/**
	 * @var null|Settings
	 */
	private static $instance = null;
	
	/**
	 * Current settings
	 *
	 * @var array
	 */
	private $settings = array();
	
	/**
	 * @var null|Options
	 */
	private $model = null;
	
	/**
	 * Settings constructor.
	 */
	private function __construct() {
	}
	
	/**
	 * Update and save the specified setting name/value pair
	 *
	 * @param string|null $name
	 * @param mixed       $value
	 */
	public static function set( $name, $value ) {
		
		$model    = self::$instance->getModel();
		
		if ( empty( $model ) ) {
			$model = new Options();
		}
		
		if ( ! empty( $name ) ) {
			$model->set( $name, $value );
		}
		
		$model = null;
	}
	
	private function getSettings() {
		return $this->settings;
	}
	
	/**
	 * Get the model for this class
	 * @return null|Options
	 */
	private function getModel() {
		return $this->model;
	}
	
	/**
	 * Return the value for the specified setting
	 *
	 * @param null|string $name
	 *
	 * @return array|mixed|null
	 */
	public static function get( $name = null ) {
		
		$utils = Utilities::get_instance();
		$utils->log( "Attempting to get setting {$name}" );
		
		$settings = self::$instance->getSettings();
		
		if ( empty( $settings ) ) {
			
			$model = self::$instance->getModel();
			
			if ( empty( $model ) ) {
				$model = new Options();
			}
			
			$settings = $model->getOptions();
		}
		
		if ( empty( $name ) ) {
			return $settings;
		} else {
			
			if ( isset( $settings[ $name ] ) && ! empty( $settings[ $name ] ) ) {
				return $settings[ $name ];
			}
		}
		
		return null;
	}
	
	/**
	 * Get or create instance of this class (Settings)
	 *
	 * @return Settings
	 */
	static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	/**
	 * CSS classes for the settings should always be a list (array)
	 *
	 * @param string|array $classes
	 *
	 * @return array
	 */
	public static function fixClasses( $classes ) {
		
		if ( empty( $classes ) ) {
			return array();
		}
		
		if ( is_array( $classes ) ) {
			return $classes;
		}
		
		if ( ! is_string( $classes ) ) {
			return array();
		}
		
		$classes = explode( ' ', trim( $classes ) );
		$classes = array_map( 'trim', $classes );
		
		return $classes;
	}
	
	/**
	 * Load the Settings hooks (as needed)
	 */
	public function loadHooks() {
		
		add_action( 'admin_init', array( $this, 'register' ), 10 );
		add_action( 'admin_menu', array( $this, 'addToAdminMenu' ), 10 );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'loadInputJS' ) );
		add_filter( 'e20r-settings-option-name', array( $this, 'setOptionName' ), 10, 2 );
		
		add_filter( 'e20r-settings-enqueue-js-path', 'E20R\PMPro\Addon\Email_Confirmation\Views\Inputs\Select::jsPath', 10, 1 );
		
	}
	
	/**
	 * Set/Define the WP Option name for this plugin
	 *
	 * @param string $name
	 * @param string $plugin_slug
	 *
	 * @return string
	 */
	public function getOptionName( $name, $plugin_slug = Email_Confirmation_Shortcode::plugin_slug ) {
		
		if ( Email_Confirmation_Shortcode::plugin_slug !== $plugin_slug ) {
			return $name;
		}
		
		return 'e20r_pec_opts';
	}
	
	/**
	 * Load JavaScript from 3rd party Settings Input library/functions
	 */
	public function loadInputJS() {
		
		$js_paths = apply_filters( 'e20r-settings-enqueue-js-path', array() );
		
		foreach ( $js_paths as $handle => $javascript_file ) {
			wp_enqueue_script( $handle, $javascript_file, array( 'jquery' ), Email_Confirmation_Shortcode::VERSION );
		}
	}
	
	/**
	 * Register the Settings_API settings for the plugin
	 */
	public function register() {
		
		$utils       = Utilities::get_instance();
		$views       = Views\Settings::getInstance();
		$option_name = Options::getOptionName();
		$level_list  = array();
		
		$current_options = get_option( $option_name, array() );
		
		add_settings_section(
			'e20r-pec-settings',
			__( 'E20R PMPro Email Confirmation Settings', Email_Confirmation_Shortcode::plugin_slug ),
			array( $views, 'pageSettingsSection' ),
			'e20r-pec-settings'
		);
		
		$redirect = new Input_Setting();
		$redirect->set( 'option_name', 'redirect_if_not_verified' );
		$redirect->set( 'setting_category', $option_name );
		$redirect->set( 'id', 'redirect_if_not_verified' );
		$redirect->set( 'type', 'checkbox' );
		$redirect->set( 'default_value', 1 );
		$redirect->set( 'callback', '\E20R\PMPro\Addon\Email_Confirmation\Views\Inputs\Checkbox::render' );
		
		add_settings_field(
			'oeis-redirect_if_not_verified',
			__( 'Redirect on login', Email_Confirmation_Shortcode::plugin_slug ),
			$redirect->getCallback(),
			'e20r-pec-settings',
			'e20r-pec-settings',
			$redirect->getSettings()
		);
		
		$all_pages = $this->getAllPages();
		
		$redirect_target = new Input_Setting();
		$redirect_target->set( 'option_name', 'pec_redirect_target_page' );
		$redirect_target->set( 'setting_category', $option_name );
		$redirect_target->set( 'id', 'pec_redirect_target_page' );
		$redirect_target->set( 'multi_select', false );
		$redirect_target->set( 'type', 'select2' );
		$redirect_target->set( 'default_value', '' );
		$redirect_target->set( 'select_options', $all_pages );
		$redirect_target->set( 'callback', '\E20R\PMPro\Addon\Email_Confirmation\Views\Inputs\Select::render' );
		
		add_settings_field(
			'oeis-pec_redirect_target_page',
			__( 'Redirect to page', Email_Confirmation_Shortcode::plugin_slug ),
			$redirect_target->getCallback(),
			'e20r-pec-settings',
			'e20r-pec-settings',
			$redirect_target->getSettings()
		);
		
		// Configure settings & how to sanitize the input from the user
		register_setting(
			'e20r_pec_group',
			$option_name,
			array( Sanitize::getInstance(), 'sanitizeSettings' )
		);
	}
	
	/**
	 * Add the Plugin Settings page
	 */
	public function addToAdminMenu() {
		
		add_menu_page(
			__( 'PMPro Email Confirmation', Email_Confirmation_Shortcode::plugin_slug ),
			__( 'PMP Email Conf', Email_Confirmation_Shortcode::plugin_slug ),
			'manage_options',
			'e20r-pec-settings',
			array( Views\Settings::getInstance(), 'addOptionsPage' ),
			null
		);
	}
	
	/**
	 * Return list of all published or future WordPress pages on this site
	 *
	 * @return array
	 */
	public function getAllPages() {
		
		$page_args = array(
			'post_type' => 'page',
			'post_status' => array( 'publish', 'future' ),
			'posts_per_page' => -1,
			'order' => 'ASC',
			'order_by' => 'title',
		);
		
		$page_query = new \WP_Query( $page_args );
		$page_list = array();
		
		/**
		 * @var \WP_Post $page
		 */
		foreach( $page_query->get_posts() as $page ) {
			$page_list[ $page->ID ] = $page->post_title;
		}
		
		return $page_list;
	}
	
	/**
	 * Destructor for the Settings class
	 */
	public function __destruct() {
		
		if ( ! empty( $this->settings ) ) {
			$this->model->save( $this->settings );
		}
		
	}
}
