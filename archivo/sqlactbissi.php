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
// ------------------------------------------------------------------------------------- //

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
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
// ------------------------------------------------------------------------------------- //

// Importamos las variables del Formulario
import_request_variables("gp", "");

if ($tactissi == "baja"){
    $h3ini = $h3acttbaja;
    $enlace = "sqlacttbaja.php";
}
elseif ($tactissi == "mod") {
    $h3ini = $h3acttmod;
    $enlace = "sqlacttmod.php";
}
?>
<h3><?php echo $h3ini;?> &mdash; Buscar ISSI:</h3>
<form name="buscaissi" id="buscaissi" action="sqlacttissi.php" method="POST">
    <input type="hidden" name="idactuacion" value="<?php echo $idactuacion; ?>" />
    <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
    <input type="hidden" name="tactissi" value="<?php echo $tactissi ;?>" />
    <label for="issibusca">ISSI:</label>
    <input type="text" name="issibusca" id="issibusca" value="" size="10">
    &nbsp;
    <a href="#" id="consultaissi"><img src="imagenes/consulta.png" alt="Buscar ISSI" title="Buscar ISSI"></a>
</form>
<table>
    <tr>
        <td class="borde">
            <a href='#' id="cancelar"><img src='imagenes/no.png' alt='<?php echo $cancelar; ?>' title='<?php echo $cancelar; ?>'></a><br><?php echo $cancelar; ?>
        </td>
    </tr>
</table>