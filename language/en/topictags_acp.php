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
	'ACP_RH_TOPICTAGS_ENABLE'		=> 'Enable Topic Tags',
	'ACP_RH_TOPICTAGS_ENABLE_EXP'	=> 'Whether or not to enable tagging of topics in this forum.',
));

