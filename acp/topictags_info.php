<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\acp;

class topictags_info
{
	public function module()
	{
		return array(
			'filename'	=> '\robertheim\topictags\acp\topictags_module',
			'title'		=> 'ACP_TOPICTAGS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_TOPICTAGS_SETTINGS',
					'auth' => 'ext_robertheim/topictags && acl_a_board',
					'cat' => array('ACP_TOPICTAGS_TITLE')
				),
				'whitelist'	=> array(
					'title' => 'ACP_TOPICTAGS_WHITELIST',
					'auth' => 'ext_robertheim/topictags && acl_a_board',
					'cat' => array('ACP_TOPICTAGS_TITLE')
				),
				'blacklist'	=> array(
					'title' => 'ACP_TOPICTAGS_BLACKLIST',
					'auth' => 'ext_robertheim/topictags && acl_a_board',
					'cat' => array('ACP_TOPICTAGS_TITLE')
				),
				'tags'	=> array(
					'title' => 'ACP_TOPICTAGS_MANAGE_TAGS',
					'auth' => 'ext_robertheim/topictags && acl_a_board',
					'cat' => array('ACP_TOPICTAGS_TITLE')
				),
			),
		);
	}
}
