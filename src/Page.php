<?php


namespace SergeLiatko\WPSettings;

use SergeLiatko\WPSettings\Interfaces\AdminItemInterface;
use SergeLiatko\WPSettings\Traits\AdminItemHandler;
use SergeLiatko\WPSettings\Traits\IsCallableOrClosure;
use SergeLiatko\WPSettings\Traits\IsEmpty;

/**
 * Class Page
 *
 * @package SergeLiatko\WPSettings
 */
class Page implements AdminItemInterface {

	use AdminItemHandler, IsCallableOrClosure, IsEmpty;

	/**
	 * Hook suffix returned by add_menu_page() or add_submenu_page() upon registration in WordPress UI.
	 *
	 * @var string $hook
	 */
	protected $hook;

	/**
	 * Page slug in WordPress admin.
	 *
	 * Represents $menu_slug parameter in add_menu_page() and add_submenu_page().
	 *
	 * @var string $slug
	 */
	protected $slug;

	/**
	 * Page label in admin navigation menu.
	 *
	 * Represents $menu_title in add_menu_page() and add_submenu_page().
	 *
	 * @var string $label
	 */
	protected $label;

	/**
	 * Page title in admin area.
	 *
	 * Optional, defaults to empty string.
	 * If left empty, $label will be used.
	 *
	 * Represents $page_title in add_menu_page() and add_submenu_page().
	 *
	 * @var string $title
	 */
	protected $title;

	/**
	 * Formatted text to display before the setting sections on this page.
	 *
	 * Optional, defaults to empty string.
	 *
	 * NOTE: the provided string will be passed to wpautop() before output.
	 *
	 * @var string $description
	 */
	protected $description;

	/**
	 * Minimum capability required to access this page in the admin.
	 *
	 * Optional, defaults to 'manage_options'.
	 *
	 * Represents $capability in add_menu_page() and add_submenu_page().
	 *
	 * @var string $capability
	 */
	protected $capability;

	/**
	 * Parent page slug.
	 *
	 * Optional, defaults to empty string.
	 * If left empty, the page will become top level admin page, submenu page otherwise.
	 *
	 * Represents $parent_page in add_submenu_page().
	 *
	 * @var string $parent
	 */
	protected $parent;

	/**
	 * Page position in admin menu.
	 *
	 * Optional, defaults to null.
	 * Negative or zero value will prepend the page in the submenu.
	 *
	 * Represents $position in add_menu_page() and add_submenu_page().
	 *
	 * @var int|null $position
	 */
	protected $position;

	/**
	 * The URL to the icon to be used for this menu.
	 *
	 * Optional, defaults to empty string.
	 *
	 * - Pass a base64-encoded SVG using a data URI, which will be colored to match the color scheme.
	 *   This should begin with 'data:image/svg+xml;base64,'.
	 * - Pass the name of a Dashicons helper class to use a font icon, e.g. 'dashicons-chart-pie'.
	 * - Pass 'none' to leave div.wp-menu-image empty so an icon can be added via CSS.
	 *
	 * Represents $icon_url in add_menu_page().
	 *
	 * @var string $icon
	 */
	protected $icon;

	/**
	 * The function to be called to output the content for this page.
	 *
	 * Optional, defaults to array( $this, 'display' ).
	 *
	 * Represents $function in add_menu_page() and add_submenu_page().
	 *
	 * @var \Closure|callable|string|array|null $callback
	 */
	protected $callback;

	/**
	 * Array of setting sections to include in this page.
	 *
	 * Optional, defaults to empty array.
	 *
	 * @see setSections()
	 *
	 * @var array|\SergeLiatko\WPSettings\Section[] $sections
	 */
	protected $sections;

	/**
	 * Array of scripts to load on this admin page.
	 *
	 * Optional. Defaults to empty array.
	 *
	 * If value in this array is a string, it will be used as script handle in wp_enqueue_scripts().
	 * If value is an associative array, its parts will be used in wp_enqueue_scripts(). See enqueueScripts() for details.
	 *
	 * @var string[]|array[]
	 */
	protected $scripts;

