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
use robertheim\topictags\TABLES;
use robertheim\topictags\PREFIXES;

class topictags_module
{

	/** @var string */
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $cache, $phpbb_container;

		// shortcut
		$conf_prefix = PREFIXES::CONFIG;

		$user->add_lang_ext('robertheim/topictags', 'topictags_acp');

		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'topictags';

		// Set the page title for our ACP page
		$this->page_title = 'ACP_TOPICTAGS_SETTINGS';

		// Define the name of the form for use as a form key
		$form_name = 'topictags';
		add_form_key($form_name);

		$tags_manager = $phpbb_container->get('robertheim.topictags.tags_manager');

		$errors = array();

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error('FORM_INVALID');
			}

			$submit = true;

			$regex = utf8_normalize_nfc($request->variable($conf_prefix.'_allowed_tags_regex', "/^[\- a-z0-9]{3,30}$/i", true));
			if (empty($regex))
			{
					$submit = false;
					$errors[] = $user->lang('ACP_RH_TOPICTAGS_REGEX_EMPTY');
			}

			$exp_for_users = utf8_normalize_nfc($request->variable($conf_prefix.'_allowed_tags_exp_for_users', "-, 0-9, a-z, A-Z, spaces (will be converted to -), min: 3, max: 30", true));
			if (empty($exp_for_users))
			{
					$submit = false;
					$errors[] = $user->lang('ACP_RH_TOPICTAGS_EXP_FOR_USERS_EMPTY');
			}

			if ($submit)
			{
				$config->set($conf_prefix.'_display_tags_in_viewforum', $request->variable($conf_prefix.'_display_tags_in_viewforum', 1));
				$config->set($conf_prefix.'_allowed_tags_regex', $regex);
				$config->set($conf_prefix.'_allowed_tags_exp_for_users', $exp_for_users);
				$config->set($conf_prefix.'_display_tagcloud_on_index', $request->variable($conf_prefix.'_display_tagcloud_on_index', 1));
				$config->set($conf_prefix.'_display_tagcount_in_tagcloud', $request->variable($conf_prefix.'_display_tagcount_in_tagcloud', 1));
				$config->set($conf_prefix.'_max_tags_in_tagcloud', $request->variable($conf_prefix.'_max_tags_in_tagcloud', 20));
				$config->set($conf_prefix.'_convert_space_to_minus', $request->variable($conf_prefix.'_convert_space_to_minus', 1));
				$config->set($conf_prefix.'_whitelist_enabled', $request->variable($conf_prefix.'_whitelist_enabled', 0));
				$config->set($conf_prefix.'_whitelist', utf8_normalize_nfc($request->variable($conf_prefix.'_whitelist', '', true)));
				$config->set($conf_prefix.'_blacklist_enabled', $request->variable($conf_prefix.'_blacklist_enabled', 0));
				$config->set($conf_prefix.'_blacklist', utf8_normalize_nfc($request->variable($conf_prefix.'_blacklist', '', true)));

				$msg = array();
				$deleted_assignments_count = 0;
				$delete_unused_tags = false;
	
				if ($request->variable($conf_prefix.'_enable_in_all_forums', 0) > 0)
				{
					$count_affected = $tags_manager->enable_tags_in_all_forums();
					$msg[] = $user->lang('TOPICTAGS_ENABLE_IN_ALL_FORUMS_DONE', $count_affected);
				}

				if ($request->variable($conf_prefix.'_disable_in_all_forums', 0) > 0)
				{
					$count_affected = $tags_manager->disable_tags_in_all_forums();
					$msg[] = $user->lang('TOPICTAGS_DISABLE_IN_ALL_FORUMS_DONE', $count_affected);
				}

				if ($request->variable($conf_prefix.'_calc_count_tags', 0) > 0)
				{
					$tags_manager->calc_count_tags();
					$msg[] = $user->lang('TOPICTAGS_CALC_COUNT_TAGS_DONE');
				}

				if ($request->variable($conf_prefix.'_prune', 0) > 0)
				{
					$deleted_assignments_count += $tags_manager->delete_assignments_where_topic_does_not_exist();
					$delete_unused_tags = true;
				}

				if ($request->variable($conf_prefix.'_prune_forums', 0) > 0)
				{
					$deleted_assignments_count += $tags_manager->delete_tags_from_tagdisabled_forums();
					$delete_unused_tags = true;
				}

				if ($request->variable($conf_prefix.'_prune_invalid_tags', 0) > 0)
				{
					$deleted_assignments_count += $tags_manager->delete_assignments_of_invalid_tags();
					$delete_unused_tags = true;
				}
	
				if ($delete_unused_tags)
				{
					$deleted_tags_count = $tags_manager->delete_unused_tags();
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

				trigger_error(join("<br/>", $msg) . adm_back_link($this->u_action));
			}
		}

		$all_enabled = $tags_manager->is_enabled_in_all_forums();
		$all_disabled = ($all_enabled ? false : $tags_manager->is_disabled_in_all_forums());

		$template->assign_vars(array(
			'TOPICTAGS_VERSION'							=> $user->lang('TOPICTAGS_INSTALLED', $config[$conf_prefix.'_version']),
			'TOPICTAGS_DISPLAY_TAGS_IN_VIEWFORUM'		=> $config[$conf_prefix.'_display_tags_in_viewforum'],
			'TOPICTAGS_DISPLAY_TAGCLOUD_ON_INDEX'		=> $config[$conf_prefix.'_display_tagcloud_on_index'],
			'TOPICTAGS_DISPLAY_TAGCOUNT_IN_TAGCLOUD'	=> $config[$conf_prefix.'_display_tagcount_in_tagcloud'],
			'TOPICTAGS_MAX_TAGS_IN_TAGCLOUD'			=> $config[$conf_prefix.'_max_tags_in_tagcloud'],
			'TOPICTAGS_ALLOWED_TAGS_REGEX'				=> $config[$conf_prefix.'_allowed_tags_regex'],
			'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS'		=> $config[$conf_prefix.'_allowed_tags_exp_for_users'],
			'TOPICTAGS_CONVERT_SPACE_TO_MINUS'			=> $config[$conf_prefix.'_convert_space_to_minus'],
			'TOPICTAGS_WHITELIST_ENABLED'				=> $config[$conf_prefix.'_whitelist_enabled'],
			'TOPICTAGS_WHITELIST'						=> $config[$conf_prefix.'_whitelist'],
			'TOPICTAGS_BLACKLIST_ENABLED'				=> $config[$conf_prefix.'_blacklist_enabled'],
			'TOPICTAGS_BLACKLIST'						=> $config[$conf_prefix.'_blacklist'],
			'TOPICTAGS_IS_ENABLED_IN_ALL_FORUMS'		=> $all_enabled,
			'TOPICTAGS_IS_DISABLED_IN_ALL_FORUMS'		=> $all_disabled,
			'S_ERROR'									=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'									=> implode('<br />', $errors),
			'U_ACTION'									=> $this->u_action,
		));
	}

}
