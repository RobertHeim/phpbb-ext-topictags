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
use robertheim\topictags\TABLES;
use robertheim\topictags\service\tags_manager;

class release_0_0_9 extends \phpbb\db\migration\migration
{
	protected $version = "0.0.9-DEV";

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
			array('config.add', array(PREFIXES::CONFIG.'_convert_space_to_minus', 1)),
			array('config.update', array(PREFIXES::CONFIG.'_version', $this->version)),
		);
	}

}

