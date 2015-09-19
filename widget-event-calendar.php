<?php

/******************************************************************************
 * =COPYRIGHT
 *****************************************************************************/

/*  Copyright 2015  Atte Moisio  (email : atte.moisio@attemoisio.fi)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class AM_Event_Calendar_Widget extends WP_Widget {
   
    /**
    * Register widget with WordPress.
    */
    public function __construct() {
        parent::__construct(
				'am_event_calendar', 
				'AM Event Calendar', 
				array('description' => __( 'Display event calendar', 'am-events' )), 
				array('width' => 400));
    }

    /**
    * Front-end display of widget.
    *
    * @see WP_Widget::widget()
    *
    * @param array $args     Widget arguments.
    * @param array $instance Saved values from database.
    */
    public function widget( $args, $instance ) {
        extract( $args );
        
        global $post;
        
        /* User-selected settings. */
        $title = apply_filters('widget_title', $instance['title'] );
        $venue = $instance['venue'];
        $category = $instance['category'];
   
        /* Before widget (defined by themes). */
        echo $before_widget;
                 
        /* Title of widget (before and after defined by themes). */
        if ( ! empty( $title ) )
                echo $before_title . $title . $after_title;
        
        am_get_calendar(true,true,null,null);
        
        /* After widget (defined by themes). */
        echo $after_widget;
    }

   /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
                
        $defaults = array( 
            'title' => __('Upcoming Events', 'am-events'),
            'category' => 'all', 
            'venue' => 'all',
            );
        $instance = wp_parse_args( (array) $instance, $defaults );


        $title      = $instance[ 'title' ];
        $category   = $instance[ 'category' ];
        $venue      = $instance[ 'venue' ];
 
        $args = array( 'hide_empty' => false );
        
        $categories = get_terms('am_event_categories', $args);
        $venues = get_terms('am_venues', $args);

        ?>
            <!-- Title -->
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'am-events')?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
            </p>
           
            
            <!-- Select event category -->
            <label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e('Select Event category:', 'am-events')?></label><br />
            <select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
                <option value="all" <?php if ( $category === "all" ){ echo 'selected="selected"'; }?>><?php _e('All', 'am-events') ?></option>
                <?php foreach ($categories as $c) { 
                    $categorySlug = $c -> slug;
					$categoryName = $c -> name;					?>
                    <option value="<?php echo $categorySlug ?>" <?php if ( $category === $categorySlug ){ echo 'selected="selected"'; }?>><?php echo $categoryName ?></option>
                <?php } ?>
            </select>
            <br />
            <br />
            
            <!-- Select event venue -->
            <label for="<?php echo $this->get_field_id( 'venue' ); ?>"><?php _e('Select Venue:', 'am-events')?></label><br />
            <select id="<?php echo $this->get_field_id( 'venue' ); ?>" name="<?php echo $this->get_field_name( 'venue' ); ?>">
                <option value="all" <?php if ( $venue === "all" ){ echo 'selected="selected"'; }?>><?php _e('All', 'am-events') ?></option>
                <?php foreach ($venues as $v) { 
					$venueSlug = $v -> slug;
                    $venueName = $v -> name; ?>
                    <option value="<?php echo $venueSlug ?>" <?php if ( $venue === $venueSlug ){ echo 'selected="selected"'; }?>><?php echo $venueName ?></option>
                <?php } ?>
            </select>
            <br />
            <br />
          
        <?php 
    }

    /**
    * Sanitize widget form values as they are saved.
    *
    * @see WP_Widget::update()
    *
    * @param array $new_instance Values just sent to be saved.
    * @param array $old_instance Previously saved values from database.
    *
    * @return array Updated safe values to be saved.
    */
   public function update( $new_instance, $old_instance ) {
           $instance = $old_instance;
        
           $instance['title'] = strip_tags( $new_instance['title'] );
           $instance['category'] = $new_instance['category'] ;
           $instance['venue'] = $new_instance['venue'];
           
           return $instance;
   }

}

/*
 * Display calendar with days that have events as links.
 *
 * The calendar is cached, which will be retrieved, if it exists. If there are
 * no events for the month, then it will not be displayed.
 *
 * @since 1.0.0
 *
 * @param bool $initial Optional, default is true. Use initial calendar names.
 * @param bool $echo Optional, default is true. Set to false for return.
 * @return string|null String when retrieving, null when displaying.
 */
