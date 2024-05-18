<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Wcrecorder
 * @subpackage Wcrecorder/includes
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
 * @package    Wcrecorder
 * @subpackage Wcrecorder/includes
 * @author     Devjoo <contact@easeare.com>
 */
class Wcrecorder {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wcrecorder_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WCRECORDER_VERSION' ) ) {
			$this->version = WCRECORDER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wcrecorder';

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
	 * - Wcrecorder_Loader. Orchestrates the hooks of the plugin.
	 * - Wcrecorder_i18n. Defines internationalization functionality.
	 * - Wcrecorder_Admin. Defines all hooks for the admin area.
	 * - Wcrecorder_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcrecorder-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcrecorder-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcrecorder-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcrecorder-public.php';

		$this->loader = new Wcrecorder_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wcrecorder_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wcrecorder_i18n();

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

		$plugin_admin = new Wcrecorder_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wcrecorder_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'custom_checkout_field', 5);
		$this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'customised_checkout_field_process');
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'custom_checkout_field_update_order_meta');
		$this->loader->add_action( 'woocommerce_admin_order_data_after_billing_address', $plugin_public, 'wc_sound_in_order_view_page', 10, 1 );
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'display_custom_field_on_order_received', 5); // Priority set for top
		$this->loader->add_action( 'woocommerce_view_order', $plugin_public, 'display_custom_field_on_order_received', 5); // Priority set for top

		$this->loader->add_action( 'init', $plugin_public, 'myrecords_tab_add_my_account_endpoint');
		$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public, 'myrecords_my_account_menu_items');
		$this->loader->add_action( 'woocommerce_account_wcsrecords_endpoint', $plugin_public, 'myrecords_my_account_endpoint_content');
		$this->loader->add_action( 'wp_ajax_upload_from_blob', $plugin_public, 'upload_from_blob');
		$this->loader->add_action( 'wp_ajax_nopriv_upload_from_blob', $plugin_public, 'upload_from_blob');

		$this->loader->add_action('admin_post_delete_sound', $plugin_public, 'handle_delete_sound');
		$this->loader->add_action('admin_post_nopriv_delete_sound', $plugin_public, 'handle_delete_sound');
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
	 * @return    Wcrecorder_Loader    Orchestrates the hooks of the plugin.
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

}
