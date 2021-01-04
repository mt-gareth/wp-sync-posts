<?php

namespace WPSP;

use WP_Error;

class PostInterface
{
	/**
	 * @var int
	 */
	private $post_id;
	/**
	 * @var \WP_Post|null
	 */
	private $post;
	/**
	 * @var array
	 */
	private $post_data;

	/**
	 * PostInterface constructor. Sets up the local post that will be worked with.
	 * @param int|string $post_id
	 */
	public function __construct( $post_id )
	{
		$this->post_id = (int)$post_id;
		$this->post = get_post( $post_id );
		$this->post_data = $this->get_post_data();
	}

	/**
	 * Returns an array of the metas with the key the meta_key and the values an array of meta values
	 * @return array
	 */
	public function get_meta()
	{
		return get_post_meta( $this->post_id ) ?: [];
	}

	/**
	 * Returns the title of the post
	 * @return string
	 */
	public function get_title()
	{
		return $this->post->post_title;
	}

	/**
	 * Returns the slug of the post
	 * @return string
	 */
	public function get_slug()
	{
		return $this->post->post_name;
	}

	/**
	 * Returns the post type
	 * @return false|string
	 */
	public function get_type()
	{
		return get_post_type( $this->post );
	}

	/**
	 * Returns the post content as it is in the DB without filtering
	 * @return string
	 */
	public function get_content()
	{
		return $this->post->post_content;
	}

	/**
	 * Returns an array of all the terms with the key as the taxonomy
	 * @return array
	 */
	public function get_all_terms()
	{
		$all_terms = [];
		foreach ( get_object_taxonomies( $this->post ) as $tax ) {
			$all_terms[ $tax ] = get_the_terms( $this->post, $tax );
		}
		return $all_terms;
	}

	/**
	 * Generates an array of all the images it can find, either through regex
	 * looking for image urls in the content or meta, for acf Image or Gallery fields,
	 * and it also grabs the featured image if set
	 * @return array
	 */
	public function get_all_images()
	{
		$images = [];
		//loop through all the meta and content looking for image urls that have the same domain as this site
		$content_images = $this->find_local_images( $this->get_content() );
		foreach ( $content_images as $content_image_index => $content_image ) {
			$content_image[ 'location' ] = 'content';
			$images[] = $content_image;
		}

		$meta = $this->get_meta();
		foreach ( $meta as $meta_key => $meta_array ) {
			foreach ( $meta_array as $meta_val ) {
				$meta_images = $this->find_local_images( $meta_val );
				if ( empty( $meta_images ) ) continue;
				foreach ( $meta_images as $meta_image_index => $meta_image ) {
					$meta_image[ 'location' ] = $meta_key;
					$images[] = $meta_image;
				}
			}
		}

		//if we have ACF lets loop through the field keys and look for galleries and image fields, so we can swap out IDs
		if ( function_exists( 'get_field_object' ) ) {
			foreach ( $meta as $meta_key => $meta_array ) {
				foreach ( $meta_array as $meta_val ) {
					//if $meta_val !begins with 'field_' continue
					if ( substr( $meta_val, 0, 6 ) !== 'field_' || substr( $meta_key, 0, 1 ) !== '_' ) continue;
					$original_key = substr( $meta_key, 1 );
					$field_object = get_field_object( $meta_val, $this->post_id, false, false );
					$val = get_field( $original_key, $this->post_id, false );
					if ( !$val ) continue;
					if ( !$field_object ) continue;
					switch ( $field_object[ 'type' ] ) {
						case 'image':
							$images[] = [ 'location' => $original_key, 'current_value' => $val, 'image_url' => wp_get_original_image_url( $val ) ];
							break;
						case 'gallery':
							foreach ( $val as $gallery_image ) {
								$images[] = [ 'location' => $original_key, 'current_value' => $gallery_image, 'image_url' => wp_get_original_image_url( $gallery_image ) ];
							}
							break;
						//TODO add action to allow for other fields to be grabbed
					}
				}
			}
		}

		//handle featured image
		if ( array_key_exists( '_thumbnail_id', $meta ) ) {
			$val = $meta[ '_thumbnail_id' ][ 0 ];
			$images[] = [ 'location' => '_thumbnail_id', 'current_value' => $val, 'image_url' => wp_get_original_image_url( $val ) ];
		}

		//TODO add action to allow for other images to be found

		return $images;
	}

