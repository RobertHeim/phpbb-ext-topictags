<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\acp;

/**
* @ignore
*/
use robertheim\topictags\prefixes;

class topictags_module
{

	/** @var string */
	public $u_action;

	/** @var \robertheim\topictags\service\tags_manager */
	protected $tags_manager;

	public function __construct()
	{
		global $phpbb_container;
		$this->tags_manager = $phpbb_container->get('robertheim.topictags.tags_manager');
	}

	/**
	 * Delegates to proper functions that handle the specific case
	 *
	 * @param string $id the id of the acp-module (the url-param "i")
	 * @param string $mode the phpbb acp-mode
	 */
	public function main($id, $mode)
	{
		global $user, $phpbb_container;

		$user->add_lang_ext('robertheim/topictags', 'topictags_acp');

		switch ($mode)
		{
			case 'whitelist':
				$this->tpl_name = 'topictags_whitelist';
				$this->page_title = 'ACP_TOPICTAGS_WHITELIST';
				$acp_whiteblacklist_controller = $phpbb_container->get('robertheim.topictags.acp.white_and_blacklist_controller');
				$acp_whiteblacklist_controller->manage_whitelist($this->u_action);
			break;
			case 'blacklist':
				$this->tpl_name = 'topictags_blacklist';
				$this->page_title = 'ACP_TOPICTAGS_BLACKLIST';
				$acp_whiteblacklist_controller = $phpbb_container->get('robertheim.topictags.acp.white_and_blacklist_controller');
				$acp_whiteblacklist_controller->manage_blacklist($this->u_action);
			break;
			case 'tags':
				$this->tpl_name = 'topictags_manage_tags';
				$this->page_title = 'ACP_TOPICTAGS_MANAGE_TAGS';
				$acp_manage_tags_controller = $phpbb_container->get('robertheim.topictags.acp.manage_tags_controller');
				$acp_manage_tags_controller->manage_tags($mode, $this->u_action, $id);
			break;
			case 'settings':
			// no break
			default:
				$this->tpl_name = 'topictags';
				$this->page_title = 'ACP_TOPICTAGS_SETTINGS';
				$this->handle_settings();
		}
	}

	/**
	 * Default settings page
	 */
	private function handle_settings()
	{
		global $config, $request, $template, $user;
		// Define the name of the form for use as a form key
		$form_name = 'topictags';
		add_form_key($form_name);

		$errors = array();

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error('FORM_INVALID');
			}

			$commit_to_db = true;
			$msg = array();

			$regex = utf8_normalize_nfc($request->variable(prefixes::CONFIG . '_allowed_tags_regex', $user->lang('ACP_RH_TOPICTAGS_REGEX_DEFAULT'), true));
			if (empty($regex))
			{
				$commit_to_db = false;
				$errors[] = $user->lang('ACP_RH_TOPICTAGS_REGEX_EMPTY');
			}

			$exp_for_users = utf8_normalize_nfc($request->variable(prefixes::CONFIG . '_allowed_tags_exp_for_users', $user->lang('ACP_RH_TOPICTAGS_REGEX_EXP_FOR_USERS_DEFAULT'), true));
			if (empty($exp_for_users))
			{
				$commit_to_db = false;
				$errors[] = $user->lang('ACP_RH_TOPICTAGS_EXP_FOR_USERS_EMPTY');
			}

