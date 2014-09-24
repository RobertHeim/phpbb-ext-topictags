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
				'LINK'	=> 'TODO',
			));
		}
		return $this->helper->render('tags.html');
	}
}
