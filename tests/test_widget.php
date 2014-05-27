<?php

/**
 * Tests to widget
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_Widget extends WP_UnitTestCase {
	
	var $widget;
	
	function setUp() {
		parent::setUp();
		
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'editor' ) ) );
	}

	function tearDown() {
		parent::tearDown();
	}
	
	
	protected static function getMethod($name) {
		$class = new ReflectionClass('AM_Upcoming_Events_Widget');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	function test_parse_event() {
		global $post;	
		
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		update_post_meta($test_post_id, 'am_startdate', '2012-11-10 09:08:07');
		update_post_meta($test_post_id, 'am_enddate', '2013-12-11 10:09:08');
		
		wp_update_post( array( 
			'ID' => $test_post_id,
			'post_content' => 'lorem ipsum dolor sit amet',
			'post_title' => 'MyTitle',
			) );
		
		$venue1 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Beach' ) );
		$venue2 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'Home' ) );
		$venue3 = $this->factory->term->create( array( 'taxonomy' => 'am_venues', 'name' => 'McDonalds' ) );
		wp_set_object_terms( $test_post_id, array($venue3), 'am_venues' );
		
		$category1 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category1' ) );
		$category2 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category2' ) );
		$category3 = $this->factory->term->create( array( 'taxonomy' => 'am_event_categories', 'name' => 'Category3' ) );
		wp_set_object_terms( $test_post_id, array($category1), 'am_event_categories' );
		
		$post = get_post( $test_post_id );
		$widget = new AM_Upcoming_Events_Widget();	
		$method = self::getMethod('parse_event');
		
		$this->assertEquals(
			"2012-11-10 09:08:07", 
			$method->invokeArgs($widget, array("[start-date format='Y-m-d H:i:s']")),
			"Testing [start-date format='Y-m-d H:i:s']");
			
		$this->assertEquals(
			"2013-12-11 10:09:08", 
			$method->invokeArgs($widget, array("[end-date format='Y-m-d H:i:s']")),
			"Testing [end-date format='Y-m-d H:i:s']");
			
		$this->assertEquals(
			"McDonalds", 
			$method->invokeArgs($widget, array("[event-venue]")),
			"Testing [event-venue]");
			
		$this->assertEquals(
			"Category1", 
			$method->invokeArgs($widget, array("[event-category]")),
			"Testing [event-category]");
		
		$this->assertEquals(
			"MyTitle", 
			$method->invokeArgs($widget, array("[event-title]")),
			"Testing [event-title]");
		
		/*$this->assertEquals(
			"lorem ipsum dolor sit amet", 
			$method->invokeArgs($widget, array("[content]")),
			"Testing [content]");
			
		$this->assertEquals(
			"lorem ipsum <p>dolor", 
			$method->invokeArgs($widget, array("[content limit=3]")),
			"Testing [content limit=3]");*/
			
		$this->assertEquals(
			"working!",
			$method->invokeArgs($widget, array("[if cond='startdate-not-enddate']working![/if]")),
			"Testing [if cond='startdate-not-enddate'] with different dates");
		
		$this->assertEquals(
			"",
			$method->invokeArgs($widget, array("[if cond='startdate-is-enddate']This should not display[/if]")),
			"Testing [if cond='startdate-is-enddate'] with different dates");
			
		$this->assertEquals(
			"working!",
			$method->invokeArgs($widget, array("[if cond='startday-not-endday']working![/if]")),
			"Testing [if cond='startday-not-enddy'] with different dates");
		
		$this->assertEquals(
			"",
			$method->invokeArgs($widget, array("[if cond='startday-is-endday']This should not display[/if]")),
			"Testing [if cond='startday-is-endday'] with different dates");
			
		update_post_meta($test_post_id, 'am_startdate', '2013-11-11 10:09:08');
		update_post_meta($test_post_id, 'am_enddate', '2013-11-11 10:09:08');
		$post = get_post( $test_post_id );
		
		$this->assertEquals(
			"",
			$method->invokeArgs($widget, array("[if cond='startdate-not-enddate']This should not display[/if]")),
			"Testing [if cond='startdate-not-enddate'] with equal dates");
		
		$this->assertEquals(
			"working!",
			$method->invokeArgs($widget, array("[if cond='startdate-is-enddate']working![/if]")),
			"Testing [if cond='startdate-is-enddate'] with equal dates");
			
		$this->assertEquals(
			"",
			$method->invokeArgs($widget, array("[if cond='startday-not-endday']This should not display[/if]")),
			"Testing [if cond='startday-not-endday'] with equal dates");
		
		$this->assertEquals(
			"working!",
			$method->invokeArgs($widget, array("[if cond='startday-is-endday']working![/if]")),
			"Testing [if cond='startday-is-endday'] with equal dates");

	}
	
	
	
}

?>