	/**
	 * Page constructor.
	 *
	 * @param array $params
	 */
	public function __construct( array $params ) {
		/**
		 * @var string           $slug
		 * @var string           $label
		 * @var string           $title
		 * @var string           $description
		 * @var string           $capability
		 * @var string           $parent
		 * @var int|null         $position
		 * @var string           $icon
		 * @var callable         $callback
		 * @var array[]          $sections
		 * @var string[]|array[] $scripts
		 */
		extract( wp_parse_args( $params, $this->getDefaultParameters() ), EXTR_OVERWRITE );
		$this->setSlug( $slug );
		$this->setLabel( $label );
		$this->setTitle( $title );
		$this->setDescription( $description );
		$this->setCapability( $capability );
		$this->setParent( $parent );
		$this->setPosition( $position );
		$this->setIcon( $icon );
		$this->setCallback( $callback );
		$this->setScripts( $scripts );
		//hook page registration before sections
		add_action( 'admin_menu', array( $this, 'register' ), 10, 0 );
		$this->setSections( $sections );
	}

	/**
	 * @param array $params
	 *
	 * @return object|\SergeLiatko\WPSettings\Interfaces\AdminItemInterface
	 */
	public static function createInstance( array $params ) {
		return Factory::createItem( $params, __CLASS__ );
	}


	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getHook(): string {
		return $this->hook;
	}

