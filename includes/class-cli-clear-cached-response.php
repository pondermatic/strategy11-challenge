<?php
/**
 * CLI_Clear_Cached_Response class definition.
 *
 * @since   1.0.0
 * @version 1.0.1
 */

namespace Pondermatic\Strategy11Challenge;

use Exception;
use WP_CLI;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a WordPress CLI command to clear the cached response value of the remote challenge API
 * request.
 *
 * @since 1.0.0
 */
class CLI_Clear_Cached_Response {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @throws Exception Thrown by WP_CLI::add_command().
	 */
	public function __construct() {
		WP_CLI::add_command(
			'psc-clear-cached-response',
			[ $this, 'clear_cached_response' ]
		);
	}

	/**
	 * Clears the cached remote challenge response.
	 *
	 * @since 1.0.0
	 */
	public function clear_cached_response(): void {
		if ( Core::$challenge_api->clear_cached_response() === true ) {
			WP_CLI::success( 'Cleared the last cached response.' );
		} else {
			WP_CLI::warning( 'Failed to clear the last cached response.' );
		}
	}
}
