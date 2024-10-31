<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2021 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api\Controller;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;
use Remotemonkey\Akeeba\Helper;

/**
 * Fetchlocalpart task class
 *
 * @since    0.0.1
 */
class Fetchlocalpart extends Controller {

	/**
	 * returns system information
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 *
	 * @since 0.0.1
	 */
	public function execute() {
		if ( empty( $this->task->backup_id ) || 0 === (int) $this->task->backup_id ) {
			throw new \InvalidArgumentException( 'Invalid or missing backup ID' );
		}

		if ( empty( $this->task->part ) || (int) $this->task->part < 1 ) {
			throw new \InvalidArgumentException( 'Invalid or missing part number' );
		}

		// Verify that the supplied id points to a valid backup
		$db = $this->getDBO();

		// Prepare list of columns
		$columns = array(
			'id',
			'type',
			'filesexist',
			'remote_filename AS remoteFilename',
			'total_size AS size',
			'multipart AS parts',
			'status',
			'absolute_path AS path',
		);

		$query = $db->prepare(
			'SELECT ' . implode( ',', $columns ) . " FROM  {$db->prefix}ak_stats WHERE id = %d",
			$this->task->backup_id
		);

		$backupRow = $db->get_row( $query );

		// Check backup state
		if ( 0 === $backupRow->size ) {
			throw new \InvalidArgumentException( 'Invalid backup ID, empty backup' );
		}

		if ( 'complete' !== $backupRow->status ) {
			throw new \InvalidArgumentException( 'Can not fetch incomplete backup' );
		}

		if ( 0 === $backupRow->filesexist || $backupRow->remoteFilename ) {
			throw new \InvalidArgumentException( 'Can not fetch remote backup using localpart endpoint' );
		}

		// Find file
		$file = $backupRow->path;

		if ( 1 !== (int) $this->task->part ) {
			$file = substr( $file, 0, -2 ) . sprintf( '%02d', (int) $this->task->part - 1 );
		}

		// Check if file exists and is readble
		// @codingStandardsIgnoreLine
		if ( ! @file_exists( $file ) || ! @is_readable( $file ) ) {
			throw new \RuntimeException( 'Backup does not exist' );
		}

		// For a certain unmentionable browser -- Thank you, Nooku, for the tip
		if ( function_exists( 'ini_get' ) && function_exists( 'ini_set' ) ) {
			if ( ini_get( 'zlib.output_compression' ) ) {
				// @codingStandardsIgnoreLine
				ini_set( 'zlib.output_compression', 'Off' );
			}
		}

		// Remove php's time limit
		if ( function_exists( 'ini_get' ) && function_exists( 'set_time_limit' ) ) {
			if ( ! ini_get( 'safe_mode' ) ) {
				// @codingStandardsIgnoreLine
				@set_time_limit( 0 );
			}
		}

		// @codingStandardsIgnoreLine
		$basename  = @basename( $file );
		// @codingStandardsIgnoreLine
		$filesize  = @filesize( $file );
		$extension = strtolower( str_replace( '.', '', strrchr( $file, '.' ) ) );

		// @codingStandardsIgnoreLine
		while ( @ob_end_clean() ) {
			true;
		}

		// @codingStandardsIgnoreLine
		@clearstatcache();

		// Send MIME headers
		header( 'MIME-Version: 1.0' );
		header( 'Content-Disposition: attachment; filename="' . $basename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );

		switch ( $extension ) {
			case 'zip':
				// ZIP MIME type
				header( 'Content-Type: application/zip' );
				break;

			default:
				// Generic binary data MIME type
				header( 'Content-Type: application/octet-stream' );
				break;
		}

		// Notify of filesize, if this info is available
		if ( $filesize > 0 ) {
			// @codingStandardsIgnoreLine
			header( 'Content-Length: ' . @filesize( $file ) );
		}

		// Disable caching
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Expires: 0' );
		header( 'Pragma: no-cache' );

		flush();

		if ( ! $filesize ) {
			// If the filesize is not reported, hope that readfile works
			// @codingStandardsIgnoreLine
			@readfile( $file );

			exit();
		}

		// If the filesize is reported, use 1M chunks for echoing the data to the browser
		$blocksize = 1048576;
		// @codingStandardsIgnoreLine
		$handle    = @fopen( $file, 'r' );

		// Now we need to loop through the file and echo out chunks of file data
		if ( false !== $handle ) {
			// @codingStandardsIgnoreLine
			while ( ! @feof( $handle ) ) {
				// @codingStandardsIgnoreLine
				echo @fread( $handle, $blocksize );
				// @codingStandardsIgnoreLine
				@ob_flush();
				flush();
			}
		}

		if ( false !== $handle ) {
			// @codingStandardsIgnoreLine
			@fclose( $handle );
		}
	}
}
