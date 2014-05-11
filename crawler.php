<?php

class WP_I18n_Team_Crawler {

	public static function get_sites() {
		include 'locales/locales.php';

		$locales = GP_Locales::locales();
		$locales = array_filter( $locales, array( __CLASS__, 'filter_locale_for_wp' ) );
		usort( $locales, array( __CLASS__, 'sort_locales' ) );

		return $locales;
	}

	public function get_locale( $slug ) {
		$locale = GP_Locales::by_slug( $slug );
		$url    = 'http://api.wordpress.org/core/credits/1.1/?version=' . self::current_wordpress_version() . '&locale=' . $locale->wp_locale;

		if ( false === ( $locale_data = get_transient( 'locale-' . $slug ) ) ) {
			$response = wp_remote_get( $url );
			$body     = wp_remote_retrieve_body( $response );

			if( $body ) {
				$data        = json_decode( $body );
				$locale_data = array();

				if( isset( $data->groups->validators ) ) {
					$locale_data['validators'] = $data->groups->validators->data;
				}
				else {
					$locale_data['validators'] = array();
				}

				if( isset( $data->groups->translators ) ) {
					$locale_data['translators'] = $data->groups->translators->data;
				}
				else {
					$locale_data['translators'] = array();
				}


				$locale_data['url']     = 'http://' . $slug . '.wordpress.org';
				$locale_data['version'] = '';

				$request = wp_remote_get( $locale_data['url'], array( 'redirection' => 0 ) );
				$code    = wp_remote_retrieve_response_code( $request );

				if ( 200 === $code ) {
					$body = wp_remote_retrieve_body( $request );
					$body = str_replace( '&nbsp;', ' ', $body );

					$dom = new DOMDocument();
					$dom->loadHTML( $body );
					$finder = new DomXPath( $dom );
					
					//$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' download-button ')]");
					$nodes = $finder->query("//a[@class='download-button']/strong");

					if ( $nodes->length > 0 ) {
						$parts = explode(' ', htmlspecialchars_decode( $nodes->item(0)->nodeValue ) );

						foreach ( $parts as $part ) {
							if ( preg_match( '/^\d+(\.\d+)*$/', $part ) ) {
								$locale_data['version'] = $part;
								break;
							}
						}
					}
				}
				else {
					$locale_data['url'] = '';
				}

				set_transient( 'locale-' . $slug, $locale_data, DAY_IN_SECONDS * 2 );
			}
		}

		return $locale_data;
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
