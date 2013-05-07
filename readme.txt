=== AM Events ===
Contributors: Moisture
Tags: events, upcoming events, event list, custom post type, custom taxonomy, plugin, widget
Requires at least: 3.3.1
Tested up to: 3.5.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds an additional interface for events similar to normal posts. Also includes a widget for upcoming events.

== Description ==

The plugin adds a custom post type for events, including two taxonomies: event category and venue. 

With this plugin, the user can add new events just like normal posts with added fields for start time, end time, category and venue. You can also easily create weekly or biweekly recurring events.

There are no special functions or template tags to add events on pages or posts. It is intended to be done in the theme files using wp_query for example, which allows full control over the layout and what elements to show.

As an extra feature, the plugin also includes a widget for showing upcoming events. It uses a very simple template system for full control of the layout.

The plugin is fully translatable. The only available languages at the moment are English(default) and Finnish. Feel free to send me additional translations.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload folder `am-events` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where can I ask a question? =

Feel free to mail me at atte.moisio@attemoisio.fi

== Screenshots ==

1. Widget administration.
2. Creating an event

== Changelog ==

= 1.0.1 =
* Fixed bugs in the upcoming events -widget
* Added missing examples.php

= 1.0.0 =
* First released/stable version

== Upgrade Notice ==

= 1.0.1 =
* Fixes bugs in the upcoming events -widget

== Widget ==

Here are all the tags that can be used in the upcoming events widget template. Don't add any extra spaces inside the brackets.

 * {{title}}
 * {{event_category}}
 * {{venue}}
 * {{start_day_name}}
 * {{start_date}} 
 * {{start_time}}
 * {{end_day_name}}
 * {{end_date}}
 * {{end_time}}

== Examples ==

The plugin folder contains a file "examples.php", which contains example functions for displaying events.
