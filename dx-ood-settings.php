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
			__( "Message: (use [ood_date] to place the post date in text)", 'ood' ),
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
			'dx_ood_skin',
			__( "Choose a skin for your template", 'ood' ),
			array( $this, 'dx_ood_skin_callback' ),
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
		
		$out .= '<select name="ood_setting[dx_ood_duration_frame]">';
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
		
		$out .= '<select name="ood_setting[dx_ood_period]">';
		foreach ( $ood_periods as $number ) {
			$out .= sprintf( '<option value="%s" %s>%s</option>', $number, selected( $number, $selected, false ), $number );
		}
		$out .= '</select>';
		
		echo $out;
	}
	
	/**
	 * The skin dropdown renderer.
	 */
	public function dx_ood_skin_callback() {
		$selected = '';
		$out = '';
	
		$ood_skins = apply_filters( 'dx_ood_skins', DX_Out_Of_Date::$skins );
	
		if ( ! empty( $this->ood_setting ) && isset ( $this->ood_setting['dx_ood_skin'] ) ) {
			$selected = $this->ood_setting['dx_ood_skin'];
		}
	
		$out .= '<select name="ood_setting[dx_ood_skin]">';
		foreach ( $ood_skins as $skin ) {
			$out .= sprintf( '<option value="%s" %s>%s</option>', $skin, selected( $skin, $selected, false ), $skin );
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
		
		$out .= '<textarea name="ood_setting[dx_ood_message]">';
		$out .= $ood_message;
		$out .= '</textarea>';
		
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
		$out .= sprintf( '<input type="checkbox" name="ood_setting[dx_ood_enable]" %s />', checked( $ood_checked, 'on', false ) );
	
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
}