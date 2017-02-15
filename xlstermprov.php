<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/xlstermprov_$idioma.php";
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
else {
    if ($idflota > 0){
        if ($flota_usu == $idflota) {
            $permiso = 1;
        }
    }
    else{
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
// Fondo Gris de fila de datos
// Estilos para la hoja:
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
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
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

// Comprobamos si se ha seleccionado un proveedor
$select = false;
$proveedores = array();
if (($idprov=="")||(($idprov=="NN"))){
    $sql_prov = "SELECT DISTINCT PROVEEDOR FROM terminales ";
    if ($idflota > 0){
        $sql_prov .= "WHERE (terminales.FLOTA = " . $idflota .") ";
    }
    $sql_prov .= "ORDER BY PROVEEDOR ASC";
    $res_prov = mysql_query($sql_prov, $link) or die(mysql_error());
    $nprov = mysql_num_rows($res_prov);
    for ($i=0; $i <$nprov ; $i++) {
        $prov = mysql_fetch_array($res_prov);
        $proveedores[] = $prov['PROVEEDOR'];
    }
}
else {
    $nprov = 1;
    $select = true;
    $proveedores[] = $idprov;
}

// Consulta a la base de datos - Tabla Flotas
if ($idflota > 0){
    $sql_flota = "SELECT * FROM flotas WHERE ID = $idflota";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de flota: ".mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota > 0){
        $flota = mysql_fetch_array($res_flota);
    }
}
else{
    $nflota = 0;
    //Incrementamos la memoria
    ini_set('memory_limit', '128M');
}

$numhoja = 0;
foreach ($proveedores as $proveedor) {
    if ($numhoja > 0){
        // Fijamos como hoja activa la siguiente:
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($numhoja);
    }

    // Fijamos el título de la Hoja:
    $tithoja = $proveedor;
    if ($tithoja == ""){
        $tithoja = $provnull;
    }
    $objPHPExcel->getActiveSheet()->setTitle($tithoja);

    // Tamaño de papel (A4) y orientación (Vertical)
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

    // Añadimos los datos de la Flota
    $objPHPExcel->getActiveSheet()->setCellValue('A4', $h2term . ' ' . $proveedor);
    $objPHPExcel->getActiveSheet()->mergeCells('A4:E4');
    $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estiloTitulo);

    // Fecha:
    $fecha = date('d-m-Y');
    $objPHPExcel->getActiveSheet()->setCellValue('A6', $thfecha);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($estiloHeader);
    $objPHPExcel->getActiveSheet()->setCellValue('B6', $fecha);
    $objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
    $objPHPExcel->getActiveSheet()->getStyle('A6:C6')->applyFromArray($estiloCelda);

    // Consulta de terminales
    // Consulta a la base de datos - Tabla Terminales
    $sql_term = "SELECT flotas.ACRONIMO, terminales.PROVEEDOR, terminales.ISSI, terminales.TEI, terminales.NUMEROK";
    $sql_term .= " FROM terminales,flotas WHERE terminales.FLOTA = flotas.ID AND terminales.PROVEEDOR = '$proveedor'";
    if ($idflota > 0){
        $sql_term .= " AND (terminales.FLOTA = " . $idflota .")";
    }
    $sql_term .= " ORDER BY flotas.ACRONIMO, terminales.ISSI ASC";
    $res_term = mysql_query($sql_term) or die(mysql_error());
    $nterm = mysql_num_rows($res_term);

    // Número de Terminales
    $objPHPExcel->getActiveSheet()->setCellValue('A8', $thnterm);
    $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloHeader);
    $objPHPExcel->getActiveSheet()->mergeCells('A8:B8');
    $objPHPExcel->getActiveSheet()->setCellValue('C8', $nterm);
    $objPHPExcel->getActiveSheet()->getStyle('A8:C8')->applyFromArray($estiloCelda);
    if ($nterm > 0) {
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'Flota');
        $objPHPExcel->getActiveSheet()->mergeCells('A10:A11');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'TERMINAL');
        $objPHPExcel->getActiveSheet()->mergeCells('B10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'ISSI');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'TEI');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'Número K');
        $objPHPExcel->getActiveSheet()->getStyle('A10:E11')->applyFromArray($estiloTh);
        $fila_inicio = 12;
        for ($i = 0; $i < $nterm; $i++){
            $fila = $fila_inicio + $i;
            $terminal = mysql_fetch_array($res_term);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $terminal['ACRONIMO']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $terminal['PROVEEDOR']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $terminal['ISSI']);
            $objPHPExcel->getActiveSheet()->getCell('D' . $fila)->setValueExplicit($terminal["TEI"], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $terminal['NUMEROK']);
            if (($i % 2) > 0){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':E' . $fila)->applyFromArray($estiloRelleno);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('A10:E' . $fila)->applyFromArray($estiloCelda);
    }
    else{
        $objPHPExcel->getActiveSheet()->setCellValue('A10', $errnoterm);
        $objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($estiloError);
        $objPHPExcel->getActiveSheet()->mergeCells('A10:E10');
    }

    // Auto-ajuste de columna
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $numhoja++;
}


// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
$fichero = "TermProv";
if ($nflota > 0){
    $fichero .= "_" . $flota['ACRONIMO'];
}

header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
header('Content-Disposition: attachment;filename="'.$fichero.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
