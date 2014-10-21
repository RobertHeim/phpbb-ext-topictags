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

class release_0_0_11 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.11-DEV';

	public function effectively_installed()
	{
		return version_compare($this->config[PREFIXES::CONFIG.'_version'], $this->version, '>=');
	}

	static public function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_10',
		);
	}

	public function update_data()
	{
		global $config;

		// convert whitelist to json
		$whitelist = $config[PREFIXES::CONFIG.'_whitelist'];
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
		$blacklist = $config[PREFIXES::CONFIG.'_blacklist'];
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
			array('config.update', array(PREFIXES::CONFIG.'_whitelist', $whitelist)),
			array('config.update', array(PREFIXES::CONFIG.'_blacklist', $blacklist)),
			array('module.add', array(
				'acp', 'ACP_TOPICTAGS_TITLE', array(
					'module_basename'	=> '\robertheim\topictags\acp\topictags_module',
					'auth'				=> 'ext_robertheim/topictags && acl_a_board',
					'modes'				=> array('whitelist', 'blacklist'),
				))),
			array('config.update', array(PREFIXES::CONFIG.'_version', $this->version)),
		);
	}
}

