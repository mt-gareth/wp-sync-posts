<?php

namespace WPSP;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       motiontactic.com
 * @since      1.0.0
 * @package    Wp_Sync_Posts
 * @subpackage Wp_Sync_Posts/admin
 * @author     Gareth McDonald <gareth@motiontactic.com>
 */
class Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version )
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'admin/dist/wp-sync-posts-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( dirname( __FILE__ ) ) . 'admin/dist/wp-sync-posts-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'wpsp', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ] );
	}

	/**
	 * Register the plugin settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings()
	{
		register_setting( $this->plugin_name . '-settings', 'wpsp_allow_pull' );
		register_setting( $this->plugin_name . '-settings', 'wpsp_allow_push' );
		register_setting( $this->plugin_name . '-settings', 'wpsp_key' );
		register_setting( $this->plugin_name . '-settings', 'wpsp_connections' );
	}

	/**
	 * Register the admin Setting Page.
	 *
	 * @since    1.0.0
	 */
	public function register_settings_page()
	{
		add_options_page( 'WP Sync Posts Settings', 'WPSP Settings', 'manage_options', $this->plugin_name . '-settings', [ $this, 'display_settings_page' ] );
	}

	/**
	 * Register the admin post meta box.
	 *
	 * @since    1.0.0
	 */
	public function register_post_meta_box()
	{
		$screens = [ 'post', 'page' ];

		foreach ( $screens as $screen ) {

			add_meta_box(
				'testdiv',
				'WP Sync Post',
				[ $this, 'display_post_meta_box' ],
				$screen,
				'side'
			);
		}

	}

	/**
	 * Display the post meta box.
	 *
	 * @since    1.0.0
	 */
	public function display_post_meta_box()
	{
		$options = [
			'wpsp_allow_pull'  => get_option( 'wpsp_allow_pull' ),
			'wpsp_allow_push'  => get_option( 'wpsp_allow_push' ),
			'wpsp_key'         => get_option( 'wpsp_key' ),
			'wpsp_connections' => get_option( 'wpsp_connections' ) ? json_decode( get_option( 'wpsp_connections' ) ) : [],
		];
		include( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wp-sync-posts-post-meta-box.php' );
	}

	/**
	 * Display the admin Settings Page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page()
	{
		$options = [
			'wpsp_allow_pull'  => get_option( 'wpsp_allow_pull' ),
			'wpsp_allow_push'  => get_option( 'wpsp_allow_push' ),
			'wpsp_key'         => get_option( 'wpsp_key' ),
			'wpsp_connections' => get_option( 'wpsp_connections' ) ? json_decode( get_option( 'wpsp_connections' ) ) : [],
		];
		include( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wp-sync-posts-admin-display.php' );
	}
}
