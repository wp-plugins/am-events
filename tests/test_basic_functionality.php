<?php

/**
 * Tests to basic functionality, like creating, updating and saving event posts
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_Basic_Functionality extends WP_UnitTestCase {
	
	/**
	 * Test event update
	 */
	function test_update() {
		/* ... */
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		$called = false;
		$handler = function () use (&$called) {
			$called = true;
		};
		add_action( 'save_post', $handler );
		wp_update_post( array( 'ID' => $test_post_id, 'post_title' => 'updated' ) );
		remove_action( 'save_post', $handler );
		$this->assertTrue( $called );
	}
	
	/**
	 * Test post startdate update
	 */
	function test_update_startdate() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		$date = '2012-12-12 12:12:12';
		update_post_meta($test_post_id, 'am_startdate', $date);
		$updated_date = get_post_meta( $test_post_id, 'am_startdate', true );
		$this->assertEquals( $date, $updated_date);
	}
	
	/**
	 * Test post enddate update
	 */
	function test_update_enddate() {
		$test_post_id = $this->factory->post->create( array( 'post_type' => 'am_event' ) );
		$date = '2011-12-12 12:12:12';
		update_post_meta($test_post_id, 'am_enddate', $date);
		$updated_date = get_post_meta( $test_post_id, 'am_enddate', true );
		$this->assertEquals( $date, $updated_date);
	}

}
