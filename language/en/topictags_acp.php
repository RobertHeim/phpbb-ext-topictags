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
	// forum settings page
	'ACP_RH_TOPICTAGS_ENABLE'								=> 'Enable Topic Tags',
	'ACP_RH_TOPICTAGS_ENABLE_EXP'							=> 'Whether or not to enable tagging of topics in this forum. (When disabling tagging, the tags are NOT REMOVED from the topics in this forum - so when you enable it again, they are still there; If you really want to delete the tags, then use the "Delete tags from this forums" option.)',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE'					=> 'Delete tags from this forum',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_EXP'				=> 'This will DELETE all assignments of tags to the topics of this forum. NOTE: To prevent accidental deletion of tags, you need to disabled tagging for this forum.',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_CONFIRM'			=> 'This option will DELETE all assignments of tags to the topics of this forum and you need to disable tagging for this forum, to perform this action.',
	'ACP_RH_TOPICTAGS_PRUNING_REQUIRES_TAGGING_DISABLED'	=> 'To prevent accidental deletion of tags, you need to disable tagging for this forum to delete the tag assignments.',

	'ACP_RH_TOPICTAGS_REGEX_EMPTY'							=> 'The regular expression cannot be left empty.',
	'ACP_RH_TOPICTAGS_EXP_FOR_USERS_EMPTY'					=> 'The explanation of which tags are allowed cannot be left empty.',

	'TOPICTAGS_CONFIG'					=> 'Configuration',
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

	'TOPICTAGS_ALLOWED_TAGS_REGEX'				=> 'Regular Expression for allowed tags',
	'TOPICTAGS_ALLOWED_TAGS_REGEX_EXP'			=> 'WARNING: Do not change this, if you don\'t know what you are doing. <strong>Tags can be 30 characters at maximum</strong>, please take this into account during regex design.<br/>default: /^[a-z0-9]{3,30}$/i',
	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS'		=> 'Explanation for Users',
	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS_EXP'	=> 'This text is shown to the users and should explain which tags are allowed and which not.<br/>default: 0-9, a-z, A-Z, min: 3, max: 30',


));

