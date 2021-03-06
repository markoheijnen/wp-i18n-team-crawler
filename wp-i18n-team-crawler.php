<?php
/*
Plugin Name: WP I18N Teams
Plugin URI:  
Description: Scans through a few APIs to generate a list of all languages/members
Version:     0.9
License:     GPLv2 or later
Author:      Marko Heijnen
Author URI:  http://www.markoheijnen.com
Text Domain: wp-i18n-team-crawler
Domain Path: /languages
*/

include 'inc/api.php';
include 'inc/crawler.php';
include 'inc/locale.php';

if ( defined('WP_CLI') && WP_CLI ) {
	include __DIR__ . '/inc/wp-cli.php';
}

class WP_I18n_Teams {
	static $version = '0.9';

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_style' ), 1 );

		add_shortcode( 'wp-i18n-team', array( $this, 'all_information' ) );

		new WP_I18n_Team_Locale;
	}


	public function register_style() {
		wp_register_style( 'wp-i18n-team', plugins_url( 'css/front.css', __FILE__ ), array(), self::$version );
	}


	public function all_information( $args ) {
		$latest_incomplete = array();
		$no_sites = $no_downloads = $latest = $minor_behind = $major_behind_one = $major_behind_many = 0;

		$sites = WP_I18n_Team_Api::get_sites();

		$table  = '<table>';
		$table .= '<thead>';
		$table .= '<tr>';
		$table .= '<th>' . __( 'Locale Name', 'wp-i18n-team-crawler' ) . '</th>';
		$table .= '<th>' . __( 'Native Name', 'wp-i18n-team-crawler' ) . '</th>';
		$table .= '<th>' . __( 'Locale Code', 'wp-i18n-team-crawler' ) . '</th>';
		$table .= '<th>' . __( 'WordPress Locale', 'wp-i18n-team-crawler' ) . '</th>';
		$table .= '<th>' . __( 'Version', 'wp-i18n-team-crawler' ) . '</th>';
		$table .= '<th>' . __( 'Core translations', 'wp-i18n-team-crawler' ) . '</th>';
		$table .= '<th></th>';
		$table .= '</tr>';
		$table .= '</thead>';
		$table .= '<tbody>';

		foreach ( $sites as $site ) {
			$locale = WP_I18n_Team_Api::get_locale( $site->slug );
			$class  = '';

		 	if ( $locale->version ) {
		 		$class = 'version';

				$wp_version = WP_I18n_Team_Api::current_wordpress_version();
				$one_lower  = $wp_version - 0.1;

				if ( $locale->version == $wp_version ) {
					$class .= ' latest';

					if ( $locale->core_percentage < 1 ) {
						$latest_incomplete[] = $site->english_name;
					}

					$latest++;
				}
				else if ( substr( $locale->version, 0, 3 ) == substr( $wp_version, 0, 3 ) ) {
					$class .= ' minor-behind';

					$minor_behind++;
				}
				else if ( substr( $locale->version, 0, 3 ) == substr( $one_lower, 0, 3 ) ) {
					$class .= ' major-behind-one';

					$major_behind_one++;
				}
				else {
					$class .= ' major-behind-many';

					$major_behind_many++;
				}
			}

			if ( $locale->url ) {
				if ( $locale->version ) {
					$version = $locale->version;
				}
				else {
					$version = __( 'None', 'wp-i18n-team-crawler' );

					$no_downloads++;
				}
			}
			else {
				$version     = __( 'No site', 'wp-i18n-team-crawler' );

				$no_sites++;
			}

			$table .= '<tr class="' . $class . '">';
			$table .= '<td>' . $site->english_name . '</td>';
			$table .= '<td>' . $site->native_name . '</td>';
			$table .= '<td>' . $site->slug . '</td>';
			$table .= '<td>' . $site->wp_locale . '</td>';
			$table .= '<td>' . $version . '</td>';
			$table .= '<td>' . $locale->core_percentage * 100 . '%</td>';
			$table .= '<td><a href="' . home_url('internationalization') . '/' . $locale->post_name . '/">' . __( 'More', 'wp-i18n-team-crawler' ) . '</a></td>';
			$table .= '</tr>';
		}

		$table .= '</tbody>';
		$table .= '</table>';


		$html  = '<p>';
		$html .= sprintf(
			__( '%s locales up-to-date. %s locales behind by one minor version. %s locales behind by one major version. %s locales behind by two or more major versions. %s locales do not yet have a package available.', 'wp-i18n-team-crawler' ),
			'<strong class="i18n-label latest">' . $latest . '</strong>',
			'<strong class="i18n-label minor-behind">' . $minor_behind . '</strong>',
			'<strong class="i18n-label major-behind-one">' . $major_behind_one . '</strong>',
			'<strong class="i18n-label major-behind-many">' . $major_behind_many . '</strong>',
			'<strong class="i18n-label">' . ( $no_sites + $no_downloads ) . '</strong>'
		);
		$html .= '</p>';

		if ( $latest_incomplete ) {
			$html .= '<p>';
			$html .= sprintf(
				__( 'Locales that are up-to-date with incomplete packages: %s' ),
				implode( ', ', $latest_incomplete )
			);
		}

		$html = '<div class="translators-info">' . $html . $table . '</div>';

		wp_enqueue_style( 'wp-i18n-team' );

		return $html;
	}

}

new WP_I18n_Teams();