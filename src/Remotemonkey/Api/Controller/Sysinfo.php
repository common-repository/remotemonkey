<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Remotemonkey\Akeeba\Helper;

/**
 * Sysinfo task class
 *
 * @since    0.0.1
 */
class Sysinfo extends Controller {

	/**
	 * returns system information
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		// Fetch akeeba version
		try {
			$akeebaInfo = Helper::getInformation();

			// Fetch profile list
			$db = $this->getDBO();

			$table = str_replace( '#__', $db->base_prefix, $akeebaInfo['profilesTable'] );

			$akeebaProfiles = $db->get_results( "SELECT id, description FROM $table" );

			$akeeba = array(
				'pro'      => $akeebaInfo['pro'],
				'version'  => $akeebaInfo['version'],
				'date'     => $akeebaInfo['date'],
				'profiles' => $akeebaProfiles,
			);
		} catch ( \RuntimeException $e ) {
			$akeeba = false;
		}

		$locale = $this->getLocale();

		/** @var $wp_version string */
		require ABSPATH . WPINC . '/version.php'; // $wp_version

		$versionParts = explode( '.', $wp_version );

		$major = $versionParts[0];
		$minor = $versionParts[1];
		$patch = count( $versionParts ) > 2 ? $versionParts[2] : 0;

		// Prepare result array
		$sysinfo = array(
			'Wordpress'      => array(
				'long'  => $wp_version,
				'major' => $major,
				'minor' => $minor,
				'patch' => $patch,
			),
			'PHP'            => array(
				'version'            => phpversion(),
				'memory_limit'       => ini_get( 'memory_limit' ),
				'max_execution_time' => ini_get( 'max_execution_time' ),
			),
			'Database'       => array(
				'type'    => 'mysql',
				'version' => $this->getDBO()->db_version(),
			),
			'Multisite'      => defined( 'WP_ALLOW_MULTISITE' ) ? WP_ALLOW_MULTISITE : false,
			'Connector'      => REMOTEMONKEY_VERSION,
			'Akeeba'         => $akeeba,
			'Sitename'       => get_bloginfo( 'name' ),
			'Offlinemode'    => wp_is_maintenance_mode(),
			'Updatechannel'  => 'default',
			'locale'         => $locale,
			'CoreNeedUpdate' => 0,
			'CoreUpdateTo'   => '',
		);

		// Check for core Updates
		$coreUpdate = $this->getCoreUpdate( array(), $locale );

		if ( ! empty( $coreUpdate ) ) {
			$sysinfo['CoreNeedUpdate'] = 1;
			$sysinfo['CoreUpdateTo']   = $coreUpdate[0]->version;
		}

		return $sysinfo;
	}
}
