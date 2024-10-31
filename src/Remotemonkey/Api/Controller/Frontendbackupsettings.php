<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2021 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Remotemonkey\Akeeba\Helper;

/**
 * Sysinfo task class
 *
 * @since    0.0.1
 */
class Frontendbackupsettings extends EncryptedSettingsController {

	/**
	 * returns system information
	 *
	 * @return array|boolean
	 */
	public function execute() {
		$params = get_option( 'akeebabackupwp_config', '{}' );
		$params = json_decode( $params, true );

		$config = array(
			'frontend_secret_word' => ( ! empty( $params['options']['frontend_secret_word'] ) ) ? $params['options']['frontend_secret_word'] : '',
			'jsonapi_enabled'      => ( ! empty( $params['options']['jsonapi_enabled'] ) ) ? $params['options']['jsonapi_enabled'] : false,
			'legacyapi_enabled'    => ( ! empty( $params['options']['legacyapi_enabled'] ) ) ? $params['options']['legacyapi_enabled'] : false,
		);

		$secretWord = $this->decryptSettings( $config['frontend_secret_word'] );

		// We have non-printable chars in the string, that's not plausible - decryption failed
		// @codingStandardsIgnoreLine
		if ( @preg_replace( '/[\x00-\x1F\x7F\xA0]/u', '', $secretWord ) !== $secretWord ) {
			$secretWord = '';
		}

		$config['frontend_secret_word']    = $secretWord;
		$config['frontend_enabled']        = $config['jsonapi_enabled'];
		$config['failure_frontend_enable'] = $config['jsonapi_enabled'];

		return $config;
	}
}
