<?php
/**
 * Admin class definition.
 *
 * @since   1.0.0
 * @version 1.0.1
 */

namespace Pondermatic\Strategy11Challenge;

defined( 'ABSPATH' ) || exit;

/**
 * Displays the administrator page for this plugin and handles forms submitted from it.
 *
 * @since 1.0.0
 */
class Admin {
	/**
	 * Displays the challenge data in a table.
	 *
	 * @since 1.0.0
	 * @var Data_List_Table
	 */
	protected Data_List_Table $list_table;
	/**
	 * The menu slug and admin page name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $menu_slug = 'pondermatic-strategy11-challenge';

	/**
	 * An instance of the class that allows the data to be viewed.
	 *
	 * @since 1.0.0
	 * @var View_Data
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

		/**
		 * Instantiate a table object when the admin page is for this plugin.
		 *
		 * @see wp-admin/admin.php `do_action( "load-{$page_hook}" );`
		 */
		add_action(
			'load-toplevel_page_pondermatic-strategy11-challenge',
			function () {
				$this->list_table = new Data_List_Table();
			}
		);
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
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			icon_url: 'data:image/svg+xml;base64,' . base64_encode(
				Images::svg_logo(
					[
						'fill'   => '#a0a5aa',
						'orange' => '#a0a5aa',
					]
				)
			),
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
		wp_enqueue_style( handle: 'psc' );
	}

	/**
	 * Adds a class to the body element of an admin page.
	 *
	 * @since 1.0.0
	 * @param string $classes Class names separated by a space.
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
	 * @since 1.0.1
	 */
	public function render_page(): void {
		$query_args = [];
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nonce not available.
		if ( isset( $_GET['orderby'] ) ) {
			$query_args['orderby'] = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		}
		if ( isset( $_GET['order'] ) ) {
			$query_args['order'] = sanitize_text_field( wp_unslash( $_GET['order'] ) );
		}
		// phpcs:enable

		/**
		 * Nonce is verified by the Challenge_API::can_clear_cache_response() function.
		 * phpcs:disable WordPress.Security.NonceVerification.Recommended
		 */
		if (
			isset( $_GET['refresh'] ) &&
			Core::$challenge_api->can_clear_cache_response()
		) {
			Core::$challenge_api->clear_cached_response();
			wp_safe_redirect(
				add_query_arg( $query_args, remove_query_arg( 'refresh' ) )
			);
			exit;
		}
		// phpcs:enable

		$header_text_escaped = esc_html__(
			'Challenge Data',
			'pondermatic-strategy11-challenge'
		);
		$logo_escaped        = Images::svg_logo(
			[
				'height' => '35',
				'width'  => '35',
			]
		);
		$button_text_escaped = esc_html__(
			'Refresh',
			'pondermatic-strategy11-challenge'
		);

		$query_args['refresh'] = '';
		$refresh_url_escaped   = Core::$challenge_api->get_clear_nonce_url(
			add_query_arg( $query_args )
		);

		$this->list_table->prepare_items();
		$this->list_table->views();
		ob_start();
		$this->list_table->display();
		$challenge_data = ob_get_clean();

		// phpcs:disable WordPress.Security.EscapeOutput.HeredocOutputNotEscaped -- All output is escaped.
		echo <<<HEREDOC
<div class="psc-wrap">
	<div class="psc-nav-bar">
		<div class="psc-logo">
			$logo_escaped
		</div>
		<div class="psc-nav-left">
			<h1>$header_text_escaped</h1>
		</div>
		<div class="psc-nav-middle">
		</div>
		<div class="psc-nav-right">
			<a href="$refresh_url_escaped" class="psc-button-primary">$button_text_escaped</a>
		</div>
	</div>
	<div class="wrap">
		$challenge_data
	</div>
</div>
HEREDOC;
	}
}
