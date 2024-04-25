<?php

namespace Pondermatic\Strategy11\Challenge\PHPUnit;

class Mock_Data {
	public function __construct() {
		// Catch calls to WP_Http::request() and return a mocked response
		// to avoid making an actual call to the remote server.
		add_filter(
			'pre_http_request',
			[ $this, 'mock_response' ]
		);
	}

	/**
	 * Returns a mocked response from WP_Http::request().
	 *
	 * @since 1.0.0
	 */
	public function mock_response(): array {
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
				'rows'  => [
					'1' => [
						'id'    => '71',
						'fname' => 'Liam',
						'lname' => 'Neeson',
						'email' => 'skills@test.com',
						'date'  => '13626000',
					],
					'2' => [
						'id'    => '90',
						'fname' => 'Shean',
						'lname' => 'Connery',
						'email' => 'bond@test.com',
						'date'  => '13626900',
					],
					'3' => [
						'id'    => '56',
						'fname' => 'Jason',
						'lname' => 'Statham',
						'email' => 'FrankMartin@test.com',
						'date'  => '13626970',
					]
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
}
