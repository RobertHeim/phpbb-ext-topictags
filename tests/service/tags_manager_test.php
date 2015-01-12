<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\tests\service;

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
		$this->db = $this->new_dbal();
		$tags_manager = new \robertheim\topictags\service\tags_manager($this->db, null, null, 'phpbb_');
		$tags_manager->calc_count_tags();
		$result = $db->sql_query('SELECT count FROM phpbb_rh_topictags_tags WHERE id=1');
		$count = $db->sql_fetchfield('count');
		$this->assertEquals($count, 1);
	}
}