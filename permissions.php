<?php
/**
*
* @package phpBB Extension - RH Topic Tags
* @copyright (c) 2014 Robet Heim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace robertheim\topictags;

/**
* @ignore
*/

abstract class permissions
{
	const ADMIN_EDIT_TAGS = 'a_rhtopictags_edit_tags';
	const MOD_EDIT_TAGS = 'm_rhtopictags_edit_tags';
	const USE_TAGS = 'u_rhtopictags_use_tags';
}
