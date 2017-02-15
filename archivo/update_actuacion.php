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
$lang = "idioma/actupd_$idioma.php";
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
     // Seleccionamos la BBDD y codificamos la conexión en UTF-8:
    if (!mysql_select_db($base_datos, $link)) {
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
        exit;
    }
    mysql_set_charset('utf8', $link);
}
// ------------------------------------------------------------------------------------- //

import_request_variables("gp", "");

$permiso = 0;
/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
if ($usu != ""){
    $sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
    $res_oficina = mysql_query($sql_oficina);
    $row_oficina = mysql_fetch_array($res_oficina);
    $flota_usu = $row_oficina["ID"];
    /*
    *  $permiso = variable de permisos de flota:
    *      0: Sin permiso
    *      1: Permiso de consulta
    *      2: Permiso de modificación
    */

    if ($flota_usu == 100) {
        $permiso = 2;
    }
    elseif ($flota_usu == $idflota) {
        $permiso = 1;
    }
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
<?php
        if ($usu == ""){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript">
            // Funciones JQuery
            $(function(){
                // Enviar el formulario una vez se ha cargado la página
                $("form#formupd").submit();
            })
        </script>
    </head>
    <body>
<?php
if ($permiso > 0) {
    // Nueva Actuación
    if ($origen == "nueva") {
        $enlaceok = "actterminales.php";
        $mensok = $mensnewact;
        $enlacefail = "nueva_actuacion.php";
        $mensfail = $errnewact;
        
        // Construimos la consulta para guardar la Actuación:
        // Fecha
        $fecha = date("Y-m-d");
        // Obtenemos los datos de la flota
        $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota == 0) {
            echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
        }
        else {
            $row_flota = mysql_fetch_array($res_flota);
            $acronimo = $row_flota["ACRONIMO"];
        }
        // Obtenemos el número de actuaciones para obtener la referencia:
        $refini = $fecha."_".$acronimo;
        $sql_actflota = "SELECT * FROM actuaciones WHERE REFERENCIA LIKE '$refini%'";
        $res_actflota = mysql_query($sql_actflota) or die("Error en la consulta de actuaciones de la Flota: " . mysql_error());
        $nactflota = mysql_num_rows($res_actflota) + 1;
        if ($nactflota < 10){
            $nactflota = "0".$nactflota;
        }
        $referencia = $refini."_".$nactflota;
        // Insertamos los datos en la consulta:
        $sql_update = "INSERT INTO actuaciones (REFERENCIA, ESTADO, FLOTA_ID, FPETICION, NOMBRE, CARGO, TELEFONO, MAIL) ";
        $sql_update .= "VALUES ('$referencia', 'CREADO', '$idflota', '$fecha', '$contnom',  '$contcargo', '$conttelf', '$contmail')";
        $res_update = mysql_query($sql_update) or die (mysql_error($link));
        $idactuacion = mysql_insert_id();
    }
    if ($res_update){
        $enlace = $enlaceok;
        $mensflash = $mensok;
        $update = "OK";
    }
    else{
        $enlace = $enlacefail;
        $mensflash = $mensfail;
        $update = "KO";
    }
?>
        <h1><?php echo $titulo; ?></h1>
        <form name="formupd" id="formupd" action="<?php echo $enlace;?>" method="POST">
            <input name="idactuacion" type="hidden" value="<?php echo $idactuacion;?>">
<?php
            if ($permiso == 2){
?>
                <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
<?php
            }
?>
            <input name="update" type="hidden" value="<?php echo $update;?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
        </form>
        
<?php
    }
    else {
?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
    }
?>
    </body>
</html>
