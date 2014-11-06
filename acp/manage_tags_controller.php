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
use \phpbb\json_response;

/**
* Handles the "manage-tags" page of the ACP.
*/
class manage_tags_controller
{

	private $config;
	private $request;
	private $user;
	private $template;
	private $pagination;
	
	private $tags_manager;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\request\request $request,
		\phpbb\user $user,
		\phpbb\template\template $template,
		\phpbb\pagination $pagination,
		\robertheim\topictags\service\tags_manager $tags_manager)
	{
		global $phpbb_container;
		$this->config		= $config;
		$this->request		= $request;
		$this->user			= $user;
		$this->template		= $template;
		$this->pagination	= $pagination;
		$this->tags_manager	= $tags_manager;		
	}
	
	/**
	 * 
	 * @param string $mode phpbb acp-mode
	 * @param string $u_action phpbb acp-u_action
	 * @param string $id the modules-id (url-param "i")
	 */	
	public function manage_tags($mode, $u_action, $id) {
		$action = $this->request->variable('action', '');
		switch ($action)
		{
			case 'delete':
				$this->handle_delete($mode, $u_action, $id);
			break;
			case 'edit':
				$this->handle_edit($u_action);
			break;
			default:
				// show all tags
				$start			= $this->request->variable('start', 0);
				$limit			= $this->config['topics_per_page'];
				$tags_count		= $this->tags_manager->count_tags();
				$start			= $this->pagination->validate_start($start, $limit, $tags_count);
				
				$tags = $this->tags_manager->get_all_tags($start, $limit, 'tag', true);
				$base_url		= $u_action;
				
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $tags_count, $limit, $start);
					
				foreach ($tags as $tag) {
					$this->template->assign_block_vars('tags', array(
						'NAME'			=> $tag['tag'],
						'ASSIGNMENTS'	=> $tag['count'],
						'U_DELETE_TAG'	=> $this->get_tag_link($u_action, $tag['id']) . '&amp;action=delete',
						// TODO none-ajax 'U_EDIT_TAG_URL'	=> $this->get_tag_link($u_action, $tag['id']) . '&amp;action=edit',
					));
				}
		}
	}

	/**
	 * 
	 * @param string $mode phpbb acp-mode
	 * @param string $u_action phpbb acp-u_action
	 * @param string $id
	 */
	private function handle_delete($mode, $u_action, $id)
	{
		$tag_id = $this->request->variable('tag_id', -1);
		if ($tag_id < 1)
		{
			if ($this->request->is_ajax())
			{
				trigger_error('TOPICTAGS_MISSING_TAG_ID', E_USER_WARNING);
			}
			trigger_error($this->user->lang('TOPICTAGS_MISSING_TAG_ID') . adm_back_link($u_action), E_USER_WARNING);
		}
		
		$tag = $this->tags_manager->get_tag_by_id($tag_id);
		
		if (confirm_box(true))
		{
			$this->tags_manager->delete_tag($tag_id);
				
			if ($this->request->is_ajax())
			{
				trigger_error('TOPICTAGS_TAG_DELETED');
			}
			//trigger_error($this->user->lang['YES'] . adm_back_link($u_action));
			trigger_error($this->user->lang('TOPICTAGS_TAG_DELETED') . adm_back_link($u_action));
		}
		else
		{
			$confirm_text = $this->user->lang('TOPICTAGS_TAG_DELETE_CONFIRM', $tag);
			confirm_box(false, $confirm_text, build_hidden_fields(array(
			'i'			=> $id,
			'mode'		=> $mode,
			'action'	=> 'delete',
			'tag_id'	=> $tag_id,
			)));
		}
	}
	
	/**
	 *
	 * @param string $u_action phpbb acp-u_action
	 */
	private function handle_edit($u_action)
	{
		// TODO none-ajax
		$old_tag_name = $this->request->variable('old_tag_name', '');
		$new_tag_name = $this->request->variable('new_tag_name', '');
		if (empty($old_tag_name) || empty($new_tag_name))
		{
			trigger_error($this->user->lang('TOPICTAGS_MISSING_TAG_NAMES') . adm_back_link($u_action), E_USER_WARNING);
		}
		else
		{
			$old_tag_name = rawurldecode(base64_decode($old_tag_name));
			$new_tag_name = rawurldecode(base64_decode($new_tag_name));
			if ($old_tag_name == $new_tag_name) {
				$error_msg = $this->user->lang('TOPICTAGS_NO_MODIFICATION', $old_tag_name);
				$response = new json_response();
				$response->send(array(
					'success'	=> false,
					'error_msg'	=> rawurlencode(base64_encode($error_msg)),
				));
				trigger_error($error_msg . adm_back_link($u_action), E_USER_WARNING);
			}
			$old_ids = $this->tags_manager->get_existing_tags(array($old_tag_name), true);
			if (empty($old_ids))
			{
				$error_msg = $this->user->lang('TOPICTAGS_TAG_DOES_NOT_EXIST', $old_tag_name);
				if ($this->request->is_ajax())
				{
					$response = new json_response();
					$response->send(array(
						'success'	=> false,
						'error_msg'	=> rawurlencode(base64_encode($error_msg)),
					));
				}
				trigger_error($error_msg . adm_back_link($u_action), E_USER_WARNING);
			}
			// if we reach here, we know that we got a single valid old tag
			$old_id = $old_ids[0];
				
			$new_tag_name_clean = $this->tags_manager->clean_tag($new_tag_name);
			$is_valid = $this->tags_manager->is_valid_tag($new_tag_name_clean, true);
			if (!$is_valid)
			{
				$error_msg = $this->user->lang('TOPICTAGS_TAG_INVALID', $new_tag_name);
				if ($this->request->is_ajax())
				{
					$response = new json_response();
					$response->send(array(
						'success'	=> false,
						'error_msg'	=> rawurlencode(base64_encode($error_msg)),
					));
				}
				trigger_error($error_msg . adm_back_link($u_action), E_USER_WARNING);
			}
				
			// old tag exist and new tag is valid
			$new_ids = $this->tags_manager->get_existing_tags(array($new_tag_name), true);
			if (!empty($new_ids))
			{
				// new tag exist -> merge
				$new_id = $new_ids[0];
				$new_tag_count = $this->tags_manager->merge($old_tag_name, $old_id, $new_tag_name, $new_id);
				if ($this->request->is_ajax())
				{
					$response = new json_response();
					$response->send(array(
						'success'		=> true,
						'merged'		=> true,
						'new_tag_count'	=> $new_tag_count,
						'msg'			=> rawurlencode(base64_encode($this->user->lang('TOPICTAGS_TAG_MERGED', $new_tag_name_clean))),
					));
				}
				trigger_error($this->user->lang('TOPICTAGS_TAG_MERGED', $new_tag_name_clean) . adm_back_link($u_action));
			}
				
			// old tag exist and new tag is valid and does not exist -> rename it
			$tag_count = $this->tags_manager->rename($old_id, $new_tag_name_clean);
			if ($this->request->is_ajax())
			{
				$response = new json_response();
				$response->send(array(
					'success'	=> true,
					'msg'		=> rawurlencode(base64_encode($this->user->lang('TOPICTAGS_TAG_CHANGED'))),
				));
			}
			trigger_error($this->user->lang('TOPICTAGS_TAG_CHANGED') . adm_back_link($u_action));
		}
	}
	
	private function get_tag_link($u_action, $tag_id)
	{
		return $u_action . (($tag_id) ? '&amp;tag_id=' . $tag_id : '');
	}
	
}