<?php

class WP_I18n_Team_Crawler {

	public static function get_sites() {
		include 'lib/locales.php';

		$locales = GP_Locales::locales();
		$locales = array_filter( $locales, array( __CLASS__, 'filter_locale_for_wp' ) );
		usort( $locales, array( __CLASS__, 'sort_locales' ) );

		return $locales;
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