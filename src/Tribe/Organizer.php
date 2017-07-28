<?php

class Tribe__Events__Organizer extends Tribe__Events__Linked_Posts__Base {
	const POSTTYPE = 'tribe_organizer';

	/**
	 * @var string
	 */
	protected $meta_prefix = '_Organizer';

	/**
	 * @var string The meta key relating a post of the type managed by the class to events.
	 */
	protected $event_meta_key = '_EventOrganizerID';

	/**
	 * Args for organizer post type
	 * @var array
	 */
	public $post_type_args = array(
		'public'              => false,
		'rewrite'             => array( 'slug' => 'organizer', 'with_front' => false ),
		'show_ui'             => true,
		'show_in_menu'        => 0,
		'supports'            => array( 'title', 'editor' ),
		'capability_type'     => array( 'tribe_organizer', 'tribe_organizers' ),
		'map_meta_cap'        => true,
		'exclude_from_search' => true,
	);

	/**
	 * @var array
	 */
	public static $valid_keys = array(
		'Organizer',
		'Phone',
		'Email',
		'Website',
	);

	/**
	 * @var array A list of the valid meta keys for this linked post.
	 */
	public static $meta_keys = array(
		'Phone',
		'Email',
		'Website',
	);


	public $singular_organizer_label;
	public $plural_organizer_label;

	protected static $instance;

	/**
	 * Returns a singleton of this class
	 *
	 * @return Tribe__Events__Organizer
	 */
	public static function instance() {
		return tribe( 'tec.linked-posts.organizer' );
	}

	/**
	 * Tribe__Events__Organizer constructor.
	 */
	public function __construct() {
		$rewrite = Tribe__Events__Rewrite::instance();

		$this->post_type                          = self::POSTTYPE;
		$this->singular_organizer_label           = $this->get_organizer_label_singular();
		$this->singular_organizer_label_lowercase = $this->get_organizer_label_singular_lowercase();
		$this->plural_organizer_label             = $this->get_organizer_label_plural();
		$this->plural_organizer_label_lowercase   = $this->get_organizer_label_plural_lowercase();

		$this->post_type_args['rewrite']['slug']   = $rewrite->prepare_slug( $this->singular_organizer_label, self::POSTTYPE, false );
		$this->post_type_args['show_in_nav_menus'] = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
		$this->post_type_args['public']            = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;

		/**
		 * Provides an opportunity to modify the labels used for the organizer post type.
		 *
		 * @param array
		 */
		$this->post_type_args['labels'] = apply_filters( 'tribe_events_register_organizer_post_type_labels', array(
			'name'                    => $this->plural_organizer_label,
			'singular_name'           => $this->singular_organizer_label,
			'singular_name_lowercase' => $this->singular_organizer_label_lowercase,
			'plural_name_lowercase'   => $this->plural_organizer_label_lowercase,
			'add_new'                 => esc_html__( 'Add New', 'the-events-calendar' ),
			'add_new_item'            => sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_organizer_label ),
			'edit_item'               => sprintf( esc_html__( 'Edit %s', 'the-events-calendar' ), $this->singular_organizer_label ),
			'new_item'                => sprintf( esc_html__( 'New %s', 'the-events-calendar' ), $this->singular_organizer_label ),
			'view_item'               => sprintf( esc_html__( 'View %s', 'the-events-calendar' ), $this->singular_organizer_label ),
			'search_items'            => sprintf( esc_html__( 'Search %s', 'the-events-calendar' ), $this->plural_organizer_label ),
			'not_found'               => sprintf( esc_html__( 'No %s found', 'the-events-calendar' ), strtolower( $this->plural_organizer_label ) ),
			'not_found_in_trash'      => sprintf( esc_html__( 'No %s found in Trash', 'the-events-calendar' ), strtolower( $this->plural_organizer_label ) ),
		) );

		$this->register_post_type();

