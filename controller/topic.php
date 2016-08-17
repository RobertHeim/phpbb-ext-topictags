<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\controller;

/**
* Represents a topic.
*/
class topic
{
	/** @var array */
	private $topic_row;

	/** @var \phpbb\user */
	private $user;

	/** @var \phpbb\auth\auth */
	private $auth;

	/** @var string */
	private $phpbb_root_path;

	/** @var string */
	private $php_ext;

	/** @var int */
	private $topic_id;

	// unread_topic should be based on the users topic tracking info
	// this wont be supported until phpbb code is less complex
	/** @var boolean */
	private $unread_topic = false;

	/** @var string */
	private $folder_img = '';
	/** @var string */
	private $folder_alt = '';
	/** @var string */
	private $topic_type = '';

	/** @var int */
	private $forum_id;

	/** @var mixed */
	private $replies;

	/** @var string */
	private $view_topic_url_params;
	/** @var string */
	private $view_topic_url;

	/** @var string */
	private $newest_post_url;
	/** @var string */
	private $last_post_url;

	/** @var boolean */
	private $topic_unapproved;
	/** @var boolean */
	private $posts_unapproved;
	/** @var boolean */
	private $topic_deleted;

	/**
	 *
	 * @param array $topic_row a db topic row
	 */
	public function __construct(array $topic_row,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\content_visibility $phpbb_content_visibility,
		$phpbb_root_path,
		$php_ext)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->topic_row = $topic_row;
		if ($topic_row['topic_status'] == ITEM_MOVED)
		{
			$this->topic_id = $topic_row['topic_moved_id'];
		}
		else
		{
			$this->topic_id = $topic_row['topic_id'];
		}
		$this->forum_id = (int) $this->topic_row['forum_id'];

		$this->view_topic_url_params = 'f=' . $this->forum_id . '&amp;t=' . $this->topic_id;
		$this->view_topic_url	= append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", $this->view_topic_url_params);
		$this->newest_post_url	= append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", $this->view_topic_url_params . '&amp;view=unread') . '#unread';
		$this->last_post_url	= append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", $this->view_topic_url_params . '&amp;p=' . $topic_row['topic_last_post_id']) . '#p' . $topic_row['topic_last_post_id'];

		$this->replies = $phpbb_content_visibility->get_count('topic_posts', $topic_row, $this->forum_id) - 1;

		if (!function_exists('topic_status'))
		{
			include ($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}
		// Get folder img, topic status/type related information
		topic_status($topic_row, $this->replies, $this->unread_topic(), $this->folder_img, $this->folder_alt, $this->topic_type);

		$this->topic_unapproved = (($topic_row['topic_visibility'] == ITEM_UNAPPROVED || $topic_row['topic_visibility'] == ITEM_REAPPROVE) && $auth->acl_get('m_approve', $this->forum_id));
		$this->posts_unapproved = ($topic_row['topic_visibility'] == ITEM_APPROVED && $topic_row['topic_posts_unapproved'] && $auth->acl_get('m_approve', $this->forum_id));
		$this->topic_deleted = $topic_row['topic_visibility'] == ITEM_DELETED;
	}

	public function user_posted()
	{
		return (isset($this->topic_row['topic_posted']) && $this->topic_row['topic_posted']) ? true : false;
	}

	public function attach_icon_img()
	{
		return ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $this->topic_row['forum_id']) && $this->topic_row['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->user->lang['TOTAL_ATTACHMENTS']) : '';
	}

	public function topic_reported()
	{
		return (!empty($this->topic_row['topic_reported']) && $this->auth->acl_get('m_report', $this->topic_row['forum_id'])) ? true : false;
	}

	public function u_mcp_queue()
	{
		$mode = '';
		if ($this->topic_unapproved)
		{
			$mode = 'approve_details';
		}
		else if ($this->posts_unapproved)
		{
			$mode = 'unapproved_posts';
		}
		else if ($this->topic_deleted)
		{
			$mode = 'deleted_topics';
		}
		else
		{
			return '';
		}
		return append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", "i=queue&amp;mode=$mode&amp;t={$this->topic_id}", true, $this->user->session_id);
	}

