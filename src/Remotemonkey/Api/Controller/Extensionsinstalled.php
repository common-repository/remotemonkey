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
class Extensionsinstalled extends ExtensionController {

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
		$plugins = $this->getPlugins();
		$results = array();

		foreach ( $plugins as $key => $properties ) {
			$result = $this->mapPluginData( $key, $properties );

			$results[] = $result;
		}

		// Themes
		$themes = $this->getThemes();
		foreach ( $themes as $key => $properties ) {
			$result = $this->mapThemeData( $key, $properties );

			$results[] = $result;
		}

		return $results;
	}
}
