<?php


namespace SergeLiatko\WPSettings;

use Exception;
use SergeLiatko\FormFields\Checkboxes;
use SergeLiatko\FormFields\InputCheckbox;
use SergeLiatko\FormFields\InputColor;
use SergeLiatko\FormFields\InputDate;
use SergeLiatko\FormFields\InputDateTimeLocal;
use SergeLiatko\FormFields\InputEmail;
use SergeLiatko\FormFields\InputFile;
use SergeLiatko\FormFields\InputHidden;
use SergeLiatko\FormFields\InputNumber;
use SergeLiatko\FormFields\InputPassword;
use SergeLiatko\FormFields\InputRadio;
use SergeLiatko\FormFields\InputRange;
use SergeLiatko\FormFields\InputTel;
use SergeLiatko\FormFields\InputText;
use SergeLiatko\FormFields\InputUrl;
use SergeLiatko\FormFields\Radios;
use SergeLiatko\FormFields\Select;
use SergeLiatko\FormFields\Textarea;
use SergeLiatko\WPSettings\Interfaces\AdminItemInterface;
use SergeLiatko\WPSettings\Traits\IsEmpty;

/**
 * Class Setting
 *
 * @package SergeLiatko\WPSettings
 */
class Setting implements AdminItemInterface {

	use IsEmpty;

	/**
	 * @var string $id Setting field id (Optional, if empty, will be generated from $option).
	 */
	protected $id;

	/**
	 * @var string $option Setting option name in the database.
	 */
	protected $option;

	/**
	 * @var string $label Setting label in WP UI.
	 */
	protected $label;

	/**
	 * @var string $help Setting help message in WP UI.
	 */
	protected $help;

	/**
	 * @var string $description Setting option description in REST API.
	 */
	protected $description;

	/**
	 * @var string $page Settings page (option group) the setting is to be displayed on.
	 */
	protected $page;

	/**
	 * @var string $section Settings section the setting is to be displayed in.
	 */
	protected $section;

	/**
	 * @var string $type Setting type. Defines how setting is displayed in WP UI and option data type in REST.
	 */
	protected $type;

	/**
	 * @var string $data_type Overwrites the option data type in REST set by $type.
	 */
	protected $data_type;

	/**
	 * @var bool|array $show_in_rest Whether data associated with this setting should be included in the REST API.
	 *                               When registering complex settings, this argument may optionally be an array with a
	 *                               'schema' key.
	 */
	protected $show_in_rest;

	/**
	 * @var callable $sanitize_callback A callback function that sanitizes the option's value.
	 */
	protected $sanitize_callback;

	/**
	 * @var callable $display_callback A callback function that displays the setting in WP UI.
	 */
	protected $display_callback;

	/**
	 * @var array $display_args Array of arguments passed to the display function.
	 */
	protected $display_args;

	/**
	 * @var mixed $default Default option value.
	 */
	protected $default;

	/**
	 * @var bool $force_default Flag to force the default value returned by get_option() if option value is empty in
	 *      database.
	 */
	protected $force_default;

	/**
	 * @var array $input_attrs Array of setting field HTML attributes.
	 */
	protected $input_attrs;

	/**
	 * @var array $choices Array of choices in setting UI (if applicable, eg. options in dropdown or a list of
	 *      checkboxes).
	 *                     NOTE: array keys well be treated as LABELS and values as option values. If the value is an
	 *                     array, it will treat it as a group of sub-options and use the key as a label for the group.
	 */
	protected $choices;

