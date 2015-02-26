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
class settings_test extends topictags_functional_test_base
{

	/**
	 * Test ACP module appears
	 */
	public function test_acp_module()
	{
		$this->login();
		$this->admin_login();
		$crawler = $this->goto_settings_page();
		// Assert Pages module appears in sidebar
		$this->assertContainsLang('ACP_TOPICTAGS_TITLE', $crawler->filter('.menu-block')->text());
		$this->assertContainsLang('ACP_TOPICTAGS_SETTINGS', $crawler->filter('.menu-block')->text());
		$this->assertContainsLang('ACP_TOPICTAGS_WHITELIST', $crawler->filter('.menu-block')->text());
		$this->assertContainsLang('ACP_TOPICTAGS_BLACKLIST', $crawler->filter('.menu-block')->text());
		$this->assertContainsLang('ACP_TOPICTAGS_MANAGE_TAGS', $crawler->filter('.menu-block')->text());
	}

	/**
	 * Tests en-/disabling tagging in forum settings as well as en-/disabling tagging
	 * in all at once via the extensions settings page.
	 */
	public function test_enable_disable_in_forum()
	{
		$this->add_lang('acp/forums');

		$this->login();
		$this->admin_login();

		// disable tags in forum
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&icat=7&mode=manage&parent_id=1&f=2&action=edit&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get('rh_topictags_enabled');
		$field->setValue(0);
		$crawler = $this->submit($form);
		$this->assertContainsLang('FORUM_UPDATED', $crawler->text());
		// must be disabled in all forums
		$crawler = $this->goto_settings_page();
		$this->assertContains($this->lang('TOPICTAGS_DISABLE_IN_ALL_FORUMS_ALREADY'), $crawler->text());

		// enable tags in forum
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&icat=7&mode=manage&parent_id=1&f=2&action=edit&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get('rh_topictags_enabled');
		$field->setValue(1);
		$crawler = $this->submit($form);
		$this->assertContainsLang('FORUM_UPDATED', $crawler->text());
		// must be enabled in all forums
		$crawler = $this->goto_settings_page();
		$this->assertContains($this->lang('TOPICTAGS_ENABLE_IN_ALL_FORUMS_ALREADY'), $crawler->text());

		// disable in all forums
		$crawler = $this->goto_settings_page();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get(prefixes::CONFIG . '_disable_in_all_forums');
		$this->assertEquals('0', $field->getValue());
		$field->setValue(1);
		$crawler = $this->submit($form);
		$this->assertContains(sprintf($this->lang['TOPICTAGS_DISABLE_IN_ALL_FORUMS_DONE'][1], 1), $crawler->text());
		// must be disabled in all forums
		$crawler = $this->goto_settings_page();
		$this->assertContains($this->lang('TOPICTAGS_DISABLE_IN_ALL_FORUMS_ALREADY'), $crawler->text());
		// must be disabled in forum
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&icat=7&mode=manage&parent_id=1&f=2&action=edit&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get('rh_topictags_enabled');
		$this->assertEquals('0', $field->getValue());

		// enable in all forums
		$crawler = $this->goto_settings_page();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get(prefixes::CONFIG . '_enable_in_all_forums');
		$this->assertEquals('0', $field->getValue());
		$field->setValue(1);
		$crawler = $this->submit($form);
		$this->assertContains(sprintf($this->lang['TOPICTAGS_ENABLE_IN_ALL_FORUMS_DONE'][1], 1), $crawler->text());
		// must be enabled in all forums
		$crawler = $this->goto_settings_page();
		$this->assertContains($this->lang('TOPICTAGS_ENABLE_IN_ALL_FORUMS_ALREADY'), $crawler->text());
		// must be enabled in forum
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&icat=7&mode=manage&parent_id=1&f=2&action=edit&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get('rh_topictags_enabled');
		$this->assertEquals('1', $field->getValue());

	}

