<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

$lang = array_merge($lang, array(
	'ACP_RH_TOPICTAGS_ENABLE'			=> 'Enable Topic Tags',
	'ACP_RH_TOPICTAGS_ENABLE_EXP'		=> 'Whether or not to enable tagging of topics in this forum.',
	'ACP_RH_TOPICTAGS_ENABLE_EXP'		=> 'Whether or not to enable tagging of topics in this forum.',
	'TOPICTAGS_MAINTENANCE'				=> 'Maintenance',
	'TOPICTAGS_TITLE'					=> 'Topic Tags',
	'TOPICTAGS_SETTINGS_SAVED'			=> 'Configuration updated successfully.',
	'TOPICTAGS_PRUNE'					=> 'Prune tags',
	'TOPICTAGS_PRUNE_EXP'				=> 'This will delete all tags, which are not used by any topic',
	'TOPICTAGS_PRUNE_CONFIRM'			=> 'This will DELETE all unused tags.',
	'TOPICTAGS_PRUNE_ASSIGNMENTS_DONE'	=> array(
			0 => '',
			1 => '%d topic-tag-assignment has been deleted.',
			2 => '%d topic-tag-assignments have been deleted.',
	),
	'TOPICTAGS_PRUNE_TAGS_DONE'			=> array(
			0 => 'There are no unused tags which we could delete.',
			1 => '%d unused tag has been deleted.',
			2 => '%d unused tags have been deleted.',
	),
	'TOPICTAGS_PRUNE_FORUMS'			=> 'Prune tags from forums with tagging disabled',
	'TOPICTAGS_PRUNE_FORUMS_EXP'		=> 'This will DELETE all assignments of tags to those topics that reside in a forum with tagging disabled.',
	'TOPICTAGS_PRUNE_FORUMS_CONFIRM'	=> 'This will REMOVE all tags from all those threads which reside in a forum with tagging disabled.',
));

