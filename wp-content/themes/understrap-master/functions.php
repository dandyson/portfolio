<?php
/**
 * UnderStrap functions and definitions
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// UnderStrap's includes directory.
$understrap_inc_dir = get_template_directory() . '/inc';

// Array of files to include.
$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker. Trying to get deeper navigation? Check out: https://github.com/understrap/understrap/issues/567.
	'/editor.php',                          // Load Editor functions.
	'/deprecated.php',                      // Load deprecated functions.
);

// Load WooCommerce functions if WooCommerce is activated.
if ( class_exists( 'WooCommerce' ) ) {
	$understrap_includes[] = '/woocommerce.php';
}

// Load Jetpack compatibility file if Jetpack is activiated.
if ( class_exists( 'Jetpack' ) ) {
	$understrap_includes[] = '/jetpack.php';
}

// Include files.
foreach ( $understrap_includes as $file ) {
	require_once $understrap_inc_dir . $file;
}

// Contact Form 7 Custom Validation

// add_filter( 'wpcf7_validate_select', 'custom_shuttleprice_validation_filter', 20, 2 );
// add_filter( 'wpcf7_validate_select*', 'custom_shuttleprice_validation_filter', 20, 2 );
// function custom_shuttleprice_validation_filter( $result, $tag ) {
//   $has_value = isset( $_POST['menu-541'] ) && '' !== $_POST['menu-541'];
//     if ( $tag->name == 'menu-541' ) {
//         if( $_POST['menu-541'] and ! $has_value ) {
//             $result->invalidate( $tag, "Fix input" );
//         }
//     }
//     return $result;
// }


add_filter( 'wpcf7_validate_select', 'select_validation_filter', 20, 2 );
add_filter( 'wpcf7_validate_select*', 'select_validation_filter', 10, 2 );

function select_validation_filter( $result, $tag ) {

if ( $tag->name == 'menu-541') {

   $budget = isset( $_POST['menu-541'] ) ? trim( $_POST['menu-541'] ) : '';

   if( $budget == 'Select Options' ) {
      $result->invalidate( $tag, "Please select a budget!" );
   }
}

return $result;
}

