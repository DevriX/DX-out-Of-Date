<?php

/**
 * The Settings API wrapper class, managing all the things.
 * 
 * @author nofearinc
 *
 */
class DX_OOD_Settings {
	
	/**
	 * 
	 * @var array ood_setting an array with all the options
	 */
	private $ood_setting;
	
	/**
	 * Construct me
	 */
	public function __construct() {
		$this->ood_setting = get_option( 'ood_setting', array() );
		
		// register the checkbox
		add_action('admin_init', array( $this, 'register_settings' ) );
		//register the custom metabox for post
		add_action( 'load-post.php', array( $this, 'register_cusom_post_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'register_cusom_post_metabox' ) );
		add_action("save_post", array( $this, 'save_custom_metabox'));
	}
		
	/**
	 * Register the setting, section and fields
	 * 
	 */
	public function register_settings() {
		register_setting( 'ood_setting', 'ood_setting', array( $this, 'dx_validate_settings' ) );

		add_settings_section(
			'ood_settings_section',
			__( "Out of Date admin panel", 'ood' ),
			array($this, 'ood_settings_callback'),
			'dx-ood'
		);
	
		add_settings_field(
			'dx_ood_duration_frame',
			__( "Duration: ", 'ood' ),
			array( $this, 'dx_ood_duration_callback' ),
			'dx-ood',
			'ood_settings_section'
		);
		
		add_settings_field(
			'dx_ood_period',
			__( "Period: ", 'ood' ),
			array( $this, 'dx_ood_period_callback' ),
			'dx-ood',
			'ood_settings_section'
		);
		
		add_settings_field(
			'dx_ood_message',
			__( "Message:", 'ood' ),
			array( $this, 'dx_ood_message_callback' ),
			'dx-ood',
			'ood_settings_section'
		);
		
		add_settings_field(
			'dx_ood_enable',
			__( "Enable the message by default on all outdated posts (display the box in the template)", 'ood' ),
			array( $this, 'dx_ood_enable_callback' ),
			'dx-ood',
			'ood_settings_section'
		);

		add_settings_field(
			'dx_ood_show_post_status',
			__( "Enable the display of the status in post (add new column to the \"All Posts\" for the status)", 'ood' ),
			array( $this, 'dx_ood_show_post_status_callback' ),
			'dx-ood',
			'ood_settings_section'
		);
		
		add_settings_field(
			'dx_ood_skin',
			__( "Choose a skin for your template", 'ood' ),
			array( $this, 'dx_ood_skin_callback' ),
			'dx-ood',
			'ood_settings_section'
		);
		add_settings_field(
			'dx_ood_text_color',
			__( "Choose a color for the text of the message", 'ood' ),
			array( $this, 'dx_ood_text_color_callback' ),
			'dx-ood',
			'ood_settings_section'
		);

		add_settings_field(
			'dx_ood_position',
			__( "Choose the postion of the message", 'ood' ),
			array( $this, 'dx_ood_position_callback' ),
			'dx-ood',
			'ood_settings_section'
		);
	}
	
	public function ood_settings_callback() {
		echo '<p>' . _e( 'Select how old a post should be in order to be marked as outdated.', 'ood' ) . '</p>';
	}
	
	/**
	 * The duration dropdown renderer.
	 */
	public function dx_ood_duration_callback() {
		$selected = '';
		$out = '';
		
		$ood_durations = array(
			'years' => __( 'Years', 'ood' ),
			'months' => __( 'Months', 'ood' ),
			'days' => __( 'Days', 'ood' ),
		);
		
		$ood_durations = apply_filters( 'dx_ood_durations', $ood_durations );
		
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_duration_frame'] ) ) {
			$selected = $this->ood_setting['dx_ood_duration_frame'];
		}
		
		$out .= '<select name="ood_setting[dx_ood_duration_frame]" class="dx-ood-form-control">';
		foreach ( $ood_durations as $value => $label ) {
			$out .= sprintf( '<option value="%s" %s>%s</option>', $value, selected( $value, $selected, false ), $label );
		}
		$out .= '</select>';

		echo $out;
	}
	
	/**
	 * The period dropdown renderer. 
	 */
	public function dx_ood_period_callback() {
		$selected = '';
		$out = '';
		
		$ood_periods = apply_filters( 'dx_ood_periods', range( 1, 40 ) );
		
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_period'] ) ) {
			$selected = $this->ood_setting['dx_ood_period'];
		}
		
		$out .= '<select name="ood_setting[dx_ood_period]" class="dx-ood-form-control" >';
		foreach ( $ood_periods as $number ) {
			$out .= sprintf( '<option value="%s" %s>%s</option>', $number, selected( $number, $selected, false ), $number );
		}
		$out .= '</select>';
		
		echo $out;
	}
	
	/**
	 * The skin color picker renderer.
	 */
	public function dx_ood_skin_callback() {
		$selected = '';
		$out = '';
	
		$ood_skins = apply_filters( 'dx_ood_skins', DX_Out_Of_Date::$skins );
	
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_skin'] ) ) {
			$skin_color = $this->ood_setting['dx_ood_skin'];
		}else
		{
			//set the default color here
			$skin_color = '#fff';
		}
	
		$out .= '<input name="ood_setting[dx_ood_skin]" id="dx_ood_skin" class="" value="'.$skin_color.'" />';
	
