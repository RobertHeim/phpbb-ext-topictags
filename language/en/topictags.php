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
	'RH_TOPICTAGS'						=> 'Tags',

	'RH_TOPICTAGS_TAGCLOUD'				=> 'Tag cloud',

	'RH_TOPICTAGS_ALLOWED_TAGS'			=> 'Allowed tags:',
	'RH_TOPICTAGS_WHITELIST_EXP'		=> 'Only these tags are allowed:',

	'RH_TOPICTAGS_SEARCH_HEADER_OR'		=> 'Search for topics with any of these tags: %s',
	'RH_TOPICTAGS_SEARCH_HEADER_AND'	=> 'Search for topics with all of these tags: %s',
	'RH_TOPICTAGS_SEARCH_IGNORED_TAGS'	=> 'The following tags have been ignored, because they are invalid: %s',

	'RH_TOPICTAGS_NO_TOPICS_FOR_NO_TAG'		=> 'Please search for at least one valid tag to show topics here.',
	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_OR'		=> 'There are no topics tagged with any of these tags: %s',
	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_AND'	=> 'There are no topics tagged with all of these tags: %s',

	'RH_TOPICTAGS_TAGS_INVALID'			=> 'The following tags are invalid: %s',

	'RH_TOPICTAGS_DISPLAYING_TOTAL_ALL'	=> 'Displaying all tags.',

	'RH_TOPICTAGS_DISPLAYING_TOTAL'	=> array(
		0 => 'There are no tags, yet',
		1 => 'Displaying the top %d tag.',
		2 => 'Displaying the top %d tags.',
	),

	'RH_TOPICTAGS_TAG_SEARCH' => 'Tag-Search',

	'RH_TOPICTAGS_TAG_SUGGEST_TAG_ROUTE_ERROR' => 'No route found for “%s”',
));
