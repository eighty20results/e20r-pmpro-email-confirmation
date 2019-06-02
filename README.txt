=== E20R Shortcode for PMPro Email Confirmations ===
Contributors: eighty20results
Tags: eighty20results, pmpro, paid memberships pro, members, memberships, confirmation email, resend confirmation email, shortcode
Requires at least: 4.9
Tested up to: 5.2.1
Stable tag: 1.0

== Description ==

This plugin provides the `[e20r_confirmation_form]` shortcode. The shortcode can be used to let users trigger a re-transmission of their PMPro Email Confirmation message to the specified email address or to the user who is logged in when they click the "Submit" button 

The following shortcode attributes are supported:

1. header: The header text to display on the form
1. button_text: The text to use on the "Submit" button
1. confirmation_msg: The message to display when the email is sent successfully
1. not_logged_in_msg: The message to show if a user attempts to access the page where this shortcode is used, but that user isn't logged in to the system.
1. confirmation_page_slug: The page slug for a page containing a confirmation message + help text, etc. If this attribute is defined, the value will take precedence over the 'confirmation_msg' text.

== Installation ==

1. Upload the `e20r-pmpro-email-confirmation` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Known/Possible Issues ==

== Changelog ==

== 1.0 ==

* Initial release (v1.0)

