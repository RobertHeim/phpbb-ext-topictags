<?php
/**
 *
 * @package phpBB Extension - RH Topic Tags
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\topictags\tests\service;

use robertheim\topictags\prefixes;
use robertheim\topictags\tables;
use robertheim\topictags\service\tags_manager;

class tags_manager_test extends \phpbb_database_test_case
{

	private $auth;

	protected function setUp()
	{
		parent::setUp();
		global $table_prefix, $user;
		$this->db = $this->new_dbal();
		$this->auth = $this->getMock('\phpbb\auth\auth');
		$config = new \phpbb\config\config(array(
			prefixes::CONFIG.'_allowed_tags_regex' => '/^[a-z]{3,30}$/i',
		));
		$this->tags_manager = new \robertheim\topictags\service\tags_manager(
			$this->db, $config, $this->auth, $table_prefix);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/tags.xml');
	}

	static protected function setup_extensions()
	{
		return array(
			'robertheim/topictags'
		);
	}

	/**
	 *
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 *
	 * @var \robertheim\topictags\service\tags_manager
	 */
	protected $tags_manager;

	public function test_remove_all_tags_from_topic()
	{
		global $table_prefix;

		$topic_id = 1;

		// there is one tag assigned to the topic
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
				 tables::TOPICTAGS . '
			WHERE topic_id=' . $topic_id);
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $assigned_tags_count);

		// there exists one unused tag
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $assigned_tags_count);

		$delete_unused_tags = true;
		$this->tags_manager->remove_all_tags_from_topic($topic_id,
			$delete_unused_tags);

		// there is no tag assigned to the topic
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
				 tables::TOPICTAGS . '
			WHERE topic_id=' . $topic_id);
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $assigned_tags_count);

		// the unused tag is deleted
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $assigned_tags_count);
	}

	public function test_delete_unused_tags()
	{
		global $table_prefix;
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count);

		$removed_count = $this->tags_manager->delete_unused_tags();

		$this->assertEquals(1, $removed_count);

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);
	}

	public function test_delete_assignments_of_invalid_tags()
	{
		global $table_prefix;

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
				 tables::TOPICTAGS);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(2, $count);

		// none of the tags is valid to the configured regex [a-z]
		// so all assignments should be deleted.
		$removed_count = $this->tags_manager->delete_assignments_of_invalid_tags();
		$this->assertEquals(2, $removed_count);

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
				 tables::TOPICTAGS);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		// both tags are not assigned to any topic now
		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=1');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);
	}

	public function test_delete_assignments_where_topic_does_not_exist()
	{
		global $table_prefix;
		$none_existing_topic_id = 999;
		$result = $this->db->sql_query(
			'UPDATE ' . $table_prefix . tables::TOPICTAGS . '
			SET topic_id = ' . $none_existing_topic_id . '
			WHERE id=1');
		$removed_count = $this->tags_manager->delete_assignments_where_topic_does_not_exist();
		$this->assertEquals(1, $removed_count);
	}

	public function test_delete_tags_from_tagdisabled_forums()
	{
		$removed_count = $this->tags_manager->delete_tags_from_tagdisabled_forums(array(1));
		$this->assertEquals(0, $removed_count);
		$removed_count = $this->tags_manager->delete_tags_from_tagdisabled_forums();
		$this->assertEquals(1, $removed_count);
	}

	public function test_get_assigned_tags()
	{
		global $table_prefix;
		$topic_id = 1;
		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$this->assertEquals(array('tag1'), $tags);
		$topic_id = 2;
		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$this->assertEquals(array('tag1'), $tags);
	}

	public function test_get_tag_suggestions()
	{
		// ensure correct counts of tags
		$this->tags_manager->calc_count_tags();

		$query = "tag";
		$exclude = array(
			"tag1",
		);
		$count = 5;
		$tags = $this->tags_manager->get_tag_suggestions($query, $exclude,
			$count);
		$this->assertEquals(array(
			array(
				"text" => "tag2",
			)
		), $tags);

		$query = "tag";
		$exclude = array();
		$count = 5;
		$tags = $this->tags_manager->get_tag_suggestions($query, $exclude,
			$count);
		$this->assertEquals(
			array(
				array(
					"text" => "tag1",
				),
				array(
					"text" => "tag2",
				),
			), $tags);

		$query = "tag";
		$exclude = array();
		$count = 1;
		$tags = $this->tags_manager->get_tag_suggestions($query, $exclude,
			$count);
		$this->assertEquals(array(
			array(
				"text" => "tag1",
			)
		), $tags);

		$query = "ta";
		$exclude = array();
		$count = 5;
		$tags = $this->tags_manager->get_tag_suggestions($query, $exclude,
			$count);
		$this->assertEquals(array(), $tags);
	}

	public function test_assign_tags_to_topic()
	{
		global $table_prefix;
		$topic_id = 2;
		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$this->assertEquals(array("tag1"), $tags);

		$valid_tags = array("tag2", "tag3");
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$this->assertEquals($valid_tags, $tags);


		$valid_tags = array("tag2");
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$this->assertEquals($valid_tags, $tags);

		// tag3 must be deleted
		$sql_array = array(
			'tag' => 'tag3',
		);
		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_array));
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);
	}

	public function test_get_existing_tags()
	{
		$tags = $this->tags_manager->get_existing_tags();
		$this->assertEquals(
			array(
				array(
					"id" => 1,
					"tag" => "tag1"
				),
				array(
					"id" => 2,
					"tag" => "tag2"
				),
			)
			, $tags);

		$tags = $this->tags_manager->get_existing_tags(array(
			"tag1",
			"tag3"
		));
		$this->assertEquals(
			array(
				array(
					"id" => 1,
					"tag" => "tag1"
				),
			)
			, $tags);

		$tag_ids = $this->tags_manager->get_existing_tags(null, true);
		$this->assertEquals(array(1, 2), $tag_ids);
	}

	public function test_get_topics_by_tags()
	{
		// uses auth, so we set up the mock/stub
		// to allow reading first forum
		$this->auth->expects($this->exactly(4))
			->method('acl_getf')
			->with($this->equalTo('f_read'))
			->willReturn(array(
			1 => array(
				'f_read' => true
			)
		));

		$tags = array(
			"tag1"
		);
		$start = 0;
		$limit = 10;
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit);

		$this->assertEquals(1, sizeof($topics));
		// check that some values exist in the found topic-array
		$diff = array_diff_assoc(
			array(
				"topic_id" => 1,
				"forum_id" => 1,
				"topic_title" => "Topic1"
			), $topics[0]);
		$this->assertEquals(0, sizeof($diff));

		// case sensitive
		$tags = array(
			"tAg1"
		);
		$start = 0;
		$limit = 10;
		$mode = 'AND';
		$casesensitive = true;
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit, $mode, $casesensitive);

		$this->assertEquals(0, sizeof($topics));

		$tags = array(
			"tag1",
			"noneExistingTag"
		);
		$start = 0;
		$limit = 10;
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit);

		$this->assertEquals(0, sizeof($topics));

		// search with OR
		$tags = array(
			"tag1",
			"noneExistingTag"
		);
		$start = 0;
		$limit = 10;
		$mode = 'OR';
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit, $mode);

		$this->assertEquals(1, sizeof($topics));
		// check that some values exist in the found topic-array
		$diff = array_diff_assoc(
			array(
				"topic_id" => 1,
				"forum_id" => 1,
				"topic_title" => "Topic1"
			), $topics[0]);
		$this->assertEquals(0, sizeof($diff));
	}

	public function test_count_topics_by_tags()
	{
		$count = $this->tags_manager->count_topics_by_tags(array());
		$this->assertEquals(0, $count);

		// uses auth, so we set up the mock/stub
		// to allow reading first forum
		$this->auth->expects($this->exactly(4))
		->method('acl_getf')
		->with($this->equalTo('f_read'))
		->willReturn(array(
			1 => array(
				'f_read' => true
			)
		));

		$tags = array(
			"tag1"
		);
		$count = $this->tags_manager->count_topics_by_tags($tags);
		$this->assertEquals(1, $count);


		// case sensitive
		$tags = array(
			"tAg1"
		);
		$mode = 'AND';
		$casesensitive = true;
		$count = $this->tags_manager->count_topics_by_tags($tags, $mode, $casesensitive);
		$this->assertEquals(0, $count);

		$tags = array(
			"tag1",
			"noneExistingTag"
		);
		$count = $this->tags_manager->count_topics_by_tags($tags);

		$this->assertEquals(0, $count);

		// search with OR
		$tags = array(
			"tag1",
			"noneExistingTag"
		);
		$mode = 'OR';
		$count = $this->tags_manager->count_topics_by_tags($tags, $mode);
		$this->assertEquals(1, $count);
	}

	public function test_is_valid_tag()
	{
		$this->assertTrue($this->tags_manager->is_valid_tag("tag"));

		// clean tags must be trimed (note the trailing space)
		$this->assertFalse($this->tags_manager->is_valid_tag("tag ", true));
		$this->assertFalse($this->tags_manager->is_valid_tag("ta"), 'tag is too short');
		$this->assertFalse($this->tags_manager->is_valid_tag("abcdefghijabcdefghijabcdefghija", true), 'tag is too long');

		// will be cleaned and thereby trimmed to 30 chars
		$this->assertTrue($this->tags_manager->is_valid_tag("abcdefghijabcdefghijabcdefghija", false));

		// enable blacklist and whitelist
		global $table_prefix, $user;
		$config = new \phpbb\config\config(array(
			prefixes::CONFIG.'_allowed_tags_regex' => '/^[a-z]{3,30}$/i',
			prefixes::CONFIG.'_whitelist_enabled' => true,
			prefixes::CONFIG.'_blacklist_enabled' => true,
			prefixes::CONFIG.'_blacklist' => json_encode(array("blacktag", "blackwhitetag")),
			prefixes::CONFIG.'_whitelist' => json_encode(array("whitetag", "blackwhitetag")),
		));
		$this->tags_manager = new \robertheim\topictags\service\tags_manager(
			$this->db, $config, $this->auth, $table_prefix);

		$this->assertFalse($this->tags_manager->is_valid_tag("blacktag", true), 'tag is on blacklist');
		$this->assertFalse($this->tags_manager->is_valid_tag("notwhitetag", true), 'tag is not on whitelist');
		$this->assertFalse($this->tags_manager->is_valid_tag("blackwhitetag", true), 'blacklist must be given priority');
	}

	public function test_split_valid_tags()
	{
		$tags = array(
			'tag',
			'ta',
			'tag1',
			'validtag'
		);
		$tags = $this->tags_manager->split_valid_tags($tags);
		$this->assertEquals(
			array(
				'valid' => array(
					'tag',
					'validtag'
				),
				'invalid' => array(
					'ta',
					'tag1'
				)
			), $tags);
	}

	public function test_clean_tag()
	{
		$this->assertEquals("tag", $this->tags_manager->clean_tag(" tag "));

		// limiting the string to 30 characters will result in the last one beeing a space, which
		// must be trimed
		$this->assertEquals('abcdefghijabcdefghijabcdefghi',
			$this->tags_manager->clean_tag("abcdefghijabcdefghijabcdefghi j"));

		$this->assertEquals("t ag", $this->tags_manager->clean_tag("t ag"));

		// auto convert space to minus
		global $table_prefix, $user;
		$config = new \phpbb\config\config(array(
			prefixes::CONFIG.'_allowed_tags_regex' => '/^[a-z]{3,30}$/i',
			prefixes::CONFIG.'_convert_space_to_minus' => true,
		));
		$this->tags_manager = new \robertheim\topictags\service\tags_manager(
			$this->db, $config, $this->auth, $table_prefix);
		$this->assertEquals("t-ag", $this->tags_manager->clean_tag("t ag"));
	}

	public function test_is_tagging_enabled_in_forum()
	{
		$this->assertTrue($this->tags_manager->is_tagging_enabled_in_forum(1));
		$this->assertFalse($this->tags_manager->is_tagging_enabled_in_forum(2));
	}

	public function test_enable_tags_in_all_forums()
	{
		$affected = $this->tags_manager->enable_tags_in_all_forums();
		$this->assertEquals(1, $affected);
		$this->assertTrue($this->tags_manager->is_tagging_enabled_in_forum(1));
		$this->assertTrue($this->tags_manager->is_tagging_enabled_in_forum(2));
		$this->assertFalse($this->tags_manager->is_tagging_enabled_in_forum(3), 'forum type does not allow for tagging and hence should not be changed');
		$this->assertTrue($this->tags_manager->is_tagging_enabled_in_forum(4));
	}

	public function test_disable_tags_in_all_forums()
	{
		$affected = $this->tags_manager->disable_tags_in_all_forums();
		$this->assertEquals(1, $affected);
		$this->assertFalse($this->tags_manager->is_tagging_enabled_in_forum(1));
		$this->assertFalse($this->tags_manager->is_tagging_enabled_in_forum(2));
		$this->assertFalse($this->tags_manager->is_tagging_enabled_in_forum(3), 'forum type does not allow for tagging');
		$this->assertTrue($this->tags_manager->is_tagging_enabled_in_forum(4), 'forum type does not allow for tagging and hence should not be changed');
	}

	public function test_calc_count_tags()
	{
		global $table_prefix;

		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		$this->tags_manager->calc_count_tags();

		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=1');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count);

		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);
	}

}
