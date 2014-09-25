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
class acp_listener implements EventSubscriberInterface
{

	static public function getSubscribedEvents()
	{
		return array(
            'core.acp_manage_forums_display_form'			=> 'acp_manage_forums_display_form',
            'core.acp_manage_forums_validate_data'			=> 'acp_manage_forums_validate_data',
		);
	}

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\request\request_interface $request
	)
	{
        $this->request	= $request;
	}


    public function acp_manage_forums_display_form($event) {
		$data = $event->get_data();
		$status = $data['forum_data']['rh_topictags_enabled'];
		$data['template_data']['S_RH_TOPICTAGS_ENABLED'] = $status;
		$event->set_data($data);
    }

    public function acp_manage_forums_validate_data($event) {
		global $request;
		$data = $event->get_data();

		$post = $this->request->get_super_global(\phpbb\request\request::POST);
		$status = isset($post['rh_topictags_enabled']) ? $post['rh_topictags_enabled'] : 0;
		$data['forum_data']['rh_topictags_enabled'] = ($status !=0 ? 1 : 0);

		$event->set_data($data);
    }

}

