<?php

/******************************************************************************
 * =COPYRIGHT
 *****************************************************************************/

/*  Copyright 2013  Atte Moisio  (email : atte.moisio@attemoisio.fi)

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
?>
 
 
<?php

class AM_Upcoming_Events_Widget extends WP_Widget {
   
    /**
    * Register widget with WordPress.
    */
    function AM_Upcoming_Events_Widget() {
        parent::WP_Widget('am_upcoming_events', 'AM Upcoming Events', array('description' => __( 'Display upcoming events', 'am-events' )), array('width' => 400));
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
        $postcount = $instance['postcount'];
        $template = $instance['template'];
        $before = $instance['before'];
        $after = $instance['after'];
        
        /* Before widget (defined by themes). */
        echo $before_widget;
        
        
        /* Event category filter args */
        $taxCategory = NULL;
        if ($category !== "all") {         
            $taxCategory = array(
                'taxonomy' => 'am_event_categories',
                'field' => 'name',
                'terms' => $category,
            );
        }
        
        /* Venue filter args */
        $taxVenue = NULL;
        if ($venue !== "all") {
            $taxVenue = array(
                'taxonomy' => 'am_venues',
                'field' => 'name',
                'terms' => $venue,
            );
        }

        /* WP_Query args */
        $args = array(
            'post_type' => 'am_event', // show only am_event cpt
            'post_status' => 'publish', // show only published
            'posts_per_page' => $postcount, // number of events to show
            'tax_query' => array( // taxonomy and term filter
                    'relation' => 'AND',
                    $taxCategory,
                    $taxVenue,
            ),
            // sort by meta value 'am_startdate' ascending
            'meta_key' => 'am_startdate',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array( array(
                'key' => 'am_enddate',
                 // display events with an end date greater than 
                 // the current time - 24hrs
                'value' => date('Y-m-d H:i:s', time() - (60 * 60 * 24)),                
                'compare' => ">" // startdate > value
				),
            ),
            
        );
        
        /* Title of widget (before and after defined by themes). */
        if ( ! empty( $title ) )
                echo $before_title . $title . $after_title;
       
        echo $before;
        
        $loop = new WP_Query( $args );
        while ($loop->have_posts()) {
            $loop->the_post();

            $post_id = get_the_ID();

            // get post meta
            $meta_venues = wp_get_post_terms( $post_id, 'am_venues' ); 
            $meta_event_categories = wp_get_post_terms( $post_id, 'am_event_categories' ); 
            

            $meta_startdate = get_post_meta($post_id, 'am_startdate', true);
            $meta_enddate = get_post_meta($post_id, 'am_enddate', true);

            // get timestamps of dates
            $timestamp_start = strtotime($meta_startdate);
            $timestamp_end = strtotime($meta_enddate);
            
            //get all the widget template data
            $template_startdate = date('d.m.Y', $timestamp_start);
            $template_enddate = date('d.m.Y', $timestamp_end);

            $template_starttime = date( 'H:i', $timestamp_start);
            $template_endtime = date( 'H:i', $timestamp_end);

            $template_startdayname = getWeekDay(date('N', $timestamp_start));
            $template_enddayname = getWeekDay(date('N', $timestamp_end));

            $template_venue = $meta_venues[0]->name;
            $template_event_category = $meta_event_categories[0]->name;
            
            $template_title = get_the_title();
                   
            // Widget template tags
            $search = array(
                '{{start_day_name}}', 
                '{{start_date}}',
                '{{start_time}}',
                '{{end_day_name}}',
                '{{end_date}}',
                '{{end_time}}',
                '{{title}}',
                '{{event_category}}',
                '{{venue}}',
                );
            
            $replace = array(
                $template_startdayname,
                $template_startdate,
                $template_starttime,
                $template_enddayname,
                $template_enddate,
                $template_endtime,
                $template_title,
                $template_event_category,
                $template_venue,
            );
            
            $output = str_replace($search, $replace, $template);

            echo $output;

         }
         echo $after;

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
        
        $default_template = '<h3>{{title}}</h3>
<p>
    {{start_day_name}} {{start_date}} {{start_time}} - 
    {{end_day_name}} {{end_date}} {{end_time}}
</p>

<p>{{event_category}}</p>

<p>{{venue}}</p>';
                
        $defaults = array( 'title' => __('Upcoming Events', 'am-events'), 'category' => 'all', 'venue' => 'all', 'postcount' => '3', 'template' => $default_template, 'after' => '<p><a href="#">' . __('See More Events ->', 'am-events') . '</a></p>', 'before' => '' );
        $instance = wp_parse_args( (array) $instance, $defaults );


        $title      = $instance[ 'title' ];
        $category   = $instance[ 'category' ];
        $venue      = $instance[ 'venue' ];
        $template   = $instance[ 'template' ];
        $before     = $instance[ 'before' ];
        $after      = $instance[ 'after' ];
        
        $args = array( 'hide_empty' => false );
        
        $types = get_terms('am_event_categories', $args);
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
                <?php foreach ($types as $t) { 
                    $typeName = $t -> name; ?>
                    <option value="<?php echo $typeName ?>" <?php if ( $category === $typeName ){ echo 'selected="selected"'; }?>><?php echo $typeName ?></option>
                <?php } ?>
            </select>
            <br />
            <br />
            
            <!-- Select event venue -->
            <label for="<?php echo $this->get_field_id( 'venue' ); ?>"><?php _e('Select Venue:', 'am-events')?></label><br />
            <select id="<?php echo $this->get_field_id( 'venue' ); ?>" name="<?php echo $this->get_field_name( 'venue' ); ?>">
                <option value="all" <?php if ( $venue === "all" ){ echo 'selected="selected"'; }?>><?php _e('All', 'am-events') ?></option>
                <?php foreach ($venues as $v) { 
                    $venueName = $v -> name; ?>
                    <option value="<?php echo $venueName ?>" <?php if ( $venue === $venueName ){ echo 'selected="selected"'; }?>><?php echo $venueName ?></option>
                <?php } ?>
            </select>
            <br />
            <br />
            
            <label for="<?php echo $this->get_field_id( 'postcount' ); ?>"><?php _e('Number of events:', 'am-events')?></label><br />
            <input type="number" id="<?php echo $this->get_field_id('postcount') ?>" name="<?php echo $this->get_field_name('postcount') ?>" type="number" min="1" value="<?php echo $instance['postcount']; ?>" />      
            <br />
            <br />
            
            <label for="<?php echo $this->get_field_id( 'before' ); ?>"><?php _e('Display before events:', 'am-events')?></label><br />
            <textarea class="widefat" rows="2" id="<?php echo $this->get_field_id('before') ?>" name="<?php echo $this->get_field_name( 'before' ) ?>"><?php echo $before ?></textarea>
            <br/>
            <br />
            
            <label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e('Template for single event:', 'am-events')?></label><br />
            <textarea class="widefat" rows="10" id="<?php echo $this->get_field_id('template') ?>" name="<?php echo $this->get_field_name( 'template' ) ?>"><?php echo $template ?></textarea>
            <br />
            <br />
            
            <label for="<?php echo $this->get_field_id( 'after' ); ?>"><?php _e('Display after events:', 'am-events')?></label><br />
            <textarea class="widefat" rows="2" id="<?php echo $this->get_field_id('after') ?>" name="<?php echo $this->get_field_name( 'after' ) ?>"><?php echo $after ?></textarea>

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
           $instance['postcount'] = strip_tags( $new_instance['postcount'] );
           $instance['template'] = $new_instance['template'];
           $instance['before'] = $new_instance['before'];
           $instance['after'] = $new_instance['after'];
           
           
           return $instance;
   }
   


}

/*
 * Returns the name of the weekday based on given number (1-7)
 */
function getWeekDay($dayNumber) {
    switch($dayNumber) {
        case 1: return __('Mon', 'am-events');
        case 2: return __('Tue', 'am-events');
        case 3: return __('Wed', 'am-events');
        case 4: return __('Thu', 'am-events');
        case 5: return __('Fri', 'am-events');
        case 6: return __('Sat', 'am-events');
        case 7: return __('Sun', 'am-events');
        default: return '';
    }
}



?>
