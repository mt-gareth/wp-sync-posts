<?php

namespace WPSP\Parsers\Images;

use WPSP\PostInterface;

class FeaturedImage
{
	public function __construct()
	{
		add_filter('wpsp_find_images', [$this, 'handle'], 10, 2);
	}

	public function handle($images, PostInterface $interface)
	{
		$meta = $interface->get_meta();
		if ( array_key_exists( '_thumbnail_id', $meta ) ) {
			$val = $meta[ '_thumbnail_id' ][ 0 ];
			$images[] = [ 'location' => '_thumbnail_id', 'current_value' => $val, 'image_url' => wp_get_original_image_url( $val ) ];
		}
		return $images;
	}
}

new FeaturedImage;
