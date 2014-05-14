<?php
/**
 * Tests to am-events.php
 *
 * @package wordpress-plugins-tests
 */
class WP_Test_AM_Events extends WP_UnitTestCase {

	function test_am_get_default_date_format() {
		$this->assertEquals( 'Y-m-d H:i:s', am_get_default_date_format());
	}
	
}

?>