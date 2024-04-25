<?php

namespace Pondermatic\Strategy11\Challenge\PHPUnit;

use PHPUnit\Framework\TestCase;
use Pondermatic\Strategy11\Challenge\Challenge_API;

class Test_Challenge_API extends TestCase {
	/**
	 * @since 1.0.0
	 */
	protected Challenge_API $api;

	/**
	 * A timestamp of when a transient should expire,
	 * or null to not change the timestamp.
	 *
	 * @since 1.0.0
	 */
	protected ?int $timeout = null;

	/**
	 * @since 1.0.0
	 */
	protected string $transient_name;

	/**
	 * @since 1.0.0
	 */
	public function get_timeout( $timeout ): int|false {
		return $this->timeout ?? $timeout;
	}

	/**
	 * @inheritDoc
	 * @since 1.0.0
	 */
	public function setUp(): void {
		$this->api            = new Challenge_API();
		$this->transient_name = $this->api->get_transient_name();

		// Clear the cache.
		delete_site_transient( $this->transient_name );

		// Allow the cache expiration to be changed.
		$option = "_site_transient_timeout_{$this->transient_name}";
		/**
		 * @use self::timeout
		 * @see get_site_transient()
		 * @see get_network_option()
		 */
		add_filter( "pre_site_option_{$option}", [ $this, 'get_timeout' ] );
	}

	/**
	 * Are requests to the remote API limited to 1 per hour?
	 *
	 * @since 1.0.0
	 */
	public function test_remote_request_frequency(): void {
		new Mock_Data();

		// After the cached data is cleared, the next call should make one request to
		// the remote API.
		$this->api->get_challenge_response_body();
		$actual = did_filter( 'pre_http_request' );
		$this->assertEquals(
			expected: 1,
			actual: $actual,
			message: 'Challenge_API::get_challenge_response_body() should make ' .
					 "one remote API request when the cached data is cleared."
		);

		// A call made 59 minutes and 55 seconds after the data was cached
		// should return the cached data and not make a remote API request.
		$this->timeout = time() + HOUR_IN_SECONDS - 55;
		$this->api->get_challenge_response_body();
		$this->assertEquals(
			expected: 1,
			actual: did_filter( 'pre_http_request' ),
			message: 'Challenge_API::get_challenge_response_body() should NOT ' .
					 'make a remote API request before the cached data has expired.',
		);

		// After the cached data has expired, the next call should make one
		// request to the remote API.
		$this->timeout = time() - HOUR_IN_SECONDS - 5;
		$this->api->get_challenge_response_body();
		$this->assertEquals(
			expected: 2,
			actual: did_filter( 'pre_http_request' ),
			message: 'Challenge_API::get_challenge_response_body() should have ' .
					 'have called WP_Http::request() after the cached data expired.'
		);
	}

	public function test_returned_data() {

	}
}
