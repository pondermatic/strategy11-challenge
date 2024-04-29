<?php
/**
 * Images class definition.
 *
 * @since   1.0.0
 * @version 1.0.0
 */

namespace Pondermatic\Strategy11Challenge;

defined( 'ABSPATH' ) || exit;

/**
 * Dynamic image helper class.
 *
 * @since 1.0.0
 */
class Images {
	/**
	 * Returns an SVG logo for this plugin.
	 *
	 * @since 1.0.0
	 * @param array $attributes Image attributes.
	 */
	public static function svg_logo( array $attributes = [] ): string {
		$defaults   = [
			'height' => 18,
			'width'  => 18,
			'fill'   => '#4d4d4d',
			'orange' => '#f05a24',
		];
		$attributes = array_merge( $defaults, $attributes );
		foreach ( $attributes as &$attribute ) {
			$attribute = esc_attr( $attribute );
		}

		return <<<HEREDOC
<svg xmlns="http://www.w3.org/2000/svg"
	viewBox="0 0 599.68 601.37"
	width="{$attributes['width']}"
	height="{$attributes['height']}"
>
	<path fill="{$attributes['orange']}" d="M289.6 384h140v76h-140z"/>
	<path fill="{$attributes['fill']}" d="M400.2 147h-200c-17 0-30.6 12.2-30.6 29.3V218h260v-71zM397.9 264H169.6v196h75V340H398a32.2 32.2 0 0 0 30.1-21.4 24.3 24.3 0 0 0 1.7-8.7V264zM299.8 601.4A300.3 300.3 0 0 1 0 300.7a299.8 299.8 0 1 1 511.9 212.6 297.4 297.4 0 0 1-212 88zm0-563A262 262 0 0 0 38.3 300.7a261.6 261.6 0 1 0 446.5-185.5 259.5 259.5 0 0 0-185-76.8z"/>
</svg>
HEREDOC;
	}
}
