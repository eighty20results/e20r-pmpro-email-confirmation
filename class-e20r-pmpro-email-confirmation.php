<?php
/*
Plugin Name: E20R - Email Confirmation Reminder Shortcode for PMPro
Plugin URI: http://eighty20results.com/paid-memberships-pro/e20r-pmpro-email-confirmation
Description: Add shortcode and redirect functionality to let the user self-service and re-send the PMPro email confirmation message when they log in and haven't yet confirmed their email address.
Version: 2.2
Author: Thomas at Eighty/20 Results by Wicked Strong Chicks, LLC <thomas@eighty20results.com>
Author URI: https://eighty20results.com/thomas-sjolshagen/
License: GPL2
 *
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

use E20R\PMPro\Addon\Email_Confirmation\AJAX_Handler;
use E20R\PMPro\Addon\Email_Confirmation\PMPEC_License;
use E20R\PMPro\Addon\Email_Confirmation\Redirect_Handler;
use E20R\PMPro\Addon\Email_Confirmation\Settings;
use E20R\PMPro\Addon\Email_Confirmation\Shortcode;
use E20R\Utilities\Licensing\Licensing;
use E20R\Utilities\Utilities;

if ( ! defined( 'E20R_LICENSE_SERVER_URL' ) ) {
	define( 'E20R_LICENSE_SERVER_URL', 'https://eighty20results.com' );
}

/**
 * Class Email_Confirmation_Shortcode
 *
 * @package E20R\PMPro\Addon
 */
class Email_Confirmation_Shortcode {
	
	/**
	 * Plugin Version
	 */
	const VERSION = '2.2';
	/**
	 * WP slug for the plugin (used in translations)
	 */
	const plugin_slug = 'e20r-pmpro-email-confirmation';
	/**
	 * File system path to the plugin
	 *
	 * @var string
	 */
	public static $LIBRARY_URL = '';
	/**
	 * URL to the plugin
	 *
	 * @var string
	 */
	public static $LIBRARY_PATH = '';
	
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
	 * Return or instantiate and return the Email_Confirmation_Shortcode class
	 *
	 * @return Email_Confirmation_Shortcode|null
	 */
	public static function getInstance() {
		
		if ( is_null( self::$instance ) ) {
			self::$instance     = new self();
			self::$LIBRARY_URL  = plugins_url( null, __FILE__ );
			self::$LIBRARY_PATH = plugin_dir_path( __FILE__ );
		}
		
		return self::$instance;
	}
	
	/**
	 * Class auto-loader for the E20R PMPro Email Confirmation Shortcode plugin
	 *
	 * @param string $class_name Name of the class to auto-load
	 *
	 * @since  1.0
	 * @access public static
	 *
	 */
	public function autoLoader( $class_name ) {
		
		$pattern       = preg_quote( 'e20r\\utilities' );
		$has_utilities = ( 1 === preg_match( "/{$pattern}/i", $class_name ) );
		
		if ( false === stripos( $class_name, 'e20r' ) ) {
			return;
		}
		
		$parts = explode( '\\', $class_name );
		//$c_name    = strtolower( preg_replace( '/_/', '-', $parts[ ( count( $parts ) - 1 ) ] ) );
		$base_path = plugin_dir_path( __FILE__ ) . 'inc/';
		
		if ( $has_utilities ) {
			$c_name   = preg_replace( '/_/', '-', $parts[ ( count( $parts ) - 1 ) ] );
			$filename = strtolower( "class.{$c_name}.php" );
		} else {
			$c_name   = $parts[ ( count( $parts ) - 1 ) ];
			$filename = "class-{$c_name}.php";
		}
		
		$iterator = new \RecursiveDirectoryIterator( $base_path, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveIteratorIterator::SELF_FIRST | \RecursiveIteratorIterator::CATCH_GET_CHILD | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS );
		
		/**
		 * Locate class files, recursively
		 */
		$filter = new \RecursiveCallbackFilterIterator( $iterator, function ( $current, $key, $iterator ) use ( $filename ) {
			
			$file_name = $current->getFilename();
			
			// Skip hidden files and directories.
			if ( $file_name[0] == '.' || $file_name == '..' ) {
				return false;
			}
			
			if ( $current->isDir() ) {
				// Only recurse into intended subdirectories.
				return $file_name() === $filename;
			} else {
				// Only consume files of interest.
				return strpos( $file_name, $filename ) === 0;
			}
		} );
		
		foreach ( new \ RecursiveIteratorIterator( $iterator ) as $f_filename => $f_file ) {
			
			$class_path = $f_file->getPath() . "/" . $f_file->getFilename();
			
			if ( $f_file->isFile() && false !== strpos( $class_path, $filename ) ) {
				require_once( $class_path );
			}
		}
	}
	
