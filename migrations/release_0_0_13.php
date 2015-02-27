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
use robertheim\topictags\tables;

class release_0_0_13 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.13-b1';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_12',
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . tables::TAGS	=> array(
					'tag_lowercase'	=> array('VCHAR:30', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . tables::TAGS	=> array(
					'tag_lowercase',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'calculate_lowercase_tags'))),
			array('config.update', array(prefixes::CONFIG.'_version', $this->version)),
		);
	}

	public function calculate_lowercase_tags()
	{
		$sql = 'SELECT id, tag
			FROM ' . $this->table_prefix . tables::TAGS;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'tag_lowercase' =>utf8_strtolower($row['tag']),
			);
			$sql = 'UPDATE ' . $this->table_prefix . tables::TAGS . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE id = ' . $row['id'];
			$this->db->sql_query($sql);
		}

		$this->db->sql_freeresult($result);
	}
}
