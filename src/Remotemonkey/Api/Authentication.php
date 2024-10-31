<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Api;

/**
 * Authentication class
 *
 * @since    0.0.1
 */
class Authentication {

	/**
	 * sites private key
	 *
	 * @var string
	 *
	 * @since 0.0.1
	 */
	private $privateKey;

	/**
	 * message timestamp
	 *
	 * @var string
	 *
	 * @since 0.0.1
	 */
	private $validateTimestamp;

	/**
	 * Authentication constructor.
	 *
	 * @param   string $privateKey         private site key
	 * @param   int    $validateTimestamp  message timestamp
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @since 0.0.1
	 */
	public function __construct( $privateKey, $validateTimestamp ) {
		if ( ! $privateKey ) {
			throw new \InvalidArgumentException( 'Invalid site key provided' );
		}

		$this->privateKey        = $privateKey;
		$this->validateTimestamp = $validateTimestamp;
	}

	/**
	 * Check message signature using the site's key
	 *
	 * @param   string $message    original message
	 * @param   string $signature  message signature
	 * @param   int    $timestamp  time stamp of command
	 *
	 * @return boolean
	 *
	 * @since 0.0.1
	 */
	public function checkSignature( $message, $signature, $timestamp ) {
		$hash = $this->makeHash( $message );

		if ( ! $this->hashEquals( $hash, $signature ) ) {
			return false;
		}

		if ( ! $this->timestampIsValid( $timestamp ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate timestamp. The meaning of this check is to enhance security by
	 * making sure any token can only be used in a short period of time.
	 *
	 * @param   int $timestamp  message timestmap
	 *
	 * @return boolean  true if timestamp is correct or if check is disabled in
	 *                  component options
	 *
	 * @since 0.0.1
	 */
	private function timestampIsValid( $timestamp ) {
		if ( ! $this->validateTimestamp ) {
			return true;
		}

		$timestamp = (int) $timestamp;

		if ( ( $timestamp > time() - 360 ) && ( $timestamp < time() + 360 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Calculate the expected hash for a given message
	 *
	 * @param   string $message  plaintext message
	 *
	 * @return string
	 *
	 * @since 0.0.1
	 */
	private function makeHash( $message ) {
		return hash_hmac( 'sha512', $message, $this->privateKey );
	}

	/**
	 * make time safe compare of two hashes
	 *
	 * @param   string $left   left  hash
	 * @param   string $right  right hash
	 *
	 * @return boolean
	 *
	 * @since 0.0.1
	 */
	private function hashEquals( $left, $right ) {
		if ( ! is_string( $left ) || ! is_string( $right ) ) {
			return false;
		}

		$len = strlen( $left );

		if ( strlen( $right ) !== $len ) {
			return false;
		}

		$status = 0;

		for ( $i = 0; $i < $len; $i++ ) {
			$status |= ord( $left[ $i ] ) ^ ord( $right[ $i ] );
		}

		return 0 === $status;
	}
}