		add_filter( 'tribe_events_linked_post_type_args', array( $this, 'filter_linked_post_type_args' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_id_field_index', array( $this, 'linked_post_id_field_index' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_name_field_index', array( $this, 'linked_post_name_field_index' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_type_container', array( $this, 'linked_post_type_container' ), 10, 2 );
		add_filter( 'tribe_events_linked_post_create_' . self::POSTTYPE, array( $this, 'save' ), 10, 5 );
		add_filter( 'tribe_events_linked_post_default', array( $this, 'linked_post_default' ), 10, 2 );
		add_action( 'tribe_events_linked_post_new_form', array( $this, 'linked_post_new_form' ) );
	}

	/**
	 * Registers the post type
	 */
	public function register_post_type() {
		/**
		 * Filters the post type arguments for the tribe_organizer post type
		 *
		 * @param array $post_type_args Post type arguments
		 */
		$post_type_args = apply_filters( 'tribe_events_register_organizer_type_args', $this->post_type_args );

		register_post_type( self::POSTTYPE, $post_type_args );
	}

	/**
	 * Filters the post type args for the venue post type
	 *
	 * @since 4.2
	 *
	 * @param array $args Array of linked post type arguments
	 * @param string $post_type Linked post type
	 *
	 * @return array
	 */
	public function filter_linked_post_type_args( $args, $post_type ) {
		if ( self::POSTTYPE !== $post_type ) {
			return $args;
		}

		$args['allow_creation'] = true;

		return $args;
	}

	/**
	 * Allow users to specify their own singular label for Organizers
	 * @return string
	 */
	public function get_organizer_label_singular() {
		/**
		 * Filters the singular label of Organizer
		 *
		 * @param string $label Singular organizer label
		 */
		return apply_filters( 'tribe_organizer_label_singular', esc_html__( 'Organizer', 'the-events-calendar' ) );
	}

	/**
	 * Allow users to specify their own plural label for Organizers
	 *
	 * @return string
	 */
	public function get_organizer_label_plural() {
		/**
		 * Filters the plural label of Organizer
		 *
		 * @param string $label Plural organizer label
		 */
		return apply_filters( 'tribe_organizer_label_plural', esc_html__( 'Organizers', 'the-events-calendar' ) );
	}

	/**
	 * Allow users to specify their own lowercase singular label for Organizers
	 * @return string
	 */
	public function get_organizer_label_singular_lowercase() {
		/**
		 * Filters the lowercase singular label of Organizer
		 *
		 * @param string $label Singular lowercase organizer label
		 */
		return apply_filters( 'tribe_organizer_label_singular_lowercase', esc_html__( 'organizer', 'the-events-calendar' ) );
	}

	/**
	 * Allow users to specify their own plural label for Organizers
	 *
	 * @return string
	 */
	public function get_organizer_label_plural_lowercase() {
		/**
		 * Filters the lowercase plural label of Organizer
		 *
		 * @param string $label Plural lowercase organizer label
		 */
		return apply_filters( 'tribe_organizer_label_plural_lowercase', esc_html__( 'organizers', 'the-events-calendar' ) );
	}

	/**
	 * Filters the linked post id field
	 *
	 * @since 4.2
	 *
	 * @param string $id_field Field name of the field that will hold the ID
	 * @param string $post_type Post type of linked post
	 */
	public function linked_post_id_field_index( $id_field, $post_type ) {
		if ( self::POSTTYPE === $post_type ) {
			return 'OrganizerID';
		}

		return $id_field;
	}

	/**
	 * Filters the linked post name field
	 *
	 * @since 4.2
	 *
	 * @param string $name_field Field name of the field that will hold the post name
	 * @param string $post_type Post type of linked post
	 */
	public function linked_post_name_field_index( $name_field, $post_type ) {
		if ( self::POSTTYPE === $post_type ) {
			return 'Organizer';
		}

		return $name_field;
	}

	/**
	 * Filters the index that contains the linked post type data during form submission
	 *
	 * @since 4.2
	 *
	 * @param string $container Container index that holds submitted data
	 * @param string $post_type Post type of linked post
	 */
	public function linked_post_type_container( $container, $post_type ) {
		if ( self::POSTTYPE === $post_type ) {
			return 'organizer';
		}

		return $container;
	}

	/**
	 * Check to see if any organizer data set
	 *
	 * @param array $data the organizer data.
	 *
	 * @return bool If there is ANY organizer data set, return true.
	 */
	public function has_organizer_data( $data ) {
		foreach ( self::$valid_keys as $key ) {
			if ( isset( $data[ $key ] ) && $data[ $key ] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Saves the event organizer information passed via an event
	 *
	 * @param int|null $id ID of event organizer
	 * @param array $data The organizer data
	 * @param string $post_type The post type
	 * @param string $post_status The intended post status
	 *
	 * @return mixed
	 */
	public function save( $id, $data, $post_type, $post_status ) {
		if ( isset( $data['OrganizerID'] ) && intval( $data['OrganizerID'] ) > 0 ) {
			if ( count( $data ) == 1 ) {
				// Only an ID was passed and we should do nothing.
				return $data['OrganizerID'];
			}

			$this->update( $data['OrganizerID'], $data );

			return $data['OrganizerID'];
		}

		return $this->create( $data, $post_status );
	}

	/**
	 * Saves organizer meta
	 *
	 * @param int   $organizerId The organizer ID.
	 * @param array $data        The organizer data.
	 *
	 */
	public function save_meta( $organizerId, $data ) {
		if ( isset( $data['FeaturedImage'] ) && ! empty( $data['FeaturedImage'] ) ) {
			update_post_meta( $organizerId, '_thumbnail_id', $data['FeaturedImage'] );
			unset( $data['FeaturedImage'] );
		}

		// the organizer name is saved in the the post_title
		unset( $data['Organizer'] );

		foreach ( $data as $key => $var ) {
			update_post_meta( $organizerId, '_Organizer' . $key, sanitize_text_field( $var ) );
		}
	}

	/**
	 * Creates a new organizer
	 *
	 * @param array  $data        The organizer data.
	 * @param string $post_status the intended post status.
	 * @param bool $avoid_duplicates Whether a check to avoid the insertion of a duplicate organizer
	 *                               should be made (`true`) or not (`false`).
	 *
	 * @return mixed
	 */
	public function create( $data, $post_status = 'publish', $avoid_duplicates = false ) {
		/**
		 * Filters the ID of the generated organizer before the class creates it.
		 *
		 * If a non `null` value is returned that will be returned and the organizer creation process will bail.
		 *
		 * @param mixed $check Whether the organizer insertion process should procede or not.
		 * @param array $data The data provided to create the organizer.
		 * @param string $post_status The post status that should be applied to the created organizer.
		 *
		 * @since TBD
		 */
		$check = apply_filters( 'tribe_events_tribe_organizer_create', null, $data, $post_status );

		if ( null !== $check ) {
			return $check;
		}

		if ( ( isset( $data['Organizer'] ) && $data['Organizer'] ) || $this->has_organizer_data( $data ) ) {

			$organizer_label = tribe_get_organizer_label_singular();

			$title   = isset( $data['Organizer'] ) ? $data['Organizer'] : sprintf( __( 'Unnamed %s', 'the-events-calendar' ), ucfirst( $organizer_label ) );
			$content = isset( $data['Description'] ) ? $data['Description'] : '';
			$slug    = sanitize_title( $title );

			$data = new Tribe__Data( $data, false );

			$postdata = array(
				'post_title'    => $title,
				'post_content'  => $content,
				'post_name'     => $slug,
				'post_type'     => self::POSTTYPE,
				'post_status'   => Tribe__Utils__Array::get( $data, 'post_status', $post_status ),
				'post_author'   => $data['post_author'],
				'post_date'     => $data['post_date'],
				'post_date_gmt' => $data['post_date_gmt'],
			);

			$found = false;
			if ( $avoid_duplicates ) {
				/** @var Tribe__Duplicate__Post $duplicates */
				$duplicates = tribe( 'post-duplicate' );
				$duplicates->set_post_type( Tribe__Events__Main::ORGANIZER_POST_TYPE );
				$duplicates->use_post_fields( $this->get_duplicate_post_fields() );
				$duplicates->use_custom_fields( $this->get_duplicate_custom_fields() );

				// for the purpose of finding duplicates we skip empty fields
				$candidate_data = array_filter( $postdata );
				$candidate_data = array_combine(
					array_map( array( $this, 'prefix_key' ), array_keys( $candidate_data ) ),
					array_values( $candidate_data )
				);

				$found = $duplicates->find_for( $candidate_data );
			}

			$organizer_id = false === $found
				? wp_insert_post( array_filter( $postdata ), true )
				: $found;
			if ( ! is_wp_error( $organizer_id ) ) {
				$this->save_meta( $organizer_id, $data );

				/**
				 * Fires immediately after an organizer has been created.
				 *
				 * @param int   $organizer_id The updated organizer post ID.
				 * @param array $data         The data used to update the organizer.
				 *
				 * @since TBD
				 */
				do_action( 'tribe_events_organizer_created', $organizer_id, $data->to_array() );

				return $organizer_id;
			}
		}

		// if the venue is blank, let's save the value as 0 instead
		return 0;
	}

	/**
	 * Updates an organizer
	 *
	 * @param int   $organizerId The organizer ID to update.
	 * @param array $data        The organizer data.
	 *
	 * @return int The updated organizer post ID
	 *
	 * @since TBD
	 */
	public function update( $id, $data ) {
		/**
		 * Filters the ID of the organizer before the class updates it.
		 *
		 * If a non `null` value is returned that will be returned and the organizer update process will bail.
		 *
		 * @param mixed $check        Whether the organizer update process should proceed or not.
		 * @param int   $organizer_id The post ID of the organizer that should be updated
		 * @param array $data         The data provided to update the organizer.
		 *
		 * @since TBD
		 */
		$check = apply_filters( 'tribe_events_tribe_organizer_update', null, $id, $data );

		if ( null !== $check ) {
			return $check;
		}

		$data = new Tribe__Data( $data, '' );

		unset( $data['OrganizerID'] );

		$args = array(
			'ID'            => $id,
			'post_title'    => Tribe__Utils__Array::get( $data, 'post_title', $data['Organizer'] ),
			'post_content'  => Tribe__Utils__Array::get( $data, 'post_content', $data['Description'] ),
			'post_excerpt'  => Tribe__Utils__Array::get( $data, 'post_excerpt', $data['Excerpt'] ),
			'post_author'   => $data['post_author'],
			'post_date'     => $data['post_date'],
			'post_date_gmt' => $data['post_date_gmt'],
			'post_status'   => $data['post_status'],
		);

		if ( count( $args ) > 1 ) {
			wp_update_post( $args );
		}

		$post_fields = array_merge( Tribe__Duplicate__Post::$post_table_columns, array(
			'Organizer',
			'Description',
			'Excerpt',
		) );
		$meta = array_diff_key( $data->to_array(), array_combine( $post_fields, $post_fields ) );

		$this->save_meta( $id, $meta );

		/**
		 * Fires immediately after an organizer has been updated.
		 *
		 * @param int $organizer_id The updated organizer post ID.
		 * @param array $data The data used to update the organizer.
		 */
		do_action( 'tribe_events_organizer_updated', $id, $data->to_array() );

		return $id;
	}

	/**
	 * Deletes an organizer
	 *
	 * @param int  $organizerId  The organizer ID to delete.
	 * @param bool $force_delete  Whether or not to bypass the trash when deleting the venue (see wp_delete_post's $force_delete param)
	 *
	 */
	public function delete( $id, $force_delete = false ) {
		wp_delete_post( $id, $force_delete );
	}

	/**
	 * Returns the default organizers
	 *
	 * @since 4.2
	 *
	 * @param int $default Default organizer ID
	 * @param string $post_type Post type of form being output
	 */
	public function linked_post_default( $default, $post_type ) {
		if ( self::POSTTYPE !== $post_type ) {
			return $default;
		}

		return Tribe__Events__Main::instance()->defaults()->organizer_id();
	}

	/**
	 * Outputs the Organizer form fields for creating new organizers
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type of form being output
	 */
	public function linked_post_new_form( $post_type ) {
		if ( self::POSTTYPE !== $post_type ) {
			return;
		}

		$template = Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/create-organizer-fields.php';

		/**
		 * Filters the template path of the template that holds the organizer form fields
		 *
		 * @param string $template Template path
		 *
		 * @since TBD
		 */
		include apply_filters( 'tribe_events_tribe_organizer_new_form_fields', $template );
	}

	/**
	 * Returns an array of post fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <post_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see Tribe__Duplicate__Strategy_Factory for supported strategies
	 */
	protected function get_duplicate_post_fields() {
		$fields = array(
			'post_title'   => array( 'match' => 'same' ),
			'post_content' => array( 'match' => 'same' ),
			'post_excerpt' => array( 'match' => 'same' ),
		);

		/**
		 * Filters the post fields that should be used to search for a organizer duplicate.
		 *
		 * @param array $fields An array associating the custom field meta key to the strategy definition.
		 *
		 * @see   Tribe__Duplicate__Strategy_Factory
		 *
		 * @since TBD
		 */
		return apply_filters( 'tribe_event_venue_duplicate_post_fields', $fields );
	}

	/**
	 * Returns an array of post custom fields that should be used to spot possible duplicates.
	 *
	 * @return array An array of post fields to matching strategy in the format
	 *               [ <custom_field> => [ 'match' => <strategy> ] ]
	 *
	 * @see Tribe__Duplicate__Strategy_Factory for supported strategies
	 */
	protected function get_duplicate_custom_fields() {
		$fields = array(
			'_OrganizerPhone'   => array( 'match' => 'same' ),
			'_OrganizerEmail'   => array( 'match' => 'same' ),
			'_OrganizerWebsite' => array( 'match' => 'same' ),
		);

		/**
		 * Filters the custom fields that should be used to search for a organizer duplicate.
		 *
		 * @param array $fields An array associating the custom field meta key to the strategy definition.
		 *
		 * @see   Tribe__Duplicate__Strategy_Factory
		 *
		 * @since TBD
		 */
		return apply_filters( 'tribe_event_organizer_duplicate_custom_fields', $fields );
	}
}