	/**
	 * @param string $hook
	 *
	 * @return Page
	 */
	public function setHook( string $hook = '' ): Page {
		if ( !empty( $hook ) ) {
			//enqueue scripts if needed
			if ( !$this->isEmpty( $this->getScripts() ) ) {
				add_action( "load-$hook", array( $this, 'loadScripts' ), 10, 0 );
			}
			//display setting errors (and update message) if it is not in admin Settings submenu.
			if ( 'options-general.php' !== $this->getParent() ) {
				add_action( "admin_footer-$hook", 'settings_errors', 10, 0 );
			}
		}
		$this->hook = $hook;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getSlug(): string {
		return $this->slug;
	}

	/**
	 * @param string $slug
	 *
	 * @return Page
	 */
	public function setSlug( string $slug ): Page {
		$this->slug = sanitize_key( $slug );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return $this->label;
	}

	/**
	 * @param string $label
	 *
	 * @return Page
	 */
	public function setLabel( string $label ): Page {
		$this->label = sanitize_text_field( $label );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		if ( empty( $this->title ) ) {
			$this->setTitle( $this->getLabel() );
		}

		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return Page
	 */
	public function setTitle( string $title = '' ): Page {
		$this->title = sanitize_text_field( $title );

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
	 * @return Page
	 */
	public function setDescription( string $description = '' ): Page {
		$this->description = trim( $description );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCapability(): string {
		if ( empty( $this->capability ) ) {
			$this->setCapability();
		}

		return $this->capability;
	}

	/**
	 * @param string $capability
	 *
	 * @return Page
	 */
	public function setCapability( string $capability = 'manage_options' ): Page {
		$this->capability = sanitize_key( $capability );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getParent(): string {
		return $this->parent;
	}

	/**
	 * @param string $parent
	 *
	 * @return Page
	 */
	public function setParent( string $parent = '' ): Page {
		$this->parent = trim( $parent );

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getPosition(): ?int {
		return $this->position;
	}

	/**
	 * @param int|null $position
	 *
	 * @return Page
	 */
	public function setPosition( ?int $position = null ): Page {
		$this->position = is_null( $position ) ? $position : absint( $position );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		return $this->icon;
	}

	/**
	 * @param string $icon
	 *
	 * @return Page
	 */
	public function setIcon( string $icon = '' ): Page {
		$this->icon = trim( $icon );

		return $this;
	}

	/**
	 * @return \Closure|callable|string|array
	 */
	public function getCallback() {
		if ( !$this->is_callable_or_closure( $this->callback ) ) {
			$this->setCallback( array( $this, 'display' ) );
		}

		return $this->callback;
	}

	/**
	 * @param \Closure|callable|string|array|null $callback
	 *
	 * @return Page
	 */
	public function setCallback( $callback ): Page {
		$this->callback = $this->is_callable_or_closure( $callback ) ? $callback : null;

		return $this;
	}

	/**
	 * @return array|\SergeLiatko\WPSettings\Section[]
	 * @noinspection PhpUnused
	 */
	public function getSections(): array {
		if ( !is_array( $this->sections ) ) {
			$this->setSections( array() );
		}

		return $this->sections;
	}

	/**
	 * @param array[] $sections
	 *
	 * @return Page
	 */
	public function setSections( array $sections = array() ): Page {
		$this->sections = $this->instantiateItems(
			$sections,
			'\\SergeLiatko\\WPSettings\\Section',
			array( 'page' => $this->getSlug() )
		);

		return $this;
	}

	/**
	 * @return array[]|string[]
	 */
	public function getScripts(): array {
		if ( !is_array( $this->scripts ) ) {
			$this->setScripts( array() );
		}

		return $this->scripts;
	}

	/**
	 * @param array[]|string[] $scripts
	 *
	 * @return Page
	 */
	public function setScripts( array $scripts ): Page {
		array_walk( $scripts, function ( &$script, $order, $defaults ) {
			if ( is_array( $script ) ) {
				$script = wp_parse_args( $script, $defaults );
				if ( empty( $script['handle'] ) ) {
					$script = null;
				}
			} else {
				if ( !is_string( $script ) || empty( $script ) ) {
					$script = null;
				}
			}
		}, array(
			'handle'    => '',
			'scr'       => '',
			'deps'      => array(),
			'ver'       => false,
			'in_footer' => false,
		) );
		$this->scripts = array_filter( $scripts );

		return $this;
	}

	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getId(): string {
		return $this->getSlug();
	}

	/**
	 * Displays settings page in WordPress admin.
	 */
	public function display() {
		$slug = $this->getSlug();
		/** @noinspection HtmlUnknownTarget */
		printf(
			'<div class="wrap %1$s-settings-page">%2$s<form action="%3$s" method="post">',
			$slug,
			sprintf( '<h2>%s</h2>', esc_html( get_admin_page_title() ) ),
			esc_url( admin_url( 'options.php' ) )
		);
		if ( !$this->isEmpty( $description = $this->getDescription() ) ) {
			echo wpautop( $description );
		}
		do_action( "before_setting_sections-$slug", $this );
		settings_fields( $slug );
		do_settings_sections( $slug );
		submit_button();
		do_action( "after_setting_sections-$slug", $this );
		echo '</form></div>';
	}

	/**
	 * Registers settings page in WordPress UI.
	 */
	public function register() {
		$hook = $this->isEmpty( $parent = $this->getParent() ) ?
			add_menu_page(
				$this->getTitle(),
				$this->getLabel(),
				$this->getCapability(),
				$this->getSlug(),
				$this->getCallback(),
				$this->getIcon(),
				$this->getPosition()
			)
			: add_submenu_page(
				$parent,
				$this->getTitle(),
				$this->getLabel(),
				$this->getCapability(),
				$this->getSlug(),
				$this->getCallback(),
				$this->getPosition()
			);
		$this->setHook( $hook );
		do_action( "settings_page_registered", $this->getSlug(), $hook, $this );
	}

	/**
	 * Hooks enqueueScripts() to admin_enqueue_scripts.
	 */
	public function loadScripts() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ), 10, 0 );
	}

	/**
	 * Enqueues scripts for this page in WordPress admin.
	 */
	public function enqueueScripts() {
		foreach ( $this->getScripts() as $script ) {
			if ( is_array( $script ) ) {
				$handle = $script['handle'];
				wp_enqueue_script(
					$handle,
					$script['src'],
					$script['deps'],
					$script['ver'],
					$script['in_footer']
				);
				//@developers: hook your wp_localize_script() functions here to localize the enqueued script
				do_action( "admin_enqueued_script-$handle", $this );
			} else {
				if ( is_string( $script ) ) {
					$handle = $script;
					wp_enqueue_script( $handle );
					//@developers: hook your wp_localize_script() functions here to localize the enqueued script
					do_action( "admin_enqueued_script-$handle", $this );
				}
			}
		}
	}

	/**
	 * Returns default Page parameters.
	 *
	 * @return array
	 */
	protected function getDefaultParameters(): array {
		return array(
			'slug'        => '',
			'label'       => '',
			'title'       => '',
			'description' => '',
			'capability'  => 'manage_options',
			'parent'      => '',
			'position'    => null,
			'icon'        => '',
			'callback'    => null,
			'sections'    => array(),
			'scripts'     => array(),
		);
	}

}
