<?php
/**
 * The core class for this plugin.
 *
 * @since   1.0.0
 * @version 1.0.1
 */

namespace Pondermatic\Strategy11Challenge;

defined( 'ABSPATH' ) || exit;

/**
 * Core class definition.
 *
 * @since 1.0.0
 */
class Core {

	const ROUTE_NAMESPACE = 'pondermatic-strategy11/v1';

	const VERSION = '1.0.1';

	/**
	 * An instance of the Challenge_API.
	 *
	 * @since 1.0.0
	 * @var Challenge_API
	 */
	public static Challenge_API $challenge_api;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'register_assets' ] );
		add_action( 'plugins_loaded', [ $this, 'load_plugin_text_domain' ] );

		// Initialize classes.
		if ( is_admin() ) {
			new Admin();
		} else {
			new Shortcode();
		}
		self::$challenge_api = new Challenge_API();
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI_Clear_Cached_Response();
		}
	}

	/**
	 * Registers CSS and JavaScript files to be enqueued later if needed.
	 *
	 * @since 1.0.0
	 */
	public static function register_assets(): void {
		$assets_dir = plugin_dir_url( __DIR__ ) . 'assets';
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_register_style( handle: 'psc', src: "$assets_dir/css/psc.css", ver: self::VERSION );
		wp_register_script(
			'psc-shortcode',
			"$assets_dir/js/psc-shortcode.js",
			[
				'jquery',
				'wp-api',
				'wp-date',
				'wp-escape-html',
				'wp-i18n',
			],
			self::VERSION,
			[
				'in_footer' => false,
			]
		);
		wp_set_script_translations(
			'psc-shortcode',
			'pondermatic-strategy11-challenge',
			dirname( __DIR__ ) . '/languages'
		);
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_text_domain(): void {
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			domain: 'pondermatic-strategy11-challenge',
			plugin_rel_path: dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/',
		);
	}
}
