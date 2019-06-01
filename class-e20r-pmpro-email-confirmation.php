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

use E20R\PMPro\Addon\Email_Confirmation\AJAX_Handler;
use E20R\PMPro\Addon\Email_Confirmation\Shortcode;

/**
 * Class Email_Confirmation_Shortcode
 *
 * @package E20R\PMPro\Addon
 */
class Email_Confirmation_Shortcode {
	
	/**
	 * WP slug for the plugin (used in translations)
	 */
	const plugin_slug = 'e20r-pmpro-email-confirmation';
	
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
			self::$instance = new self();
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
	public static function autoLoader( $class_name ) {
		
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
		
		// Shortcode handler for the plugin
		add_shortcode( 'e20r_confirmation_form', array( Shortcode::getInstance(), 'loadShortcode' ) );
		
		// AJAX handler(s) for this shortcode
		add_action( 'wp_ajax_e20r_send_confirmation', array( AJAX_Handler::getInstance(), 'sendConfirmation' ) );
		add_action( 'wp_ajax_nopriv_e20r_send_confirmation', array( AJAX_Handler::getInstance(), 'sendConfirmation' ) );
		
		add_action( 'admin_init', array( $this, 'checkDependencies' ) );
	}
	
	public function checkDependencies() {
		
		// TODO: Add plugin check for the PMPro Email Validation plugin
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
	require 'lib/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
}

$plugin_updates = \Puc_v4_Factory::buildUpdateChecker(
	sprintf( 'https://eighty20results.com/protected-content/%s/metadata.json',Email_Confirmation_Shortcode::plugin_slug ),
	__FILE__,
	Email_Confirmation_Shortcode::plugin_slug
);
