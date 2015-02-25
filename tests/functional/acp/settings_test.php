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

/**
 * @group functional
 */
class settings_test extends topictags_functional_test_base
{
	/**
	 * Gets the acp module page for the settings.
	 *
	 * @return \Symfony\Component\DomCrawler\Crawler the crawler of the acp settings page.
	 */
	private function goto_settings_page()
	{
		// Load Pages ACP page
		return self::request('GET', "adm/index.php?i=-robertheim-topictags-acp-topictags_module&sid={$this->sid}");
	}

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

	public function test_display_tagloud()
	{
		$this->login();
		$this->admin_login();

		// disable tagcloud
		$crawler = $this->goto_settings_page();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get('robertheim_topictags_display_tagcloud_on_index');
		$field->setValue(0);
		$crawler = $this->submit($form);
		$this->assertContainsLang('TOPICTAGS_SETTINGS_SAVED', $crawler->text());

		// must not be visible on index
		$crawler = self::request('GET', 'index.php');
		$this->assertNotContainsLang('RH_TOPICTAGS_TAGCLOUD', $crawler->text());

		// must be disabled
		$crawler = $this->goto_settings_page();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get('robertheim_topictags_display_tagcloud_on_index');
		$this->assertEquals('0', $field->getValue());

		// enable it
		$field->setValue(1);
		$crawler = $this->submit($form);
		$this->assertContainsLang('TOPICTAGS_SETTINGS_SAVED', $crawler->text());

		// must be enabled now
		$crawler = $this->goto_settings_page();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get('robertheim_topictags_display_tagcloud_on_index');
		$this->assertEquals('1', $field->getValue());

		// must be visible on the index page
		$crawler = self::request('GET', 'index.php');
		$this->assertContainsLang('RH_TOPICTAGS_TAGCLOUD', $crawler->text());
	}

}
