<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/buzonupd_$idioma.php";
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
if ($permiso == 2) {
    if ($origen == "nuevo") {
        $enlacefail = "buzones.php";
        $enlaceok = "detalle_buzon.php";
        $titulo = $titnew;
        // Comprobamos si el nombre o el acrónimo están ya utilizados:
        $sql_checkrep = "SELECT * FROM buzons WHERE (NOMBRE = '" . $nombre . "')" ;
        $sql_checkrep .= " OR (ACRONIMO = '" . $acronimo . "')" ;
        $res_checkrep = mysql_query($sql_checkrep) or die (mysql_error($link));
        $ncheckrep = mysql_num_rows($res_checkrep);
        if ($ncheckrep > 0){
            $res_update = false;
            $errnew .= ". " . $errrep;
        }
        else{
            $sql_update = "INSERT INTO buzons (NOMBRE, ACRONIMO, ACTIVO)";
            $sql_update .= "VALUES ('" . $nombre . "', '" . $acronimo . "', '" . $activo . "')";
            $res_update = mysql_query($sql_update) or die (mysql_error($link));
            $buzon_id = mysql_insert_id($link);
        }
        $mensaje = $mensnew;
        $error = $errnew;
    }
    
    if ($origen == "editar") {
        $enlaceok = $enlacefail = "detalle_buzon.php";
        $titulo = $titedi;
        // Comprobamos si el nombre o el acrónimo están ya utilizados:
        $sql_checkrep = "SELECT * FROM buzons WHERE ((NOMBRE = '" . $nombre . "') OR (ACRONIMO = '" . $acronimo . "'))" ;
        $sql_checkrep .= " AND (ID <> '" . $buzon_id . "')" ;
        $res_checkrep = mysql_query($sql_checkrep) or die (mysql_error($link));
        $ncheckrep = mysql_num_rows($res_checkrep);
        if ($ncheckrep > 0){
            $res_update = false;
            $erredi .= ". " . $errrep;
        }
        else{
            $sql_update = "UPDATE buzons SET NOMBRE = '$nombre', ACRONIMO = '$acronimo', ACTIVO='$activo' WHERE ID = $buzon_id";
            $res_update = mysql_query($sql_update) or die (mysql_error($link) . $sql_update);
        }
        $mensaje = $mensedi;
        $error = $erredi;
    }
    
    if ($origen == "borrar") {
        $enlaceok = $enlacefail = "buzones.php";
        $titulo = $titdel;
        // Comprobamos si el nombre o el acrónimo están ya utilizados:
        $sql_checkbuz = "SELECT * FROM buzons WHERE (ID = '" . $buzon_id . "')" ;
        $res_checkbuz = mysql_query($sql_checkbuz) or die (mysql_error($link));
        $ncheckbuz = mysql_num_rows($res_checkbuz);
        if ($ncheckbuz > 0){
            // Borramos primero los datos de la tabla flotas_buzones:
            $sql_delbuzflo = "DELETE FROM flotas_buzons WHERE BUZON_ID = $buzon_id";
            $res_delbuzflo = mysql_query($sql_delbuzflo) or die ("Error al borrar las flotas del buzón:" . mysql_error($link));
            $sql_update = "DELETE FROM buzons WHERE ID = $buzon_id";
            $res_update = mysql_query($sql_update) or die ("Error al borrar el buzón:" . mysql_error($link));
        }
        else{
            $res_update = false;
            $errdel .= ". " . $errnobuz;
        }
        $mensaje = $mensdel;
        $error = $errdel;
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
        <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="<?php echo $enlace;?>" method="POST">
            <input name="buzon_id" type="hidden" value="<?php echo $buzon_id;?>">
            <input name="update" type="hidden" value="<?php echo $update;?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
        </form>
         <script language="javascript" type="text/javascript">
            document.formupd.submit();
         </script>
         <noscript>
                <input type="submit" value="Submit">
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
