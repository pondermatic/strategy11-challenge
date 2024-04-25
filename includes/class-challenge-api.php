<?php
/**
 * Endpoint class definition.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11\Challenge;

use stdClass;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a WordPress API endpoint for the challenge API.
 *
 * @since 1.0.0
 */
class Challenge_API {

	const ROUTE = '/challenge';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action(
			hook_name: 'rest_api_init',
			callback: [ $this, 'register_reset_route' ]
		);
	}

	/**
	 * Deletes the cached remote challenge response.
	 *
	 * @since 1.0.0
	 * @return bool True on success or not cached, false on failure.
	 */
	public function clear_cached_response(): bool {
		// Only WordPress admins and WP-CLI users may clear the cache.
		if ( ! is_admin() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return false;
		}
		if ( get_site_transient( Core::ROUTE_NAMESPACE . self::ROUTE ) === false ) {
			return true;
		}
		return delete_site_transient( Core::ROUTE_NAMESPACE . self::ROUTE );
	}

	/**
	 * Returns data from a request to the Strategy11 challenge API.
	 *
	 * @since 1.0.0
	 */
	public function get_challenge_response_body(): WP_Error|stdClass {
		$cached_value = get_site_transient( $this->get_transient_name() );
		if ( $cached_value !== false ) {
			$cached_value = json_decode( $cached_value );
			return $cached_value->response;
		}

		/* @noinspection HttpUrlsUsage */
		$response = wp_remote_get( 'http://api.strategy11.com/wp-json/challenge/v1/1' );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$challenge_response = json_decode( $response['body'], associative: false );
		// @todo Validate or sanitize challenge data using a schema.
		$json_error         = json_last_error();
		if ( $json_error === JSON_ERROR_NONE ) {
			$cached_value           = new stdClass();
			$cached_value->response = $challenge_response;
			$cached_value->time     = time();
			set_site_transient(
				$this->get_transient_name(),
				wp_json_encode( $cached_value ),
				HOUR_IN_SECONDS
			);
			return $challenge_response;
		} else {
			return new WP_Error( $json_error, json_last_error_msg() );
		}
	}

	/**
	 * Returns the URL for this plugin's challenge API endpoint.
	 *
	 * @since 1.0.0
	 */
	public function get_this_endpoint(): string {
		return get_rest_url( null, Core::ROUTE_NAMESPACE . self::ROUTE );
	}

	/**
	 * Returns the transient name used to cache the response body from the remote API.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_transient_name(): string {
		return Core::ROUTE_NAMESPACE . self::ROUTE;
	}

	/**
	 * Adds an endpoint to this server's API.
	 *
	 * @since 1.0.0
	 */
	public function register_reset_route(): void {
		// @todo Add error checking on register_rest_route().
		register_rest_route(
			route_namespace: Core::ROUTE_NAMESPACE,
			route: self::ROUTE,
			args: array(
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_challenge_response_body' ],
				'permission_callback' => '__return_true', // Can be used when logged out or in.
			)
		);
	}
}
