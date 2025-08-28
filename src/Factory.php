<?php


namespace SergeLiatko\WPSettings;

/**
 * Class Factory
 *
 * @package SergeLiatko\WPSettings
 */
class Factory {

	/**
	 * Creates new instance of object implementing AdminItemInterface.
	 *
	 * @param array $params Array of parameters to create new instance of object implementing AdminItemInterface.
	 * @param string $default Class name to use to create the instance if ['_class'] is missing in $params.
	 *
	 * @return object
	 */
	public static function createItem( array $params, string $default ): object {
		$class = empty( $params['_class'] ) ? $default : $params['_class'];
		unset( $params['_class'] );

		return new $class( $params );
	}

}
