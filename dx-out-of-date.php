<?php
/**
 * Plugin Name: DX Out of Date
 * Description: Display a notice above each post of yours that has been published a while ago and may be outdated.
 * Author: nofearinc
 * Text Domain: dx-out-of-date
 * Author URI: https://devrix.com/
 * Version: 1.0.1
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

require_once 'dx-ood-helper.php';

/**
 * The main class for the Out of Date plugin
 *
 * @author nofearinc
 */
class DX_Out_Of_Date {
	/**
	 * List with all available skins.
	 *
	 * @var array skins
	 */
	public static $skins = array(
		'clean',
		'light',
		'dark',
		'red',
		'green',
		'blue',
	);

	/**
	 * The constructor function.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'dx_ood_register_settings' ), 3 );
		// Register admin pages for the plugin.
		add_action( 'admin_menu', array( $this, 'dx_ood_register_admin_page' ) );
		add_action( 'template_redirect', array( $this, 'dx_ood_top_content_filter' ) );
		add_action( 'init', array( $this, 'dx_ood_add_shortcodes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'dx_ood_enqueue_box_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'dx_ood_admin_enqueue_style' ) );
		add_filter( 'manage_posts_columns', array( $this, 'dx_ood_render_outdated_column' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'dx_ood_display_post_status' ), 6, 2 );
		add_filter( 'the_content', array( $this, 'dx_ood_top_content_filter_callback' ) );

		// Register new column for the post screen.
		$ood_setting = get_option( 'ood_setting', array() );
	}

	/**
	 * Adding the filter on the top of the content area in a single post view.
	 *
	 * Visible only if the checkbox in the admin has been clicked.
	 */
	public function dx_ood_top_content_filter() {
		$ood_setting = get_option( 'ood_setting', array() );

		if ( ! empty( $ood_setting['dx_ood_enable'] ) && is_single() ) {
			add_filter( 'the_content', array( $this, 'dx_ood_top_content_filter_callback' ) );
		}
	}

	/**
	 * Filtering the content for a single view.
	 *
	 * @uses DX_Out_Of_Date::dx_ood_outdated_box_generator for the core functionality
	 *
	 * @param string $content the original post content.
	 * @return string the altered content entry with the box.
	 */
	public function dx_ood_top_content_filter_callback( $content ) {
		$box = $this->dx_ood_outdated_box_generator();

		if ( empty( $box ) ) {
			return $content;
		}
		if ( 'bottom' === $box[1] ) {
			return $content . $box[0];
		}
		return $box[0] . $content;
	}

	/**
	 * The core function for displaying the box generator for outdated posts.
	 *
	 * Used by the the_content filter, shortcode, and standalone.
	 *
	 * @return string box markup or empty string if irrelevant.
	 */
	public static function dx_ood_outdated_box_generator() {
		$ood_setting = get_option( 'ood_setting', array() );

		// If no options are set, bail.
		if ( empty( $ood_setting['dx_ood_duration_frame'] )
			|| empty( $ood_setting['dx_ood_period'] )
			|| empty( $ood_setting['dx_ood_message'] ) ) {
			return '';
		}

		// Read the options from the admin page.
		$duration       = $ood_setting['dx_ood_duration_frame'];
		$period         = (int) $ood_setting['dx_ood_period'];
		$message        = $ood_setting['dx_ood_message'];
		$position       = is_null( $ood_setting['dx_ood_position'] ) ? 'default' : $ood_setting['dx_ood_position'];
		$ood_skin       = $ood_setting['dx_ood_skin'];
		$ood_text_color = $ood_setting['dx_ood_text_color'];

		// Calculate the interval.
		$post_date    = DX_OOD_Helper::get_post_date();
		$current_date = DX_OOD_Helper::get_current_date();
		$interval     = DX_OOD_Helper::get_date_interval( $post_date, $current_date, $duration );

		// Get option to enable or disable the notification from post custom metabox.
		$dx_ood_enable_noti = get_post_meta( get_the_ID(), 'dx_ood_enable_noti', true );

		// Don't filter if the post is recent.
		if ( $interval < $period ) {
			return '';
		}

		// Check if the notie is disabled or emabled from post custom metabox.
		if ( is_null( $dx_ood_enable_noti ) ) {
			return '';
		}

		// Generate the box.
		$box = '<div class="out-of-date" style="background-color:' . $ood_skin . ';color:' . $ood_text_color . ';" >' . do_shortcode( $message ) . '</div>';

		return array( $box, $position );
	}

	/**
	 * Register the settings page.
	 */
	public function dx_ood_register_settings() {
		include_once 'dx-ood-settings.php';

		new DX_OOD_Settings();
	}

