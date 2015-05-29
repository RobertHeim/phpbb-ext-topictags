<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/
namespace robertheim\topictags\tests\functional\acp;

/**
 * @ignore
 */
use \robertheim\topictags\tests\functional\topictags_functional_test_base;
use \robertheim\topictags\prefixes;

/**
 * @group functional
 */
class manage_tags_controller_test extends topictags_functional_test_base
{

	/**
	 * Tests changing tags in ACP manage tags.
	 */
	public function test_handle_edit()
	{
		// == setup specific to this test ==

		$this->login();
		$this->admin_login();

		// enable tagging in forum used for testing
		$forum_id = 2;
		$this->enable_topictags_in_forum($forum_id);

		$this->auth->expects($this->exactly(4))
		->method('acl_getf')
		->with($this->equalTo('f_read'))
		->willReturn(array(
				$forum_id => array(
						'f_read' => true
				)
		));

		// create some topics to work with
		$tmp = $this->create_topic($forum_id, 'tag_edit_functional_test', 'test topic for editing tag');
		$topic_id = $tmp['topic_id'];
		$tmp = $this->create_topic($forum_id, 'tag_edit_functional_test2', 'test topic for editing tag (2)');
		$topic_id2 = $tmp['topic_id'];

		// add tags
		$tagname = 'tag1091723409837401993874';
		$valid_tags = array($tagname);
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		$tagname2 = 'tag210739481730498403981';
		$valid_tags = array($tagname2);
		$this->tags_manager->assign_tags_to_topic($topic_id2, $valid_tags);

		$this->assertEquals(2, sizeof($this->tags_manager->get_existing_tags(array($tagname, $tagname2), true)));

		// ensure both tags exist
		$crawler = $this->goto_manage_tags_page();
		$this->assertGreaterThanOrEqual(2, $crawler->filter('.topictags_editable_tag')->count());

		$displayed_tags = $crawler->filter('.topictags_editable_tag')->each(
				function (\Symfony\Component\DomCrawler\Crawler $node, $i)
				{
					return $node->text();
				}
		);
		$this->assertContains($tagname, $displayed_tags);
		$this->assertContains($tagname2, $displayed_tags);

		// == actual tests ==

		// empty old_tag_name
		$tagname_new = 'tagnew';
		$url = "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&action=edit&sid={$this->sid}";
		$params = array(
				'old_tag_name' => base64_encode(rawurlencode('')),
				'new_tag_name' => base64_encode(rawurlencode($tagname_new))
		);

		$response = $this->ajax($url, $params);
		$this->assertEquals(false, $response['success']);
		$this->assertEquals($this->lang('TOPICTAGS_MISSING_TAG_NAMES', $tagname_new), rawurldecode(base64_decode($response['error_msg'])));

		// empty new_tag_name
		$tagname_new = '';
		$url = "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&action=edit&sid={$this->sid}";
		$params = array(
				'old_tag_name' => base64_encode(rawurlencode($tagname)),
				'new_tag_name' => base64_encode(rawurlencode($tagname_new))
		);

		$response = $this->ajax($url, $params);
		$this->assertEquals(false, $response['success']);
		$this->assertEquals($this->lang('TOPICTAGS_MISSING_TAG_NAMES', $tagname_new), rawurldecode(base64_decode($response['error_msg'])));

		// old tag does not exist
		$tagname_new = 'tagnew';
		$url = "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&action=edit&sid={$this->sid}";
		$params = array(
				'old_tag_name' => base64_encode(rawurlencode('not_existing_tag')),
				'new_tag_name' => base64_encode(rawurlencode($tagname_new))
		);

		$response = $this->ajax($url, $params);
		$this->assertEquals(false, $response['success']);
		$this->assertEquals($this->lang('TOPICTAGS_TAG_DOES_NOT_EXIST', 'not_existing_tag'), rawurldecode(base64_decode($response['error_msg'])));

		// invalid tag name
		$tagname_new = 'tag_invalid';
		$url = "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&action=edit&sid={$this->sid}";
		$params = array(
			'old_tag_name' => base64_encode(rawurlencode($tagname)),
			'new_tag_name' => base64_encode(rawurlencode($tagname_new))
		);

		$response = $this->ajax($url, $params);
		$this->assertEquals(false, $response['success']);
		$this->assertEquals($this->lang('TOPICTAGS_TAG_INVALID', $tagname_new), rawurldecode(base64_decode($response['error_msg'])));

		// same tagname
		$tagname_new = $tagname;
		$url = "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&action=edit&sid={$this->sid}";
		$params = array(
				'old_tag_name' => base64_encode(rawurlencode($tagname)),
				'new_tag_name' => base64_encode(rawurlencode($tagname_new))
		);

		$response = $this->ajax($url, $params);
		$this->assertEquals(false, $response['success']);
		$this->assertEquals($this->lang('TOPICTAGS_NO_MODIFICATION', $tagname_new), rawurldecode(base64_decode($response['error_msg'])));

		// new tagname already exists -> must be merged
		$tagname_new = $tagname2;
		$url = "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&action=edit&sid={$this->sid}";
		$params = array(
				'old_tag_name' => base64_encode(rawurlencode($tagname)),
				'new_tag_name' => base64_encode(rawurlencode($tagname_new))
		);
		$response = $this->ajax($url, $params);
		$this->assertEquals(true, $response['success']);
		$this->assertEquals(true, $response['merged']);
		$this->assertEquals($this->lang('TOPICTAGS_TAG_MERGED', $tagname_new), rawurldecode(base64_decode($response['msg'])));
		$this->assertEquals(2, $response['new_tag_count'], 'The tag must be assigned to two topics.');
		$existing_tags = $this->tags_manager->get_existing_tags(array($tagname, $tagname2));
		$this->assertEquals(1, sizeof($existing_tags), 'One of the test\'s tags must exist.');
		$this->assertEquals($tagname2, $existing_tags[0]['tag'], 'The 2nd of the test\'s tags must exist.');
		$topics = $this->tags_manager->get_topics_by_tags(array($tagname), 0, 2);
		$this->assertEquals(0, sizeof($topics), 'There should be no topics assigned to the tag.');
		$topics = $this->tags_manager->get_topics_by_tags(array($tagname2), 0, 2);
		$this->assertEquals(2, sizeof($topics), 'There should be two topics assigned to the tag.');

		// new tag does not yet exist -> rename tag
		$tagname_new = 'tagvalid';
		$url = "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&action=edit&sid={$this->sid}";
		$params = array(
				// tagname has been renamed to tagname2 before
				'old_tag_name' => base64_encode(rawurlencode($tagname2)),
				'new_tag_name' => base64_encode(rawurlencode($tagname_new))
		);
		$response = $this->ajax($url, $params);
		$this->assertEquals(true, $response['success']);
		$this->assertEquals($this->lang('TOPICTAGS_TAG_CHANGED'), rawurldecode(base64_decode($response['msg'])));
		$existing_tags = $this->tags_manager->get_existing_tags(array($tagname2, $tagname_new));
		$this->assertEquals(1, sizeof($existing_tags), 'One of the test\'s tags must exist.');
		$this->assertEquals($tagname_new, $existing_tags[0]['tag'], 'The 2nd of the test\'s tags must exist.');
		$topics = $this->tags_manager->get_topics_by_tags(array($tagname2), 0, 2);
		$this->assertEquals(0, sizeof($topics), 'There should be no topics assigned to the tag.');
		$topics = $this->tags_manager->get_topics_by_tags(array($tagname_new), 0, 2);
		$this->assertEquals(2, sizeof($topics), 'There should be two topics assigned to the tag.');

		// == cleanup ==

		// delete the created tags
		$existing_tags = $this->tags_manager->get_existing_tags(array($tagname, $tagname2, $tagname_new));
		foreach ($existing_tags as $tag)
		{
			$this->tags_manager->delete_tag($tag['id']);
		}

		// delete the created topics
		$this->delete_topic($topic_id);
		$this->delete_topic($topic_id2);
	}
}
