<?php
/**
 * Shortcode class definition.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11\Challenge;

use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a WordPress shortcode to display the data from our AJAX endpoint.
 *
 * @since 1.0.0
 */
class Shortcode {
	/**
	 * Use this shortcode in a post to trigger rendering of the challenge data.
	 *
	 * @since 1.0.0
	 */
	protected string $shortcode = 'pondermatic-strategy11-challenge';

	/**
	 * An instance of the class that allows the data to be viewed.
	 *
	 * @since 1.0.0
	 */
	protected View_Data $view;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'init' ] );

		add_shortcode( $this->shortcode, [ $this, 'shortcode_handler' ] );
	}

	/**
	 * Get ready to render the shortcode.
	 *
	 * This must be called after the global $post is set
	 * and before the shortcode is handled.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		if ( $this->is_shortcode_used( $this->shortcode ) === false ) {
			return;
		}
		$this->view = new View_Data();
	}

	/**
	 * Return `true` if the current post contains the given shortcode, else `false`.
	 *
	 * @since 1.0.0
	 * @global WP_Post $post
	 * @param string   $shortcode
	 * @return bool
	 */
	protected function is_shortcode_used( string $shortcode ): bool {
		global $post;
		return is_singular() &&
			   is_a( $post, 'WP_Post' ) &&
			   has_shortcode( $post->post_content, $shortcode );
	}

	/**
	 * Executes Javascript to contact this plugin's AJAX endpoint
	 * and display the data returned formatted into a table-like display.
	 *
	 * @since 1.0.0
	 * @return string The rendered output of the shortcode.
	 */
	public function shortcode_handler(): string {
		$challenge_data = $this->view->render();
		return <<<HEREDOC
<div class="psc-wrap">
	$challenge_data
</div>

HEREDOC;
	}
}
