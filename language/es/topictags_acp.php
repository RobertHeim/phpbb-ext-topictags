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

	'TOPICTAGS_PRUNE_INVALID_TAGS'			=> 'Limpiar etiquetas no válidas',
	'TOPICTAGS_PRUNE_INVALID_TAGS_EXP'			=> 'Esto ELIMINARÁ todas las etiquetas (y sus asignaciones) que no son válidas. Esto sólo es necesario si ha cambiado la expresión regular y quiere deshacerse de las etiquetas no válidas. Tenga en cuenta que las etiquetas no válidas no se pueden buscar, pero todavía se muestran en los temas.',
	'TOPICTAGS_PRUNE_INVALID_TAGS_CONFIRM'	=> '¡Esto ELIMINARÁ todas las etiquetas que no están conformes con la configuración de expresión regular y puede eliminar una gran cantidad de su material si no tienes cuidado!',

	'TOPICTAGS_ALLOWED_TAGS_REGEX'				=> 'Expresión regular para etiquetas permitidas',
	'TOPICTAGS_ALLOWED_TAGS_REGEX_EXP'			=> 'ADVERTENCIA: No cambie esto, si usted no sabe lo que está haciendo. <strong>Las etiquetas pueden ser de 30 caracteres como máximo</strong>, por favor considere esto durante el diseño de expresiones regulares.<br/>Además, usted debe purgar/limpiar manualmente las etiquetas (ver sección de mantenimiento) después de cambiar la expresión regular, si quiere eliminar todas las etiquetas no válidas.<br/>por defecto: /^[a-z0-9]{3,30}$/i',
	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS'		=> 'Explicación para los usuarios',
	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS_EXP'	=> 'Este texto se muestra a los usuarios y debe explicar qué etiquetas se permiten y cuáles no.<br/>por defecto: 0-9, a-z, A-Z, min: 3, max: 30',

));
