<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/archupd_$idioma.php";
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
    if ($permiso > 1) {
        $errarch = $_FILES["archivo"]["error"];
        if ($errarch > 0) {
            $enlacefail = "excel_flota.php";
            $res_update = false;
            $error = $errupload . $errfile[$errarch];
        }
        else{
            if ($_FILES["archivo"]["size"] > 5000000){
                $res_update = false;
                $enlacefail = "excel_flota.php";
                $error = $errupload.$errupmax;
            }
            //elseif (($_FILES["archivo"]["type"] != "application/vnd.ms-excel")&&($_FILES["archivo"]["type"] != "application/x-msexcel")){
            /*elseif(strpos($_FILES["archivo"]["name"], 'xls') == FALSE){
                $res_update = false;
                $enlacefail = "excel_flota.php";
                $error = $errupload.$erruptype;
                //$error = "Error de tipo de fichero: " . $finfo->file($_FILES['archivo']['tmp_name']);
            }*/
            else {
                $nombre_fichero = "flotas/flotas_cdd.txt";
                if ($accion == "NO"){
                    $res_update = false;
                    $enlacefail = "cargartxt.php";
                    $error = "Error: $errnoacc";
                }
                else{
                    if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $nombre_fichero)){
                        $res_update = true;
                        // Comprobamos la accion a realizar:
                        switch ($accion) {
                            case "FLOTAS":{
                                $enlaceok = "leerflotas.php";
                                break;
                            }
                            case "TERMINALES":{
                                $enlaceok = "leerissis.php";
                                break;
                            }
                            default:{
                                $res_update = false;
                                unlink($fichero);
                                $enlacefail = "cargartxt.php";
                                $error = "Error: $erraccnov.";
                                break;
                            }
                        }
                    }
                    else{
                        $res_update = false;
                        $error = $errmoveup;
                        $enlacefail = "cargartxt.php";
                        $imagen = "imagenes/atras.png";
                        $texto = $volver;
                    }
                }
            }
        }

        if ($res_update){
            $enlace = $enlaceok;
            $mensflash = $mensaje;
            $update = "OK";
        }
        else{
            $enlace = $enlacefail;
            $mensflash = $error.  mysql_error();
            $update = "KO";
        }
?>
        <h1><?php echo $title; ?></h1>
        <form name="formupd" action="<?php echo $enlace;?>" method="POST">
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
