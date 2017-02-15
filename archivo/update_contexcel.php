<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contexc_$idioma.php";
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
       // Aumentamos el tamaño de la memoria:
        ini_set('memory_limit', '64M');
        $sql_flota = "SELECT * from FLOTAS WHERE ID = '$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
        $res_update = true;
        if ($nflota == 0){
            $res_update = false;
            $error = $error_noflota;
        }
        else{
            $row_flota = mysql_fetch_array($res_flota);
            $flota = $row_flota["FLOTA"];
            $domicilio = $row_flota["DOMICILIO"];
            // Clases para generar el Excel
            /** Error reporting */
            error_reporting(E_ALL);
            date_default_timezone_set('Europe/Madrid');
            /** PHPExcel */
            require_once 'Classes/PHPExcel.php';
            $fichero = "flotas/$idflota.xls";

            // Creamos el tipo de fichero
            $tipoFich = PHPExcel_IOFactory::identify($fichero);
            $objReader = PHPExcel_IOFactory::createReader($tipoFich);
            // Sólo nos interesa cargar los datos:
            $objReader->setReadDataOnly(true);
            try {
                $objPHPExcel = $objReader->load($fichero);
            }
            catch(Exception $e){
                die("Error al cargar el fichero de datos: ".$e->getMessage());
            }
            // Fijamos como hoja activa la primera
            $objPHPExcel->setActiveSheetIndex(0);
            // Obtenemos el número de filas de la hoja:
            $numfilas = $objPHPExcel->getActiveSheet()->getHighestRow();

            // Responsable de Organización:
            $fila = $filaresporg;
            $resporg = array();
            $resporg ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
            $resporg ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("J$fila")->getValue();
            $fila++;
            $resporg ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
            $fila++;
            if ($row_flota['ORGANIZACION'] == 0){
                $organizacion = array();
                $organizacion['ORGANIZACION'] = $objPHPExcel->getActiveSheet()->getCell("C6")->getValue();
                $organizacion['INE'] = 0;
                $organizacion['DOMICILIO'] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
                $fila++;
                $celdadom = explode('-',$objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue());
                $organizacion['CP'] = trim($celdadom[0]);
                $nomorganiza = mysql_real_escape_string($organizacion['ORGANIZACION']);
                $sql_orgcheck = "SELECT * FROM organizaciones WHERE ORGANIZACION = '" . $nomorganiza . "'";
                $res_organiza = mysql_query($sql_orgcheck) or die("Error en la consulta del Nombre de Organización: " . mysql_error());
                $norganiza = mysql_num_rows($res_organiza);
                if ($norganiza > 0) {
                    $organiza = mysql_fetch_array($res_organiza);
                    $idorg = $organiza['ID'];
                    $sql_updorg = "UPDATE organizaciones SET ORGANIZACION = '" . $nomorganiza . "',";
                    $sql_updorg .= " DOMICILIO = '" . $organizacion['DOMICILIO'] . "', CP = '" . $organizacion['CP'] . "'";
                    $sql_updorg .= " WHERE ID = " . $idorg;
                    $res_organiza = mysql_query($sql_updorg) or die("Error al actualizar la Organización: " . mysql_error());
                }
                else{
                    $sql_insorg = "INSERT INTO organizaciones (ORGANIZACION, DOMICILIO, CP) VALUES('" . $nomorganiza . "', ";
                    $sql_insorg .= "'" . $organizacion['DOMICILIO'] . "', '" . $organizacion['CP'] . "')";
                    $res_organiza = mysql_query($sql_insorg) or die("Error al insertar la Organización: " . mysql_error());
                    $idorg = mysql_insert_id($link);
                }
            }
            else{
                $idorg = $row_flota['ORGANIZACION'];
                $sql_selorg = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
                $res_organiza = mysql_query($sql_selorg) or die("Error en la consulta de Organización: " . mysql_error());
                $norganiza = mysql_num_rows($res_organiza);
                if ($norganiza > 0) {
                    $organizacion = mysql_fetch_array($res_organiza);
                }
                $fila++;
            }
            $sql_updflota = "UPDATE flotas SET ORGANIZACION = " . $idorg . " WHERE ID = " . $idflota;
            $res_updflota = mysql_query($sql_updflota) or die("Error al actualizar la Organización de la Flota: " . mysql_error());
            $fila++;
            $resporg ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
            $resporg ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
            $nomresporg = mysql_real_escape_string($resporg['NOMBRE']);
            $sql_resporgcheck = "SELECT * FROM contactos WHERE NOMBRE = '" . $nomresporg . "'";
            $res_resporgcheck = mysql_query($sql_resporgcheck) or die("Error en la consulta de Responsable de Organización: " . mysql_error());
            $nresporgcheck = mysql_num_rows($res_resporgcheck);
            if ($nresporgcheck > 0){
                $resporgcheck = mysql_fetch_array($res_resporgcheck);
                $idresporg = $resporgcheck['ID'];
                $sql_updresporg = "UPDATE contactos SET NOMBRE = '" . $nomresporg . "', NIF = '" . $resporg['NIF'] . "', ";
                $sql_updresporg .= "CARGO = '" . $resporg['CARGO'] . "', TELEFONO = '" . $resporg['TELEFONO'] . "', ";
                $sql_updresporg .= "MAIL = '" . $resporg['MAIL'] . "' WHERE ID = " . $idresporg;
                $res_updresporg = mysql_query($sql_updresporg) or die("Error al actualizar el Responsable de Organización: " . mysql_error());
            }
            else {
                $sql_insresporg = "INSERT INTO contactos (NOMBRE, NIF, CARGO, TELEFONO, MAIL) VALUES";
                $sql_insresporg .= " ('" . $nomresporg . "', '" . $resporg['NIF'] . "', '" . $resporg['CARGO'] . "', '";
                $sql_insresporg .= $resporg['TELEFONO'] . "', '" . $resporg['MAIL'] . "')";
                $res_insresporg = mysql_query($sql_insresporg) or die("Error al insertar el Responsable de Organización: " . mysql_error());
                $idresporg = mysql_insert_id($link);
            }
            $sql_updorg = "UPDATE organizaciones SET RESPONSABLE = " . $idresporg . " WHERE ID = " . $idorg;
            $res_updorg = mysql_query($sql_updorg) or die("Error al actualizar la Organización: " . mysql_error());

            // Responsable de la Flota
            $fila = $filarespflo;
            $respflo = array();
            $respflo ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
            $respflo ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("J$fila")->getValue();
            $fila++;
            $respflo ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
            $fila++;
            $fila++;
            $fila++;
            $respflo ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
            $respflo ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
            $fila++;
            // Comprobamos si el contacto existe:
            $nomrespflo = mysql_real_escape_string($respflo['NOMBRE']);
            $sql_respflocheck = "SELECT * FROM contactos WHERE NOMBRE = '" . $nomrespflo . "'";
            $res_respflocheck = mysql_query($sql_respflocheck) or die("Error en la consulta del Nombre de Organización: " . mysql_error());
            $nrespflocheck = mysql_num_rows($res_respflocheck);
            if ($nrespflocheck > 0){
                $respflocheck = mysql_fetch_array($res_respflocheck);
                $idrespflo = $respflocheck['ID'];
                $sql_updrespflo = "UPDATE contactos SET NOMBRE = '" . $nomrespflo . "', NIF = '" . $respflo['NIF'] . "', ";
                $sql_updrespflo .= "CARGO = '" . $respflo['CARGO'] . "', TELEFONO = '" . $respflo['TELEFONO'] . "', ";
                $sql_updrespflo .= "MAIL = '" . $respflo['MAIL'] . "' WHERE ID = " . $idrespflo;
                $res_updrespflo = mysql_query($sql_updrespflo) or die("Error al actualizar el Responsable de la Flota: " . mysql_error());
            }
            else{
                $sql_insrespflo = "INSERT INTO contactos (NOMBRE, NIF, CARGO, TELEFONO, MAIL) VALUES";
                $sql_insrespflo .= " ('" . $nomrespflo . "', '" . $respflo['NIF'] . "', '" . $respflo['CARGO'] . "', '";
                $sql_insrespflo .= $respflo['TELEFONO'] . "', '" . $respflo['MAIL'] . "')";
                $res_insrespflo = mysql_query($sql_insrespflo) or die("Error al insertar el Responsable de la Flota: " . mysql_error());
                $idrespflo = mysql_insert_id($link);
            }
            $sql_cfcheck = "SELECT * FROM contactos_flotas WHERE (ROL = 'RESPONSABLE') AND (FLOTA_ID = ". $idflota . ")";
            $res_cfcheck = mysql_query($sql_cfcheck) or die("Error en la consulta del Resposnable de Flota: " . mysql_error());
            $ncfcheck = mysql_num_rows($res_cfcheck);
            if ($ncfcheck > 0){
                $cfcheck = mysql_fetch_array($res_cfcheck);
                $idcf = $cfcheck['ID'];
                $sql_updcfresp = "UPDATE contactos_flotas SET CONTACTO_ID = " . $idrespflo . " WHERE ID = " . $idcf;
                $res_updcfresp = mysql_query($sql_updcfresp) or die("Error al actualizar el Responsable de la Flota: " . mysql_error());
            }
            else{
                $sql_inscfresp = "INSERT INTO contactos_flotas (CONTACTO_ID, FLOTA_ID, ROL) VALUES";
                $sql_inscfresp .= " (" . $idrespflo . ", " . $idflota . ", 'RESPONSABLE')";
                $res_inscfresp = mysql_query($sql_inscfresp) or die("Error al agregar el Responsable de la Flota: " . mysql_error());
            }

            // Contactos Operativos:
            $sql_delcfop = "DELETE FROM contactos_flotas WHERE (FLOTA_ID = " . $idflota . ") AND (ROL = 'OPERATIVO')";
            $res_delcfop = mysql_query($sql_delcfop) or die("Error al borrar Contactos Operativos: " . mysql_error());
            if ($filaop > 0){
                $fila = $filaop;
                $contop = array();
                while ($objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue() != "") {
                    $contacto = array();
                    $contacto ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue();
                    $contacto ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("D$fila")->getValue();
                    $contacto ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("F$fila")->getValue();
                    $contacto ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
                    $contacto ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("L$fila")->getValue();
                    array_push($contop, $contacto);
                    $fila++;
                }
                foreach ($contop as $contacto) {
                    $sql_opcheck = "SELECT * FROM contactos WHERE NOMBRE = '" . $contacto["NOMBRE"] . "'";
                    $res_opcheck = mysql_query($sql_opcheck) or die("Error al consultar el contacto operativo " . $contacto["NOMBRE"] . ": " . mysql_error());
                    $nopcheck = mysql_num_rows($res_opcheck);
                    if ($nopcheck > 0){
                        $opcheck = mysql_fetch_array($res_opcheck);
                        $idop = $opcheck['ID'];
                        $sql_updcop = "UPDATE contactos SET NOMBRE = '" . $contacto["NOMBRE"] . "', NIF = '" . $contacto['NIF'] . "', ";
                        $sql_updcop .= "CARGO = '" . $contacto['CARGO'] . "', TELEFONO = '" . $contacto['TELEFONO'] . "', ";
                        $sql_updcop .= "MAIL = '" . $contacto['MAIL'] . "' WHERE ID = " . $idop;
                        $res_updcop = mysql_query($sql_updcop) or die("Error al actualizar el Contacto Operativo: " . mysql_error());
                    }
                    else{
                        $sql_insop = "INSERT INTO contactos (NOMBRE, NIF, CARGO, TELEFONO, MAIL) VALUES";
                        $sql_insop .= " ('" . $contacto["NOMBRE"] . "', '" . $contacto['NIF'] . "', '" . $contacto['CARGO'] . "', '";
                        $sql_insop .= $contacto['TELEFONO'] . "', '" . $contacto['MAIL'] . "')";
                        $res_insop = mysql_query($sql_insop) or die("Error al insertar el Contacto Operativo: " . mysql_error());
                        $idop = mysql_insert_id($link);
                    }
                    $sql_inscfop = "INSERT INTO contactos_flotas (CONTACTO_ID, FLOTA_ID, ROL) VALUES";
                    $sql_inscfop .= " (" . $idop . ", " . $idflota . ", 'OPERATIVO')";
                    $res_inscfop = mysql_query($sql_inscfop) or die("Error al agregar el Contacto Operativo: " . mysql_error());
                }
            }

            // Contactos Técnicos:
            $sql_delcftec = "DELETE FROM contactos_flotas WHERE (FLOTA_ID = " . $idflota . ") AND (ROL = 'TECNICO')";
            $res_delcftec = mysql_query($sql_delcftec) or die("Error al borrar Contactos Técnicos: " . mysql_error());
            if ($filatec > 0){
                $fila = $filatec;
                $contec = array();
                while ($objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue() != "") {
                    $contacto = array();
                    $contacto ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue();
                    $contacto ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("D$fila")->getValue();
                    $contacto ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("F$fila")->getValue();
                    $contacto ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
                    $contacto ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("L$fila")->getValue();
                    array_push($contec, $contacto);
                    $fila++;
                }
                foreach ($contec as $contacto) {
                    $sql_teccheck = "SELECT * FROM contactos WHERE NOMBRE = '" . $contacto["NOMBRE"] . "'";
                    $res_teccheck = mysql_query($sql_teccheck) or die("Error al consultar el contacto Técnico " . $contacto["NOMBRE"] . ": " . mysql_error());
                    $nteccheck = mysql_num_rows($res_teccheck);
                    if ($nteccheck > 0){
                        $teccheck = mysql_fetch_array($res_teccheck);
                        $idtec = $teccheck['ID'];
                        $sql_updtec = "UPDATE contactos SET NOMBRE = '" . $contacto["NOMBRE"] . "', NIF = '" . $contacto['NIF'] . "', ";
                        $sql_updtec .= "CARGO = '" . $contacto['CARGO'] . "', TELEFONO = '" . $contacto['TELEFONO'] . "', ";
                        $sql_updtec .= "MAIL = '" . $contacto['MAIL'] . "' WHERE ID = " . $idtec;
                        $res_updtec = mysql_query($sql_updtec) or die("Error al actualizar el Contacto Técnico: " . mysql_error());
                    }
                    else{
                        $sql_instec = "INSERT INTO contactos (NOMBRE, NIF, CARGO, TELEFONO, MAIL) VALUES";
                        $sql_instec .= " ('" . $contacto["NOMBRE"] . "', '" . $contacto['NIF'] . "', '" . $contacto['CARGO'] . "', '";
                        $sql_instec .= $contacto['TELEFONO'] . "', '" . $contacto['MAIL'] . "')";
                        $res_instec = mysql_query($sql_instec) or die("Error al insertar el Contacto Técnico: " . mysql_error());
                        $idtec = mysql_insert_id($link);
                    }
                    $sql_inscftec = "INSERT INTO contactos_flotas (CONTACTO_ID, FLOTA_ID, ROL) VALUES";
                    $sql_inscftec .= " (" . $idtec . ", " . $idflota . ", 'TECNICO')";
                    $res_inscftec = mysql_query($sql_inscftec) or die("Error al agregar el Contacto Técnico: " . mysql_error());
                }
            }

            // Contactos 24x7:
            $sql_delcf24h = "DELETE FROM contactos_flotas WHERE (FLOTA_ID = " . $idflota . ") AND (ROL = 'CONT24H')";
            $res_delcf24h = mysql_query($sql_delcf24h) or die("Error al borrar Contactos 24x7: " . mysql_error());
            if ($fila24h > 0){
                $fila = $fila24h;
                $cont24h = array();
                while ($objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue() != "") {
                    $contacto = array();
                    $contacto ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue();
                    $contacto ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("F$fila")->getValue();
                    $contacto ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("K$fila")->getValue();
                    array_push($cont24h, $contacto);
                    $fila++;
                }
                foreach ($cont24h as $contacto) {
                    $sql_24hcheck = "SELECT * FROM contactos WHERE NOMBRE = '" . $contacto["NOMBRE"] . "'";
                    $res_24hcheck = mysql_query($sql_24hcheck) or die("Error al consultar el contacto 24x7 " . $contacto["NOMBRE"] . ": " . mysql_error());
                    $n24hcheck = mysql_num_rows($res_24hcheck);
                    if ($n24hcheck > 0){
                        $h24check = mysql_fetch_array($res_24hcheck);
                        $id24h = $h24check['ID'];
                        $sql_upd24h = "UPDATE contactos SET NOMBRE = '" . $contacto["NOMBRE"] . "', TELEFONO = '" . $contacto['TELEFONO'] . "', ";
                        $sql_upd24h .= "MAIL = '" . $contacto['MAIL'] . "' WHERE ID = " . $id24h;
                        $res_upd24h = mysql_query($sql_upd24h) or die("Error al actualizar el Contacto 24x7: " . mysql_error());
                    }
                    else{
                        $sql_ins24h = "INSERT INTO contactos (NOMBRE, TELEFONO, MAIL) VALUES";
                        $sql_ins24h .= " ('" . $contacto["NOMBRE"] . "', '" . $contacto['TELEFONO'] . "', '" . $contacto['MAIL'] . "')";
                        $res_ins24h = mysql_query($sql_ins24h) or die("Error al insertar el Contacto 24x7: " . mysql_error());
                        $id24h = mysql_insert_id($link);
                    }
                    $sql_inscf24h = "INSERT INTO contactos_flotas (CONTACTO_ID, FLOTA_ID, ROL) VALUES";
                    $sql_inscf24h .= " (" . $id24h . ", " . $idflota . ", 'CONT24H')";
                    $res_inscf24h = mysql_query($sql_inscf24h) or die("Error al agregar el Contacto 24x7: " . mysql_error());
                }
            }
        }

        if ($res_update){
            $ncadd = $ncupd = $ncdel = 0;
            $enlace = "leerterm.php";
            $mensflash = $mensaje;
            $update = "OK";
        }
        else{
            $enlace = "update_flota.php";
            $mensflash = $error;
            $update = "KO";
        }
?>
        <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="<?php echo $enlace;?>" method="POST">
            <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
            <input name="origen" type="hidden" value="leercont">
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
