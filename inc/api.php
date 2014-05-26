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
		if ( self::$counter <= 20 ) {
			echo self::$counter . '-';
			self::$counter++;
		}

		if ( false === ( $locale_data = get_transient( 'locale-' . $slug ) ) ) {
			$locale_data = WP_I18n_Team_Crawler::get_locale( $slug );
		}

		return $locale_data;
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
