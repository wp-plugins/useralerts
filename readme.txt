=== UserAlerts ===
Contributors: userpress
Requires at least: 3.7
Tested up to: 4.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow your web visitors to receive a periodic e-mail digest when there's new content matching their query.


== Description ==

Many high-end web sites offer e-mail alerts so visitors can be notified when there's new content matching their query.

This is especially useful for dating sites, job boards, online stores, forums, and directories.

But there's hasn't been a simple, customizable alert system for WordPress... until now.

UserAlerts can be used with virtually any theme or plugin.

The plugin generally requires no customization. Just install and activate the plugin and you'll be read to go.

(However, your website might be designed in a way that makes it incompatible with UserAlerts. If that's the case, let us know and we'll try to find a solution for you.)


PRO Features

- Unlimited e-mail alerts for archives (authors, taxonomies, custom post types) and custom queries (free version: 100 alerts limit).

- Allow registered users to list and manage their lists. Simply create a page and insert a shortcode.


Visit our web site to learn more: http://useralerts.org/

== Installation ==

1. Upload UserAlerts via your wp-admin plugins manager.

2. Activate the plugin.

3. Visit wp-admin/appearance/widgets/

4. Add the UserAlerts widget your sidebar/widget area.

5. Create a Page and add the shortcode [wp_digest_listing]  (For loggedin users this will allow them to list and delete their alerts.) 

== Troubleshooting ==

1. You can enable UserAlerts' debug mode, by enabling wp_debug mode in your wp-config.php file. UserAlerts debug mode displays the wp_query array above your loop. While also adding a "1 second interval" option to the subscription widget.