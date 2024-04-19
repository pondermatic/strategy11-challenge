<?php
/**
 * Admin class definition.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11\Challenge;

defined( 'ABSPATH' ) || exit;

/**
 * Displays the administrator page for this plugin and handles forms submitted from it.
 *
 * @since 1.0.0
 */
class Admin {
	/**
	 * The menu slug and admin page name.
	 *
	 * @since 1.0.0
	 */
	protected string $menu_slug = 'pondermatic-strategy11-challenge';

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
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Adds a menu item for this plugin on the WP admin navigation menu.
	 *
	 * @since 1.0.0
	 */
	public function add_menu(): void {
		add_menu_page(
			page_title: __( 'Strategy11 Challenge', 'pondermatic-strategy11-challenge' ),
			menu_title: __( 'Challenge', 'pondermatic-strategy11-challenge' ),
			capability: 'manage_options',
			menu_slug: $this->menu_slug,
			callback: [ $this, 'render_page' ],
			icon_url: 'data:image/svg+xml;base64,' . base64_encode( Images::svg_logo( [
				'fill'   => '#a0a5aa',
				'orange' => '#a0a5aa',
			] ) ),
			position: 29,
		);
	}

	/**
	 * Get ready to display the admin page.
	 *
	 * This must be called before the global $plugin_page is set
	 * and before this admin menu page callback is called.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		if ( $this->is_current_page() === false ) {
			return;
		}
		add_filter( 'admin_body_class', [ $this, 'add_admin_body_classes' ], 999 );
		$this->view = new View_Data();
	}

	/**
	 * Adds a class to the body element of an admin page.
	 *
	 * @since 1.0.0
	 * @param string $classes
	 * @return string
	 */
	public function add_admin_body_classes( string $classes ): string {
		return "$classes psc-admin";
	}

	/**
	 * Returns `true` if the current plugin admin page is this one, else `false`.
	 *
	 * @since 1.0.0
	 */
	protected function is_current_page(): bool {
		global $plugin_page;
		return $plugin_page === $this->menu_slug;
	}

	/**
	 * Displays the admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_page(): void {
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'refresh' ) {
			Core::$challenge_api->clear_cached_response();
			wp_redirect( get_admin_url() . "admin.php?page={$this->menu_slug}" );
			exit;
		}

		$header_text    = __( 'Challenge Data', 'pondermatic-strategy11-challenge' );
		$logo           = Images::svg_logo( [ 'height' => '35', 'width' => '35' ] );
		$button_text    = __( 'Refresh', 'pondermatic-strategy11-challenge' );
		$challenge_data = $this->view->render();
		$refresh_url    = get_admin_url() . "admin.php?page={$this->menu_slug}&action=refresh";
		echo <<<HEREDOC
<div class="psc-wrap">
	<div class="psc-nav-bar">
		<div class="psc-logo">
			$logo
		</div>
		<div class="psc-nav-left">
			<h1>$header_text</h1>
		</div>
		<div class="psc-nav-middle">
		</div>
		<div class="psc-nav-right">
			<a href="$refresh_url" class="psc-button-primary">$button_text</a>
		</div>
	</div>
	$challenge_data
</div>
HEREDOC;
	}
}