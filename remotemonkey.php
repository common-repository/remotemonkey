<?php
// @codingStandardsIgnoreFile
/*
Plugin Name: RemoteMonkey
Description: Connecting to the BackupMonkey website management
Version: 1.1.5
Author: BackupMonkey.io Team <team@backupmonkey.io>
Author URI: https://backupmonkey.io/
License: GPLv2
*/

// No direct access
defined( 'WPINC' ) || die;

define( 'REMOTEMONKEY_APIURL', 'https://api.backupmonkey.io/' );
define( 'REMOTEMONKEY_GUIURL', 'https://console.backupmonkey.io' );
define( 'REMOTEMONKEY_VERSION', '1.1.5' );

/**
 * Include the autoloader
 */
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	include __DIR__ . '/vendor/autoload.php';
}

// Configure Settings
( new Remotemonkey\Admin\Settings() )->setup();

// Add Menu
( new \Remotemonkey\Admin\Menu() )->add();

// Init Admin-AJAX API
( new \Remotemonkey\Api\Base() )->init();

// Init REST API
( new \Remotemonkey\Api\BaseREST() )->init();

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function remotemonkey_activate() {
	( new \Remotemonkey\Admin\Activate() )->execute();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function remotemonkey_deactivate() {
	( new \Remotemonkey\Admin\Deactivate() )->execute();
}

register_activation_hook( __FILE__, 'remotemonkey_activate' );
register_deactivation_hook( __FILE__, 'remotemonkey_deactivate' );
