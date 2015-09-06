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
	'RH_TOPICTAGS'						=> 'Теги',

	'RH_TOPICTAGS_TAGCLOUD'				=> 'Облако тегов',

	'RH_TOPICTAGS_ALLOWED_TAGS'			=> 'Разрешенные теги:',
	'RH_TOPICTAGS_WHITELIST_EXP'		=> 'Разрешены только эти теги:',

	'RH_TOPICTAGS_SEARCH_HEADER_OR'		=> 'Поиск по темам с одним из этих тегов: %s',
	'RH_TOPICTAGS_SEARCH_HEADER_AND'	=> 'Поиск по темам с любым из этих тегов: %s',
	'RH_TOPICTAGS_SEARCH_IGNORED_TAGS'	=> 'Следующие теги будут проигнорированы, поскольку они неправильные: %s',

	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_OR'		=> 'Нет тем ни с одним из этих тегов: %s',
	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_AND'	=> 'Нет тем с этими тегами: %s',

	'RH_TOPICTAGS_TAGS_INVALID'			=> 'Следующие теги неправильные: %s',

	'RH_TOPICTAGS_DISPLAYING_TOTAL_ALL'	=> 'Показать все теги.',

	'RH_TOPICTAGS_DISPLAYING_TOTAL'	=> array(
		0 => 'Теги еще не созданы.',
		1 => 'Самый популярный тег: %d.',
		2 => 'Самые популярные теги: %d.',
	),

));
