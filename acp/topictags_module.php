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
		global $config, $request, $template, $user, $cache;

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

		$errors = array();

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error('FORM_INVALID');
			}

			$submit = true;

			$regex = $request->variable($conf_prefix.'_allowed_tags_regex', '/^[a-z0-9]{3,30}$/i');
			if (empty($regex))
			{
					$submit = false;
					$errors[] = $user->lang('ACP_RH_TOPICTAGS_REGEX_EMPTY');
			}

			$exp_for_users = $request->variable($conf_prefix.'_allowed_tags_exp_for_users', '0-9, a-z, A-Z, min: 3, max: 30');
			if (empty($exp_for_users))
			{
					$submit = false;
					$errors[] = $user->lang('ACP_RH_TOPICTAGS_EXP_FOR_USERS_EMPTY');
			}

			if ($submit)
			{
				$config->set($conf_prefix.'_allowed_tags_regex', $regex);
				$config->set($conf_prefix.'_allowed_tags_exp_for_users', $exp_for_users);

				$msg = array();
				$deleted_assignments_count = 0;
				$delete_unused_tags = false;
	
				if ($request->variable($conf_prefix.'_prune', 0) > 0)
				{
					global $phpbb_container;
					$tags_manager = $phpbb_container->get('robertheim.topictags.tags_manager');
					$deleted_assignments_count += $tags_manager->delete_assignments_where_topic_does_not_exist();
					$delete_unused_tags = true;
				}

				if ($request->variable($conf_prefix.'_prune_forums', 0) > 0)
				{
					global $phpbb_container;
					$tags_manager = $phpbb_container->get('robertheim.topictags.tags_manager');
					$deleted_assignments_count += $tags_manager->delete_tags_from_tagdisabled_forums();
					$delete_unused_tags = true;
				}

				if ($request->variable($conf_prefix.'_prune_invalid_tags', 0) > 0)
				{
					global $phpbb_container;
					$tags_manager = $phpbb_container->get('robertheim.topictags.tags_manager');
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

		$template->assign_vars(array(
			'TOPICTAGS_VERSION'						=> $user->lang('TOPICTAGS_INSTALLED', $config[$conf_prefix.'_version']),
			'TOPICTAGS_ALLOWED_TAGS_REGEX'			=> $config[$conf_prefix.'_allowed_tags_regex'],
			'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS'	=> $config[$conf_prefix.'_allowed_tags_exp_for_users'],
			'S_ERROR'								=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'								=> implode('<br />', $errors),
			'U_ACTION'								=> $this->u_action,
		));

	}

}
