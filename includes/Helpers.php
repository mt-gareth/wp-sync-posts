<?php

namespace WPSP;

class Helpers
{
	public static function find_shortcodes( $content, $shortcode )
	{
		$pattern = '~\[' . $shortcode . ' (.*?)\]~';
		preg_match_all( $pattern, $content, $match, PREG_SET_ORDER );
		if ( !count( $match ) ) return [];

		$galleries = [];
		foreach ( $match as $match_group ) {
			$galleries[] = $match_group[ 0 ];
		}
		return $galleries;
	}
}
