<?php

namespace WPSP;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       motiontactic.com
 * @since      1.0.0
 *
 * @package    Wp_Sync_Posts
 * @subpackage Wp_Sync_Posts/includes
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
 * @package    Wp_Sync_Posts
 * @subpackage Wp_Sync_Posts/includes
 * @author     Gareth McDonald <gareth@motiontactic.com>
 */
class App
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
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
	public function __construct()
	{
		if ( defined( 'WP_SYNC_POSTS_VERSION' ) ) {
			$this->version = WP_SYNC_POSTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-sync-posts';
		$this->loader = new Loader();

		$this->load_parsers();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_ajax_hooks();

	}

	/**
	 * Load the required parsers for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_parsers()
	{
		new Parsers\Images\ACF;
		new Parsers\Images\ContentUrl;
		new Parsers\Images\FeaturedImage;
		new Parsers\Images\MetaUrl;
		new Parsers\Images\WPGallery;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Sync_Posts_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_settings_page' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_post_meta_box' );

	}

	/**
	 * Register all of the hooks related to the ajax functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_ajax_hooks()
	{

		$ajax = new Ajax();
		$ajax_nopriv = new AjaxNopriv();

		$this->loader->add_action( 'init', $ajax, 'admin_ajax' );
		$this->loader->add_action( 'init', $ajax_nopriv, 'nopriv_ajax' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version()
	{
		return $this->version;
	}

}
