<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2021 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Joomla\Database\ParameterType;
use Remotemonkey\Akeeba\Helper;

/**
 * Backuplist task class
 *
 * @since    0.0.1
 */
class Backup extends Controller {

	/**
	 * returns system information
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		if ( empty( $this->task->backup_id ) || 0 === (int) $this->task->backup_id ) {
			throw new \InvalidArgumentException( 'Invalid or missing backup ID' );
		}

		$db = $this->getDBO();

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
			'total_size AS totalSize',
			'multipart AS parts',
			'profile_id',
		);

		$query = $db->prepare(
			'SELECT ' . implode( ',', $columns ) . " FROM  {$db->prefix}ak_stats WHERE id = %d",
			$this->task->backup_id
		);

		return $db->get_row( $query );
	}
}
