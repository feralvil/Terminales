<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/terminales_$idioma.php";
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

// Aumentamos el tamaño de la memoria:
ini_set('memory_limit', '64M');

// Importamos las variables de formulario:
import_request_variables("gp", "");

// Obtenemos el usuario
include_once('auth_user.php');
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    $flota = $flota_usu;
    $permiso = 1;
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
if (($tei != '') || ($issi != '') || ($nserie != '') || (!(empty ($issiterm)))) {
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
    if (!(empty ($issiterm))){
        $issi = $issiselec;
        $sql .= " AND terminales.ID IN (";
        for ($i = 0; $i < count($issiterm); $i++){
            $sql .= $issiterm[$i];
            if ($i < (count($issiterm) - 1)){
                $sql .= ", ";
            }
        }
        $sql .= ")";
    }
}
if (($autentica != '') && ($autentica != "00")) {
    $sql = $sql . "AND (terminales.AUTENTICADO = '$autentica') ";
}
if (($carpeta != '') && ($carpeta != "00")) {
    $sql = $sql . "AND (terminales.CARPETA='".$carpeta."') ";
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
if (($modelo != '') && ($modelo != "00")) {
    $sql = $sql . "AND (terminales.MODELO='$modelo') ";
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
}
$sql_term = $sql . "ORDER BY flotas.ACRONIMO ASC, terminales.ID ASC";
$res_term = mysql_query($sql_term) or die(mysql_error());
$nterm = mysql_num_rows($res_term);
$fila = 2;

# Creamos el objeto Excel
$objPHPExcel = new PHPExcel();
$locale = 'Es';
$validLocale = PHPExcel_Settings::setLocale($locale);


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
$estiloTitulo = array(
    'font' => array('bold' => true, 'size' => 12),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('argb' => 'FFCCCCCC'),
    )
);
$estiloCriterio = array(
    'font' => array('bold' => true, 'size' => 11),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
);
$estiloError = array(
    'font' => array('bold' => true, 'size' => 11, 'color' => array('argb' => 'FFFF0000',),),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
);
$estiloRelleno = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('argb' => 'FFEFEFEF'),
    )
);
$estiloCelda = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
);
$estiloTh = array(
    'font' => array('bold' => true, 'size' => 10),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    ),
);
$estiloHeader = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('argb' => 'FFCCCCCC'),
    ),
    'font' => array('bold' => true, 'size' => 10),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
);
$estiloCentro = array(
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
);

// Fijamos como hoja activa la primera y fijamos el título:
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setTitle('(3) ISSI');

// Tamaño de papel (A4) y orientación (Apaisado)
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

//Ajustamos 1 Página de Ancho x N de Alto
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

// En PDF no se imprime la rejilla:
if ($formato == 'pdf'){
    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
}

// Pie de Página
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

// Añadimos el Logo:
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName("Logo");
$objDrawing->setCoordinates('A1');
$objDrawing->setPath('./imagenes/comdesmini.png');
$objDrawing->setHeight(50);
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

// Título de la hoja:
$objPHPExcel->getActiveSheet()->setCellValue('A4', $h1);
$objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estiloTitulo);
$objPHPExcel->getActiveSheet()->mergeCells('A4:V4');

// Fijamos la fecha:
$objPHPExcel->getActiveSheet()->setCellValue('A6', $criterios);
$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($estiloCriterio);
$objPHPExcel->getActiveSheet()->mergeCells('A6:E6');
$fecha = date("d/m/Y");
$objPHPExcel->getActiveSheet()->setCellValue('N6', $thfecha);
$objPHPExcel->getActiveSheet()->getStyle('N6')->applyFromArray($estiloTh);
$objPHPExcel->getActiveSheet()->setCellValue('O6', $fecha);
$objPHPExcel->getActiveSheet()->mergeCells('O6:P6');
$objPHPExcel->getActiveSheet()->getStyle('N6:P6')->applyFromArray($estiloCelda);