	public function test_display_tags_in_viewforum()
	{
		// == test specific setup ==

		$this->login();
		$this->admin_login();

		// enable tagging in forum used for testing
		$forum_id = 2;
		$this->enable_topictags_in_forum($forum_id);

		// create a topic to work with
		$tmp = $this->create_topic($forum_id, 'display_tags_in_viewforum_functional_test', 'test topic');
		$topic_id = $tmp['topic_id'];

		// add tag
		$tagname = 'tag19849817435928751';
		$valid_tags = array($tagname);
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		// == actual tests ==

		// disable
		$this->set_topictags_setting('_display_tags_in_viewforum', 0);

		// must not be shown
		$crawler = $this->request('GET', "viewforum.php?f=$forum_id");
		$this->assertNotContains($tagname, $crawler->text());
		$this->assertEquals(0, $crawler->filter('.rh_tag:contains("' . $tagname . '")')->count());

		// enable
		$this->set_topictags_setting('_display_tags_in_viewforum', 1);

		// must be shown
		$crawler = $this->request('GET', "viewforum.php?f=$forum_id");
		$this->assertContains($tagname, $crawler->text());
		$this->assertEquals(1, $crawler->filter('.rh_tag:contains("' . $tagname . '")')->count());

		// == cleanup ==

		// delete the created tags
		$existing_tags = $this->tags_manager->get_existing_tags(array($tagname));
		foreach ($existing_tags as $tag)
		{
			$this->tags_manager->delete_tag($tag['id']);
		}

		// delete the created topics
		$this->delete_topic($topic_id);
	}

	public function test_display_tagloud()
	{
		$this->login();
		$this->admin_login();

		// disable tagcloud
		$this->set_topictags_setting('_display_tagcloud_on_index', 0);

		// must not be visible on index
		$crawler = self::request('GET', 'index.php');
		$this->assertNotContainsLang('RH_TOPICTAGS_TAGCLOUD', $crawler->text());

		// enable it
		$this->set_topictags_setting('_display_tagcloud_on_index', 1);

		// must be visible on the index page
		$crawler = self::request('GET', 'index.php');
		$this->assertContainsLang('RH_TOPICTAGS_TAGCLOUD', $crawler->text());
	}

	public function test_max_tags_in_tagcloud()
	{
		// == test specific setup ==

		$this->login();
		$this->admin_login();

		// enable tagging in forum used for testing
		$forum_id = 2;
		$this->enable_topictags_in_forum($forum_id);

		// create a topic to work with
		$tmp = $this->create_topic($forum_id, 'display_tags_in_viewforum_functional_test', 'test topic');
		$topic_id = $tmp['topic_id'];

		// add tags
		$count_of_tags = 20;
		$valid_tags = array();
		for ($i = 1; $i <= $count_of_tags; $i++)
		{
			$valid_tags[] = 'tag5694524_' . $i;
		}
		$this->tags_manager->assign_tags_to_topic($topic_id, $valid_tags);

		// ensure cloud is shown
		$this->set_topictags_setting('_display_tagcloud_on_index', 1);

		// == actual tests ==

		$tag_count_in_cloud = -1;
		$this->set_topictags_setting('_max_tags_in_tagcloud', $tag_count_in_cloud, false, true);
		// check index
		$crawler = $this->request('GET', "index.php");
		$this->assertEquals(0, $crawler->filter('.rh_topictags_tagcloud li')->count());

		$tag_count_in_cloud = 2;
		$this->set_topictags_setting('_max_tags_in_tagcloud', $tag_count_in_cloud);
		// check index
		$crawler = $this->request('GET', "index.php");
		$this->assertEquals($tag_count_in_cloud, $crawler->filter('.rh_topictags_tagcloud li')->count());

		$tag_count_in_cloud = 200;
		$this->set_topictags_setting('_max_tags_in_tagcloud', $tag_count_in_cloud);
		// check index
		$crawler = $this->request('GET', "index.php");
		foreach ($valid_tags as $tag)
		{
			$this->assertContains($tag, $crawler->text());
		}
		$this->assertLessThanOrEqual($tag_count_in_cloud, $crawler->filter('.rh_topictags_tagcloud li')->count());

		// == cleanup ==

		// restore default
		$tag_count_in_cloud = 20;
		$this->set_topictags_setting('_max_tags_in_tagcloud', $tag_count_in_cloud);

		// delete the created tags
		$existing_tags = $this->tags_manager->get_existing_tags($valid_tags);
		foreach ($existing_tags as $tag)
		{
			$this->tags_manager->delete_tag($tag['id']);
		}

		// delete the created topics
		$this->delete_topic($topic_id);
	}
}
