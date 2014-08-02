<?php

class WP_I18n_Team_Locale {

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		add_filter( 'the_content', array( $this, 'the_content' ) );
	}



	

	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Locales', 'post type general name', 'wp-i18n-team-crawler' ),
			'singular_name'      => _x( 'Locale', 'post type singular name', 'wp-i18n-team-crawler' ),
			'menu_name'          => _x( 'Locales', 'admin menu', 'wp-i18n-team-crawler' ),
			'name_admin_bar'     => _x( 'Locale', 'add new on admin bar', 'wp-i18n-team-crawler' ),
			'add_new'            => _x( 'Add New', 'locale', 'wp-i18n-team-crawler' ),
			'add_new_item'       => __( 'Add New locale', 'wp-i18n-team-crawler' ),
			'new_item'           => __( 'New locale', 'wp-i18n-team-crawler' ),
			'edit_item'          => __( 'Edit locale', 'wp-i18n-team-crawler' ),
			'view_item'          => __( 'View locale', 'wp-i18n-team-crawler' ),
			'all_items'          => __( 'All locales', 'wp-i18n-team-crawler' ),
			'search_items'       => __( 'Search locales', 'wp-i18n-team-crawler' ),
			'parent_item_colon'  => __( 'Parent locales:', 'wp-i18n-team-crawler' ),
			'not_found'          => __( 'No locales found.', 'wp-i18n-team-crawler' ),
			'not_found_in_trash' => __( 'No locales found in Trash.', 'wp-i18n-team-crawler' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'internationalization' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor' )
		);

		register_post_type( 'locale', $args );
	}

	/**
	 * Locale update messages.
	 *
	 * See /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array Amended post update messages with new CPT update messages.
	 */
	function post_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['locale'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Locale updated.', 'wp-i18n-team-crawler' ),
			2  => __( 'Custom field updated.', 'wp-i18n-team-crawler' ),
			3  => __( 'Custom field deleted.', 'wp-i18n-team-crawler' ),
			4  => __( 'Locale updated.', 'wp-i18n-team-crawler' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Locale restored to revision from %s', 'wp-i18n-team-crawler' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Locale published.', 'wp-i18n-team-crawler' ),
			7  => __( 'Locale saved.', 'wp-i18n-team-crawler' ),
			8  => __( 'Locale submitted.', 'wp-i18n-team-crawler' ),
			9  => sprintf(
				__( 'Locale scheduled for: <strong>%1$s</strong>.', 'wp-i18n-team-crawler' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'wp-i18n-team-crawler' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Locale draft updated.', 'wp-i18n-team-crawler' )
		);

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View locale', 'wp-i18n-team-crawler' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview locale', 'wp-i18n-team-crawler' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}



	public function the_content( $content ) {
		$post = get_post();

		if ( 'locale' == $post->post_type ) {
			$validators = WP_I18n_Team_Api::get_validators( $post->post_name );
			$translators = WP_I18n_Team_Api::get_translators( $post->post_name );

			$content .= "<h2>Validators</h2>";
			if ( $validators ) {
				$content .= '<ul>';
				foreach( $validators as $validator ) {
					$content .= '<li>';
					$content .= $validator[0];
					$content .= '</li>';
				}
				$content .= '</ul>';
			}
			else {
				$content .= '<p>' . __( 'No validators for the language.', 'wp-i18n-team-crawler' ) . '</p>';
			}

			if ( $translators ) {
				$content .= "<h2>Translators</h2>";
				$content .= '<ul>';
				foreach( $translators as $translator ) {
					$content .= '<li>';
					$content .= $translator;
					$content .= '</li>';
				}
				$content .= '</ul>';
			}
		}

		return $content;
	}

}
