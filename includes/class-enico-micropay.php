<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://enico.info
 * @since      1.5.0
 *
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/includes
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
 * @since      1.5.0
 * @package    Enico_Micropay
 * @subpackage Enico_Micropay/includes
 * @author     Enico <info@enico.info>
 */
class Enico_Micropay {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.5.0
	 * @access   protected
	 * @var      Enico_Micropay_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.5.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.5.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.5.0
	 */
	public function __construct() {
		if ( defined( 'ENICO_MICROPAY_VERSION' ) ) {
			$this->version = ENICO_MICROPAY_VERSION;
		} else {
			$this->version = '1.5.0';
		}
		$this->plugin_name = 'enico-micropay';

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
	 * - Enico_Micropay_Loader. Orchestrates the hooks of the plugin.
	 * - Enico_Micropay_i18n. Defines internationalization functionality.
	 * - Enico_Micropay_Admin. Defines all hooks for the admin area.
	 * - Enico_Micropay_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.5.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-enico-micropay-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-enico-micropay-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-enico-micropay-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-enico-micropay-public.php';

		$this->loader = new Enico_Micropay_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Enico_Micropay_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.5.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Enico_Micropay_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Enico_Micropay_Admin( $this->get_plugin_name(), $this->get_version() );

		// Enqueue styles and scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Rest API routes
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_rest_routes');

		// Add ADMIN MENU
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'custom_menu_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		// Add post metaboxes
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_meta_boxes' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_meta_box', 10, 2 );
		
		// Add settings link action in the installed plugins table
		$this->loader->add_filter( 'plugin_action_links_' . $this->get_plugin_name() . '/' . $this->get_plugin_name() . '.php', $plugin_admin, 'plugin_action_links', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.5.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Enico_Micropay_Public( $this->get_plugin_name(), $this->get_version() );

		// Enqueue styles and scripts
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 100 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Filters the content
		$this->loader->add_action( 'the_content', $plugin_public, 'the_content', 10, 1 );

		// Filters the document title
		$this->loader->add_filter( 'wp_title', $plugin_public, 'document_title', 999, 2 );
		$this->loader->add_filter( 'pre_get_document_title', $plugin_public, 'document_title', 10, 1);

		// Process validation link
		$this->loader->add_action( 'template_redirect', $plugin_public, 'process_link_validation');

		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.5.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.5.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.5.0
	 * @return    Enico_Micropay_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.5.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
