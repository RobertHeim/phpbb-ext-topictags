<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\migrations;
use robertheim\topictags\PREFIXES;
use robertheim\topictags\PERMISSIONS;

class release_0_0_9 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.9-DEV';

	public function effectively_installed()
	{
		return version_compare($this->config[PREFIXES::CONFIG.'_version'], $this->version, '>=');
	}

	static public function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_8',
		);
	}

	public function update_data()
	{
		return array(
			// add permissions
			array('permission.add', array(PERMISSIONS::ADMIN_EDIT_TAGS)),
			array('permission.add', array(PERMISSIONS::MOD_EDIT_TAGS)),
			array('permission.add', array(PERMISSIONS::USE_TAGS)),

			// Set permissions for the board roles
			array('permission.permission_set', array('ROLE_ADMIN_FULL', PERMISSIONS::ADMIN_EDIT_TAGS)),
			array('permission.permission_set', array('ROLE_MOD_FULL', PERMISSIONS::MOD_EDIT_TAGS)),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', PERMISSIONS::MOD_EDIT_TAGS)),
			array('permission.permission_set', array('ROLE_USER_FULL', PERMISSIONS::USE_TAGS)),
			array('permission.permission_set', array('ROLE_USER_STANDARD', PERMISSIONS::USE_TAGS)),

			array('config.add', array(PREFIXES::CONFIG.'_convert_space_to_minus', 1)),
			array('config.add', array(PREFIXES::CONFIG.'_whitelist_enabled', 0)),
			array('config.add', array(PREFIXES::CONFIG.'_whitelist', '')),
			array('config.add', array(PREFIXES::CONFIG.'_blacklist_enabled', 0)),
			array('config.add', array(PREFIXES::CONFIG.'_blacklist', '')),

			array('config.update', array(PREFIXES::CONFIG.'_version', $this->version)),
		);
	}

}

