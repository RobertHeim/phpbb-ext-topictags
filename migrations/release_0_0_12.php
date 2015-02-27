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

class release_0_0_12 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.12-BETA';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_11',
		);
	}

	public function update_data()
	{
		global $config;

		// convert whitelist to json
		$whitelist = $config[prefixes::CONFIG.'_whitelist'];
		if (empty($whitelist))
		{
			$whitelist = array();
		}
		else
		{
			$whitelist = explode(',', $whitelist);
		}
		$whitelist = json_encode($whitelist);

		// convert blacklist to json
		$blacklist = $config[prefixes::CONFIG.'_blacklist'];
		if (empty($blacklist))
		{
			$blacklist = array();
		}
		else
		{
			$blacklist = explode(',', $blacklist);
		}
		$blacklist = json_encode($blacklist);

		return array(
			array('config.update', array(prefixes::CONFIG.'_whitelist', $whitelist)),
			array('config.update', array(prefixes::CONFIG.'_blacklist', $blacklist)),
			array('module.add', array(
				'acp', 'ACP_TOPICTAGS_TITLE', array(
					'module_basename'	=> '\robertheim\topictags\acp\topictags_module',
					'auth'				=> 'ext_robertheim/topictags && acl_a_board',
					'modes'				=> array('whitelist', 'blacklist', 'tags'),
				))),
			array('config.update', array(prefixes::CONFIG.'_version', $this->version)),
		);
	}
}
