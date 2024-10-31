<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2021 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

/**
 * Abstract extension controller
 *
 * @since    0.0.2
 */
abstract class ExtensionController extends Controller {

	/**
	 * @param $key
	 * @param $properties
	 *
	 * @return array
	 */
	protected function mapPluginData( $key, $properties ) {
		$pluginFolder = pathinfo( $key, PATHINFO_DIRNAME );

		return array(
			'element'        => '.' === $pluginFolder ? $properties['Name'] : $pluginFolder,
			'extension_id'   => preg_replace( '/\.php$/i', '', $key ),
			'name'           => $properties['Name'],
			'translatedName' => $properties['Name'],
			'manifest_cache' => $properties,
			'version'        => $properties['Version'],
			'type'           => 'plugin',
			'folder'         => $pluginFolder,
			'enabled'        => is_plugin_active( $key ),
			'needsUpdate'    => false,
			'newVersion'     => '',
		);
	}

	/**
	 * @param $key
	 * @param $properties
	 *
	 * @return array
	 */
	protected function mapThemeData( $key, $properties ) {
		return array(
			'element'               => $properties['Name'],
			'extension_id'          => $key,
			'name'                  => $properties['Name'],
			'translatedName'        => $properties['Name'],
			'manifest_cache'        => $properties,
			'version'               => $properties['Version'],
			'type'                  => 'theme',
			'folder'                => '',
			'enabled'               => 1,
			'requires_core_version' => $properties['RequiresWP'],
			'requires_php_version'  => $properties['RequiresPHP'],
			'requires_db_version'   => '',
			'needsUpdate'           => false,
			'newVersion'            => '',
		);
	}

	/**
	 * Get all Extensions
	 *
	 * @return mixed
	 *
	 * @since 0.0.2
	 */
	public function getPlugins() {
		// Check if function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return \get_plugins();
	}

	/**
	 * Get all Plugins who need an update
	 *
	 * @return array
	 */
	public function getPluginUpdates() {
		// Check if function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		return \get_plugin_updates();
	}

	/**
	 * Get plugin meta data
	 *
	 * @param $plugin
	 *
	 * @return array
	 */
	public function getPluginData( $plugin ) {
		// Check if function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return \get_plugin_data( $plugin . '.php' );
	}

	/**
	 *
	 * @return \WP_Theme[]
	 */
	public function getThemes() {
		// Check if function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'wp_get_themes' ) ) {
			require_once ABSPATH . 'wp-includes/theme.php';
		}

		return \wp_get_themes();
	}

	/**
	 * Get all Themes who need an update
	 *
	 * @return array
	 */
	public function getThemeUpdates() {
		// Check if function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_theme_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		return \get_theme_updates();
	}

	/**
	 * Get the update info for a theme
	 *
	 * @param $theme
	 *
	 * @return mixed
	 */
	public function themeUpdateInfo( $theme ) {
		$result  = new \stdClass();
		$updates = $this->getThemeUpdates();

		if ( ! isset( $updates[ $theme ] ) ) {
			return $result;
		}

		$update = $updates[ $theme ];

		$result->RequiresWP  = $update->update['requires'];
		$result->RequiresPHP = $update->update['requires_php'];

		return $result;
	}

	/**
	 * Get the update info for a plugin
	 *
	 * @param $plugin
	 *
	 * @return mixed
	 */
	public function pluginUpdateInfo( $plugin ) {
		$updates = $this->getPluginUpdates();

		if ( isset( $updates[ $plugin ] ) ) {
			return $updates[ $plugin ];
		}

		return new \stdClass();
	}
}
