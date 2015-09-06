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

// these will be re-used in the definitions below - that is why we need to define and merge them first.
$lang = array_merge($lang, array(
	'ACP_RH_TOPICTAGS_REGEX_DEFAULT'				=> '/^[\- a-z0-9]{3,30}$/i',
	'ACP_RH_TOPICTAGS_REGEX_EXP_FOR_USERS_DEFAULT'	=> '-, 0-9, a-z, A-Z, espacios (se convertirán a -), min: 3, max: 30',
));

$lang = array_merge($lang, array(
	// forum settings page
	'ACP_RH_TOPICTAGS_ENABLE'								=> 'Habilitar RH Etiquetas de temas',
	'ACP_RH_TOPICTAGS_ENABLE_EXP'							=> 'Sea o no habilitado el etiquetado de los temas en este foro. (Al desactivar el etiquetado, las etiquetas no serán eliminadas de los temas en este foro, por lo que cuando se habilite de nuevo, todavía estarán allí, si realmente desea eliminar las etiquetas, utilice la opción “Eliminar las etiquetas de este foro”.)',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE'					=> 'Eliminar las etiquetas de este foro',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_EXP'				=> 'Esto ELIMINARÁ todas las asignaciones de las etiquetas de los temas de este foro. NOTA: Para evitar el borrado accidental de las etiquetas, es necesario deshabilitar el etiquetado para este foro.',
	'ACP_FORUM_SETTINGS_RH_TOPICTAGS_PRUNE_CONFIRM'			=> 'Esta opción ELIMINARÁ todas las asignaciones de las etiquetas de los temas de este foro, hay que desactivar el etiquetado de este foro para realizar esta acción.',
	'ACP_RH_TOPICTAGS_PRUNING_REQUIRES_TAGGING_DISABLED'	=> 'Para evitar el borrado accidental de las etiquetas, es necesario deshabilitar el etiquetado de este foro para eliminar las asignaciones de etiqueta.',

	// config
	'TOPICTAGS_INSTALLED'					=> 'Versión instalada: v%s',

	'ACP_RH_TOPICTAGS_REGEX_EMPTY'			=> 'La expresión regular no se puede dejar vacía.',
	'ACP_RH_TOPICTAGS_EXP_FOR_USERS_EMPTY'	=> 'La explicación de las etiquetas que están permitidas no se puede dejar vacía.',

	'TOPICTAGS_CONFIG'					=> 'Configuración',
	'TOPICTAGS_CONFIG_TAGCLOUD'			=> 'Ajustes de nube de etiquetas',
	'TOPICTAGS_CONFIG_TAGS'				=> 'Ajustes de etiquetas',
	'TOPICTAGS_MAINTENANCE'				=> 'Mantenimiento',
	'TOPICTAGS_TITLE'					=> 'RH Etiquetas de temas',
	'TOPICTAGS_SETTINGS_SAVED'			=> 'Configuración actualizada correctamente.',
	'TOPICTAGS_WHITELIST_SAVED'			=> 'Lista blanca actualizada correctamente.',
	'TOPICTAGS_BLACKLIST_SAVED'			=> 'Lista negra actualizada correctamente.',

	'TOPICTAGS_DISPLAY_TAGCLOUD_ON_INDEX'		=> 'Mostrar nube de etiquetas en el índice',
	'TOPICTAGS_DISPLAY_TAGCLOUD_ON_INDEX_EXP'	=> 'Cuando se habilita, una nube de etiquetas se muestra en la parte inferior de la página de índice',

	'TOPICTAGS_DISPLAY_TAGCOUNT_IN_TAGCLOUD'		=> 'Mostrar contador de uso de etiquetas en la nube de etiquetas',
	'TOPICTAGS_DISPLAY_TAGCOUNT_IN_TAGCLOUD_EXP'	=> 'Cuando se habilita la nube de etiqueta muestra cuántos temas se marcan con cada etiqueta',

	'TOPICTAGS_MAX_TAGS_IN_TAGCLOUD'			=> 'Max. etiquetas en la nube de etiquetas',
	'TOPICTAGS_MAX_TAGS_IN_TAGCLOUD_EXP'		=> 'Esto limita el número de etiquetas que se muestran en la nube de etiquetas con el valor configurado.',

	'TOPICTAGS_DISPLAY_TAGS_IN_VIEWFORUM'		=> 'Mostrar etiquetas viendo un foro',
	'TOPICTAGS_DISPLAY_TAGS_IN_VIEWFORUM_EXP'	=> 'Si se establece en sí, las etiquetas asignadas para cada tema se muestran en la lista de tema.',

	'TOPICTAGS_ENABLE_IN_ALL_FORUMS_ALREADY'	=> 'El etiquetado ya está habilitado para todos los foros.',
	'TOPICTAGS_ENABLE_IN_ALL_FORUMS'			=> 'Habilitar RH Etiquetas de temas en todos los foros',
	'TOPICTAGS_ENABLE_IN_ALL_FORUMS_EXP'		=> 'Esto permitirá que el etiquetado en <em>todos</em> los foros. Lo puede activar (o desactivar) en un solo foro desde la configuración del foro.',
	'TOPICTAGS_ENABLE_IN_ALL_FORUMS_DONE'	=> array(
			1 => 'El etiquetado se ha habilitado para %d foro.',
			2 => 'El etiquetado se ha habilitado para %d foros.',
	),

	'TOPICTAGS_DISABLE_IN_ALL_FORUMS_ALREADY'	=> 'El etiquetado ya está deshabilitado para todos los foros.',
	'TOPICTAGS_DISABLE_IN_ALL_FORUMS'			=> 'Deshabilitar RH Etiquetas de temas en todos los foros',
	'TOPICTAGS_DISABLE_IN_ALL_FORUMS_EXP'		=> 'Esto deshabilita el etiquetado en <em>todos</em> los foros. Lo puede activar (o desactivar) en un solo foro desde la configuración del foro.',
	'TOPICTAGS_DISABLE_IN_ALL_FORUMS_DONE'	=> array(
			1 => 'El etiquetado se ha deshabilitado para %d foro.',
			2 => 'El etiquetado se ha deshabilitado para %d foros.',
	),

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

	'TOPICTAGS_PRUNE_INVALID_TAGS'				=> 'Limpiar etiquetas no válidas',
	'TOPICTAGS_PRUNE_INVALID_TAGS_EXP'			=> 'Esto ELIMINARÁ todas las etiquetas (y sus asignaciones) que no son válidas. Esto sólo es necesario si ha cambiado la expresión regular y quiere deshacerse de las etiquetas no válidas. Tenga en cuenta que las etiquetas no válidas no se pueden buscar, pero todavía se muestran en los temas.',
	'TOPICTAGS_PRUNE_INVALID_TAGS_CONFIRM'		=> '¡Esto ELIMINARÁ todas las etiquetas que no están conformes con la configuración de expresión regular y puede eliminar una gran cantidad de su material si no tienes cuidado!',

	'TOPICTAGS_CALC_COUNT_TAGS'					=> 'Recalcular el contador de etiquetas',
	'TOPICTAGS_CALC_COUNT_TAGS_EXP'				=> 'Esto volverá a calcular con qué frecuencia se usa cada etiqueta.',
	'TOPICTAGS_CALC_COUNT_TAGS_DONE'			=> 'El contador de etiquetas se ha recalculado.',

	'TOPICTAGS_ENABLE_WHITELIST'				=> 'Habilitar Lista blanca',
	'TOPICTAGS_ENABLE_WHITELIST_EXP'			=> 'Si está habilitado, sólo etiquetas que son conformes con la expresión regular y están presentes en la lista blanca de abajo estarán permitidas.<br/>NOTA 1: Si la lista negra está activada, también, y es una etiqueta en la lista blanca, así como en la lista negra, será rechazada.<br/>NOTA 2: Para evitar la pérdida accidental de datos, las etiquetas que ya están en la base de datos, pero no en la lista blanca no se eliminan automáticamente y se mostrarán también. Debe quitar las etiquetas existentes con a mano.',

	'TOPICTAGS_WHITELIST'						=> 'Lista blanca',
	'TOPICTAGS_WHITELIST_EXP'					=> 'Lista de etiquetas permitidas.<br/>NOTA: Las etiquetas deben ser conformes con la expresión regular, así, así que asegúrese de que todas estas etiquetas se ajustan a sus expresiones regulares (ajustes de abajo, no controladas de forma automática).',

	'TOPICTAGS_ENABLE_BLACKLIST'				=> 'Habilitar Lista negra',
	'TOPICTAGS_ENABLE_BLACKLIST_EXP'			=> 'Si está activado, las variables configuradas en la lista negra serán rechazadas, incluso si están conformes con la expresión regular.<br/>NOTA 1: Para evitar la pérdida accidental de datos, las etiquetas que ya están en la base de datos no se eliminan automáticamente. Debe eliminar a mano de cada tema.<br/>NOTA 2: La lista negra nunca se muestra a los usuarios.',

	'TOPICTAGS_BLACKLIST'						=> 'Lista negra',
	'TOPICTAGS_BLACKLIST_EXP'					=> 'Lista de etiquetas prohibidas.<br/>NOTA: Todas las etiquetas que no están conformes con la expresión regular siempre serán rechazadas.',

	'TOPICTAGS_ALLOWED_TAGS_REGEX'				=> 'Expresión regular para etiquetas permitidas',
	'TOPICTAGS_ALLOWED_TAGS_REGEX_EXP'			=> 'ADVERTENCIA: No cambie esto, si no sabe lo que está haciendo. <strong>Las etiquetas pueden ser de 30 caracteres como máximo</strong>, por favor considere esto durante el diseño de expresiones regulares.<br/>Tenga en cuenta que las etiquetas no válidas, no se pueden buscar después, pero todavía se muestran en los temas.<br/>Considere la limpieza de las etiquetas no válidas (ver sección de mantenimiento).<br/>Por defecto: ' . $lang['ACP_RH_TOPICTAGS_REGEX_DEFAULT'],

	'TOPICTAGS_CONVERT_SPACE_TO_MINUS'			=> 'Convertir “ ” a “-”',
	'TOPICTAGS_CONVERT_SPACE_TO_MINUS_EXP'		=> 'Si se establece en sí, todos los espacios (“ ”) se convierten automáticamente a menos (“-”).<br/>NOTA 1: En la expresión regular se debe permitir “-”; de lo contrario se rechazarán las etiquetas con espacios en blanco.<br/>NOTA 2: Las etiquetas existentes con espacios NO se convertirán automáticamente.',

	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS'		=> 'Explicación para los usuarios',
	'TOPICTAGS_ALLOWED_TAGS_EXP_FOR_USERS_EXP'	=> 'Este texto se muestra a los usuarios y debe explicar qué etiquetas se permiten, y cuáles no.<br/>Por defecto: ' . $lang['ACP_RH_TOPICTAGS_REGEX_EXP_FOR_USERS_DEFAULT'],

	'TOPICTAGS_MANAGE_TAGS_EXP'					=> 'La tabla muestra todas las etiquetas existentes. Aquí puede eliminarlas (y todas sus asignaciones) o editar una etiqueta. También puede combinar etiquetas, con la edición de una etiqueta y el establecimiento de su nombre para que sea igual a otra etiqueta y así se fusionarán automáticamente.',
	'TOPICTAGS_NO_TAGS'							=> 'No hay etiquetas todavía.',
	'TOPICTAGS_TAG'								=> 'Etiqueta',
	'TOPICTAGS_ASSIGNMENTS'						=> 'Asignaciones',
	'TOPICTAGS_NEW_TAG_NAME'					=> 'Nuevo nombre de etiqueta',
	'TOPICTAGS_NEW_TAG_NAME_EXP'				=> 'Por favor, introduzca un nuevo nombre de etiqueta.',
	'TOPICTAGS_TAG_DELETE_CONFIRM'				=> '¿Seguro que quiere eliminar la etiqueta <em>%s</em>? Esto eliminará la etiqueta de <b>todos los temas</b> donde se le asignó. Esto no puede ser revertido.',
	'TOPICTAGS_TAG_DELETED'						=> 'La etiqueta ha sido borrada.',
	'TOPICTAGS_MISSING_TAG_ID'					=> 'Falta el ID de la etiqueta.',
	'TOPICTAGS_TAG_CHANGED'						=> 'La etiqueta ha sido cambiada.',
	'TOPICTAGS_TAG_MERGED'						=> 'La etiqueta se ha fusionado con la etiqueta “%s”.',
	'TOPICTAGS_MISSING_TAG_NAMES'				=> 'Faltan nombres de etiquetas.',
	'TOPICTAGS_TAG_INVALID'						=> 'La etiqueta “%s” no es válida, por favor verifique los ajustes de etiquetas.',
	'TOPICTAGS_TAG_DOES_NOT_EXIST'				=> 'La etiqueta “%s” no existe.',
	'TOPICTAGS_NO_MODIFICATION'					=> 'La etiqueta no fue cambiada.',

	'TOPICTAGS_SORT_NAME_ASC'					=> 'Nombre de etiqueta A&rArr;Z', // &rArr; is a right-arrow (=>)
	'TOPICTAGS_SORT_NAME_DESC'					=> 'Nombre de etiqueta Z&rArr;A', // &rArr; is a right-arrow (=>)
	'TOPICTAGS_SORT_COUNT_ASC'					=> 'Orden de asignaciones ascendente',
	'TOPICTAGS_SORT_COUNT_DESC'					=> 'Orden de asignaciones descendente',

));
