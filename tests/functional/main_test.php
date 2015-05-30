<?php
/**
 *
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/
namespace robertheim\topictags\tests\functional;

/**
 * @ignore
 */
use \robertheim\topictags\tests\functional\topictags_functional_test_base;
use \robertheim\topictags\prefixes;
use robertheim\topictags\tables;

/**
 * @group functional
 */
class main_test extends topictags_functional_test_base
{

	/**
	 * Tests auto prune of tags when topic is deleted
	 */
	public function test_prune_tags_when_topic_deleted()
	{
		$this->login();
		$this->admin_login();

		// enable tagging in forum used for testing
		$forum_id = 2;
		$this->enable_topictags_in_forum($forum_id);

		// create some topics to work with
		$tmp = $this->create_topic($forum_id, 'test_prune_tags_when_topic_deleted', 'test topic for test_prune_tags_when_topic_deleted');
		$topic_id = $tmp['topic_id'];
		$tmp = $this->create_topic($forum_id, 'test_prune_tags_when_topic_deleted2', 'test topic for test_prune_tags_when_topic_deleted (2)');
		$topic_id2 = $tmp['topic_id'];

		// add one tag to the first topic
		$tagname = 'tag1091723409838701993874';
		$valid_tags = array($tagname);
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		// add the same tag + another one to the second topic
		$tagname2 = 'tag210778577730498403981';
		$valid_tags = array($tagname , $tagname2);
		$this->tags_manager->assign_tags_to_topic($topic_id2, $valid_tags);

		// ensure that both tags exist
		$this->assertEquals(2, sizeof($this->tags_manager->get_existing_tags(array($tagname, $tagname2), true)));

		$this->delete_topic($topic_id2);

		global $table_prefix;
		// ensure that the tags are no longer assigned to the deleted topic
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
				 tables::TOPICTAGS . '
			WHERE topic_id=' . $topic_id2);
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(0, $assigned_tags_count);

		// ensure that the tag still is assigned the undeleted topic
		$result = $this->db->sql_query(
			'SELECT COUNT(*) as count
			FROM ' . $table_prefix .
			tables::TOPICTAGS . '
			WHERE topic_id=' . $topic_id);
		$assigned_tags_count = $this->db->sql_fetchfield('count');
		$this->assertEquals(1, $assigned_tags_count);

		// ensure that the tag that was only assigned to the deleted topic
		// has been deleted
		$existing_tags = $this->tags_manager->get_existing_tags(array($tagname2), true);
		$this->assertEquals(0, sizeof($existing_tags));

		// ensure that the tag that was assigned to both topics
		// still exists
		$existing_tags = $this->tags_manager->get_existing_tags(array($tagname), true);
		$this->assertEquals(1, sizeof($existing_tags));
	}
}
