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
use robertheim\topictags\service\tags_manager;
use robertheim\topictags\service\db_helper;

class release_0_0_8 extends \phpbb\db\migration\container_aware_migration
{
	protected $version = '0.0.8-DEV';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_7',
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . tables::TAGS	=> array(
					'count'	=> array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . tables::TAGS	=> array(
					'count',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'calc_count_tags'))),
			array('config.add', array(prefixes::CONFIG.'_display_tagcloud_on_index', 1)),
			array('config.add', array(prefixes::CONFIG.'_max_tags_in_tagcloud', 20)),
			array('config.add', array(prefixes::CONFIG.'_display_tagcount_in_tagcloud', 1)),
			array('config.update', array(prefixes::CONFIG.'_version', $this->version)),
		);
	}

	public function revert_data()
	{
		// nothing to do, because tags field is deleted anyway.
		return array();
	}

	public function calc_count_tags()
	{
		/* @var $auth \phpbb\auth\auth */
		$auth = $this->container->get('auth');
		$db_helper = new db_helper($this->db);
		$config_text = $this->container->get('config_text');
		$tags_manager = new tags_manager($this->db, $this->config, $config_text, $auth, $db_helper, $this->table_prefix);
		$tags_manager->calc_count_tags();
	}
}