	public function unapproved_img()
	{
		return ($this->topic_unapproved || $this->posts_unapproved) ? $this->user->img('icon_topic_unapproved', ($this->topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '';
	}

	public function topic_deleted()
	{
		return $this->topic_deleted;
	}

	public function topic_unapproved()
	{
		return $this->topic_unapproved;
	}

	public function posts_unapproved()
	{
		return $this->posts_unapproved;
	}

	public function replies()
	{
		return $this->replies;
	}

	public function newest_post_url()
	{
		return $this->newest_post_url;
	}

	public function last_post_url()
	{
		return $this->last_post_url;
	}

	public function view_topic_url()
	{
		return $this->view_topic_url;
	}

	public function img_style()
	{
		return $this->folder_img;
	}

	public function folder_img()
	{
		return $this->user->img($this->folder_img, $this->folder_alt);
	}

	public function folder_img_alt()
	{
		return $this->user->lang[$this->folder_alt];
	}

	public function topic_type()
	{
		return $this->topic_type;
	}

	public function unread_topic()
	{
		return $this->unread_topic;
	}

	public function forum_id()
	{
		return $this->forum_id;
	}

	public function topic_id()
	{
		return $this->topic_id;
	}

	public function has_poll()
	{
		return ($this->topic_row['poll_start']) ? true : false;
	}

	public function post_announce()
	{
		return ($this->topic_row['topic_type'] == POST_ANNOUNCE) ? true : false;
	}

	public function post_global()
	{
		return ($this->topic_row['topic_type'] == POST_GLOBAL) ? true : false;
	}

	public function post_sticky()
	{
		return ($this->topic_row['topic_type'] == POST_STICKY) ? true : false;
	}

	public function locked()
	{
		return ($this->topic_row['topic_status'] == ITEM_LOCKED) ? true : false;
	}

	public function moved()
	{
		return ($this->topic_row['topic_status'] == ITEM_MOVED) ? true : false;
	}

	public function last_post_author()
	{
		return get_username_string('profile', $this->topic_row['topic_last_poster_id'], $this->topic_row['topic_last_poster_name'], $this->topic_row['topic_last_poster_colour']);
	}

	public function topic_author()
	{
		return get_username_string('profile', $this->topic_row['topic_poster'], $this->topic_row['topic_first_poster_name'], $this->topic_row['topic_first_poster_colour']);
	}

	public function mcp_report()
	{
		return append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=reports&amp;mode=reports&amp;f=' . $this->forum_id . '&amp;t=' . $this->topic_id, true, $this->user->session_id);
	}

	public function forum_name()
	{
		return (isset($this->topic_row['forum_name'])) ? $this->topic_row['forum_name'] : '';
	}

	public function topic_title()
	{
		return censor_text($this->topic_row['topic_title']);
	}

	public function views()
	{
		return $this->topic_row['topic_views'];
	}

	public function author($mode)
	{
		return get_username_string($mode, $this->topic_row['topic_poster'], $this->topic_row['topic_first_poster_name'], $this->topic_row['topic_first_poster_colour']);
	}

	public function last_author($mode)
	{
		return get_username_string($mode, $this->topic_row['topic_last_poster_id'], $this->topic_row['topic_last_poster_name'], $this->topic_row['topic_last_poster_colour']);
	}

	public function topic_time()
	{
		return $this->user->format_date($this->topic_row['topic_time']);
	}

	public function last_post_subject()
	{
		return censor_text($this->topic_row['topic_last_post_subject']);
	}

	public function last_post_time()
	{
		return $this->user->format_date($this->topic_row['topic_last_post_time']);
	}

	public function last_view_time()
	{
		return $this->user->format_date($this->topic_row['topic_last_view_time']);
	}

	/**
	 * This will allow the style designer to output a different header
	 * or even separate the list of announcements from sticky and normal topics
	 * @return number
	 */
	public function topic_type_switch()
	{
		$s_type_switch = 0;
		$s_type_switch_test = ($this->topic_row['topic_type'] == POST_ANNOUNCE || $this->topic_row['topic_type'] == POST_GLOBAL) ? 1 : 0;
		return ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test;
	}
}
