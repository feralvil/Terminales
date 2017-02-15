<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/permisosupd_$idioma.php";
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
            if ($idflota > 0){
                $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
                $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
                $nflota = mysql_num_rows($res_flota);
                if ($nflota > 0){
                    if ($origen == "permadd"){
                        $enlaceok = "permisos_flota.php";
                        $enlacefail = "permisos_floadd.php";
                        $namehid = "idflota";
                        $valuehid = $idflota;
                        $error = $erraddperm;
                        $mensaje = $mensaddperm;
                        if ($gissi > 0){
                            $sql_gssi = "SELECT * FROM grupos WHERE GISSI = " . $gissi;
                            $res_gssi = mysql_query($sql_gssi) or die("Error en la consulta de GSSI: " . $sql_gssi . " - " . mysql_error());
                            $ngssi = mysql_num_rows($res_gssi);
                            if ($ngssi > 0){
                                $ncarp = 0;
                                if ((!empty($carpterm)) || ($newcarp != "")){
                                    $sql_update = "INSERT INTO permisos_flotas (FLOTA, GISSI, CARPTERM, NOMBRE) VALUES";
                                    if (!empty($carpterm)){
                                        foreach ($carpterm as $carpeta) {
                                            $sql_check = "SELECT * FROM permisos_flotas WHERE (GISSI = " . $gissi . ")";
                                            $sql_check .= " AND (FLOTA = " . $idflota . ") AND (CARPTERM = '" . $carpeta . "')";
                                            $res_check = mysql_query($sql_check) or die("Error en la consulta de Carpeta: " . mysql_error());
                                            $ncheck = mysql_num_rows($res_check);
                                            if ($ncheck == 0){
                                                $ncarp++;
                                                $sql_update .= "(" . $idflota . ", " . $gissi . ", '" . $carpeta . "', '-'), ";
                                            }
                                        }
                                    }
                                    if ($newcarp != ""){
                                        $sql_check = "SELECT * FROM permisos_flotas WHERE (GISSI = " . $gissi . ")";
                                        $sql_check .= " AND (FLOTA = " . $idflota . ") AND (CARPTERM = '" . $newcarp . "')";
                                        $res_check = mysql_query($sql_check) or die("Error en la consulta de Carpeta: " . mysql_error());
                                        $ncheck = mysql_num_rows($res_check);
                                        if ($ncheck == 0){
                                            $ncarp++;
                                            $sql_update .= "(" . $idflota . ", " . $gissi . ", '" . $newcarp . "', '-'), ";
                                        }
                                    }
                                    if ($ncarp > 0){
                                        $sql_update = substr($sql_update, 0, -2);
                                        $res_update = mysql_query($sql_update) or die ($error . " " . $sql_update . " " . mysql_error($link));
                                    }
                                }
                                else{
                                    $error .= '. ' . $errnocarp;
                                }
                            }
                            else {
                                $error .= '. ' . $errgssicheck;
                            }
                        }
                        else{
                            $error .= '. ' . $errnogssi;
                        }

                    }
                    if ($origen == "permdel"){
                        $enlaceok = "permisos_flota.php";
                        $enlacefail = "permisos_flodel.php";
                        $namehid = "idflota";
                        $valuehid = $idflota;
                        $error = $errdelperm;
                        $mensaje = $mensdelperm;
                        if ($gissi > 0){
                            $sql_gssi = "SELECT * FROM grupos WHERE GISSI = " . $gissi;
                            $res_gssi = mysql_query($sql_gssi) or die("Error en la consulta de GSSI: " . $sql_gssi . " - " . mysql_error());
                            $ngssi = mysql_num_rows($res_gssi);
                            if ($ngssi > 0){
                                $ncarp = 0;
                                if (!empty($carpterm)){
                                    $sql_update = "DELETE FROM permisos_flotas WHERE ID IN";
                                    foreach ($carpterm as $carpeta) {
                                        $sql_check = "SELECT * FROM permisos_flotas WHERE (GISSI = " . $gissi . ")";
                                        $sql_check .= " AND (FLOTA = " . $idflota . ") AND (CARPTERM = '" . $carpeta . "')";
                                        $res_check = mysql_query($sql_check) or die("Error en la consulta de Carpeta: " . mysql_error());
                                        $ncheck = mysql_num_rows($res_check);
                                        if ($ncheck > 0){
                                            for ($i = 0 ; $i < $ncheck; $i++){
                                                $ncarp++;
                                                $row_permiso = mysql_fetch_array($res_check);
                                                $valores .= $row_permiso['ID'] . ", ";
                                            }

                                        }
                                    }
                                    $sql_update .= " (" . substr($valores, 0 , -2) .")";
                                    if ($ncarp > 0){
                                        $res_update = mysql_query($sql_update) or die ($error . " " . $sql_update . " " . mysql_error($link));
                                    }
                                }
                                else{
                                    $error .= '. ' . $errnocarp;
                                }
                            }
                            else {
                                $error .= '. ' . $errgssicheck;
                            }
                        }
                        else{
                            $error .= '. ' . $errnogssi;
                        }

                    }
                    if ($origen == "impexcel"){
                        $enlaceok = "permisos_flota.php";
                        $enlacefail = "excel_flota.php";
                        $namehid = "idflota";
                        $valuehid = $idflota;
                        $error = $errimpgrupos;
                        $mensaje = $mensimpgrupos;
                        // Aumentamos el tamaño de la memoria:
                        ini_set('memory_limit', '64M');
                        // Clases para generar el Excel
                        /** Error reporting */
                        error_reporting(E_ALL);
                        date_default_timezone_set('Europe/Madrid');
                        /** PHPExcel */
                        require_once 'Classes/PHPExcel.php';
                        $fichero = "flotas/$idflota.xls";

                        // Creamos el objeto PHPExcel
                        $objPHPExcel = new PHPExcel();

                        $tipoFich = PHPExcel_IOFactory::identify($fichero);
                        $objReader = PHPExcel_IOFactory::createReader($tipoFich);
                        // Leemos los datos de la hoja de Terminales:
                        $nomHoja = "(4) ISSIs - PERMISOS";

                        // Sólo nos interesa cargar los datos:
                        $objReader->setReadDataOnly(true);
                        try {
                            $objPHPExcel = $objReader->load($fichero);
                        } catch (Exception $e) {
                            die("Error al cargar el fichero de datos: " . $e->getMessage());
                        }

                        // Fijamos como hoja activa la primera (sólo se importa una)
                        try{
                            $objPHPExcel->setActiveSheetIndexByName($nomHoja);
                        }
                        catch (Exception $e){
                            $nomHoja = $nomHoja." ";
                            try {
                                $objPHPExcel->setActiveSheetIndexByName($nomHoja);
                            }
                            catch (Exception $f){
                                echo "<p class = 'error'>No se ha encontrado la hoja de datos buscada</p>";
                            }
                        }

                        // Borramos los grupos de la base de datos:
                        $sql_update = "DELETE FROM permisos_flotas WHERE FLOTA = " . $idflota;
                        $res_update = mysql_query($sql_update) or die ($error . " " . $errdelpermimp . mysql_error($link));

                        // Leemos los datos:
                        // Leemos las carpetas:
                        $permisos = array();
                        $columna = 3;
                        $fila = $fila_inicio - 1;
                        $carpetas = array();
                        while ($columna < $maxcolumna) {
                            $permcarp = array();
                            $fila = $fila_inicio;
                            $carpeta = trim($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila_inicio - 1)->getValue());
                            if ($carpeta != "") {
                                while ($fila < $maxfila) {
                                    $permiso = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue();
                                    if ($permiso != ""){
                                        $gssi = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, $fila)->getValue();
                                        $mnemo = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $fila)->getValue();
                                        $permiso = strtoupper($permiso);
                                        if ($permiso == "X"){
                                            $observ = "-";
                                        }
                                        else{
                                            $observ = $permiso;
                                        }
                                        $permcarp[] = array($gssi, $mnemo, $observ);
                                    }
                                    $fila++;
                                }
                                if (count($permcarp > 0)){
                                    $permisos[$carpeta] = $permcarp;
                                }
                            }
                            $columna++;
                        }
                        foreach ($permisos as $carpeta => $permcarp) {
                            $sql_update = "INSERT INTO permisos_flotas (FLOTA, GISSI, CARPTERM, NOMBRE) VALUES";
                            $i = 0;
                            $fila = "";
                            foreach ($permcarp as $vectfila) {
                                $fila .= " ('" . $idflota . "', '" . $vectfila[0] . "', '" . $carpeta . "', '" . $vectfila[2] . "'),";
                            }
                            $fila = substr($fila, 0, -1) . ";";
                            $sql_update .= $fila;
                            $res_update = mysql_query($sql_update) or die ($error . " " . $sql_update . " " . mysql_error($link));
                        }
                    }
                }
                else{
                    $error = "Error: " . $errcheckflota;
                }
            }
            else{
                $error = "Error: " . $errnoflota;
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
            <p>
                <?php //echo $sql_total;?>
            </p>
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
