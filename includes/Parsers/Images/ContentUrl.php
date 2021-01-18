<?php
namespace WPSP\Parsers\Images;

use WPSP\PostInterface;

class ContentUrl
{
	public function __construct()
	{
		add_filter('wpsp_find_images', [$this, 'handle'], 10, 2);
	}

	public function handle($images, PostInterface $interface)
	{
		$content_images = $interface->find_local_images( $interface->get_content() );
		foreach ( $content_images as $content_image_index => $content_image ) {
			$content_image[ 'location' ] = 'content';
			$images[] = $content_image;
		}
	}
}

new ContentUrl;
