<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\migrations;

use robertheim\topictags\tables;
use robertheim\topictags\prefixes;

class release_0_0_1 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.1-DEV';

	protected $config_prefix = prefixes::CONFIG;

	public function effectively_installed()
	{
		$installed_version = $this->config[$this->config_prefix.'_version'];
		return isset($installed_version) && version_compare($installed_version, $this->version, '>=');
	}

	public static function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . tables::TOPICTAGS	=> array(
					'COLUMNS'		=> array(
						'id'			=> array('UINT', null, 'auto_increment'),
						'topic_id'		=> array('UINT', 0),
						'tag_id'		=> array('UINT', 1),
					),
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> array(
						'idx_topic'		=> array('INDEX', array('topic_id')),
						'idx_tag'		=> array('INDEX', array('tag_id')),
					),
				),
				$this->table_prefix . tables::TAGS => array(
					'COLUMNS'		=> array(
						'id'			=> array('UINT', null, 'auto_increment'),
						'tag'			=> array('VCHAR:30', ''),
					),
					'PRIMARY_KEY'	=> 'id',
					'KEYS'			=> array(
						'unq_tag'		=> array('UNIQUE', array('tag')),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'    => array(
				$this->table_prefix . tables::TOPICTAGS,
				$this->table_prefix . tables::TAGS,
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array($this->config_prefix.'_version', $this->version)),
		);
	}
}