	/**
	 * Setting constructor.
	 *
	 * @param array $args
	 *
	 * @throws \Exception
	 */
	public function __construct( array $args ) {
		/**
		 * @var string     $id
		 * @var string     $option
		 * @var string     $label
		 * @var string     $help
		 * @var string     $description
		 * @var string     $page
		 * @var string     $section
		 * @var string     $type
		 * @var string     $data_type
		 * @var bool|array $show_in_rest
		 * @var callable   $sanitize_callback
		 * @var callable   $display_callback
		 * @var array      $display_args
		 * @var mixed      $default
		 * @var bool       $force_default
		 * @var array      $input_attrs
		 * @var array      $choices
		 */
		extract(
			$this->parse_args_recursive( $args, $this->getDefaultParameters() ),
			EXTR_OVERWRITE
		);
		// throw an exception if the $option is empty
		try {
			$this->setOption( $option );
		} catch ( Exception $exception ) {
			throw $exception;
		}
		$this->setId( $id );
		$this->setLabel( $label );
		$this->setHelp( $help );
		$this->setDescription( $description );
		$this->setPage( $page );
		$this->setSection( $section );
		$this->setType( $type );
		$this->setDataType( $data_type );
		$this->setShowInRest( $show_in_rest );
		$this->setSanitizeCallback( $sanitize_callback );
		$this->setDisplayCallback( $display_callback );
		$this->setDisplayArgs( $display_args );
		$this->setDefault( $default );
		$this->setForceDefault( $force_default );
		$this->setInputAttrs( $input_attrs );
		$this->setChoices( $choices );
		// register setting in admin and rest
		add_action( 'admin_init', array( $this, 'register' ), 10, 0 );
		if ( ! $this->isEmpty( $this->getShowInRest() ) ) {
			add_action( 'rest_api_init', array( $this, 'register' ), 10, 0 );
		}
		// add setting field to WP UI.
		add_action( 'admin_menu', array( $this, 'addSettingField' ), 10, 0 );
		// handle default value
		if ( ! is_null( $this->getDefault() ) ) {
			//make sure the default option value is not saved to database (default value is defined in the source code)
			add_action( "update_option_{$this->getOption()}", array( $this, 'doNotUpdateDefault' ), 10, 2 );
			add_action( "add_option_{$this->getOption()}", array( $this, 'doNotAddDefault' ), 10, 2 );
			//make sure the default value is also provided on the front end
			if ( ! is_admin() ) {
				add_filter( "default_option_{$this->getOption()}", array( $this, 'filterDefaultOption' ), 10, 3 );
			}
			//make sure the default value is returned if the option value is empty and $force_default is true
			if ( $this->isForceDefault() ) {
				add_filter( "option_{$this->getOption()}", array( $this, 'forceDefault' ), 10, 1 );
			}
		}
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
	 */
	public function getId() {
		if ( empty( $this->id ) ) {
			$this->setId( $this->generateId() );
		}

		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setId( $id = '' ) {
		$this->id = sanitize_key( $id );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOption() {
		return $this->option;
	}

	/**
	 * @param $option
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 *
	 * @throws \Exception
	 */
	public function setOption( $option ) {
		if ( $this->isEmpty( $option = sanitize_key( $option ) ) ) {
			throw new Exception( 'Option parameter must not be empty.' );
		}
		$this->option = $option;

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
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setLabel( $label = '' ) {
		$this->label = sanitize_text_field( $label );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHelp() {
		return $this->help;
	}

	/**
	 * @param string $help
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setHelp( $help = '' ) {
		$this->help = strval( $help );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		if ( empty( $this->description ) ) {
			$this->setDescription( $this->getLabel() );
		}

		return $this->description;
	}

	/**
	 * @param string $description
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setDescription( $description = '' ) {
		$this->description = strval( $description );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPage() {
		if ( empty( $this->page ) ) {
			$this->setPage( 'general' );
		}

		return $this->page;
	}

	/**
	 * @param string $page
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setPage( $page = '' ) {
		$this->page = sanitize_text_field( $page );

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSection() {
		if ( empty( $this->section ) ) {
			$this->setSection( 'default' );
		}

		return $this->section;
	}

	/**
	 * @param string $section
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setSection( $section = '' ) {
		if ( $this->isEmpty( $section = sanitize_key( $section ) ) ) {
			$section = 'default';
		}
		$this->section = $section;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setType( $type = '' ) {
		if ( $this->isEmpty( $type = sanitize_key( $type ) ) ) {
			$type = 'text';
		}
		$this->type = $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		if ( empty( $this->data_type ) ) {
			$this->setDataType( $this->generateDataType() );
		}

		return $this->data_type;
	}

	/**
	 * @param string $data_type
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setDataType( $data_type = '' ) {
		$this->data_type = sanitize_key( $data_type );

		return $this;
	}

	/**
	 * @return array|bool
	 */
	public function getShowInRest() {
		return $this->show_in_rest;
	}

	/**
	 * @param array|bool $show_in_rest
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setShowInRest( $show_in_rest ) {
		$this->show_in_rest = $show_in_rest;

		return $this;
	}

	/**
	 * @return callable
	 */
	public function getSanitizeCallback() {
		return $this->sanitize_callback;
	}

	/**
	 * @param callable $sanitize_callback
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setSanitizeCallback( callable $sanitize_callback ) {
		$this->sanitize_callback = $sanitize_callback;

		return $this;
	}

	/**
	 * @return callable
	 */
	public function getDisplayCallback() {
		return $this->display_callback;
	}

	/**
	 * @param callable $display_callback
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setDisplayCallback( callable $display_callback ) {
		$this->display_callback = $display_callback;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getDisplayArgs() {
		if ( ! is_array( $this->display_args ) ) {
			$this->setDisplayArgs( array() );
		}

		return $this->display_args;
	}

	/**
	 * @param array $display_args
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setDisplayArgs( array $display_args ) {
		$this->display_args = wp_parse_args(
			$display_args,
			array(
				'label_for' => $this->generateId(),
			)
		);

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDefault() {
		return $this->default;
	}

	/**
	 * @param mixed|null $default
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setDefault( $default = null ) {
		$this->default = $default;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isForceDefault() {
		return (bool) $this->force_default;
	}

	/**
	 * @param bool $force_default
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setForceDefault( $force_default ) {
		$this->force_default = ! empty( $force_default );

		return $this;
	}

	/**
	 * @return array
	 */
	public function getInputAttrs() {
		if ( ! is_array( $this->input_attrs ) ) {
			$this->setInputAttrs( array() );
		}

		return $this->input_attrs;
	}

	/**
	 * @param array $input_attrs
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setInputAttrs( array $input_attrs ) {
		$this->input_attrs = wp_parse_args(
			$input_attrs,
			array(
				'id'   => $this->generateId(),
				'name' => $this->getOption(),
			)
		);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getChoices() {
		if ( ! is_array( $this->choices ) ) {
			$this->setChoices( array() );
		}

		return $this->choices;
	}

	/**
	 * @param array $choices
	 *
	 * @return \SergeLiatko\WPSettings\Setting
	 */
	public function setChoices( array $choices ) {
		$this->choices = $choices;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function sanitize( $value ) {
		switch ( $this->getDataType() ) {
			case 'boolean':
				// handle all strings but for "false" and "0" and not empty values as boolean true.
				$value = is_string( $value ) ?
					( in_array( strtolower( $value ), array( 'false', '0' ) ) ? false : true )
					: ! empty( $value );
				break;
			case 'integer':
				$value = intval( $value );
				break;
			case 'number':
				$value = floatval( $value );
				break;
			case 'string':
				switch ( $this->getType() ) {
					case 'textarea':
						$value = sanitize_textarea_field( $value );
						break;
					case 'email':
						$value = sanitize_email( $value );
						break;
					case 'url':
						$value = esc_url_raw( $value );
						break;
					case 'hidden':
					case 'text':
					case 'checkbox':
					case 'radio':
					case 'password':
					case 'tel':
					case 'color':
					case 'date': #todo: separate sanitize
					case 'date-time-local': #todo: separate sanitize
					case 'number': #prefer data type parameter over type - sanitize as string value
					case 'range': #prefer data type parameter over type - sanitize as string value
					case 'select':
					default:
						$value = sanitize_text_field( $value );
						break;
				}
				break;
			//todo: implement sanitize for objects and arrays based on schema
			case 'array':
				$value = array_filter( (array) $value );
				break;
			default:
				// sanitize as text field by default
				$value = sanitize_text_field( $value );
				break;
		}

		return $value;
	}

	/**
	 * Displays setting field in WP UI.
	 */
	public function display() {
		$current = get_option( $this->getOption(), $this->getDefault() );
		switch ( $this->getType() ) {
			case 'textarea':
				echo Textarea::HTML( $this->getFieldArguments( $current, array(
					'rows'  => 10,
					'cols'  => 50,
					'class' => 'large-text code',
				) ) );
				break;
			case 'text':
				echo InputText::HTML( $this->getFieldArguments( $current, array(
					'class' => 'regular-text code',
				) ) );
				break;
			case 'checkbox':
				if ( $this->isEmpty( $this->getChoices() ) ) {
					echo InputCheckbox::HTML( $this->getFieldArguments( $current, array(
						'value' => '1',
					) ) );
				} else {
					echo Checkboxes::HTML( $this->getFieldArguments( $current ) );
				}
				break;
			case 'radio':
				if ( $this->isEmpty( $this->getChoices() ) ) {
					echo InputRadio::HTML( $this->getFieldArguments( $current ) );
				} else {
					echo Radios::HTML( $this->getFieldArguments( $current ) );
				}
				break;
			case 'select':
				echo Select::HTML( $this->getFieldArguments( $current ) );
				break;
			case 'number':
				echo InputNumber::HTML( $this->getFieldArguments( $current, array(
					'class' => 'small-text code',
				) ) );
				break;
			case 'email':
				echo InputEmail::HTML( $this->getFieldArguments( $current, array(
					'class' => 'regular-text code',
				) ) );
				break;
			case 'url':
				echo InputUrl::HTML( $this->getFieldArguments( $current, array(
					'class' => 'large-text code',
				) ) );
				break;
			case 'password':
				echo InputPassword::HTML( $this->getFieldArguments( $current, array(
					'class' => 'regular-text code',
				) ) );
				break;
			case 'range':
				echo InputRange::HTML( $this->getFieldArguments( $current ) );
				break;
			case 'color':
				echo InputColor::HTML( $this->getFieldArguments( $current ) );
				break;
			case 'tel':
				echo InputTel::HTML( $this->getFieldArguments( $current, array(
					'class' => 'regular-text code',
				) ) );
				break;
			case 'date':
				echo InputDate::HTML( $this->getFieldArguments( $current ) );
				break;
			case 'date-time-local':
				echo InputDateTimeLocal::HTML( $this->getFieldArguments( $current ) );
				break;
			case 'file':
				echo InputFile::HTML( $this->getFieldArguments( $current ) );
				break;
			case 'hidden':
				echo InputHidden::HTML( $this->getFieldArguments( $current ) );
				break;
			default:
				break;
		}
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function forceDefault( $value ) {
		return empty( $value ) ? $this->getDefault() : $value;
	}

	/**
	 * @param mixed  $default
	 * @param string $option
	 * @param bool   $passed_default
	 *
	 * @return mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filterDefaultOption( $default, $option, $passed_default ) {
		if ( $passed_default ) {
			return $default;
		}

		return $this->getDefault();
	}

	/**
	 * Adds setting field to WP UI.
	 */
	public function addSettingField() {
		add_settings_field(
			$this->getId(),
			$this->getLabel(),
			$this->getDisplayCallback(),
			$this->getPage(),
			$this->getSection(),
			$this->getDisplayArgs()
		);
	}

	/**
	 * Registers setting in WP.
	 */
	public function register() {
		register_setting(
			$this->getPage(),
			$this->getOption(),
			array(
				'type'              => $this->getDataType(),
				'description'       => $this->getDescription(),
				'sanitize_callback' => $this->getSanitizeCallback(),
				'show_in_rest'      => $this->getShowInRest(),
			)
		);
	}

	/**
	 * @param mixed $old_value
	 * @param mixed $new_value
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function doNotUpdateDefault( $old_value, $new_value ) {
		if ( $this->getDefault() == $new_value ) {
			delete_option( $this->getOption() );
		}
	}

	/**
	 * @param string $option
	 * @param mixed  $new_value
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function doNotAddDefault( $option, $new_value ) {
		if ( $this->getDefault() == $new_value ) {
			delete_option( $this->getOption() );
		}
	}

	/**
	 * @param mixed $current
	 * @param array $input_attrs
	 *
	 * @return array
	 */
	protected function getFieldArguments( $current, $input_attrs = array() ) {
		return array(
			'id'          => $this->getId(),
			'input_attrs' => wp_parse_args(
				$this->getInputAttrs(),
				$input_attrs
			),
			'value'       => $current,
			'choices'     => $this->getChoices(),
			'help'        => $this->getHelp(),
			'help_attrs'  => array(
				'class' => 'description',
			),
		);
	}

	/**
	 * Generates data type based on setting type.
	 *
	 * @return string
	 */
	protected function generateDataType() {
		//todo: check the data type from the show in rest parameter first
		switch ( $this->getType() ) {
			case 'checkbox':
				$type = $this->isEmpty( $this->getChoices() ) ? 'string' : 'array';
				break;
			case 'integer':
			case 'range':
				$type = 'integer';
				break;
			case 'number':
				$type = 'number';
				break;
			case 'select':
				$input_attrs = $this->getInputAttrs();
				$type        = isset( $input_attrs['multiple'] ) ? 'array' : 'string';
				break;
			default:
				$type = 'string';
				break;
		}

		return $type;
	}

	/**
	 * @return string
	 */
	protected function generateId() {
		return trim( preg_replace( '/([^a-z0-9-]+)/', '-', strtolower( $this->getOption() ) ), '-' );
	}

	/**
	 * @param array|object $args
	 * @param array|object $default
	 * @param bool         $preserve_integer_keys
	 *
	 * @return array|object
	 * @noinspection DuplicatedCode
	 */
	protected function parse_args_recursive( $args, $default, $preserve_integer_keys = false ) {
		if ( ! is_array( $default ) && ! is_object( $default ) ) {
			return wp_parse_args( $args, $default );
		}

		$is_object = ( is_object( $args ) || is_object( $default ) );
		$output    = array();

		foreach ( array( $default, $args ) as $elements ) {
			foreach ( (array) $elements as $key => $element ) {
				if ( is_integer( $key ) && ! $preserve_integer_keys ) {
					$output[] = $element;
				} elseif (
					isset( $output[ $key ] ) &&
					( is_array( $output[ $key ] ) || is_object( $output[ $key ] ) ) &&
					( is_array( $element ) || is_object( $element ) )
				) {
					$output[ $key ] = $this->parse_args_recursive(
						$element,
						$output[ $key ],
						$preserve_integer_keys
					);
				} else {
					$output[ $key ] = $element;
				}
			}
		}

		return $is_object ? (object) $output : $output;
	}

	/**
	 * @return array
	 */
	protected function getDefaultParameters() {
		return array(
			'id'                => '',
			'option'            => '',
			'label'             => '',
			'help'              => '',
			'description'       => '',
			'page'              => 'general',
			'section'           => 'default',
			'type'              => 'text',
			'data_type'         => '',
			'show_in_rest'      => false,
			'sanitize_callback' => array( $this, 'sanitize' ),
			'display_callback'  => array( $this, 'display' ),
			'display_args'      => array(),
			'default'           => null,
			'force_default'     => false,
			'input_attrs'       => array(),
			'choices'           => array(),
		);
	}

}
