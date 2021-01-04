<?php

namespace WPSP;

class RemoteSiteInterface
{
	/**
	 * @var string
	 */
	private $url;
	/**
	 * @var string
	 */
	private $ajax_url;
	/**
	 * @var string
	 */
	private $key;

	/**
	 * RemoteSiteInterface constructor.
	 * @param string $url
	 * @param string $key
	 */
	public function __construct( $url, $key )
	{
		$this->url = $url;
		//todo find a better wat to get the admin ajax url
		$this->ajax_url = $this->url . '/wp-admin/admin-ajax.php';
		$this->key = $key;
	}

	/**
	 * @param array $data
	 * @return array|mixed
	 */
	public function send( $data )
	{
		$response = wp_remote_post( $this->ajax_url, array(
				'method'   => 'POST',
				'blocking' => true,
				'headers'  => [],
				'body'     => $data,
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			error_log( print_r( $error_message, true ) );
			return ['success' => false];
		}

		return json_decode( $response[ 'body' ], true );
	}

	/**
	 * @return array
	 */
	public function validate()
	{
		return $this->send( [
			'key'    => $this->key,
			'action' => 'wpsp_validate'
		] );
	}

	/**
	 * @param array $local_post_data
	 * @param array $find_replace
	 * @param string $remote_post_selection
	 * @param null|int|string $manual_post_id
	 * @return array
	 */
	public function send_push_request( $local_post_data, $find_replace = [], $remote_post_selection = 'find', $manual_post_id = null )
	{
		return $this->send( [
			'key'                   => $this->key,
			'action'                => 'wpsp_accept_push_request',
			'local_post_data'       => $local_post_data,
			'find_replace'          => $find_replace,
			'remote_post_selection' => $remote_post_selection,
			'manual_post_id'        => $manual_post_id
		] );
	}

	/**
	 * @param string $remote_post_selection
	 * @param null|string $post_slug
	 * @param null|int|string $manual_post_id
	 * @return array
	 */
	public function send_pull_request( $remote_post_selection = 'find', $post_slug = null, $manual_post_id = null )
	{
		return $this->send( [
			'key'                   => $this->key,
			'action'                => 'wpsp_accept_pull_request',
			'remote_post_selection' => $remote_post_selection,
			'post_slug'             => $post_slug,
			'manual_post_id'        => $manual_post_id
		] );
	}
}