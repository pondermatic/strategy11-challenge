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

    const VERSION = '1.0.0';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Add hook callbacks for use on admin or public-facing pages.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function add_hooks() {
	}
}
