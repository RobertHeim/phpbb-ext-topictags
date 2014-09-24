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

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'								=> 'load_language_on_setup',
			'core.posting_modify_template_vars'				=> 'posting_modify_template_vars',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_assign_template_vars_before',
			'core.submit_post_end'							=> 'submit_post_end',
		);
	}

	protected $tags_manager;

	/**
	 * Constructor
	 */
	public function __construct(
							\robertheim\topictags\service\tags_manager $tags_manager
	)
	{
		$this->tags_manager = $tags_manager;
	}

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'robertheim/topictags',
            'lang_set' => 'topictags',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

	private function get_clean_tags_from_post_request() {
		global $request;
        $post = $request->get_super_global(\phpbb\request\request::POST);

		$tags_string = $post['rh_topictags'];
		$tags = explode(',', $tags_string);

		$clean_tags = array();
		for ($i = 0; $i < sizeof($tags); $i++) {
			$tag = $this->tags_manager->clean_tag($tags[$i]);
			if (!empty($tag))
			{
				$clean_tags[] = $tag;
			} 
		}

		return $clean_tags;
	}

	public function submit_post_end($event) {
        $event_data = $event->get_data();
        $data = $event_data['data'];
		$tags = $this->get_clean_tags_from_post_request();
		$this->tags_manager->assign_tags_to_topic($data['topic_id'], $tags);
        $event->set_data($event_data);
    }

    /**
     * Event: core.posting_modify_template_vars
     *
     * Send the tags on edits or preview
     *
     * @param $event
     */
    public function posting_modify_template_vars($event) {
        $mode = $enable_trader = $topic_id = $post_id = $topic_first_post_id = false;

        $data = $event->get_data();

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
			global $request;
	        $_post = $request->get_super_global(\phpbb\request\request::POST);
			// do we got some preview-data?
			$tags = array();
			if (isset($_post['rh_topictags'])) {
				// use data from post-request
				$tags = $this->get_clean_tags_from_post_request();
			} elseif ($is_edit_first_post) {
				// use data from db
				$tags = $this->tags_manager->get_assigned_tags($topic_id);
			}
			$data['page_data']['RH_TOPICTAGS'] = join(", ", $tags);
			// display tags
            $data['page_data']['RH_TOPICTAGS_SHOW_FIELD'] = true;
			$event->set_data($data);
		}
    }

	/**
	 * Event: core.viewtopic_assign_template_vars_before
	 *
	 * Send the tags on edits or preview
	 *
	 * @param $event
	 */
	public function viewtopic_assign_template_vars_before($event)
	{
        global $template;
        $data = $event->get_data();
		$topic_id = $data['topic_id'];

		$tags = $this->tags_manager->get_assigned_tags($topic_id);
		$show_tags = !empty($tags);
		if ($show_tags) {
			$tpl_tags = array();
			foreach ($tags as $tag) {
		        $template->assign_block_vars('rh_topic_tags', array(
					'NAME' => $tag,
					'LINK' => "TODO",
				));
			}
			$template->assign_var('RH_TOPICTAGS_SHOW', $show_tags);
		}
	}
}
