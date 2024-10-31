<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Exception;

/**
 * Sysinfo task class
 *
 * @since    0.0.2
 */
class Updateextension extends ExtensionController {

	/**
	 * returns system information
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		if ( empty( $this->task->eid ) ) {
			throw new \InvalidArgumentException( 'Could not find extension id parameter' );
		}

		if ( '' === $this->task->eid ) {
			throw new \InvalidArgumentException( 'Invalid extension id' );
		}

		list($type, $eid) = explode( ':', $this->task->eid );

		switch ( strtolower( $type ) ) {
			case 'plugin':
				$result   = $this->updatePlugin( $eid );
				$metaData = $this->getPluginData( $eid );
				break;
			case 'theme':
				$result = $this->updateTheme( $eid );
				break;
		}

		if ( true === $result ) {
			return array(
				'success' => true,
				'version' => $metaData['Version'],
			);
		}

		return array(
			'success' => false,
			'version' => $metaData['Version'],
		);
	}

	/**
	 * @param $eid
	 *
	 * @throws Exception
	 *
	 * @return bool
	 */
	protected function updatePlugin( $eid ) {
		$this->cleanUpdateCache();

		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/admin.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once ABSPATH . WPINC . '/update.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
		}

		if ( ! class_exists( 'Automatic_Upgrader_Skin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
		}

		$skin = new \Automatic_Upgrader_Skin();

		$upgrader = new \Plugin_Upgrader( $skin );

		$pluginFilePath = $eid . '.php';

		// Check target versions
		$updateData = $this->pluginUpdateInfo( $pluginFilePath );

		if ( ! empty( $updateData->RequiresPHP ) && version_compare( phpversion(), $updateData->RequiresPHP ) < 0 ) {
			throw new Exception( 'The minimum required PHP version for this update is ' . esc_html( $updateData->RequiresPHP ), 500 );
		}

		// Check activation state before update
		$activeBeforeUpdate = is_plugin_active( $pluginFilePath );

		$result = $upgrader->upgrade( $pluginFilePath );

		// Plugins need to be re-enabled after updates
		if ($activeBeforeUpdate) {
			activate_plugins( $pluginFilePath, '');
		}

		if ( is_wp_error( $result ) ) {
			throw new Exception( esc_html( $result->get_error_code() . $result->get_error_data() ), 500 );
		}

		if ( false === $result || is_null( $result ) ) {
			throw new Exception( 'unknown error', 500 );
		}

		return true;
	}

	/**
	 * @param $eid
	 *
	 * @return true
	 * @throws Exception
	 */
	protected function updateTheme( $eid ) {
		$this->cleanUpdateCache();

		if ( ! class_exists( 'Theme_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/admin.php';
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			require_once ABSPATH . WPINC . '/update.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
		}

		if ( ! class_exists( 'Automatic_Upgrader_Skin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
		}

		$skin = new \Automatic_Upgrader_Skin();

		$upgrader = new \Theme_Upgrader( $skin );

		// Check target version
		$updateData = $this->themeUpdateInfo( $eid );

		if ( ! empty( $updateData->RequiresPHP ) && version_compare( phpversion(), $updateData->RequiresPHP ) < 0 ) {
			throw new Exception( 'The minimum required PHP version for this update is ' . esc_html( $updateData->RequiresPHP ), 400 );
		}

		$wpVersion = (string) get_bloginfo( 'version' );

		if ( ! empty( $updateData->RequiresWP ) && version_compare( $wpVersion, $updateData->RequiresWP ) < 0 ) {
			throw new Exception( 'The minimum required WordPress version for this update is ' . esc_html( $updateData->RequiresWP ), 400 );
		}

		// Perform upgrade
		$result = $upgrader->upgrade( $eid );

		if ( is_wp_error( $result ) ) {
			throw new Exception( 'theme could not be updated : ' . esc_html( $result->get_error_message() ), 400 );
		}

		if ( false === $result || is_null( $result ) ) {
			throw new Exception( 'unknown error', 500 );
		}

		return true;
	}
}
