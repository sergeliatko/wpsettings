<?php


namespace SergeLiatko\WPSettings\Traits;

/**
 * Trait IsEmpty
 *
 * @package SergeLiatko\WPSettings\Traits
 */
trait IsEmpty {

	/**
	 * @param mixed|null $data
	 *
	 * @return bool
	 */
	protected function isEmpty( mixed $data = null ): bool {
		return empty( $data );
	}

}
