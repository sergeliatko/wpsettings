<?php


namespace SergeLiatko\WPSettings\Traits;

/**
 * Trait IsEmpty
 *
 * @package SergeLiatko\WPSettings\Traits
 */
trait IsEmpty {

	/**
	 * @param mixed $data
	 *
	 * @return bool
	 */
	protected function isEmpty( $data = null ) {
		return empty( $data );
	}

}
