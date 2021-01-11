<?php
/**
 * Class SampleTest
 *
 * @package Wp_Sync_Posts
 */

use WPSP\Ajax;

/**
 * Sample test case.
 */
class TestAjax extends \WP_Ajax_UnitTestCase
{
	/**
	 * @var Ajax
	 */
	private Ajax $class;

	/**
	 * Load up the class
	 */
	public function setUp()
	{
		parent::setup();

		$this->class = new Ajax();
		wp_set_current_user( 1 );
	}

	private function send_ajax( $endpoint )
	{
		$this->class->admin_ajax();
		try {
			$this->_handleAjax( $endpoint );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		return json_decode( $this->_last_response );
	}

	/**
	 * Test that all the requested ajax actions are made
	 */
	public function test_admin_ajax()
	{
		$this->class->admin_ajax();
		$prefix = $this->class->get_prefix();
		$actions = $this->class->get_actions();
		foreach ( $actions as $action ) {
			$this->assertTrue( has_action( $prefix . $action, [ $this->class, 'ajax_' . $action ] ) !== false );
		}
	}

	/************************* ajax_update_setting ******************************
	 */

	public function test_ajax_update_setting_fail_if_nonce_not_provided()
	{
		global $_POST;

		$response = $this->send_ajax( 'wpsp_update_setting' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_update_setting_fail_if_nonce_wrong()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wrong' );
		$response = $this->send_ajax( 'wpsp_update_setting' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_update_setting_fails_user_is_not_editor()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-update-settings' );
		$_POST[ 'setting' ] = 'wpsp_allow_pull';
		$_POST[ 'value' ] = 1;

		wp_set_current_user( $this->factory()->user->create() );

		$response = $this->send_ajax( 'wpsp_update_setting' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_update_setting_works()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-update-settings' );
		$_POST[ 'setting' ] = 'wpsp_allow_pull';
		$_POST[ 'value' ] = 1;

		$response = $this->send_ajax( 'wpsp_update_setting' );
		$this->assertTrue( $response->success );
		$this->assertEquals( get_option( 'wpsp_allow_pull' ), '1' );
	}

	public function test_ajax_update_setting_works_with_other_value()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-update-settings' );
		$_POST[ 'setting' ] = 'wpsp_allow_pull';
		$_POST[ 'value' ] = 0;

		$response = $this->send_ajax( 'wpsp_update_setting' );
		$this->assertTrue( $response->success );
		$this->assertEquals( get_option( 'wpsp_allow_pull' ), '0' );
	}

	/************************* ajax_reset_key ******************************
	 */

	public function test_ajax_reset_key_fail_if_nonce_not_provided()
	{
		global $_POST;

		$response = $this->send_ajax( 'wpsp_reset_key' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_reset_key_fail_if_nonce_wrong()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wrong' );
		$response = $this->send_ajax( 'wpsp_reset_key' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_reset_key_fails_user_is_not_editor()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-settings-reset-key' );

		wp_set_current_user( $this->factory()->user->create() );

		$response = $this->send_ajax( 'wpsp_reset_key' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_reset_key_works_with_other_value()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-settings-reset-key' );

		$old_key = get_option( 'wpsp_key' );

		$response = $this->send_ajax( 'wpsp_reset_key' );
		$this->assertTrue( $response->success );
		$this->assertNotEquals( get_option( 'wpsp_key' ), $old_key );

		$this->assertEquals( get_option( 'wpsp_key' ), $response->data );
	}

	/************************* ajax_add_update_connection ******************************
	 */

	public function test_ajax_add_update_connection_fail_if_nonce_not_provided()
	{
		global $_POST;

		$response = $this->send_ajax( 'wpsp_add_update_connection' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_add_update_connection_fail_if_nonce_wrong()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wrong' );
		$response = $this->send_ajax( 'wpsp_add_update_connection' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_add_update_connection_fails_user_is_not_editor()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-connection' );

		wp_set_current_user( $this->factory()->user->create() );

		$response = $this->send_ajax( 'wpsp_add_update_connection' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_add_update_connection_add_new_connection()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-connection' );

		$form = [
			'connection-id' => '',
			'name'          => 'Test Connection 1',
			'url'           => 'test-site-1.com',
			'key'           => '123wer',
			'find-replace'  => [
				[
					'find'    => 'find-1',
					'replace' => 'replace-1'
				],
				[
					'find'    => 'find-2',
					'replace' => 'replace-2'
				],
			]
		];
		$_POST[ 'form' ] = http_build_query( $form );

		$response = $this->send_ajax( 'wpsp_add_update_connection' );
		$this->assertTrue( $response->success );
		$this->assertEquals( $response->data, json_decode( get_option( 'wpsp_connections' ) ) );
		$current_connection = $response->data[ 0 ];
		$this->assertConnectionMatchForm($current_connection, $form);
	}

	public function test_ajax_add_update_connection_update_existing_connection()
	{
		$old_connections = [
			[
				'ID'           => 1,
				'name'         => 'Test Site 6',
				'url'          => 'http://test-site-6.d.mt.com:8080/',
				'key'          => 'LziqUUbx7gqWlLZenF5osxSj',
				'find_replace' => [
					[
						'test-find-1',
						'test-replace-2',
					],
					[
						'//test-site-9.d.mt.com:8080',
						'//test-site-6.d.mt.com:8080',
					],
				]
			],
			[
				'ID'           => 2,
				'name'         => 'Test Site 6',
				'url'          => 'http://test-site-6.d.mt.com:8080/',
				'key'          => 'LziqUUbx7gqWlLZenF5osxSj',
				'find_replace' => [
					[
						'test-find-1',
						'test-replace-2',
					],
					[
						'//test-site-9.d.mt.com:8080',
						'//test-site-6.d.mt.com:8080',
					],
				]
			]
		];

		update_option( 'wpsp_connections', json_encode( $old_connections ), false );

		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-connection' );

		$form = [
			'connection-id' => '2',
			'name'          => 'Test Connection 1',
			'url'           => 'test-site-1.com',
			'key'           => '123wer',
			'find-replace'  => [
				[
					'find'    => 'find-1',
					'replace' => 'replace-1'
				],
				[
					'find'    => 'find-2',
					'replace' => 'replace-2'
				],
			]
		];
		$_POST[ 'form' ] = http_build_query( $form );

		$response = $this->send_ajax( 'wpsp_add_update_connection' );
		$this->assertTrue( $response->success );
		$this->assertEquals( $response->data, json_decode( get_option( 'wpsp_connections' ) ) );

		$current_connection = false;
		foreach ($response->data as $returned_connections) {
			if($returned_connections->ID === (int)$form['connection-id']) $current_connection = $returned_connections;
		}
		$this->assertNotEquals( $current_connection, false );
		$this->assertConnectionMatchForm($current_connection, $form);

	}

	
	private function assertConnectionMatchForm($current_connection, $form)
	{
		$this->assertEquals( $current_connection->name, $form[ 'name' ] );
		$this->assertStringContainsString( $form[ 'url' ], $current_connection->url );
		$this->assertEquals( $current_connection->key, $form[ 'key' ] );
		$this->assertEquals( $current_connection->find_replace[ 0 ][ 0 ], $form[ 'find-replace' ][ 0 ][ 'find' ] );
		$this->assertEquals( $current_connection->find_replace[ 0 ][ 1 ], $form[ 'find-replace' ][ 0 ][ 'replace' ] );
		$this->assertEquals( $current_connection->find_replace[ 1 ][ 0 ], $form[ 'find-replace' ][ 1 ][ 'find' ] );
		$this->assertEquals( $current_connection->find_replace[ 1 ][ 1 ], $form[ 'find-replace' ][ 1 ][ 'replace' ] );
	}


	//update an existing connection

	/************************* ajax_delete_connection ******************************
	 */

	public function test_ajax_delete_connection_fail_if_nonce_not_provided()
	{
		global $_POST;

		$response = $this->send_ajax( 'wpsp_delete_connection' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_delete_connection_fail_if_nonce_wrong()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wrong' );
		$response = $this->send_ajax( 'wpsp_delete_connection' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_delete_connection_fails_user_is_not_editor()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-connection' );
		$_POST[ 'setting' ] = 'wpsp_allow_pull';
		$_POST[ 'value' ] = 1;

		wp_set_current_user( $this->factory()->user->create() );

		$response = $this->send_ajax( 'wpsp_delete_connection' );
		$this->assertFalse( $response->success );
	}

	//todo functionality test
	//test fail if connection id does not exisit
	//test success


	/************************* ajax_check_remote_url ******************************
	 */

	public function test_ajax_check_remote_url_fail_if_nonce_not_provided()
	{
		global $_POST;

		$response = $this->send_ajax( 'wpsp_check_remote_url' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_check_remote_url_fail_if_nonce_wrong()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wrong' );
		$response = $this->send_ajax( 'wpsp_check_remote_url' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_check_remote_url_fails_user_is_not_editor()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-connection' );
		$_POST[ 'setting' ] = 'wpsp_allow_pull';
		$_POST[ 'value' ] = 1;

		wp_set_current_user( $this->factory()->user->create() );

		$response = $this->send_ajax( 'wpsp_check_remote_url' );
		$this->assertFalse( $response->success );
	}

	//todo functionality test

	/************************* ajax_start_sync ******************************
	 */

	public function test_ajax_start_sync_fail_if_nonce_not_provided()
	{
		global $_POST;

		$response = $this->send_ajax( 'wpsp_start_sync' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_start_sync_fail_if_nonce_wrong()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wrong' );
		$response = $this->send_ajax( 'wpsp_start_sync' );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_start_sync_fails_user_is_not_editor()
	{
		global $_POST;
		$_POST[ 'nonce' ] = wp_create_nonce( 'wpsp-sync' );
		$_POST[ 'setting' ] = 'wpsp_allow_pull';
		$_POST[ 'value' ] = 1;

		wp_set_current_user( $this->factory()->user->create() );

		$response = $this->send_ajax( 'wpsp_start_sync' );
		$this->assertFalse( $response->success );
	}

	//todo functionality test

}

