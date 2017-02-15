<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termupd_$idioma.php";
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
    $enlace = "detalle_terminal.php";
    $valuehid = $idterm;
    $namehid = "idterm";
    if ($origen == "editar") {
        $enlaceok = "detalle_terminal.php";
        $enlacefail = "editar_terminal.php";
        $fval = "$anyo-$mes-$dia";
        $dirip = "";
        if (($ipa !="")&&($ipb !="")&&($ipc !="")&&($ipc !="")){
            $dirip = "$ipa.$ipb.$ipc.$ipd";
        }
        if (strlen($tei) < 15){
            $numceros = 15 - strlen($tei);
            for ($i = 0; $i < $numceros; $i++){
                $tei = '0' . $tei;
            }
        }
        if (strlen($tei) > 15){
            $inicio = strlen($tei) - 15;
            $tei = substr($tei, $inicio, 15);
        }
        $sql_update = "UPDATE terminales SET ISSI='$issi', TEI='$tei', NUMEROK='$numk', TIPO='$tipo', CARPETA='$carpeta', CODIGOHW='$codigohw', ";
        $sql_update = $sql_update . "NSERIE='$nserie', MNEMONICO='$mnemonico', OBSERVACIONES='$observaciones', MARCA='$marca', MODELO='$modelo', ";
        $sql_update = $sql_update . "DIRIP = '$dirip', PROVEEDOR='$proveedor', AM='$am', AUTENTICADO = '$autenticado', ENCRIPTADO = '$encriptado', ";
        $sql_update = $sql_update . "$fecha = '$fval', DOTS='$dots', SEMID='$semid', DUPLEX='$duplex' WHERE ID=$idterm";
        $titulo = $titedi . $idterm;
        $mensaje = $mensedi;
        $error = $erredi . $idterm . ":";
        $res_update = mysql_query($sql_update) or die (mysql_error($link));
    }
    if ($origen == "eliminar") {
        $fecha = date("Y-m-d");
        $sql_flota = "SELECT * FROM terminales WHERE ID = '$idterm'";
        $res_flota = mysql_query($sql_flota) or die(mysql_error());
        $nflota = mysql_numrows($res_flota);
        if ($nflota > 0) {
            $row_flota = mysql_fetch_array($res_flota);
            $idflota = $row_flota["FLOTA"];
        }
        $enlaceok = "terminales.php";
        $enlacefail = "detalle_terminal.php";
        $sql_update = "DELETE FROM terminales WHERE ID = '$idterm'";
        $titulo = $titbaja . $idterm;
        $mensaje = "Terminal ID = $idterm " . $mensbaja;
        $error = $errbaja . $idterm . ":";
        $res_update = mysql_query($sql_update) or die (mysql_error($link));
        if ($res_update){
            $namehid = "flota";
            $valuehid = $idflota;
        }
    }
    if ($origen == "baja") {
        $fecha = date("Y-m-d");
        $enlaceok = "detalle_terminal.php";
        $enlacefail = "detalle_terminal.php";
        $sql_update = "UPDATE terminales SET ESTADO='B', FBAJA='$fecha' WHERE ID=$idterm";
        $titulo = $titbaja . $idterm;
        $mensaje = "Terminal ID = $idterm " . $mensbaja;
        $error = $errbaja . $idterm . ":";
        $res_update = mysql_query($sql_update) or die (mysql_error($link));
        if ($res_update){
            $namehid = "idterm";
            $valuehid = $idterm;
        }
    }
    if ($origen == "alta") {
        $fecha = date("Y-m-d");
        $enlaceok = "detalle_terminal.php";
        $enlacefail = "detalle_terminal.php";
        $sql_update = "UPDATE terminales SET ESTADO='A', FALTA='$fecha' WHERE ID=$idterm";
        $titulo = $titalta . $idterm;
        $mensaje = "Terminal ID = $idterm ".$mensalta;
        $error = $erralta . $idterm . ":";
        $res_update = mysql_query($sql_update) or die (mysql_error($link));
    }
    if ($origen == "nuevo") {
        $enlaceok = "detalle_terminal.php";
        $enlacefail = "terminales.php";
        $fecha = date("Y-m-d");
        $dirip = "";
        if (($ipa !="")&&($ipb !="")&&($ipc !="")&&($ipc !="")){
            $dirip = "$ipa.$ipb.$ipc.$ipd";
        }
        $error = $errnew;
        switch ($estado) {
            case ("A"): {
                $falta = $fecha;
                $fbaja = '0000-00-00';
                break;
            }
            case ("B"): {
                $fbaja = $fecha;
                $falta = '0000-00-00';
                break;
            }
        }
        $repetido = false;
        $sql_terminales = "SELECT * FROM terminales WHERE TEI = '$tei'"; // AND ESTADO = 'A'";
        $res_terminales = mysql_query($sql_terminales) or die(mysql_error());
        $nterminales = mysql_numrows($res_terminales);
        if ($nterminales > 0) {
            $repetido = true;
            $terminal = mysql_fetch_array($res_terminales);
            $idr = $terminal["ID"];
            $issir = $terminal["ISSI"];
            $flotar = $terminal["FLOTA"];
            $sql_flotar = "SELECT * FROM flotas WHERE ID='$flotar'";
            $res_flotar = mysql_query($sql_flotar) or die(mysql_error());
            $nflotar = mysql_numrows($res_flotar);
            if ($nflotar > 0) {
                $row_flota = mysql_fetch_array($res_flotar);
                $flotar_nom = $row_flota["FLOTA"];
            }
            $error = $errnew . "<br />$repet1 $tei $repet2:";
            $error = $error . "<br />  &mdash; Flota: $flotar_nom";
            $error = $error . "<br />  &mdash; Terminal ID: $idr";
            $error = $error . "<br />  &mdash; Terminal ISSI: $issir";
        }
        if ($repetido) {
            $res_update = false;
            $enlacefail = "detalle_terminal.php";
            $valuehid = $idr;
            $namehid = "idterm";
        }
        else {
            if (strlen($tei) < 15){
                $numceros = 15 - strlen($tei);
                for ($i = 0; $i < $numceros; $i++){
                    $tei = '0' . $tei;
                }
            }
            if (strlen($tei) > 15){
                $inicio = strlen($tei) - 15;
                $tei = substr($tei, $inicio, 15);
            }
            $sql_update = "INSERT INTO terminales (ISSI, TEI, CODIGOHW, NSERIE, NUMEROK, TIPO, MARCA, MODELO, DIRIP, ";
            $sql_update = $sql_update . "PROVEEDOR, FLOTA, MNEMONICO, DOTS, AM, AUTENTICADO, ENCRIPTADO, OBSERVACIONES, ";
            $sql_update = $sql_update . "CARPETA, DUPLEX, SEMID, ESTADO, FALTA, FBAJA) VALUES ";
            $sql_update = $sql_update . "('$issi', '$tei', '$codigohw', '$nserie', '$numerok', '$tipo', '$marca', '$modelo', '$dirip', ";
            $sql_update = $sql_update . "'$proveedor', '$flota', '$mnemonico', '$dots', '$am', '$autenticado', '$encriptado', ";
            $sql_update = $sql_update . "'$observaciones', '$carpeta', '$duplex', '$semid', '$estado', '$falta', '$fbaja') ";
            $titulo = $titnew;
            $mensaje = $mensnew;
            if ($nterminales > 0){
                $mensaje = $mensaje . "<br />".$repet1.$issi;
            }
            $res_update = mysql_query($sql_update) or die (mysql_error($link));
            $idterm = mysql_insert_id();
            if ($res_update){
                $valuehid = $idterm;
                $namehid = "idterm";
            }
            else{
                $valuehid = $flota;
                $namehid = "flota";
            }
        }
    }
    if ($origen == "dots") {
        if ($action == "add") {
            $dots = "SI";
            $titulo = $titadd;
            $error = $erradd;
            $mensaje = "Terminal ID = $idterm " . $mensadd;
        }
        else {
            $dots = "NO";
            $titulo = $titdel;
            $error = $errdel;
            $mensaje = "Terminal ID = $idterm " . $mensdel;
        }
        $sql_update = "UPDATE terminales SET DOTS = '$dots' WHERE ID = $idterm";
        $res_update = mysql_query($sql_update) or die (mysql_error($link));
    }

    if ($origen == "autflota") {
        $valuehid = $idflota;
        $namehid = "idflota";
        $enlacefail = $enlaceok = "detalle_flota.php";
        if ($idflota != "") {
            $sql_update = "UPDATE terminales SET AUTENTICADO = '$autenticacion' WHERE FLOTA = $idflota";
            $res_update = mysql_query($sql_update) or die (mysql_error($link));
            $mensaje = $mensaut;
        }
        else {
            $res_update = false;
            $error = $erraut;
        }
    }
    if ($origen == "modelo") {
        $valuehid = $modelonew;
        $namehid = "modelo";
        $enlacefail = $enlaceok = "modelo_terminal.php";
        $sql_update = "UPDATE terminales SET MODELO = '$modelonew' WHERE MODELO = '$modeloact'";
        $res_update = mysql_query($sql_update) or die (mysql_error($link));
        $nupdate = mysql_affected_rows($link);
        $mensaje = sprintf($mensmodelo, $nupdate);
        $error = $errmodelo;
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
