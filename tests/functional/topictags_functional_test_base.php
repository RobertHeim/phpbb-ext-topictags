<?php
/**
 *
 * @package phpBB Extension - RH Topic Tags
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\topictags\tests\functional;

use \robertheim\topictags\prefixes;
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
		$this->tags_manager = new \robertheim\topictags\service\tags_manager(
				$this->get_db(), $config, $this->auth, $table_prefix);
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
}
