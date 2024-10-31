<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Akeeba;

/**
 * Akeeba Helper Class
 *
 * @since       1.0.0
 */
class Helper {

	/**
	 * Return version-specific information about the installed akeeba version
	 *
	 * @throws \RuntimeException
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function getInformation() {
		$component = false;

		if ( ! file_exists( WP_PLUGIN_DIR . '/akeebabackupwp/app/version.php' ) ) {
			throw new \RuntimeException( 'Akeeba not installed' );
		}

		require_once WP_PLUGIN_DIR . '/akeebabackupwp/app/version.php';

		return array(
			'version'       => AKEEBABACKUP_VERSION,
			'pro'           => AKEEBABACKUP_PRO,
			'date'          => AKEEBABACKUP_DATE,
			'component'     => 'akeebabackupwp',
			'backupsTable'  => '#__ak_stats',
			'profilesTable' => '#__ak_profiles',
		);
	}
}
