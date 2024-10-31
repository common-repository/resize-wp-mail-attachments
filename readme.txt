=== Resize WP_Mail Attachments ===
Contributors: Mill Hill Automation
Requires at least: 5
Tested up to: 6.0
Stable tag: 1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Contributors: patabugen
Requires PHP: 5.3

Attempts to auto-resize the attachments of emails sent via wp_mail() in order to fit them within a predefined limit.

== Description ==

All email providers have a maximum email size. Postmark - for example - have a hard limit of 10mb.

For usability, it's best not to ask your visitors to do their own image resizing. This plugin will automatically reduce any images (specifically anything which can be edited with the WP_Image_Editor) a little at a time to try and fit the limit.

== Installation ==

1. Upload the plugin directory `resize-wp-mail-attachments` to the `/wp-content/plugins/` directory or install it directly from the Wordpress plugin directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. The plugin automatically starts working - there is no admin or settings screens. See Frequently Asked Questions if you'd like to change any settings.

== Frequently Asked Questions ==

= How do I change the size limits =

You can use these filters to customise settings, here the filter names are listed with their defaults.
resize_wp_mail_attachments_max_total_size = 10 (megabytes)
resize_wp_mail_attachments_fit_max_attempts = 5
resize_wp_mail_attachments_fit_reduction_amount = 0.98

For exampple, to set the email size to 25mb add code like this to your functions.php:
<code>
    add_filter('resize_wp_mail_attachments_max_total_size', function(){ return 25; });
</code>

== Changelog ==

= 1.2 =
 * Tested on WordPress 6

= 1.1 =
 * Fixed: Path does not include directory separator

= 1 =
 * Initial release
