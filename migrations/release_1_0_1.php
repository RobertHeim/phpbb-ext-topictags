<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2015 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\migrations;

use robertheim\topictags\prefixes;

class release_1_0_1 extends \phpbb\db\migration\migration
{
	protected $version = '1.0.1';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_1_0_0',
		);
	}


	public function update_data()
	{
		global $config;
		// put white- and blacklist from config to config_text
		$whitelist = $config[prefixes::CONFIG.'_whitelist'];
		$blacklist = $config[prefixes::CONFIG.'_blacklist'];
		return array(
			array('config.remove', array(prefixes::CONFIG.'_whitelist')),
			array('config.remove', array(prefixes::CONFIG.'_blacklist')),
			array('config_text.add', array(prefixes::CONFIG.'_whitelist', $whitelist)),
			array('config_text.add', array(prefixes::CONFIG.'_blacklist', $blacklist)),
			array('config.update', array(prefixes::CONFIG.'_version', $this->version)),
		);
	}

}
