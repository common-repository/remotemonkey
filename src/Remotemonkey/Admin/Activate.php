<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Admin;

/**
 * Class Activate
 *
 * @package  Remotemonkey\Admin
 * @since    0.0.1
 */
class Activate {

	public function execute() {
		// Check if it is the fist time the plugin is activated
		if ( $this->isFirstInstall() ) {
			// Generate a site key
			$key = wp_generate_password( 64, false, false );

			$value = array(
				'remotemonkey_site_key'           => $key,
				'remotemonkey_validate_timestamp' => '1',
			);

			add_option( 'remotemonkey_options', $value );

			add_option( 'remotemonkey_do_activation_redirect', true );
		}

		$connected = \Remotemonkey\Connect\Connector::getConnectionStatus();

		// Check connection
		if ( ! $connected ) {
			add_option( 'remotemonkey_do_activation_redirect', true );
		}
	}

	/**
	 * Checks if this is the first time activation
	 *
	 * @return bool
	 */
	private function isFirstInstall() {
		return false === get_option( 'remotemonkey_options' );
	}
}