			if ($commit_to_db)
			{
				$config->set(prefixes::CONFIG . '_display_tags_in_viewforum', $request->variable(prefixes::CONFIG . '_display_tags_in_viewforum', 1));
				$config->set(prefixes::CONFIG . '_allowed_tags_regex', $regex);
				$config->set(prefixes::CONFIG . '_allowed_tags_exp_for_users', $exp_for_users);
				$config->set(prefixes::CONFIG . '_display_tagcloud_on_index', $request->variable(prefixes::CONFIG . '_display_tagcloud_on_index', 1));
				$config->set(prefixes::CONFIG . '_display_tagcount_in_tagcloud', $request->variable(prefixes::CONFIG . '_display_tagcount_in_tagcloud', 1));
				$max_tags_in_tagcloud = $request->variable(prefixes::CONFIG . '_max_tags_in_tagcloud', 20);
				if ($max_tags_in_tagcloud < 0)
				{
					$max_tags_in_tagcloud = 0;
				}
				$config->set(prefixes::CONFIG . '_max_tags_in_tagcloud', $max_tags_in_tagcloud);
				$config->set(prefixes::CONFIG . '_convert_space_to_minus', $request->variable(prefixes::CONFIG . '_convert_space_to_minus', 1));

				$deleted_assignments_count = 0;
				$delete_unused_tags = false;

				if ($request->variable(prefixes::CONFIG . '_enable_in_all_forums', 0) > 0)
				{
					$count_affected = $this->tags_manager->enable_tags_in_all_forums();
					$msg[] = $user->lang('TOPICTAGS_ENABLE_IN_ALL_FORUMS_DONE', $count_affected);
				}

				if ($request->variable(prefixes::CONFIG . '_disable_in_all_forums', 0) > 0)
				{
					$count_affected = $this->tags_manager->disable_tags_in_all_forums();
					$msg[] = $user->lang('TOPICTAGS_DISABLE_IN_ALL_FORUMS_DONE', $count_affected);
				}

				if ($request->variable(prefixes::CONFIG . '_calc_count_tags', 0) > 0)
				{
					$this->tags_manager->calc_count_tags();
					$msg[] = $user->lang('TOPICTAGS_CALC_COUNT_TAGS_DONE');
				}

				if ($request->variable(prefixes::CONFIG . '_prune', 0) > 0)
				{
					$deleted_assignments_count += $this->tags_manager->delete_assignments_where_topic_does_not_exist();
					$delete_unused_tags = true;
				}

				if ($request->variable(prefixes::CONFIG . '_prune_forums', 0) > 0)
				{
					$deleted_assignments_count += $this->tags_manager->delete_tags_from_tagdisabled_forums();
					$delete_unused_tags = true;
				}

				if ($request->variable(prefixes::CONFIG . '_prune_invalid_tags', 0) > 0)
				{
					$deleted_assignments_count += $this->tags_manager->delete_assignments_of_invalid_tags();
					$delete_unused_tags = true;
				}

				if ($delete_unused_tags)
				{
					$deleted_tags_count = $this->tags_manager->delete_unused_tags();
					$msg[] = $user->lang('TOPICTAGS_PRUNE_TAGS_DONE', $deleted_tags_count);
				}

				if ($deleted_assignments_count > 0)
				{
					$msg[] = $user->lang('TOPICTAGS_PRUNE_ASSIGNMENTS_DONE', $deleted_assignments_count);
				}

				if (empty($msg))
				{
					$msg[] = $user->lang('TOPICTAGS_SETTINGS_SAVED');
				}
				trigger_error(join('<br/>', $msg) . adm_back_link($this->u_action));
			}
		}
		$all_enabled = $this->tags_manager->is_enabled_in_all_forums();
		$all_disabled = ($all_enabled ? false : $this->tags_manager->is_disabled_in_all_forums());
		$template->assign_vars(array(
			'TOPICTAGS_VERSION'                      => $user->lang('TOPICTAGS_INSTALLED', $config[prefixes::CONFIG . '_version']),
			'TOPICTAGS_DISPLAY_TAGS_IN_VIEWFORUM'    => $config[prefixes::CONFIG . '_display_tags_in_viewforum'],
			'TOPICTAGS_DISPLAY_TAGCLOUD_ON_INDEX'    => $config[prefixes::CONFIG . '_display_tagcloud_on_index'],
			'TOPICTAGS_DISPLAY_TAGCOUNT_IN_TAGCLOUD' => $config[prefixes::CONFIG . '_display_tagcount_in_tagcloud'],
			'TOPICTAGS_MAX_TAGS_IN_TAGCLOUD'         => $config[prefixes::CONFIG . '_max_tags_in_tagcloud'],
			'TOPICTAGS_ALLOWED_TAGS_REGEX'           => $config[prefixes::CONFIG . '_allowed_tags_regex'],
			'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS'   => $config[prefixes::CONFIG . '_allowed_tags_exp_for_users'],
			'TOPICTAGS_CONVERT_SPACE_TO_MINUS'       => $config[prefixes::CONFIG . '_convert_space_to_minus'],
			'TOPICTAGS_IS_ENABLED_IN_ALL_FORUMS'     => $all_enabled,
			'TOPICTAGS_IS_DISABLED_IN_ALL_FORUMS'    => $all_disabled,
			'S_ERROR'                                => (sizeof($errors)) ? true : false,
			'ERROR_MSG'                              => implode('<br />', $errors),
			'U_ACTION'                               => $this->u_action,
		));
	}
}
