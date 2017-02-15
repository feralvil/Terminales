<?php

// ------------ Obtención del usuario Joomla! --------------------------------------- //
// Le decimos que estamos en Joomla
define('_JEXEC', 1);

// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..');

// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

// Iniciamos nuestra aplicación (site: frontend)
$mainframe = & JFactory::getApplication('site');

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/export_$idioma.php";
include ($lang);

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;
// ------------------------------------------------------------------------------------- //
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

import_request_variables("gp", "");

// Aumentamos el tamaño de la memoria:
ini_set('memory_limit', '64M');

$permiso = 0;
/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
if ($usu != ""){
    $sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
    $res_oficina = mysql_query($sql_oficina);
    $row_oficina = mysql_fetch_array($res_oficina);
    $flota_usu = $row_oficina["ID"];
    /*
    *  $permiso = variable de permisos de flota:
    *      0: Sin permiso
    *      1: Permiso de consulta
    *      2: Permiso de modificación
    */

    if ($flota_usu == 100) {
        $permiso = 2;
    }
    else {
        $sql_permiso = "SELECT * FROM usuarios_flotas WHERE NOMBRE='$usu'";
        $res_permiso = mysql_query($sql_permiso) or die(mysql_error());
        $npermiso = mysql_num_rows($res_permiso);
        if ($npermiso > 1) {
            $permiso = 1;
        }
    }
}

// Clases para generar el Excel
/** Error reporting */
//error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
/** PHPExcel */
require_once 'Classes/PHPExcel.php';

// Consulta a la base de datos
$sql = "SELECT flotas.ACRONIMO, terminales.* FROM terminales, flotas ";
$sql = $sql . "WHERE (terminales.FLOTA = flotas.ID) ";
if ($permiso == 2) {
    if (($flota != '') && ($flota != "NN")) {
        $sql = $sql . "AND (terminales.FLOTA='$flota') ";
        $sql_flota = "SELECT * FROM flotas WHERE ID='$flota'";
        $res_flota = mysql_query($sql_flota) or die(mysql_error());
        $row_flota = mysql_fetch_array($res_flota);
        $flota_txt = $row_flota["FLOTA"];
    }
}
elseif ($permiso == 1) {
    if (($flota != '') && ($flota != "NN")) {
        $sql = $sql . "AND (terminales.FLOTA='$flota') ";
        $sql_flota = "SELECT * FROM flotas WHERE ID='$flota'";
        $res_flota = mysql_query($sql_flota) or die(mysql_error());
        $row_flota = mysql_fetch_array($res_flota);
        $flota_txt = $row_flota["FLOTA"];
    }
    else {
        $sql_flotas = "SELECT ID, ACRONIMO FROM flotas, usuarios_flotas WHERE ";
        $sql_flotas = $sql_flotas . "usuarios_flotas.NOMBRE='$usu' AND flotas.ID = usuarios_flotas.ID_FLOTA";
        $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
        $flotas = array();
        for ($i = 0; $i < $nflotas; $i++) {
            $row_flotas = mysql_fetch_array($res_flotas);
            $flotas[$i] = $row_flotas[0];
        }
        $sql = $sql . "AND terminales.FLOTA IN (";
        for ($i = 0; $i < $nflotas; $i++) {
            $sql = $sql . $flotas[$i];
            if ($i < ($nflotas - 1)) {
                $sql = $sql . ",";
            }
        }
        $sql = $sql . ") ";
    }
}
else {
    $sql = $sql . "AND (terminales.FLOTA='$flota_usu') ";
    $flota = $flota_usu;
    $sql_flota = "SELECT * FROM flotas WHERE ID='$flota'";
    $res_flota = mysql_query($sql_flota) or die(mysql_error());
    $row_flota = mysql_fetch_array($res_flota);
    $flota_txt = $row_flota["FLOTA"];
}
if (($tei != '') || ($issi != '') || ($nserie != '')) {
    $amarco = $tipoterm = $marca = $dots = "00";
    if ($tei != '') {
        $sql = $sql . "AND (terminales.TEI='$tei') ";
    }
    if ($issi != '') {
        $sql = $sql . "AND (terminales.ISSI='$issi') ";
    }
    if ($nserie != '') {
        $sql = $sql . "AND (terminales.NSERIE='$nserie') ";
    }
}
if (($amarco != '') && ($amarco != "00")) {
    $sql = $sql . "AND (terminales.AM='$amarco') ";
}
if (($estado != '') && ($estado != "00")) {
    $sql = $sql . "AND (terminales.ESTADO='$estado') ";
}
if (($carpeta != '') && ($carpeta != "00")) {
    $sql = $sql . "AND (terminales.CARPETA='$carpeta') ";
}
if (($tipoterm != '') && ($tipoterm != "00")) {
    $sql = $sql . "AND (terminales.TIPO LIKE '$tipoterm') ";
    switch ($tipoterm) {
        case ("F"): {
                $tipoterm = $fijo;
                break;
        }
        case ("M%"): {
                $tipoterm = $movil;
                break;
        }
        case ("MB"): {
                $tipoterm = $movilb;
                break;
        }
        case ("MA"): {
                $tipoterm = $movila;
                break;
        }
        case ("MG"): {
                $tipoterm = $movilg;
                break;
        }
        case ("P%"): {
                $tipoterm = $portatil;
                break;
        }
        case ("PB"): {
                $tipoterm = $portatilb;
                break;
        }
        case ("PA"): {
                $tipoterm = $portatila;
                break;
        }
        case ("PX"): {
                $tipoterm = $portatilx;
                break;
        }
        case ("D"): {
                $tipoterm = $despacho;
                break;
        }
    }
}
if (($marca != '') && ($marca != "00")) {
    $sql = $sql . "AND (terminales.MARCA='$marca') ";
}
if (($permisos != '') && ($permisos != "00")) {
    switch ($permisos) {
        case "NO": {
                $sql = $sql . "AND (terminales.SEMID='NO') AND (terminales.DUPLEX='NO') ";
                $permisos = $permno;
                break;
        }
        case "SEMID": {
                $sql = $sql . "AND (terminales.SEMID='SI') ";
                $permisos = $perms;
                break;
        }
        case "DUPLEX": {
                $sql = $sql . "AND (terminales.DUPLEX='SI') ";
                $permisos = $permd;
                break;
        }
        case "SYD": {
                $sql = $sql . "AND (terminales.DUPLEX='SI') AND (terminales.SEMID='SI') ";
                $permisos = $permsd;
                break;
        }
    }
}
if (($estado != '') && ($estado != "00")) {
    $sql = $sql . "AND (terminales.ESTADO='$estado') ";
    switch ($estado) {
        case "A": {
                $estado = $alta;
                break;
        }
        case "B": {
                $estado = $baja;
                break;
        }
        case "R": {
                $estado = $rep;
                break;
        }
    }
}
if (($dots != '') && ($dots != "00")) {
    $sql = $sql . "AND (terminales.DOTS='$dots') ";
} //Aquí
$sql_term = $sql . "ORDER BY flotas.ACRONIMO ASC, terminales.ID ASC";
$res_term = mysql_query($sql_term) or die(mysql_error());
$nterm = mysql_num_rows($res_term);
$fila = 2;

