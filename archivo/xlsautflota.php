<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/export_$idioma.php";
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

// Clases para generar el Excel
/** Error reporting */
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
/** PHPExcel */
require_once 'Classes/PHPExcel.php';

// Consulta a la base de datos - Tabla Terminales
$sql_term = "SELECT * FROM terminales WHERE terminales.FLOTA = $idflota ";

if (!(empty ($termaut))){
    $sql_term .= "AND terminales.ID IN (";
    for ($i = 0; $i < count($termaut); $i++){
        $sql_term .= $termaut[$i];
        if ($i < (count($termaut) - 1)){
            $sql_term .= ", ";
        }
    }
    $sql_term .= ") ";        
}
if (($carpeta != "") && ($carpeta != "NN")) {
    $sql_term .= "AND (CARPETA = '".$carpeta."') ";
}
if ($issi != '') {
    $sql_term .= "AND (ISSI = '".$issi."') ";
}
if ($tei != '') {
    $sql_term .= "AND (TEI = '".$tei."') ";
}
$sql_term = $sql_term."ORDER BY terminales.ISSI ASC";
$res_term = mysql_query($sql_term) or die(mysql_error());
$nterm = mysql_num_rows($res_term);

// Consulta a la base de datos - Tabla Flotas
$sql_flota = "SELECT * FROM flotas WHERE ID = $idflota";
$res_flota = mysql_query($sql_flota) or die(mysql_error());
$nflota = mysql_num_rows($res_flota);
$fila = 2;

# Creamos el objeto Excel
$objPHPExcel = new PHPExcel();
$locale = 'Es';
$validLocale = PHPExcel_Settings::setLocale($locale);

// Leemos el fichero de la plantilla:
$fichero = "plantillas/termaut_$idioma.xls";
$tipoFich = PHPExcel_IOFactory::identify($fichero);
$objReader = PHPExcel_IOFactory::createReader($tipoFich);
// Cargamos el fichero
try {
    $objPHPExcel = $objReader->load($fichero);
}
catch(Exception $e){
    die("$errtemp: ".$e->getMessage());
}

// Set properties
$objPHPExcel->getProperties()->setCreator("Oficina COMDES");
$objPHPExcel->getProperties()->setLastModifiedBy("Oficina COMDES");
$objPHPExcel->getProperties()->setTitle("Oficina COMDES");
$objPHPExcel->getProperties()->setSubject("Oficina COMDES");
$objPHPExcel->getProperties()->setDescription("Oficina COMDES");
$objPHPExcel->getProperties()->setKeywords("Oficina COMDES Terminales");
$objPHPExcel->getProperties()->setCategory("Terminales COMDES");




// Fijamos como hoja activa la primera:
$nomHoja = "(3) ISSI";
try{
    $objPHPExcel->setActiveSheetIndexByName($nomHoja);
}
catch (Exception $e) {
    die("Error al cargar el fichero de datos: " . $e->getMessage());
}

// Fijamos los estilos generales de la hoja:
$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Fondo Gris de fila de datos
$estilgris = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array(
            'argb' => 'FFF0F2F5'
        ),
    ),
);
// Estilo de error
$estilerr = array(
    'font' => array(
        'bold' => true,
        'color' => array(
            'argb' => 'FFFF0000',
        ),
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    ),
);

// Tamaño de papel (A4) y orientación (Apaisado)
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

// Pie de Página
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

// Añadimos el Logo:
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName("Logo");
$objDrawing->setCoordinates('A1');
$objDrawing->setPath('./imagenes/comdes.png');
$objDrawing->setHeight(50);
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

// Primera hoja: Título de la tabla de datos
$objPHPExcel->getActiveSheet()->setCellValue('A2', $h1term);

// Fecha:
$fecha = date("d/m/Y");
$objPHPExcel->getActiveSheet()->setCellValue('O4', $fecha);

// Datos de la flota:
if ($nflota > 0){
    $row_flota = mysql_fetch_array($res_flota);
    $objPHPExcel->getActiveSheet()->setCellValue('C6', $row_flota["FLOTA"]);
    $idc = 0;
    if ($row_flota["RESPONSABLE"] != 0){
        $idc = $row_flota["RESPONSABLE"];
        // Consulta a la base de datos - Tabla Contactos
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idc";
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $row_contacto = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('C9', $row_contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G9', $row_contacto["NIF"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $row_contacto["CARGO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C11', $row_flota["DOMICILIO"]);
            // Consulta a la base de datos - Tabla Municipios
            $ine = $row_flota["INE"];
            $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
            $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
            $nmun = mysql_num_rows($res_mun);
            if ($nmun > 0) {
                $row_mun = mysql_fetch_array($res_mun);
                $objPHPExcel->getActiveSheet()->setCellValue('C12', $row_mun["MUNICIPIO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('G12', $row_mun["PROVINCIA"]);
            }
            $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idc";
            $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('C13', $row_contacto["TELEFONO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G13', $row_contacto["MAIL"]);
        }
    }
    else {
        for ($i = 8; $i <= 14; $i++){
            $objPHPExcel->getActiveSheet()->getRowDimension("$i")->setVisible(false);
        }
    }

    // Imprimir el número de registros:
    $objPHPExcel->getActiveSheet()->setCellValue('A15', "- $nreg: $nterm");

    // Imprimir los resultados
    // Repetimos la cabecera de la tabla;
    $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(17, 18);


    // Imprimir los terminales
    if ($nterm > 4){
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(20, ($nterm - 4));
    }
    for ($j = 0; $j < $nterm; $j++){
        $fila = 19 + $j;
        $row_term = mysql_fetch_array($res_term);
        $objPHPExcel->getActiveSheet()->setCellValue("A$fila",($j +1)); //$row_term["ID"]);
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
        
        
        $objPHPExcel->getActiveSheet()->setCellValue("O$fila", $row_term["DOTS"]);
        $objPHPExcel->getActiveSheet()->setCellValue("P$fila", $row_term["AUTENTICADO"]);
        $objPHPExcel->getActiveSheet()->setCellValue("Q$fila", $row_term["ENCRIPTADO"]);
        $objPHPExcel->getActiveSheet()->setCellValue("R$fila", $row_term["DIRIP"]);
        $objPHPExcel->getActiveSheet()->getCell("S$fila")->setValueExplicit($row_term["VERSION"], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue("T$fila", $row_term["FALTA"]);
        $objPHPExcel->getActiveSheet()->setCellValue("U$fila", $row_term["OBSERVACIONES"]);
        $objPHPExcel->getActiveSheet()->setCellValue("V$fila", $row_term["NUMEROK"]);
        if (($j % 2) == 1){
            $rango = "A$fila:V$fila";
            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilgris);
        }
    }    
}
else{
    $objPHPExcel->getActiveSheet()->removeRow(3, 31);
    $objPHPExcel->getActiveSheet()->setCellValue("A4", $errnoflota);
    $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estilerr);
    $objPHPExcel->getActiveSheet()->mergeCells('A4:O4');
}

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
$fichero = $terminales."_".$row_flota["ACRONIMO"];

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$fichero.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>
