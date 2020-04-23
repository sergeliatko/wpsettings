<?php


namespace SergeLiatko\WPSettings\Interfaces;

/**
 * Interface adminItemInterface
 *
 * @package SergeLiatko\WPSettings\Interfaces
 */
interface AdminItemInterface {

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @param array $params
	 *
	 * @return \SergeLiatko\WPSettings\Interfaces\AdminItemInterface
	 */
	public static function createInstance( array $params );

}
