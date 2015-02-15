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

	protected function setUp()
	{
		parent::setUp();
		global $table_prefix;
		$this->db = $this->new_dbal();
		$auth = new \phpbb\auth\auth();
		$config = new \phpbb\config\config(array());
		$this->tags_manager = new \robertheim\topictags\service\tags_manager($this->db, $config, $auth, $table_prefix);
	}

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

	/**
	 * @var \robertheim\topictags\service\tags_manager
	 */
	protected $tags_manager;

	public function test_calc_count_tags()
	{
		global $table_prefix;
		$this->tags_manager->calc_count_tags();

		$result = $this->db->sql_query('SELECT count FROM ' . $table_prefix . tables::TAGS . ' WHERE id=1');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals($count, 1);
	}

	public function test_delete_unused_tags()
	{
		global $table_prefix;
		$result = $this->db->sql_query('SELECT COUNT(*) as count FROM ' . $table_prefix . tables::TAGS . ' WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals($count, 1);

		$removed_count = $this->tags_manager->delete_unused_tags();

		$this->assertEquals($removed_count, 1);

		$result = $this->db->sql_query('SELECT COUNT(*) as count FROM ' . $table_prefix . tables::TAGS . ' WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals($count, 0);
	}

}
