<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\migrations;

use robertheim\topictags\prefixes;
use robertheim\topictags\permissions;

class release_0_0_9 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.9-DEV';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_8',
		);
	}

	public function update_data()
	{
		$re = array();
		// add permissions
		$re[] = array('permission.add', array(permissions::ADMIN_EDIT_TAGS));
		$re[] = array('permission.add', array(permissions::MOD_EDIT_TAGS));
		$re[] = array('permission.add', array(permissions::USE_TAGS));

		// Set permissions for the board roles
		$re = $this->add_role_permission($re, 'ROLE_ADMIN_FULL', permissions::ADMIN_EDIT_TAGS);
		$re = $this->add_role_permission($re, 'ROLE_MOD_FULL', permissions::MOD_EDIT_TAGS);
		$re = $this->add_role_permission($re, 'ROLE_MOD_STANDARD', permissions::MOD_EDIT_TAGS);
		$re = $this->add_role_permission($re, 'ROLE_USER_FULL', permissions::USE_TAGS);
		$re = $this->add_role_permission($re, 'ROLE_USER_STANDARD', permissions::USE_TAGS);

		$re[] = array('config.add', array(prefixes::CONFIG.'_convert_space_to_minus', 1));
		$re[] = array('config.add', array(prefixes::CONFIG.'_whitelist_enabled', 0));
		$re[] = array('config.add', array(prefixes::CONFIG.'_whitelist', ''));
		$re[] = array('config.add', array(prefixes::CONFIG.'_blacklist_enabled', 0));
		$re[] = array('config.add', array(prefixes::CONFIG.'_blacklist', ''));

		$re[] = array('config.update', array(prefixes::CONFIG.'_version', $this->version));

		return $re;
	}

	private function add_role_permission(array $re, $rolename, $permission)
	{
		if ($this->role_exists($rolename))
		{
			$re[] = array('permission.permission_set', array($rolename, $permission));
		}
		return $re;
	}

	/**
	 * Checks whether the given role does exist or not.
	 *
	 * @param String $role the name of the role
	 * @return true if the role exists, false otherwise.
	 */
	protected function role_exists($role)
	{
		$sql = 'SELECT role_id
			FROM ' . ACL_ROLES_TABLE . '
			WHERE ' . $this->db->sql_in_set('role_name', $role);
		$result = $this->db->sql_query_limit($sql, 1);
		$role_id = $this->db->sql_fetchfield('role_id');
		$this->db->sql_freeresult($result);

		return $role_id > 0;
	}
}