// Criterios de selección
// Fila de Inicio de los terminales
$fila = 7;
$fila_term = 25;
if (($flota!='')&&($flota!="NN")) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, 'Flota');
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $row_flota["FLOTA"]);
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila . ':I' . $fila);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':I' . $fila)->applyFromArray($estiloCelda);
    $fila = $fila + 2;
}
$columnas = array(0,4,8);
$indexcol = 0;
if ($issi!="") {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, 'ISSI');
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $issi);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if ($tei!="") {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, 'TEI');
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $tei);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if ($nserie!="") {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, $thnserie);
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $nserie);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if (($tipoterm!='')&&($tipoterm!="00")) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, $thtipo);
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $tipoterm);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if ((($marca!='')&&($marca!="00")) || (($modelo!='')&&($modelo!="00"))) {
    $valor = '';
    if (($marca!='')&&($marca!="00")){
        $valor .= $marca;
    }
    $valor .= '-';
    if (($modelo!='')&&($modelo!="00")){
        $valor .= $modelo;
    }
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, 'Marca - ' . $thmodelo);
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $valor);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if (($carpeta!='')&&($carpeta!="00")) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, 'Carpeta');
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $carpeta);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if (($autentica!='')&&($autentica!="00")) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, $thaut);
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $autentica);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if (($dots!='')&&($dots!="00")) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, 'Alta DOTS');
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $dots);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if (($permisos!='')&&($permisos!="00")) {
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol], $fila, $thllamind);
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($columnas[$indexcol], $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columnas[$indexcol] + 1, $fila, $permisos);
    $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columnas[$indexcol] + 1, $fila, $columnas[$indexcol] + 3, $fila);
    $indexcol++;
    if ($indexcol == 3){
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);
        $indexcol = 0;
        $fila = $fila + 2;
    }
}
if ($indexcol > 0){
    $colact = PHPExcel_Cell::stringFromColumnIndex ($columnas[$indexcol - 1] + 3);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':' . $colact . $fila)->applyFromArray($estiloCelda);
    $fila = $fila + 2;
}

// Imprimir el número de registros:
$objPHPExcel->getActiveSheet()->setCellValue("A$fila", $thnterm);
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloTh);
$objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':D' . $fila);
$objPHPExcel->getActiveSheet()->setCellValue("E$fila", $nterm);
$objPHPExcel->getActiveSheet()->mergeCells('E' . $fila . ':F' . $fila);
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($estiloCelda);
// Imprimir el rango de Flotas:
$objPHPExcel->getActiveSheet()->setCellValue("I$fila", $thrango);
$objPHPExcel->getActiveSheet()->getStyle('I' . $fila)->applyFromArray($estiloTh);
$objPHPExcel->getActiveSheet()->mergeCells('I' . $fila . ':J' . $fila);
if ($row_flota['RANGO'] == ""){
    $objPHPExcel->getActiveSheet()->getStyle("K$fila")->applyFromArray($estilerr);
}
else{
    $objPHPExcel->getActiveSheet()->setCellValue("K$fila", $row_flota["RANGO"]);
}
$objPHPExcel->getActiveSheet()->mergeCells('K' . $fila . ':L' . $fila);
$objPHPExcel->getActiveSheet()->getStyle('I' . $fila . ':L' . $fila)->applyFromArray($estiloCelda);

