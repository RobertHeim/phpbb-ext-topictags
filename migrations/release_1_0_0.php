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

class release_1_0_0 extends \phpbb\db\migration\migration
{
	protected $version = '1.0.0';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_14',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array(prefixes::CONFIG.'_version', $this->version)),
		);
	}

}
