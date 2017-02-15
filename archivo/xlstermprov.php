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
    $permiso = 1;
    $idflota = $flota_usu;
}

// Comprobamos si se ha seleccionado un proveedor
$select = false;
if (($idprov=="")||(($idprov=="NN"))){
    $sql_prov = "SELECT DISTINCT PROVEEDOR FROM terminales ";
    if ($idflota > 0){
        $sql_prov .= "WHERE (terminales.FLOTA = " . $idflota .") ";
    }
    $sql_prov .= "ORDER BY PROVEEDOR ASC";
    $res_prov = mysql_query($sql_prov, $link) or die(mysql_error());
    $nprov = mysql_num_rows($res_prov);
    $nomprov = $provtxt;
}
else {
    $nprov = 1;
    $select = true;
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

// Leemos el fichero de la plantilla:
$fichero = "plantillas/termprov_$idioma.xls";
$tipoFich = PHPExcel_IOFactory::identify($fichero);
$objReader = PHPExcel_IOFactory::createReader($tipoFich);
// Cargamos el fichero sólo con el número de hojas que se requieren:
// 1er Paso ---------------------------
// Construimos un vector de la forma
// hojas = array("Prov1", "Prov2", ... "ProvN"), donde N = nprov
$hojas = array();
for ($i = 1; $i <= $nprov ; $i++){
    array_push($hojas, "Prov$i");
}

// Indicamos que cargue sólo las hojas necesarias
$objReader->setLoadSheetsOnly($hojas);
// Se intenta cargar la hoja
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

//Incrementamos la memoria
ini_set('memory_limit', '128M');

// Fecha:
$fecha = date("d/m/Y");


for ($i = 0; $i < $nprov; $i++){
    if ($select){
        $valprov = $nomprov = $idprov;
    }
    else{
        $row_prov = mysql_fetch_array($res_prov);
        $valprov = $row_prov[0];
    }
    // Consulta a la base de datos - Tabla Terminales
    $sql_term = "SELECT flotas.ACRONIMO, terminales.PROVEEDOR, terminales.ISSI, terminales.TEI, terminales.NUMEROK ";
    $sql_term = $sql_term."FROM terminales,flotas WHERE terminales.FLOTA = flotas.ID AND terminales.PROVEEDOR = '$valprov' ";
    if ($idflota > 0){
        $sql_term .= " AND (terminales.FLOTA = " . $idflota .") ";
    }
    $sql_term = $sql_term."ORDER BY flotas.ACRONIMO, terminales.ISSI ASC";
    $res_term = mysql_query($sql_term) or die(mysql_error());
    $nterm = mysql_num_rows($res_term);

    // Fijamos como hoja activa la que corresponda en el bucle y fijamos el título:
    if ($i > 10){
        $objPHPExcel->createSheet();
    }
    $objPHPExcel->setActiveSheetIndex($i);
    $objPHPExcel->getActiveSheet()->setTitle($valprov);

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

    // Proveedor seleccionado
    $objPHPExcel->getActiveSheet()->setCellValue('B5', $valprov);

    // Fecha:
    $objPHPExcel->getActiveSheet()->setCellValue('B7', $fecha);

    // Datos de la flota:
    if ($nterm > 0){
        // Imprimir el número de registros:
        $objPHPExcel->getActiveSheet()->setCellValue('C9', $nterm);

        // Imprimir los resultados
        // Repetimos la cabecera de la tabla;
        $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(11, 12);

        // Imprimir los terminales
        if ($nterm > 4){
            $objPHPExcel->getActiveSheet()->insertNewRowBefore(15, ($nterm - 4));
        }

        for ($j = 0; $j < $nterm; $j++){
            $fila = 13 + $j;
            $row_term = mysql_fetch_array($res_term);
            $acronimo = $row_term["ACRONIMO"];
            $objPHPExcel->getActiveSheet()->setCellValue("A$fila",$row_term["ACRONIMO"]);
            $objPHPExcel->getActiveSheet()->setCellValue("B$fila",$row_term["PROVEEDOR"]);
            $objPHPExcel->getActiveSheet()->setCellValue("C$fila",$row_term["ISSI"]);
            $objPHPExcel->getActiveSheet()->getCell("D$fila")->setValueExplicit($row_term["TEI"], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue("E$fila",$row_term["NUMEROK"]);
            // Filas impares en gris
            if (($j % 2) == 1){
                $rango = "A$fila:E$fila";
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
}
/*
if ($nprov < 10){
    for ($i = ($nprov + 1); $i < 10; $i++){
        $objPHPExcel->setActiveSheetIndex($i);
        $objPHPExcel->removeSheetByIndex();
    }
}*/

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
$nomfichero = $terminales."_".$nomprov;
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$nomfichero.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
unset ($objPHPExcel);
exit;
?>
