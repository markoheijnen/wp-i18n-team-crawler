<?php
/*
Plugin Name: WP I18N Teams
Plugin URI:  
Description: Scans through a few APIs to generate a list of all languages/members
Version:     0.5
License:     GPLv2 or later
Author:      Marko Heijnen
Author URI:  http://www.markoheijnen.com
Text Domain: wp-i18n-team-crawler
Domain Path: /languages
*/

include 'crawler.php';

class WP_I18n_Teams {

	public function __construct() {
		add_shortcode( 'wp-i18n-team', array( $this, 'all_information' ) );
	}

	public function all_information( $args ) {
		$sites = WP_I18n_Team_Crawler::get_sites();

		$html  = '<p>' . __( '(white: none; green: okay; yellow: minor version behind, orange: 1 major version behind, red: many major version behind)', 'wp-i18n-team-crawler' ) . '</p>';
		$html .= '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . __( 'Locale Name', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'Native Name', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'Locale Code', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'WordPress Locale', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'Version', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		foreach ( $sites as $site ) {
			$locale = WP_I18n_Team_Crawler::get_locale( $site->slug );
			$class  = $locale['version'] ? $this->get_version_class( $locale['version'] ) : '';

			if ( $locale['url'] ) {
				$locale_code = '<a href="' . $locale['url'] . '">' . $site->slug . '</a>';

				if ( $locale['version'] ) {
					$version = $locale['version'];
				}
				else {
					$version = __( 'None', 'wp-i18n-team-crawler' );
				}
			}
			else {
				$locale_code  = $site->slug;
				$version = __( 'No site', 'wp-i18n-team-crawler' );
			}

			$html .= '<tr class="' . $class . '">';
			$html .= '<td>' . $site->english_name . '</td>';
			$html .= '<td>' . $site->native_name . '</td>';
			$html .= '<td>' . $locale_code . '</td>';
			$html .= '<td>' . $site->wp_locale . '</td>';
			$html .= '<td>' . $version . '</td>';


			$html .= '</tr>';
		}

		$html .= '</tbody>';

		$html .= '</table>';

		$html = '<div class="translators-info">' . $html . '</div>';

		wp_enqueue_style( 'wp-i18n-team', plugins_url( 'css/front.css', __FILE__ ), array(), '0.6' );

		return $html;
	}

	private function get_version_class( $version ) {
		$class = 'version';

		$wp_version = WP_I18n_Team_Crawler::current_wordpress_version();
		$one_lower  = $wp_version - 0.1;

		if ( $version == $wp_version ) {
			$class .= ' latest';
		}
		else if ( substr( $version, 0, 3 ) == substr( $wp_version, 0, 3 ) ) {
			$class .= ' minor-behind';
		}
		else if ( substr( $version, 0, 3 ) == substr( $one_lower, 0, 3 ) ) {
			$class .= ' major-behind-one';
		}
		else {
			$class .= ' major-behind-many';
		}

		return $class;
	}

}

new WP_I18n_Teams();