		echo $out;
	}
	/**
	 * The text color picker renderer.
	 */
	public function dx_ood_text_color_callback() {
		$selected = '';
		$out = '';
	
		$ood_skins = apply_filters( 'dx_ood_text_color', DX_Out_Of_Date::$skins );
	
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_text_color'] ) ) {
			$text_color = $this->ood_setting['dx_ood_text_color'];
		}else
		{
			//set the default color here
			$text_color = '#000';
		}
	
		$out .= '<input name="ood_setting[dx_ood_text_color]" id="dx_ood_text_color" class="" value="'.$text_color.'" />';
	
		echo $out;
	}
	/**
	 * The postion dropdown renderer. 
	 */
	public function dx_ood_position_callback() {
		$selected = '';
		$out = '';
		
		$ood_position = array(
			'default' => __( 'Default', 'ood' ),
			'top' => __( 'Top', 'ood' ),
			'top-left' => __( 'Top Left', 'ood' ),
			'top-right' => __( 'Top Right', 'ood' ),
			'bottom' => __( 'Bottom', 'ood' ),
			'bottom-left' => __( 'Bottom Left', 'ood' ),
			'bottom-right' => __( 'Bottom Right', 'ood' ),
		);
		
		$ood_position = apply_filters( 'dx_ood_position', $ood_position );
		
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_position'] ) ) {
			$selected = $this->ood_setting['dx_ood_position'];
		}
		
		$out .= '<select name="ood_setting[dx_ood_position]" class="dx-ood-form-control">';
		foreach ( $ood_position as $value => $label ) {
			$out .= sprintf( '<option value="%s" %s>%s</option>', $value, selected( $value, $selected, false ), $label );
		}
		$out .= '</select>';

		echo $out;
	}
	
	/**
	 * The message callback renderer. 
	 */
	public function dx_ood_message_callback() {
		$old_value = 'This entry has been published on [ood_date] and may be out of date.';
		$out = '';
		
		// Allows for setting default messages
		$ood_message = apply_filters( 'dx_ood_message', $old_value );
		
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_message'] ) ) {
			$ood_message = $this->ood_setting['dx_ood_message'];
		}
		
		$out .= '<textarea name="ood_setting[dx_ood_message]" class="dx-ood-form-control dx-ood-form-textarea" >';
		$out .= $ood_message;
		$out .= '</textarea>';
		$out .= '<p><b>(use [ood_date] to place the post date in text)</b></p>';
		
		echo $out;
	}
	
	/**
	 * The "Enable on posts template" checkbox renderer.
	 */
	public function dx_ood_enable_callback() {
		$checked = false;
		$out = '';
	
		$ood_checked = apply_filters( 'dx_ood_enable', $checked );
	
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_enable'] ) ) {
			$ood_checked = $this->ood_setting['dx_ood_enable'];
		}
		$out .= sprintf( '<input type="checkbox" class="dx-ood-form-control" name="ood_setting[dx_ood_enable]" %s />', checked( $ood_checked, 'on', false ) );
	
		echo $out;
	}

	/**
	 * The "Enable the display of status on view all post" checkbox renderer.
	 */
	public function dx_ood_show_post_status_callback() {
		$checked = false;
		$out = '';
	
		$ood_checked = apply_filters( 'dx_ood_show_post_status', $checked );
	
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_show_post_status'] ) ) {
			$ood_checked = $this->ood_setting['dx_ood_show_post_status'];
		}
		$out .= sprintf( '<input type="checkbox" class="dx-ood-form-control" name="ood_setting[dx_ood_show_post_status]" %s />', checked( $ood_checked, 'on', false ) );
	
		echo $out;
	}
	
	/**
	 * Validate Settings
	 * 
	 * Filter the submitted data as per your request and return the array
	 * 
	 * @param array $input
	 */
	public function dx_validate_settings( $input ) {
		// No validation occurs as everything is possible. 
		// Message could get all flavors of HTML too, and it's admin-limited.
		return $input;
	}

	// register custom metabox
	public function register_cusom_post_metabox()
	{
		add_action( 'add_meta_boxes', array($this,'custom_metabox_settings'));
	}

	// set the settings of custom metabox
	public function custom_metabox_settings() {

	  add_meta_box(
	    'dx_ood_enable_noti',
	    esc_html__( 'Out of Date Notification', 'Show Notify?' ),
	    array($this,'render_custom_metabox_show_noti'), 
	    'post',
	    'side',
	    'high'
	  );
	}

	// render the html view of custom metabox for showing noti
	public function render_custom_metabox_show_noti()
	{
		$dx_ood_enable_noti = get_post_meta( get_the_ID(), 'dx_ood_enable_noti', TRUE );
		?>
		<p>
			<label for="dx_ood_enable_noti">Show Notification if outdated?</label>
			<input type="checkbox" name="dx_ood_enable_noti" id="dx_ood_enable_noti" <?= !is_null($dx_ood_enable_noti)? 'checked':''; ?> />
		</p>
		<?php
	}

	//save custom metabox when post is update or publish
	public function save_custom_metabox($post_ID = 0)
	{
	 	$post_ID = (int) $post_ID;
	    $post_type = get_post_type( $post_ID );
	    $post_status = get_post_status( $post_ID );
	    if ( "post" == $post_type && "auto-draft" != $post_status ) {
	        update_post_meta($post_ID, "dx_ood_enable_noti", $_POST["dx_ood_enable_noti"]);
	    }
	    return $post_ID;
	}
}