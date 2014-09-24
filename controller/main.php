<?php
/**
 *
 * @package phpBB Extension - RH Topic Tags
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace robertheim\topictags\controller;

class main
{

	protected $template;

	protected $helper;

	protected $tags_manager;

	/**
	 * Constructor
	 */
	public function __construct(
						\phpbb\template\template $template,
						\phpbb\controller\helper $helper,
						\robertheim\topictags\service\tags_manager $tags_manager
	)
	{
		$this->template = $template;
		$this->helper = $helper;
		$this->tags_manager = $tags_manager;
	}

	/**
	 * Demo controller for route /tags
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function show()
	{
		$tags = $this->tags_manager->get_existing_tags();
		foreach ($tags as $tag)
		{
			$this->template->assign_block_vars('tags', array(
				'NAME'	=> $tag['tag'],
				'LINK'	=> $this->helper->route('robertheim_topictags_show_tag_controller', array(
					'tag'	=> $tag['tag']
					)),
			));
		}
		return $this->helper->render('tags.html');
	}

	public function show_tag($tag)
	{
		global $phpbb_root_path, $phpEx;
		$tag = $this->tags_manager->clean_tag($tag);
		$this->template->assign_var('TAG', $tag);
		$topics = $this->tags_manager->get_topics_by_tag($tag, true);
		foreach ($topics as $topic)
		{
			$view_topic_url_params = 'f=' . $topic['forum_id'] . '&amp;t=' . $topic['topic_id'];
			$view_topic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params);

			$this->template->assign_block_vars('topics', array(
				'TITLE'		=> $topic['topic_title'],
				'POSTER'	=> $topic['topic_poster'],
				'LINK'		=> $view_topic_url,
			));
		
		}
		return $this->helper->render('show_tag.html');
	}

}
