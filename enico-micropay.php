<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://enico.info
 * @since             1.5.0
 * @package           Enico_Micropay
 *
 * @wordpress-plugin
 * Plugin Name:       Énico Micropagos
 * Plugin URI:        https://github.com/enicopagos/enico-micropay
 * Description:       Énico es un sistema de micropagos para contenidos de tu sitio web. A través de un muro de pago (paywall), permite monetizar piezas de contenido de forma individual. Está asociado a Mercado Pago.
 * Version:           1.5.1
 * Author:            Énico
 * Author URI:        https://enico.info
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       enico-micropay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.5.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ENICO_MICROPAY_VERSION', '1.5.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-enico-micropay-activator.php
 */
function activate_enico_micropay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-enico-micropay-activator.php';
	Enico_Micropay_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-enico-micropay-deactivator.php
 */
function deactivate_enico_micropay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-enico-micropay-deactivator.php';
	Enico_Micropay_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_enico_micropay' );
register_deactivation_hook( __FILE__, 'deactivate_enico_micropay' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-enico-micropay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.5.0
 */
function run_enico_micropay() {

	$plugin = new Enico_Micropay();
	$plugin->run();

}
run_enico_micropay();
