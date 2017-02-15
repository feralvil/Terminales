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
$lang = "idioma/newactuacion_$idioma.php";
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

// Busqueda de datos:
$sql_contacto = "SELECT * FROM CONTACTOS WHERE ID = '$contexist'";
$res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contactos" . mysql_error());
$ncontacto = mysql_num_rows($res_contacto);
if ($ncontacto > 0){
    $row_contacto = mysql_fetch_array($res_contacto);    
}
?>
<table>
    <tr>
        <th><?php echo $nomflota;  ?></th>
        <td><input type="text" name="contnom" id="contnom" size="60" value="<?php echo $row_contacto["NOMBRE"]; ?>"</td>
        <th><?php echo $telefono; ?></th>
        <td><input type="text" name="conttelf" id="conttelf" size="30" value="<?php echo $row_contacto["TELEFONO"]; ?>"</td>
    </tr>
    <tr>
        <th><?php echo $cargo; ?></th>
        <td><input type="text" name="contcargo" id="contcargo" size="60" value="<?php echo $row_contacto["CARGO"]; ?>"</td>
        <th><?php echo $mail; ?></th>
        <td><input type="text" name="contmail" id="contmail" size="30" value="<?php echo $row_contacto["MAIL"]; ?>"</td>
    </tr>
</table>