	/**
	 * Load all filter/action/shortcode handlers for the plugin
	 */
	public function loadHooks() {
		
		add_action( 'plugins_loaded', array( Licensing::get_instance(), 'load_hooks' ), 11 );
		add_action( 'plugins_loaded', array( PMPEC_License::getInstance(), 'load_hooks' ), 11 );
		add_action( 'plugins_loaded', array( Settings::getInstance(), 'loadHooks' ), 11 );
		add_action( 'plugins_loaded', array( Redirect_Handler::getInstance(), 'loadHooks' ), 11 );
		
		// Shortcode handler for the plugin
		add_shortcode( 'e20r_confirmation_form', array( Shortcode::getInstance(), 'loadShortcode' ) );
		
		// AJAX handler(s) for this shortcode
		add_action( 'wp_ajax_e20r_send_confirmation', array( AJAX_Handler::getInstance(), 'sendConfirmation' ) );
		add_action( 'wp_ajax_nopriv_e20r_send_confirmation', array( AJAX_Handler::getInstance(), 'sendConfirmation' ) );
		
		// Check to make sure all required plugins are loaded
		add_action( 'admin_init', array( $this, 'checkDependencies' ) );
		
		// Load our own post/page resources
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue' ) );
	}
	
	/**
	 * Load the JS and CSS whenever the 'e20r_confirmation_form' or block is being loaded
	 */
	public function enqueue() {
		
		// Processing an ajax call
		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
			return;
		}
		
		global $post;
		
		// No content in post
		if ( empty( $post->post_content ) ) {
			return;
		}
		
		if ( false === Licensing::is_licensed( 'e20r_pmpec' ) ) {
			return;
		}
		
		// Check whether the shortcode is present on this post
		if ( false === has_shortcode( $post->post_content, 'e20r_confirmation_form' ) &&
		     ( function_exists( 'has_block' ) && false === has_block( 'e20r-confirmation-form', $post ) )
		) {
			return;
		}
		
		// Load CSS and JS files
		wp_enqueue_style( 'e20r-pmpro-email-confirmation', plugins_url( 'css/e20r-pmpro-email-confirmation.css', __FILE__ ), null, '1.0' );
		
		wp_register_script( 'e20r-pmpro-email-confirmation', plugins_url( 'js/e20r-pmpro-email-confirmation.js', __FILE__ ), array( 'jquery' ), '1.0' );
		
		wp_localize_script( 'e20r-pmpro-email-confirmation', 'e20r_pec', array(
				'timeout' => apply_filters( 'e20r-pec-ajax-timeout', 15 ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
		
		wp_enqueue_script( 'e20r-pmpro-email-confirmation' );
	}
	
	/**
	 * Check that the required add-ons/plugins are present. Show warning message if not
	 */
	public function checkDependencies() {
		
		$has_PMPro     = function_exists( 'pmpro_getAllLevels' );
		$has_EmailConf = function_exists( 'pmproec_pmpro_membership_level_after_other_settings' );
		$utils         = Utilities::get_instance();
		
		if ( is_admin() ) {
			
			if ( false === $has_PMPro ) {
				$utils->add_message( sprintf(
					__( "Error: The <a href=\"%s\" target=\"_blank\">Paid Memberships Pro</a> plugin must be installed and active!", Email_Confirmation_Shortcode::plugin_slug ),
					'https://wordpress.org/plugins/paid-memberships-pro/'
				), 'error', 'backend' );
			}
			
			if ( false === $has_EmailConf ) {
				$utils->add_message( sprintf(
					__( "Error: The <a href=\"%s\" target=\"_blank\">Email Confirmation</a> add-on for PMPro must be installed and active!", Email_Confirmation_Shortcode::plugin_slug ),
					'https://www.paidmembershipspro.com/add-ons/email-confirmation-add-on/'
				), 'error', 'backend' );
			}
		}
	}
	
	/**
	 * Hide the clone() magic method
	 *
	 * @access private
	 * @return false
	 */
	private function __clone() {
		return false;
	}
}

try {
	spl_autoload_register( array( Email_Confirmation_Shortcode::getInstance(), 'autoLoader' ) );
} catch ( \Exception $exception ) {
	error_log( "Error: Unable to register autoloader for E20R PMPro Email Confirmation shortcode: " . $exception->getMessage() );
	exit();
}

add_action( 'plugins_loaded', array( Email_Confirmation_Shortcode::getInstance(), 'loadHooks' ) );

/**
 * One-click update handler & checker
 */
if ( ! class_exists( '\\Puc_v4_Factory' ) ) {
	
	$local_path  = plugin_dir_path( __FILE__ ) . 'lib/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
	$plugin_path = plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';
	
	if ( file_exists( $plugin_path ) ) {
		require $plugin_path;
	} else if ( file_exists( $local_path ) ) {
		require $local_path;
	}
}

$plugin_updates = \Puc_v4_Factory::buildUpdateChecker(
	sprintf( 'https://eighty20results.com/protected-content/%s/metadata.json', Email_Confirmation_Shortcode::plugin_slug ),
	__FILE__,
	Email_Confirmation_Shortcode::plugin_slug
);
