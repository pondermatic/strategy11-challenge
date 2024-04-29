<?php

namespace Pondermatic\Strategy11\Challenge\PHPUnit;

class Mock_Data {

	/**
	 * The HTTP response body to return.
	 *
	 * @since 1.0.1
	 * @var array
	 */
	protected array $body = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->set_body( $this->get_default_body() );

		// Catch calls to WP_Http::request() and return a mocked response
		// to avoid making an actual call to the remote server.
		add_filter(
			'pre_http_request',
			[ $this, 'mock_response' ]
		);
	}

	/**
	 * The default response body.
	 *
	 * @since 1.0.1
	 * @return array
	 */
	public function get_default_body(): array {
		return [
			'title' => 'To the person who stole my copy of Microsoft Office. ' .
				'I will find you. You have my Word.',
			'data'  => [
				'headers' => [
					'ID',
					'First Name',
					'Last Name',
					'Email',
					'Date',
				],
				'rows'    => [
					'1' => [
						'id'    => 71,
						'fname' => 'Liam',
						'lname' => 'Neeson',
						'email' => 'skills@test.com',
						'date'  => 13626000,
					],
					'2' => [
						'id'    => 90,
						'fname' => 'Sean',
						'lname' => 'Connery',
						'email' => 'bond@test.com',
						'date'  => 13626900,
					],
					'3' => [
						'id'    => 56,
						'fname' => 'Jason',
						'lname' => 'Statham',
						'email' => 'FrankMartin@test.com',
						'date'  => 13626970,
					],
				],
			],
		];
	}

	/**
	 * Returns a mocked response from WP_Http::request().
	 *
	 * @since 1.0.0
	 */
	public function mock_response(): array {

		return [
			'headers'       => [],
			'body'          => wp_json_encode( $this->body ),
			'response'      => [
				'code'    => false,
				'message' => false,
			],
			'cookies'       => [],
			'http_response' => null,
		];
	}

	/**
	 * Sets the response body.
	 *
	 * @param array $get_body The response body array.
	 * @return $this
	 */
	public function set_body( array $get_body ): self {
		$this->body = $get_body;
		return $this;
	}
}
