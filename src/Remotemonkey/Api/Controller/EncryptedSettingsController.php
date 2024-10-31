<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2021 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Remotemonkey\Encryption\Encrypt;

/**
 * abstract API controller
 *
 * @since    0.0.1
 */
abstract class EncryptedSettingsController extends Controller {

	/**
	 * get serverside encryption key
	 *
	 * @return mixed
	 *
	 * @since version
	 *
	 * @throws \RuntimeException
	 */
	protected function getKey() {
		if ( ! file_exists( WP_CONTENT_DIR . '/akeebabackup_secretkey.php' ) ) {
			throw new \RuntimeException( 'Akeeba not installed' );
		}

		require_once WP_CONTENT_DIR . '/akeebabackup_secretkey.php';

		if ( ! defined( 'AKEEBA_SERVERKEY' ) ) {
			throw new \RuntimeException( 'Could not find serverkey constant' );
		}

		return base64_decode( AKEEBA_SERVERKEY );
	}


	/**
	 * Decrypts the encrypted settings and returns the plaintext INI string
	 *
	 * @param   string $encrypted  The encrypted data
	 *
	 * @return  string   The decrypted config or false
	 *
	 * @since  0.0.1
	 *
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	protected function decryptSettings( $encrypted ) {
		$crypto = new Encrypt();

		if ( substr( $encrypted, 0, 12 ) === '###AES128###' ) {
			$mode = 'AES128';
		} elseif ( substr( $encrypted, 0, 12 ) === '###CTR128###' ) {
			$mode = 'CTR128';
		} else {
			return $encrypted;
		}

		if ( empty( $key ) ) {
			$key = $this->getKey();
		}

		if ( empty( $key ) ) {
			return '';
		}

		$encrypted = substr( $encrypted, 12 );

		switch ( $mode ) {
			default:
			case 'AES128':
				$encrypted = base64_decode( $encrypted );
				$decrypted = rtrim( $crypto->AESDecryptCBC( $encrypted, $key ), "\0" );
				break;

			case 'CTR128':
				$decrypted = $crypto->AESDecryptCtr( $encrypted, $key, 128 );
				break;
		}

		if ( empty( $decrypted ) ) {
			$decrypted = '';
		}

		return $decrypted;
	}
}
