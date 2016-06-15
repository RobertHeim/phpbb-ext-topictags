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
	'RH_TOPICTAGS'						=> 'Balises',

	'RH_TOPICTAGS_TAGCLOUD'				=> 'Nuage de balises',

	'RH_TOPICTAGS_ALLOWED_TAGS'			=> 'Balises autorisées :',
	'RH_TOPICTAGS_WHITELIST_EXP'		=> 'Seules ces balises seront autorisées :',

	'RH_TOPICTAGS_SEARCH_HEADER_OR'		=> 'Recherche de sujets comportant l\'une quelconque des balises suivantes : %s',
	'RH_TOPICTAGS_SEARCH_HEADER_AND'	=> 'Recherche de sujets avec toutes les balises suivantes : %s',
	'RH_TOPICTAGS_SEARCH_IGNORED_TAGS'	=> 'Les balises suivantes ont été ignorées parce qu\'elles ne sont pas valides : %s',

	'RH_TOPICTAGS_NO_TOPICS_FOR_NO_TAG'		=> 'Chercher au moins une balise valide à montrer ici.',
	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_OR'		=> 'Il n\'existe pas de sujets comportant l\'une quelconque des balises suivantes : %s',
	'RH_TOPICTAGS_NO_TOPICS_FOR_TAG_AND'	=> 'Il n\'existe pas de sujets comportant toutes les balises suivantes : %s',

	'RH_TOPICTAGS_TAGS_INVALID'			=> 'Les balises suivantes ne sont pas valides : %s',

	'RH_TOPICTAGS_DISPLAYING_TOTAL_ALL'	=> 'Affichage de toutes les balises.',

	'RH_TOPICTAGS_DISPLAYING_TOTAL'	=> array(
		0 => 'Il n\'existe pas encore de balises',
		1 => 'Affichage de la première balise.',
		2 => 'Affichage des %d premières balises.',
	),

	'RH_TOPICTAGS_TAG_SEARCH' => 'Recherche de balises',

	'RH_TOPICTAGS_TAG_SUGGEST_TAG_ROUTE_ERROR' => 'Pas de route trouvée pour “%s”',

));
