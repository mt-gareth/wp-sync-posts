<?php

namespace WPSP\Parsers\Images;

use WPSP\PostInterface;
use WPSP\Helpers;

class WPGallery
{
	public function __construct()
	{
		add_filter( 'wpsp_find_images', [ $this, 'handle' ], 10, 2 );
	}

	public function handle( $images, PostInterface $interface )
	{
		$gallery_images = [];
		$content_galleries = $this->search_for_gallery( $interface->get_content() );
		if ( !empty( $content_galleries ) ) {
			foreach ( $content_galleries as $content_gallery ) {
				$content_images = $this->get_gallery_images( $content_gallery );
				foreach ( $content_images as $image ) {
					$image[ 'location' ] = 'content';
					$image[ 'shortcode' ] = 'gallery';
					$gallery_images[] = $image;
				}
			}
		}
		$meta = $interface->get_meta();
		foreach ( $meta as $meta_key => $meta_array ) {
			foreach ( $meta_array as $meta_val ) {
				$meta_galleries = $this->search_for_gallery( $meta_val );
				if ( !empty( $meta_galleries ) ) {
					foreach ( $meta_galleries as $meta_gallery ) {
						$meta_images = $this->get_gallery_images( $meta_gallery );
						foreach ( $meta_images as $image ) {
							$image[ 'location' ] = $meta_key;
							$image[ 'shortcode' ] = 'gallery';
							$gallery_images[] = $image;
						}
					}
				}
			}
		}

		$images = [ ...$images, ...$gallery_images ];

		return $images;
	}

	private function search_for_gallery( $content )
	{
		return Helpers::find_shortcodes($content, 'gallery');
	}

	private function get_gallery_images( $content_gallery )
	{
		$pattern = '~ids="(.*?)"~';
		preg_match_all( $pattern, $content_gallery, $ids, PREG_SET_ORDER );
		$ids = explode( ',', $ids[ 0 ][ 1 ] );
		$images = [];
		foreach ( $ids as $id ) {
			$images[] = [ 'current_value' => $id, 'image_url' => wp_get_original_image_url( $id ) ];
		}
		return $images;
	}
}

new WPGallery;
