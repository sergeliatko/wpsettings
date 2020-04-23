<?php


namespace SergeLiatko\WPSettings\Traits;


use SergeLiatko\WPSettings\Interfaces\AdminItemInterface;

/**
 * Trait AdminItemHandler
 *
 * @package SergeLiatko\WPSettings\Traits
 */
trait AdminItemHandler {

	/**
	 * Checks if $item is a not empty array.
	 *
	 * @param mixed $item
	 *
	 * @return bool
	 */
	protected function isNotEmptyArray( $item ) {
		return ! empty( $item ) && is_array( $item );
	}

	/**
	 * Maps array keys with ids of the value instances.
	 *
	 * @param array|AdminItemInterface[] $items
	 *
	 * @return array|AdminItemInterface[]
	 */
	protected function mapIds( array $items ) {
		$new_items = array();
		foreach ( $items as $item ) {
			if ( $item instanceof AdminItemInterface ) {
				$new_items[ $item->getId() ] = $item;
			}
		}

		return $new_items;
	}

	/**
	 * Instantiates items implementing AdminItemInterface based on their parameters. Maps the keys to IDs.
	 *
	 * @param array|array[] $items    Array of parameter arrays to use for AdminItemInterface instances.
	 * @param string        $class    Name of the class to create. Must implement the AdminItemInterface.
	 * @param array         $defaults Array of default parameters to provide for the instance.
	 *
	 * @return array|\SergeLiatko\WPSettings\Interfaces\AdminItemInterface[]
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function instantiateItems( array $items, $class, array $defaults = array() ) {
		$items = array_filter( $items, array( $this, 'isNotEmptyArray' ) );
		array_walk( $items, function ( &$item, $key, $defaults ) use ( $class ) {
			$params = empty( $defaults ) ? $item : wp_parse_args( $item, $defaults );
			$item   = is_callable( $factory = array( $class, 'createInstance' ) ) ?
				call_user_func( $factory, $params )
				: null;
		}, $defaults );

		return $this->mapIds( $items );
	}

}
