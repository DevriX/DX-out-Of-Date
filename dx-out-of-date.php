<?php
/**
 * Plugin Name: DX Out of Date
 * Description: Display a notice above each post of yours that has been published a while ago and may be outdated.
 * Author: nofearinc
 * Text Domain: dx-out-of-date
 * Author URI: http://devwp.eu/
 * Version: 0.3
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

include_once 'dx-ood-helper.php';

/**
 * The main class for the Out of Date plugin
 *
 * @author nofearinc
 *
 */
class DX_Out_Of_Date {
	
	/**
	 *
	 * @var array skins list with all available skins
	 */
	public static $skins = array(
			'clean',
			'light',
			'dark',
			'red',
			'green',
			'blue'
	);
	
	public function __construct() {
		add_action('admin_init', array( $this, 'register_settings' ), 3 );
		
		// register admin pages for the plugin
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'template_redirect', array( $this, 'top_content_filter' ) );
		add_action( 'init', array( $this, 'add_shortcodes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_box_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_style' ) );
		add_filter( 'manage_posts_columns', array( $this,'render_outdated_column' ) );
		add_action( 'manage_posts_custom_column', array( $this,'display_post_status' ), 6, 2 );
		add_filter( 'the_content', array( $this, 'top_content_filter_callback' ) );

