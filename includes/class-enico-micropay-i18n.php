<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://enico.info
 * @since      1.5.0
 *
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.5.0
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/includes
 * @author     Enico <info@enico.info>
 */
class Enico_Micropay_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.5.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'enico-micropay',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
