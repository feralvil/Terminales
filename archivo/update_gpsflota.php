<?php
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

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotagps_$idioma.php";
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
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $title; ?></title>
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
    </head>
    <body>
<?php
if ($permiso == 2) {
    $enlace = "detalle_flota.php";
    $namehid = "idflota";
    $valuehid = $idflota;
    if ($origen == "eliminar") {
        $titulo = "$titdelgps $flota_org ($acro_org)";
        $sql_update = "DELETE FROM gpsusuarios WHERE ID='$idgps'";
        $mensaje = $mensdel;
        $error = $errdel;
    }
    if ($origen == "nuevo") {
        $titulo = "$titaddgps $flota_org ($acro_org)";
        $fecha = date("Y-m-d");
        $password = md5($passwd1);
        $sql_update = "INSERT INTO gpsusuarios (FLOTA, USUARIO, PASSWORD, CREADO) ";
        $sql_update .= "VALUES ('$idflota', '$usuario', '$password', '$fecha')";
        $mensaje = $mensadd;
        $error = $erradd;
    }
    if ($origen == "editar") {
        $titulo = "$titmodgps $flota_org ($acro_org)";
        $fecha = date("Y-m-d");
        $password = md5($passwd1);
        $sql_update = "UPDATE gpsusuarios SET USUARIO = '$usuario', PASSWORD = '$password', ";
        $sql_update .= "MODIFICADO = '$fecha' WHERE ID = '$idgps'";
        $mensaje = $mensmod;
        $error = $errmod;
    }

    $res_update = mysql_db_query($base_datos, $sql_update) or die (mysql_error($link));
    if ($res_update){
        $mensflash = $mensaje;
        $update = "OK";
    }
    else{
        $mensflash = $error.  mysql_error();
        $update = "KO";
    }
?>
        <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="<?php echo $enlace;?>" method="POST">
            <input name="<?php echo $namehid;?>" type="hidden" value="<?php echo $valuehid;?>">
            <input name="update" type="hidden" value="<?php echo $update;?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
        </form>
         <script language="javascript" type="text/javascript">
            document.formupd.submit();
         </script>
         <noscript>
                <input type="submit" value="verify submit">
         </noscript>
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
