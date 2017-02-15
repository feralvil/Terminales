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
$fichero = "plantillas/Plantilla_Flota-$idioma.xlsx";
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

// Primera hoja: Datos de la Flota y contactos:
$fecha = date("d/m/Y");
$objPHPExcel->getActiveSheet()->setCellValue('M4', $fecha);
// Datos de la flota:
if ($nflota > 0){
    $row_flota = mysql_fetch_array($res_flota);
    $idorg = $row_flota['ORGANIZACION'];
    $sql_organizacion = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
    $res_organizacion = mysql_query($sql_organizacion) or die("Error en la consulta de flota: ".mysql_error());
    $norganiza = mysql_num_rows($res_organizacion);
    if ($norganiza > 0){
        $organizacion = mysql_fetch_array($res_organizacion);
        // Datos de la Organización:
        $objPHPExcel->getActiveSheet()->setCellValue('C6', $organizacion["ORGANIZACION"]);

        // Municipio de la Organización:
        $ineorg = $organizacion["INE"];
        $sql_mun = "SELECT * FROM municipios WHERE INE='$ineorg'";
        $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio de la Organización" . mysql_error());
        $nmun = mysql_num_rows($res_mun);
        if ($nmun > 0) {
            $row_mun = mysql_fetch_array($res_mun);
            $objPHPExcel->getActiveSheet()->setCellValue('C12', $organizacion["CP"] . ' - ' . $row_mun["MUNICIPIO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J12', $row_mun["PROVINCIA"]);
        }

        // Responsable de la Organizacion:
        if ($organizacion["RESPONSABLE"] != 0){
            // Consulta a la base de datos - Tabla Contactos
            $idc = $organizacion["RESPONSABLE"];
            $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idc";
            $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0){
                $resporg = mysql_fetch_array($res_contacto);
                $objPHPExcel->getActiveSheet()->setCellValue('C9', $resporg["NOMBRE"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J9', $resporg["NIF"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C10', $resporg["CARGO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C11', $organizacion["DOMICILIO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C13', $resporg["TELEFONO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I13', $resporg["MAIL"]);
            }
        }
    }

    // Añadimos los datos de la Flota
    $objPHPExcel->getActiveSheet()->setCellValue('A2', $row_flota["FLOTA"]);
    
    // Contactos de la Flota:
    // Responsable de la Flota:
    $sql_contresp = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'RESPONSABLE')";
    $res_contresp = mysql_query($sql_contresp) or die(mysql_error());
    $ncontresp = mysql_num_rows($res_contresp);
    if ($ncontresp > 0){
        $contresp = mysql_fetch_array($res_contresp);
        $idrespflo = $contresp['CONTACTO_ID'];
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idrespflo";
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $respflo = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('C17', $respflo["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J17', $respflo["NIF"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C18', $respflo["CARGO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C19', $row_flota["DOMICILIO"]);
            // Consulta a la base de datos - Tabla Municipios
            $ineflo = $row_flota["INE"];
            if ($ineflo <> $ineorg){
                $sql_mun = "SELECT * FROM municipios WHERE INE='$ineflo'";
                $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio de la Organización" . mysql_error());
                $nmun = mysql_num_rows($res_mun);
                if ($nmun > 0) {
                    $row_mun = mysql_fetch_array($res_mun);
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C20', $organizacion["CP"] . ' - ' . $row_mun["MUNICIPIO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J20', $row_mun["PROVINCIA"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C21', $respflo["TELEFONO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I21', $respflo["MAIL"]);
        }
    }

    // Cntactos Operativos:
    $sql_contop = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'OPERATIVO')";
    $res_contop = mysql_query($sql_contop) or die(mysql_error());
    $ncontop = mysql_num_rows($res_contop);
    for ($i = 0 ; $i < $ncontop; $i++){
        $fila = 26 + $i;
        $contop = mysql_fetch_array($res_contop);
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = " . $contop['CONTACTO_ID'];
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $contacto = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$fila, $contacto["NIF"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $contacto["CARGO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$fila, $contacto["MAIL"]);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$fila, $contacto["TELEFONO"]);
        }
        if (($i % 2) == 1){
            $rango = "A$fila:M$fila";
            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilgris);
        }
    }

    // Contactos Técnicos:
    $sql_conttec = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'TECNICO')";
    $res_conttec = mysql_query($sql_conttec) or die(mysql_error());
    $nconttec = mysql_num_rows($res_conttec);
    for ($i = 0 ; $i < $nconttec; $i++){
        $fila = 33 + $i;
        $conttec = mysql_fetch_array($res_conttec);
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = " . $conttec['CONTACTO_ID'];
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $contacto = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$fila, $contacto["NIF"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $contacto["CARGO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$fila, $contacto["MAIL"]);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$fila, $contacto["TELEFONO"]);
        }
        if (($i % 2) == 1){
            $rango = "A$fila:M$fila";
            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilgris);
        }
    }

    // Contactos 24x7:
    $sql_cont24h = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'CONT24H')";
    $res_cont24h = mysql_query($sql_cont24h) or die(mysql_error());
    $ncont24h = mysql_num_rows($res_cont24h);
    for ($i = 0 ; $i < $ncont24h; $i++){
        $fila = 40 + $i;
        $cont24h = mysql_fetch_array($res_cont24h);
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = " . $cont24h['CONTACTO_ID'];
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $contacto = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $contacto["MAIL"]);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$fila, $contacto["TELEFONO"]);
        }
        if (($i % 2) == 1){
            $rango = "A$fila:M$fila";
            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilgris);
        }
    }

    /* Datos de Terminales */
    // Fijamos como hoja activa la segunda (Terminales):
    $objPHPExcel->setActiveSheetIndex(1);

    // Añadimos el Logo:
    //$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

    // Añadimos la Fecha:
    $objPHPExcel->getActiveSheet()->setCellValue('O4', $fecha);

    // Añadimos el Nombre de Flota:
    $objPHPExcel->getActiveSheet()->setCellValue('C6', $row_flota["FLOTA"]);

    // Imprimir el número de registros:
    $objPHPExcel->getActiveSheet()->setCellValue('D8', $nterm);

    // Añadimos el Rango de ISSI de la Flota:
    $objPHPExcel->getActiveSheet()->setCellValue('I8', $row_flota["RANGO"]);

    // Imprimir los resultados
    // Repetimos la cabecera de la tabla;
    $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(10, 11);

    // Imprimir los terminales
    if ($nterm > 4){
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(14, ($nterm - 4));
    }
    if($nterm > 200){
        ini_set('memory_limit', "64M");
        set_time_limit(120);
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
$fichero = "Flota_".$row_flota["ACRONIMO"];

header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
header('Content-Disposition: attachment;filename="'.$fichero.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
