<?php

namespace Pondermatic\Strategy11\Challenge\PHPUnit;

use ParagonIE\Sodium\Core\Curve25519\H;
use PHPUnit\Framework\TestCase;
use Pondermatic\Strategy11\Challenge\Challenge_API;
use stdClass;

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
	 * Returns a mocked response from WP_Http::request().
	 *
	 * @since 1.0.0
	 */
	protected function mock_response(): array {
		$body = [
			'title' => 'To the person who stole my copy of Microsoft Office. ' .
					   'I will find you. You have my Word.',
			'data'  => [
				'headers' => [
					'ID',
					'First Name',
					'Last Name',
					'Email',
					'Date'
				],
			],
			'rows'  => [
				'1' => [
					'id'    => '71',
					'fname' => 'Liam',
					'lname' => 'Neeson',
					'email' => 'skills@test.com',
					'date'  => '13626000',
				],
			],
		];

		return [
			'headers'       => [],
			'body'          => json_encode( $body ),
			'response'      => [
				'code'    => false,
				'message' => false,
			],
			'cookies'       => [],
			'http_response' => null,
		];
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
		// Catch calls to WP_Http::request() and return a mocked response
		// to avoid making an actual call to the remote server.
		add_filter( 'pre_http_request', function () {
			return $this->mock_response();
		} );

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