# Creamos el objeto Excel
$objPHPExcel = new PHPExcel();
$locale = 'Es';
$validLocale = PHPExcel_Settings::setLocale($locale);

// Leemos el fichero de la plantilla:
$fichero = "plantillas/terminales_$idioma.xls";
$tipoFich = PHPExcel_IOFactory::identify($fichero);
$objReader = PHPExcel_IOFactory::createReader($tipoFich);
// Cargamos el fichero
try {
    $objPHPExcel = $objReader->load($fichero);
}
catch(Exception $e){
    die("$errload: ".$e->getMessage());
}

// Set properties
$objPHPExcel->getProperties()->setCreator("Oficina COMDES");
$objPHPExcel->getProperties()->setLastModifiedBy("Oficina COMDES");
$objPHPExcel->getProperties()->setTitle("Oficina COMDES");
$objPHPExcel->getProperties()->setSubject("Oficina COMDES");
$objPHPExcel->getProperties()->setDescription("Oficina COMDES");
$objPHPExcel->getProperties()->setKeywords("Oficina COMDES Terminales");
$objPHPExcel->getProperties()->setCategory("Terminales COMDES");

// Fijamos los estilos generales de la hoja:
$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

// Fijamos como hoja activa la primera y fijamos el título:
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle('(3) ISSI');

// Fijamos los estilos generales de la hoja:
// Fondo Gris de fila de datos
$estilgris = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array(
            'argb' => 'FFF0F2F5'
        ),
    ),
);

// Tamaño de papel (A4) y orientación (Apaisado)
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

//Ajustamos 1 Página de Ancho x N de Alto
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

// Pie de Página
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

// Añadimos el Logo:
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName("Logo");
$objDrawing->setCoordinates('A1');
$objDrawing->setPath('./imagenes/comdesmini.png');
$objDrawing->setHeight(50);
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

