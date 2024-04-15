<?php
/**
 * Plugin Name:          Pondermatic Strategy11 Challenge
 * Plugin URI:           https://github.com/pondermatic/strategy11-challenge
 * Description:          Adds a WordPress endpoint to return data from a Strategy11 challenge API.
 * Version:              1.0.0
 * Requires at least:    5.8
 * Requires PHP:         7.4
 * Author:               Pondermatic
 * Author URI:           https://www.pondermatic.com/
 * Copyright:            2024 Pondermatic
 * License:              GPLv3
 * License URI:          http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:          pondermatic-strategy11-challenge
 * Domain Path:          /languages
 */

/**
 * This file is part of Pondermatic Strategy11 Challenge.
 *
 * Pondermatic Strategy11 Challenge is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * Pondermatic Strategy11 Challenge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Pondermatic Strategy11 Challenge. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Pondermatic\Strategy11\Challenge;

defined( 'ABSPATH' ) || exit;

// Register an autoloader for this plugin.
require_once __DIR__ . '/vendor/autoload.php';

new Core();
