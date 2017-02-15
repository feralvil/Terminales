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

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_query($sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    if ($usu != ""){
        $permiso = 1;
        $idflota = $flota_usu;
    }
}

// Clases para generar el Excel
/** Error reporting */
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
/** PHPExcel */
require_once 'Classes/PHPExcel.php';

// Consulta a la base de datos - Tabla Terminales
$sql_term = "SELECT * FROM terminales WHERE terminales.FLOTA = $idflota ";
$sql_term = $sql_term."ORDER BY terminales.ISSI ASC";
$res_term = mysql_query($sql_term) or die("Error en la consulta de $terminales: ".mysql_error());
$nterm = mysql_num_rows($res_term);

// Consulta a la base de datos - Tabla Flotas
$sql_flota = "SELECT * FROM flotas WHERE ID = $idflota";
$res_flota = mysql_query($sql_flota) or die("Error en la consulta de flota: ".mysql_error());
$nflota = mysql_num_rows($res_flota);

# Creamos el objeto Excel
$objPHPExcel = new PHPExcel();
$locale = 'Es';
$validLocale = PHPExcel_Settings::setLocale($locale);

// Leemos el fichero de la plantilla:
$fichero = "plantillas/flota_$idioma.xls";
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

// Fijamos como hoja activa la primera (Datos de la Flota):
$objPHPExcel->setActiveSheetIndex(0);

// Tamaño de papel (A4) y orientación (Apaisado)
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

// Pie de Página
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

// Añadimos el Logo:
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName("Logo");
$objDrawing->setCoordinates('A1');
$objDrawing->setPath('./imagenes/comdes.png');
$objDrawing->setHeight(50);
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

// Primera hoja: Datos de la Flota y contactos:
$fecha = date("d/m/Y");
$objPHPExcel->getActiveSheet()->setCellValue('M4', $fecha);

// Datos de la flota:
if ($nflota > 0){
    $row_flota = mysql_fetch_array($res_flota);

    // Añadimos los datos de la Flota
    $objPHPExcel->getActiveSheet()->setCellValue('C6', $row_flota["FLOTA"]);

    // Datos del responsable:
    if ($row_flota["RESPONSABLE"] != 0){
        $idc = $row_flota["RESPONSABLE"];
        // Consulta a la base de datos - Tabla Contactos
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idc";
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $row_contacto = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('C9', $row_contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J9', $row_contacto["NIF"]);
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
                $objPHPExcel->getActiveSheet()->setCellValue('J12', $row_mun["PROVINCIA"]);
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C13', $row_contacto["TELEFONO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I13', $row_contacto["MAIL"]);
        }
    }

    // Personas de Contacto para incidencias:
    $id_contacto = array($row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
    for ($i = 0; $i < count($id_contacto); $i++) {
        if ($id_contacto[$i] != 0) {
            $fila = 17 + 3*$i;
            $idc = $id_contacto[$i];
            $sql_contacto = "SELECT * FROM contactos WHERE ID=$idc";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto != 0) {
                $row_contacto = mysql_fetch_array($res_contacto);
                $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $row_contacto["NOMBRE"]);
                $objPHPExcel->getActiveSheet()->setCellValue("J$fila", $row_contacto["CARGO"]);
                $fila++;
                $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $row_contacto["TELEFONO"]);
                $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $row_contacto["MOVIL"]);
                $objPHPExcel->getActiveSheet()->setCellValue("K$fila", $row_contacto["MAIL"]);
            }
        }
    }

    // Personas de Contacto para incidencias:
    $id_contacto = array($row_flota["INCID1"], $row_flota["INCID2"], $row_flota["INCID3"], $row_flota["INCID4"]);
    for ($i = 0; $i < count($id_contacto); $i++) {
        if ($id_contacto[$i] != 0) {
            $fila = 29 + $i;
            $idc = $id_contacto[$i];
            $sql_contacto = "SELECT * FROM contactos WHERE ID=$idc";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto != 0) {
                $row_contacto = mysql_fetch_array($res_contacto);
                $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $row_contacto["NOMBRE"]);
                $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $row_contacto["CARGO"]);
                $objPHPExcel->getActiveSheet()->setCellValue("G$fila", $row_contacto["MOVIL"]);
                $objPHPExcel->getActiveSheet()->setCellValue("I$fila", $row_contacto["MAIL"]);
                $objPHPExcel->getActiveSheet()->setCellValue("L$fila", $row_contacto["HORARIO"]);
            }
        }
    }

    // Fijamos como hoja activa la segunda (Terminales):
    $objPHPExcel->setActiveSheetIndex(1);

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

    // Añadimos el Logo:
    //$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    // Añadimos la Fecha:
    $objPHPExcel->getActiveSheet()->setCellValue('O4', $fecha);

    // Añadimos el Nombre de Flota:
    $objPHPExcel->getActiveSheet()->setCellValue('C6', $row_flota["FLOTA"]);

    // Imprimir el número de registros:
    $objPHPExcel->getActiveSheet()->setCellValue('D8', $nterm);

    // Imprimir los resultados
    // Repetimos la cabecera de la tabla;
    $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(11, 12);

    // Imprimir los terminales
    if ($nterm > 4){
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(13, ($nterm - 4));
    }
    for ($j = 0; $j < $nterm; $j++){
        $fila = 12 + $j;
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
$fichero = "Flota_".$row_flota["ACRONIMO"].".pdf";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="'.$fichero.'"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
$objWriter->save('php://output');
exit;
?>
