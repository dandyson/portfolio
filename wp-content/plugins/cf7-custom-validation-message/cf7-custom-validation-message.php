<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://digitaldyna.com
 * @since             1.0.0
 * @package           CF7_CVM
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Validation Message for Contact Form 7
 * Description:       Plugin provides custom validation message for each field of contact form 7.
 * Version:           1.2.1
 * Author:            DigitalDyna
 * Author URI:        http://digitaldyna.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7-custom-validation-message
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CF7_CVM_VERSION', '1.2.1' );
define( 'CF7_CVM_BASE_URL', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cf7cvm-activator.php
 */
function activate_cf7cvm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7cvm-activator.php';
	CF7_CVM_Activator::activate($network_wide);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cf7cvm-deactivator.php
 */
function deactivate_cf7cvm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7cvm-deactivator.php';
	CF7_CVM_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cf7cvm' );
register_deactivation_hook( __FILE__, 'deactivate_cf7cvm' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cf7cvm.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cf7cvm() {

	$plugin = new CF7_CVM();
	$plugin->run();

}
run_cf7cvm();