	/**
	 * Admin pages.
	 */
	public function dx_ood_register_admin_page() {
		add_submenu_page(
			'options-general.php',
			__( 'Out of Date', 'dx-out-of-date' ),
			__( 'Out of Date', 'dx-out-of-date' ),
			'manage_options',
			'dx-out-of-date',
			array( $this, 'dx_ood_register_admin_page_callback' )
		);
	}

	/**
	 * Admin pages render admin views.
	 */
	public function dx_ood_register_admin_page_callback() {
		include_once 'dx-ood-admin.php';
	}

	/**
	 * Enqueue the admin styles.
	 */
	public function dx_ood_admin_enqueue_style() {
		$menu_css_ver          = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . '/assets/css/main.css' ) );
		$colorpicker_js_ver    = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . '/assets/js/dx-ood-colorpicker.js' ) );
		$custom_column_css_ver = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . '/assets/css/custom_column.css' ) );

		wp_enqueue_style( 'ood-main', plugin_dir_url( __FILE__ ) . '/assets/css/main.css', array(), $menu_css_ver );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'dx-ood-script-colorpicker', plugin_dir_url( __FILE__ ) . '/assets/js/dx-ood-colorpicker.js', array( 'wp-color-picker' ), array(), $colorpicker_js_ver );
		$ood_setting = get_option( 'ood_setting', array() );
		// Check if the settings is false.
		if ( $ood_setting ) {
			if ( ! isset( $ood_setting['dx_ood_show_post_status'] ) ) {
				wp_enqueue_style( 'ood-hide-column', plugin_dir_url( __FILE__ ) . '/assets/css/custom_column.css', array(), $custom_column_css_ver );
			}
		}
	}

	/**
	 * Shortcodes.
	 */
	public function dx_ood_add_shortcodes() {
		add_shortcode( 'ood_date', array( $this, 'ood_date_shortcode' ) );
		add_shortcode( 'out_of_date', array( $this, 'dx_ood_core_shortcode' ) );
	}

	/**
	 * Render the post date in a shortcode (for the message format)
	 *
	 * @param mixed $atts The attributes.
	 * @param mixed $content The content.
	 * @return string The post date.
	 */
	public function ood_date_shortcode( $atts, $content ) {
		return get_the_date();
	}

	/**
	 * Call the "Out of Date" functionality as a shortcode in a post's body
	 *
	 * @param mixed $atts The attributes.
	 * @param mixed $content The content.
	 * @return string the box HTML
	 */
	public function dx_ood_core_shortcode( $atts, $content = '' ) {
		return $this->dx_ood_outdated_box_generator();
	}

	/**
	 * Enqueue the skins for the single post view.
	 *
	 * Ignore the clean skin which doesn't set any styles.
	 */
	public function dx_ood_enqueue_box_style() {
		// Only for selected skin (non-clean) and on single page template.
		if ( is_single() ) {
			// Add the css for the postion of the message.
			$position_column_css_ver = gmdate( 'ymd-Gis', filemtime( plugin_dir_path( __FILE__ ) . '/assets/css/position.css' ) );
			wp_enqueue_style( 'ood-position', plugin_dir_url( __FILE__ ) . '/assets/css/position.css', array(), $position_column_css_ver );
		}

		$ood_setting = get_option( 'ood_setting', array() );

		if ( $ood_setting['dx_ood_custom_css'] ) { ?>
			<style>
				.out-of-date {
					<?php echo $ood_setting['dx_ood_custom_css']; ?>
				}
			</style>
			<?php
		}
	}

	/**
	 * Render the new column for the post admin screen.
	 *
	 * @param array $defaults The default columns.
	 */
	public function dx_ood_render_outdated_column( $defaults ) {
		$new_column = array();

		foreach ( $defaults as $key => $value ) {
			if ( 'date' === $key ) {
				$new_column['ood_status'] = 'Post Outdated';
			}
			$new_column[ $key ] = $value;
		}

		return $new_column;
	}

	/**
	 * Displays the post status.
	 *
	 * @param mixed $column_name The name of the column.
	 * @param mixed $post_ID The post ID.
	 */
	public function dx_ood_display_post_status( $column_name, $post_ID ) {
		if ( 'ood_status' === $column_name ) {
			$ood_setting = get_option( 'ood_setting', array() );
			// Read the options from the admin page.
			$duration = $ood_setting['dx_ood_duration_frame'];
			$period   = (int) $ood_setting['dx_ood_period'];

			// Calculate the interval.
			$post_date    = DX_OOD_Helper::get_post_date();
			$current_date = DX_OOD_Helper::get_current_date();

			$interval = DX_OOD_Helper::get_date_interval( $post_date, $current_date, $duration );

			// Check if the post is outdated.
			if ( $interval < $period ) {
				echo 'No';
			} else {
				echo 'Yes';
			}
		}
	}
}

// Aaand... Action!
new DX_Out_Of_Date();
