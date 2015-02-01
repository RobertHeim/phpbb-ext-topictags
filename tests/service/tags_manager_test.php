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

	public function test_calc_count_tags()
	{
		global $table_prefix;
		$auth = new \phpbb\auth\auth();
		$config = new \phpbb\config\config(array());
		$this->db = $this->new_dbal();
		$tags_manager = new \robertheim\topictags\service\tags_manager($this->db, $config, $auth, $table_prefix);
		$tags_manager->calc_count_tags();

		$result = $this->db->sql_query('SELECT count FROM ' . $table_prefix . tables::TAGS . ' WHERE id=1');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals($count, 1);
	}
}
