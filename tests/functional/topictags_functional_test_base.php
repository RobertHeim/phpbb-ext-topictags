<?php
/**
 *
 * @package phpBB Extension - RH Topic Tags
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\topictags\tests\functional;

use \robertheim\topictags\prefixes;
use robertheim\topictags\service\db_helper;

/**
 * @group functional
 */
class topictags_functional_test_base extends \phpbb_functional_test_case
{

	/** @var /phpbb\auth\auth */
	protected $auth;

	/** @var \robertheim\topictags\service\tags_manager */
	protected $tags_manager;

	static protected function setup_extensions()
	{
		return array('robertheim/topictags');
	}

	public function setUp()
	{
		parent::setUp();
		// Load all language files
		$this->add_lang_ext('robertheim/topictags', array(
				'info_acp_topictags',
				'permissions_topictags',
				'topictags_acp',
				'topictags',
		));

		global $table_prefix;
		$this->auth = $this->getMock('\phpbb\auth\auth');
		$config = new \phpbb\config\config(array(
				prefixes::CONFIG.'_allowed_tags_regex' => '/^[a-z]{3,30}$/i',
		));
		$db_helper = new db_helper($this->get_db());
		$config_text = new \phpbb\config\db_text($this->get_db(), $table_prefix . 'config_text');
		$this->tags_manager = new \robertheim\topictags\service\tags_manager(
				$this->get_db(), $config, $config_text, $this->auth, $db_helper, $table_prefix);
	}

	/**
	 * Gets the acp module page for the settings.
	 *
	 * @return \Symfony\Component\DomCrawler\Crawler the crawler of the acp settings page.
	 */
	protected function goto_settings_page()
	{
		// Load Pages ACP page
		return self::request('GET', "adm/index.php?i=-robertheim-topictags-acp-topictags_module&sid={$this->sid}");
	}

	/**
	 * Gets the acp module page for managing tags.
	 *
	 * @return \Symfony\Component\DomCrawler\Crawler the crawler of the acp settings page.
	 */
	protected function goto_manage_tags_page()
	{
		// Load Pages ACP page
		return self::request('GET', "adm/index.php?i=-robertheim-topictags-acp-topictags_module&mode=tags&sid={$this->sid}");
	}

	/**
	 * Performs an ajax POST request, expectes a json response and decodes this json response an assoc array which is returned
	 * @return array the decoded json response as assoc array
	 */
	protected function ajax($url, $params)
	{
		self::$client->request('POST', self::$root_url . $url, $params, array(), array(
				'HTTP_X-Requested-With' => 'XMLHttpRequest',
		));
		$this->assertEquals('application/json',
				self::$client->getResponse()->getHeader('Content-Type')
		);
		return json_decode(self::$client->getResponse()->getContent(), true);
	}

	protected function enable_topictags_in_forum($forum_id)
	{
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', array('rh_topictags_enabled' => 1)) . '
			WHERE forum_id = ' . ((int) $forum_id);
		$this->db->sql_query($sql);
		$this->tags_manager->calc_count_tags();
	}

	/**
	 * Sets a setting via the acp settings page of the extension.
	 *
	 * @param string $html_name_postfix the name of the html form element to set the value for
	 * @param mixed $value the value to set
	 * @param boolean $validate_setting_update if true (default) the settings page is checked again for holding the new value after the update.
	 * @param boolean $already_logged_in_acp whether (default) or not the user is already logged in into the acp
	 */
	protected function set_topictags_setting($html_name_postfix, $value, $validate_setting_update = true, $already_logged_in_acp = true)
	{
		if (!$already_logged_in_acp)
		{
			$this->login();
			$this->admin_login();
		}
		$crawler = $this->goto_settings_page();
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$field = $form->get(prefixes::CONFIG . $html_name_postfix);
		$field->setValue($value);
		$crawler = $this->submit($form);
		$this->assertContainsLang('TOPICTAGS_SETTINGS_SAVED', $crawler->text());

		if ($validate_setting_update)
		{
			// must be updated
			$crawler = $this->goto_settings_page();
			$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
			$field = $form->get(prefixes::CONFIG . $html_name_postfix);
			$this->assertEquals($value, $field->getValue());
		}
	}
}
