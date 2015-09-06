<?php
/**
 *
 * @package phpBB Extension - RH Topic Tags
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\topictags\tests\service;

use robertheim\topictags\prefixes;
use robertheim\topictags\tables;
use robertheim\topictags\service\tagcloud_manager;

class tagcloud_manager_test extends \phpbb_database_test_case
{
	/** @var \robertheim\topictags\service\tagcloud_manager */
	protected $tagcloud_manager;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	protected function setUp()
	{
		parent::setUp();
		global $table_prefix;
		$this->db = $this->new_dbal();
		$config = new \phpbb\config\config(
			array(
				prefixes::CONFIG . '_display_tagcount_in_tagcloud' => true,
				prefixes::CONFIG . '_max_tags_in_tagcloud' => 1,
			));
		$this->template = $this->getMock('\phpbb\template\template');

		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();

		$this->helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();

		$this->tagcloud_manager = new tagcloud_manager($this->db, $config, $this->template, $this->user, $this->helper, $table_prefix);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/tagcloud.xml');
	}

	static protected function setup_extensions()
	{
		return array(
			'robertheim/topictags'
		);
	}

	public function test_assign_tagcloud_to_template()
	{
		$limit = 1;
		$show_count = 'Display ' . $limit . ' tags.';
		$this->user->expects($this->once())
			->method('lang')
			->with('RH_TOPICTAGS_DISPLAYING_TOTAL', $limit)
			->willReturn($show_count);
		$this->template->expects($this->once())
			->method('assign_vars')
			->with($this->equalTo(
				array(
					'S_RH_TOPICTAGS_INCLUDE_CSS'		=> true,
					'RH_TOPICTAGS_TAGCLOUD_SHOW_COUNT'	=> true,
					'RH_TOPICTAGS_TAGCLOUD_TAG_COUNT'	=> $show_count,
				))
			)->willReturn(true);

		$this->template->expects($this->exactly($limit))
			->method('assign_block_vars')
			->with('rh_topictags_tags', $this->callback(function($o) {
				$diff = array_diff_assoc(array(
					'NAME' => 'tag1',
					'COUNT' => 2
				), $o);
				return sizeof($diff) == 0;
			}))
			->willReturn(true);

		$this->tagcloud_manager->assign_tagcloud_to_template($limit);
	}

	public function test_assign_tagcloud_to_template2()
	{
		$limit = 2;
		$show_count = 'Display ' . $limit . ' tags.';
		$this->user->expects($this->once())
	 		->method('lang')
	 		->with('RH_TOPICTAGS_DISPLAYING_TOTAL', $limit)
	 		->willReturn($show_count);
		$this->template->expects($this->once())
	 		->method('assign_vars')
	 		->with($this->equalTo(
	 			array(
	 				'S_RH_TOPICTAGS_INCLUDE_CSS'		=> true,
	 				'RH_TOPICTAGS_TAGCLOUD_SHOW_COUNT'	=> true,
	 				'RH_TOPICTAGS_TAGCLOUD_TAG_COUNT'	=> $show_count,
	 			))
	 		)->willReturn(true);

		$this->template->expects($this->exactly(2))
	 		->method('assign_block_vars')
	 		->withConsecutive(
	 			array('rh_topictags_tags', $this->callback(function($o) {
		 			$diff = array_diff_assoc(array(
		 				'NAME' => 'tag1',
		 				'COUNT' => 2
		 			), $o);
		 			return sizeof($diff) == 0;
		 		})),
		 		array('rh_topictags_tags', $this->callback(function($o) {
		 			$diff = array_diff_assoc(array(
		 				'NAME' => 'anothertag3',
		 				'COUNT' => 1
		 			), $o);
		 			return sizeof($diff) == 0;
		 		}))
	 		)
	 		->willReturn(true);

		$this->tagcloud_manager->assign_tagcloud_to_template($limit);
	}

	public function test_assign_tagcloud_to_template3()
	{
		// limit must be read from config which is 1
		$limit = 0;
		$show_count = 'Display ' . 1 . ' tags.';
		$this->user->expects($this->once())
			->method('lang')
			->with('RH_TOPICTAGS_DISPLAYING_TOTAL', 1)
			->willReturn($show_count);
		$this->template->expects($this->once())
			->method('assign_vars')
			->with($this->equalTo(
				array(
					'S_RH_TOPICTAGS_INCLUDE_CSS'		=> true,
					'RH_TOPICTAGS_TAGCLOUD_SHOW_COUNT'	=> true,
					'RH_TOPICTAGS_TAGCLOUD_TAG_COUNT'	=> $show_count,
				))
			)->willReturn(true);

		$this->template->expects($this->exactly(1))
			->method('assign_block_vars')
			->with('rh_topictags_tags', $this->callback(function($o) {
				$diff = array_diff_assoc(array(
					'NAME' => 'tag1',
					'COUNT' => 2
				), $o);
				return sizeof($diff) == 0;
			}))
			->willReturn(true);

		$this->tagcloud_manager->assign_tagcloud_to_template($limit);
	}

	public function test_assign_tagcloud_to_template4()
	{
		// there exist only 3 tags and only 2 are used => the limit must be adjusted
		$limit = 4;
		$show_count = 'Display ' . 2 . ' tags.';
		$this->user->expects($this->once())
			->method('lang')
			->with('RH_TOPICTAGS_DISPLAYING_TOTAL', 2)
			->willReturn($show_count);
		$this->template->expects($this->once())
			->method('assign_vars')
			->with($this->equalTo(
				array(
					'S_RH_TOPICTAGS_INCLUDE_CSS'		=> true,
					'RH_TOPICTAGS_TAGCLOUD_SHOW_COUNT'	=> true,
					'RH_TOPICTAGS_TAGCLOUD_TAG_COUNT'	=> $show_count,
				))
			)->willReturn(true);

		$this->template->expects($this->exactly(2))
			->method('assign_block_vars')
			->withConsecutive(
				array('rh_topictags_tags', $this->callback(function($o) {
					$diff = array_diff_assoc(array(
						'NAME' => 'tag1',
						'COUNT' => 2
					), $o);
					return sizeof($diff) == 0;
				})),
				array('rh_topictags_tags', $this->callback(function($o) {
					$diff = array_diff_assoc(array(
						'NAME' => 'anothertag3',
						'COUNT' => 1
					), $o);
					return sizeof($diff) == 0;
				}))
			)
			->willReturn(true);

		$this->tagcloud_manager->assign_tagcloud_to_template($limit);
	}

	public function test_assign_tagcloud_to_template5()
	{
		// all tags should be shown
		$limit = -1;
		$show_count = 'Display all tags.';
		$this->user->expects($this->once())
			->method('lang')
			->with('RH_TOPICTAGS_DISPLAYING_TOTAL_ALL')
			->willReturn($show_count);
		$this->template->expects($this->once())
			->method('assign_vars')
			->with($this->equalTo(
				array(
					'S_RH_TOPICTAGS_INCLUDE_CSS'		=> true,
					'RH_TOPICTAGS_TAGCLOUD_SHOW_COUNT'	=> true,
					'RH_TOPICTAGS_TAGCLOUD_TAG_COUNT'	=> $show_count,
				))
			)->willReturn(true);

		$this->template->expects($this->exactly(3))
			->method('assign_block_vars')
			->withConsecutive(
				array('rh_topictags_tags', $this->callback(function($o) {
					$diff = array_diff_assoc(array(
						'NAME' => 'tag1',
						'COUNT' => 2
					), $o);
					return sizeof($diff) == 0;
				})),
				array('rh_topictags_tags', $this->callback(function($o) {
					$diff = array_diff_assoc(array(
						'NAME' => 'anothertag3',
						'COUNT' => 1
					), $o);
					return sizeof($diff) == 0;
				})),
				array('rh_topictags_tags', $this->callback(function($o) {
					$diff = array_diff_assoc(array(
						'NAME' => 'tag2',
						'COUNT' => 0
					), $o);
					return sizeof($diff) == 0;
				}))
			)
			->willReturn(true);
		$this->tagcloud_manager->assign_tagcloud_to_template($limit);
	}

	public function test_get_top_tags()
	{
		$tags = $this->tagcloud_manager->get_top_tags(1);
		$this->assertEquals(array(
			array(
				'tag' => 'tag1',
				'count' => 2
			)
		), $tags);

		$tags = $this->tagcloud_manager->get_top_tags(0);
		$this->assertEquals(
			array(
				array(
					'tag' => 'tag1',
					'count' => 2
				),
				array(
					'tag' => 'anothertag3',
					'count' => 1
				),
				array(
					'tag' => 'tag2',
					'count' => 0
				)
			)
			, $tags);
	}

	public function test_get_maximum_tag_usage_count()
	{
		$class = new \ReflectionClass($this->tagcloud_manager);
		$method = $class->getMethod('get_maximum_tag_usage_count');
		$method->setAccessible(true);

		$max = $method->invokeArgs($this->tagcloud_manager, array());
		$this->assertEquals(2, $max);
	}

	public function test_get_css_class()
	{
		$class = new \ReflectionClass($this->tagcloud_manager);
		$method = $class->getMethod('get_css_class');
		$method->setAccessible(true);

		$maximum = 100;

		$count = -10;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_smallest', $css_class);

		$count = 0;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_smallest', $css_class);

		$count = 1;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_smallest', $css_class);

		$count = 21;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_small', $css_class);

		$count = 41;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_medium', $css_class);

		$count = 61;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_large', $css_class);

		$count = 81;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_largest', $css_class);

		$count = 110;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_largest', $css_class);

		// invalid maximum
		$maximum = -100;

		$count = -10;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_medium', $css_class);

		$count = 0;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_medium', $css_class);

		$count = 1;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_medium', $css_class);

		$count = 110;
		$css_class = $method->invokeArgs($this->tagcloud_manager, array($count, $maximum));
		$this->assertEquals('rh_topictags_medium', $css_class);
	}

}
