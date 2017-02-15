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

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotamail_$idioma.php";
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
        echo 'Error al seleccionar la Base de Datos: ' . mysql_error();
        exit;
    }
    mysql_set_charset('utf8', $link);
}
// ------------------------------------------------------------------------------------- //

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_query($sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}

// Iniciamos las funciones de correo:
ini_set("SMTP", "smtp.gva.es");
ini_set('sendmail_from', "info_comdes@gva.es");
$ndestinatarios = 0;
$destinatarios = "";
$mailnom = array("Fernando Alfonso", "Manuel Cava", "Santiago Vieco");
$mailadr = array("alfonso_fer@gva.es", "cava_man@gva.es", "vieco_san@gva.es");
// Obtenemos el mensaje de la BBDD
if (isset ($idm)){
    $sql_mensaje = "SELECT * FROM mensajes WHERE ID='$idm'";
    $res_mens = mysql_query($sql_mensaje) or die($errmens.": ".mysql_error());
    $nmens = mysql_num_rows($res_mens);
    if ($nmens > 0){
        $row_mens = mysql_fetch_array($res_mens);
        $mensbbdd = $row_mens["MENSAJE"];
        $asunto = $row_mens["ASUNTO"];
    }
}
$mensaje = "
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
";
$mensaje .= "
<title>$asunto</title>
</head>
<body>";
$mensaje .="
    $mensbbdd
    ";
$mensaje .="
    </body>
    </html>
    ";
$tabladest = "
    <table>
        <tr>
            <th>Flota</th>
            <th>Nombre</th>
            <th>Mail</th>
        </tr>
    ";

// Consulta a la base de datos
$sql_flotas = "SELECT ID, FLOTA FROM flotas ORDER BY FLOTA ASC";
$res_flotas = mysql_query($sql_flotas) or die(mysql_error());
$nfilas = mysql_num_rows($res_flotas);
$par = 0;
for ($j = 0; $j < $nfilas; $j++) {
    $row_flota = mysql_fetch_array($res_flotas);
    $flotaid = $row_flota["ID"];
    $idc = $idcont[$j];
    $enviar = true;
    if (!(empty($idflota))) {
        $enviar = false;
        if (in_array($flotaid, $idflota)){
            $enviar = true;
        }
    }
    if ($enviar){
        if ($idc != 0){
            $sql_contacto = "SELECT NOMBRE, MAIL FROM contactos WHERE ID = '$idc'";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto != 0) {
                $row_contacto = mysql_fetch_array($res_contacto);
                $flota = $row_flota["FLOTA"];
                $email = $row_contacto["MAIL"];
                $pos = strpos($email, " / ");
                if ($pos !== FALSE){
                    $email = substr($email, 0, $pos+1);
                }
                $email = trim($email);
                $nombre = $row_contacto["NOMBRE"];
                $dest = $flota." <".$email.">";
                $destinatarios .= $dest.", ";
                $tr = "<tr>";
                if (($par % 2) == 1){
                    $tr = "<tr class='filapar'>";
                }
                $tabladest .= $tr;
                $tabladest .= "
                        <td>$flota</td>
                        <td>$nombre</td>
                        <td>$email</td>
                    </tr>
                ";
                $par++;
            }
        }
    }
} //primer for
$tabladest .= "
        </table>
    ";

$mailto = $destinatarios;//"alfonso_fer@gva.es";//

// Para enviar un correo HTML mail, la cabecera Content-type debe fijarse
$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
$cabeceras .= 'Content-type: text/html; charset=utf-8' . "\r\n";
// Cabeceras adicionales
$cabeceras .= "To: $mailto \r\n";
$cabeceras .= 'From: Oficina COMDES <info_comdes@gva.es>' . "\r\n";

// Mail it
$res_mail = mail($mailto, $asunto, $mensaje, $cabeceras);
?>
<html>
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($usu == ""){
?>
            <script type="text/javascript">
                window.top.location.href = "https://comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
    </head>
    <body>
<?php
    if ($res_mail){
?>
        <p class="centro"><img src='imagenes/clean.png' alt='OK' title="OK"></p>
        <p><?php echo $mailenv; ?></p>
        <hr />
        <?php echo $mensbbdd; ?>
        <hr />
        <p><?php echo $maildest; ?></p>
        <?php echo $tabladest; ?>
<?php
    }
    else{
?>
        <p class="centro"><img src='imagenes/error.png' alt='Error' title="Error"></p>
        <p><?php echo $mailerror; ?></p>
<?php
    }
?>
    </body>
</html>
