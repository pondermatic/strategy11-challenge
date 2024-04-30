<?php
/**
 * Bootstrap class
 *
 * @since 1.0.0
 * @version 1.0.1
 */

namespace Pondermatic\Strategy11Challenge\PHPUnit;

use Pondermatic\WordpressPhpunitFramework\Bootstrap as FrameworkBootstrap;

/**
 * PHPUnit bootstrap.
 *
 * @since 1.0.0
 */
class Bootstrap extends FrameworkBootstrap {

	/**
	 * Main PHP File for the plugin.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_main = 'pondermatic-strategy11-challenge.php';

	/**
	 * Install the plugin being tested and any plugins it is integrated with.
	 *
	 * @since 1.0.0
	 */
	public function install() {
	}

	/**
	 * Manually load the plugin being tested and any dependencies.
	 *
	 * @since 1.0.0
	 */
	public function load(): void {

		parent::load();
	}
}
