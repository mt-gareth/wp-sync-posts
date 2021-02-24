<?php

namespace WPSP;

class Ajax
{
	/**
	 * @var string
	 */
	private $prefix = 'wp_ajax_wpsp_';

	/**
	 * @var array
	 */
	private $actions = [
		'update_setting',
		'reset_key',
		'add_update_connection',
		'delete_connection',
		'check_remote_url',
		'start_sync'
	];

	public function get_prefix()
	{
		return $this->prefix;
	}

	public function get_actions()
	{
		return $this->actions;
	}

	/**
	 * Register Ajax Endpoints for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_ajax()
	{
		foreach ( $this->actions as $action ) {
			add_action( $this->prefix . $action, [ $this, 'ajax_' . $action ] );
		}
	}

	public function ajax_update_setting()
	{
		if ( !current_user_can( 'edit_posts' ) ) wp_send_json_error( 'This user does not have access to update settings' );
		$verify_nonce = check_ajax_referer( 'wpsp-update-settings', 'nonce', false );
		if ( $verify_nonce === false ) wp_send_json_error( 'This session did not verify. Please try again.' );

		$allowed_settings = [
			'wpsp_allow_pull',
			'wpsp_allow_push',
		];
		$request_setting = esc_textarea( $_REQUEST[ 'setting' ] );
		$request_value = esc_html( $_REQUEST[ 'value' ] );
		if ( !in_array( $request_setting, $allowed_settings ) ) wp_send_json_error( 'Not an allowed setting!' );
		if ( !update_option( $request_setting, $request_value, false ) ) wp_send_json_error( 'There was an error while updating settings' );
		wp_send_json_success( $request_value );
	}

	public function ajax_reset_key()
	{
		if ( !current_user_can( 'edit_posts' ) ) wp_send_json_error( 'This user does not have access to update settings' );
		$verify_nonce = check_ajax_referer( 'wpsp-settings-reset-key', 'nonce', false );
		if ( $verify_nonce === false ) wp_send_json_error( 'This session did not verify. Please try again.' );

		$new_key = $this->generate_new_key();
		if ( !update_option( 'wpsp_key', $new_key, false ) ) wp_send_json_error( 'There was an error while updating settings' );
		wp_send_json_success( $new_key );
	}

	public function ajax_add_update_connection()
	{
		if ( !current_user_can( 'edit_posts' ) ) wp_send_json_error( 'This user does not have access to update settings' );
		$verify_nonce = check_ajax_referer( 'wpsp-connection', 'nonce', false );
		if ( $verify_nonce === false ) wp_send_json_error( 'This session did not verify. Please try again.' );

		$current_connections = get_option( 'wpsp_connections' );
		if ( $current_connections ) $current_connections = json_decode( $current_connections, true );

		$params = [];
		parse_str( $_REQUEST[ 'form' ], $params );


		$connection = [
			'ID'           => (int)esc_textarea( $params[ 'connection-id' ] ),
			'name'         => esc_textarea( $params[ 'name' ] ),
			'url'          => esc_url_raw( $params[ 'url' ] ),
			'key'          => esc_textarea( $params[ 'key' ] ),
			'find_replace' => [],
			//add in allow push and pull
			'allow_pull'   => esc_textarea( $params[ 'allow_pull' ] ),
			'allow_push'   => esc_textarea( $params[ 'allow_push' ] ),

		];

		error_log( print_r( $current_connections, true ) );

		foreach ( $params[ 'find-replace' ] as $find_replace ) {
			$connection[ 'find_replace' ][] = [ esc_textarea( $find_replace[ 'find' ] ), esc_textarea( $find_replace[ 'replace' ] ) ];
		}

		if ( $connection[ 'ID' ] ) {
			$found = false;
			foreach ( $current_connections as $connection_index => $current_connection ) {
				if ( (int)$current_connection[ 'ID' ] === (int)$connection[ 'ID' ] ) {
					$current_connections[ $connection_index ] = $connection;
					$found = true;
					break;
				}
			}
			if ( !$found ) wp_send_json_error( 'Connection not found' );
		} else {
			$highest_id = 0;
			foreach ( $current_connections as $connection_index => $current_connection ) {
				if ( (int)$current_connection[ 'ID' ] > $highest_id ) $highest_id = (int)$current_connection[ 'ID' ];
			}
			$connection[ 'ID' ] = $highest_id + 1;
			$current_connections[] = $connection;
		}

		if ( !update_option( 'wpsp_connections', json_encode( $current_connections ), false ) ) wp_send_json_error( 'There was an error while updating settings' );
		wp_send_json_success( $current_connections );
	}

	public function ajax_delete_connection()
	{
		if ( !current_user_can( 'edit_posts' ) ) wp_send_json_error( 'This user does not have access to update settings' );
		$verify_nonce = check_ajax_referer( 'wpsp-connection', 'nonce', false );
		if ( $verify_nonce === false ) wp_send_json_error( 'This session did not verify. Please try again.' );

		$current_connections = get_option( 'wpsp_connections' );
		if ( !$current_connections ) wp_send_json_error( 'No Connections Found' );
		$current_connections = json_decode( $current_connections, true );

		$connection_to_delete = (int)esc_textarea( $_REQUEST[ 'connection_id' ] );
		$found = false;
		foreach ( $current_connections as $connection_index => $current_connection ) {
			if ( (int)$current_connection[ 'ID' ] === $connection_to_delete ) {
				array_splice( $current_connections, $connection_index, 1 );
				$found = true;
				break;
			}
		}
		if ( !$found ) wp_send_json_error( 'Connection not found' );

		if ( !update_option( 'wpsp_connections', json_encode( $current_connections ), false ) ) wp_send_json_error( 'There was an error while updating settings' );
		wp_send_json_success( $current_connections );
	}

	public function ajax_check_remote_url()
	{
		if ( !current_user_can( 'edit_posts' ) ) wp_send_json_error( 'This user does not have access to update settings' );
		$verify_nonce = check_ajax_referer( 'wpsp-connection', 'nonce', false );
		if ( $verify_nonce === false ) wp_send_json_error( 'This session did not verify. Please try again.' );

		if ( !array_key_exists( 'url', $_REQUEST ) ) wp_send_json_error( 'The URL is not provided or valid' );
		if ( !array_key_exists( 'key', $_REQUEST ) ) wp_send_json_error( 'The key is not provided or valid' );

		$url = esc_url_raw( $_REQUEST[ 'url' ] );
		$key = esc_textarea( $_REQUEST[ 'key' ] );

		$remote_site = new RemoteSiteInterface( $url, $key );
		$remote_data = $remote_site->validate();
		if ( !$remote_data[ 'success' ] ) wp_send_json_error( 'The url and key did not validate' . print_r( $remote_data[ 'data' ], true ) );

		$response_data = $remote_data;
		$response_data[ 'url' ] = $url;
		wp_send_json_success( $response_data );
	}

	public function ajax_start_sync()
	{
		if ( !current_user_can( 'edit_posts' ) ) wp_send_json_error( 'This user does not have access to update settings' );
		$verify_nonce = check_ajax_referer( 'wpsp-sync', 'nonce', false );
		if ( $verify_nonce === false ) wp_send_json_error( 'This session did not verify. Please try again.' );

		$request_data = $_REQUEST[ 'data' ];
		$connection_id = (int)esc_textarea( $request_data[ 'connection_id' ] );
		$connection = $this->get_connection( $connection_id );

		$remote_site = new RemoteSiteInterface( $connection->url, $connection->key );
		$remote_post_selection = esc_textarea( $request_data[ 'remote_post_selection' ] );
		$manual_post_id = (int)esc_textarea( $request_data[ 'manual_post_id' ] );

		$local_post_id = (int)esc_textarea( $request_data[ 'local_post_id' ] );
		$local_post = new PostInterface( $local_post_id );

		$direction = esc_textarea( $request_data[ 'direction' ] );
		if ( $direction === 'push' ) {
			$send_status = $remote_site->send_push_request( $local_post->get_post_data(), $connection->find_replace, $remote_post_selection, $manual_post_id );
			if ( !$send_status[ 'success' ] ) wp_send_json_error( 'There was an error while sending request ' . print_r( $send_status[ 'data' ], true ) );
		} else {
			$remote_post_data = $remote_site->send_pull_request( $remote_post_selection, $local_post->get_slug(), $manual_post_id );
			if ( !$remote_post_data[ 'success' ] ) wp_send_json_error( 'There was an error while sending request ' . print_r( $remote_post_data[ 'data' ], true ) );
			$local_post->set_post_data( $remote_post_data[ 'data' ][ 'local_post_data' ], $this->reverse_find_replace( $connection->find_replace ) );
		}
		wp_send_json_success();
	}

	private function reverse_find_replace( $find_replace_array )
	{
		foreach ( $find_replace_array as $find_replace_key => $find_replace ) {
			$find_replace_array[ $find_replace_key ] = [ $find_replace[ 1 ], $find_replace[ 0 ] ];
		}
		return $find_replace_array;
	}

	private function generate_new_key()
	{
		$length = 24;
		return substr( preg_replace( "/[^a-zA-Z0-9]/", "", base64_encode( openssl_random_pseudo_bytes( $length + 1, $strong ) ) ), 0, $length );
	}

	private function get_connection( $connection_id )
	{
		$connections = get_option( 'wpsp_connections' ) ? json_decode( get_option( 'wpsp_connections' ) ) : [];
		foreach ( $connections as $connection ) {
			if ( $connection->ID == $connection_id ) {
				return $connection;
			}
		}
		return false;
	}

}