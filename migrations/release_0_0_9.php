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
		$re = array();
		// add permissions
		$re[] = array('permission.add', array(PERMISSIONS::ADMIN_EDIT_TAGS));
		$re[] = array('permission.add', array(PERMISSIONS::MOD_EDIT_TAGS));
		$re[] = array('permission.add', array(PERMISSIONS::USE_TAGS));
		
		// Set permissions for the board roles
		if ($this->role_exists('ROLE_ADMIN_FULL')) {
			$re[] = array('permission.permission_set', array('ROLE_ADMIN_FULL', PERMISSIONS::ADMIN_EDIT_TAGS));
		}
		if ($this->role_exists('ROLE_MOD_FULL')) {
			$re[] = array('permission.permission_set', array('ROLE_MOD_FULL', PERMISSIONS::MOD_EDIT_TAGS));
		}
		if ($this->role_exists('ROLE_MOD_STANDARD')) {
			$re[] = array('permission.permission_set', array('ROLE_MOD_STANDARD', PERMISSIONS::MOD_EDIT_TAGS));
		}
		if ($this->role_exists('ROLE_USER_FULL')) {
			$re[] = array('permission.permission_set', array('ROLE_USER_FULL', PERMISSIONS::USE_TAGS));
		}
		if ($this->role_exists('ROLE_USER_STANDARD')) {
			$re[] = array('permission.permission_set', array('ROLE_USER_STANDARD', PERMISSIONS::USE_TAGS));
		}

		$re[] = array('config.add', array(PREFIXES::CONFIG.'_convert_space_to_minus', 1));
		$re[] = array('config.add', array(PREFIXES::CONFIG.'_whitelist_enabled', 0));
		$re[] = array('config.add', array(PREFIXES::CONFIG.'_whitelist', ''));
		$re[] = array('config.add', array(PREFIXES::CONFIG.'_blacklist_enabled', 0));
		$re[] = array('config.add', array(PREFIXES::CONFIG.'_blacklist', ''));
		
		$re[] = array('config.update', array(PREFIXES::CONFIG.'_version', $this->version));
				
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

