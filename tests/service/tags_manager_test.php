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

	/** @var \phpbb\auth\auth */
	private $auth;

	/** @var \phpbb\config\db_text */
	private $config_text;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \robertheim\topictags\service\tags_manager */
	protected $tags_manager;

	protected function setUp()
	{
		parent::setUp();
		global $table_prefix;
		$this->db = $this->new_dbal();
		$this->auth = $this->getMock('\phpbb\auth\auth');
		$config = new \phpbb\config\config(array(
			prefixes::CONFIG.'_allowed_tags_regex' => '/^[a-zäÄ]{3,30}$/i',
		));
		$this->config_text = new \phpbb\config\db_text($this->db, $table_prefix . 'config_text');
		$db_helper = new \robertheim\topictags\service\db_helper($this->db);
		$this->tags_manager = new tags_manager(
			$this->db, $config, $this->config_text, $this->auth, $db_helper, $table_prefix);
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

	public function test_remove_all_tags_from_topics()
	{
		global $table_prefix;

		$topic_ids = array(1, 2);

		// there are three tag assignments to the topics,
		// but only two tags, because one is assigned to both
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
			tables::TOPICTAGS . '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids));
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(3, $assigned_tags_count);

		$delete_unused_tags = true;
		$this->tags_manager->remove_all_tags_from_topics($topic_ids,
			$delete_unused_tags);

		// there is no tag assigned to the topics
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
			tables::TOPICTAGS . '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids));
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $assigned_tags_count);

		// one tag was not assigned to any other topic and must be deleted
		// because it is not used anymore
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=1');
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $assigned_tags_count);

		// the other tag must still exist, because it is assigned to
		// another topic
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=3');
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $assigned_tags_count);

		// there was one unused tag before already, which now must
		// have been deleted
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
		$this->assertEquals(4, $count);

		// none of the tags is valid to the configured regex [a-z]
		// so all assignments should be deleted.
		$removed_count = $this->tags_manager->delete_assignments_of_invalid_tags();
		$this->assertEquals(4, $removed_count);

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
				 tables::TOPICTAGS);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		// all tags are not assigned to any topic now
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

		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=3');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		// remove all tags
		$this->tags_manager->delete_unused_tags();
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);
		// all tags are valid
		$removed_count = $this->tags_manager->delete_assignments_of_invalid_tags();
		$this->assertEquals(0, $removed_count);
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

		// nothing to do now
		$removed_count = $this->tags_manager->delete_assignments_where_topic_does_not_exist();
		$this->assertEquals(0, $removed_count);
	}

	public function test_delete_tags_from_tagdisabled_forums()
	{
		$removed_count = $this->tags_manager->delete_tags_from_tagdisabled_forums(array());
		$this->assertEquals(0, $removed_count);
		$removed_count = $this->tags_manager->delete_tags_from_tagdisabled_forums(array(1));
		$this->assertEquals(0, $removed_count);
		$removed_count = $this->tags_manager->delete_tags_from_tagdisabled_forums();
		$this->assertEquals(2, $removed_count);
	}

	public function test_get_assigned_tags()
	{
		global $table_prefix;
		$topic_id = 1;
		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$this->assertEquals(array('tag1'), $tags);
		$topic_id = 2;
		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		sort($tags);
		$this->assertEquals(array('anothertag3', 'tag1'), $tags);
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
		sort($tags);
		$this->assertEquals(array('anothertag3', 'tag1'), $tags);

		$valid_tags = array('tag2', 'tag3');
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		sort($tags);
		$this->assertEquals($valid_tags, $tags);

		$valid_tags = array('tag2');
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$this->assertEquals($valid_tags, $tags);

		// tag3 must be deleted
		$sql_array = array(
			'tag' => 'tag3',
		);
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_array));
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);
	}

	public function test_get_existing_tags()
	{
		$tags = $this->tags_manager->get_existing_tags(array());
		$this->assertEquals(array(), $tags);
		$tags = $this->tags_manager->get_existing_tags();
		usort($tags,
			function ($a, $b)
			{
				return $a['id'] - $b['id'];
			});
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
				array(
					"id" => 3,
					"tag" => "anothertag3"
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
		sort($tag_ids);
		$this->assertEquals(array(1, 2, 3), $tag_ids);
	}

	public function test_get_topics_by_tags()
	{
		// uses auth, so we set up the mock/stub
		// to allow reading first forum
		$this->auth->expects($this->exactly(6))
			->method('acl_getf')
			->with($this->equalTo('f_read'))
			->willReturn(array(
			1 => array(
				'f_read' => true
			)
		));

		$tags = array();
		$start = 0;
		$limit = 10;
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit);
		$this->assertEquals(0, sizeof($topics));

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

		// case sensitive + utf8
		$topic_id = 4;
		$tags = array(
			"tÄäg"
		);
		$this->tags_manager->assign_tags_to_topic($topic_id, $tags);
		$start = 0;
		$limit = 10;
		$mode = 'AND';
		$casesensitive = true;
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit, $mode, $casesensitive);

		$this->assertEquals(1, sizeof($topics));
		$this->assertEquals($topic_id, $topics[0]['topic_id']);

		// case insensitive + utf8
		$topic_id = 4;
		$tags = array(
			"tÄäg"
		);
		$this->tags_manager->assign_tags_to_topic($topic_id, $tags);
		$tags = array(
			// note that the case is different now
			"täÄg"
		);
		$start = 0;
		$limit = 10;
		$mode = 'AND';
		$casesensitive = false;
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit, $mode, $casesensitive);

		$this->assertEquals(1, sizeof($topics));
		$this->assertEquals($topic_id, $topics[0]['topic_id']);

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

	public function test_get_topics_by_tags2()
	{
		// test if no forums are readable
		$this->auth->expects($this->once())
		->method('acl_getf')
		->with($this->equalTo('f_read'))
		->willReturn(array());
		$tags = array(
			"tag1",
			"noneExistingTag"
		);
		$start = 0;
		$limit = 10;
		$mode = 'OR';
		$topics = $this->tags_manager->get_topics_by_tags($tags, $start, $limit, $mode);
		$this->assertEquals(0, sizeof($topics));
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
		global $table_prefix;
		$this->assertTrue($this->tags_manager->is_valid_tag("tag"));

		// clean tags must be trimed (note the trailing space)
		$this->assertFalse($this->tags_manager->is_valid_tag("tag ", true));
		$this->assertFalse($this->tags_manager->is_valid_tag("ta"), 'tag is too short');
		$this->assertFalse($this->tags_manager->is_valid_tag("abcdefghijabcdefghijabcdefghija", true), 'tag is too long');

		// will be cleaned and thereby trimmed to 30 chars
		$this->assertTrue($this->tags_manager->is_valid_tag("abcdefghijabcdefghijabcdefghija", false));

		// enable blacklist and whitelist
		global $table_prefix;
		$config = new \phpbb\config\config(array(
			prefixes::CONFIG.'_allowed_tags_regex' => '/^[a-z]{3,30}$/i',
			prefixes::CONFIG.'_whitelist_enabled' => true,
			prefixes::CONFIG.'_blacklist_enabled' => true,
		));
		$this->config_text->set_array(array(
			prefixes::CONFIG.'_blacklist' => json_encode(array("blacktag", "blackwhitetag")),
			prefixes::CONFIG.'_whitelist' => json_encode(array("whitetag", "blackwhitetag")),
		));
		$db_helper = new \robertheim\topictags\service\db_helper($this->db);
		$this->tags_manager = new tags_manager(
			$this->db, $config, $this->config_text, $this->auth, $db_helper, $table_prefix);

		$this->assertFalse($this->tags_manager->is_valid_tag("blacktag", true), 'tag is on blacklist');
		$this->assertFalse($this->tags_manager->is_valid_tag("notwhitetag", true), 'tag is not on whitelist');
		$this->assertTrue($this->tags_manager->is_valid_tag("whitetag", true), 'tag is not blacklisted and on whitelist');
		$this->assertFalse($this->tags_manager->is_valid_tag("blackwhitetag", true), 'blacklist must be given priority');
		$this->config_text->delete_array(array(
			prefixes::CONFIG.'_blacklist',
			prefixes::CONFIG.'_whitelist',
		));
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
		global $table_prefix;
		$config = new \phpbb\config\config(array(
			prefixes::CONFIG.'_allowed_tags_regex' => '/^[a-z]{3,30}$/i',
			prefixes::CONFIG.'_convert_space_to_minus' => true,
		));
		$db_helper = new \robertheim\topictags\service\db_helper($this->db);
		$this->tags_manager = new tags_manager(
			$this->db, $config, $this->config_text, $this->auth, $db_helper, $table_prefix);

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

	public function test_is_enabled_in_all_forums()
	{
		$this->assertFalse($this->tags_manager->is_enabled_in_all_forums());
		$this->tags_manager->enable_tags_in_all_forums();
		$this->assertTrue($this->tags_manager->is_enabled_in_all_forums());
	}

	public function test_is_disabled_in_all_forums()
	{
		$this->assertFalse($this->tags_manager->is_disabled_in_all_forums());
		$this->tags_manager->disable_tags_in_all_forums();
		$this->assertTrue($this->tags_manager->is_disabled_in_all_forums());
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

	public function test_merge()
	{
		global $table_prefix;
		// uses auth, so we set up the mock/stub
		// to allow reading first forum
		$this->auth->expects($this->exactly(2))
			->method('acl_getf')
			->with($this->equalTo('f_read'))
			->willReturn(array(
			1 => array(
				'f_read' => true
			)
		));

		$tag_to_delete = "tag1";
		$tag_to_delete_id = 1;
		$tag_to_keep = "tag2";
		$tag_to_keep_id = 2;

		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=' . $tag_to_keep_id);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		// 2 assignments but only 1 is readable
		$count_of_assignments = $this->tags_manager->merge($tag_to_delete_id,
				$tag_to_keep, $tag_to_keep_id);
		$this->assertEquals(1, $count_of_assignments);
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TOPICTAGS . '
			WHERE tag_id=' . $tag_to_keep_id);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(2, $count);

		// tag1 must be deleted
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=' . $tag_to_delete_id);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		// tag2 must be assignet to both topics
		// but only counted once because of tagging disabled forum
		$result = $this->db->sql_query(
			'SELECT count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=' . $tag_to_keep_id);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count);

		$result = $this->db->sql_query(
			'SELECT topic_id
			FROM ' . $table_prefix . tables::TOPICTAGS . '
			WHERE tag_id=' . $tag_to_keep_id);
		$topics = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topics[] = $row['topic_id'];
		}
		$this->assertEquals(array(
			1,
			2
		), $topics);

		// test if keep-tag is already assigned to topics that the deleted one is assigned to
		$tag_to_delete = "tag2";
		$tag_to_delete_id = 2;
		$tag_to_keep = "anothertag3";
		$tag_to_keep_id = 3;

		// ensure test setup (tag2 must be assigned because of the merge before)
		$tags = $this->tags_manager->get_assigned_tags(2);
		sort($tags);
		$this->assertEquals(array('anothertag3', 'tag2'), $tags);

		// 3 assignments, but only 2 are valid
		$count_of_assignments = $this->tags_manager->merge($tag_to_delete_id,
			$tag_to_keep, $tag_to_keep_id);
		$this->assertEquals(2, $count_of_assignments);
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TOPICTAGS . '
			WHERE tag_id=' . $tag_to_keep_id);
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(3, $count);

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TOPICTAGS . '
			WHERE tag_id=' . $tag_to_keep_id . ' AND topic_id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count, 'the topic must not be assigned twice');
		$tags = $this->tags_manager->get_assigned_tags(2);
		$this->assertEquals(array('anothertag3'), $tags);
	}

	public function test_delete_tag()
	{
		global $table_prefix;

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=1');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count);

		$this->tags_manager->delete_tag(1);

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=1');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count);

		$this->tags_manager->delete_tag(2);

		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE id=2');
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);
	}

	public function test_rename()
	{
		global $table_prefix;
		// uses auth, so we set up the mock/stub
		// to allow reading first forum
		$this->auth->expects($this->once())
		->method('acl_getf')
		->with($this->equalTo('f_read'))
		->willReturn(array(
			1 => array(
				'f_read' => true
			)
		));

		$sql_array = array(
			'tag' => 'tag1',
		);
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_array));
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count);

		$sql_array = array(
			'tag' => 'newtagname',
		);
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_array));
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		$tag_id = 1;
		$new_name_clean = "newtagname";
		$assigned_count = $this->tags_manager->rename($tag_id, $new_name_clean);
		$this->assertEquals(1, $assigned_count);

		$sql_array = array(
			'tag' => 'tag1',
		);
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_array));
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $count);

		$sql_array = array(
			'tag' => 'newtagname',
		);
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix . tables::TAGS . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_array));
		$count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $count);
	}

	public function test_get_tag_by_id()
	{
		$tag = $this->tags_manager->get_tag_by_id(1);
		$this->assertEquals('tag1', $tag);

		$tag = $this->tags_manager->get_tag_by_id(2);
		$this->assertEquals('tag2', $tag);
	}

	public function test_get_all_tags()
	{
		$start = 0;
		$limit = 1;
		$sort_field = 'tag';
		$asc = true;
		$tags = $this->tags_manager->get_all_tags($start, $limit, $sort_field,
			$asc);
		$this->assertEquals(
			array(
				array(
					'id' => 3,
					'tag' => 'anothertag3',
					'tag_lowercase' => 'anothertag3',
					'count' => 0
				)
			), $tags);

		$start = 0;
		$limit = 1;
		$sort_field = 'tag';
		$asc = false;
		$tags = $this->tags_manager->get_all_tags($start, $limit, $sort_field,
			$asc);
		$this->assertEquals(
			array(
				array(
					'id' => 2,
					'tag' => 'tag2',
					'tag_lowercase' => 'tag2',
					'count' => 0
				)
			), $tags);

		$start = 1;
		$limit = 1;
		$sort_field = 'tag';
		$asc = true;
		$tags = $this->tags_manager->get_all_tags($start, $limit, $sort_field,
			$asc);
		$this->assertEquals(
			array(
				array(
					'id' => 1,
					'tag' => 'tag1',
					'tag_lowercase' => 'tag1',
					'count' => 0
				)
			), $tags);

		// ensure proper counts
		$this->tags_manager->calc_count_tags();

		$start = 0;
		$limit = 2;
		$sort_field = 'count';
		$asc = true;
		$tags = $this->tags_manager->get_all_tags($start, $limit, $sort_field,
			$asc);
		$this->assertEquals(
			array(
				array(
					'id' => 2,
					'tag' => 'tag2',
					'tag_lowercase' => 'tag2',
					'count' => 0
				),
				array(
					'id' => 1,
					'tag' => 'tag1',
					'tag_lowercase' => 'tag1',
					'count' => 1
				)
			), $tags);
	}

	public function test_count_tags()
	{
		$count = $this->tags_manager->count_tags();
		$this->assertEquals(3, $count);
		$this->tags_manager->delete_tag(1);
		$count = $this->tags_manager->count_tags();
		$this->assertEquals(2, $count);
	}
}
