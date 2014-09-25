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
	'RH_TOPICTAGS_ANY'					=> 'any',
	'RH_TOPICTAGS_ALL'					=> 'all',
	'RH_TOPICTAGS_EXP'					=> 'Tags separated by comma (",")',
	'RH_TOPICTAGS_SEARCH_HEADER'		=> 'Search for topics with %s of these tags: %s', // %s=any||all, %s=tags,
	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG'	=> 'There are no topics tagged with %s of these tags: %s', // %s=any||all, %s=tags,
	'RH_TOPICTAGS_NO_TAGS'				=> 'There are no tags, yet.',

));

