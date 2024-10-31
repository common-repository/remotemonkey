<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api;

use Remotemonkey\Exception\ExceptionHandler;

/**
 * Class Base
 *
 * @package  BM\Api
 * @since    1.0
 */
class Base {
	/**
	 * @var Authentication
	 */
	private $auth;

	/**
	 * Class constructor
	 */
	public function __construct() {

		new ExceptionHandler();

		$options = get_option( 'remotemonkey_options', array() );

		if ( empty( $options['remotemonkey_site_key'] ) ) {
			return;
		}

		$validateTimestamp = ! empty( $options['remotemonkey_validate_timestamp'] ) ? $options['remotemonkey_validate_timestamp'] : 0;

		$this->auth = new Authentication(
			$options['remotemonkey_site_key'],
			$validateTimestamp
		);
	}

	/**
	 * Init the API routes
	 */
	public function init() {
		if (isset( $_POST['monkeytask'] ) && isset( $_POST['signature'] )) {
			define('DOING_CRON', true);
		}

		add_action(
			'wp_ajax_nopriv_remotemonkey_performtask',
			array( $this, 'task' )
		);
	}

	/**
	 * Check the authentication of the request
	 */
	private function authCheck() {
		$message   = isset( $_POST['monkeytask'] ) ? wp_unslash( $_POST['monkeytask'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$signature = isset( $_POST['signature'] ) ? wp_unslash( $_POST['signature'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		// Split message into timestamp and task
		list($timestamp, $monkeytask) = explode( ':', $message, 2 );

		// Validate inputs
		if ( ! $timestamp ) {
			wp_send_json_error( new \WP_Error( 'auth_error_1', 'Invalid or missing timestamp in message argument', array( 'status' => 400 ) ) );
		}

		if ( ! $monkeytask ) {
			wp_send_json_error( new \WP_Error( 'auth_error_2', 'Invalid or missing monkeytask in message argument', array( 'status' => 400 ) ) );
		}

		if ( ! $signature ) {
			wp_send_json_error( new \WP_Error( 'auth_error_3', 'Invalid or missing signature argument', array( 'status' => 400 ) ) );
		}

		// Verify message signature
		if ( $this->auth->checkSignature( $message, $signature, $timestamp ) !== true ) {
			wp_send_json_error( new \WP_Error( 'auth_error_4', 'Invalid message signature', array( 'status' => 400 ) ) );
		}

		return true;
	}

	/**
	 * Performs a given task
	 */
	public function task() {
		$message = isset( $_POST['monkeytask'] ) ? wp_unslash( $_POST['monkeytask'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		// Split message into timestamp and task
		list($timestamp, $monkeytaskJson) = explode( ':', $message, 2 );

		if ( $this->authCheck() !== true ) {
			wp_send_json_error( new \WP_Error( 'request_auth', 'Invalid message signature', array( 'status' => 401 ) ) );
		}

		// Get task to execute
		$monkeytask = json_decode( (string) $monkeytaskJson );

		// Validate correct JSON
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( new \WP_Error( 'request_1', 'Invalid monkeytask JSON ' . $monkeytaskJson, array( 'status' => 422 ) ) );
		}

		$classname = '\\Remotemonkey\\Api\\Controller\\' . ucfirst( $monkeytask->controller );

		// Dispatch task
		if ( ! class_exists( $classname ) ) {
			wp_send_json_error( new \WP_Error( 'request_2', 'Invalid Controller', array( 'status' => 422 ) ) );
		}

		define('DOING_CRON', true);

		/** @var \Remotemonkey\Api\Controller\Controller $controller */
		$controller = new $classname( $monkeytask );

		$response = $controller->execute();

		wp_send_json(
			array(
				'success'  => true,
				'data'     => $response,
				'messages' => array(),
			),
			200
		);
	}
}
