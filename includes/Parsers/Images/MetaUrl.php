<?php

namespace WPSP\Parsers\Images;

use WPSP\PostInterface;

class MetaUrl
{
	public function __construct()
	{
		add_filter('wpsp_find_images', [$this, 'handle'], 10, 2);
	}

	public function handle($images, PostInterface $interface)
	{
		$meta = $interface->get_meta();
		foreach ( $meta as $meta_key => $meta_array ) {
			foreach ( $meta_array as $meta_val ) {
				$meta_images = $interface->find_local_images( $meta_val );
				if ( empty( $meta_images ) ) continue;
				foreach ( $meta_images as $meta_image_index => $meta_image ) {
					$meta_image[ 'location' ] = $meta_key;
					$images[] = $meta_image;
				}
			}
		}
		return $images;
	}
}

new MetaUrl;
