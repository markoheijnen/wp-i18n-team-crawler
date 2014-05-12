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

		$html  = '<table>';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . __( 'Locale Name', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'Native Name', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'WordPress Locale Code', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'Locale Code', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '<th>' . __( 'Version', 'wp-i18n-team-crawler' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		foreach ( $sites as $site ) {
			$locale  = WP_I18n_Team_Crawler::get_locale( $site->slug );

			$html .= '<tr>';
			$html .= '<td>' . $site->english_name . '</td>';
			$html .= '<td>' . $site->native_name . '</td>';
			$html .= '<td>' . $site->wp_locale . '</td>';

			if ( $locale['url'] ) {
				$html .= '<td><a href="' . $locale['url'] . '">' . $site->slug . '</a></td>';

				if ( $locale['version'] ) {
					$html .= '<td>' . $locale['version'] . '</td>';
				}
				else {
					$html .= '<td>' . __( 'None', 'wp-i18n-team-crawler' ) . '</td>';
				}
			}
			else {
				$html .= '<td>' . $site->slug . '</td>';
				$html .= '<td>' . __( 'No site', 'wp-i18n-team-crawler' ) . '</td>';
			}

			$html .= '</tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		$html = '<div class="translators-info">' . $html . '</div>';

		wp_enqueue_style( 'wp-i18n-team', plugins_url( 'css/front.css', __FILE__ ), array(), '0.5' );

		return $html;
	}
}

new WP_I18n_Teams();