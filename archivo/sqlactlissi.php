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
<h3><?php echo $h4res; ?></h3>
<?php
    if((isset($issibusca))&&($issibusca != "")){
        $sql_issi = "SELECT * FROM terminales WHERE ISSI LIKE '%$issibusca%' AND FLOTA = '$idflota' ORDER BY ISSI ASC";
        $res_issi = mysql_query($sql_issi) or die("Error en la consulta de ISSI" . mysql_error());
        $nissi = mysql_num_rows($res_issi);
        if ($nissi == 0){
?>
            <p class="error"><?php echo $errnoissi; ?> </p>
<?php
        }
        else{
?>
            <h4><?php echo $h4res; ?></h4>
            <form name="datosterm" id="datosterm" action="<?php echo $enlace ;?>" method="POST">
                <input type="hidden" name="idactuacion" value="<?php echo $idactuacion; ?>" />
                <input type="hidden" name="tactissi" value="<?php echo $tactissi ;?>" />
                <table>
                    <tr>
                        <th>Seleccionar</th>
                        <th><?php echo $tipotxt; ?></th>
                        <th>ISSI</th>
                        <th>TEI</th>
                        <th>Marca</th>
                        <th><?php echo $modtxt; ?></th>
                        <th><?php echo $mnemo; ?></th>
                        <th>Carpeta</th>
                    </tr>
<?php
                    for ($i = 0; $i < $nissi; $i++){
                        $row_term = mysql_fetch_array($res_issi);
?>
                        <tr<?php if (($i % 2) == 1) echo " class='filapar'" ?>>
                            <td><?php echo $row_term["ID"] ;?></td>
                            <td><?php echo $row_term["TIPO"] ;?></td>
                            <td><?php echo $row_term["ISSI"] ;?></td>
                            <td><?php echo $row_term["TEI"] ;?></td>
                            <td><?php echo $row_term["MARCA"] ;?></td>
                            <td><?php echo $row_term["MODELO"] ;?></td>
                            <td><?php echo $row_term["MNEMONICO"] ;?></td>
                            <td><?php echo $row_term["CARPETA"] ;?></td>
                        </tr>
<?php
                    }
?>
                </table>                
            </form>
<?php
        }
    }
?>
<table>
    <tr>
        <td class="borde">
            <a href='#' id="cancelar"><img src='imagenes/no.png' alt='<?php echo $cancelar; ?>' title='<?php echo $cancelar; ?>'></a><br><?php echo $cancelar; ?>
        </td>
    </tr>
</table>