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
	public function getId(): string;

	/**
	 * @param array $params
	 *
	 * @return AdminItemInterface
	 */
	public static function createInstance( array $params ): AdminItemInterface;

}
