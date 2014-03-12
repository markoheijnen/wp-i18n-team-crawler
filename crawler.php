<?php

class WP_I18n_Team_Crawler {

	public static function get_sites() {
		include 'locales/locales.php';

		$locales = GP_Locales::locales();
		$locales = array_filter( $locales, array( __CLASS__, 'filter_locale_for_wp' ) );
		usort( $locales, array( __CLASS__, 'sort_locales' ) );

		return $locales;
	}

	public static function get_validators( $locale ) {
		$url = 'http://api.wordpress.org/core/credits/1.1/?version=' . self::current_wordpress_version() . '&locale=' . $locale;

		if ( false === ( $validators = get_transient( 'wp-i18n-validators-' . $locale ) ) ) {
			$response = wp_remote_get( $url );
			$body     = wp_remote_retrieve_body( $response );

			if( $body ) {
				$data = json_decode( $body );

				if( isset( $data->groups->validators ) ) {
					$validators  = $data->groups->validators->data;
				}
				else {
					$validators  = array();
				}

				if( isset( $data->groups->translators ) ) {
					$translators = $data->groups->translators->data;
				}
				else {
					$translators = array();
				}

				set_transient( 'wp-i18n-validators-' . $locale, $validators, DAY_IN_SECONDS * 2 );
				set_transient( 'wp-i18n-translators-' . $locale, $translators, DAY_IN_SECONDS * 2 );
			}
		}

		return $validators;
	}

	public static function get_translators( $locale ) {
		if ( false === ( $translators = get_transient( 'wp-i18n-translators-' . $locale ) ) ) {
			self::get_validators( $locale );

			$translators = get_transient( 'wp-i18n-translators-' . $locale );
		}

		return $translators;
	}


	/* HELPER FUNCTIONS*/

	private static function current_wordpress_version() {
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

new WP_I18n_Team_Crawler();