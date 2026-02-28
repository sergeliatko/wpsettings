<?php


namespace SergeLiatko\WPSettings;

use Closure;
use SergeLiatko\WPSettings\Interfaces\AdminItemInterface;
use SergeLiatko\WPSettings\Traits\AdminItemHandler;
use SergeLiatko\WPSettings\Traits\IsCallableOrClosure;
use SergeLiatko\WPSettings\Traits\IsEmpty;
use WP_Exception;

/**
 * Class Section
 *
 * @package SergeLiatko\WPSettings
 */
class Section implements AdminItemInterface {

	use AdminItemHandler, IsCallableOrClosure, IsEmpty;

	/**
	 * @var string $id Section ID. Optional, defaults to 'default'.
	 */
	protected string $id;

	/**
	 * @var string Section parent page slug. Optional, defaults to 'general'.
	 */
	protected string $page;

	/**
	 * @var string Section title.
	 */
	protected string $title;

	/**
	 * @var string Section description text or html code.
	 *             Optional, defaults to empty string. Will be passed through wpautop($description).
	 */
	protected string $description;

	/**
	 * @var Closure|callable|string|array|null $callback Section display callback function.
	 *                                                    Optional, defaults to \SergeLiatko\WPSettings\Section::display().
	 */
	protected $callback = null;

	/**
	 * @var Setting[]|null $settings Array of settings to add to this section.
	 *                                                  Optional, defaults to empty array.
	 *                                                  Values must be arrays of parameters for Setting.
	 */
	protected ?array $settings = null;

	/**
	 * Section constructor.
	 *
	 * @param array $args
	 *
	 * @throws WP_Exception
	 */
	public function __construct( array $args = array() ) {
		/**
		 * @var string $id
		 * @var string $page
		 * @var string $title
		 * @var string $description
		 * @var callable $callback
		 * @var array[] $settings
		 */
		extract( wp_parse_args( $args, $this->getDefaultParameters() ) );
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
	 * @param array $params
	 *
	 * @return Section
	 */
	public static function createInstance( array $params ): static {
		return Factory::createItem( $params, __CLASS__ );
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return Section
	 */
	public function setId( string $id = 'default' ): Section {
		if ( $this->isEmpty( $id = sanitize_key( $id ) ) ) {
			$id = 'default';
		}
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPage(): string {
		if ( $this->isEmpty( $this->page ) ) {
			$this->setPage();
		}

		return $this->page;
	}

	/**
	 * @param string $page
	 *
	 * @return Section
	 */
	public function setPage( string $page = 'general' ): Section {
		$this->page = $page;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return Section
	 */
	public function setTitle( string $title = '' ): Section {
		$this->title = trim( $title );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @param string $description
	 *
	 * @return Section
	 */
	public function setDescription( string $description = '' ): Section {
		$this->description = trim( $description );

		return $this;
	}

	/**
	 * @return Closure|callable|string|array
	 */
	public function getCallback(): callable|array|Closure|string {
		if ( ! $this->is_callable_or_closure( $this->callback ) ) {
			$this->setCallback( array( $this, 'display' ) );
		}

		return $this->callback;
	}

	/**
	 * @param callable|array|string|Closure|null $callback
	 *
	 * @return Section
	 */
	public function setCallback( callable|array|string|Closure|null $callback = null ): Section {
		$this->callback = $this->is_callable_or_closure( $callback ) ? $callback : null;

		return $this;
	}

	/**
	 * @return array|Setting[]
	 * @noinspection PhpUnused
	 * @throws WP_Exception
	 */
	public function getSettings(): array {
		if ( ! is_array( $this->settings ) ) {
			$this->setSettings();
		}

		return $this->settings;
	}

	/**
	 * @param array[] $settings
	 *
	 * @return Section
	 * @throws WP_Exception When instantiation of a setting fails.
	 */
	public function setSettings( array $settings = array() ): Section {
		$this->settings = $this->instantiateItems(
			$settings,
			'\\SergeLiatko\\WPSettings\\Setting',
			array(
				'page'    => $this->getPage(),
				'section' => $this->getId(),
			)
		);

		return $this;
	}

	/**
	 * Displays section in WordPress UI.
	 */
	public function display(): void {
		do_action( "before_setting_section-{$this->getId()}-{$this->getPage()}", $this );
		if ( ! $this->isEmpty( $description = $this->getDescription() ) ) {
			echo wpautop( $description );
		}
		do_action( "after_setting_section-{$this->getId()}-{$this->getPage()}", $this );
	}

	/**
	 * Registers setting section in WordPress UI.
	 */
	public function register(): void {
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
	public function getDefaultParameters(): array {
		return array(
			'id'          => 'default',
			'page'        => 'general',
			'title'       => '',
			'description' => '',
			'callback'    => null,
			'settings'    => array(),
		);
	}

}
