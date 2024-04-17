<?php
/**
 * The core class for this plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11\Challenge;

/**
 * Core class definition.
 *
 * @since 1.0.0
 */
class Core {

	const ROUTE_NAMESPACE = 'pondermatic-strategy11/v1';

	const VERSION = '1.0.0';

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
		if ( ! is_admin() ) {
			new Shortcode();
		}
		self::$challenge_api = new Challenge_API();
	}
}
