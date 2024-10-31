<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2021 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;
use Remotemonkey\Akeeba\Helper;
use Remotemonkey\Encryption\Encrypt;

/**
 * Fetchprofilesettings task class
 *
 * @since    0.0.1
 */
class Fetchprofilesettings extends EncryptedSettingsController {

	/**
	 * returns system information
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 *
	 * @return string
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		if ( empty( $this->task->profile_id ) || 0 === (int) $this->task->profile_id ) {
			throw new \InvalidArgumentException( 'Invalid or missing profile ID' );
		}

		$db = $this->getDBO();

		$query = $db->prepare(
			"SELECT id, description, configuration FROM  {$db->prefix}ak_profiles WHERE id = %d",
			$this->task->profile_id
		);

		$profileRow = $db->get_row( $query );

		if ( ! $profileRow ) {
			throw new \InvalidArgumentException( 'Invalid profile ID' );
		}

		// Parse ini-coded backup data
		// @codingStandardsIgnoreLine
		$result = @parse_ini_string( $this->decryptSettings( $profileRow->configuration ) );

		// Ini-based parsing failed, try json
		if ( false === $result ) {
			// @codingStandardsIgnoreLine
			$result = @json_decode( $this->decryptSettings( $profileRow->configuration ) );
		}

		// Decryption failed
		if ( null === $result ) {
			throw new \RuntimeException( 'Could not decode settings' );
		}

		// Return settings settings
		return wp_json_encode( $result );
	}
}
