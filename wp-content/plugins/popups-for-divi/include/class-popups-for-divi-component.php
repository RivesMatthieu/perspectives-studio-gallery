<?php
/**
 * Base component for other Popups for Divi classes.
 *
 * @package Popups_For_Divi
 */

/**
 * Base class.
 */
class Popups_For_Divi_Component {
	/**
	 * Whether the current instance was already set up.
	 *
	 * @since 2.0.4
	 * @var bool
	 */
	private $setup_complete = false;

	/**
	 * Holds details about registered child modules of the component.
	 *
	 * Used by add_module() / module() / module_type().
	 *
	 * @since 2.0.4
	 * @var array
	 */
	private $internal_modules = [];

	/**
	 * The parent app component.
	 *
	 * @since 2.0.4
	 * @var object
	 */
	private $internal_app = null;

	/**
	 * Set up the new component.
	 *
	 * @since 2.0.4
	 * @param mixed $parent A parent component, or empty.
	 */
	public function __construct( $parent = null ) {
		$this->internal_app = $parent;

		// Initialize variables instantly.
		$this->setup_instance();
	}

	/**
	 * Setup class variables during constructor call.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	protected function setup_instance() {
		// This function can be overwritten in the child class.
	}

	/**
	 * Setup all the hooks of the object and initialize members.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	public function setup() {
		// This function can be overwritten in the child class.
	}

	/**
	 * Marks the current instance as set-up.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	final public function internal_setup() {
		$this->setup_complete = true;
	}

	/**
	 * Registers a setup hook name and returns $this for chaining.
	 *
	 * @since 2.0.4
	 * @param string $hook_name Defines the WP action which triggers setup().
	 * @return Popups_For_Divi_Component
	 */
	final public function setup_on( $hook_name ) {
		if ( ! $this->setup_complete ) {
			add_action( $hook_name, [ $this, 'setup' ] );
			add_action( $hook_name, [ $this, 'internal_setup' ] );
		}

		return $this;
	}

	/**
	 * Returns the parent component of the current component.
	 *
	 * @since 2.0.4
	 * @return object
	 */
	final public function app() {
		return $this->internal_app;
	}

	/**
	 * Registers a new child module of the current component.
	 *
	 * @throws Exception When the specified class name is invalid.
	 * @throws Exception When the given module identifier is already registered.
	 *
	 * @since 2.0.4
	 * @param string $id         Identifier of the module, used to address it later.
	 * @param string $class_name Class name of the new module.
	 * @return object The newly created module instance.
	 */
	final protected function add_module( $id, $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			throw new Exception( 'Class not found: ' . $class_name );
		}
		if ( isset( $this->internal_modules[ $id ] ) ) {
			throw new Exception( 'Module already registered: ' . $id );
		}

		// Create the new module instance.
		$inst = new $class_name( $this );

		// Store the instance for later access.
		$this->internal_modules[ $id ] = [
			'id'    => $id,
			'class' => $class_name,
			'inst'  => $inst,
		];

		return $inst;
	}

	/**
	 * Returns a previously registered module instance.
	 *
	 * When the current instance has no component with that identifier, then the
	 * $strict param determines whether to check all parent components or whether
	 * an error should be thrown.
	 *
	 * @throws Exception When no module with the requested identifier exists.
	 *
	 * @since 2.0.4
	 * @param string $id     The module identifier.
	 * @param bool   $strict When false, then the parents module() is returned in
	 *                       case the current component does not have the requested
	 *                       module.
	 * @return object Returns the module instance.
	 */
	final public function module( $id, $strict = false ) {
		if ( isset( $this->internal_modules[ $id ] ) ) {
			return $this->internal_modules[ $id ]['inst'];
		}

		if ( $strict || ! $this->internal_app ) {
			throw new Exception( 'No module found with id: ' . $id );
		} else {
			return $this->app()->module( $id, false );
		}
	}

	/**
	 * Returns the class name of the requested module, or an empty string when no
	 * module with the given identifier was registered.
	 *
	 * @since 2.0.4
	 * @param string $id The module identifier.
	 * @return string Returns the class name of the module, or an empty string when
	 *                the module was not registered.
	 */
	final public function module_type( $id ) {
		if ( isset( $this->internal_modules[ $id ] ) ) {
			return $this->internal_modules[ $id ]['class'];
		}

		return '';
	}
}
