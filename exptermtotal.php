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
ini_set('memory_limit', '512M');

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
$sql_term = $sql . "ORDER BY terminales.ISSI ASC";
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

// Título de la hoja:
$objPHPExcel->getActiveSheet()->setCellValue('A1', $h1);
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
$objPHPExcel->getActiveSheet()->mergeCells('A1:V1');

// Imprimir el número de registros y a fecha:
$fila = 3;
$objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $thnterm);
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloTh);
$objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':D' . $fila);
$objPHPExcel->getActiveSheet()->setCellValue("E$fila", $nterm);
$objPHPExcel->getActiveSheet()->mergeCells('E' . $fila . ':F' . $fila);
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($estiloCelda);
$fecha = date('Y-m-d');
$objPHPExcel->getActiveSheet()->setCellValue('I' . $fila, $thfecha);
$objPHPExcel->getActiveSheet()->getStyle('I' . $fila)->applyFromArray($estiloTh);
$objPHPExcel->getActiveSheet()->setCellValue('J' . $fila, $fecha);
$objPHPExcel->getActiveSheet()->getStyle('I' . $fila . ':J' . $fila)->applyFromArray($estiloCelda);



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
    set_time_limit(180);
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
$nom_fichero = $termcomdes . '_' .$fecha;

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
header('Content-Disposition: attachment;filename="'.$nom_fichero.'.xlsx"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objWriter->save('php://output');
exit;
?>
