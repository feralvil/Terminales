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

// Busqueda de datos:
$sql_actterm = "SELECT * FROM actterminales WHERE ID = '$idactuacion'";
$res_actterm = mysql_query($sql_actterm) or die("Error en la consulta de Terminales de las actuaciones" . mysql_error());
$nactterm = mysql_num_rows($res_actterm);
if ($nactterm > 0){
?>
    <h3><?php echo $h3actterm; ?></h3>
    <table>
        <tr>
            <th><?php echo $tipoact;?></th>
            <th><?php echo $issiold;?></th>
            <th><?php echo $issinew;?></th>
            <th>TEI</th>
            <th>Número K</th>
            <th><?php echo $acciones;?></th>
        </tr>
<?php
        for($i = 0; $i < $nactterm; $i++){
            $row_actterm = mysql_fetch_array($res_actterm);
            $idactterm = $row_actterm["ID"];
?>
            <tr>
                <td><?php echo $row_actterm["TIPOACT"]; ?></td>
                <td><?php echo $row_actterm["ISSIOLD"]; ?></td>
                <td><?php echo $row_actterm["ISSINEW"]; ?></td>
                <td><?php echo $row_actterm["TEI"]; ?></td>
                <td><?php echo $row_actterm["NUMEROK"]; ?></td>
                <td>
                    <a href="#" id="det-<?php echo $idactterm;?>" class="detactterm"><img src="imagenes/consulta.png" alt="<?php echo $detalle;?>" title="<?php echo $detalle;?>"></a>
                </td>
            </tr>
<?php
        }
?>
    </table>
<?php
}
?>