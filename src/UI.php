<?php


namespace SergeLiatko\WPSettings;


use SergeLiatko\WPSettings\Traits\AdminItemHandler;

/**
 * Class UI
 *
 * @package SergeLiatko\WPSettings
 */
class UI {

	use AdminItemHandler;

	/**
	 * @var array|array[]|Page[]
	 */
	protected array $pages;

	/**
	 * @var array|array[]|Section[]
	 */
	protected array $sections;

	/**
	 * @var array|array[]|Setting[]
	 */
	protected array $settings;

	/**
	 * UI constructor.
	 *
	 * @param array $params
	 */
	public function __construct( array $params ) {

		/**
		 * @var array|array[] $pages
		 * @var array|array[] $sections
		 * @var array|array[] $settings
		 */
		extract( wp_parse_args( $params, array(
			'pages'    => array(),
			'sections' => array(),
			'settings' => array(),
		) ) );
		$this->setPages( $pages );
		$this->setSections( $sections );
		$this->setSettings( $settings );
	}

	/**
	 * @return array|array[]|Page[]
	 * @noinspection PhpUnused
	 */
	public function getPages(): array {
		return $this->pages;
	}

	/**
	 * @param array|array[] $pages
	 *
	 * @return UI
	 */
	public function setPages( array $pages ): UI {
		$this->pages = $this->instantiateItems(
			$pages,
			'\\SergeLiatko\\WPSettings\\Page'
		);

		return $this;
	}

	/**
	 * @return array|array[]|Section[]
	 * @noinspection PhpUnused
	 */
	public function getSections(): array {
		return $this->sections;
	}

	/**
	 * @param array|array[] $sections
	 *
	 * @return UI
	 */
	public function setSections( array $sections ): UI {
		$this->sections = $this->instantiateItems(
			$sections,
			'\\SergeLiatko\\WPSettings\\Section'
		);

		return $this;
	}

	/**
	 * @return array|array[]|Setting[]
	 * @noinspection PhpUnused
	 */
	public function getSettings(): array {
		return $this->settings;
	}

	/**
	 * @param array|array[] $settings
	 *
	 * @return UI
	 */
	public function setSettings( array $settings ): UI {
		$this->settings = $this->instantiateItems(
			$settings,
			'\\SergeLiatko\\WPSettings\\Setting'
		);

		return $this;
	}

}
