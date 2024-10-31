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
class BaseREST {
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
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'remotemonkey/v1',
					'task',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'task' ),
						'permission_callback' => '__return_true'
					)
				);
			}
		);
	}

	/**
	 * Check the authentication of the request
	 *
	 * @param \WP_REST_Request $request  request object
	 */
	private function authCheck( \WP_REST_Request $request ) {
		$message   = $request->get_param( 'monkeytask' );
		$signature = $request->get_param( 'signature' );

		// Split message into timestamp and task
		list($timestamp, $monkeytask) = explode( ':', $message, 2 );

		// Validate inputs
		if ( ! $timestamp ) {
			return new \WP_Error( 'auth_error_1', 'Invalid or missing timestamp in message argument', array( 'status' => 400 ) );
		}

		if ( ! $monkeytask ) {
			return new \WP_Error( 'auth_error_2', 'Invalid or missing monkeytask in message argument', array( 'status' => 400 ) );
		}

		if ( ! $signature ) {
			return new \WP_Error( 'auth_error_3', 'Invalid or missing signature argument', array( 'status' => 400 ) );
		}

		// Verify message signature
		if ( $this->auth->checkSignature( $message, $signature, $timestamp ) !== true ) {
			return new \WP_Error( 'auth_error_4', 'Invalid message signature', array( 'status' => 400 ) );
		}

		return true;
	}

	/**
	 * Performs a given task
	 *
	 * @param \WP_REST_Request $request  request object
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function task( \WP_REST_Request $request ) {
		$message = $request->get_param( 'monkeytask' );

		// Split message into timestamp and task
		list($timestamp, $monkeytaskJson) = explode( ':', $message, 2 );

		if ( $this->authCheck( $request ) !== true ) {
			return new \WP_Error( 'request_auth', 'Invalid message signature', array( 'status' => 401 ) );
		}

		// Get task to execute
		$monkeytask = json_decode( (string) $monkeytaskJson );

		// Validate correct JSON
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error( 'request_1', 'Invalid monkeytask JSON ' . $monkeytaskJson, array( 'status' => 422 ) );
		}

		$classname = '\\Remotemonkey\\Api\\Controller\\' . ucfirst( $monkeytask->controller );

		// Dispatch task
		if ( ! class_exists( $classname ) ) {
			return new \WP_Error( 'request_2', 'Invalid Controller', array( 'status' => 422 ) );
		}

		/** @var \Remotemonkey\Api\Controller\Controller $controller */
		$controller = new $classname( $monkeytask );

		$response = $controller->execute();

		return new \WP_REST_Response(
			array(
				'success'  => true,
				'data'     => $response,
				'messages' => array(),
			)
		);
	}
}
