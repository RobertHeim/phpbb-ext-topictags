<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\tests\service;

use robertheim\topictags\tables;

class tags_manager_test extends \phpbb_database_test_case
{

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/tags.xml');
	}

	static protected function setup_extensions()
	{
		return array('robertheim/topictags');
	}

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	private function get_tags_manager()
	{
		global $table_prefix;
		$auth = new \phpbb\auth\auth();
		$config = new \phpbb\config\config(array());
		$this->db = $this->new_dbal();
		$tags_manager = new \robertheim\topictags\service\tags_manager($this->db, $config, $auth, $table_prefix);
		return $tags_manager;
	}

	public function test_calc_count_tags()
	{
		$tags_manager = $this->get_tags_manager();
		$tags_manager->calc_count_tags();

		$result = $this->db->sql_query('SELECT count FROM ' . $table_prefix . tables::TAGS . ' WHERE id=1');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals($count, 1);
	}

	public function test_delete_unused_tags()
	{
		$result = $this->db->sql_query('SELECT COUNT(*) as count FROM ' . $table_prefix . tables::TAGS . ' WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals($count, 1);

		$tags_manager = $this->get_tags_manager();
		$tags_manager->delete_unused_tags();
		$this->assertEquals($count, 0);

	}
}
