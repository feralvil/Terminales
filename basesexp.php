<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/bases_$idioma.php";
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
elseif ($flota_usu > 0){
    $permiso = 1;
}

// Clases para generar el Excel
date_default_timezone_set('Europe/Madrid');
/** PHPExcel */
require_once 'Classes/PHPExcel.php';

// Consulta a la base de datos
//datos de la tabla Bases
$sql_bases = "SELECT terminales.ISSI, flotas.FLOTA, municipios.MUNICIPIO, bases.TERMINAL, bases.FLOTA AS 'IDFLOTA' FROM bases, terminales, flotas, municipios ";
$sql_bases = $sql_bases . "WHERE terminales.ID = bases.TERMINAL AND flotas.ID = bases.FLOTA AND municipios.ine = bases.MUNICIPIO ";
$sql_bases = $sql_bases . " ORDER BY flotas.FLOTA ASC";
$res_bases = mysql_query($sql_bases) or die(mysql_error());
$nbases = mysql_num_rows($res_bases);
$fila = 2;

# Creamos el objeto Excel
$objPHPExcel = new PHPExcel();
$locale = 'Es';
$validLocale = PHPExcel_Settings::setLocale($locale);

// Leemos el fichero de la plantilla:
$fichero = "plantillas/bases_$idioma.xls";
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
$objPHPExcel->getProperties()->setKeywords("Oficina COMDES Flotas Bases");
$objPHPExcel->getProperties()->setCategory("Bases COMDES");

// Fijamos los estilos generales de la hoja:
$tamfont = 10;
if ($formato == "pdf"){
    $tamfont = 8;
}
$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($tamfont);

// Fijamos como hoja activa la primera y fijamos el título:
$objPHPExcel->setActiveSheetIndex(0);

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

// En PDF no se imprime la rejilla:
if ($formato == 'pdf'){
    $objPHPExcel->getActiveSheet()->setShowGridlines(false);
}

// Añadimos el Logo:
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName("Logo");
$objDrawing->setCoordinates('A1');
$objDrawing->setPath('./imagenes/comdes.png');
$objDrawing->setHeight(50);
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

// Pie de Página
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

// Imprimir el número de registros:
$objPHPExcel->getActiveSheet()->setCellValue("B4","$nbases");

// Imprimir los resultados
// Fila de Inicio de la tabla de Bases
$fila = 7;
// Repetimos la cabecera de la tabla;
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($fila, $fila);
$objPHPExcel->getActiveSheet()->freezePaneByColumnAndRow(0, 8);

// Imprimir las bases
if ($nbases > 4){
    $objPHPExcel->getActiveSheet()->insertNewRowBefore(8, ($nbases - 4));
}
elseif ($nbases < 4){
    $objPHPExcel->getActiveSheet()->removeRow(8, 4 - $nbases);
}

for ($j = 0; $j < $nbases; $j++){
    $fila = 7 + $j;
    $row_base = mysql_fetch_array($res_bases);
    $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $row_base["MUNICIPIO"]);
    $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $row_base["FLOTA"]);
    $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $row_base["ISSI"]);
    if (($j % 2) == 1){
        $rango = "A$fila:C$fila";
        $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilgris);
    }
}

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

if ($formato == "xls"){
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Bases_COMDES.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
}
elseif ($formato == "pdf"){
    // Redirect output to a client’s web browser (PDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="Bases_COMDES.pdf"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
}
$objWriter->save('php://output');
exit;
?>
