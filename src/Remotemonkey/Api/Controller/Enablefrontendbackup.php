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
use Joomla\CMS\User\UserHelper;

/**
 * Sysinfo task class
 *
 * @since    0.0.1
 */
class Enablefrontendbackup extends Controller {

	/**
	 * returns system information
	 *
	 * @return array|boolean
	 *
	 * @since 0.0.1
	 *
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function execute() {
		$params = get_option( 'akeebabackupwp_config', '{}' );
		$params = json_decode( $params, true );

		if ( empty( $params['options'] ) ) {
			$params['options'] = array();
		}

		$params['options']['jsonapi_enabled']      = true;
		$params['options']['legacyapi_enabled']    = true;
		$params['options']['frontend_secret_word'] = wp_generate_password( 32 );

		update_option( 'akeebabackupwp_config', wp_json_encode( $params ) );

		return true;
	}
}
