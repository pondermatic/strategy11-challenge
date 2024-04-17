<?php
/**
 * Shortcode class definition.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11\Challenge;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a WordPress shortcode to display the data from our AJAX endpoint.
 *
 * @since 1.0.0
 */
class Shortcode {
	const SHORTCODE = 'pondermatic-strategy11-challenge';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_shortcode( self::SHORTCODE, [ $this, 'shortcode_handler' ] );
	}

	/**
	 * Adds scripts to the page.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts(): void {
		if ( wc_post_content_has_shortcode( self::SHORTCODE ) ) {
			wp_enqueue_script(
				'psc-shortcode',
				plugin_dir_url( __DIR__ ) . 'assets/js/psc-shortcode.js',
				[
					'jquery',
					'wp-api',
				],
				Core::VERSION,
				[
					'in_footer' => false,
				]
			);
		}
	}

	/**
	 * Executes Javascript to contact this plugin's AJAX endpoint
	 * and display the data returned formatted into a table-like display.
	 *
	 * @since 1.0.0
	 * @param string[]|empty $attributes An associative array of attributes, or an empty string if
	 *                                   no attributes are given.
	 * @param string         $content    The enclosed content, if the shortcode is used in its
	 *                                   enclosing form.
	 * @param string         $tag        The shortcode tag, useful for shared callback functions.
	 * @return string The rendered output of the shortcode.
	 */
	public function shortcode_handler( array|string $attributes, string $content, string $tag ): string {
		$debug    = 0 ? '?XDEBUG_SESSION_START=psc' : '';
		$rest_url = Core::$challenge_api->get_this_endpoint() . $debug;
		$data     = 0 ? print_r( json_decode( wp_remote_get( $rest_url )['body'] ), true ) : '';
		return <<<HEREDOC
<div id="psc-display">
<pre>$data</pre>
<h2 class="psc-table-title"></h2>
<table class="psc-table wp-list-table widefat fixed">
<thead></thead>
<tbody></tbody>
</table>
</div>
<script>psc.shortcode.load( "$rest_url" );</script>
HEREDOC;
	}
}
