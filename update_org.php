<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organizaupd_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
}
else{
    // Codificación de carácteres de la conexión a la BBDD
    mysql_set_charset('utf8',$link);
}
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //

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
<!DOCTYPE html>
<html lang="es">
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu = 0){
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
        if ($permiso == 2){
            $res_update = false;
            $res_update = true;
            if ($origen == "impflotas"){
                $enlaceok = "flotas.php";
                $enlacefail = "flotas.php";
                $error = $errimpflotas;
                $mensaje = $mensimpflotas;
                $sql_flotas = "SELECT ID, FLOTA FROM flotas ORDER BY ID ASC";
                $res_flotas = mysql_query($sql_flotas) or die("Error en la Consulta de Flotas: " . mysql_error());
                $nflotas = mysql_num_rows($res_flotas);
                if ($nflotas > 0){
                    $nfupd = 0;
                    $sql_update = "";
                    for ($i = 0; $i < $nflotas ; $i++){
                        if ($res_update){
                            $flota = mysql_fetch_array($res_flotas);
                            $sql_org = "SELECT ID, ORGANIZACION FROM organizaciones WHERE (FLOTA_ID = '" . $flota['ID'] . "')";
                            $res_org = mysql_query($sql_org) or die("Error en la Consulta de Organizaciones: " . mysql_error());
                            $norg = mysql_num_rows($res_org);
                            if ($norg > 0){
                                $org = mysql_fetch_array($res_org);
                                $sql_update = "UPDATE flotas SET ORGANIZACION = ". $org['ID'];
                                $sql_update .= " WHERE (ID = ". $flota['ID'] .")";
                                $nfupd++;
                                $res_update = mysql_query($sql_update) or die("Error al actualizar las flotas: " . mysql_error() . " - " . $sql_update);
                            }
                        }
                    }
                }
                else{
                    $res_update = false;
                    $error .= " " . $errnoflotas;
                }
            }
            if ($origen == "editar"){
                $enlaceok = "detalle_organizacion.php";
                $enlacefail = "editar_organiza.php";
                $mensaje = $mensedi;
                $error = $erredi;
                if ($organiza == ""){
                    $res_update = false;
                    $error .= ": " . $errorgvac;
                }
                else{
                    $sql_check = "SELECT * FROM organizaciones WHERE (ORGANIZACION = '" . $organiza . "') AND (ID <> " . $idorg . ")";
                    $res_check = mysql_query($sql_check) or die("Error en la Consulta Previa de Organizaciones: " . mysql_error());
                    $ncheck = mysql_num_rows($res_check);
                    if ($ncheck == 0){
                        $sql_check = "SELECT * FROM organizaciones WHERE (ID = " . $idorg . ")";
                        $res_check = mysql_query($sql_check) or die("Error en la 2ª Consulta Previa de Organizaciones: " . mysql_error());
                        $ncheck = mysql_num_rows($res_check);
                        if ($ncheck > 1){
                            $res_update = false;
                            $error .= ": " . $errnoorg;
                        }
                        else{
                            $sql_update = "UPDATE organizaciones SET ORGANIZACION = '" . $organiza . "', DOMICILIO = '" . $domicilio . "', ";
                            $sql_update .= "CP = '" . $cp . "', INE = '" . $ine . "' WHERE ID = " . $idorg;
                            $res_update = mysql_query($sql_update) or die("Error en la actualización de Organizaciones: " . mysql_error());
                        }
                    }
                    else{
                        $res_update = false;
                        $error .= ": " . $errorgrep;
                    }
                }
            }

            if ($origen == "agregar"){
                $enlaceok = "detalle_organizacion.php";
                $enlacefail = "nueva_organiza.php";
                $error = $errorgnew;
                $mensaje = $mensorgnew;
                if ($organiza == ""){
                    $res_update = false;
                    $error .= ": " . $errorgvac;
                }
                else{
                    $organiza = mysql_escape_string($organiza);
                    $sql_checkorg = "SELECT * FROM organizaciones WHERE ORGANIZACION = '" . $organiza . "'";
                    $res_checkorg = mysql_query($sql_checkorg) or die("Error en la Consulta Previa de Organizaciones: " . mysql_error());
                    $ncheckorg = mysql_num_rows($res_checkorg);
                    if ($ncheckorg > 0){
                        $res_update = false;
                        $error .= ": " . $errorgrep;
                    }
                    else{
                        $idresp = 0;
                        if ($idcontacto > 0){
                            $idresp = $idcontacto;
                        }
                        else{
                            if ($nombre != ""){
                                $sql_checkcont = "SELECT * FROM contactos WHERE NOMBRE = '" . $nombre . "'";
                                $res_checkcont = mysql_query($sql_checkcont) or die("Error en la Consulta Previa de Contacto: " . mysql_error());
                                $ncheckcont = mysql_num_rows($res_checkcont);
                                if ($ncheckcont > 0){
                                    $contacto = mysql_fetch_array($res_checkcont);
                                    $idresp = $contacto['ID'];
                                }
                                else{
                                    $sql_inscont = "INSERT INTO contactos (NOMBRE, CARGO, TELEFONO, MAIL) VALUES ";
                                    $sql_inscont .= "('" . $nombre . "', '" . $cargo  . "', '" . $telefono  . "', '" . $mail . "')";
                                    $res_inscont = mysql_query($sql_inscont) or die("Error al insetar el nuevo Contacto: " . mysql_error());
                                    $idresp = mysql_insert_id();
                                }
                            }
                        }
                        $sql_update = "INSERT INTO organizaciones (ORGANIZACION, ACRONIMO, INE, DOMICILIO, CP, RESPONSABLE) VALUES";
                        $sql_update .= "('" . $organiza . "', '" . $acronimo . "', '" . $ine . "', '" . $domicilio . "', '" . $cp . "', " .$idresp . ")";
                        $res_update = mysql_query($sql_update) or die("Error al insetar la nueva Organización: " . mysql_error());
                        $idorg = mysql_insert_id();
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
            <h1><?php echo $h1; ?></h1>
            <form name="formupd" action="<?php echo $enlace;?>" method="POST">
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
        else{
        ?>
            <h1><?php echo $h1perm; ?></h1>
            <p class='error'><?php echo $errnoperm; ?></p>
        <?php
        }
        ?>
    </body>
</html>
