<?php
/**
 * Plugin Name: Crosspost
 * Description: Automatically add latest posts from other WordPress sites with a shortcode like [crosspost url="example.com" postnumber="3"]
 * Version: 0.1.0
 * Author: Laurence Bahiirwa 
 * Author URI: https://omukiguy.com
 * Plugin URI: https://github.com/bahiirwa/crosspost
 * Text Domain: crosspost
 * Requires at least: 4.9
 * Tested up to: 5.4.1
 * 
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 *
 */

namespace bahiirwa\Crosspost;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Crosspost {
	/**
	 * Add action to process shortcodes.
	 *
	 * @since 0.1.0
	 * @since 2.0.0 The function is now static.
	 *
	 */
	public static function register() {
		add_shortcode( 'crosspost', [ __CLASS__, 'process_shortcode' ] );
	}

	/**
	 * Process shortcode.
	 *
	 * This public function processes the cp_release_link shortcode into HTML markup.
	 *
	 * @since 0.1.0
	 * @since 2.0.0 The function is now static.
	 *
	 * @param array $atts Shortcode arguments.
	 * @return string $html
	 */
	public static function process_shortcode( $atts ) {
		// Default values for when not passed in shortcode.
		$defaults = [
			'url'  => '',
			'characters'  => '150',
			'readmoretext'  => 'Read more',
			'postnumber'  => '3'
		];

		// Replace any missing shortcode arguments with defaults.
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'crosspost'
		);

		// Validate the user and the repo.
		if ( empty( $atts['url'] ) ) {
			return '<p>[crosspost] Missing URL</p>';
		}

		// Get the release data from External Website.
		$release_data = self::get_release_data_cached( $atts );
		if ( is_wp_error( $release_data ) ) {
			return (
				'<!-- [crosspost] '
				. esc_html( $release_data->get_error_message() )
				. ' -->'
			);
		}
			
		foreach ( $release_data as $data ) {
			
			$html .= (
				'<div class="crosspost-plugin" id="' . $data['id'] . '">' .
					'<img class="featured-image" src="' . $data['featured_image_src'] . '" />' .
					'<h3>' . $data['title']['rendered'] . '</h3>' .
					'<div class="content">' . self::reduce_content( $data['content']['rendered'], $atts['characters'] ) . '</div>' .
					'<div class="post-meta">' .
						'<span class="date">' . self::convert_date_to_human( $data['date'] ) . '</span>' .
						'<span class="author">' . $data['author_info']['display_name'] . '</span>' .
					'</div>' .
					'<a href="' . $data['link'] . '">' . $atts['readmoretext'] . '</a>' .
				'</div>' 
			);
				
		}

		/**
		 * Filters the HTML for the release link.
		 *
		 * @since 2.0.0
		 *
		 * @param string $html The link HTML.
		 * @param array  $atts The full array of shortcode attributes.
		 */
		return apply_filters( 'crosspost_link', $html, $atts );
	}

	public static function reduce_content( $string, $characters ) {
		if ( strlen( $string ) > 10 ) {
			return esc_textarea( sanitize_text_field( $string = substr( $string , 0, $characters ) ) ); 
		}
	}

	public static function convert_date_to_human( $date ) {
		return $date = date( 'l jS M Y g:ia ', strtotime( $date ) );
	}

	/**
	 * Fetch release data from External Website or return it from a cached value.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return array|\WP_Error Release data from External Website, or an error object.
	 */
	public static function get_release_data_cached( $atts ) {
		// Get any existing copy of our transient data
		$release_data = get_transient( self::get_transient_name( $atts ) );

		if ( empty( $release_data ) ) {
			$release_data = self::get_release_data( $atts );

			if ( is_wp_error( $release_data ) ) {
				return $release_data;
			}

			// Save release data in transient inside DB to reduce network calls.
			set_transient(
				self::get_transient_name( $atts ),
				$release_data,
				15 * MINUTE_IN_SECONDS
			);
		}

		return $release_data;
	}

	/**
	 * Return the name of the transient that should be used to cache the
	 * release information for a repository.
	 *
	 * @since 1.2.0
	 * @since 2.0.0 The function is now static, and the transient names have
	 * changed because the full release data is stored instead of just the URL
	 * to a zip file.
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return string Transient name to use for caching this repository.
	 */
	public static function get_transient_name( $atts ) {
		return (
			'crosspost_link_'
			. substr( md5( $atts['url'] ), 0, 16 )
		);
	}

	/**
	 * Fetch release data from External Website.
	 *
	 * @since 2.0.0
	 *
	 * @internal - use self::get_release_data_cached() instead.
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return array|\WP_Error Release data from External Website, or an error object.
	 */
	private static function get_release_data( $atts ) {
		// Build the External Website API URL for the latest release.
		$api_url = ( $atts['url'] . '/wp-json/wp/v2/posts'
		);

		// Make API call.
		$response = wp_remote_get( esc_url_raw( $api_url ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Parse the JSON response from the API into an array of data.
		$response_body = wp_remote_retrieve_body( $response );
		$response_json = json_decode( $response_body, true );
		$status_code = wp_remote_retrieve_response_code( $response );

		if ( empty( $response_json ) || $status_code !== 200 ) {
			return new \WP_Error(
				'invalid_data',
				'Invalid data returned from External Website',
				[
					'code' => $status_code,
					'body' => empty( $response_json ) ? $response_body : $response_json,
				]
			);
		}

		return $response_json;
	}

	/**
	 * Given a set of release data from the External Website API, return a release zip URL.
	 *
	 * @since 2.0.0
	 *
	 * @param array $release_data Release data from the External Website API.
	 * @return string URL of latest zip release file on External Website.
	 */
	// public static function get_zip_url_for_release( $release_data ) {
	// 	// If any files were uploaded for this release, use the first one.
	// 	// TODO: Allow specifying which file to use somehow (name regex?)
	// 	if ( ! empty( $release_data['assets'] ) ) {
	// 		return $release_data['assets'][0]['browser_download_url'];
	// 	}

	// 	// Otherwise, build a URL based on the tag name of the latest release.
	// 	$version = $release_data['tag_name'];

	// 	// Extract the user and repo name from the External Website API URL.
	// 	preg_match(
	// 		'#^https://api\.External Website\.com/repos/([^/]+)/([^/]+)/releases/#',
	// 		$release_data['url'],
	// 		$matches
	// 	);
	// 	$user = $matches[1];
	// 	$repo = $matches[2];

	// 	return (
	// 		'https://External Website.com/'
	// 		. $user . '/' . $repo
	// 		. '/archive/' . $version . '.zip'
	// 	);
	// }
}

Crosspost::register();
