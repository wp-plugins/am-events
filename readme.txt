=== AM Events ===
Contributors: Moisture
Tags: events, upcoming events, event list, custom post type, custom taxonomy, plugin, widget
Requires at least: 3.3.1
Tested up to: 3.5.1
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds an additional interface for events similar to normal posts. Also includes a widget for upcoming events.

== Description ==

The plugin adds a custom post type for events, including two taxonomies: event category and venue. 

With this plugin, the user can add new events just like normal posts with added fields for start time, end time, category and venue. You can also easily create weekly or biweekly recurring events.

There are no special functions or template tags to add events on pages or posts. It is intended to be done in the theme files using WP_Query for example, which allows full control over the layout and what elements to show. See 'Other Notes' for a simple tutorial.

As an extra feature, the plugin also includes a widget for showing upcoming events. It uses a very simple template system for full control of the layout.

The plugin is fully translatable. The only available languages at the moment are English(default) and Finnish. The download includes a pot-file for additional translations.

If you think something critical is missing, feel free to send me a request.



== Installation ==

This section describes how to install the plugin and get it working.

1. Upload folder `am-events` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where can I ask a question? =

Feel free to mail me at atte.moisio@attemoisio.fi

= Can you give me an example of using WP_Query? =

See 'Other Notes' for a simple tutorial.

== Screenshots ==

1. Widget administration.
2. Creating an event

== Changelog ==

= 1.2.1 =
* Fixed localization typos
* Added simple WP_Query tutorial in 'Other Notes'

= 1.2.0 =
* Added support for PHP 5.2 (previously needed 5.3)
* Fixed multiple bugs

= 1.1.0 =
* Added localization to date format

= 1.0.1 =
* Fixed bugs in the upcoming events -widget
* Added missing examples.php

= 1.0.0 =
* First released/stable version

== Upgrade Notice ==

= 1.2.1 =
* Fixes localization typos
* Adds simple WP_Query tutorial to readme.txt

= 1.2.0 =
* Adds support for php 5.2 (previously needed 5.3)
* Fixes bugs.

= 1.1.0 =
* Adds localization support for date format.

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

== Custom post type, taxonomies and meta data ==

* The custom post is named 'am_event'
* Taxonomies are named 'am_venues' and 'am_event_categories'
* Each event post has metadata named 'am_startdate' and 'am_enddate', that are formatted like 'yyyy-mm-dd hh:mm')

== Creating a WP_Query ==

The bare minimum arguments for getting all published events are these:

    $args = array(
            'post_type' => 'am_event',
            'post_status' => 'publish',
        );
		
	$the_query = new WP_Query($args);
		
So suppose I wanted to limit the query to a single event category named 'other' and venue named 'mcdonalds'. I would then add a tax_query argument:

	$args = array(
			// The first two are the same as above
            'post_type' => 'am_event',
            'post_status' => 'publish',
			// Add a tax_query arqument to limit events to a single category and venue
			'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'am_venues',
                        'field' => 'name',
                        'terms' => 'mcdonalds',
                    ),
                    array(
                        'taxonomy' => 'am_event_categories',
                        'field' => 'name',
                        'terms' => 'other'
                    ),
            ),
        );
		
	$the_query = new WP_Query($args);
	
If you want the events ordered by start date, add the following to $args:

    'orderby' => 'meta_value',
    'meta_key' => 'am_startdate',
    'order' => 'ASC',

If you need to display only upcoming events, add the following to $args:

    'meta_query' => array(
            array(
                'key' => 'am_enddate',
                'value' => date('Y-m-d H:i:s', time()),
                'compare' => ">"
                ),
            ),
		),
		
= The Loop =

When you've got all the arguments in place, it's time to construct the loop. Example follows:
	
	// ... CREATE $args HERE ...  //
	
    $the_query = new WP_Query($args);

    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            $the_query->the_post();

            $postId = $post->ID;

            // get start and end date
            $startDate = get_post_meta($postId, 'am_startdate', true);
            $endDate = get_post_meta($postId, 'am_enddate', true);

            // get venues and categories in an array
            $venues = wp_get_post_terms($postId, 'am_venues');
            $eventCategories = wp_get_post_terms($postId, 'am_event_categories');

            // All the other wordpress functions used for normal posts like
            // the_title() and the_content() work just like with normal posts.

            // ...  DISPLAY POST CONTENT HERE ... //

        }
    }

Note that if you want the date formatted other than the default, you will need to use additional php:

    // Format the date as 00.00.0000 00:00
    $newDate = date('d.m.Y H:i', $strtotime($startDate)); 

= Other examples =
	
The plugin folder also contains a file "examples.php", which contains an example function for displaying upcoming events in a paged table.
