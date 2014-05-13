<?php

/**
 * Tests to template tags.
 *
 * Dates:
 *
 *          am_the_startdate($format = 'Y-m-d H:i:s', $before = '', $after = '', $echo = true)
 *          am_get_the_startdate( $format = 'Y-m-d H:i:s', $post = 0 )
 *          am_the_enddate($format = 'Y-m-d H:i:s', $before = '', $after = '', $echo = true)
 *          am_get_the_enddate( $format = 'Y-m-d H:i:s', $post = 0 )
 * 
 * Venues:
 *
 *          am_get_the_venue( $id = false )
 *          am_in_venue( $venue, $post = null )
 *          am_get_the_venue_list( $separator = '', $parents='', $post_id = false )
 *          am_the_venue( $separator = '', $parents='', $post_id = false )
 * 
 * Event categories:
 *
 *          am_get_the_event_category( $id = false )
 *          am_get_the_event_category_list( $separator = '', $parents='', $post_id = false )
 *          am_in_event_category( $eventCategory, $post = null )
 *          am_the_event_category( $separator = '', $parents='', $post_id = false )
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_Template_Tags extends WP_UnitTestCase {
	
	/**
	 * Test am_get_the_startdate($format = 'Y-m-d H:i:s')
	 */
	function test_am_get_the_startdate() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		update_post_meta($test_post_id, 'am_startdate', '2012-11-10 09:08:07');

		$this->assertEquals( '2012-11-10 09:08:07', am_get_the_startdate( 'Y-m-d H:i:s',$test_post_id ) );
		$this->assertEquals( '10/11/2012', am_get_the_startdate( 'd/m/Y',$test_post_id ) );
	}
	
	/**
	 * Test am_the_startdate($format = 'Y-m-d H:i:s', $before = '', $after = '', $echo = true)
	 */
	function test_am_the_startdate() {
		global $post;
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		update_post_meta($test_post_id, 'am_startdate', '2012-11-10 09:08:07');
		
		$post = get_post( $test_post_id );
		$this->assertEquals( '<test>2012-11-10 09:08:07</test>', am_the_startdate( 'Y-m-d H:i:s', '<test>', '</test>', false ) );
	}

}
