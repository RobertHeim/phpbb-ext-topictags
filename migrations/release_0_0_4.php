<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags\migrations;

use robertheim\topictags\prefixes;

class release_0_0_4 extends \phpbb\db\migration\migration
{
	protected $version = '0.0.4-DEV';

	public function effectively_installed()
	{
		return version_compare($this->config[prefixes::CONFIG.'_version'], $this->version, '>=');
	}

	public static function depends_on()
	{
		return array(
			'\robertheim\topictags\migrations\release_0_0_3',
		);
	}

	public function update_data()
	{
		global $user;
		$user->add_lang_ext('robertheim/topictags', 'topictags_acp');
		return array(
			array('config.add', array(prefixes::CONFIG.'_allowed_tags_regex', $user->lang('ACP_RH_TOPICTAGS_REGEX_DEFAULT'))),
			array('config.add', array(prefixes::CONFIG.'_allowed_tags_exp_for_users', $user->lang('ACP_RH_TOPICTAGS_REGEX_EXP_FOR_USERS_DEFAULT'))),
			array('config.update', array(prefixes::CONFIG.'_version', $this->version)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_TOPICTAGS_TITLE'
			)),

			array('module.add', array(
				'acp', 'ACP_TOPICTAGS_TITLE', array(
					'module_basename'	=> '\robertheim\topictags\acp\topictags_module',
					'auth'				=> 'ext_robertheim/topictags && acl_a_board',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
