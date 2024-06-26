<?php
/**
 * Test_Challenge_API class definition.
 *
 * @since 1.0.0
 * @version 1.0.1
 */

namespace Pondermatic\Strategy11Challenge\PHPUnit\unit_tests;

use Pondermatic\Strategy11Challenge\Challenge_API;
use Pondermatic\Strategy11Challenge\PHPUnit\Mock_Data;
use Pondermatic\WordpressPhpunitFramework\Test_Case;

/**
 * Tests features involving the remote API.
 *
 * @since 1.0.0
 */
class Test_Challenge_API extends Test_Case {
	/**
	 * Instance of the Challenge_API class.
	 *
	 * @since 1.0.0
	 * @var Challenge_API
	 */
	protected Challenge_API $api;

	/**
	 * A timestamp of when a transient should expire,
	 * or null to not change the timestamp.
	 *
	 * @since 1.0.0
	 * @var int|null
	 */
	protected ?int $timeout = null;

	/**
	 * The transient key name for caching the response data.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $transient_name;

	/**
	 * Returns the expiration timeout property if set, else the given value.
	 *
	 * @since 1.0.0
	 * @param mixed $timeout A Unix timestamp of when the cached response should expire.
	 * @return int|null|false
	 */
	public function get_timeout( mixed $timeout ): int|null|false {
		return $this->timeout ?? $timeout;
	}

	/**
	 * This method is called before each test.
	 *
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function setUp(): void {
		$this->api            = new Challenge_API();
		$this->transient_name = $this->api->get_transient_name();

		// Clear the cache.
		delete_site_transient( $this->transient_name );

		/**
		 * Allow the cache expiration to be changed.
		 *
		 * @use self::timeout
		 * @see get_site_transient()
		 * @see get_network_option()
		 */
		$option = "_site_transient_timeout_{$this->transient_name}";
		add_filter( "pre_site_option_{$option}", [ $this, 'get_timeout' ] );
	}

	/**
	 * Are requests to the remote API limited to 1 per hour?
	 *
	 * @since 1.0.0
	 */
	public function test_remote_request_frequency(): void {
		$filter_count = did_filter( 'pre_http_request' );
		new Mock_Data();

		// After the cached data is cleared, the next call should make one request to
		// the remote API.
		$this->api->get_challenge_response_body();
		$actual = did_filter( 'pre_http_request' ) - $filter_count;
		$this->assertEquals(
			expected: 1,
			actual: $actual,
			message: 'Challenge_API::get_challenge_response_body() should make ' .
				'one remote API request when the cached data is cleared.'
		);

		// A call made 59 minutes and 55 seconds after the data was cached
		// should return the cached data and not make a remote API request.
		$this->timeout = time() + HOUR_IN_SECONDS - 55;
		$this->api->get_challenge_response_body();
		$this->assertEquals(
			expected: 1,
			actual: did_filter( 'pre_http_request' ) - $filter_count,
			message: 'Challenge_API::get_challenge_response_body() should NOT ' .
				'make a remote API request before the cached data has expired.',
		);

		// After the cached data has expired, the next call should make one
		// request to the remote API.
		$this->timeout = time() - HOUR_IN_SECONDS - 5;
		$this->api->get_challenge_response_body();
		$this->assertEquals(
			expected: 2,
			actual: did_filter( 'pre_http_request' ) - $filter_count,
			message: 'Challenge_API::get_challenge_response_body() should have ' .
				'have called WP_Http::request() after the cached data expired.'
		);
	}

	/**
	 * Test that JSON validation is working.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function test_json_schema_validation(): void {
		$mock_data = new Mock_Data();

		// Valid data.
		$body   = $mock_data->get_default_body();
		$result = $this->api->get_challenge_response_body();
		$this->assertEqualsCanonicalizing( json_decode( wp_json_encode( $body ) ), $result );

		$functions = [
			'/title, The data (integer) must match the type: string'             =>
				function ( $body ) {
					$body['title'] = 96;
					return $body;
				},
			'/data/rows/1/fname, The data (integer) must match the type: string' =>
				function ( $body ) {
					$body['data']['rows']['1']['fname'] = 100;
					return $body;
				},
			'/data/rows/1/id, The data (string) must match the type: integer'    =>
				function ( $body ) {
					$body['data']['rows']['1']['id'] = '71';
					return $body;
				},
			'/data/rows/1, The required properties (id) are missing'             =>
				function ( $body ) {
					unset( $body['data']['rows']['1']['id'] );
					return $body;
				},
		];

		$error_message = __(
			'The fetched user data did not pass JSON schema validation tests.',
			'pondermatic-strategy11-challenge'
		);

		foreach ( $functions as $message => $function ) {
			$this->api->clear_cached_response();
			$body = $mock_data->get_default_body();

			$body = call_user_func( $function, $body );

			$mock_data->set_body( $body );
			$result = $this->api->get_challenge_response_body();
			$this->assertWPError( $result );

			$actual_message = $result->get_error_message();
			$this->assertEquals( $error_message, $actual_message, $message );

			$formatted      = $result->get_error_data();
			$actual_message = key( $formatted ) . ', ' . current( $formatted );
			$this->assertEquals( $message, $actual_message );
		}
	}
}
