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
 * @ignore
 */
use phpbb\json_response;

class main
{

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\event\dispatcher */
	protected $phpbb_dispatcher;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $phpbb_root_path;

	/** @var \robertheim\topictags\service\tags_manager */
	protected $tags_manager;

	/** @var \robertheim\topictags\service\tagcloud_manager */
	protected $tagcloud_manager;

	/**
	 * Constructor
	 */
	public function __construct(
						\phpbb\config\config $config,
						\phpbb\template\template $template,
						\phpbb\controller\helper $helper,
						\phpbb\request\request $request,
						\phpbb\user $user,
						\phpbb\auth\auth $auth,
						\phpbb\event\dispatcher $phpbb_dispatcher,
						\phpbb\pagination $pagination,
						\phpbb\content_visibility $content_visibility,
						$php_ext,
						$phpbb_root_path,
						\robertheim\topictags\service\tags_manager $tags_manager,
						\robertheim\topictags\service\tagcloud_manager $tagcloud_manager
	)
	{
		$this->config = $config;
		$this->template = $template;
		$this->helper = $helper;
		$this->request = $request;
		$this->user = $user;
		$this->auth = $auth;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->pagination = $pagination;
		$this->content_visibility = $content_visibility;
		$this->php_ext = $php_ext;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->tags_manager = $tags_manager;
		$this->tagcloud_manager = $tagcloud_manager;
	}

