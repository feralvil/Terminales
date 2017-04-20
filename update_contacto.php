<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contupd_$idioma.php";
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
        <title><?php echo $titulo; ?></title>
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
        $enlacefail = $enlaceok = 'contactos_flota.php';
        $res_update = false;
        if ($origen == "editar"){
            $titulo = $titedi . ": " . $rolestxt[$rol];
            $mensaje = $mensedi . ": " . $rolestxt[$rol];
            $error = $erredi . ": " . $rolestxt[$rol];
            $sql_update = "UPDATE contactos SET NOMBRE='$nombre', NIF='$nif', CARGO='$cargo', ";
            $sql_update = $sql_update . "TELEFONO='$telefono', MAIL='$mail' WHERE ID=$idcont";
            $res_update = mysql_query($sql_update) or die ("Error al modificar contacto: " . mysql_error($link));
        }
        if ($origen == "borrar"){
            $titulo = $titdel . ": " . $rolestxt[$rol];
            $mensaje = $mensdel . ": " . $rolestxt[$rol];
            $error = $errdel . ": " . $rolestxt[$rol];
            // Obtenemos el orden
            if ($rol != 'RESPONSABLE'){
                $sql_check = "SELECT * FROM contactos_flotas WHERE (ID = $idcf)";
                $res_check = mysql_query($sql_check) or die ("Error en la consulta de comprobación: " . mysql_error($link));
                $ncheck = mysql_num_rows($res_check);
                if ($ncheck > 0){
                    $row_contflo = mysql_fetch_array($res_check);
                    $idflota = $row_contflo['FLOTA_ID'];
                    $rol = $row_contflo['ROL'];
                    $orden = $row_contflo['ORDEN'];
                    $sql_contflota = "SELECT * FROM contactos_flotas WHERE (FLOTA_ID = $idflota) AND (ROL = '$rol') AND (ORDEN > $orden)";
                    $res_contflota = mysql_query($sql_contflota) or die ("Error en la consulta de los contactos de Flota: " . mysql_error($link));
                    $ncontflota = mysql_num_rows($res_contflota);
                    if ($ncontflota > 0){
                        for ($i = 0; $i < $ncontflota; $i++){
                            $row_contflota = mysql_fetch_array($res_contflota);
                            $idcontflota = $row_contflota['ID'];
                            $orden = $row_contflota['ORDEN'] - 1;
                            $sql_updorden = "UPDATE contactos_flotas SET ORDEN = $orden WHERE ID = $idcontflota";
                            $res_update = mysql_query($sql_updorden) or die ("Error al modificar el orden de los contactos: " . mysql_error($link));
                        }
                    }
                }
            }
            $sql_update = "DELETE FROM contactos_flotas WHERE ID = " . $idcf;
            $res_update = mysql_query($sql_update) or die ("Error al borrar contacto: " . mysql_error($link));
        }
        if ($origen == "addexist"){
            $rolindex = substr($rol, 0, 3);
            $titulo = $titexist. ": " . $rolestxt[$rolindex];
            $mensaje = $mensedi. ": " . $rolestxt[$rolindex];
            $error = $erredi . ": " . $rolestxt[$rolindex];
            if ($idcont > 0){
                $sql_check = "SELECT * FROM contactos_flotas WHERE (FLOTA_ID = $idflota) AND";
                $sql_check .= " (CONTACTO_ID = $idcont) AND (ROL = '$rol')";
                $res_check = mysql_query($sql_check) or die ("Error en la consulta de comprobación: " . mysql_error($link));
                $ncheck = mysql_num_rows($res_check);
                if ($ncheck > 0){
                    $error .= ". " . $errcontrep;
                }
                else {
                    // Obtenemos el orden
                    if ($rol == 'RESPONSABLE'){
                        $orden = 0;
                    }
                    else{
                        $orden = 1;
                        $sql_orden = "SELECT MAX(ORDEN) AS MAXORDEN FROM contactos_flotas WHERE (FLOTA_ID = $idflota) AND (ROL = '$rol')";
                        $res_orden = mysql_query($sql_orden) or die ("Error en la consulta del orden de contactos: " . mysql_error($link));
                        $norden = mysql_num_rows($res_orden);
                        if ($norden > 0){
                            $row_orden = mysql_fetch_array($res_orden);
                            $orden = $row_orden['MAXORDEN'] + 1;
                        }
                    }
                    $sql_update = "INSERT INTO contactos_flotas (CONTACTO_ID, FLOTA_ID, ROL, ORDEN)";
                    $sql_update .= " VALUES ($idcont, $idflota, '$rol', $orden)";
                    $res_update .= mysql_query($sql_update) or die ("Error al insertar contacto existente: " . mysql_error($link));
                }
            }
            else{
                $error .= ". " . $errnocontexist;
            }
        }
        if ($origen == "addnew"){
            $rolindex = substr($rol, 0, 3);
            $titulo = $titnew . ": " . $rolestxt[$rolindex];
            $mensaje = $mensnew . ": " . $rolestxt[$rolindex];
            $error = $errnew . ": " . $rolestxt[$rolindex];
            if ($nombre == ""){
                $error .= ". " . $errnocontnew;
            }
            else{
                $sql_check = "SELECT * FROM contactos WHERE (NOMBRE = '$nombre')";
                $res_check = mysql_query($sql_check) or die ("Error en la consulta de comprobación: " . mysql_error($link));
                $ncheck = mysql_num_rows($res_check);
                if ($ncheck > 0){
                    $error .= ". " . $errcontnewrep;
                }
                else {
                    $sql_insert = "INSERT INTO contactos (NOMBRE, CARGO, NIF, MAIL, TELEFONO)";
                    $sql_insert .= " VALUES ('$nombre', '$cargo', '$nif', '$mail', '$telefono')";
                    $res_insert = mysql_query($sql_insert) or die ("Error al insertar nuevo contacto: " . mysql_error($link));
                    $idcont = mysql_insert_id($link);
                    // Obtenemos el orden
                    if ($rol == 'RESPONSABLE'){
                        $orden = 0;
                    }
                    else{
                        $orden = 1;
                        $sql_orden = "SELECT MAX(ORDEN) AS MAXORDEN FROM contactos_flotas WHERE (FLOTA_ID = $idflota) AND (ROL = '$rol')";
                        $res_orden = mysql_query($sql_orden) or die ("Error en la consulta del orden de contactos: " . mysql_error($link));
                        $norden = mysql_num_rows($res_orden);
                        if ($norden > 0){
                            $row_orden = mysql_fetch_array($res_orden);
                            $orden = $row_orden['MAXORDEN'] + 1;
                        }
                    }
                    $sql_update = "INSERT INTO contactos_flotas (CONTACTO_ID, FLOTA_ID, ROL, ORDEN)";
                    $sql_update .= " VALUES ($idcont, $idflota, '$rol', $orden)";
                    $res_update = mysql_query($sql_update) or die ("Error al insertar nuevo contacto en la flota: " . mysql_error($link));
                }
            }
        }

        if ($origen == "limpiar"){
            $enlacefail = $enlaceok = 'flotas.php';
            $titulo = $titlimpiar;
            $mensaje = $menslimpiar;
            $error = $errlimpiar;
            //datos de la tabla Contactos
            $sql_contactos = "SELECT ID, NOMBRE FROM contactos ORDER BY NOMBRE ASC";
            $res_contactos = mysql_query($sql_contactos) or die($errsqlcont . ": " . mysql_error());
            $ncontactos = mysql_num_rows($res_contactos);
            for($i = 0; $i < $ncontactos; $i++){
                $row_contacto = mysql_fetch_array($res_contactos);
                $idcont = $row_contacto['ID'];
                $sql_contflotas = "SELECT * FROM contactos_flotas WHERE CONTACTO_ID = " . $idcont;
                $res_contflotas = mysql_query($sql_contflotas) or die($errsqlcf . " = " . $idcont . mysql_error());
                $ncontcf = mysql_num_rows($res_contflotas);
                $nresporg = 0;
                if ($ncontcf == 0){
                    $sql_resporg = "SELECT * FROM organizaciones WHERE RESPONSABLE = " . $idcont;
                    $res_resporg = mysql_query($sql_resporg) or die($errresporg . " = " . $idcont . mysql_error());
                    $nresporg = mysql_num_rows($res_resporg);
                }
                $ncont = $ncontcf + $nresporg;
                if ($ncont == 0){
                    $sql_delete = "DELETE FROM contactos WHERE ID = " . $idcont;
                    $res_update = mysql_query($sql_delete) or die ("Error al borrar el contacto: " . mysql_error($link));
                }
            }
        }

        if ($origen == "ordenar"){
            $enlacefail = $enlaceok = 'contactos_orden.php';
            $titulo = $titorden;
            $error = $errorden;
            $norden = 0;
            // Seleccionamos contactos:
            $roles = array('OPERATIVO', 'TECNICO', 'CONT24H');
            foreach ($roles as $rol) {
                $sql_contflota = "SELECT * FROM contactos_flotas WHERE (FLOTA_ID = $idflota) AND (ROL = '$rol') ORDER BY ORDEN ASC";
                $res_contflota = mysql_query($sql_contflota) or die("Error en la consulta de contactos de Flota: " . mysql_error());
                $ncontflota = mysql_num_rows($res_contflota);
                $orden = 1;
                $ordencont = array();
                for ($i = 0; $i < $ncontflota; $i++){
                    $row_contflota = mysql_fetch_array($res_contflota);
                    $idcf = $row_contflota['ID'];
                    $ordcf = $row_contflota['ORDEN'];
                    if (($ordcf == 0) || ($ordcf <> $orden)){
                        $ordcf = $orden;
                        $ordencont[$i] = $ordcf;
                        $sql_update = "UPDATE contactos_flotas SET ORDEN = $ordcf WHERE ID = $idcf";
                        $res_update = mysql_query($sql_update) or die ("Error al actualizar el orden del $rol " . $row_contflota['CONTACTO_ID'] . mysql_error($link));
                        $norden++;
                    }
                    $orden++;
                }
                if ($norden == 0){
                    $res_update = true;
                }
                $mensaje = $norden . ' ' . $mensorden;
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
       <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="<?php echo $enlace;?>" method="POST">
            <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
            <input name="idorg" type="hidden" value="<?php echo $idorg;?>">
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
