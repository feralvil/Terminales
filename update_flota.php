<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotaupd_$idioma.php";
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
else {
    if ($flota_usu == $idflota) {
        $permiso = 1;
    }
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
if ($permiso > 0) {
    $repetido = false;
    $imagen = "imagenes/atras.png";
    $texto = $volver;
    //$namehid = "idflota";
    //$valuehid = $idflota;
    if (($permiso == 1)||($permiso == 2)){
        if ($origen == "acceso") {
            $enlaceok = $enlacefail = "detalle_flota.php";
            $titulo = "$titacc $flota_org ($acro_org)";
            $mensaje = $mensacc;
            if ($passwd1 != $passwd2) {
                $sql_update = false;
                $error = $errpwd;
            }
            else {
                $sql_update = "UPDATE FLOTAS SET PASSWORD = '$passwd1' WHERE ID = $idflota";
            }
            $res_update = mysql_query($sql_update) or die (mysql_error($link));
        }
    }

    if ($permiso == 2){
        if ($origen == "editar") {
            $enlaceok = $enlacefail = "detalle_flota.php";
            $titulo = "$titedi $flota_org ($acro_org)";
            $rango = "";
            if (($rangoini != "")&&($rangofin != "")){
                $rango = $rangoini . "-" . $rangofin;
            }
            $sql_update = "UPDATE flotas SET FLOTA='$flota', ACRONIMO='$acronimo', LOGIN='$login', ";
            $sql_update .= "DOMICILIO='$domicilio', ORGANIZACION=$organiza, CP='$cp', INE='$ine', AMBITO='$ambito', " ;
            $sql_update .= "ENCRIPTACION='$encriptacion', ACTIVO='$activa', RANGO = '$rango' WHERE ID=$idflota";
            $mensaje = $mensedi;
            $error = $erredi;
            $res_update = mysql_query($sql_update) or die (mysql_error($link));
        }

        if ($origen == "leerexcel"){
            $enlacefail = $enlaceok = "detalle_flota.php";
            //Gestión de errores
            error_reporting(E_ALL ^ E_NOTICE);
            $fichero = "flotas/$idflota.xls";
            $res_update = unlink($fichero);
            $mensaje = $mensdelfich;
            $error = "$errunlink: $fichero";
        }

        if ($origen == "nueva") {
            $titulo = "$titnew $flota ($acronimo)";
            $error = "$errnew $flota:";
            $enlacefail = "nueva_flota.php";
            $enlaceok = "detalle_flota.php";
            if (($usuario == "") || ($flota == "") || ($acronimo == "")) {
                $enlacefail = "nueva_flota.php";
                $res_update = false;
                if ($usuario == "") {
                    $vacio = $vacuser;
                }
                if ($flota == "") {
                    if ($usuario == "") {
                        $vacio = $vacio . ", FLOTA";
                    }
                    else {
                        $vacio = "FLOTA";
                    }
                }
                if ($acronimo == "") {
                    if (($usuario == "") || ($flota == "")) {
                        $vacio = $vacio . ", $vacacro";
                    }
                    else {
                        $vacio = $vacacro;
                    }
                }
                $error = "$errvac1 $vacio $errvac2";
            }
            else{
                $repetido = false;
                $errarch = $_FILES["archivo"]["error"];
                if ($errarch > 0) {
                    if ($errarch != 4){
                        $repetido = true;
                        $error = $errupload . $errfile[$errarch];
                    }
                }
                else {
                    if ($_FILES["archivo"]["size"] > 5000000) {
                        $repetido = true;
                        $error = $errupload . $errupmax;
                    }
                    // elseif (($_FILES["archivo"]["type"] != "application/vnd.ms-excel")&&($_FILES["archivo"]["type"] != "application/x-msexcel")){
                    elseif(strpos($_FILES["archivo"]["type"], 'excel') == FALSE){
                        $repetido = true;
                        $error = $errupload.$erruptype;
                    }
                }
                if (!$repetido){
                    $sql_flotas = "SELECT * FROM flotas";
                    $res_flotas = mysql_query($sql_flotas) or die("Error en la consulta de Flota: " . mysql_error());
                    $nflotas = mysql_num_rows($res_flotas);
                    $i = 0;
                    while (($i < $nflotas) && (!$repetido)) {
                        $row_flota = mysql_fetch_array($res_flotas);
                        $i++;
                        if ($flota == $row_flota["FLOTA"]) {
                            $repetido = true;
                            $error = "Error: La flota $flota $errflota";
                        }
                        elseif ($acronimo == $row_flota["ACRONIMO"]) {
                            $repetido = true;
                            $error = "Error: $erracro $acronimo $erremp ".$row_flota["FLOTA"];
                        }
                        elseif ($usuario == $row_flota["LOGIN"]) {
                            $repetido = true;
                            $error = "Error: $erruser $usuario $erremp ".$row_flota["FLOTA"];
                        }
                    }
                    if ($repetido){
                        $res_update = false;
                    }
                    else{
                        //$password = md5($password);
                        $mail = $usuario . "@comdes.es";
                        $rango = "";
                        if (($rangoini != "")&&($rangofin != "")){
                            $rango = $rangoini . "-" . $rangofin;
                        }
                        $sql_update = "INSERT INTO flotas (FLOTA, ACRONIMO, INE, DOMICILIO, CP, LOGIN, ORGANIZACION, ";
                        $sql_update .= "PASSWORD, MAIL, ACTIVO,  AMBITO, ENCRIPTACION, RANGO) VALUES ";
                        $sql_update .= "('$flota', '$acronimo', '$ine', '$domicilio', '$cp', '$usuario', '$organiza', ";
                        $sql_update .= "'$password', '$mail', '$activa', '$ambito', '$encriptacion', '$rango')";
                        $mensaje = "Flota $flota_txt $mensnew.";
                        $res_update = mysql_query($sql_update) or die (mysql_error($link));
                        $idflota = mysql_insert_id();
                        $sql_perm = "INSERT INTO usuarios_flotas (NOMBRE, ID_FLOTA) VALUES ('$usuario', '$idflota')";
                        $res_perm = mysql_query($sql_perm) or die (mysql_error($link));
                        $idperm = mysql_insert_id();
                        if ($errarch == 4){
                            $mensaje = "$mensaje. Nota: ".$errfile[4];
                            $enlaceok = "detalle_flota.php";
                        }
                        else {
                            $nombre_fichero = "flotas/$idflota.xls";
                            if (move_uploaded_file($_FILES["archivo"]["tmp_name"], $nombre_fichero)) {
                                $enlaceok = "leercont.php";
                            }
                            else {
                                $res_update = false;
                                $error = $errmoveup;
                                $sql_delflotas = "DELETE * FROM flotas WHERE ID = '$idflota'";
                                mysql_query($sql_delflotas) or die($errdelflo . mysql_error());
                                $sql_delusuflo = "DELETE * FROM usuarios_flotas WHERE ID = '$idperm'";
                                mysql_query($sql_delusuflo) or die($errdeluf . mysql_error());
                            }
                        }
                    }
                }
            }
        }
    }
    if ($origen == "nomflota") {
        $titulo = $titnom;
        $error = $errnom . ":";
        $enlacefail = "nombre_flotas.php";
        $enlaceok = "flotas.php";
        $condicion = "((FLOTA LIKE 'PL%') OR (FLOTA LIKE 'PC%'))";
        $sql_flotas = "SELECT * FROM flotas WHERE " . $condicion . " ORDER BY FLOTA ASC";
        $flotas = mysql_query($sql_flotas) or die("Error en la consulta de Flotas" . mysql_error());
        $nflotas = mysql_num_rows($flotas);
        $sql_total = "";
        $nupdate = 0;
        if ($nflotas > 0){
            $res_update = true;
            for ($i = 0; $i < $nflotas; $i++){
                $flota = mysql_fetch_array($flotas);
                $prefijo = substr($flota['FLOTA'], 0, 2);
                if ($prefijo == "PL"){
                    $nomnou = "POLICÍA LOCAL " . substr($flota['FLOTA'], 3);
                }
                elseif ($prefijo == "PC") {
                    $nomnou = "PROTECCIÓN CIVIL " . substr($flota['FLOTA'], 3);
                }
                else{
                    $nomnou = substr($flota['FLOTA'], 3);
                }
                $nomnou = mysql_escape_string($nomnou);
                $sql_update = "UPDATE flotas SET FLOTA = '" . $nomnou . "' WHERE (ID = " . $flota['ID'] . ")";
                if ($res_update){
                    $res_update = mysql_query($sql_update) or die("Error al actualizar la flota" . " " . $flota['FLOTA'] . ": " . $sql_update . " - " . mysql_error());
                    $nupdate++;
                }
            }
            $mensaje = $nupdate . " " . $mensnom;
        }
        else{
            $res_update = FALSE;
            $error .= $errnoflotas;
        }
    }

    if ($origen == "resetpassw") {
        $titulo = $titresetpw;
        $error = $errresetpw . ":";
        $enlacefail = "password_flotas.php";
        $enlaceok = "password_flotas.php";
        $sql_flotas = "SELECT ID FROM flotas WHERE (ID <> 100)";
        if (($idflota != '') && ($idflota != "00")) {
            $sql_flotas = $sql_flotas . " AND (flotas.ID = $idflota)";
        }
        if (($idorg != '') && ($idorg != "00")) {
            $sql_flotas = $sql_flotas . " AND (flotas.ORGANIZACION = $idorg)";
        }
        if (!(empty ($flotasel))){
            $sql_flotas .= " AND flotas.ID IN (";
            for ($i = 0; $i < count($flotasel); $i++){
                $sql_flotas .= $flotasel[$i];
                if ($i < (count($flotasel) - 1)){
                    $sql_flotas .= ", ";
                }
            }
            $sql_flotas .= ")";
        }
        $flotas = mysql_query($sql_flotas) or die("Error en la consulta de Flotas" . mysql_error());
        $nflotas = mysql_num_rows($flotas);
        $sql_total = "";
        $nupdate = 0;
        $fecha = date('Y-m-d');
        if ($nflotas > 0){
            $res_update = true;
            $txtupdate = "";
            $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $longCaracteres = strlen($caracteres) - 1;
            for ($i = 0; $i < $nflotas; $i++){
                $flota = mysql_fetch_array($flotas);
                $newpass = '';
                for ($j = 0; $j < 8; $j++) {
                    $newpass .= $caracteres[rand(0, $longCaracteres)];
                }
                $sql_update = 'UPDATE flotas SET PASSWORD = "' . $newpass . '", PASSRESET = "PDTE", PASSUPDATE = "' . $fecha . '" WHERE (ID = ' . $flota['ID'] . ')';
                if ($res_update){
                    $res_update = mysql_query($sql_update) or die("Error al actualizar la flota" . " " . $flota['FLOTA'] . ": " . $sql_update . " - " . mysql_error());
                    $nupdate++;
                }
                $txtupdate .= $sql_update . "<br />";
            }
            $mensaje = sprintf($mensresetpw, $nupdate);
        }
        else{
            $error .= $errnoflotas;
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