	/**
	 * looks for image urls in a string and returns an array with the original file url and the old url.
	 * @param string $string
	 * @return array
	 */
	public function find_local_images( $string )
	{
		$images = [];
		$pattern = '~(http.*\.)(jpe?g|png|[tg]iff?|svg)~i';
		preg_match_all( $pattern, $string, $matches );
		$matches = array_unique( $matches );
		foreach ( $matches[ 0 ] as $match_url ) {
			$match_id = $this->get_attachment_id( $match_url );
			if ( !$match_id ) continue;
			$images[] = [ 'current_value' => $match_url, 'image_url' => wp_get_original_image_url( $match_id ) ];
		}
		return $images;
	}

	/**
	 * @param string $url
	 * @return bool|int|\WP_Post
	 */
	public function get_attachment_id( $url )
	{

		//bail if the url does not contain the uploads directory
		$dir = wp_upload_dir();
		if ( false === strpos( $url, $dir[ 'baseurl' ] . '/' ) ) {
			return false;
		}

		//first we try to find it by matching file name to file name
		$file = basename( $url );
		$query = [
			'post_type'  => 'attachment',
			'fields'     => 'ids',
			'meta_query' => [
				[
					'key'     => '_wp_attached_file',
					'value'   => $file,
					'compare' => 'LIKE',
				],
			]
		];

		$ids = get_posts( $query );

		if ( !empty( $ids ) ) {

			foreach ( $ids as $id ) {
				//check if the urls match
				if ( $url === array_shift( wp_get_attachment_image_src( $id, 'full' ) ) )
					return $id;
			}
		}

		//if it didnt find a direct match it might be a wordpress generated resize of an image
		$query[ 'meta_query' ][ 0 ][ 'key' ] = '_wp_attachment_metadata';

		// query attachments again
		$ids = get_posts( $query );

		//if I still cant find it it must not exist
		if ( empty( $ids ) )
			return false;

		foreach ( $ids as $id ) {
			//loop though all the sizes and see if it is a match
			$meta = wp_get_attachment_metadata( $id );
			foreach ( $meta[ 'sizes' ] as $size => $values ) {

				if ( $values[ 'file' ] === $file && $url === array_shift( wp_get_attachment_image_src( $id, $size ) ) )
					return $id;
			}
		}

		return false;
	}

	/**
	 * Gather it all up into a nice array
	 * @return array
	 */
	public function get_post_data()
	{
		return [
			'ID'      => $this->post_id,
			'type'    => $this->get_type(),
			'slug'    => $this->get_slug(),
			'title'   => $this->get_title(),
			'content' => $this->get_content(),
			'meta'    => $this->get_meta(),
			'terms'   => $this->get_all_terms(),
			'images'  => $this->get_all_images(),
		];
	}

	/**
	 * Take remote post data to make a copy of it while applying the find and replaces
	 * @param array $remote_post_data
	 * @param array $find_replace_array
	 */
	public function set_post_data( $remote_post_data, $find_replace_array )
	{
		//setup filters to jump in and perform find and replaces
		foreach ( $find_replace_array as $find_replace ) {
			add_filter( 'wpsp_filter_find_replace', function ( $text ) use ( $find_replace ) {
				if ( !is_string( $text ) ) return $text;
				return str_replace( $find_replace[ 0 ], $find_replace[ 1 ], $text );
			} );
		}

		//take provided images, either finding them or uploading them and do the find and replace
		foreach ( $remote_post_data[ 'images' ] as $image ) {
			$local_image_data = $this->find_or_upload_image( $image[ 'image_url' ] );
			$find = $image[ 'current_value' ];
			$is_find_id = is_numeric( $find );
			$replace = $is_find_id ? $local_image_data[ 'image_id' ] : $local_image_data[ 'image_url' ];
			if ( $image[ 'location' ] === 'content' ) {
				$remote_post_data[ 'content' ] = str_replace( $find, $replace, $remote_post_data[ 'content' ] );
				continue;
			}
			$remote_post_data[ 'meta' ][ $image[ 'location' ] ] = str_replace( $find, $replace, $remote_post_data[ 'meta' ][ $image[ 'location' ] ] );
		}

		//set all the post values
		$this->set_title( $remote_post_data[ 'title' ] );
		$this->set_content( $remote_post_data[ 'content' ] );
		$this->set_meta( $remote_post_data[ 'meta' ] );
		$this->set_terms( $remote_post_data[ 'terms' ] );
	}

