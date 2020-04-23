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
	 * @var array|array[]|\SergeLiatko\WPSettings\Page[]
	 */
	protected $pages;

	/**
	 * @var array|array[]|\SergeLiatko\WPSettings\Section[]
	 */
	protected $sections;

	/**
	 * @var array|array[]|\SergeLiatko\WPSettings\Setting[]
	 */
	protected $settings;

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
		) ), EXTR_OVERWRITE );
		$this->setPages( $pages );
		$this->setSections( $sections );
		$this->setSettings( $settings );
	}

	/**
	 * @return array|array[]|\SergeLiatko\WPSettings\Page[]
	 * @noinspection PhpUnused
	 */
	public function getPages() {
		return $this->pages;
	}

	/**
	 * @param array|array[] $pages
	 *
	 * @return UI
	 */
	public function setPages( array $pages ) {
		$this->pages = $this->instantiateItems(
			$pages,
			'\\SergeLiatko\\WPSettings\\Page'
		);

		return $this;
	}

	/**
	 * @return array|array[]|\SergeLiatko\WPSettings\Section[]
	 * @noinspection PhpUnused
	 */
	public function getSections() {
		return $this->sections;
	}

	/**
	 * @param array|array[] $sections
	 *
	 * @return UI
	 */
	public function setSections( array $sections ) {
		$this->sections = $this->instantiateItems(
			$sections,
			'\\SergeLiatko\\WPSettings\\Section'
		);

		return $this;
	}

	/**
	 * @return array|array[]|\SergeLiatko\WPSettings\Setting[]
	 * @noinspection PhpUnused
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @param array|array[] $settings
	 *
	 * @return UI
	 */
	public function setSettings( array $settings ) {
		$this->settings = $this->instantiateItems(
			$settings,
			'\\SergeLiatko\\WPSettings\\Setting'
		);

		return $this;
	}
}