function am_get_calendar($initial = true, $echo = true, $category = null, $venue = null) {
		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

        // TODO: remove before release
        delete_get_calendar_cache();
        
		$key = md5( $m . $monthnum . $year );
		if ( $cache = wp_cache_get( 'get_calendar', 'calendar' ) ) {
            if ( is_array($cache) && isset( $cache[ $key ] ) ) {
                if ( $echo ) {
                    /** This filter is documented in wp-includes/general-template.php */
                    echo apply_filters( 'get_calendar', $cache[$key] );
                    return;
                } else {
                    /** This filter is documented in wp-includes/general-template.php */
                    return apply_filters( 'get_calendar', $cache[$key] );
                }
            }
		}

		if ( !is_array($cache) )
            $cache = array();

		if ( isset($_GET['w']) )
            $w = ''.intval($_GET['w']);

		// week_begins = 0 stands for Sunday
		$week_begins = intval(get_option('start_of_week'));

		// Let's figure out when we are
		if ( !empty($monthnum) && !empty($year) ) {
            $thismonth = ''.zeroise(intval($monthnum), 2);
            $thisyear = ''.intval($year);
		} elseif ( !empty($w) ) {
            // We need to get the month from MySQL
            $thisyear = ''.intval(substr($m, 0, 4));
            $d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
            $thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
		} elseif ( !empty($m) ) {
            $thisyear = ''.intval(substr($m, 0, 4));
            if ( strlen($m) < 6 )
                $thismonth = '01';
            else
                $thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
		} else {
            $thisyear = gmdate('Y', current_time('timestamp'));
            $thismonth = gmdate('m', current_time('timestamp'));
		}

		$unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear);

		$previous_year = gmdate('Y', strtotime('-1 month', current_time('timestamp')));
		$previous_month = gmdate('m', strtotime('-1 month', current_time('timestamp')));
		
		$next_year = gmdate('Y', strtotime('1 month', current_time('timestamp')));
		$next_month = gmdate('m', strtotime('1 month', current_time('timestamp')));

		/* translators: Calendar caption: 1: month name, 2: 4-digit year */
		$calendar_caption = _x('%1$s %2$s', 'calendar caption');
		$calendar_output = '<table id="wp-calendar">
		<caption>' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y', $unixmonth)) . '</caption>
		<thead>
		<tr>';

		$myweek = array();

		for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
			$myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
		}

		foreach ( $myweek as $wd ) {
            $day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
			$wd = esc_attr($wd);
			$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
		}
		
		$calendar_output .= '
		</tr>
		</thead>

		<tfoot>
		<tr>';

		$calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . am_get_event_date_archive_link( $previous_year, $previous_month ) . '">&laquo; ' . $wp_locale->get_month_abbrev($wp_locale->get_month($previous_month)) . '</a></td>';
		$calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';

		$calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . am_get_event_date_archive_link( $next_year, $next_month ) . '">' . $wp_locale->get_month_abbrev($wp_locale->get_month($next_month)) . ' &raquo;</a></td>';

		$calendar_output .= '
		</tr>
		</tfoot>

		<tbody>
		<tr>';

		$taxQuery = array( 'relation' => 'AND' );
		
        /* Event category filter args */
        if ($category) {         
            $taxCategory = array(
                'taxonomy' => 'am_event_categories',
                'field' => 'slug',
                'terms' => $category,
            );
			$taxQuery[] = $taxCategory;
        }
        
        /* Venue filter args */
        if ($venue) {
            $taxVenue = array(
                'taxonomy' => 'am_venues',
                'field' => 'slug',
                'terms' => $venue,
            );
			$taxQuery[] = $taxVenue;
        }
		
		$first_second_of_month = date("Y-$thismonth-01 00:00:00", current_time('timestamp'));
		$last_second_of_month  = date("Y-$thismonth-t 23:59:59", current_time('timestamp'));
		
        /* WP_Query args */
        $args = array(
            'post_type' => 'am_event', // show only am_event cpt
            'post_status' => 'publish', // show only published
            'tax_query' => $taxQuery,  
            'meta_query' => array( 'relation' => 'AND', 
				array(
                'key' => 'am_startdate',
                'value' => $last_second_of_month,                
                'compare' => "<"
				),
				array(
                'key' => 'am_enddate',
                'value' => $first_second_of_month,                
                'compare' => ">"
				),
            ),
            
        );

		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'camino') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false)
			$ak_title_separator = "\n";
		else
			$ak_title_separator = ', ';

		$titles_for_day = array();
		
		$query = new WP_Query( $args );
		$events = $query->get_posts();
		
        $titles_for_day = array();
		if ( $events ) {
			foreach ( (array) $events as $event ) {

				/** This filter is documented in wp-includes/post-template.php */
				$event_title = esc_attr( apply_filters( 'the_title', $event->post_title, $event->ID ) );

				$start_day = intval(am_get_the_startdate('d', $event->ID));
				$end_day = intval(am_get_the_enddate('d', $event->ID));
				
				if ( empty($titles_for_day[$start_day]) )
					$titles_for_day[$start_day] = ''.$event_title;
				else
					$titles_for_day[$start_day] .= $ak_title_separator . $event_title;
                   
				if ( empty($titles_for_day[$end_day]) ) // first one
					$titles_for_day[$end_day] = ''.$event_title;
				else
					$titles_for_day[$end_day] .= $ak_title_separator . $event_title;
					
			}
		}
        
		// See how much we should pad in the beginning
		$pad = calendar_week_mod(date('w', $unixmonth)-$week_begins);
		if ( 0 != $pad )
			$calendar_output .= "\n\t\t".'<td colspan="'. esc_attr($pad) .'" class="pad">&nbsp;</td>';

		$daysinmonth = intval(date('t', $unixmonth));
		for ( $day = 1; $day <= $daysinmonth; ++$day ) {
			if ( isset($newrow) && $newrow )
				$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
			$newrow = false;

			if ( $day == gmdate('j', current_time('timestamp')) && $thismonth == gmdate('m', current_time('timestamp')) && $thisyear == gmdate('Y', current_time('timestamp')) )
				$calendar_output .= '<td id="today">';
			else
				$calendar_output .= '<td>';

			if ( !empty($titles_for_day[$day] )) // any posts today?
				$calendar_output .= '<a href="' . am_get_event_date_archive_link( $thisyear, $thismonth, $day ) . '" title="' . esc_attr( $titles_for_day[ $day ] ) . "\">$day</a>";
			else
				$calendar_output .= $day;
				
			$calendar_output .= '</td>';

			if ( 6 == calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins) )
				$newrow = true;
		}

		$pad = 7 - calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins);
		if ( $pad != 0 && $pad != 7 )
			$calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';

		$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

		$cache[ $key ] = $calendar_output;
		wp_cache_set( 'get_calendar', $cache, 'calendar' );

		if ( $echo ) {
			/**
			 * Filter the HTML calendar output.
			 *
			 * @since 3.0.0
			 *
			 * @param string $calendar_output HTML output of the calendar.
			 */
			echo apply_filters( 'get_calendar', $calendar_output );
		} else {
			/** This filter is documented in wp-includes/general-template.php */
			return apply_filters( 'get_calendar', $calendar_output );
		}

}

