<?php

namespace WPSP\Parsers\Images;

use WPSP\PostInterface;

class ACF
{
	public function __construct()
	{
		add_filter('wpsp_find_images', [$this, 'handle'], 10, 2);
	}

	public function handle($images, PostInterface $interface)
	{
		$meta = $interface->get_meta();
		if ( function_exists( 'get_field_object' ) ) {
			foreach ( $meta as $meta_key => $meta_array ) {
				foreach ( $meta_array as $meta_val ) {
					//if $meta_val !begins with 'field_' continue
					if ( substr( $meta_val, 0, 6 ) !== 'field_' || substr( $meta_key, 0, 1 ) !== '_' ) continue;
					$original_key = substr( $meta_key, 1 );
					$field_object = get_field_object( $meta_val, $interface->get_post_id(), false, false );
					$val = get_field( $original_key, $interface->get_post_id(), false );
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
		return $images;
	}
}

new ACF;
