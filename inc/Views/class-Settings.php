<?php
/**
 * Copyright (c) 2019 - Eighty / 20 Results by Wicked Strong Chicks.
 * ALL RIGHTS RESERVED
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace E20R\PMPro\Addon\Email_Confirmation\Views;

use E20R\PMPro\Addon\Email_Confirmation_Shortcode;
use E20R\Utilities\Utilities;


/**
 * Class Settings
 * @package E20R\PMPro\Addon\Email_Confirmation\Views
 *
 */
class Settings {
	
	/**
	 * The post/page slug where we'd like to redirect new members to when they need to update
	 */
	const DATA_COLLECTION_SLUG = 'member-info';
	
	/**
	 * @var null|Settings
	 */
	private static $instance = null;
	
	/**
	 * Settings constructor.
	 */
	private function __construct() {
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
	 * Description for the Application settings on the options page
	 */
	public function pageSettingsSection() { ?>
        <p><?php _e( 'Configure whether to redirect a user on login to recover their currently un-validated Email Confirmation.', Email_Confirmation_Shortcode::plugin_slug ); ?></p>
		<?php
		
	}
	
	/**
	 * Add the settings page framework
	 */
	public function addOptionsPage() {
		$utils = Utilities::get_instance();
		?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h1><?php _e( 'Settings for PMPro Email Confirmation', Email_Confirmation_Shortcode::plugin_slug ); ?></h1>
			<?php settings_errors();
			
			$active_tab = $utils->get_variable( 'tab', 'committee' ); ?>
            <!-- <h2 class="nav-tab-wrapper">
                <a href="?page=oeis-settings&tab=committee"
                   class="nav-tab <?php echo $active_tab == 'committee' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Committee Settings', Email_Confirmation_Shortcode::plugin_slug ); ?></a>
                <a href="?page=oeis-settings&tab=application"
                   class="nav-tab <?php echo $active_tab == 'application' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Application Settings', Email_Confirmation_Shortcode::plugin_slug ); ?></a>
                <a href="?page=oeis-settings&tab=templates"
                   class="nav-tab <?php echo $active_tab == 'templates' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Template Settings', Email_Confirmation_Shortcode::plugin_slug ); ?></a>
            </h2>
            -->
            <form method="post" action="options.php">
				<?php
				
				//add_settings_section callback is displayed here. For every new section we need to call settings_fields.
				settings_fields( "e20r_pec_group" );
				
				// all the add_settings_field callbacks is displayed here
				do_settings_sections( "e20r-pec-settings" );
				
				// Add the submit button to serialize the options
				submit_button(); ?>
            </form>
        </div>
		<?php
	}
}
