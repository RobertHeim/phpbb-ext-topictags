<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

use robertheim\topictags\permissions;

$lang = array_merge($lang, array(
	'ACL_'.utf8_strtoupper(permissions::ADMIN_EDIT_TAGS)	=> 'Может редактировать RH topic tags',
	'ACL_'.utf8_strtoupper(permissions::MOD_EDIT_TAGS)	=> 'Может редактировать RH topic tags',
	'ACL_'.utf8_strtoupper(permissions::USE_TAGS)			=> 'Может использовать RH topic tags',
));
