<?php


namespace SergeLiatko\WPSettings;

use SergeLiatko\WPSettings\Traits\IsEmpty;

/**
 * Class Section
 *
 * @package SergeLiatko\WPSettings
 */
class Section {

	use IsEmpty;

	/**
	 * @var string $id Section ID. Optional, defaults to 'default'.
	 */
	protected $id;

	/**
	 * @var string Section parent page slug. Optional, defaults to 'general'.
	 */
	protected $page;

	/**
	 * @var string Section title.
	 */
	protected $title;

	/**
	 * @var string Section description text or html code.
	 *             Optional, defaults to empty string. Will be passed through wpautop($description).
	 */
	protected $description;

	/**
	 * @var callable $callback Section display callback function.
	 *                         Optional, defaults to \SergeLiatko\WPSettings\Section::display().
	 */
	protected $callback;

	/**
	 * @var \SergeLiatko\WPSettings\Setting[] $settings Array of settings to add to this section.
	 *                                                  Optional, defaults to empty array.
	 *                                                  Values must be arrays of parameters for Setting.
	 */
	protected $settings;

	/**
	 * Section constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		/**
		 * @var string   $id
		 * @var string   $page
		 * @var string   $title
		 * @var string   $description
		 * @var callable $callback
		 * @var array[]  $settings
		 */
		extract( wp_parse_args( (array) $args, $this->getDefaultParameters() ), EXTR_OVERWRITE );
		$this->setId( $id );
		$this->setPage( $page );
		$this->setTitle( $title );
		$this->setDescription( $description );
		$this->setCallback( $callback );
		//register before instantiating settings
		add_action( 'admin_menu', array( $this, 'register' ), 10, 0 );
		//instantiate settings
		$this->setSettings( $settings );
	}


	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return Section
	 */
	public function setId( $id = 'default' ) {
		if ( $this->isEmpty( $id = sanitize_key( $id ) ) ) {
			$id = 'default';
		}
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * @param string $page
	 *
	 * @return Section
	 */
	public function setPage( $page = 'general' ) {
		if ( $this->isEmpty( $page = strval( $page ) ) ) {
			$page = 'general';
		}
		$this->page = $page;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return Section
	 */
	public function setTitle( $title = '' ) {
		$this->title = trim( strval( $title ) );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 *
	 * @return Section
	 */
	public function setDescription( $description = '' ) {
		$this->description = trim( strval( $description ) );

		return $this;
	}

	/**
	 * @return callable
	 */
	public function getCallback() {
		return $this->callback;
	}

	/**
	 * @param callable $callback
	 *
	 * @return Section
	 */
	public function setCallback( $callback = null ) {
		if ( ! is_callable( $callback ) ) {
			$callback = array( $this, 'display' );
		}
		$this->callback = $callback;

		return $this;
	}

	/**
	 * @return array|\SergeLiatko\WPSettings\Setting[]
	 * @noinspection PhpUnused
	 */
	public function getSettings() {
		if ( ! is_array( $this->settings ) ) {
			$this->setSettings( array() );
		}

		return $this->settings;
	}

	/**
	 * @param array[] $settings
	 *
	 * @return Section
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function setSettings( array $settings = array() ) {
		//filter settings
		$settings = array_filter( $settings, function ( $item ) {
			return ! empty( $item ) && is_array( $item );
		} );
		//instantiate settings
		array_walk(
			$settings,
			function ( &$item, $key, $defaults ) {
				$item = new Setting( wp_parse_args( $item, $defaults ) );
			},
			array(
				'page'    => $this->getPage(),
				'section' => $this->getId(),
			)
		);
		//store instantiated settings in an array( Setting ID => Setting Instance )
		$new_settings = array();
		foreach ( $settings as $setting ) {
			if ( $setting instanceof Setting ) {
				$new_settings[ $setting->getId() ] = $setting;
			}
		}
		//set settings
		$this->settings = $new_settings;

		return $this;
	}

	/**
	 * Displays section in WordPress UI.
	 */
	public function display() {
		do_action( "before_setting_section-{$this->getId()}-{$this->getPage()}", $this );
		if ( ! $this->isEmpty( $description = $this->getDescription() ) ) {
			echo wpautop( $description );
		}
		do_action( "after_setting_section-{$this->getId()}-{$this->getPage()}", $this );
	}

	/**
	 * Registers setting section in WordPress UI.
	 */
	public function register() {
		add_settings_section(
			$this->getId(),
			$this->getTitle(),
			$this->getCallback(),
			$this->getPage()
		);
	}

	/**
	 * @return array Default parameters for \SergeLiatko\WPSettings\Section.
	 */
	public function getDefaultParameters() {
		return array(
			'id'          => 'default',
			'page'        => 'general',
			'title'       => '',
			'description' => '',
			'callback'    => array( $this, 'display' ),
			'settings'    => array(),
		);
	}

}
