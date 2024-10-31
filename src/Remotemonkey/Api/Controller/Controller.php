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
 * abstract API controller
 *
 * @since    0.0.1
 */
abstract class Controller {

	/**
	 * task object
	 *
	 * @var \stdClass
	 *
	 * @since  0.0.1
	 */
	protected $task;

	/**
	 * Controller constructor.
	 *
	 * @param   \stdClass $task  task object provided by backend
	 *
	 * @since  0.0.1
	 */
	public function __construct( \stdClass $task ) {
		$this->task = $task;
	}

	/**
	 * main method
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	abstract public function execute();

	/**
	 * Get a DB Connection
	 *
	 * @return \wpdb
	 */
	public function getDBO() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Clears existing update caches for plugins, themes, and core.
	 */
	public function cleanUpdateCache() {
		// Check if function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'wp_clean_update_cache' ) ) {
			require_once ABSPATH . WPINC . '/update.php';

		}

		wp_clean_update_cache();

		if ( ! function_exists( 'delete_site_transient' ) ) {
			require_once ABSPATH . WPINC . '/option.php';

		}

		delete_site_transient( 'update_plugins' );
		delete_transient( 'update_plugins' );

		// Force system to make a check
		wp_update_plugins();

		delete_site_transient( 'update_themes' );
		delete_transient( 'update_themes' );

		wp_update_themes();

		wp_version_check();
	}

	/**
	 * @param $options
	 * @param $locale
	 *
	 * @return array
	 */
	public function getCoreUpdate( $options = array(), $locale = null ) {
		$this->cleanUpdateCache();

		if ( ! function_exists( 'get_core_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$updates = get_core_updates( $options );

		if ( empty( $updates ) ) {
			return array();
		}

		// Ship all non updates
		$updates = array_filter(
			$updates,
			function ( $element ) {
				return 'latest' !== $element->response;
			}
		);

		if ( empty( $locale ) ) {
			return $updates;
		}

		$result = array();

		foreach ( $updates as $update ) {
			if ( $update->locale === $locale ) {
				$result[] = $update;

				break;
			}
		}

		return $result;
	}

	/**
	 * Get the current locale for the site
	 *
	 * @return string
	 */
	public function getLocale() {
		if ( ! function_exists( 'get_locale' ) ) {
			require_once ABSPATH . 'wp-includes/l10n.php';
		}

		return get_locale();
	}
}
