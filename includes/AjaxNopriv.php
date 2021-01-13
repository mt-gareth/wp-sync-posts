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
	 *
	 */
	private function validate_key()
	{
		$key = esc_textarea( $_REQUEST[ 'key' ] );
		$valid = $key === get_option( 'wpsp_key' );
		if ( !$valid ) wp_send_json_error( 'Key did not validate' );
	}

	private function validate_sig( $filtered_post )
	{
		$valid = RemoteSiteInterface::verify_signature( $filtered_post, get_option( 'wpsp_key' ) );
		if ( !$valid ) wp_send_json_error( 'Sig did not validate' );
	}

	private function filter_post_elements( $post_array, $accepted_elements )
	{
		if ( isset( $post_array[ 'form_data' ] ) ) {
			$post_array[ 'form_data' ] = stripslashes( $post_array[ 'form_data' ] );
		}
		$accepted_elements[] = 'sig';
		return array_intersect_key( $post_array, array_flip( $accepted_elements ) );
	}

	/**
	 *
	 */
	public function ajax_nopriv_validate()
	{
		$filtered_post = $this->filter_post_elements( $_POST, [ 'action', 'key' ] );
		$this->validate_key();
		$this->validate_sig( $filtered_post );

		$data = [
			'allow_pull' => get_option( 'wpsp_allow_pull' ),
			'allow_push' => get_option( 'wpsp_allow_push' ),
		];
		$data[ 'sig' ] = RemoteSiteInterface::create_signature( $data, get_option( 'wpsp_key' ) );
		wp_send_json_error( 'Key is valid' );
	}

	/**
	 *
	 */
	public function ajax_nopriv_accept_push_request()
	{
		$filtered_post = $this->filter_post_elements( $_POST, [ 'action', 'key', 'local_post_data', 'find_replace', 'remote_post_selection', 'manual_post_id' ] );
		$this->validate_key();
		$this->validate_sig( $filtered_post );

		if ( !get_option( 'wpsp_allow_push' ) ) wp_send_json_error( 'This site does not allow Push' );

		$remote_post_data = $_REQUEST[ 'local_post_data' ];
		$find_replace = $_REQUEST[ 'find_replace' ] ?: [];
		$post_selection = esc_textarea( $_REQUEST[ 'remote_post_selection' ] );
		$post_id = (int)esc_textarea( $_REQUEST[ 'manual_post_id' ] );

		if ( $post_selection === 'find' ) {
			$post_id = PostInterface::find_post( $remote_post_data[ 'slug' ] );
		}

		if ( $post_selection === 'new' ) {
			$post_id = PostInterface::create_blank_post( $remote_post_data[ 'type' ] );
		}

		if ( !$post_id ) wp_send_json_error( 'Post not found' );

		$local_post = new PostInterface( $post_id );
		$local_post->set_post_data( $remote_post_data, $find_replace );
		$data = [
			'local_post_id' => $post_id,
		];
		$data[ 'sig' ] = RemoteSiteInterface::create_signature( $data, get_option( 'wpsp_key' ) );
		wp_send_json_success($data);
	}

	/**
	 *
	 */
	public function ajax_nopriv_accept_pull_request()
	{
		$filtered_post = $this->filter_post_elements( $_POST, [ 'action', 'key', 'remote_post_selection', 'post_slug', 'manual_post_id' ] );
		$this->validate_key();
		$this->validate_sig( $filtered_post );

		if ( !get_option( 'wpsp_allow_pull' ) ) wp_send_json_error( 'This site does not allow Pull' );

		$post_selection = esc_textarea( $_REQUEST[ 'remote_post_selection' ] );
		$slug = esc_textarea( $_REQUEST[ 'post_slug' ] );
		$post_id = (int)esc_textarea( $_REQUEST[ 'manual_post_id' ] );

		if ( $post_selection === 'find' ) {
			$post_id = PostInterface::find_post( $slug );
		}

		if ( !$post_id ) wp_send_json_error( 'Post not found' );

		$local_post = new PostInterface( $post_id );
		$data = [
			'local_post_data' => $local_post->get_post_data(),
		];
		$data[ 'sig' ] = RemoteSiteInterface::create_signature( $data, get_option( 'wpsp_key' ) );
		wp_send_json_success($data);
	}

}