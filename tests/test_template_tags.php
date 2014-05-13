<?php

/**
 * Tests to template tags.
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
		ob_start();
		am_the_startdate( 'Y-m-d H:i:s', '<test>', '</test>', true );
		$echoed = ob_get_contents();
		ob_end_clean();
		
		$returned = am_the_startdate( 'Y-m-d H:i:s', '<test>', '</test>', false );
		
		$this->assertEquals( '<test>2012-11-10 09:08:07</test>', $returned );
		$this->assertEquals( '<test>2012-11-10 09:08:07</test>', $echoed );
	}
	
	/**
	 * Test am_get_the_enddate($format = 'Y-m-d H:i:s')
	 */
	function test_am_get_the_enddate() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		update_post_meta($test_post_id, 'am_enddate', '2012-11-10 09:08:07');

		$this->assertEquals( '2012-11-10 09:08:07', am_get_the_enddate( 'Y-m-d H:i:s',$test_post_id ) );
		$this->assertEquals( '10/11/2012', am_get_the_enddate( 'd/m/Y',$test_post_id ) );
	}
	
	/**
	 * Test am_the_enddate($format = 'Y-m-d H:i:s', $before = '', $after = '', $echo = true)
	 */
	function test_am_the_enddate() {
		global $post;
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		update_post_meta($test_post_id, 'am_enddate', '2012-11-10 09:08:07');
		
		$post = get_post( $test_post_id );
		
		ob_start();
		am_the_enddate( 'Y-m-d H:i:s', '<test>', '</test>', true );
		$echoed = ob_get_contents();
		ob_end_clean();
		
		$returned = am_the_enddate( 'Y-m-d H:i:s', '<test>', '</test>', false );
		
		$this->assertEquals( '<test>2012-11-10 09:08:07</test>', $returned );
		$this->assertEquals( '<test>2012-11-10 09:08:07</test>', $echoed );
	}
	
	/**
	 * Test am_get_the_venue( $id = false )
	 */
	function test_am_get_the_venue( ) {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$venue1 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Venue1' ) );
		$venue2 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Venue2' ) );
		$venue3 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Venue3' ) );
		wp_set_object_terms( $test_post_id, array($venue1, $venue3), 'am_venues' );
		$this->assertEquals( array($venue1, $venue3), wp_list_pluck(am_get_the_venue( $test_post_id ), 'term_id'));
	}

	
	/**
	 * Test am_get_the_venue_list( $separator = '', $parents='', $post_id = false )
	 */
	function test_am_get_the_venue_list() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$venue1 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Beach' ) );
		$venue2 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Home' ) );
		$venue3 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'McDonalds' ) );
		wp_set_object_terms( $test_post_id, array($venue1, $venue2, $venue3), 'am_venues' );
		
		// Example of return value:
		// <a href="" title="View all events in Beach" rel="venue">Beach</a>|<a href="" title="View all events in Home" rel="venue">Home</a>|<a href="" title="View all events in McDonalds" rel="venue">McDonalds</a>
		// TODO: Assert with all values of parent
		
		$regexp_separator = '/<a.*<\\/a>\|<a.*<\\/a>\|<a.*<\\/a>/i';
		$regexp_no_separator = "/<ul.*(<li>.*<a.*<\/a><\/li>)*<\/ul>/sx";
		
		$this->assertRegExp( $regexp_separator, am_get_the_venue_list( '|', 'single', $test_post_id), "\$parents = 'single', \$separator='|'");
		$this->assertRegExp( $regexp_no_separator, am_get_the_venue_list( '', 'single', $test_post_id), "\$parents = 'single', \$separator=''");
		$this->assertRegExp( $regexp_separator, am_get_the_venue_list( '|', '', $test_post_id), "\$parents = '', \$separator='|'");
		$this->assertRegExp( $regexp_no_separator, am_get_the_venue_list( '', '', $test_post_id), "\$parents = '', \$separator=''");
		$this->assertRegExp( $regexp_separator, am_get_the_venue_list( '|', 'multiple', $test_post_id), "\$parents = 'multiple', \$separator='|'");
		$this->assertRegExp( $regexp_no_separator, am_get_the_venue_list( '', 'multiple', $test_post_id), "\$parents = 'multiple', \$separator=''");
	
	}
	
	/**
	 * Test am_the_venue( $separator = '', $parents='', $post_id = false )
	 */
	function test_am_the_venue( ) {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$venue1 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Beach' ) );
		$venue2 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Home' ) );
		$venue3 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'McDonalds' ) );
		wp_set_object_terms( $test_post_id, array($venue1, $venue2, $venue3), 'am_venues' );
		
		ob_start();
		am_the_venue('|', 'single', $test_post_id);
		$echoed = ob_get_contents();
		ob_end_clean();
		
		$this->assertRegExp( '/<a.*Beach<\\/a>\|<a.*Home<\\/a>\|<a.*McDonalds<\\/a>/i', $echoed);
	}
	
	/**
	 * Test am_the_event_category( $separator = '', $parents='', $post_id = false )
	 */
	function test_am_the_event_category() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$category1 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category1' ) );
		$category2 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category2' ) );
		$category3 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category3' ) );
		wp_set_object_terms( $test_post_id, array($category1, $category2, $category3), 'am_event_categories' );
		
		ob_start();
		am_the_event_category('|', 'single', $test_post_id);
		$echoed = ob_get_contents();
		ob_end_clean();
		
		// TODO: Assert with all values of parent
		$this->assertRegExp( '/<a.*Category1<\\/a>\|<a.*Category2<\\/a>\|<a.*Category3<\\/a>/i', $echoed);
	}
	
	/**
	 * Test am_in_venue( $venue, $post = null )
	 */
	function test_am_in_venue() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$venue1 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Beach' ) );
		$venue2 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Home' ) );
		$venue3 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'McDonalds' ) );
		wp_set_object_terms( $test_post_id, array($venue1, $venue3), 'am_venues' );
		$this->assertFalse( am_in_venue( "", $test_post_id) );
		$this->assertTrue( am_in_venue( "Beach", $test_post_id) );
		$this->assertFalse( am_in_venue( "Home", $test_post_id) );
	}
	
	
	/**
	 * Test am_get_the_event_category( $id = false ) {
	 */
	function test_am_get_the_event_category( ) {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$cat1 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category1' ) );
		$cat2 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category2' ) );
		$cat3 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category3' ) );
		wp_set_object_terms( $test_post_id, array($cat1, $cat3), 'am_event_categories' );
		
		$this->assertEquals( array($cat1, $cat3), wp_list_pluck(am_get_the_event_category( $test_post_id ), 'term_id'));
	}
	
	/**
	 * Test am_get_the_event_category_list( $separator = '', $parents='', $post_id = false )
	 */
	function test_am_get_the_event_category_list() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$category1 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category1' ) );
		$category2 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category2' ) );
		$category3 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category3' ) );
		wp_set_object_terms( $test_post_id, array($category1, $category2, $category3), 'am_event_categories' );
		
		// Example of return value:
		// <a href="" title="View all events in Category1" rel="category">Category1</a>|<a href="" title="View all events in Category2" rel="category">Category2</a>|<a href="" title="View all events in Category3" rel="category">Category3</a>
		
		// TODO: Assert with all values of parent
		$this->assertRegExp( '/<a.*Category1<\\/a>\|<a.*Category2<\\/a>\|<a.*Category3<\\/a>/i', am_get_the_event_category_list( '|', 'single', $test_post_id));
		
		$regexp_separator = '/<a.*<\\/a>\|<a.*<\\/a>\|<a.*<\\/a>/i';
		$regexp_no_separator = "/<ul.*(<li>.*<a.*<\/a><\/li>)*<\/ul>/sx";
		
		$this->assertRegExp( $regexp_separator, am_get_the_event_category_list( '|', 'single', $test_post_id), "\$parents = 'single', \$separator='|'");
		$this->assertRegExp( $regexp_no_separator, am_get_the_event_category_list( '', 'single', $test_post_id), "\$parents = 'single', \$separator=''");
		$this->assertRegExp( $regexp_separator, am_get_the_event_category_list( '|', '', $test_post_id), "\$parents = '', \$separator='|'");
		$this->assertRegExp( $regexp_no_separator, am_get_the_event_category_list( '', '', $test_post_id), "\$parents = '', \$separator=''");
		$this->assertRegExp( $regexp_separator, am_get_the_event_category_list( '|', 'multiple', $test_post_id), "\$parents = 'multiple', \$separator='|'");
		$this->assertRegExp( $regexp_no_separator, am_get_the_event_category_list( '', 'multiple', $test_post_id), "\$parents = 'multiple', \$separator=''");
	}
	
	/**
	 * Test am_in_event_category( $eventCategory, $post = null )
	 */
	function test_am_in_event_category() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		
		$cat1 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category1' ) );
		$cat2 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category2' ) );
		$cat3 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category3' ) );
		wp_set_object_terms( $test_post_id, array($cat1, $cat3), 'am_event_categories' );
		$this->assertFalse( am_in_event_category( '', $test_post_id) , "Empty category did not return false");
		$this->assertTrue( am_in_event_category( 'Category1', $test_post_id), "Category1 did not return true" );
		$this->assertFalse( am_in_event_category( 'Category2', $test_post_id), "Category2 did not return false" );
	}
	
	
}
