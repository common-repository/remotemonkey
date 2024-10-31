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
 * Class Menu
 *
 * @package  Remotemonkey\Admin
 * @since    0.0.1
 */
class Menu {

	/**
	 * Add the menu
	 */
	public function add() {
		add_action( 'admin_menu', array( $this, 'addSubmitPages' ) );
	}

	/**
	 * Add the submit page
	 */
	public function addSubmitPages() {
		add_management_page(
			'BackupMonkey',
			'BackupMonkey',
			'manage_options',
			'rbm',
			array( $this, 'adminPageHtml' )
		);
	}

	/**
	 * The admin Page HTML
	 */
	public function adminPageHtml() {
		?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php

		$connected = \Remotemonkey\Connect\Connector::getConnectionStatus();

		if ( $connected ) {
			$this->adminPageFormHtml();
		} else {
			$this->adminPageButtonHtml();
		}

		?>
		</div>
		<?php
	}

	/**
	 * The form HTML
	 */
	public function adminPageFormHtml() {
		?>

		<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
			<?php
			settings_fields( 'remotemonkey_options' );
			do_settings_sections( 'remotemonkey_plugin' );
			?>
			<input
			name="submit"
			class="button button-primary"
			type="submit"
			value="<?php esc_attr_e( 'Save' ); ?>"
			/>
		</form>

		<?php
	}

	/**
	 * The HTML for the connect button
	 */
	public function adminPageButtonHtml() {
		$options = get_option( 'remotemonkey_options', array() );

		$key = $options['remotemonkey_site_key'];

		$addEndpoint = REMOTEMONKEY_GUIURL . '/#/addSite/' . base64_encode(
			wp_json_encode(
				array(
					'name'       => get_bloginfo( 'name' ),
					'key'        => $key,
					'access_url' => home_url(),
					'apptype'    => 'Wordpress',
				)
			)
		);

		?>
		<a href="<?php echo esc_url( $addEndpoint ); ?>" class="button button-primary" target="_blank">
			Connect
		</a>
		<?php
	}
}