// Fijamos la fecha:
$fecha = date("d/m/Y");
$objPHPExcel->getActiveSheet()->setCellValue('O4', $fecha);
// Criterios de selección
$fila = 5;
if ((($flota!='')&&($flota!="NN"))||(($tipoterm!='')&&($tipoterm!="00"))||(($marca!='')&&($marca!="00"))||(($carpeta!='')&&($carpeta!="00"))||($tei!="")||($issi!="")||($nserie!="")||(($amarco!='')&&($amarco!="00"))||(($dots!='')&&($dots!="00"))||(($permisos!='')&&($permisos!="00"))) {
    if (($flota!='')&&($flota!="NN")) {
        $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $row_flota["FLOTA"]);
    }
    else {
        //Ocultamos las filas correspondientes
        $objPHPExcel->getActiveSheet()->getRowDimension('5')->setVisible(false);
        $objPHPExcel->getActiveSheet()->getRowDimension('6')->setVisible(false);
    }
    $fila = 7;
    if (($tei!="")||($issi!="")||($nserie!="")) {
        if ($issi!="") {
            $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $issi);
        }
        if ($tei!="") {
            $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $tei);
        }
        if ($nserie!="") {
            $objPHPExcel->getActiveSheet()->setCellValue("J$fila", $nserie);
        }
    }
    else{
        $objPHPExcel->getActiveSheet()->getRowDimension('7')->setVisible(false);
        $objPHPExcel->getActiveSheet()->getRowDimension('8')->setVisible(false);
        $fila_actual = $fila = 9;
        if (($tipoterm!='')&&($tipoterm!="00")) {
            $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $tipoterm);
            $fila_actual++;
        }
        if (($marca!='')&&($marca!="00")) {
            $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $marca);
            $fila_actual++;
        }
        if (($carpeta!='')&&($carpeta!="00")) {
            $objPHPExcel->getActiveSheet()->setCellValue("J$fila", $carpeta);
            $fila_actual++;
        }
        if($fila_actual == $fila){
            $objPHPExcel->getActiveSheet()->getRowDimension('9')->setVisible(false);
            $objPHPExcel->getActiveSheet()->getRowDimension('10')->setVisible(false);
        }
        $fila_actual = $fila = 11;
        if (($amarco!='')&&($amarco!="00")) {
            $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $amarco);
            $fila_actual++;
        }
        if (($dots!='')&&($dots!="00")) {
            $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $dots);
            $fila_actual++;
        }
        if (($permisos!='')&&($permisos!="00")) {
            $objPHPExcel->getActiveSheet()->setCellValue("J$fila", $marca);
            $fila_actual++;
        }
        if($fila_actual == $fila){
            $objPHPExcel->getActiveSheet()->getRowDimension('11')->setVisible(false);
            $objPHPExcel->getActiveSheet()->getRowDimension('12')->setVisible(false);
        }
    }
}

// Imprimir el número de registros:
$objPHPExcel->getActiveSheet()->setCellValue("C14", $nterm);

// Imprimir los resultados
// Repetimos la cabecera de la tabla;
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(16, 17);
$fila_inicio = 18;

// Imprimir los terminales
if ($nterm > 4){
    $objPHPExcel->getActiveSheet()->insertNewRowBefore(19, ($nterm - 4));
}
elseif ($nterm > 4){
    $objPHPExcel->getActiveSheet()->removeRow(21,(4 - $nterm));
}
for ($j = 0; $j < $nterm; $j++){
    $fila = $fila_inicio + $j;
    $row_term = mysql_fetch_array($res_term);
    $tipot = $row_term ["TIPO"];
    switch ($tipot) {
        case ("F"): {
            $tipot = $fijo;
            break;
        }
        case ("M%"): {
            $tipot = $movil;
            break;
        }
        case ("MB"): {
            $tipot = $movilb;
            break;
        }
        case ("MA"): {
            $tipot = $movila;
            break;
        }
        case ("MG"): {
            $tipot = $movilg;
            break;
        }
        case ("P%"): {
            $tipot = $portatil;
            break;
        }
        case ("PB"): {
            $tipot = $portatilb;
            break;
        }
        case ("PA"): {
            $tipot = $portatila;
            break;
        }
        case ("PX"): {
            $tipot = $portatilx;
            break;
        }
        case ("D"): {
            $tipot = $despacho;
            break;
        }
    }
    $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $row_term["ACRONIMO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $row_term["MARCA"]);
    $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $row_term["MODELO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $row_term["TIPO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("E$fila", $row_term["PROVEEDOR"]);
    $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $row_term["CODIGOHW"]);
    $objPHPExcel->getActiveSheet()->setCellValue("G$fila", $row_term["AM"]);
    $objPHPExcel->getActiveSheet()->setCellValue("H$fila", $row_term["ISSI"]);
    $objPHPExcel->getActiveSheet()->getCell("I$fila")->setValueExplicit($row_term["TEI"], PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValue("J$fila", $row_term["NSERIE"]);
    $objPHPExcel->getActiveSheet()->setCellValue("K$fila", $row_term["MNEMONICO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("L$fila", $row_term["CARPETA"]);
    $objPHPExcel->getActiveSheet()->setCellValue("M$fila", $row_term["DUPLEX"]);
    $objPHPExcel->getActiveSheet()->setCellValue("N$fila", $row_term["SEMID"]);
    $objPHPExcel->getActiveSheet()->setCellValue("O$fila", $row_term["OBSERVACIONES"]);
    $objPHPExcel->getActiveSheet()->setCellValue("P$fila", $row_term["FALTA"]);
    $objPHPExcel->getActiveSheet()->setCellValue("Q$fila", $row_term["DOTS"]);
    if ($permiso == 2){
        $objPHPExcel->getActiveSheet()->setCellValue("R$fila", $row_term["NUMEROK"]);
    }
    if (($j % 2) == 1){
        $rango = "A$fila:R$fila";
        $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilgris);
    }
}


// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$terminales.'_COMDES.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>
