<?php
/**
 * Endpoint class definition.
 *
 * @since   1.0.0
 * @version 1.0.1
 */

namespace Pondermatic\Strategy11\Challenge;

use Exception;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use stdClass;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a WordPress API endpoint for the challenge API.
 *
 * @since 1.0.0
 */
class Challenge_API {

	/**
	 * The API's route.
	 *
	 * @since 1.0.1
	 */
	const ROUTE = '/challenge';

	/**
	 * The action to encode into the nonce.
	 *
	 * @since 1.0.1
	 * @var string
	 */
	protected string $nonce_clear_action = 'clear cached remote challenge response';

	/**
	 * The variable name of the nonce.
	 *
	 * @since 1.0.1
	 * @var string
	 */
	protected string $nonce_clear_name = 'psc-clear-nonce';

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
	 * Returns true if the user is allowed to clear the cached remote challenge response.
	 *
	 * @since 1.0.1
	 */
	public function can_clear_cache_response(): bool {
		// Only WordPress admins with a valid nonce field
		// and WP-CLI users may clear the cache.
		$given_nonce = sanitize_text_field( wp_unslash( $_REQUEST[ $this->nonce_clear_name ] ?? '' ) );
		if (
			( is_admin() && wp_verify_nonce( $given_nonce, $this->nonce_clear_action ) ) ||
			( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Deletes the cached remote challenge response.
	 *
	 * @since 1.0.0
	 * @return bool True on success or not cached, false on failure.
	 */
	public function clear_cached_response(): bool {
		if ( $this->can_clear_cache_response() === false ) {
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
			return json_decode( $cached_value );
		}

		/* @noinspection HttpUrlsUsage */
		$response = wp_remote_get( 'http://api.strategy11.com/wp-json/challenge/v1/1' );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$challenge_response = json_decode( $response['body'], associative: false );
		$json_error         = json_last_error();
		if ( $json_error !== JSON_ERROR_NONE ) {
			return new WP_Error( $json_error, json_last_error_msg() );
		}

		$validation_result = $this->validate_json_data_opis( $challenge_response );
		if ( is_wp_error( $validation_result ) ) {
			return $validation_result;
		}

		set_site_transient(
			$this->get_transient_name(),
			wp_json_encode( $challenge_response ),
			HOUR_IN_SECONDS
		);
		return $challenge_response;
	}

	/**
	 * Returns a nonce field for clearing the cached remote challenge response.
	 *
	 * @since 1.0.1
	 */
	public function get_clear_nonce_field(): string {
		return wp_nonce_field(
			action: $this->nonce_clear_action,
			name: $this->nonce_clear_name,
			display: false
		);
	}

	/**
	 * Adds a nonce for clearing the cached remote challenge response
	 * to the given URL.
	 *
	 * @since 1.0.1
	 * @param string $url The URL that the nonce should be appended to.
	 * @return string
	 */
	public function get_clear_nonce_url( string $url ): string {
		return wp_nonce_url(
			actionurl: $url,
			action: $this->nonce_clear_action,
			name: $this->nonce_clear_name,
		);
	}

	/**
	 * Returns an empty response object.
	 *
	 * @since 1.0.1
	 * @return stdClass
	 */
	public function get_empty_response(): stdClass {
		$table                = new stdClass();
		$table->title         = '';
		$table->data          = new stdClass();
		$table->data->headers = [];
		$table->data->rows    = new stdClass();
		return $table;
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

	/**
	 * Returns true if the given JSON data is valid according to a schema, else false.
	 *
	 * @since 1.0.1
	 * @param stdClass $json_data The data to validate.
	 * @return bool|WP_Error
	 */
	protected function validate_json_data_opis( stdClass $json_data ): bool|WP_Error {
		$validator = new Validator();
		$validator->resolver()->registerFile(
			'https://strategy11.com/schemas/users',
			__DIR__ . '/../schemas/users.json'
		);
		try {
			$result = $validator->validate( $json_data, 'https://strategy11.com/schemas/users' );
		} catch ( Exception $exception ) {
			// Opis\JsonSchema\Validator does not set the exception code,
			// which causes WordPress to return an empty WP_Error object.
			return new WP_Error( 1, $exception->getMessage() );
		}
		if ( $result->isValid() ) {
			return true;
		} else {
			$message    = __(
				'The fetched user data did not pass JSON schema validation tests.',
				'pondermatic-strategy11-challenge'
			);
			$formatter  = new ErrorFormatter();
			$error_data = $formatter->format( error: $result->error(), multiple: false );
			return new WP_Error( 1, $message, $error_data );
		}
	}
}