		//register new column for the post screen
		$ood_setting= get_option( 'ood_setting', array() );
	}
	
	/**
	 * Adding the filter on the top of the content area in a single post view.
	 *
	 * Visible only if the checkbox in the admin has been clicked.
	 */
	public function top_content_filter() {
		$ood_setting = get_option( 'ood_setting', array() );
		
		if ( ! empty( $ood_setting[ 'dx_ood_enable' ] ) && is_single() ) {
			add_filter( 'the_content', array( $this, 'top_content_filter_callback' ) );
		}
	}
	
	/**
	 * Filtering the content for a single view.
	 *
	 * @uses DX_Out_Of_Date::outdated_box_generator for the core functionality
	 *
	 * @param string $content the original post content
	 * @return string the altered content entry with the box
	 */
	public function top_content_filter_callback( $content ) {
		$box = $this->outdated_box_generator();
		
		if( $box[ 1 ] == 'bottom' ) {
			return $content . $box[ 0 ];
		}
		return $box[ 0 ] . $content;
	}
	
	/**
	 * The core function for displaying the box generator for outdated posts.
	 *
	 * Used by the the_content filter, shortcode, and standalone.
	 *
	 * @return string box markup or empty string if irrelevant.
	 */
	public static function outdated_box_generator() {
		$ood_setting = get_option( 'ood_setting', array() );
		
		// If no options are set, bail
		if ( empty( $ood_setting[ 'dx_ood_duration_frame' ] )
		||   empty( $ood_setting[ 'dx_ood_period' ] )
		||   empty( $ood_setting[ 'dx_ood_message' ] ) ) {
			return '';
		}
		
		// Read the options from the admin page
		$duration = $ood_setting[ 'dx_ood_duration_frame' ];
		$period = ( int ) $ood_setting[ 'dx_ood_period' ];
		$message = $ood_setting[ 'dx_ood_message' ];
		$position = is_null( $ood_setting[ 'dx_ood_position' ] ) ? 'default' : $ood_setting[ 'dx_ood_position' ];
		$ood_skin = $ood_setting[ 'dx_ood_skin' ];
		$ood_text_color = $ood_setting['dx_ood_text_color' ];


		// Calculate the interval
		$post_date = DX_OOD_Helper::get_post_date();
		$current_date = DX_OOD_Helper::get_current_date();
		
		$interval = DX_OOD_Helper::get_date_interval( $post_date, $current_date, $duration );

		//get option to enable or disable the notification from post custom metabox
		$dx_ood_enable_noti = get_post_meta( get_the_ID(), 'dx_ood_enable_noti', true );
		
		// Don't filter if the post is recent.
		if ( $interval < $period ) {
			return '';
		}

		// check if the notie is disabled or emabled from post custom metabox
		if ( is_null( $dx_ood_enable_noti ) ) {
			return '';
		}
		
		// Generate the box
		$box = '<div class="out-of-date" style="background-color:' . $ood_skin . ';color:' . $ood_text_color . ';" >' . do_shortcode( $message ) . '</div>';
		
		return array( $box, $position );
	}
	
	/**
	 * Register the settings page
	 */
	public function register_settings() {
		include_once 'dx-ood-settings.php';

		new DX_OOD_Settings();
	}
	
	/**
	 * Admin pages
	 */
	public function register_admin_page() {
		add_submenu_page(
			'options-general.php',
			__( "Out of Date", 'dx-out-of-date' ),
			__( "Out of Date", 'dx-out-of-date' ),
			'manage_options',
			'dx-out-of-date',
			array( $this, 'register_admin_page_callback' )
		);
	}
	
	/**
	 * Admin pages render admin views
	 */
	public function register_admin_page_callback() {
		include_once 'dx-ood-admin.php';
	}

	public function admin_enqueue_style() {	
		$menu_css_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . '/assets/css/main.css' ) );
		$colorpicker_js_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . '/assets/js/dx-ood-colorpicker.js' ) );
		$custom_column_css_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . '/assets/css/custom_column.css' ) );

		wp_enqueue_style( 'ood-main', plugin_dir_url( __FILE__ ) . '/assets/css/main.css', array(),$menu_css_ver );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'dx-ood-script-colorpicker', plugin_dir_url( __FILE__ ) . '/assets/js/dx-ood-colorpicker.js', array( 'wp-color-picker' ), array(), $colorpicker_js_ver );
		$ood_setting = get_option( 'ood_setting', array() );
		// check if the settings is false
		if ( $ood_setting ) {
			if ( ! isset( $ood_setting[ 'dx_ood_show_post_status' ] ) ) {
				wp_enqueue_style( 'ood-hide-column', plugin_dir_url( __FILE__ ) . '/assets/css/custom_column.css', array(), $custom_column_css_ver );
			}
		}
	}
	
	/**
	 * Shortcodes
	 */
	public function add_shortcodes() {
		add_shortcode( 'ood_date', array( $this, 'ood_date_shortcode' ) );
		add_shortcode( 'out_of_date', array( $this, 'ood_core_shortcode' ) );
	}
	
	/**
	 * Render the post date in a shortcode (for the message format)
	 *
	 * @return string the post date
	 */
	public function ood_date_shortcode( $atts, $content ) {
		return get_the_date();
	}
	
	/**
	 * Call the "Out of Date" functionality as a shortcode in a post's body
	 *
	 * @return string the box HTML
	 */
	public function ood_core_shortcode( $atts, $content = '' ) {
		return $this->outdated_box_generator();
	}
	
	/**
	 * Enqueue the skins for the single post view.
	 *
	 * Ignore the clean skin which doesn't set any styles.
	 */
	public function enqueue_box_style() {
		// Only for selected skin (non-clean) and on single page template
		if ( is_single() ) {
			//add the css for the postion of the message
			$position_column_css_ver = date( "ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . '/assets/css/position.css' ) );
			wp_enqueue_style( 'ood-position', plugin_dir_url( __FILE__ ) . '/assets/css/position.css', array(), $position_column_css_ver );
		}
	}

	/**
	 * render the new column for the post admin screen
	 *
	 */

	public function render_outdated_column( $defaults )	{
		$new_column = array();
	   

		foreach ( $defaults as $key => $value ) {
			if ( $key == 'date' ) {
				$new_column[ 'ood_status' ] = 'Post Outdated';
			}
			$new_column[ $key ]=$value;
		}

		return $new_column;
	}

	public function display_post_status( $column_name, $post_ID ) {
		
		if ( $column_name == 'ood_status' ) {
			$ood_setting = get_option( 'ood_setting', array() );
			// Read the options from the admin page
			$duration = $ood_setting[ 'dx_ood_duration_frame' ];
			$period = ( int ) $ood_setting[ 'dx_ood_period' ];

			// Calculate the interval
			$post_date = DX_OOD_Helper::get_post_date();
			$current_date = DX_OOD_Helper::get_current_date();
			
			$interval = DX_OOD_Helper::get_date_interval( $post_date, $current_date, $duration );

			//check if the post is outdated
			if ( $interval < $period ) {
				echo 'No';
			} else {
				echo "Yes";
			}
		}
	}
}

// Aaand... Action!
new DX_Out_Of_Date();
