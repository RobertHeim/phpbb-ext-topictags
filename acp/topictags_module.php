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
use \phpbb\json_response;

class topictags_module
{

	/** @var string */
	public $u_action;

	protected $tags_manager;
	
	public function __construct()
	{
		global $phpbb_container;
		$this->tags_manager = $phpbb_container->get('robertheim.topictags.tags_manager');
	}
	
	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $cache, $phpbb_container;

		// shortcut
		$conf_prefix = PREFIXES::CONFIG;

		$user->add_lang_ext('robertheim/topictags', 'topictags_acp');

		if ('whitelist' == $mode)
		{
			$this->tpl_name = 'topictags_whitelist';
			$this->page_title = 'ACP_TOPICTAGS_WHITELIST';

		}
		else if ('blacklist' == $mode)
		{
			$this->tpl_name = 'topictags_blacklist';
			$this->page_title = 'ACP_TOPICTAGS_BLACKLIST';
		}
		else if ('tags' == $mode)
		{
			$action = $request->variable('action', '');
			$this->tpl_name = 'topictags_manage_tags';
			$this->page_title = 'ACP_TOPICTAGS_MANAGE_TAGS';
			if ('delete' == $action)
			{
				$tag_id = $request->variable('tag_id', -1);
				if ($tag_id < 1)
				{
					if ($request->is_ajax())
					{
						trigger_error('TOPICTAGS_MISSING_TAG_ID', E_USER_WARNING);
					}
					trigger_error($user->lang('TOPICTAGS_MISSING_TAG_ID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$tag = $this->tags_manager->get_tag_by_id($tag_id);
				
				if (confirm_box(true))
				{
					$this->tags_manager->delete_tag($tag_id);
					
					if ($request->is_ajax())
					{
						trigger_error('TOPICTAGS_TAG_DELETED');
					}
					//trigger_error($user->lang['YES'] . adm_back_link($this->u_action));
					trigger_error($user->lang('TOPICTAGS_TAG_DELETED') . adm_back_link($this->u_action));
				}
				else
				{
					$confirm_text = $user->lang('TOPICTAGS_TAG_DELETE_CONFIRM', $tag);
					confirm_box(false, $confirm_text, build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
						'tag_id'	=> $tag_id,
					)));
				}
			} // delete
			if ('edit' == $action)
			{
				// TODO none-ajax
				$old_tag_name = $request->variable('old_tag_name', '');
				$new_tag_name = $request->variable('new_tag_name', '');
				if (empty($old_tag_name) || empty($new_tag_name))
				{
					trigger_error($user->lang('TOPICTAGS_MISSING_TAG_NAMES') . adm_back_link($this->u_action), E_USER_WARNING);
				}
				else
				{
					$old_ids = $this->tags_manager->get_existing_tags(array($old_tag_name), true);
					if (empty($old_ids))
					{
						$error_msg = $user->lang('TOPICTAGS_TAG_DOES_NOT_EXIST', $old_tag_name);
						if ($request->is_ajax())
						{
							$response = new json_response();
							$response->send(array(
								'success'	=> false,
								'error_msg'	=> $error_msg,
							));
						}
						trigger_error($error_msg . adm_back_link($this->u_action), E_USER_WARNING);
					}
					// if we reach here, we know that we got a single valid old tag
					$old_id = $old_ids[0];

					$new_tag_name_clean = $this->tags_manager->clean_tag($new_tag_name);
					$is_valid = $this->tags_manager->is_valid_tag($new_tag_name_clean, true);
					if (!$is_valid)
					{
						$error_msg = $user->lang('TOPICTAGS_TAG_INVALID', $new_tag_name);
						if ($request->is_ajax())
						{
							$response = new json_response();
							$response->send(array(
								'success'	=> false,
								'error_msg'	=> $error_msg,
							));
						}
						trigger_error($error_msg . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// old tag exist and new tag is valid
					$new_ids = $this->tags_manager->get_existing_tags(array($new_tag_name), true);
					if (!empty($new_ids))
					{
						// new tag exist -> merge
						$new_id = $new_ids[0];
						$new_tag_count = $this->tags_manager->merge($old_tag_name, $old_id, $new_tag_name, $new_id);
						if ($request->is_ajax())
						{
							$response = new json_response();
							$response->send(array(
								'success'		=> true,
								'merged'		=> true,
								'new_tag_count'	=> $new_tag_count,
								'msg'			=> $user->lang('TOPICTAGS_TAG_MERGED', $new_tag_name_clean),
							));
						}
						trigger_error($user->lang('TOPICTAGS_TAG_MERGED', $new_tag_name_clean) . adm_back_link($this->u_action));
					}

					// old tag exist and new tag is valid and does not exist -> rename it
					$tag_count = $this->tags_manager->rename($old_id, $new_tag_name_clean);
					if ($request->is_ajax())
					{
						$response = new json_response();
						$response->send(array(
							'success'	=> true,
							'msg'		=> $user->lang('TOPICTAGS_TAG_CHANGED'),
						));
					}
					trigger_error($user->lang('TOPICTAGS_TAG_CHANGED') . adm_back_link($this->u_action));
				}
			} // edit

			// show all tags
			$pagination		= $phpbb_container->get('pagination');
			
			$start			= $request->variable('start', 0);
			$limit			= $config['topics_per_page'];
			$tags_count		= $this->tags_manager->count_tags();
			$start			= $pagination->validate_start($start, $limit, $tags_count);
				
			$tags = $this->tags_manager->get_all_tags($start, $limit, 'tag', true);
			$base_url		= $this->u_action;

			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $tags_count, $limit, $start);
			
			foreach ($tags as $tag) {
				$template->assign_block_vars('tags', array(
					'NAME'			=> $tag['tag'],
					'ASSIGNMENTS'	=> $tag['count'],
					'U_DELETE_TAG'	=> $this->get_tag_link($mode, $tag['id']) . '&amp;action=delete',
					// TODO none-ajax 'U_EDIT_TAG_URL'	=> $this->get_tag_link($mode, $tag['id']) . '&amp;action=edit',
				));
			}
		}
		else
		{
			$this->tpl_name = 'topictags';
			$this->page_title = 'ACP_TOPICTAGS_SETTINGS';
		}

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
			$msg = array();

			if ('whitelist' == $mode)
			{
				$config->set($conf_prefix.'_whitelist_enabled', $request->variable($conf_prefix.'_whitelist_enabled', 0));
				$whitelist = rawurldecode(base64_decode($request->variable($conf_prefix.'_whitelist', '')));
				if (!empty($whitelist))
				{
					$whitelist = json_decode($whitelist, true);
					$tags = array();
					for ($i = 0, $size = sizeof($whitelist); $i < $size; $i++)
					{
						$tags[] = $whitelist[$i]['text'];
					}
					$whitelist = json_encode($tags);
				}
				$config->set($conf_prefix.'_whitelist', $whitelist);
			}

			if ('blacklist' == $mode)
			{
				$config->set($conf_prefix.'_blacklist_enabled', $request->variable($conf_prefix.'_blacklist_enabled', 0));
				$blacklist = rawurldecode(base64_decode($request->variable($conf_prefix.'_blacklist', '')));
				if (!empty($blacklist))
				{
					$blacklist = json_decode($blacklist, true);
					$tags = array();
					for ($i = 0, $size = sizeof($blacklist); $i < $size; $i++)
					{
						$tags[] = $blacklist[$i]['text'];
					}
					$blacklist = json_encode($tags);
				}
				$config->set($conf_prefix.'_blacklist', $blacklist);
			}

			if ('settings' == $mode)
			{
				$regex = utf8_normalize_nfc($request->variable($conf_prefix.'_allowed_tags_regex', $user->lang('ACP_RH_TOPICTAGS_REGEX_DEFAULT'), true));
				if (empty($regex))
				{
						$submit = false;
						$errors[] = $user->lang('ACP_RH_TOPICTAGS_REGEX_EMPTY');
				}

				$exp_for_users = utf8_normalize_nfc($request->variable($conf_prefix.'_allowed_tags_exp_for_users', $user->lang('ACP_RH_TOPICTAGS_REGEX_EXP_FOR_USERS_DEFAULT'), true));
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
				}
			}
			if ($submit)
			{
				if (empty($msg))
				{
					$msg[] = $user->lang('TOPICTAGS_SETTINGS_SAVED');
				}
				trigger_error(join('<br/>', $msg) . adm_back_link($this->u_action));
			}
		}
		if ('whitelist' == $mode)
		{
			$whitelist = $config[$conf_prefix.'_whitelist'];
			$whitelist = base64_encode(rawurlencode($whitelist));
			$template->assign_vars(array(
				'TOPICTAGS_VERSION'							=> $user->lang('TOPICTAGS_INSTALLED', $config[$conf_prefix.'_version']),
				'TOPICTAGS_WHITELIST_ENABLED'				=> $config[$conf_prefix.'_whitelist_enabled'],
				'TOPICTAGS_WHITELIST'						=> $whitelist,
				'S_RH_TOPICTAGS_INCLUDE_NG_TAGS_INPUT'		=> true,
				'S_RH_TOPICTAGS_INCLUDE_CSS'				=> true,
				'S_ERROR'									=> (sizeof($errors)) ? true : false,
				'ERROR_MSG'									=> implode('<br />', $errors),
				'U_ACTION'									=> $this->u_action,
			));
		}
		else if ('blacklist' == $mode)
		{
			$blacklist = $config[$conf_prefix.'_blacklist'];
			$blacklist = base64_encode(rawurlencode($blacklist));
			$template->assign_vars(array(
				'TOPICTAGS_VERSION'							=> $user->lang('TOPICTAGS_INSTALLED', $config[$conf_prefix.'_version']),
				'TOPICTAGS_BLACKLIST_ENABLED'				=> $config[$conf_prefix.'_blacklist_enabled'],
				'TOPICTAGS_BLACKLIST'						=> $blacklist,
				'S_RH_TOPICTAGS_INCLUDE_NG_TAGS_INPUT'		=> true,
				'S_RH_TOPICTAGS_INCLUDE_CSS'				=> true,
				'S_ERROR'									=> (sizeof($errors)) ? true : false,
				'ERROR_MSG'									=> implode('<br />', $errors),
				'U_ACTION'									=> $this->u_action,
			));
		}
		else
		{
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
				'TOPICTAGS_IS_ENABLED_IN_ALL_FORUMS'		=> $all_enabled,
				'TOPICTAGS_IS_DISABLED_IN_ALL_FORUMS'		=> $all_disabled,
				'S_ERROR'									=> (sizeof($errors)) ? true : false,
				'ERROR_MSG'									=> implode('<br />', $errors),
				'U_ACTION'									=> $this->u_action,
			));
		}
	}

	private function get_tag_link($mode, $tag_id)
	{
		return $this->u_action . (($tag_id) ? '&amp;tag_id=' . $tag_id : '');
	}
	
}
