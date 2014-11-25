<?php

if ( ! defined('ABSPATH') ) {
	die();
}

/**
 * Retrievs data from Site Manager
 */
class Site_Manager_WPCLI extends WP_CLI_Command {

	/**
	 * Updates all locales at once
	 */
	public function update( $args, $assoc_args ) {
		WP_I18n_Team_Api::$max = 1000;

		if ( isset( $args[0] ) && $args[0] ) {
			$sites = array( (object) array( 'slug' => $args[0] ) );
		}
		else {
			$sites = WP_I18n_Team_Api::get_sites();
		}

		foreach ( $sites as $site ) {
			if ( WP_I18n_Team_Api::get_locale( $site->slug, false ) ) {
				echo 'Updated: ' . $site->slug . PHP_EOL;
			}
			else {
				echo 'Failed updating: ' . $site->slug . PHP_EOL;
			}
		}
	}

}

WP_CLI::add_command( 'i18n', 'Site_Manager_WPCLI' );