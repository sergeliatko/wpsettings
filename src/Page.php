<?php


namespace SergeLiatko\WPSettings;

use SergeLiatko\WPSettings\Traits\IsEmpty;

/**
 * Class Page
 *
 * @package SergeLiatko\WPSettings
 */
class Page {

	use IsEmpty;

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
	 * @var callable $callback
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
	 * Page constructor.
	 *
	 * @param array $params
	 */
	public function __construct( array $params ) {
		/**
		 * @var string   $slug
		 * @var string   $label
		 * @var string   $title
		 * @var string   $description
		 * @var string   $capability
		 * @var string   $parent
		 * @var int|null $position
		 * @var string   $icon
		 * @var callable $callback
		 * @var array[]  $sections
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
		//hook page registration before sections
		add_action( 'admin_menu', array( $this, 'register' ), 10, 0 );
		$this->setSections( $sections );
	}

	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getHook() {
		return $this->hook;
	}

	/**
	 * @param string $hook
	 *
	 * @return Page
	 */
	public function setHook( $hook = '' ) {
		//display setting errors (and update message) if it is not in admin Settings submenu.
		if ( ! empty( $hook ) && ( 'options-general.php' !== $this->getParent() ) ) {
			add_action( "admin_footer-{$hook}", 'settings_errors', 10, 0 );
		}
		$this->hook = $hook;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * @param string $slug
	 *
	 * @return Page
	 */
	public function setSlug( $slug ) {
		$this->slug = sanitize_key( $slug );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 *
	 * @return Page
	 */
	public function setLabel( $label ) {
		$this->label = sanitize_text_field( $label );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
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
	public function setTitle( $title = '' ) {
		$this->title = sanitize_text_field( $title );

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
	 * @return Page
	 */
	public function setDescription( $description = '' ) {
		$this->description = trim( strval( $description ) );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCapability() {
		if ( empty( $this->capability ) ) {
			$this->setCapability( 'manage_options' );
		}

		return $this->capability;
	}

	/**
	 * @param string $capability
	 *
	 * @return Page
	 */
	public function setCapability( $capability = 'manage_options' ) {
		$this->capability = sanitize_key( $capability );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @param string $parent
	 *
	 * @return Page
	 */
	public function setParent( $parent = '' ) {
		$this->parent = trim( strval( $parent ) );

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @param int|null $position
	 *
	 * @return Page
	 */
	public function setPosition( $position = null ) {
		$this->position = is_null( $position ) ? $position : intval( $position );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @param string $icon
	 *
	 * @return Page
	 */
	public function setIcon( $icon = '' ) {
		$this->icon = trim( strval( $icon ) );

		return $this;
	}

	/**
	 * @return callable
	 */
	public function getCallback() {
		if ( ! is_callable( $this->callback ) ) {
			$this->setCallback( array( $this, 'display' ) );
		}

		return $this->callback;
	}

	/**
	 * @param callable $callback
	 *
	 * @return Page
	 */
	public function setCallback( callable $callback ) {
		$this->callback = $callback;

		return $this;
	}

	/**
	 * @return array|\SergeLiatko\WPSettings\Section[]
	 * @noinspection PhpUnused
	 */
	public function getSections() {
		if ( ! is_array( $this->sections ) ) {
			$this->setSections( array() );
		}

		return $this->sections;
	}

	/**
	 * @param array[] $sections
	 *
	 * @return Page
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function setSections( array $sections = array() ) {
		//filter sections
		$sections = array_filter( $sections, function ( $item ) {
			return ! empty( $item ) && is_array( $item );
		} );
		//instantiate sections
		array_walk( $sections, function ( &$item, $key, $defaults ) {
			$item = new Section( wp_parse_args( $item, $defaults ) );
		}, array( 'page' => $this->getSlug() ) );
		//store instantiated sections in array( Section ID => Section )
		$new_sections = array();
		foreach ( $sections as $section ) {
			if ( $section instanceof Section ) {
				$new_sections[ $section->getId() ] = $section;
			}
		}
		//set sections
		$this->sections = $new_sections;

		return $this;
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
		if ( ! $this->isEmpty( $description = $this->getDescription() ) ) {
			echo wpautop( $description );
		}
		do_action( "before_setting_sections-{$slug}", $this );
		settings_fields( $slug );
		do_settings_sections( $slug );
		submit_button();
		do_action( "after_setting_sections-{$slug}", $this );
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
	 * Returns default Page parameters.
	 *
	 * @return array
	 */
	protected function getDefaultParameters() {
		return array(
			'slug'        => '',
			'label'       => '',
			'title'       => '',
			'description' => '',
			'capability'  => 'manage_options',
			'parent'      => '',
			'position'    => null,
			'icon'        => '',
			'callback'    => array( $this, 'display' ),
			'sections'    => array(),
		);
	}

}
