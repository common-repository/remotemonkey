<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Connect;

/**
 * Class Connector
 *
 * @package  BM\Connect
 * @since    1.0
 */
class Connector {

	/**
	 * Get conneciton status from API
	 *
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public static function getConnectionStatus() {
		$options = get_option( 'remotemonkey_options', array() );

		$body = array(
			'siteToken' => $options['remotemonkey_site_key'],
			'url'       => home_url(),
		);

		$args = array(
			'body'        => $body,
			'timeout'     => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(),
			'cookies'     => array(),
		);

		$response = wp_remote_post( REMOTEMONKEY_APIURL . 'api/sitestatus', $args );

		// Check code
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		// Check presence of id
		if ( empty( $body ) || empty( $body->data->id ) ) {
			return false;
		}

		return true;
	}
}
