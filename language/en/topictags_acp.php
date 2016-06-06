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

// these will be re-used in the definitions below - that is why we need to define and merge them first.
$lang = array_merge($lang, array(
	'ACP_RH_TOPICTAGS_REGEX_DEFAULT'				=> '/^[\- a-z0-9]{3,30}$/i',
	'ACP_RH_TOPICTAGS_REGEX_EXP_FOR_USERS_DEFAULT'	=> '-, 0-9, a-z, A-Z, spaces (will be converted to -), min: 3, max: 30',
));

$lang = array_merge($lang, array(
	// forum settings page
	'ACP_RH_TOPICTAGS_ENABLE'								=> 'Enable RH Topic Tags',
	'ACP_RH_TOPICTAGS_ENABLE_EXP'							=> 'Whether or not to enable tagging of topics in this forum. (When disabling tagging, the tags are NOT REMOVED from the topics in this forum - so when you enable it again, they are still there; If you really want to delete the tags, then use the “Delete tags from this forum” option.)',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE'					=> 'Delete tags from this forum',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_EXP'				=> 'This will DELETE all assignments of tags to the topics of this forum. NOTE: To prevent accidental deletion of tags, you need to disabled tagging for this forum.',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_CONFIRM'			=> 'This option will DELETE all assignments of tags to the topics of this forum and you need to disable tagging for this forum, to perform this action.',
	'ACP_RH_TOPICTAGS_PRUNING_REQUIRES_TAGGING_DISABLED'	=> 'To prevent accidental deletion of tags, you need to disable tagging for this forum to delete the tag assignments.',
	'ACP_RH_TOPICTAGS_ERROR'								=> 'Error',
	'ACP_RH_TOPICTAGS_UNKNOWN_ERROR'						=> 'Unknown error. See javascript-console for server response.',

	// config
	'TOPICTAGS_INSTALLED'					=> 'Installed Version: v%s',

	'ACP_RH_TOPICTAGS_REGEX_EMPTY'			=> 'The regular expression cannot be left empty.',
	'ACP_RH_TOPICTAGS_EXP_FOR_USERS_EMPTY'	=> 'The explanation of which tags are allowed cannot be left empty.',

	'TOPICTAGS_CONFIG'					=> 'Configuration',
	'TOPICTAGS_CONFIG_TAGCLOUD'			=> 'Tag cloud settings',
	'TOPICTAGS_CONFIG_TAGS'				=> 'Tag settings',
	'TOPICTAGS_MAINTENANCE'				=> 'Maintenance',
	'TOPICTAGS_TITLE'					=> 'RH Topic Tags',
	'TOPICTAGS_SETTINGS_SAVED'			=> 'Configuration updated successfully.',
	'TOPICTAGS_WHITELIST_SAVED'			=> 'Whitelist updated successfully.',
	'TOPICTAGS_BLACKLIST_SAVED'			=> 'Blacklist updated successfully.',

	'TOPICTAGS_DISPLAY_TAGCLOUD_ON_INDEX'		=> 'Display tag cloud on index',
	'TOPICTAGS_DISPLAY_TAGCLOUD_ON_INDEX_EXP'	=> 'When enabled a tag cloud is displayed on the bottom of the index page',

	'TOPICTAGS_DISPLAY_TAGCOUNT_IN_TAGCLOUD'		=> 'Display usage-count of tags in tag cloud',
	'TOPICTAGS_DISPLAY_TAGCOUNT_IN_TAGCLOUD_EXP'	=> 'When enabled the tag cloud displays how many topics are tagged with each tag',

	'TOPICTAGS_MAX_TAGS_IN_TAGCLOUD'			=> 'Max tags in tag cloud',
	'TOPICTAGS_MAX_TAGS_IN_TAGCLOUD_EXP'		=> 'This limits the count of tags shown in the tag cloud to the configured value.',

	'TOPICTAGS_DISPLAY_TAGS_IN_VIEWFORUM'		=> 'Display tags in viewforum',
	'TOPICTAGS_DISPLAY_TAGS_IN_VIEWFORUM_EXP'	=> 'If set to yes, the assigned tags for each topic are shown in topic-lists.',

	'TOPICTAGS_ENABLE_IN_ALL_FORUMS_ALREADY'	=> 'Tagging is already enabled for all forums.',
	'TOPICTAGS_ENABLE_IN_ALL_FORUMS'			=> 'Enable RH Topic Tags in all forums',
	'TOPICTAGS_ENABLE_IN_ALL_FORUMS_EXP'		=> 'This will enable tagging in <em>all</em> forums. You can enable (or disable) it in a single forum in the settings of the forum.',
	'TOPICTAGS_ENABLE_IN_ALL_FORUMS_DONE'	=> array(
			1 => 'Tagging has been enabled for %d forum.',
			2 => 'Tagging has been enabled for %d forums.',
	),

	'TOPICTAGS_DISABLE_IN_ALL_FORUMS_ALREADY'	=> 'Tagging is already disabled for all forums.',
	'TOPICTAGS_DISABLE_IN_ALL_FORUMS'			=> 'Disable RH Topic Tags in all forums',
	'TOPICTAGS_DISABLE_IN_ALL_FORUMS_EXP'		=> 'This will disable tagging in <em>all</em> forums. You can enable (or disable) it in a single forum in the settings of the forum.',
	'TOPICTAGS_DISABLE_IN_ALL_FORUMS_DONE'	=> array(
			1 => 'Tagging has been disabled for %d forum.',
			2 => 'Tagging has been disabled for %d forums.',
	),

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

	'TOPICTAGS_PRUNE_INVALID_TAGS'				=> 'Prune invalid tags',
	'TOPICTAGS_PRUNE_INVALID_TAGS_EXP'			=> 'This will DELETE all tags (and their assignments) that are not valid anymore. This is only required if you changed the regex and want to get rid of the invalid tags.',
	'TOPICTAGS_PRUNE_INVALID_TAGS_CONFIRM'		=> 'This will REMOVE all tags that are not conform with the configured regex and can delete a lot of your stuff, if you are not careful!',

	'TOPICTAGS_CALC_COUNT_TAGS'					=> 'Recalculate tag-counts',
	'TOPICTAGS_CALC_COUNT_TAGS_EXP'				=> 'This will re-calculate how often each tag is used.',
	'TOPICTAGS_CALC_COUNT_TAGS_DONE'			=> 'Tag-counts have been recalculated.',

	'TOPICTAGS_ENABLE_WHITELIST'				=> 'Enable Whitelist',
	'TOPICTAGS_ENABLE_WHITELIST_EXP'			=> 'If enabled, only tags that are conform to the regex AND are present in the whitelist below are allowed.<br/>NOTE 1: If the blacklist is enabled, too, and a tag is in the whitelist as well as in the blacklist, it will be rejected.<br/>NOTE 2: To prevent accidental data loss, tags that are already in the database, but not on the whitelist are NOT deleted automatically and will be displayed as well. You must remove the existing tags by hand.',

	'TOPICTAGS_WHITELIST'						=> 'Whitelist',
	'TOPICTAGS_WHITELIST_EXP'					=> 'List of allowed tags.<br/>NOTE: Tags must be conform to the regex as well, so be sure that all these tags are conform to your regex-settings below (not checked automatically).',

	'TOPICTAGS_ENABLE_BLACKLIST'				=> 'Enable Blacklist',
	'TOPICTAGS_ENABLE_BLACKLIST_EXP'			=> 'If enabled, the tags configured in the blacklist will be rejected even if they are conform with the regex.<br/>NOTE 1: To prevent accidental data loss, tags that are already in the database are not deleted automatically. You must remove them by hand from each topic.<br/>NOTE 2: The blacklist is never shown to the users.',

	'TOPICTAGS_BLACKLIST'						=> 'Blacklist',
	'TOPICTAGS_BLACKLIST_EXP'					=> 'List of forbidden tags.<br/>NOTE: All tags that are not conform with the regex are always rejected.',

	'TOPICTAGS_ALLOWED_TAGS_REGEX'				=> 'Regular Expression for allowed tags',
	'TOPICTAGS_ALLOWED_TAGS_REGEX_EXP'			=> 'WARNING: Do not change this, if you don\'t know what you are doing. <strong>Tags can be 30 characters at maximum and delimiter must be “/”</strong>, please consider this during regex design.<br/>Note that afterwards invalid tags are not searchable, but are still displayed in the topics.<br/>Consider pruning the invalid tags (see maintenance-section).<br/>default: ' . $lang['ACP_RH_TOPICTAGS_REGEX_DEFAULT'],

	'TOPICTAGS_CONVERT_SPACE_TO_MINUS'			=> 'Convert “ ” to “-”',
	'TOPICTAGS_CONVERT_SPACE_TO_MINUS_EXP'		=> 'If set to yes, all spaces (“ ”) are automatically converted to minus (“-”).<br/>NOTE 1: In the regex you must allow “-”; otherwise tags with whitespaces will be rejected.<br/>NOTE 2: Existing tags with spaces will NOT be converted automatically.',

	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS'		=> 'Explanation for Users',
	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS_EXP'	=> 'This text is shown to the users and should explain which tags are allowed and which not.<br/>default: ' . $lang['ACP_RH_TOPICTAGS_REGEX_EXP_FOR_USERS_DEFAULT'],

	'TOPICTAGS_MANAGE_TAGS_EXP'					=> 'The table shows all existing tags. Here you can <ul><li>delete a tag (and all their assignments).</li><li>edit a tag (double click the tags\' names for faster workflow).</li><li>merge tags by editing a tag and setting its name so that it equals another tag - they will be merged automatically.</li></ul>',
	'TOPICTAGS_NO_TAGS'							=> 'There are no tags yet.',
	'TOPICTAGS_TAG'								=> 'Tag',
	'TOPICTAGS_ASSIGNMENTS'						=> 'Assignments',
	'TOPICTAGS_NEW_TAG_NAME'					=> 'New tag name',
	'TOPICTAGS_NEW_TAG_NAME_EXP'				=> 'Please enter the new tag name.',
	'TOPICTAGS_TAG_DELETE_CONFIRM'				=> 'Are you sure that you want to delete the tag <em>%s</em>? This will delete the tag from <b>all topics</b> where it is assigned. This can not be reverted.',
	'TOPICTAGS_TAG_DELETED'						=> 'The tag has been deleted.',
	'TOPICTAGS_MISSING_TAG_ID'					=> 'Missing tag-id.',
	'TOPICTAGS_TAG_CHANGED'						=> 'The tag has been changed.',
	'TOPICTAGS_TAG_MERGED'						=> 'The tag has been merged with tag “%s”',
	'TOPICTAGS_MISSING_TAG_NAMES'				=> 'Missing tag-names.',
	'TOPICTAGS_TAG_INVALID'						=> 'The tag “%s” is invalid, please check your tag-settings.',
	'TOPICTAGS_TAG_DOES_NOT_EXIST'				=> 'The tag “%s” does not exist.',
	'TOPICTAGS_NO_MODIFICATION'					=> 'The tag was not changed.',

	'TOPICTAGS_SORT_NAME_ASC'					=> 'Tag name A&rArr;Z', // &rArr; is a right-arrow (=>)
	'TOPICTAGS_SORT_NAME_DESC'					=> 'Tag name Z&rArr;A', // &rArr; is a right-arrow (=>)
	'TOPICTAGS_SORT_COUNT_ASC'					=> 'Assignments count ascending',
	'TOPICTAGS_SORT_COUNT_DESC'					=> 'Assignments count descending',

));
