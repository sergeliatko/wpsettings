<?php


namespace SergeLiatko\WPSettings\Traits;


use SergeLiatko\WPSettings\Interfaces\AdminItemInterface;
use Throwable;
use WP_Exception;

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
	protected function isNotEmptyArray( mixed $item ): bool {
		return is_array( $item ) && ! empty( $item );
	}

	/**
	 * Maps array keys with ids of the value instances.
	 *
	 * @param array|AdminItemInterface[] $items
	 *
	 * @return array|AdminItemInterface[]
	 */
	protected function mapIds( array $items ): array {
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
	 * @param array|array[] $items Array of parameter arrays to use for AdminItemInterface instances.
	 * @param string $class Name of the class to create. Must implement the AdminItemInterface.
	 * @param array $defaults Array of default parameters to provide for the instance.
	 *
	 * @return array|AdminItemInterface[]
	 * @throws WP_Exception When instantiation of an item fails.
	 */
	protected function instantiateItems( array $items, string $class, array $defaults = array() ): array {
		$items = array_filter( $items, array( $this, 'isNotEmptyArray' ) );
		array_walk( $items, function ( &$item, $key, $defaults ) use ( $class ) {
			try {
				$params = empty( $defaults ) ? $item : wp_parse_args( $item, $defaults );
				$item   = method_exists( $class, 'createInstance' ) ?
					$class::createInstance( $params ) :
					null;
			} catch ( Throwable $e ) {
				$item = null;
				// add debug log
				wp_trigger_error(
					__METHOD__,
					sprintf( "Error creating instance of %s: %s\n", $class, $e->getMessage() )
				);
			}
		}, $defaults );

		return $this->mapIds( array_filter(
			$items,
			fn( mixed $item ) => $item instanceof AdminItemInterface
		) );
	}

}
