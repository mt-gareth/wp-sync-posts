<?php

namespace WPSP;

class AjaxNopriv
{
	/**
	 * Register the Ajax Endpoints for the remote.
	 *
	 * @since    1.0.0
	 */
	public function nopriv_ajax()
	{
		$prefix = 'wp_ajax_nopriv_wpsp_';
		$actions = [
			'validate',
			'accept_push_request',
			'accept_pull_request',
		];
		foreach ( $actions as $action ) {
			add_action( $prefix . $action, [ $this, 'ajax_nopriv_' . $action ] );
		}
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	private function validate_key( $key )
	{
		return $key === get_option( 'wpsp_key' );
	}

	/**
	 *
	 */
	public function ajax_nopriv_validate()
	{
		$key = esc_textarea( $_REQUEST[ 'key' ] );
		$valid = $this->validate_key( $key );
		if ( $valid ) wp_send_json_success( $valid );
		wp_send_json_error( $valid );
	}

	/**
	 *
	 */
	public function ajax_nopriv_accept_push_request()
	{
		$key = esc_textarea( $_REQUEST[ 'key' ] );
		$valid = $this->validate_key( $key );
		if ( !$valid ) wp_send_json_error( 'Key did not validate' );

		if(!get_option( 'wpsp_allow_push' )) wp_send_json_error( 'This site does not allow Push' );

		$remote_post_data = $_REQUEST[ 'local_post_data' ];
		$find_replace = $_REQUEST[ 'find_replace' ] ?: [];
		$post_selection = esc_textarea( $_REQUEST[ 'remote_post_selection' ] );
		$post_id = (int)esc_textarea( $_REQUEST[ 'manual_post_id' ] );

		if ( $post_selection === 'find' ) {
			$post_id = PostInterface::find_post( $remote_post_data[ 'slug' ] );
		}

		if ( $post_selection === 'new' ) {
			$post_id = PostInterface::create_blank_post($remote_post_data['type']);
		}

		if ( !$post_id ) wp_send_json_error( 'Post not found' );

		$local_post = new PostInterface( $post_id );
		$local_post->set_post_data( $remote_post_data, $find_replace );
		wp_send_json_success();
	}

	/**
	 *
	 */
	public function ajax_nopriv_accept_pull_request()
	{
		$key = esc_textarea( $_REQUEST[ 'key' ] );
		$valid = $this->validate_key( $key );
		if ( !$valid ) wp_send_json_error( 'Key did not validate' );

		if(!get_option( 'wpsp_allow_pull' )) wp_send_json_error( 'This site does not allow Pull' );

		$post_selection = esc_textarea( $_REQUEST[ 'remote_post_selection' ] );
		$slug = esc_textarea( $_REQUEST[ 'post_slug' ] );
		$post_id = (int)esc_textarea( $_REQUEST[ 'manual_post_id' ] );

		if ( $post_selection === 'find' ) {
			$post_id = PostInterface::find_post( $slug );
		}

		if ( !$post_id ) wp_send_json_error( 'Post not found' );

		$local_post = new PostInterface( $post_id );
		$local_data = $local_post->get_post_data();
		wp_send_json_success( $local_data );
	}

}