	/**
	 * Take a url and either find the file or upload it, either way return the image id and url
	 * @param string $remote_image_url
	 * @return array
	 */
	public function find_or_upload_image( $remote_image_url )
	{
		$file_name = $this->get_file_name( $remote_image_url );
		$args = array(
			'post_type'      => 'attachment',
			'name'           => sanitize_title( $file_name ),
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
		);
		$posts = get_posts( $args );
		$post = $posts ? array_pop( $posts ) : null;
		if ( $post ) return [ 'image_url' => wp_get_attachment_url( $post->ID ), 'image_id' => $post->ID ];

		$new_image_id = $this->upload_image( $remote_image_url );
		return [ 'image_url' => wp_get_attachment_url( $new_image_id ), 'image_id' => $new_image_id ];
	}

	/**
	 * Maybe the only bug free method I have ever wrote
	 * @return bool
	 */
	public function return_true()
	{
		return true;
	}

	/**
	 * Upload an image from a url returning its ID
	 * @param string $url
	 * @return int|WP_Error
	 */
	public function upload_image( $url )
	{
		$timeout_seconds = 30;
		add_filter( 'http_request_host_is_external', [ $this, 'return_true' ] );
		$temp_file = download_url( $url, $timeout_seconds );
		remove_action( 'http_request_host_is_external', [ $this, 'return_true' ] );
		if ( !is_wp_error( $temp_file ) ) {
			$file = array(
				'name'     => basename( $url ),
				'type'     => wp_check_filetype( $temp_file ),
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);
			return media_handle_sideload( $file, $this->post_id );
		}
		return 0;
	}

	/**
	 * @param string $url
	 * @return mixed|string
	 */
	public function get_file_name( $url )
	{
		if ( strpos( $url, '?' ) !== false ) {
			$t = explode( '?', $url );
			$url = $t[ 0 ];
		}
		$path_info = pathinfo( $url );
		return $path_info[ 'filename' ];
	}

	/**
	 * @param string $title
	 */
	public function set_title( $title )
	{
		$my_post = [
			'ID'         => $this->post_id,
			'post_title' => apply_filters( 'wpsp_filter_find_replace', $title ),
		];
		wp_update_post( $my_post );
	}

	/**
	 * @param string $content
	 */
	public function set_content( $content )
	{
		$my_post = [
			'ID'           => $this->post_id,
			'post_content' => apply_filters( 'wpsp_filter_find_replace', $content ),
		];
		wp_update_post( $my_post );
	}

	/**
	 * @param array $new_meta_array
	 */
	public function set_meta( $new_meta_array )
	{
		$current_meta = $this->get_meta();
		foreach ( $new_meta_array as $new_meta_key => $new_meta_value_array ) {
			foreach ( $new_meta_value_array as $new_meta_value ) {
				if ( is_serialized( $new_meta_value ) ) {
					$new_val_array = unserialize( stripslashes( $new_meta_value ) );
					//TODO this only works for single level arrays, I need recursion at some point
					foreach ( $new_val_array as $new_val_key => $new_val ) {
						$new_val_array[ $new_val_key ] = apply_filters( 'wpsp_filter_find_replace', $new_val );
					}
					update_post_meta( $this->post_id, $new_meta_key, $new_val_array );
				} else {
					update_post_meta( $this->post_id, $new_meta_key, apply_filters( 'wpsp_filter_find_replace', $new_meta_value ) );
				}

			}
			if ( array_key_exists( $new_meta_key, $current_meta ) )
				unset( $current_meta[ $new_meta_key ] );
		}

		foreach ( $current_meta as $current_meta_key => $current_meta_value ) {
			delete_post_meta( $this->post_id, $current_meta_key, $current_meta_value );
		}

	}

	/**
	 * @param array $terms
	 */
	public function set_terms( $terms )
	{
		//TODO Set Terms
		error_log( 'I need to set terms' );
		error_log( print_r( $terms, true ) );
	}

	/**
	 * Find post_id from slug
	 * @param string $post_slug
	 * @return int|bool
	 */
	public static function find_post( $post_slug )
	{
		global $wpdb;
		$sql = 'SELECT ID FROM wp_posts WHERE post_name = "' . $post_slug . '"  ';
		$results = $wpdb->get_results( $sql );
		return count( $results ) > 0 ? (int)$results[ 0 ]->ID : false;
	}

	/**
	 * Generate a new post
	 * @param string $type
	 * @return int|WP_Error
	 */
	public static function create_blank_post( $type )
	{
		return wp_insert_post( [
			'post_status' => 'publish',
			'post_type'   => $type,
		] );
	}


}
