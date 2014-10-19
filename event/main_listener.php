<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use robertheim\topictags\PREFIXES;
use robertheim\topictags\PERMISSIONS;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'								=> 'load_language_on_setup',
			'core.index_modify_page_title'					=> 'index_modify_page_title',
			'core.modify_posting_parameters'				=> 'modify_posting_parameters',
			'core.posting_modify_template_vars'				=> 'posting_modify_template_vars',
			'core.viewforum_modify_topicrow'				=> 'viewforum_modify_topicrow',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_assign_template_vars_before',
			'core.submit_post_end'							=> 'submit_post_end',
		);
	}

	protected $config;

	protected $tags_manager;

	protected $helper;

	protected $request;

	protected $user;

	protected $template;

	protected $auth;

	protected $tagcloud_manager;

	/**
	 * Constructor
	 */
	public function __construct(
							\phpbb\config\config $config,
							\robertheim\topictags\service\tags_manager $tags_manager,
							\phpbb\controller\helper $helper,
							\phpbb\request\request $request, 
							\phpbb\user $user, 
							\phpbb\template\template $template,
							\phpbb\auth\auth $auth,
							\robertheim\topictags\service\tagcloud_manager $tagcloud_manager
	)
	{
		$this->config = $config;
		$this->tags_manager = $tags_manager;
		$this->helper = $helper;
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->auth = $auth;
		$this->tagcloud_manager = $tagcloud_manager;
	}

	/**
	 * Reads all tags from request variable 'rh_topictags' and splits them by the separator (default: comma (',')) and trims them.
	 * NOTE: These tags might be dirty!
	 * 
	 * @return array of dirty tags
	 */
	private function get_tags_from_post_request()
	{
		$tags_string = utf8_normalize_nfc($this->request->variable('rh_topictags', '', true));
		$tags_string = base64_decode($tags_string);
		$tags_string = utf8_encode($tags_string);
		if ('' === $tags_string) {
			return array();
		}

		$tagsJson = json_decode($tags_string, true);
		$tags = array();
		for ($i = 0, $count = sizeof($tagsJson); $i<$count; $i++)
		{
			$tags[] = trim($tagsJson[$i]['text']);
		}

		return $tags;
	}

	/**
     * Event: core.load_language_on_setup
	 */
    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'robertheim/topictags',
            'lang_set' => 'topictags',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

	/**
     * Event: core.index_modify_page_title
	 */
    public function index_modify_page_title($event)
    {
		if ($this->config[PREFIXES::CONFIG . '_display_tagcloud_on_index'])
		{
        	$this->template->assign_vars(array(
				'RH_TOPICTAGS_DISPLAY_TAGCLOUD_ON_INDEX'	=> true,
			));
			$this->tagcloud_manager->assign_tagcloud_to_template();
		}
    }

	/**
     * Event: core.modify_posting_parameters
     * 
	 * Validate the tags and create an error if any tag is invalid.
	 */
	public function modify_posting_parameters($event)
	{
		if ($this->auth->acl_get(PERMISSIONS::USE_TAGS, PERMISSIONS::ADMIN_EDIT_TAGS, PERMISSIONS::MOD_EDIT_TAGS))
		{

			$data = $event->get_data();
			$tags = $this->get_tags_from_post_request();

			$all_tags = $this->tags_manager->split_valid_tags($tags);
			$invalid_tags = $all_tags['invalid'];

			if (sizeof($invalid_tags))
			{
				$this->user->add_lang_ext('robertheim/topictags', 'topictags');
				$data['error'][] = $this->user->lang('RH_TOPICTAGS_TAGS_INVALID', join(', ', $invalid_tags));
			}

			$event->set_data($data);
		}
	}

	/**
     * Event: core.postingsubmit_post_end
     * 
	 * After a posting we assign the tags to the topic
	 */
	public function submit_post_end($event)
	{
		if ($this->auth->acl_get(PERMISSIONS::USE_TAGS, PERMISSIONS::ADMIN_EDIT_TAGS, PERMISSIONS::MOD_EDIT_TAGS))
		{
	        $event_data = $event->get_data();
        	$data = $event_data['data'];

			$tags = $this->get_tags_from_post_request();
			$all_tags = $this->tags_manager->split_valid_tags($tags);
			$valid_tags = $all_tags['valid'];
			if (!empty($valid_tags))
			{
				$this->tags_manager->assign_tags_to_topic($data['topic_id'], $valid_tags);
		        $event->set_data($event_data);
			}
		}
    }

    /**
     * Event: core.posting_modify_template_vars
     *
     * Send the tags on edits or preview to the template
     *
     * @param $event
     */
    public function posting_modify_template_vars($event)
	{
		if ($this->auth->acl_get(PERMISSIONS::USE_TAGS, PERMISSIONS::ADMIN_EDIT_TAGS, PERMISSIONS::MOD_EDIT_TAGS))
		{
			$data = $event->get_data();
			$forum_id = $data['forum_id'];

			if (!$this->tags_manager->is_tagging_enabled_in_forum($forum_id))
			{
				return;
			}

	        $mode = $enable_trader = $topic_id = $post_id = $topic_first_post_id = false;

	        if (!empty($data['mode'])) {
            	$mode = $data['mode'];
	        }

    	    if ($mode == 'reply') {
	            return;
	        }

    	    if (!empty($data['post_data']['topic_id'])) {
	            $topic_id = $data['post_data']['topic_id'];
	        }

	        if (!empty($data['post_data']['post_id'])) {
	            $post_id = $data['post_data']['post_id'];
    	    }

    	    if (!empty($data['post_data']['topic_first_post_id'])) {
    	        $topic_first_post_id = $data['post_data']['topic_first_post_id'];
			    }

			$is_new_topic = $mode == 'post';
			$is_edit_first_post = $mode == 'edit' && $topic_id && $post_id && $post_id == $topic_first_post_id;
			if ($is_new_topic || $is_edit_first_post) {

		        $data['page_data']['RH_TOPICTAGS_SHOW_FIELD'] = true;

				// do we got some preview-data?
				$tags = array();
				if ($this->request->is_set_post('rh_topictags')) {
					// use data from post-request
					$tags = $this->get_tags_from_post_request();
				} else if ($is_edit_first_post) {
					// use data from db
					$tags = $this->tags_manager->get_assigned_tags($topic_id);
				}

				$data['page_data']['RH_TOPICTAGS'] = base64_encode(json_encode($tags));

				$data['page_data']['RH_TOPICTAGS_ALLOWED_TAGS_REGEX'] = $this->config[PREFIXES::CONFIG.'_allowed_tags_regex'];
				$data['page_data']['RH_TOPICTAGS_CONVERT_SPACE_TO_MINUS'] = $this->config[PREFIXES::CONFIG.'_convert_space_to_minus'] ? 'true' : 'false';

		        if ($this->config[PREFIXES::CONFIG.'_whitelist_enabled'])
				{
					$data['page_data']['RH_TOPICTAGS_WHITELIST_ENABLED'] = true;
					$data['page_data']['RH_TOPICTAGS_ALLOWED_TAGS_EXP'] = $this->config[PREFIXES::CONFIG.'_whitelist'];
				}
				else
				{
					$data['page_data']['RH_TOPICTAGS_ALLOWED_TAGS_EXP'] = $this->config[PREFIXES::CONFIG.'_allowed_tags_exp_for_users'];
				}
				$data['page_data']['S_RH_TOPICTAGS_INCLUDE_NG_TAGS_INPUT'] = true;
				$data['page_data']['S_RH_TOPICTAGS_INCLUDE_CSS'] = true;

				$event->set_data($data);
			}
		}
    }

	/**
	 * Event: core.viewforum_modify_topicrow
	 *
	 * Get and assign tags to topic-row-template -> RH_TOPICTAGS_TAGS.
	 *
	 * Note that we assign a string which includes the a-href-links already,
	 * because we cannot assign sub-blocks before the outer-block with
	 * assign_block_vars(...) and the event is before the actual assignment.
	 *
	 * @param $event
	 */
	public function viewforum_modify_topicrow($event)
	{
		if ($this->config[PREFIXES::CONFIG.'_display_tags_in_viewforum'])
		{
	        $data = $event->get_data();
			$topic_id = (int) $data['row']['topic_id'];
			$forum_id = (int) $data['row']['forum_id'];

			if ($this->tags_manager->is_tagging_enabled_in_forum($forum_id))
			{
				$tags = $this->tags_manager->get_assigned_tags($topic_id);
				if (!empty($tags))
				{
					// we cannot use assign_block_vars('topicrow.tags', ...) here, because the block 'topicrow' is not yet assigned
					// add links
					$tpl_tags = array();
					foreach ($tags as $tag)
					{
						$this->template->assign_block_vars('rh_tags_tmp', array (
							'NAME'	=> $tag,
							'LINK'	=> $this->helper->route('robertheim_topictags_show_tag_controller', array(
											'tags'	=> urlencode($tag),
										)),
						));
					}

					// small_tag.html might want to use our extension's css.
					$this->template->assign_var('S_RH_TOPICTAGS_INCLUDE_CSS', true);
					// we cannot just use 'small_tag.html' because in viewforum.php twig only searches in phpbb_root/styles/prosilver/template,
					// but we need a template from our extension.
					$rendered_tags = $this->template->assign_display('./../../../ext/robertheim/topictags/styles/all/template/small_tag.html');
					// remove temporary data
					$this->template->destroy_block_vars('rh_tags_tmp');

					// assign the template data
					$data['topic_row']['RH_TOPICTAGS_TAGS'] = $rendered_tags;
			
					$event->set_data($data);
				}
			}
		}
	}

	/**
	 * Event: core.viewtopic_assign_template_vars_before
	 *
	 * assign tags to topic-template and header-meta
	 *
	 * @param $event
	 */
	public function viewtopic_assign_template_vars_before($event)
	{
        $data = $event->get_data();
		$topic_id = (int) $data['topic_id'];
		$forum_id = (int) $data['forum_id'];

		if ($this->tags_manager->is_tagging_enabled_in_forum($forum_id))
		{
			$tags = $this->tags_manager->get_assigned_tags($topic_id);
			if (!empty($tags))
			{
				foreach ($tags as $tag) {
			        $this->template->assign_block_vars('rh_topic_tags', array(
						'NAME' => $tag,
						'LINK' => $this->helper->route('robertheim_topictags_show_tag_controller', array(
							'tags'	=> urlencode($tag)
						)),
					));
				}
	
				$this->template->assign_vars(array(
					'RH_TOPICTAGS_SHOW'	=> true,
					'META'				=> '<meta name="keywords" content="' . join(', ', $tags) . '">',
				));
				// tags might want to use our extension's css.
				$this->template->assign_var('S_RH_TOPICTAGS_INCLUDE_CSS', true);
			}
		}
	}
}
