<?php
namespace MEC_Advanced_Speaker;

// don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Autoloader.
 *
 * @author     author
 * @package     package
 * @since     1.0.0
 */
class Autoloader {

	protected static $classes    = [];
	protected static $namespaces = [];
	/**
	 * Instance of this class.
	 *
	 * @since     1.0.0
	 * @access     private
	 * @var     Autoloader
	 */
	private static $instance;

	/**
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @since   1.0.0
	 * @return  object
	 */
	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		self::register();
	}

	/**
	 * Register spl autoload.
	 *
	 * @since     1.0.0
	 */
	public static function register() {
		spl_autoload_register( '\MEC_Advanced_Speaker\Autoloader::load', true, true );
	}

	public static function addClass( $class, $path ) {
		static::$classes[ strtolower( $class ) ] = $path;
	}

	public static function addClasses( $classes ) {
		foreach ( $classes as $class => $path ) {
			static::$classes[ strtolower( $class ) ] = $path;
		}
	}

	/**
	 * Load
	 *
	 * @since     1.0.0
	 */
	public static function load( $class ) {

		// deal with funny is_callable('static::classname') side-effect
		if ( strpos( $class, 'static::' ) === 0 ) {
			// is called from within the class, so it's already loaded
			return true;
		}

		$class = ltrim( $class, '\\' );
		if ( isset( static::$classes[ strtolower( $class ) ] ) ) {
			static::initClass( $class, str_replace( '/', DS, static::$classes[ strtolower( $class ) ] ) );
			return true;
		}

		return false;
	}

	/**
	 * Init Class
	 *
	 * @since     1.0.0
	 */
	protected static function initClass( $class, $file = null ) {
		// include the file if needed
		if ( $file && file_exists( $file ) ) {
			include $file;
		}

		// if the loaded file contains a class...
		if ( class_exists( $class, false ) ) {
			// call the classes static init if needed
			if ( method_exists( $class, 'instance' ) and is_callable( $class . '::instance' ) ) {
				call_user_func( $class . '::instance' );
			}
		}
	}
} //Autoloader

Autoloader::instance();
