<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://digitaldyna.com
 * @since      1.0.0
 *
 * @package    CF7_CVM
 * @subpackage CF7_CVM/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CF7_CVM
 * @subpackage CF7_CVM/includes
 * @author     Support <support@digitaldyna.com>
 */
class CF7_CVM {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CF7_CVM_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The priority for validation.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The priority for validation.
	 */
	protected $cf7_validation_priority;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CF7CVM_VERSION' ) ) {
			$this->version = CF7_CVM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->cf7_validation_priority = 9;
		$this->plugin_name = 'cf7cvm';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - CF7_CVM_Loader. Orchestrates the hooks of the plugin.
	 * - CF7_CVM_i18n. Defines internationalization functionality.
	 * - CF7_CVM_Admin. Defines all hooks for the admin area.
	 * - CF7_CVM_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7cvm-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7cvm-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cf7cvm-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cf7cvm-public.php';

		$this->loader = new CF7_CVM_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the CF7_CVM_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CF7_CVM_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new CF7_CVM_Admin( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'wpcf7_editor_panels', $plugin_admin, 'cf7cvm_add_panel' );
		$this->loader->add_action( 'wpcf7_after_save', $plugin_admin, 'cf7cvm_store_messages' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new CF7_CVM_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wpcf7_validate_text*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_email*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_textarea*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_tel*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_url*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_checkbox*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_number*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		//$this->loader->add_action( 'wpcf7_validate_range*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_date*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_select*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_radio', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_file*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		//$this->loader->add_action( 'wpcf7_validate_acceptance*', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );
		$this->loader->add_action( 'wpcf7_validate_quiz', $plugin_public, 'cf7cv_custom_form_validation', $this->get_cf7_validation_priority(), 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    CF7_CVM_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the priority for validation.
	 *
	 * @since     1.0.0
	 * @return    string    The priority for validation of cf7 validation message.
	 */
	public function get_cf7_validation_priority() {
		return $this->cf7_validation_priority;
	}

}
