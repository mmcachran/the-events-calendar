<?php
/**
 * List Widget
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

/**
 * Class for the List Widget.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Widget_List extends Widget_Abstract {

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $slug = 'tribe_events_list_widget';

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $view_slug = 'widget-list';

	/**
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $default_arguments = [
		// View options.
		'view'                 => null,
		'should_manage_url'    => false,

		// Event widget options.
		'id'                   => null,
		'alias-slugs'          => null,
		'title'                => '',
		'limit'                => '5',
		'no_upcoming_events'   => false,
		'featured_events_only' => false,
		'jsonld_enable'        => true,
		'admin_fields'         => [
			'title'                => [],
			'limit'                => [],
			'no_upcoming_events'   => [],
			'featured_events_only' => [],
			'tribe_is_list_widget' => [],
			'jsonld_enable'        => [],
		],

		// WP_Widget properties.
		'id_base'              => 'tribe-events-list-widget',
		'name'                 => null,
		'widget_options'       => [
			'classname'   => 'tribe-events-list-widget',
			'description' => null,
		],
		'control_options'      => [
			'id_base' => 'tribe-events-list-widget',
		],
	];

	/**
	 * @todo update in TEC-3612 & TEC-3613
	 *
	 * {@inheritDoc}
	 *
	 * @var array<string,mixed>
	 */
	protected $validate_arguments_map = [
		'should_manage_url'    => 'tribe_is_truthy',
		'no_upcoming_events'   => 'tribe_is_truthy',
		'featured_events_only' => 'tribe_is_truthy',
		'jsonld_enable'        => 'tribe_is_truthy',
	];

	/**
	 * {@inheritDoc}
	 */
	public function get_arguments( $instance = [] ) {
		$arguments = $this->arguments;

		$arguments['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );
		// @todo update name once this widget is ready to replace the existing list widget.
		$arguments['name']                          = esc_html__( 'Events List V2', 'the-events-calendar' );
		$arguments['widget_options']['description'] = esc_html__( 'A widget that displays upcoming events.', 'the-events-calendar' );

		// Setup Admin Fields.
		$arguments['title']        = __( 'Upcoming Events', 'the-events-calendar' );
		$arguments['admin_fields'] = [
			'title'                => [
				'label' => __( 'Title:', 'the-events-calendar' ),
				'type'  => 'text',
			],
			'limit'                => [
				'label' => __( 'Show:', 'the-events-calendar' ),
				'type'  => 'dropdown',
				'options' => $this->get_limit_options(),
			],
			'no_upcoming_events'   => [
				'label' => __( 'Show widget only if there are upcoming events', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],
			'featured_events_only' => [
				'label' => _x( 'Limit to featured events only', 'events list widget setting', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],
			'jsonld_enable'        => [
				'label' => __( 'Generate JSON-LD data', 'the-events-calendar' ),
				'type'  => 'checkbox',
			],

		];

		// Add the Widget to the arguments to pass to the admin template.
		$arguments['widget_obj'] = $this;

		$arguments = wp_parse_args( $arguments, $this->get_default_arguments() );

		return $this->filter_arguments( $arguments );
	}

	/**
	 *
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_limit_options() {
		/**
		 * Filter the max limit of events to display in the List Widget.
		 *
		 * @since TBD
		 *
		 * @param int The max limit of events to display in the List Widget, default 10.
		 */
		$events_limit = apply_filters( "tribe_events_widget_list_events_max_limit", 10 );

		$options = [];

		for ( $i = 1; $i <= $events_limit; $i ++ ) {
			$options[] = [
				'text'  => $i,
				'value' => $i,
			];
		}

		return $options;
	}
}
