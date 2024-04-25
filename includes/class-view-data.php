<?php
/**
 * View_Data class definition.
 *
 * @since    1.0.0
 * @version  1.0.1
 */

namespace Pondermatic\Strategy11\Challenge;

defined( 'ABSPATH' ) || exit;

/**
 * Displays the challenge data.
 *
 * @since 1.0.0
 */
class View_Data {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Adds scripts to the page.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script( handle: 'psc-shortcode' );
		wp_enqueue_style( handle: 'psc' );
	}

	/**
	 * Returns HTML to display the challenge data by using a JavaScript call to
	 * get and fill in the HTML.
	 *
	 * @since 1.0.0
	 */
	public function render(): string {
		$rest_url = esc_js( Core::$challenge_api->get_this_endpoint() );
		return <<<HEREDOC
<div id="psc-display">
	<h2 class="psc-table-title"></h2>
	<table class="psc-table">
		<thead></thead>
		<tbody></tbody>
	</table>
</div>
<script>psc.shortcode.load( "$rest_url" );</script>
HEREDOC;
	}
}
