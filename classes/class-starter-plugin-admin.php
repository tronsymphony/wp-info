<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Starter_Plugin_Admin Class
 *
 * @class Starter_Plugin_Admin
 * @version 1.0.0
 * @since 1.0.0
 * @package Starter_Plugin
 * @author Jeffikus
 */
final class Starter_Plugin_Admin {
	/**
	 * Starter_Plugin_Admin The single instance of Starter_Plugin_Admin.
	 * @var     object
	 * @access  private
	 * @since   1.0.0
	 */
	private static $instance = null;

	/**
	 * The string containing the dynamically generated hook token.
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $hook;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct() {
		// Register the settings with WordPress.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Register the settings screen within WordPress.
		add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );
	}

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
	 * Register the admin screen.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function register_settings_screen() {
		$this->hook = add_submenu_page( 'options-general.php', __( 'Starter Plugin Settings', 'starter-plugin' ), __( 'Starter Plugin', 'starter-plugin' ), 'manage_options', 'starter-plugin', array( $this, 'settings_screen' ) );
	}

	/**
	 * Output the markup for the settings screen.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function settings_screen() {
		global $title;
		$sections = Starter_Plugin()->settings->get_settings_sections();
		$tab      = $this->get_current_tab( $sections );
		?>
		<div class="wrap starter-plugin-wrap">
			<?php
				$this->admin_header_html( $sections, $title );
			?>
			<form action="options.php" method="post">
				<?php
					settings_fields( 'starter-plugin-settings-' . $tab );
					do_settings_sections( 'starter-plugin-' . $tab );
					submit_button( __( 'Save Changes', 'starter-plugin' ) );
				?>
			</form>
		</div><!--/.wrap-->
		<?php
	}

	/**
	 * Register the settings within the Settings API.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function register_settings() {
		$sections = Starter_Plugin()->settings->get_settings_sections();
		if ( 0 < count( $sections ) ) {
			foreach ( $sections as $k => $v ) {
				register_setting( 'starter-plugin-settings-' . sanitize_title_with_dashes( $k ), 'starter-plugin-' . $k, array( $this, 'validate_settings' ) );
				add_settings_section( sanitize_title_with_dashes( $k ), $v, array( $this, 'render_settings' ), 'starter-plugin-' . $k, $k, $k );
			}
		}
	}

	/**
	 * Render the settings.
	 * @access  public
	 * @param  array $args arguments.
	 * @since   1.0.0
	 * @return  void
	 */
	public function render_settings( $args ) {
		$token  = $args['id'];
		$fields = Starter_Plugin()->settings->get_settings_fields( $token );

		if ( 0 < count( $fields ) ) {
			foreach ( $fields as $k => $v ) {
				$args       = $v;
				$args['id'] = $k;

				add_settings_field( $k, $v['name'], array( Starter_Plugin()->settings, 'render_field' ), 'starter-plugin-' . $token, $v['section'], $args );
			}
		}
	}

	/**
	 * Validate the settings.
	 * @access  public
	 * @since   1.0.0
	 * @param   array $input Inputted data.
	 * @return  array        Validated data.
	 */
	public function validate_settings( $input ) {
		$sections = Starter_Plugin()->settings->get_settings_sections();
		// $tab      = $this->_get_current_tab( $sections );
		return Starter_Plugin()->settings->validate_settings( $input, $tab );
	}

	/**
	 * Return marked up HTML for the header tag on the settings screen.
	 * @access  public
	 * @since   1.0.0
	 * @param   array  $sections Sections to scan through.
	 * @param   string $title    Title to use, if only one section is present.
	 * @return  string           The current tab key.
	 */
	public function get_admin_header_html( $sections, $title ) {
		$defaults = array(
			'tag'     => 'h2',
			'atts'    => array( 'class' => 'starter-plugin-wrapper' ),
			'content' => $title,
		);

		$args = $this->get_admin_header_data( $sections, $title );

		$args = wp_parse_args( $args, $defaults );

		$atts = '';
		if ( 0 < count( $args['atts'] ) ) {
			foreach ( $args['atts'] as $k => $v ) {
				$atts .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
			}
		}

		$response = '<' . esc_attr( $args['tag'] ) . $atts . '>' . $args['content'] . '</' . esc_attr( $args['tag'] ) . '>' . "\n";

		return $response;
	}

	/**
	 * Print marked up HTML for the header tag on the settings screen.
	 * @access  public
	 * @since   1.0.0
	 * @param   array  $sections Sections to scan through.
	 * @param   string $title    Title to use, if only one section is present.
	 * @return  string           The current tab key.
	 */
	public function admin_header_html( $sections, $title ) {
		echo $this->get_admin_header_html( $sections, $title ); /* phpcs:ignore */
	}

	/**
	 * Return the current tab key.
	 * @access  private
	 * @since   1.0.0
	 * @param   array  $sections Sections to scan through for a section key.
	 * @return  string           The current tab key.
	 */
	private function get_current_tab( $sections = array() ) {
		$response = key( $sections );

		if ( isset( $_GET['tab'] ) && check_admin_referer( 'starter_plugin_switch_settings_tab', 'starter_plugin_switch_settings_tab' ) ) {
			$response = sanitize_title_with_dashes( $_GET['tab'] );
		}

		return $response;
	}

	/**
	 * Return an array of data, used to construct the header tag.
	 * @access  private
	 * @since   1.0.0
	 * @param   array  $sections Sections to scan through.
	 * @param   string $title    Title to use, if only one section is present.
	 * @return  array            An array of data with which to mark up the header HTML.
	 */
	private function get_admin_header_data( $sections, $title ) {
		$response = array(
			'tag'     => 'h2',
			'atts'    => array( 'class' => 'starter-plugin-wrapper' ),
			'content' => $title,
		);

		if ( is_array( $sections ) && 1 < count( $sections ) ) {
			$response['content']       = '';
			$response['atts']['class'] = 'nav-tab-wrapper';

			$tab = $this->get_current_tab( $sections );

			foreach ( $sections as $key => $value ) {
				$class = 'nav-tab';
				if ( $tab === $key ) {
					$class .= ' nav-tab-active';
				}

				$response['content'] .= '<a href="' . wp_nonce_url( admin_url( 'options-general.php?page=starter-plugin&tab=' . sanitize_title_with_dashes( $key ) ), 'starter_plugin_switch_settings_tab', 'starter_plugin_switch_settings_tab' ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $value ) . '</a>';
			}
		}

		return (array) apply_filters( 'starter_plugin_get_admin_header_data', $response );
	}
}
