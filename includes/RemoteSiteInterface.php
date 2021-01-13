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
		$this->ajax_url = $this->url . '/wp-admin/admin-ajax.php';
		$this->key = $key;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function send( $data )
	{
		$data['sig'] = self::create_signature($data, $this->key);
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
			return [ 'success' => false, 'data' => 'There was an error trying to connect to remote url: ' . $this->ajax_url ];
		}

		if ( $response['body'] === '0' ) {
			return [ 'success' => false, 'data' => 'WP Sync Posts Does not seem to be activated on the remote site.' ];
		}

		$return = json_decode( $response[ 'body' ], true );
		if(!$return['success']) return $return;

		if(!self::verify_signature($return['data'], $this->key)) {
			return [ 'success' => false, 'data' => 'Invalid content verification signature, please verify the connection information on the remote site and try again.' ];
		}

		return $return;
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



	public static function create_signature( $data, $key ) {
		if ( isset( $data['sig'] ) ) {
			unset( $data['sig'] );
		}
		$flat_data = implode( '', $data );
		return base64_encode( hash_hmac( 'sha1', $flat_data, $key, true ) );
	}

	public static function verify_signature( $data, $key ) {
		if( empty( $data['sig'] ) ) {
			return false;
		}
		if ( isset( $data['nonce'] ) ) {
			unset( $data['nonce'] );
		}
		$temp = $data;
		$computed_signature = self::create_signature( $temp, $key );
		return $computed_signature === $data['sig'];
	}

}