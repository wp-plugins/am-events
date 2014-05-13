<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_WordPress_Plugin_Tests extends WP_UnitTestCase {

	/**
	 * If these tests are being run on Travis CI, verify that the version of
	 * WordPress installed is the version that we requested.
	 *
	 * @requires PHP 5.3
	 */
	function test_wp_version() {

		if ( !getenv( 'TRAVIS' ) )
			$this->markTestSkipped( 'Test skipped since Travis CI was not detected.' );

		$requested_version = getenv( 'WP_VERSION' ) . '-src';

		// The "master" version requires special handling.
		if ( $requested_version == 'master-src' ) {
			$file = file_get_contents( 'https://raw.github.com/tierra/wordpress/master/src/wp-includes/version.php' );
			preg_match( '#\$wp_version = \'([^\']+)\';#', $file, $matches );
			$requested_version = $matches[1];
		}

		$this->assertEquals( get_bloginfo( 'version' ), $requested_version );

	}

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'am-events/am-events.php' ) );
	}
	
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
	
	/**
	 * Test taxonomy creation
	 */
	function test_create_event_category() {
		$cat1 = $this->factory->term->create_and_get( array( 
			'name' => 'category-1',
			'taxonomy' => 'am_event_categories' 
			) );
		
	}

}
