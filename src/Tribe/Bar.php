<?php

/**
 *
 */
class Tribe__Events__Bar {

	// Each row should be an associative array with three fields: name, caption and html (html is the markup of the field)
	private $filters = array();

	// Each row should be an associative array with three fields: displaying, anchor and url.
	// Displaying is the value of Tribe__Events__Main->displaying
	private $views = array();

	/**
	 * Hooking the required Filters and Actions for this Class
	 *
	 * @since  4.6.21
	 *
	 * @return void
	 */
	public function hook() {
		add_filter( 'wp_enqueue_scripts', array( $this, 'load_script' ), 9 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'tribe_events_bar_before_template', array( $this, 'disabled_bar_before' ) );
		add_action( 'tribe_events_bar_after_template', array( $this, 'disabled_bar_after' ) );
	}

	/**
	 * Decide if the TribeBar should be shown in a particular pageview.
	 *
	 * @filter tribe-events-bar-views to get all the registered views that the Bar will show
	 * @filter tribe-events-bar-should-show to allow themers to always hide the bar if they want.
	 *
	 * To always hide the Bar, add this to your theme's functions.php:
	 *        add_filter( 'tribe-events-bar-should-show', '__return_false' );
	 *
	 * @return bool
	 *
	 */
	public function should_show() {
		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		$disallowed_types = array(
			Tribe__Events__Organizer::POSTTYPE,
			Tribe__Events__Venue::POSTTYPE,
		);

		$in_disallowed_type = in_array( get_post_type(), $disallowed_types );
		$is_tribe_view   = ( ! empty( $wp_query->tribe_is_event_query ) && ! is_single() && ! $in_disallowed_type );

		return apply_filters( 'tribe-events-bar-should-show', $is_tribe_view );
	}

	/**
	 * Adds a body class of tribe-bar-is-disabled when the Tribe Bar is disabled.
	 *
	 * @return array The new body class array
	 */
	public function body_class( $classes ) {
		if ( tribe_get_option( 'tribeDisableTribeBar', false ) == true ) {
			$classes[] = 'tribe-bar-is-disabled';
		}

		return $classes;
	}

	/**
	 * Returns the opening tag of the disabled bar wrapper
	 *
	 * @return string
	 */
	public function disabled_bar_before( $before ) {
		if ( tribe_get_option( 'tribeDisableTribeBar', false ) == true ) {
			$before = '<div class="tribe-bar-disabled">';
			echo $before;
		}
	}

	/**
	 * Returns the closing tag of the disabled bar wrapper
	 *
	 * @return array The new body class array
	 */
	public function disabled_bar_after( $after ) {
		if ( tribe_get_option( 'tribeDisableTribeBar', false ) == true ) {
			$after = '</div>';
			echo $after;
		}
	}

	/**
	 * Load the CSSs and JSs only if the Bar will be shown
	 */
	public function load_script() {
		if ( ! $this->should_show() ) {
			return false;
		}

		tribe_asset_enqueue( 'tribe-events-bar' );

		do_action( 'tribe-events-bar-enqueue-scripts' );
	}

	/**
	 * @deprecated 4.6.21
	 *
	 * @return Tribe__Events__Bar
	 */
	public static function instance() {
		return tribe( 'tec.bar' );
	}
}
