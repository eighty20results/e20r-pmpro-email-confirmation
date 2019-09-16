=== E20R Shortcode for PMPro Email Confirmations ===
Contributors: eighty20results
Tags: eighty20results, pmpro, paid memberships pro, members, memberships, confirmation email, resend confirmation email, shortcode
Requires at least: 5.0
Tested up to: 5.3
Stable tag: 2.0

== Description ==

This plugin provides the [e20r_confirmation_form] short code. This short code generates a form to let members resend their PMPro Email Confirmation message. The message is sent, either to the specified email address, or to the user who is logged in when the "Submit"/"Send Now" button is clicked.

The short code supports the following attributes:

1. header -- The header text to display on the form (string)
1. button_text -- The text to use on the "Send Now" button (string)
1. confirmation_msg: The message to display when the email is sent successfully (string).
1. not_logged_in_msg: The message to show if a user attempts to access the page where this shortcode is used, but that user isn't logged in to the system yet
1. confirmation_page_slug:  The page slug for a page containing a confirmation message + help text, etc. If this attribute is defined, the value will take precedence over the 'confirmation_msg' text. (string: a valid page slug)
1. show_full_form: Should we display input fields for the form, or use default values (numeric: 0 = no, 1 = yes). The '0' value is typically only makes sense if the short code is being used on a page where the 'Redirect on login' settings (see below) point to.

Using the "PMP Email Conf" settings page in the /wp-admin/ backend, you configure whether to enable redirecting an unverified member to a specific page (defined in the same location). That target page may contain the [e20r_confirmation_form] short code, or not.

This settings page will also ask you to select the target page of the redirect operation. The target page setting will cause a login operation to send an unverified member to this page. The [e20r_confirmation_form] short code does not have to be present on the target page.

== Installation ==

1. Upload the `e20r-pmpro-email-confirmation` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Known/Possible Issues ==

== Changelog ==

== 2.0 ==

* ENHANCEMENT: Add support for a Support and Updates license
* ENHANCEMENT: Refactored for v3.0 of the E20R Licensing utilities module
* ENHANCEMENT: Add HTML message when the Support and Updates license is inactive
* ENHANCEMENT: Added missing code comments
* ENHANCEMENT: Remove some of the composer installed dependencies for testing from GIT
* ENHANCEMENT: Using composer to install select2 and new licensing client libraries
* ENHANCEMENT: Updated the plugin description in the composer.json file
* ENHANCEMENT: Updated Plugin name and description
* ENHANCEMENT: Updated version number (v2.0)
* BUG FIX: Incorrect handler function for 'e20r-settings-option-name' filter
* BUG FIX: Error handler when no option name is specified
* BUG FIX: Out-Of-Memory error when running on sites with 1000s of pages
* BUG FIX: Error handling in JavaScript file for the redirect action(s)
* BUG FIX: File clean-up
* BUG FIX: Incorrect path to select2 libraries for settings page

== 1.3 ==

* ENHANCEMENT: Added cursor status update during AJAX call(s)

== 1.2 ==

* BUG FIX: Activation error on new server
* BUG FIX: Catch attempts to self-redirect
* BUG FIX: Better handling of plugin-update functionality

== 1.1 ==

* ENHANCEMENT: Added support for settings page and redirect on login
* ENHANCEMENT: Add support for the 'show_full_form' attribute to show/hide the input fields
* ENHANCEMENT: Support redirect to a configured page when user isn't a validated member
* ENHANCEMENT: Added and reverted support for bypass link in short code output
* BUG FIX: Didn't include check for required plugin/add-on options
* BUG FIX: Incorrect path to plugin files in build scripts
* BUG FIX: Renamed full_form attribute to 'show_full_form'. Accepts 0 or 1 as values
* BUG FIX: Didn't redirect to the specified page on login
* BUG FIX: Clean up check of validation key
* BUG FIX: Improved documentation of plugin in description
* BUG FIX: Clean up the WordPress plugin header with description, etc

== 1.0 ==

* Initial release (v1.0)

