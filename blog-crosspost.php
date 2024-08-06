<?php
/**
 * Plugin Name:       Blog Crosspost
 * Description:       Automatically add posts from another WordPress website using the REST API with a shortcode like [blogcrosspost url="example.com"]
 * Version:           0.2.2
 * Author:            Laurence Bahiirwa 
 * Author URI:        https://omukiguy.com
 * Plugin URI:        https://github.com/bahiirwa/blogcrosspost
 * Text Domain:       blogcrosspost
 * Requires at least: 4.9
 * Tested up to:      6.6.1
 * 
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 *
 */

namespace bahiirwa\Blogcrosspost;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Get image from WordPress REST API.

class Blogcrosspost {
	/**
	 * Add action to process shortcodes.
	 *
	 * @since 0.1.0
	 * @since 2.0.0 The function is now static.
	 *
	 */
	public static function register() {
		add_shortcode( 'blogcrosspost', [ __CLASS__, 'process_shortcode' ] );
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
			'url'          => '',
			'image_size'   => 'full',
			'characters'   => '150',
			'readmoretext' => 'Read more',
			'number'       => '3',
			'class'        => 'blogcrosspost-plugin blog-crosspost-item',
		];

		// Replace any missing shortcode arguments with defaults.
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'blogcrosspost'
		);

		// Validate the user and the repo.
		if ( empty( $atts['url'] ) ) {
			$html = '<p>Add the Missing URL. The shortcode should be as such [blogcrosspost url="add url link goes here"]</p>';
			return apply_filters( 'blogcrosspost_missing_url', $html );
		}

		// Get the release data from External Website.
		$release_data = self::get_release_data_cached( $atts );

		// return json_encode($release_data);

		if ( is_wp_error( $release_data ) ) {
			
			$html = (
				'<!-- [blogcrosspost] '
				. esc_html( $release_data->get_error_message() )
				. ' -->'
			);

			return apply_filters( 'blogcrosspost_release_data', $html, $release_data );
		}

		$count = 0;
		$html  = '';

		foreach ( $release_data as $data ) {

			// When your count is at $atts['number'] "continue" is to go to the end of the loop.
			if ( $atts['number'] == $count++ ) break;

			$author = '';
			if ( ! empty( $data['author_info']['display_name'] ) ) {
				$author = $data['author_info']['display_name'];
			}
			
			$html .= (
				'<h2>' . $count . '</h2>' .
                '<div class="' . esc_attr( $atts['class'] ) . '" id="' . esc_attr( $data['id'] ) . '">' .
					self::get_image_processed_from_api( $atts['image_size'], $atts['url'], $data['featured_media'] ) .
					'<h3>' . esc_attr( $data['title']['rendered'] ) . '</h3>' .
					'<div class="content">' . esc_attr( self::reduce_content( $data['content']['rendered'], $atts['characters'] ) ) . '</div>' .
					'<div class="post-meta">' .
						'<span class="date">' . esc_attr( self::convert_date_to_human( $data['date'] ) ) . '</span>' .
						self::get_author_processed_from_api( $author ) .
					'</div>' .
					'<a href="' . esc_url( $data['link'] ) . '">' . esc_attr( $atts['readmoretext'] ) . '</a>' .
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
		return apply_filters( 'blogcrosspost_link', $html, $atts, $data );
	}

	/**
	 * Get Image displayed from API.
	 *
	 * @param array $url               URL of the Image in REST API.
     * @param array $featured_media_id Featured Media ID.
     *
	 * @return string $image Image URL.
	 */
	private static function get_image_processed_from_api( $size, $url, $featured_media_id ) {

        $featured_image_url = "{$url}/wp-json/wp/v2/media/{$featured_media_id}";
        $response           = wp_remote_get( $featured_image_url );

        if ( is_array( $response ) && ! is_wp_error( $response ) ) {
            $body          = $response['body']; // use the content
            $response_body = json_decode($body, true);
            $image_src     = ( 'full' === $size ) ? $response_body['guid']['rendered'] : $response_body['media_details']['sizes'][ $size ]['source_url'];
            $caption       = $response_body['caption']['rendered'];
            $alt_text      = $response_body['alt_text'];
        }

		if ( ! empty( $image_src ) || null != $image_src ) {
			$image = '<figure>
                        <img class="featured-image" src="' . esc_url( $image_src ) . '" alt="' . esc_attr( $alt_text ) . '" />
                        <figcaption>' . esc_attr( $caption ) . '</figcaption>
                      </figure>';
		}
		
		return $image;

	}

	/**
	 * Get author displayed from API.
	 *
	 * @param integer $author_info Author ID.
	 *
	 * @return string $author Author URL.
	 */
	private static function get_author_processed_from_api( $author_info ) {

		$author = '';

		if ( ! empty( $author_info ) || null != $author_info ) {
			$author = '<span class="author">' . esc_attr( $author ) . '</span>';
		}
		
		return $author;

	}

	/**
	 * Trim the content block to show on the post item.
	 *
	 * @param string $string  Post Content Block
	 * @param int    $characters Number of characters to show on the post block.
	 * @return void
	 */	
	public static function reduce_content( $string, $characters ) {
		if ( strlen( $string ) > 10 ) {
			return esc_textarea( sanitize_text_field( $string = substr( $string , 0, $characters ) ) ); 
		}
	}

	/**
	 * Convert date to human readble time.
	 *
	 * @param date   $date
	 * @return void
	 */
	public static function convert_date_to_human( $date ) {
		return $date = date( 'l jS M Y g:ia ', strtotime( $date ) );
	}

	/**
	 * Fetch release data from External Website or return it from a cached value.
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Array containing 'url' arguments.
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
	 * release information for a repository. The function is static, and the transient names have
	 * changed because the full release data is stored instead of just the URL
	 * to a zip file.
	 *
	 * @since 0.1.0 
	 *
	 * @param array $atts Array containing 'user' and 'repo' arguments.
	 * @return string Transient name to use for caching this repository.
	 */
	public static function get_transient_name( $atts ) {
		return (
			'blogcrosspost_link_'
			. substr( md5( $atts['url'] ), 0, 16 )
		);
	}

	/**
	 * Fetch release data from External Website.
	 *
	 * @since 0.1.0
	 *
	 * @internal - use self::get_release_data_cached() instead.
	 *
	 * @param array $atts Array containing 'url' arguments.
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

}

blogcrosspost::register();
