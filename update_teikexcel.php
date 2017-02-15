<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/teikexc_$idioma.php";
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

// Clases para generar el Excel
/** PHPExcel */
require_once 'Classes/PHPExcel.php';
$fichero = "flotas/$idflota.xls";
/** Error reporting */
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');

// Creamos el objeto PHPExcel
$objPHPExcel = new PHPExcel();
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
        $sql_flota = "SELECT * from FLOTAS WHERE ID = '$idflota'";
        $res_flota = mysql_query($sql_flota);
        $res_update = true;
        if ($res_flota){
            $nflota = mysql_num_rows($res_flota);
            if ($nflota == 0){
                $res_update = false;
                $error = $error_noflota;
            }
            else{
                $row_flota = mysql_fetch_array($res_flota);
                $flota = $row_flota["FLOTA"];
                //Leemos los datos de a hoja:
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
                // Fijamos como hoja activa la segunda
                $objPHPExcel->setActiveSheetIndex(1);
                // Obtenemos el número de filas de la hoja:
                $numfilas = $objPHPExcel->getActiveSheet()->getHighestRow();

                $fila = $filaterm;
                $errores = 0;
                while (($objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue()!= "")&&($errores == 0)) {
                    $issi = $objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue();
                    $tei = $objPHPExcel->getActiveSheet()->getCell("I".$fila)->getValue();
                    $numk = $objPHPExcel->getActiveSheet()->getCell("R".$fila)->getValue();
                    $update = true;
                    if (!(empty ($issisel))){
                        $update = false;
                        for ($i = 0; $i < count($issisel); $i++){
                            if (!$update){
                                if ($issi == $issisel[$i]){
                                    $update = true;
                                }
                            }
                        }
                    }
                    if ($update){
                        $sql_issi = "SELECT * FROM terminales WHERE ISSI = '$issi' AND FLOTA = '$idflota'";
                        $res_issi = mysql_query($sql_issi, $link) or die ($errissi."$issi: ". mysql_error());
                        $nissi = mysql_num_rows($res_issi);
                        if ($nissi != 1){
                            $error = "$errissi $issi $errissi2";
                            $errores++;
                            $res_update = false;
                        }
                        else{
                            $row_issi = mysql_fetch_array($res_issi);
                            $idterm = $row_issi["ID"];
                            $sql_updterm = "UPDATE terminales SET TEI = '$tei', ";
                            $sql_updterm .= "NUMEROK = '$numk' WHERE ID = '$idterm'";
                            $res_updterm = mysql_query($sql_updterm);
                            $res_update = ($res_update && $res_updterm);
                            $termok++;
                        }
                    }
                    $fila++;
                }
            }
        }
        else {
            $res_update = false;
            $error = "Error en la consulta de flota: ".mysql_error();
        }
        if ($res_update){
            $update = "OK";
            $mensflash = "$termok $mensaje";
        }
        else{
            $update = "OK";
            $mensflash = $error;
        }
?>
        <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="detalle_flota.php" method="POST">
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
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
