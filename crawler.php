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
					$validators = $data->groups->validators->data;
				}
				else {
					$validators = array( array( 'No validators' ) );
				}

				set_transient( 'wp-i18n-validators-' . $locale, $validators );
			}
		}

		return $validators;
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