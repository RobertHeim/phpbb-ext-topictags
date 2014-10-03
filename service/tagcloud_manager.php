<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\service;

/**
 * @ignore
 */
use robertheim\topictags\TABLES;
use robertheim\topictags\PREFIXES;

class tagcloud_manager
{
	private $db;
	private $config;
	private $template;
	private $helper;
	private $table_prefix;

	public function __construct(
					\phpbb\db\driver\driver_interface $db,
					\phpbb\config\config $config,
					\phpbb\template\template $template,
					\phpbb\controller\helper $helper,
					$table_prefix
	)
	{
		$this->db			= $db;
		$this->config		= $config;
		$this->template		= $template;
		$this->helper		= $helper;
		$this->table_prefix	= $table_prefix;
	}

	/**
	 * Assigns all required data for the tag cloud to the template so that including tagcloud.html can display the tag cloud.
	 */
	public function assign_tagcloud_to_template()
	{
		// ensure that the css for the tag cloud will be included
        $this->template->assign_vars(array(
			'S_RH_TOPICTAGS_INCLUDE_CSS'		=> true,
			'RH_TOPICTAGS_TAGCLOUD_SHOW_COUNT'	=> $this->config[PREFIXES::CONFIG . '_display_tagcount_in_tagcloud'],
		));

		// get the data
		$maximum = $this->get_maximum_tag_usage_count();
		$tags = $this->get_top_tags($this->config[PREFIXES::CONFIG . '_max_tags_in_tagcloud']);

		// display it
		foreach ($tags as $tag)
		{
			$css_class = $this->get_css_class($tag['count'], $maximum);
			$link = $this->helper->route('robertheim_topictags_show_tag_controller', array(
						'tags'	=> $tag['tag']
					));

			$this->template->assign_block_vars('rh_topictags_tags', array(
				'NAME'		=> $tag['tag'],
				'LINK'		=> $link,
				'CSS_CLASS'	=> $css_class,
				'COUNT'		=> $tag['count'],
			));
		}
	}

	/**
	 * Gets the $limit most used tags.
	 *
	 * @param $limit max results
	 * @return array (array('tag' => string, 'count' => int), ...)
	 */
	public function get_top_tags($limit)
	{
		$sql_array = array(
			'SELECT'	=> 't.tag, t.count',
			'FROM'		=> array(
				$this->table_prefix . TABLES::TAGS  => 't'
			),
			'ORDER_BY'	=> 't.count DESC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, (int) $limit);
        while ($row = $this->db->sql_fetchrow($result))
		{
			$tags[] = array(
				'tag'	=> $row['tag'],
				'count'	=> $row['count']
			);
		}
		return $tags;
	}

	/**
	 * Get the usage count of the tag that is used the most
	 *
	 * @return int maximum
	 */
	private function get_maximum_tag_usage_count()
	{
		$sql_array = array(
			'SELECT'	=> 't.count',
			'FROM'		=> array(
				$this->table_prefix . TABLES::TAGS  => 't'
			),
			'ORDER_BY'	=> 't.count DESC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, 1);
		return (int) $this->db->sql_fetchfield('count');
	}

	/**
	 * Determines the size of the tag depending on its usage count
	 *
	 * @param $count the count of usages of a tag
	 * @param $maximum the usage-count of the most used tag
	 * @return string the css class name
	 */
	private function get_css_class($count, $maximum)
	{
		$percent = floor(($count / $maximum) * 100);
		if ($percent < 20)
		{
			return 'rh_topictags_smallest';
		}
		elseif ($percent >= 20 and $percent < 40)
		{
			return 'rh_topictags_small'; 
		}
		elseif ($percent >= 40 and $percent < 60)
		{
			return 'rh_topictags_medium';
		}
		elseif ($percent >= 60 and $percent < 80)
		{
			return 'rh_topictags_large';
		}
		else
		{
			return 'rh_topictags_largest';
		}
	}
}
