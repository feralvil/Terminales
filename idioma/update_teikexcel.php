<?php
// Revisado 2011-08-09
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
$lang = "idioma/teikexc_$idioma.php";
include ($lang);

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;
// ------------------------------------------------------------------------------------- //
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
}
else {
    // Codificación de carácteres de la conexión a la BBDD
    mysql_set_charset('utf8', $link);
    // Seleccionamos la base de datos
    mysql_select_db($base_datos);
}
// ------------------------------------------------------------------------------------- //

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_db_query($base_datos, $sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación
 */
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}

/*
* Importamos la clase necesaria para leer el fichero Excel con los datos de la flota
*/
require_once 'excelreader/reader.php';

// Instanciamos la clase
$libro = new Spreadsheet_Excel_Reader();

// Conversión de codificación de carácteres
$libro->setUTFEncoder('mb');
$libro->setOutputEncoding('UTF-8');

// Accedemos al fichero
$fichero = "flotas/$idflota.xls";
$libro->read($fichero);

//Gestión de errores
error_reporting(E_ALL ^ E_NOTICE);
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
<?php
    if ($permiso == 2) {
        $sql_flota = "SELECT * from FLOTAS WHERE ID = '$idflota'";
        $res_flota = mysql_db_query($base_datos, $sql_flota);
        $res_update = true;
        if ($res_flota){
            $nflota = mysql_num_rows($res_flota);
            if ($nflota == 0){
                $res_update = false;
                $error = $error_noflota;
            }
            else{
                $row_flota = mysql_fetch_array($res_flota);
                $flota = $row_flota["FLOTA"];
                //Leemos los datos de a hoja:
                $fila = 19;
                $repetido = false;
                $errores = $termok = 0;
                $issierr = "";
                while (($libro->sheets[1]['cells'][$fila][8]!= "")&&($errores == 0)) {
                    $issi = $libro->sheets[1]['cells'][$fila][8];
                    $tei = $libro->sheets[1]['cells'][$fila][9];
                    $numk = $libro->sheets[1]['cells'][$fila][18];
                    $sql_issi = "SELECT * FROM terminales WHERE ISSI = '$issi' AND FLOTA = '$id'";
                    $res_issi = mysql_query($sql_issi, $link) or die ($errissi."$issi: ". mysql_error());
                    $nissi = mysql_num_rows($res_issi);
                    if ($nissi != 1){
                        $error = "$errissi $issi $errissi2";
                        $errores++;
                        $res_update = false;
                    }
                    else{
                        $row_issi = mysql_fetch_array($res_issi);
                        $idterm = $row_issi["ID"];
                        $sql_updterm = "UPDATE terminales SET TEI = '$tei', ";
                        $sql_updterm .= "NUMEROK = '$numk' WHERE ID = '$idterm'";
                        $res_updterm = mysql_query($sql_updterm);
                        $res_update = ($res_update && $res_updterm);
                        $fila++;
                        $termok++;
                    }
                }
            }
        }
        else {
            $res_update = false;
            $error = "Error en la consulta de flota: ".mysql_error();
        }
        if ($res_update){
            $enlace = "terminales.php";
            $imagen = "imagenes/adelante.png";
            $mensaje = "$nterm $mensaje";
            $nominput = "id";
            unlink($fichero);
        }
        else{
            $enlace = "detalle_flota.php";
            $imagen = "imagenes/atras.png";
            $nominput = "flota";
        }
?>
        <h1><?php echo "$titulo $flota";?></h1>
        <div class="centro">
            <form name="leerterm" action="<?php echo $enlace;?>" method="POST">
                <input name="<?php echo $nominput;?>" type="hidden" value="<?php echo $idflota;?>">
<?php
            if ($res_update) {
?>
                <p><img src='imagenes/clean.png' alt='OK' title="OK"></p>
                <p><?php echo $mensaje; ?></p>
                <p><input type="image" name="accion" src='<?php echo $imagen; ?>' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>"><BR><?php echo $volver; ?></p>
<?php
            }
            else {
?>
                <p><img src='imagenes/error.png' alt='Error' title="Error"></p>
                <p class="error"><?php echo $error." ".mysql_error(); ?></p>
                <p><input type="image" name="accion" src='<?php echo $imagen; ?>' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>"><BR><?php echo $volver; ?></p>
<?php
            }
?>
            </form>
        </div>
<?php
    }
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
