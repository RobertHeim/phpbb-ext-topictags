<?php
/**
 *
 * @package phpBB Extension - RH Topic Tags
 * @copyright (c) 2014 Robet Heim
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */
namespace robertheim\topictags\tests\functional;

/**
 * @group functional
 */
class topictags_functional_test_base extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('robertheim/topictags');
	}

	public function setUp()
	{
		parent::setUp();
		// Load all language files
		$this->add_lang_ext('robertheim/topictags', array(
				'info_acp_topictags',
				'permissions_topictags',
				'topictags_acp',
				'topictags',
		));
	}

}
