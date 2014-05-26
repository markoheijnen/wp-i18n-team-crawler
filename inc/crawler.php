<?php

class WP_I18n_Team_Crawler {

	public static function get_locale( $slug ) {
		$locale_data = array();
		$locale      = GP_Locales::by_slug( $slug );
		$url         = 'http://api.wordpress.org/core/credits/1.1/?version=' . WP_I18n_Team_Api::current_wordpress_version() . '&locale=' . $locale->wp_locale;

		$response = wp_remote_get( $url );
		$body     = wp_remote_retrieve_body( $response );

		if( $body ) {
			$data = json_decode( $body );

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

			$locale_data['url']     = self::get_locale_url( $slug );
			$locale_data['version'] = '';

			$request = wp_remote_get( $locale_data['url'], array( 'redirection' => 0 ) );
			$code    = wp_remote_retrieve_response_code( $request );

			if ( 200 === $code ) {
				$body = wp_remote_retrieve_body( $request );

				$locale_data['version'] = self::get_version_from_html( $body );
			}
			else {
				$locale_data['url'] = '';
			}

			set_transient( 'locale-' . $slug, $locale_data, DAY_IN_SECONDS * 2 );
		}

		return $locale_data;
	}

	public static function get_locale_url( $slug ) {
		switch ( $slug ) {
			case 'es-cl':
				$url = 'http://cl.wordpress.org';
			break;
			case 'es-pe':
				$url = 'http://pe.wordpress.org';
			break;
			case 'es-ve':
				$url = 'http://ve.wordpress.org';
			break;
			case 'pt-br':
				$url = 'http://br.wordpress.org';
			break;
			case 'sa-in':
				$url = 'http://sa.wordpress.org';
			break;
			case 'zh-cn':
				$url = 'http://cn.wordpress.org';
			break;
			case 'zh-tw':
				$url = 'http://tw.wordpress.org';
			break;
			default:
				$url = 'http://' . $slug . '.wordpress.org';
		}

		return $url;
	}

	private static function get_version_from_html( $html ) {
		$html = str_replace( '&nbsp;', ' ', $html );

		$dom = new DOMDocument();
		@$dom->loadHTML( $html ); // Error supressing due to the fact that special characters haven't been converted to HTML.
		$finder = new DomXPath( $dom );
		
		$nodes = $finder->query("//a[@class='download-button']/strong");

		if ( $nodes->length > 0 ) {
			$parts = explode(' ', htmlspecialchars_decode( $nodes->item(0)->nodeValue ) );

			foreach ( $parts as $part ) {
				if ( preg_match( '/^\d+(\.\d+)*$/', $part ) || strpos( $part, 'alpha' ) !== false || strpos( $part, 'beta' ) !== false ) {
					return $part;
				}
			}
		}

		return '';
	}

}
