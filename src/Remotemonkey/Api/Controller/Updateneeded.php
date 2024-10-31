<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

/**
 * Sysinfo task class
 *
 * @since    0.0.1
 */
class Updateneeded extends ExtensionController {

	/**
	 * returns system information
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		// Clean cache to get actual data
		$this->cleanUpdateCache();

		// Plugins
		$plugins    = $this->getPlugins();
		$updates    = $this->getPluginUpdates();
		$updateKeys = array_keys( $updates );

		$results = array();

		foreach ( $plugins as $key => $properties ) {
			$result = $this->mapPluginData( $key, $properties );

			if ( in_array( $key, $updateKeys, true ) ) {
				$result['needsUpdate'] = true;
				$result['newVersion']  = isset( $updates[ $key ]->update->new_version )
					? $updates[ $key ]->update->new_version : '0.0.0';

				$results[] = $result;
			}
		}

		// Themes
		$themes     = $this->getThemes();
		$updates    = $this->getThemeUpdates();
		$updateKeys = array_keys( $updates );

		foreach ( $themes as $key => $properties ) {
			$result = $this->mapThemeData( $key, $properties );

			if ( in_array( $key, $updateKeys, true ) ) {
				$result['needsUpdate'] = true;
				$result['newVersion']  = isset( $updates[ $key ]->update['new_version'] )
					? $updates[ $key ]->update['new_version'] : '0.0.0';

				// Overwrite value because we want to know the target platform
				$result['requires_core_version'] = $properties['requires'];
				$result['requires_php_version']  = $properties['requires_php'];

				$results[] = $result;
			}
		}

		return $results;
	}
}
