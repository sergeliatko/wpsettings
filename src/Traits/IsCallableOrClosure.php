<?php


namespace SergeLiatko\WPSettings\Traits;

use Closure;

/**
 * Trait IsCallableOrClosure
 *
 * @package SergeLiatko\WPSettings\Traits
 */
trait IsCallableOrClosure {

	/**
	 * @param $maybe_callable
	 *
	 * @return bool
	 */
	protected function is_callable_or_closure( $maybe_callable ): bool {
		return ( $maybe_callable instanceof Closure ) || is_callable( $maybe_callable, true );
	}

}