	/**
	 * Controller for route /tags
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function show()
	{
		$this->tagcloud_manager->assign_tagcloud_to_template(-1);
		return $this->helper->render('tags.html', $this->user->lang('RH_TOPICTAGS'));
	}

	/**
	 * Shows a list of topics that have the given $tags assigned
	 *
	 * @param $tags tags seperated by comma (",")
	 * @param $mode the mode indicates whether all tags (AND, default) or any tag (OR) should be assigned to the resulting topics
	 * @param casesensitive wether to search case-sensitive (true) or -insensitive (false, default)
	 */
	public function show_tag($tags, $mode, $casesensitive)
	{
		// validate mode
		// default == AND
		$mode = ($mode == 'OR' ? 'OR' : 'AND');

		$tags = explode(',', urldecode($tags));
		// remove possible duplicates
		$tags = array_unique($tags);
		$all_tags = $this->tags_manager->split_valid_tags($tags);

		if (sizeof($all_tags['invalid']) > 0)
		{
			$this->template->assign_var('RH_TOPICTAGS_SEARCH_IGNORED_TAGS',
				$this->user->lang('RH_TOPICTAGS_SEARCH_IGNORED_TAGS', join(', ', $all_tags['invalid']))
			);
		}

		$tags = $all_tags['valid'];
		$tags_string = join(', ', $tags);
		$this->template->assign_var('RH_TOPICTAGS_SEARCH_HEADER',
			$this->user->lang('RH_TOPICTAGS_SEARCH_HEADER_' . $mode, $tags_string)
		);
		if (empty($tags))
		{
			// no valid tags
			$this->template->assign_var('NO_TOPICS_FOR_TAG', $this->user->lang('RH_TOPICTAGS_NO_TOPICS_FOR_NO_TAG'));
			return $this->helper->render('show_tag.html', $this->user->lang('RH_TOPICTAGS_TAG_SEARCH'));
		}

		$topics_count	= $this->tags_manager->count_topics_by_tags($tags, $mode, $casesensitive);
		if ($topics_count <= 0)
		{
			$this->template->assign_var('NO_TOPICS_FOR_TAG', $this->user->lang('RH_TOPICTAGS_NO_TOPICS_FOR_TAG_'.$mode,
				$tags_string));
		}
		else
		{
			$pagination		= $this->pagination;

			$start			= $this->request->variable('start', 0);
			$limit			= $this->config['topics_per_page'];

			$start			= $pagination->validate_start($start, $limit, $topics_count);

			$topics			= $this->tags_manager->get_topics_by_tags($tags, $start, $limit, $mode, $casesensitive);

			$base_url		= $this->helper->route('robertheim_topictags_show_tag_controller', array(
				'tags'	=> urlencode($tags_string),
			));
			$base_url		= append_sid($base_url);

			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $topics_count, $limit, $start);

			$this->user->add_lang('viewforum');

			$this->template->assign_vars(array(
				'TOTAL_TOPICS'				=> $this->user->lang('VIEW_FORUM_TOPICS', $topics_count),
				'NEWEST_POST_IMG'			=> $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
				'LAST_POST_IMG'				=> $this->user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
				'REPORTED_IMG'				=> $this->user->img('icon_topic_reported', 'TOPIC_REPORTED'),
				'UNAPPROVED_IMG'			=> $this->user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
				'DELETED_IMG'				=> $this->user->img('icon_topic_deleted', 'TOPIC_DELETED'),
				'POLL_IMG'					=> $this->user->img('icon_topic_poll', 'TOPIC_POLL'),
				'S_TOPIC_ICONS'				=> true,
			));

			$this->display_topics($topics);
		} // else
		return $this->helper->render('show_tag.html', $this->user->lang('RH_TOPICTAGS_TAG_SEARCH'));
	}

	/**
	 * Generates all the data in the template to show the topics list.
	 *
	 * @param array $topics the topics to display
	 */
	private function display_topics($topics)
	{
		$pagination = $this->pagination;

		foreach ($topics as $t)
		{
			$topic = new topic($t, $this->user, $this->auth, $this->content_visibility, $this->phpbb_root_path, $this->php_ext);

			// Send vars to template
			$topic_row = array(
				'FORUM_ID'					=> $topic->forum_id(),
				'TOPIC_ID'					=> $topic->topic_id(),
				'TOPIC_AUTHOR'				=> $topic->author('username'),
				'TOPIC_AUTHOR_COLOUR'		=> $topic->author('colour'),
				'TOPIC_AUTHOR_FULL'			=> $topic->author('full'),
				'FIRST_POST_TIME'			=> $topic->topic_time(),
				'LAST_POST_SUBJECT'			=> $topic->last_post_subject(),
				'LAST_POST_TIME'			=> $topic->last_post_time(),
				'LAST_VIEW_TIME'			=> $topic->last_view_time(),
				'LAST_POST_AUTHOR'			=> $topic->last_author('username'),
				'LAST_POST_AUTHOR_COLOUR'	=> $topic->last_author('colour'),
				'LAST_POST_AUTHOR_FULL'		=> $topic->last_author('full'),

				'REPLIES'			=> $topic->replies(),
				'VIEWS'				=> $topic->views(),
				'TOPIC_TITLE'		=> $topic->topic_title(),
				'TOPIC_TYPE'		=> $topic->topic_type(),
				'FORUM_NAME'		=> $topic->forum_name(),

				'TOPIC_IMG_STYLE'		=> $topic->img_style(),
				'TOPIC_FOLDER_IMG'		=> $topic->folder_img(),
				'TOPIC_FOLDER_IMG_ALT'	=> $topic->folder_img_alt(),

				// not supported by RH Topic Tags
				'TOPIC_ICON_IMG'		=> '',
				'TOPIC_ICON_IMG_WIDTH'	=> '',
				'TOPIC_ICON_IMG_HEIGHT'	=> '',

				'ATTACH_ICON_IMG'		=> $topic->attach_icon_img(),
				'UNAPPROVED_IMG'		=> $topic->unapproved_img(),

				'S_TOPIC_TYPE'			=> $topic->topic_type(),
				'S_USER_POSTED'			=> $topic->user_posted(),
				'S_UNREAD_TOPIC'		=> $topic->unread_topic(),
				'S_TOPIC_REPORTED'		=> $topic->topic_reported(),
				'S_TOPIC_UNAPPROVED'	=> $topic->topic_unapproved(),
				'S_POSTS_UNAPPROVED'	=> $topic->posts_unapproved(),
				'S_TOPIC_DELETED'		=> $topic->topic_deleted(),
				'S_HAS_POLL'			=> $topic->has_poll(),
				'S_POST_ANNOUNCE'		=> $topic->post_announce(),
				'S_POST_GLOBAL'			=> $topic->post_global(),
				'S_POST_STICKY'			=> $topic->post_sticky(),
				'S_TOPIC_LOCKED'		=> $topic->locked(),
				'S_TOPIC_MOVED'			=> $topic->moved(),

				'U_NEWEST_POST'			=> $topic->newest_post_url(),
				'U_LAST_POST'			=> $topic->last_post_url(),
				'U_LAST_POST_AUTHOR'	=> $topic->last_post_author(),
				'U_TOPIC_AUTHOR'		=> $topic->topic_author(),
				'U_VIEW_TOPIC'			=> $topic->view_topic_url(),
				'U_MCP_REPORT'			=> $topic->mcp_report(),
				'U_MCP_QUEUE'			=> $topic->u_mcp_queue(),

				'S_TOPIC_TYPE_SWITCH'	=> $topic->topic_type_switch(),
			);

			// create row for event so it is simmilar to the core.viewforum_modify_topicrow
			$row = $t;
			/**
			 * Modify the topic data before it is assigned to the template
			 *
			 * @event robertheim.topictags.viewforum_modify_topicrow
			 * @var	array	row			Array with topic data
			 * @var	array	topic_row	Template array with topic data
			 * @since 0.0.13-b1
			*/
			$vars = array('row', 'topic_row');
			extract($this->phpbb_dispatcher->trigger_event('robertheim.topictags.viewforum_modify_topicrow', compact($vars)));

			$this->template->assign_block_vars('topicrow', $topic_row);

			// mini pagination of posts in topic-rowss
			$pagination->generate_template_pagination($topic->view_topic_url(), 'topicrow.pagination', 'start', $topic->replies() + 1, $this->config['posts_per_page'], 1, true, true);
		} // foreach
	}

	/**
	 * Gets suggestions for tags based on a ajax request, route: /tags/suggest
	 *
	 * @param php://input raw post data must contain a json-encoded object of this structure: {"query":"...", "exclude":["...", "...", ...]}
	 */
	public function suggest_tags()
	{
		if ($this->request->is_ajax())
		{
			$data = json_decode(file_get_contents('php://input'), true);
			$query = $data['query'];
			$exclude = $data['exclude'];
			$tags = $this->tags_manager->get_tag_suggestions($query, $exclude, 5);
			$json_response = new json_response();
			$json_response->send($tags);
		}
		// fake a 404
		return $this->helper->error($this->user->lang('RH_TOPICTAGS_TAG_SUGGEST_TAG_ROUTE_ERROR', $this->helper->get_current_url()) , 404);
	}
}
