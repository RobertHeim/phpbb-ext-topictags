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

class release_0_0_2 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.2-DEV';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_1',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array(prefixes::CONFIG.'_version', $this->version)),
		);
	}
}
