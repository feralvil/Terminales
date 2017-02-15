<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termbusca_$idioma.php";
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
else {
    if ($flota_usu == $id_flota) {
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
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu == ""){
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
    $res_consulta = false;
    $error = $errpermno;
    $idterm = $prevterm;
    if ($permiso != 0) {
        if ($issi == ""){
            $error = $errissivac;
        }
        else {
            $sql_terminal = "SELECT * FROM terminales WHERE ISSI = '$issi' AND FLOTA = '$idflota'";
            $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
            $nterminal = mysql_num_rows($res_terminal);
            if ($nterminal == 0) {
                $error = sprintf($errissino, $issi);
            }
            else {
                $res_consulta = true;
                $mensaje = sprintf($mensissi, $issi);
                $row_terminal = mysql_fetch_array($res_terminal);
                $idterm = $row_terminal["ID"];
            }
        }
    }
    if ($res_consulta){
        $update = "OK";
        $mensflash = $mensaje;
    }
    else{
        $update = "KO";
        $mensflash = $error;
    }
?>
        <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="detalle_terminal.php" method="POST">
            <input name="idterm" type="hidden" value="<?php echo $idterm;?>">
            <input name="update" type="hidden" value="<?php echo $update;?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
             <script language="javascript" type="text/javascript">
                document.formupd.submit();
             </script>
             <noscript>
                    <input type="submit" value="Continuar">
             </noscript>
        </form>
    </body>
</html>