// Imprimir los resultados
// Imprimimos la cabecera de la tabla;
$fila = $fila + 2;
$fila_aux = $fila + 1;
$objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, 'Flota');
$objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':A' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, 'TERMINAL');
$objPHPExcel->getActiveSheet()->mergeCells('B' . $fila . ':G' . $fila);
$objPHPExcel->getActiveSheet()->setCellValue('H' . $fila, 'ISSI');
$objPHPExcel->getActiveSheet()->mergeCells('H' . $fila . ':H' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('I' . $fila, 'TEI');
$objPHPExcel->getActiveSheet()->mergeCells('I' . $fila . ':I' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('J' . $fila, $thnserie);
$objPHPExcel->getActiveSheet()->mergeCells('J' . $fila . ':J' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('K' . $fila, $thmnemo);
$objPHPExcel->getActiveSheet()->mergeCells('K' . $fila . ':K' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('L' . $fila, 'Carpeta');
$objPHPExcel->getActiveSheet()->mergeCells('L' . $fila . ':L' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('M' . $fila, $thllamind);
$objPHPExcel->getActiveSheet()->mergeCells('M' . $fila . ':N' . $fila);
$objPHPExcel->getActiveSheet()->setCellValue('O' . $fila, 'Alta DOTS');
$objPHPExcel->getActiveSheet()->mergeCells('O' . $fila . ':O' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('P' . $fila, $thaut);
$objPHPExcel->getActiveSheet()->mergeCells('P' . $fila . ':P' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('Q' . $fila, $thenc);
$objPHPExcel->getActiveSheet()->mergeCells('Q' . $fila . ':Q' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('R' . $fila, $thdirip);
$objPHPExcel->getActiveSheet()->mergeCells('R' . $fila . ':R' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('S' . $fila, $thversion);
$objPHPExcel->getActiveSheet()->mergeCells('S' . $fila . ':S' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('T' . $fila, $thfalta);
$objPHPExcel->getActiveSheet()->mergeCells('T' . $fila . ':T' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('U' . $fila, $thobserv);
$objPHPExcel->getActiveSheet()->mergeCells('U' . $fila . ':U' . $fila_aux);
$objPHPExcel->getActiveSheet()->setCellValue('V' . $fila, $thnumk);
$objPHPExcel->getActiveSheet()->mergeCells('V' . $fila . ':V' . $fila_aux);
$fila++;
$objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, 'Marca');
$objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $thmodelo);
$objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $thtipo);
$objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $thprov);
$objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $thcodhw);
$objPHPExcel->getActiveSheet()->setCellValue('G' . $fila, 'A.M.');
$objPHPExcel->getActiveSheet()->setCellValue('M' . $fila, 'D');
$objPHPExcel->getActiveSheet()->setCellValue('N' . $fila, 'S-D');
$fila_aux = $fila - 1;
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila_aux . ':V' . $fila)->applyFromArray($estiloTh);

// Ampliamos el timepo máximo de ejecución
if ($nterm > 400){
    set_time_limit(90);
}
$fila_term = $fila + 1;
for ($j = 0; $j < $nterm; $j++){
    $fila = $fila_term + $j;
    $row_term = mysql_fetch_array($res_term);
    $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $row_term["ACRONIMO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $row_term["MARCA"]);
    $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $row_term["MODELO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $row_term["TIPO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("E$fila", $row_term["PROVEEDOR"]);
    $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $row_term["CODIGOHW"]);
    $objPHPExcel->getActiveSheet()->setCellValue("G$fila", $row_term["AM"]);
    $objPHPExcel->getActiveSheet()->setCellValue("H$fila", $row_term["ISSI"]);
    $tei = $row_term["TEI"];
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
    $objPHPExcel->getActiveSheet()->getCell("I$fila")->setValueExplicit($tei, PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->setCellValue("J$fila", $row_term["NSERIE"]);
    $objPHPExcel->getActiveSheet()->setCellValue("K$fila", $row_term["MNEMONICO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("L$fila", $row_term["CARPETA"]);
    $objPHPExcel->getActiveSheet()->setCellValue("M$fila", $row_term["DUPLEX"]);
    $objPHPExcel->getActiveSheet()->setCellValue("N$fila", $row_term["SEMID"]);
    $objPHPExcel->getActiveSheet()->setCellValue("O$fila", $row_term["DOTS"]);
    $objPHPExcel->getActiveSheet()->setCellValue("P$fila", $row_term["AUTENTICADO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("Q$fila", $row_term["ENCRIPTADO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("R$fila", $row_term["DIRIP"]);
    $objPHPExcel->getActiveSheet()->setCellValue("S$fila", $row_term["VERSION"]);
    $objPHPExcel->getActiveSheet()->setCellValue("T$fila", $row_term["FALTA"]);
    $objPHPExcel->getActiveSheet()->setCellValue("U$fila", $row_term["OBSERVACIONES"]);
    $objPHPExcel->getActiveSheet()->setCellValue("V$fila", $row_term["NUMEROK"]);
    if (($j % 2) == 1){
        $rango = "A$fila:V$fila";
        $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloRelleno);
    }
}
$fila_aux = $fila_term - 2;
$rango = "A" . $fila_aux . ":V" . $fila;
$objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloCelda);

$colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
$maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
for ($i = 0; $i < $maxcol; $i++){
    $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
}

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Creamos el fichero defintivo
$nom_fichero = $termcomdes."_".$row_flota["ACRONIMO"];
if (!(empty ($issiterm))){
    $nom_fichero .= "_ISSI";
    $issi = '';
}

if ($issi != '') {
    $nom_fichero .= "_" . $issi;
}
if ($formato == "xls"){
    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
    header('Content-Disposition: attachment;filename="'.$nom_fichero.'.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
}
elseif ($formato == "pdf"){
    // Redirect output to a client’s web browser (PDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="'.$nom_fichero.'.pdf"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
}
$objWriter->save('php://output');
exit;
?>
