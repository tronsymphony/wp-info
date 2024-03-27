<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Starter_Plugin_Admin_Bar Class
 *
 * @class Starter_Plugin_Admin_Bar
 * @version 1.0.0
 * @since 1.0.0
 * @package Starter_Plugin
 * @author Jeffikus
 */
final class Starter_Plugin_Admin_Bar {

		/**
	 * Starter_Plugin_Admin The single instance of Starter_Plugin_Admin.
	 * @var     object
	 * @access  private
	 * @since   1.0.0
	 */
private static $instance = null;

	private $screen_id_options;

		/**
	 * Main Starter_Plugin_Admin Instance
	 *
	 * Ensures only one instance of Starter_Plugin_Admin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Main Starter_Plugin_Admin instance
	 */
public static function instance() {
	if ( is_null( self::$instance ) ) {
		self::$instance = new self();
	}
	return self::$instance;
}

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 */
public function __construct() {
	add_action( 'rest_api_init', array( $this, 'register_my_rest_routes' ) );
	add_action( 'rest_api_init', array( $this, 'rest_api_register_route' ) );

	add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );
	add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );

	/*
	* Add custom routes to the Rest API
	*/
	public function rest_api_register_route() {

		//Add the GET 'react-settings-page/v1/options' endpoint to the Rest API
		register_rest_route(
			'react-settings-page/v1',
			'/options',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_api_react_settings_page_read_options_callback' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/*
	* Callback for the GET 'react-settings-page/v1/options' endpoint of the Rest API
	*/
	public function rest_api_react_settings_page_read_options_callback( $data ) {

		//Check the capability
		if (!current_user_can( 'manage_options' )) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to view the options.',
				array( 'status' => 403 )
			);
		}

		//Generate the response
		$response                    = array();
		$response['plugin_option_1'] = get_option( 'plugin_option_1' );
		$response['plugin_option_2'] = get_option( 'plugin_option_2' );

		//Prepare the response
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
 * Registers all rest routes for this plugin.
 */
	public function register_my_rest_routes() {
		register_rest_route(
			'react-settings-page/v1',
			'/options',
			array(
				'methods'             => WP_REST_Server::CREATABLE, // This is equivalent to 'POST'.
				'callback'            => array( $this, 'rest_api_react_settings_page_update_options_callback' ),
				'permission_callback' => '__return_true', // Allows public access. Consider using a capability check for more security.
			)
		);
	}

	public function rest_api_react_settings_page_update_options_callback( $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to update the DAEXT UI Test options.',
				array( 'status' => 403 )
			);
		}

		//Get the data and sanitize
		//Note: In a real-world scenario, the sanitization function should be based on the option type.
		$plugin_option_1 = sanitize_text_field( $request->get_param( 'plugin_option_1' ) );
		$plugin_option_2 = sanitize_text_field( $request->get_param( 'plugin_option_2' ) );

		//Update the options
		update_option( 'plugin_option_1', $plugin_option_1 );
		update_option( 'plugin_option_2', $plugin_option_2 );

		$response = new WP_REST_Response( 'Data successfully added.', '200' );

		return $response;
	}

	/**
 * Method to run during plugin activation
 */
	public function activate_plugin() {
		// Add options with default values upon plugin activation
		add_option( 'plugin_option_1', 'default_value_1' );
		add_option( 'plugin_option_2', 'default_value_2' );
	}

	public function me_add_admin_menu() {
		add_menu_page(
			esc_html__( 'UT', 'react-settings-page' ),
			esc_html__( 'UI Test', 'react-settings-page' ),
			'manage_options',
			'react-settings-page-options',
			array( $this, 'me_display_menu_options' ),
		);

		// Use $this-> to refer to the class property
		$this->screen_id_options = add_submenu_page(
			'react-settings-page-options',
			esc_html__( 'UT - Options', 'react-settings-page' ),
			esc_html__( 'Options', 'react-settings-page' ),
			'manage_options',
			'react-settings-page-options',
			array( $this, 'me_display_menu_options' ),
		);
	}

	public function me_display_menu_options() {
		$plugin_dir_path = plugin_dir_path( __FILE__ );
		require $plugin_dir_path . '../includes/options.php';
	}

	public function enqueue_admin_scripts( $hook_suffix ) {

		if ( $hook_suffix == $this->screen_id_options ) {

			$plugin_url = plugin_dir_url( __FILE__ );

			wp_enqueue_script(
				'react-settings-page-menu-options',
				$plugin_url . '../build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				'1.00',
				true
			);

		}
	}
}
