<?php
/**
 * @package    Remotemonkey
 * @author     BackupMonkey.io Team <team@backupmonkey.io>
 *
 * @copyright  2023 backupmonkey.io
 * @license    GNU General Public License version 2 or later
 */

namespace Remotemonkey\Admin;

/**
 * Class Settings
 *
 * @package  BM\Admin
 * @since    1.0
 */
class Settings {

	public function setup() {
		// Init setting and forms
		add_action( 'admin_init', array( $this, 'init' ) );

		// Redirect to finish setup
		add_action(
			'admin_init',
			function () {
				if ( get_option( 'remotemonkey_do_activation_redirect', false ) ) {
					delete_option( 'remotemonkey_do_activation_redirect' );

					if ( wp_safe_redirect( 'tools.php?page=rbm' ) ) {
						exit();
					}
				}
			}
		);
	}

	public function init() {
		register_setting(
			'remotemonkey_options',
			'remotemonkey_options',
			array( $this, 'checkValues' )
		);

		add_settings_section(
			'remotemonkey_setting',
			'Remote Monkey Settings',
			function () {
				echo '<p>' . esc_html( __( 'Here you can set all the options for using the Remote Monkey from Backup Monkey', 'remotemonkey' ) ) . '</p>';
			},
			'remotemonkey_plugin'
		);

		add_settings_field(
			'remotemonkey_site_key',
			__( 'Site Key', 'remotemonkey' ),
			function () {
				$options = get_option( 'remotemonkey_options', array() );
				echo "<input id='remotemonkey_site_key' readonly name='remotemonkey_options[remotemonkey_site_key]' size='64' type='text' value='"
					. esc_attr( $options['remotemonkey_site_key'] ?? '' ) . "' />";
			},
			'remotemonkey_plugin',
			'remotemonkey_setting'
		);

		add_settings_field(
			'remotemonkey_validate_timestamp',
			__( 'Validate Timestamp', 'remotemonkey' ),
			function () {
				$options = get_option( 'remotemonkey_options', array() );
				echo "<input id='remotemonkey_validate_timestamp' name='remotemonkey_options[remotemonkey_validate_timestamp]' type='checkbox' value='1'
				" . esc_attr( '1' === $options['remotemonkey_validate_timestamp'] ? ' checked' : '' ) . ' />';
			},
			'remotemonkey_plugin',
			'remotemonkey_setting'
		);
	}

	public function checkValues( $value ) {
		$valid = true;
		$input = (array) $value;

		if ( empty( $input['remotemonkey_site_key'] ) ) {
			$valid = false;
			add_settings_error( 'bm_remotemonkey', 'site_key', __( 'Site Key is empty.', 'bm_remotemonkey' ) );
		}

		if ( empty( $input['remotemonkey_validate_timestamp'] ) ) {
			$value['remotemonkey_validate_timestamp'] = 0;
		}

		// Ignore the user's changes and use the old database value.
		if ( ! $valid ) {
			$value = get_option( 'bm_remotemonkey' );
		}

		return $value;
	}
}