/**
 * This allows us to generate any archive link - plain, yearly, monthly, daily
 * 
 * @param int $year
 * @param int $month (optional)
 * @param int $day (optional)
 * @return string
 */
function am_get_event_date_archive_link( $year, $month = 0, $day = 0 ) {
    global $wp_rewrite;
    
    $post_type_obj = get_post_type_object( 'am_event' );
    $post_type_slug = $post_type_obj->rewrite['slug'] ? $post_type_obj->rewrite['slug'] : $post_type_obj->name;
    if( $day ) { // day archive link
        // set to today's values if not provided
        if ( !$year )
            $year = gmdate('Y', current_time('timestamp'));
        if ( !$month )
            $month = gmdate('m', current_time('timestamp'));
        $link = $wp_rewrite->get_day_permastruct();
    } else if ( $month ) { // month archive link
        if ( !$year )
            $year = gmdate('Y', current_time('timestamp'));
        $link = $wp_rewrite->get_month_permastruct();
    } else { // year archive link
        $link = $wp_rewrite->get_year_permastruct();
    }
    if ( !empty($link) ) {
        $link = str_replace('%year%', $year, $link);
        $link = str_replace('%monthnum%', zeroise(intval($month), 2), $link );
        $link = str_replace('%day%', zeroise(intval($day), 2), $link );
        return home_url( "$post_type_slug$link" );
    }
    return home_url( "$post_type_slug" );
}

?>