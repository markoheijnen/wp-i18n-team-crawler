<?php

class WP_I18n_Team_Api {
	private static $counter = 1;

	public static function get_sites() {
		include_once dirname( __DIR__ ) . '/locales/locales.php';

		$locales = GP_Locales::locales();
		$locales = array_filter( $locales, array( __CLASS__, 'filter_locale_for_wp' ) );
		usort( $locales, array( __CLASS__, 'sort_locales' ) );

		return $locales;
	}

	public static function get_locale( $slug ) {
		include_once dirname( __DIR__ ) . '/locales/locales.php';

		$post = get_post();

		if ( $post && 'locale' == $post->post_type && $slug == $post->post_nama ) {
			$locale_object = $post;
		}
		else {
			$locale_object = get_page_by_path( $slug, OBJECT, 'locale' );
		}

		if ( ! $locale_object ) {
			$locale = GP_Locales::by_slug( $slug );

			if ( ! $locale ) {
				return false;
			}

			$args = array(
				'post_type'   => 'locale',
				'post_name'   => $slug,
				'post_title'  => $locale->english_name,
				'post_status' => 'publish',
			);
			$post_id = wp_insert_post( $args );

			update_post_meta( $post_id, 'locale', $locale->wp_locale );
			self::update_locale_info( $post_id, $slug );

			$locale_object = get_post( $post_id );
		}
		else {
			$date    = mysql2date( 'U', $locale_object->post_modified ) + DAY_IN_SECONDS;
			$current = current_time( 'timestamp' );

			// Only rune the first 15 calls.
			if ( self::$counter <= 15 && $date < $current ) {
				self::update_locale_info( $locale_object->ID, $slug );

				wp_update_post( 
					array(
						'ID'                => $locale_object->ID,
						'post_modified'     => current_time( 'mysql' ),
						'post_modified_gmt' => current_time( 'mysql', 1 )
					)
				);

				self::$counter++;
			}
		}

		return $locale_object;
	}


	/**
	 * Retrieve language packs
	 *
	 * @param string $wp_version The WordPress version.
	 *
	 * @return array|bool A list of all locales with language packs, or false on error.
	*/
	public static function get_language_packs() {
		$wp_version = self::current_wordpress_version();

		// We can't request data before this.
		if ( version_compare( $wp_version, '4.0', '<' ) ) {
			return false;
		}

		$results = get_transient('language_packs');
		if ( $results ) {
			return $results;
		}

		$response = wp_remote_get( 'http://api.wordpress.org/translations/core/1.0/?version=' . $wp_version );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$results = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $results ) ) {
			return false;
		}

		set_transient( 'language_packs', $results, DAY_IN_SECONDS );

		return $results;
	}


	private static function update_locale_info( $post_id, $slug ) {
		$data = WP_I18n_Team_Crawler::get_locale( $slug );


		if ( $data ) {
			update_post_meta( $post_id, 'url', $data['url'] );
			update_post_meta( $post_id, 'version', $data['version'] );

			update_post_meta( $post_id, '_validators', $data['validators'] );
			update_post_meta( $post_id, '_translators', $data['translators'] );
		}

		return (bool) $data;
	}


	public static function get_validators( $slug ) {
		$locale = self::get_locale( $slug );

		$validators = get_post_meta( $locale->ID, '_validators', true );

		if ( $validators ) {
			return $validators;
		}

		return array();
	}

	public static function get_translators( $slug ) {
		$locale = self::get_locale( $slug );

		$translators = get_post_meta( $locale->ID, '_translators', true );

		if ( $translators ) {
			return $translators;
		}

		return array();
	}


	/* HELPER FUNCTIONS*/

	public static function current_wordpress_version() {
		global $wp_version;

		return $wp_version;
	}

	public static function filter_locale_for_wp( $element ) {
		if ( ! isset( $element->wp_locale ) ) {
			return false;
		}

		return true;
	}

	public static function sort_locales( $a, $b ) {
		return strcmp( $a->english_name, $b->english_name );
	}

}
