<?php

use App\Http\Models\Options;

/*----------  General Functions  ----------*/

/**
 * Get defined statuses for admin posts.
 *
 * @return array
 */
function get_post_statuses() {
	return [
		'public' => 'Public',
		'private' => 'Private',
	];
}

/**
 * Get option value by name.
 *
 * @param string $name
 * @return string
 */
function get_option( $name ) {
	$result = '';

	$options = Options::all()->toArray();

	if ( is_array($options) && !empty($options) ) {
		foreach ( $options as $option ) {
			if ( $option['name'] == $name ) {				
				$result = $option['value'];
				break;
			}
		}
	}

	return $result;
}

/*----------  Imported Functions  ----------*/

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source https://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array() ) {
	$url  = 'https://www.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";

	if ( $img ) {
		$url = '<img src="' . $url . '"';

		foreach ( $atts as $key => $val ) {
			$url .= ' ' . $key . '="' . $val . '"';
		}

		$url .= ' />';
	}

	return $url;
}
