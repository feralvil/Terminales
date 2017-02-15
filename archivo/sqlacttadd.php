<?php

/*
 * Consulta de la tabla de contactos con AJAX
 * 
 */

// ------------ Obtención del usuario Joomla! --------------------------------------- //
// Le decimos que estamos en Joomla
define('_JEXEC', 1);

// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..');

// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

// Iniciamos nuestra aplicación (site: frontend)
$mainframe = & JFactory::getApplication('site');

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/actterm_$idioma.php";
include ($lang);

// ------------ Conexión a la BBDD ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
}
else {
    // Seleccionamos la BBDD y codificamos la conexión en UTF-8:
    if (!mysql_select_db($base_datos, $link)) {
        echo 'Error al seleccionar la Base de Datos: ' . mysql_error();
        exit;
    }
    mysql_set_charset('utf8', $link);
}
// -------------------------------------------------------------------------------------

// Importamos las variables del Formulario
import_request_variables("gp", "");

?>
<h3><?php echo $h3tipoact; ?></h3>
<table>
    <tr>
        <td class="borde">
            <a href='#' id="actalta">
                <img src='imagenes/base_add.png' alt='<?php echo $alta; ?>' title='<?php echo $alta; ?>'>
            </a><br><?php echo $alta; ?>
        </td>
        <td class="borde">
            <a href='#' id="actbaja">
                <img src='imagenes/base_del.png' alt='<?php echo $baja; ?>' title='<?php echo $baja; ?>'>
            </a><br><?php echo $baja; ?>
        </td>
        <td class="borde">
            <a href='#' id="actmod">
                <img src='imagenes/leave.png' alt='<?php echo $mod; ?>' title='<?php echo $mod; ?>'>
            </a><br><?php echo $mod; ?>
        </td>
    </tr>
</table>