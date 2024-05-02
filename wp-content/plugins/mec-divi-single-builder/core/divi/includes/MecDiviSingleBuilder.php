<?php

class MDSB_MecDiviSingleBuilder extends \DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'mdsb-mec-divi-single-builder';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'mec-divi-single-builder';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * MDSB_MecDiviSingleBuilder constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'mec-divi-single-builder', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
	}
}

new MDSB_MecDiviSingleBuilder;
