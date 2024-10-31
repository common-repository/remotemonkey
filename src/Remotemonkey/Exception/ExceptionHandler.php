<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Exception;

/**
 * Class for handling exceptions.
 */
class ExceptionHandler {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		set_exception_handler( array( $this, 'exception' ) );
	}

	/**
	 * Handle the exception.
	 *
	 * @param \Exception|\Throwable|\Error $exception The exception to handle.
	 */
	public function exception( $exception ) {

		$response = array(
			'success' => false,
			'code'    => 200,
		);

		if ( $exception instanceof \Exception || $exception instanceof \Error ) {
			$response = array(
				'success'  => false,
				'code'     => 200,
				'messages' => (array) $exception->getMessage(),
				'data'     => array(
					'exception_code' => $exception->getCode(),
					'file'           => $exception->getFile(),
					'line'           => $exception->getLine(),
				),
			);
		}

		echo wp_json_encode( $response );
		die();
	}
}
