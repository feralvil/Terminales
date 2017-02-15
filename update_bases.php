<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/baseupd_$idioma.php";
include ($lang);

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

// Importamos las variables de formulario:
import_request_variables("gp", "");

/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación (Oficina COMDES)
 */
// Obtenemos el usuario
include_once('auth_user.php');

$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $title; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
<?php
        if ($flota_usu == 0){
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
            if ($origen == "terminal") {
                $enlace = "detalle_terminal.php";
                $valuehid = $idterm;
                $namehid = "idterm";
                if ($accbase == "add") {
                    $titulo = "$anyadir $titterm $issi a la Flota $flotatxt";
                    $mensaje = $mensadd;
                    $error = $erradd;
                    $sql_base = "SELECT * FROM bases WHERE TERMINAL='$idterm' AND FLOTA='$idflota'";
                    $res_base = mysql_query($sql_base) or die("Error en la consulta de Bases: " . mysql_error());
                    $nbase = mysql_num_rows($res_base);
                    if ($nbase > 0) {
                        $res_update = false;
                        $error = "$erradd La flota $flotatxt $rep1 $issi $base";
                    }
                    else {
                        $sql_update = "INSERT INTO bases (TERMINAL, FLOTA, MUNICIPIO) ";
                        $sql_update = $sql_update . "VALUES ('$idterm', '$idflota', '$ineselect')";
                        $res_update = mysql_query($sql_update);
                        $error = $erradd;
                    }
                }
                else {
                    $titulo = "$eliminar $titterm $issi a la Flota $flotatxt";
                    $sql_update = "DELETE FROM bases WHERE TERMINAL = '$idterm' AND FLOTA = '$idflota'";
                    $res_update = mysql_query($sql_update);
                    $mensaje = $mensdel;
                    $error = $errdel;
                }
            }
            if ($origen == "flota") {
                $enlace = "detalle_flota.php";
                $valuehid = $idflota;
                $namehid = "idflota";
                $titulo = "$anyadir $titterm $issi a la Flota $flotatxt";
                $mensaje = $mensadd;
                $sql_base = "SELECT * FROM bases WHERE TERMINAL='$idterm' AND FLOTA='$idflota'";
                $res_base = mysql_query($sql_base) or die("Error en la consulta de Bases: " . mysql_error());
                $nbase = mysql_num_rows($res_base);
                if ($nbase > 0) {
                    $res_update = false;
                    $error = "$erradd La flota $flotatxt $rep1 $issi $base";
                }
                else {
                    $sql_update = "INSERT INTO bases (TERMINAL, FLOTA, MUNICIPIO) ";
                    $sql_update = $sql_update . "VALUES ('$idterm', '$idflota', '$ineselect')";
                    $res_update = mysql_query($sql_update);
                    $error = $erradd;
                }
            }
            if ($res_update){
                $mensflash = $mensaje;
                $update = "OK";
            }
            else{
                $mensflash = $error.mysql_error();
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
            <h1><?php echo $h1perm ?></h1>
            <p class='error'><?php echo $permno ?></p>
<?php
        }
?>
    </body>
</html>
