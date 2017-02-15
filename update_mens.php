<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/updmens_$idioma.php";
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
        echo 'Error al seleccionar la Base de Datos: ' . mysql_error();
        exit;
    }
    mysql_set_charset('utf8', $link);
}
// ------------------------------------------------------------------------------------- //

// Importamos las variables de formulario:
import_request_variables("p", "");

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
    if ($tipo == "M"){
        $titulo = $titmail;
        $h1 = $h1mail;
        $errmensaje = $errmensmail;
        $errupd = $errupdmail;
        $mensok = $mensokmail;
        $actionok = "mail_flotas.php";
        $actionerr = "compmail.php";
    }
    elseif ($tipo == "N"){
        $titulo = $titnot;
        $h1 = $h1not;
        $errmensaje = $errmensnot;
        $errupd = $errupdnot;
        $mensok = $mensoknot;
        $actionok = "not_flotas.php";
        $actionerr = "compnot.php";
    }
    $error = false;
    if (($asunto == "") || ($mensaje == "")) {
        $error = true;
        if ($asunto == "") {
            $menserror = "Error: $errasunto";
        }
        else {
            $menserror = "Error: $errmensaje";
        }
    }
    else {
        $fecha = date("Y-m-d H:i:s");
        if ($origen == "new") {
            $sql_update = "INSERT INTO mensajes (FCREACION, FMODIFICA, TIPO, ASUNTO, MENSAJE)";
            $sql_update .= "VALUES ('$fecha', '$fecha', '$tipo', '$asunto', '$mensaje')";
        }

        if ($origen == "editar") {
            $sql_update = "UPDATE mensajes SET FMODIFICA = '$fecha', ASUNTO = '$asunto', ";
            $sql_update .= "MENSAJE = '$mensaje' WHERE ID = '$idm'";
        }
    }
    if ($error) {
        $res_update = false;
        $action = $actionerr;
        $mensflash = $menserror;
        $update = "KO";
    }
    else {
        $res_update = mysql_query($sql_update);
        if ($res_update) {
            if ($origen == "new") {
                $idm = mysql_insert_id();
            }
            $action = $actionok;
            $mensflash = $mensok;
            $update = "OK";
        }
        else {
            $action = $actionerr;
            $mensflash = $errupd . ": " . mysql_error();
            $update = "KO";
        }
    }
?>
        <h1><?php echo $titulo; ?></h1>
        <form name="formmens" action="<?php echo $action; ?>" method="POST">
            <input name="idm" type="hidden" value="<?php echo $idm; ?>">
            <input name="update" type="hidden" value="<?php echo $update; ?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash; ?>">
        </form>
        <script language="javascript" type="text/javascript">
            document.formmens.submit();
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