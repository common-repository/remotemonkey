<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2021 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Remotemonkey\Akeeba\Helper;

/**
 * Backuplist task class
 *
 * @since    0.0.1
 */
class Backuplist extends Controller {

	/**
	 * returns system information
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		// Test if akeeba is installed
		try {
			$akeebaInfo = Helper::getInformation();
		} catch (\RuntimeException $e) {
			return [];
		}

		$db = $this->getDbo();

		// Prepare and quote list of columns
		$columns = array(
			'id',
			'description',
			'backupstart AS start',
			'backupend AS end',
			'status',
			'filesexist',
			'remote_filename AS remoteFilename',
			'origin',
			'type',
			'archivename',
			'absolute_path',
			'total_size AS totalSize',
			'multipart AS parts',
			'profile_id',
			'comment',
		);

		return $db->get_results( 'SELECT ' . implode( ',', $columns ) . " FROM  {$db->prefix}ak_stats" );
	}
}
