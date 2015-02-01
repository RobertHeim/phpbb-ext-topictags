<?php
/**
 *
 * @package phpBB Extension - RH Topic Tags
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\topictags\acp;

/**
* @ignore
*/
use robertheim\topictags\prefixes;

/**
 * Handles the "whitelist" and "blacklist" page of the ACP.
 */
class white_and_blacklist_controller
{

	private $config;

	private $request;

	private $user;

	private $template;

	private $tags_manager;

	public function __construct(\phpbb\config\config $config, 
		\phpbb\request\request $request,
		\phpbb\user $user, 
		\phpbb\template\template $template,
		\robertheim\topictags\service\tags_manager $tags_manager)
	{
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->tags_manager = $tags_manager;
	}

	/**
	 *
	 * @param string $mode
	 *        	phpbb acp-mode
	 * @param string $u_action
	 *        	phpbb acp-u_action
	 */
	public function manage_whitelist($mode, $u_action)
	{
		// Define the name of the form for use as a form key
		$form_name = 'topictags';
		add_form_key($form_name);

		$errors = array();

		if ($this->request->is_set_post('submit'))
		{
			if (! check_form_key($form_name))
			{
				trigger_error('FORM_INVALID');
			}

			$this->config->set(prefixes::CONFIG . '_whitelist_enabled', $this->request->variable(prefixes::CONFIG . '_whitelist_enabled', 0));
			$whitelist = rawurldecode(base64_decode($this->request->variable(prefixes::CONFIG . '_whitelist', '')));
			if (! empty($whitelist))
			{
				$whitelist = json_decode($whitelist, true);
				$tags = array();
				for ($i = 0, $size = sizeof($whitelist); $i < $size; $i ++)
				{
					$tags[] = $whitelist[$i]['text'];
				}
				$whitelist = json_encode($tags);
			}
			$this->config->set(prefixes::CONFIG . '_whitelist', $whitelist);
			trigger_error($this->user->lang('TOPICTAGS_WHITELIST_SAVED') . adm_back_link($u_action));
		}
		$whitelist = $this->config[prefixes::CONFIG . '_whitelist'];
		$whitelist = base64_encode(rawurlencode($whitelist));
		$this->template->assign_vars(
			array(
				'TOPICTAGS_VERSION'						=> $this->user->lang('TOPICTAGS_INSTALLED', $this->config[prefixes::CONFIG . '_version']),
				'TOPICTAGS_WHITELIST_ENABLED'			=> $this->config[prefixes::CONFIG . '_whitelist_enabled'],
				'TOPICTAGS_WHITELIST'					=> $whitelist,
				'S_RH_TOPICTAGS_INCLUDE_NG_TAGS_INPUT'	=> true,
				'S_RH_TOPICTAGS_INCLUDE_CSS'			=> true,
				'S_ERROR'								=> (sizeof($errors)) ? true : false,
				'ERROR_MSG'								=> implode('<br />', $errors),
				'U_ACTION'								=> $u_action
			));
	}

	/**
	 *
	 * @param string $mode
	 *        	phpbb acp-mode
	 * @param string $u_action
	 *        	phpbb acp-u_action
	 */
	public function manage_blacklist($mode, $u_action)
	{
		// Define the name of the form for use as a form key
		$form_name = 'topictags';
		add_form_key($form_name);

		$errors = array();

		if ($this->request->is_set_post('submit'))
		{
			if (! check_form_key($form_name))
			{
				trigger_error('FORM_INVALID');
			}

			$this->config->set(prefixes::CONFIG . '_blacklist_enabled', $this->request->variable(prefixes::CONFIG . '_blacklist_enabled', 0));
			$blacklist = rawurldecode(base64_decode($this->request->variable(prefixes::CONFIG . '_blacklist', '')));
			if (! empty($blacklist))
			{
				$blacklist = json_decode($blacklist, true);
				$tags = array();
				for ($i = 0, $size = sizeof($blacklist); $i < $size; $i ++)
				{
					$tags[] = $blacklist[$i]['text'];
				}
				$blacklist = json_encode($tags);
			}
			$this->config->set(prefixes::CONFIG . '_blacklist', $blacklist);
			trigger_error($this->user->lang('TOPICTAGS_BLACKLIST_SAVED') . adm_back_link($u_action));
		}
		$blacklist = $this->config[prefixes::CONFIG . '_blacklist'];
		$blacklist = base64_encode(rawurlencode($blacklist));
			$this->template->assign_vars(
				array(
					'TOPICTAGS_VERSION'						=> $this->user->lang('TOPICTAGS_INSTALLED', $this->config[prefixes::CONFIG . '_version']),
					'TOPICTAGS_BLACKLIST_ENABLED'			=> $this->config[prefixes::CONFIG . '_blacklist_enabled'],
					'TOPICTAGS_BLACKLIST'					=> $blacklist,
					'S_RH_TOPICTAGS_INCLUDE_NG_TAGS_INPUT'	=> true,
					'S_RH_TOPICTAGS_INCLUDE_CSS'			=> true,
					'S_ERROR'								=> (sizeof($errors)) ? true : false,
					'ERROR_MSG'								=> implode('<br />', $errors),
					'U_ACTION'								=> $u_action
				));
	}

}
