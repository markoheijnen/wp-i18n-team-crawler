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
		$locale_object = get_page_by_path( $slug, OBJECT, 'locale' );

		if ( ! $locale_object ) {

			$locale = GP_Locales::by_slug( $slug );

			$args = array(
				'post_type'   => 'locale',
				'post_name'   => $slug,
				'post_title'  => $locale->english_name,
				'post_status' => 'publish',
			);
			$post_id = wp_insert_post( $args );

			self::update_locale_info( $post_id, $slug );

			$locale_object = get_post( $post_id );
		}
		else {
			$date    = mysql2date( 'U', $locale_object->post_modified - DAY_IN_SECONDS );
			$current = current_time( 'timestamp' );

			// Only rune the first 20 calls.
			if ( self::$counter <= 20 && $date > $current ) {
				$locale_data = WP_I18n_Team_Crawler::get_locale( $slug );
			}
		}

		return $locale_object;
	}

	private static function update_locale_info( $post_id, $slug ) {
		$data = WP_I18n_Team_Crawler::get_locale( $slug );

		update_post_meta( $post_id, 'url', $data['url'] );
		update_post_meta( $post_id, 'version', $data['version'] );


		update_post_meta( $post_id, '_validators', $data['validators'] );
		update_post_meta( $post_id, '_translators', $data['translators'] );
	}


	public static function get_validators( $slug ) {
		$locale = self::get_locale( $slug );

		if ( $locale && isset( $locale['validators'] ) ) {
			return $locale['validators'];
		}

		return array();
	}

	public static function get_translators( $locale ) {
		$locale = self::get_locale( $slug );

		if ( $locale && isset( $locale['translators'] ) ) {
			return $locale['translators'];
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
