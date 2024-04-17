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
	 * The number of seconds that a cached response is valid.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $cache_duration = 60 * 60;

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
	 * Returns data from a request to the Strategy11 challenge API.
	 *
	 * @since 1.0.0
	 */
	public function get_challenge_response_body(): WP_Error|stdClass {
		$cached_value = get_site_transient( Core::ROUTE_NAMESPACE . self::ROUTE );
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
		$json_error         = json_last_error();
		if ( $json_error === JSON_ERROR_NONE ) {
			$cached_value           = new stdClass();
			$cached_value->response = $challenge_response;
			$cached_value->time     = time();
			set_site_transient(
				Core::ROUTE_NAMESPACE . self::ROUTE,
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
