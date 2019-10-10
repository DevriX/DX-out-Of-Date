<?php

/**
 * Some helper functions for the Out of Date plugin
 * 
 * @author nofearinc
 *
 */
class DX_OOD_Helper {
	
	/**
	 * Get the current post date.
	 * 
	 * Note: you should be inside of the loop to run that.
	 * 
	 * @uses get_the_date()
	 * 
	 * @return DateTime date object
	 */
	public static function get_post_date() {
		$post_date = get_the_date('Y-m-d');
		
		$date = new DateTime( $post_date );
		
		return $date;
	}
	
	/**
	 * Get the current date
	 * 
	 * @return DateTime date object
	 */
	public static function get_current_date() {
		$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
		
		$date = new DateTime( $current_date );
		
		return $date;
	}
	
	/**
	 * Get the interval between two dates
	 * 
	 * @param DateTime $post_date the DateTime object of a post date
	 * @param DateTime $current_date the DateTime object of the current date
	 * @param string $duration_frame days, months or years for the interval
	 * 
	 * @return int interval between the two dates
	 */
	public static function get_date_interval( $post_date, $current_date, $duration_frame ) {
		$interval = $current_date->diff( $post_date );
		$diff = 0;

		switch( $duration_frame ) {
			case 'days':
				// We use the days field, otherwise days reset after 31
				$diff = $interval->days;
				break;
			case 'months':
				// A bit tricky - months reset after a year and we need to compare years
				$years = (int) $interval->format('%y');
				$diff = 12 * $years + (int) $interval->format('%m');
				break;
			case 'years':
				$diff = $interval->format('%y');
				break;
		}
		
		return (int) $diff;
	}
}