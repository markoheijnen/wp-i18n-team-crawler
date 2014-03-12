<?php
/*
Plugin Name: WP I18N Teams
Plugin URI:  
Description: Scans through a few APIs to generate a list of all languages/members
Version:     0.1
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
		$html  = '';
		$sites = WP_I18n_Team_Crawler::get_sites();

		foreach ( $sites as $site ) {
			$validators  = WP_I18n_Team_Crawler::get_validators( $site->wp_locale );
			$translators = WP_I18n_Team_Crawler::get_translators( $site->wp_locale );

			$html .= '<li>';
			$html .= '<h2>' . $site->english_name . ' &ndash; ' . $site->native_name . ' ( ' . $site->wp_locale . ' )</h2>';
			$html .= '<a href="http://' . $site->slug .'.wordpress.org">View site</a>';

			$html .= '<h3>' . __( 'Validators', 'wp-i18n-team-crawler' ) . '</h3>';
			$html .= '<ul>';

			if ( $validators ) {
				foreach( $validators as $validator ) {
					$html .= '<li>';
					$html .= $validator[0];
					$html .= '</li>';
				}
			}
			else {
				$html .= '<li>' . __( 'No validators yet', 'wp-i18n-team-crawler' ) . '</li>';
			}

			$html .= '</ul>';

			if( $translators ) {
				$html .= '<h3>' . __( 'Translators', 'wp-i18n-team-crawler' ) . '</h3>';
				$html .= '<ul>';

				foreach( $translators as $translator ) {
					$html .= '<li>';
					$html .= $translator;
					$html .= '</li>';
				}

				$html .= '</ul>';
			}
		}

		$html = '<ul class="translators-info">' . $html . '</ul>';

		return $html;
	}
}

new WP_I18n_Teams();