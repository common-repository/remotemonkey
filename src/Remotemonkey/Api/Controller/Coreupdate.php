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
 * Coreupdate task class
 *
 * @since    0.0.1
 */
class Coreupdate extends Controller {

	/**
	 * returns system information
	 *
	 * @return bool
	 *
	 * @throws Exception
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			throw new Exception( 'file modification is disabled (DISALLOW_FILE_MODS)', 403 );
		}

		if ( ! class_exists( 'Core_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/admin.php';
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			require_once ABSPATH . WPINC . '/update.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		if ( ! class_exists( 'Automatic_Upgrader_Skin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
		}

		$updates = $this->getCoreUpdate();

		if ( empty( $updates ) ) {
			// No Update -> done
			return true;
		}

		$skin = new \Automatic_Upgrader_Skin();

		// Check for filesystem access.
		if ( false === $skin->request_filesystem_credentials( false, ABSPATH ) ) {
			throw new Exception( 'filesystem not writable', 403 );
		}

		$upgrader = new \Core_Upgrader( $skin );

		$update = reset( $updates );

		$result = $upgrader->upgrade( $update );

		if ( is_wp_error( $result ) ) {
			throw new Exception( esc_html( $result->get_error_code() . $result->get_error_data() ), 500 );
		}

		return true;
	}
}
