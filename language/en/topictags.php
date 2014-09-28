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
	'RH_TOPICTAGS_EXP'					=> 'Tags separated by comma (",")',
	'RH_TOPICTAGS_ALLOWED_TAGS'			=> 'Allowed tags:',

	'RH_TOPICTAGS_SEARCH_HEADER_OR'		=> 'Search for topics with any of these tags: %s',
	'RH_TOPICTAGS_SEARCH_HEADER_AND'	=> 'Search for topics with all of these tags: %s',
	'RH_TOPICTAGS_SEARCH_IGNORED_TAGS'	=> 'The following tags have been ignored, because they are invalid: %s',

	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_OR'		=> 'There are no topics tagged with any of these tags: %s',
	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_AND'	=> 'There are no topics tagged with all of these tags: %s',

	'RH_TOPICTAGS_NO_TAGS'				=> 'There are no tags, yet.',

	'RH_TOPICTAGS_TAGS_INVALID'			=> 'The following tags are invalid: %s',

));

