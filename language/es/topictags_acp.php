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
	'ACP_RH_TOPICTAGS_ENABLE'								=> 'Habilitar Etiquetas de temas',
	'ACP_RH_TOPICTAGS_ENABLE_EXP'							=> 'Sea o no habilitado el etiquetado de los temas en este foro. (Al desactivar el etiquetado, las etiquetas no serán eliminadas de los temas en este foro, por lo que cuando se habilite de nuevo, todavía estarán allí, si realmente desea eliminar las etiquetas, utilice la opción "Eliminar las etiquetas de este foro".)',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE'					=> 'Eliminar las etiquetas de este foro',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_EXP'				=> 'Esto ELIMINARÁ todas las asignaciones de las etiquetas de los temas de este foro. NOTA: Para evitar el borrado accidental de las etiquetas, es necesario deshabilitar el etiquetado para este foro.',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_CONFIRM'			=> 'Esta opción ELIMINARÁ todas las asignaciones de las etiquetas de los temas de este foro, hay que desactivar el etiquetado de este foro para realizar esta acción.',
	'ACP_RH_TOPICTAGS_PRUNING_REQUIRES_TAGGING_DISABLED'	=> 'Para evitar el borrado accidental de las etiquetas, es necesario deshabilitar el etiquetado de este foro para eliminar las asignaciones de etiqueta.',

	'TOPICTAGS_MAINTENANCE'				=> 'Mantenimiento',
	'TOPICTAGS_TITLE'					=> 'Etiquetas de temas',
	'TOPICTAGS_SETTINGS_SAVED'			=> 'Configuración actualizada correctamente.',
	'TOPICTAGS_PRUNE'					=> 'Limpiar etiquetas',
	'TOPICTAGS_PRUNE_EXP'				=> 'Esto ELIMINARÁ todas las etiquetas, que no son utilizadas en ningún tema',
	'TOPICTAGS_PRUNE_CONFIRM'			=> 'Esto ELIMINARÁ todas las etiquetas no utilizadas.',
	'TOPICTAGS_PRUNE_ASSIGNMENTS_DONE'	=> array(
			0 => '',
			1 => '%d asignación de etiqueta del tema ha sido eliminada.',
			2 => '%d asignaciones de etiquetas del tema han sido eliminadas.',
	),
	'TOPICTAGS_PRUNE_TAGS_DONE'			=> array(
			0 => 'No hay etiquetas no usadas que podamos eliminar.',
			1 => '%d etiqueta no utilizada ha sido eliminada.',
			2 => '%d etiquetas no utilizadas han sido eliminadas.',
	),

	'TOPICTAGS_PRUNE_FORUMS'			=> 'Limpiar las etiquetas de foros con etiquetado deshabilitado',
	'TOPICTAGS_PRUNE_FORUMS_EXP'		=> 'Esto ELIMINARÁ todas las asignaciones de las etiquetas de los temas que residen en un foro con etiquetado deshabilitado.',
	'TOPICTAGS_PRUNE_FORUMS_CONFIRM'	=> 'Esto ELIMINARÁ todas las etiquetas de todos los hilos que residen en un foro con etiquetado deshabilitado.',

));
