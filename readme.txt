=== My Events Calendar ===
Contributors: makingtheimpact
Tags: events, calendar, event management, scheduling, WordPress
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 7.0
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
[GitHub Updater](https://github.com/afragen/github-updater) plugin for automatic updates

== Description ==
My Events Calendar is a simple and flexible calendar and event management system for WordPress. It allows users to create, manage, and display events easily. With customizable features, you can tailor the calendar to fit your needs.

== Features ==
* Create and manage events with custom post types.
* Display events in a calendar view.
* Support for recurring events.
* Customizable event categories with color coding.
* Shortcodes for easy integration into posts and pages.
* Responsive design for mobile and desktop.
* Integration with Google Calendar and Apple Calendar.
* Automatic updates

== Installation ==
1. Upload the `my-events-calendar` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the settings under the 'Events Calendar' menu in the WordPress admin.

== Usage ==
* Use the shortcode `[my_event_list]` to display a list of events.
* Use the shortcode `[my_events_calendar]` to display the calendar on any page or post.
* Customize event categories and settings from the plugin settings page.

== Frequently Asked Questions (FAQ) ==
=== How do I create an event? ===
Navigate to the 'Events' menu in the WordPress admin and click 'Add New' to create an event.

=== Can I customize the appearance of the calendar? ===
Yes, you can customize the styles through the plugin settings and your theme's CSS.

=== Slug Conflicts ===

The plugin uses the following URL slugs by default:
- /events/ - For the events listing
- /event-location/ - For event locations
- /event-category/ - For event categories

If these slugs conflict with existing pages on your site, you have two options:

1. Change the slug of your existing page(s)
2. Customize the plugin's slugs in Settings > My Events Calendar > URL Slugs

Note: After changing any slugs, you may need to refresh your permalinks by going to Settings > Permalinks and clicking "Save Changes".

== Changelog ==

=== 1.0.3 ===
* Updated slug handling to prevent conflicts.
* Added template files for single and archive event locations.
* Changed the GitHub updater to use proxy server.

=== 1.0.2 ===
* Fixed bugs and made improvements.

=== 1.0.1 ===
* Added GitHub Updater

=== 1.0 ===
* Initial release of My Events Calendar.

== Screenshots ==
1. Monthly calendar view.
2. Event list view.

== Support ==
For support, please visit the [plugin support forum](https://wordpress.org/support/plugin/my-events-calendar).

== License ==
This plugin is licensed under the GPL2 license.