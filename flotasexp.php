<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotas_$idioma.php";
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

// Incrementamos el tamaño de la memoria disponible:
ini_set("memory_limit","64M");

// Clases para generar el Excel
date_default_timezone_set('Europe/Madrid');
/** PHPExcel */
require_once 'Classes/PHPExcel.php';

// Consulta a la base de datos
$sql = "SELECT flotas.ID, organizaciones.ORGANIZACION, flotas.FLOTA, ";
$sql .= "flotas.ACRONIMO, flotas.ENCRIPTACION FROM flotas, organizaciones";
$sql .= " WHERE (flotas.ORGANIZACION = organizaciones.ID)";
if (($flota!='')&&($flota!="00")) {
    $sql.=" AND (flotas.ID='$flota')";
    $sql_flota = "SELECT * FROM flotas WHERE ID='$flota'";
    $res_flota=mysql_db_query($base_datos,$sql_flota) or die(mysql_error());
    $row_flota=mysql_fetch_array($res_flota);
    $flota_txt = $row_flota["FLOTA"];
}
if (($activa!='')&&($activa!="00")) {
    $sql.=" AND (flotas.ACTIVO='$activa')";
    if ($activa=="SI"){
        $activa = "Sí";
    }
    else{
        $activa = "No";
    }
}
$sql_flotas = $sql." ORDER BY organizaciones.ORGANIZACION ASC, flotas.FLOTA ASC";
$res_flotas = mysql_query($sql_flotas) or die(mysql_error());
$nflotas = mysql_num_rows($res_flotas);
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
$objPHPExcel->getProperties()->setKeywords("Oficina COMDES Flotas");
$objPHPExcel->getProperties()->setCategory("Flotas COMDES");

// Fijamos los estilos generales de la hoja:
$tamfont = 10;
if ($formato == "pdf"){
    $tamfont = 8;
}
$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize($tamfont);

// Fijamos como hoja activa la primera y fijamos el título:
$objPHPExcel->setActiveSheetIndex(0);

$cabecera = $campospdf;
$anchos = array(5, 40, 40, 15, 15, 20, 15, 15 , 15, 15);
if ($formato == "pdf"){
    $anchos = array(5, 30, 30, 20, 15, 20, 15, 15 , 15, 15);
}
$nomcolumna = array("A","B","C","D","E","F","G","H","I", "J");

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

//Cabecera de Página
$objDrawing = new PHPExcel_Worksheet_HeaderFooterDrawing();
$objDrawing->setName("Logo COMDES");
$objDrawing->setPath("imagenes/comdes2.png");
$objPHPExcel->getActiveSheet()->getHeaderFooter()->addImage($objDrawing, PHPExcel_Worksheet_HeaderFooter::IMAGE_FOOTER_LEFT);

// Pie de Página
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

// Fijamos los anchos de columnas:
for ($i = 0; $i < count($anchos); $i++){
    $objPHPExcel->getActiveSheet()->getColumnDimension($nomcolumna[$i])->setWidth($anchos[$i]);
}

// Primera hoja: Título de la tabla de datos
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
$objPHPExcel->getActiveSheet()->setCellValue('A1', $h1);
$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');


$fila = 3;
if ((($flota!='')&&($flota!="00"))||(($activa!='')&&($activa!="00"))) {
    $objPHPExcel->getActiveSheet()->setCellValue('A3',$criterios);
    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$fila)->applyFromArray($estiloCriterio);
    $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');
    $fila++;
    if (($flota!='')&&($flota!="00")) {
        $objPHPExcel->getActiveSheet()->setCellValue("B$fila","- Flota: ");
        $objPHPExcel->getActiveSheet()->getStyle("B$fila")->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->setCellValue("B$fila",$flota_txt);
        $objPHPExcel->getActiveSheet()->mergeCells("C$fila:F$fila");
        $fila++;
    }
    if (($flota!='')&&($flota!="00")) {
        $objPHPExcel->getActiveSheet()->setCellValue("B$fila","- Activa: ");
        $objPHPExcel->getActiveSheet()->getStyle("B$fila")->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $activa);
        $objPHPExcel->getActiveSheet()->mergeCells("C$fila:F$fila");
        $fila++;
    }
}
$fila++;

// Imprimir el número de registros:
$objPHPExcel->getActiveSheet()->setCellValue("A$fila","- $nreg: $nflotas");
$objPHPExcel->getActiveSheet()->getStyle("A$fila")->applyFromArray($estiloCriterio);
$objPHPExcel->getActiveSheet()->mergeCells("A$fila:C$fila");
$fila = $fila + 2;

// Imprimir los resultados
// Fila de Inicio de la tabla de terminales
$fila_inicio = $fila;
// Repetimos la cabecera de la tabla;
$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($fila_inicio, $fila_inicio);
$objPHPExcel->getActiveSheet()->freezePaneByColumnAndRow(0, $fila_inicio+1);

// Bordes de la tabla de datos
$fila_fin = $fila_inicio + $nflotas;
$rango = "A$fila_inicio:J$fila_fin";
$objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloCelda);

// Cabecera de la tabla
for($i = 0; $i < count($cabecera); $i++) {
    $celda = $nomcolumna[$i].$fila;
    $objPHPExcel->getActiveSheet()->setCellValue($celda,$cabecera[$i]);
}
$objPHPExcel->getActiveSheet()->getStyle("A$fila:J$fila")->applyFromArray($estiloTh);
$fila++;
$tterm = array (0,0,0,0,0);

// Imprimir las flotas
for ($j = 0; $j < $nflotas; $j++){
    $row_flota = mysql_fetch_array($res_flotas);
    $tipos = array("F", "M%", "P%", "D");
    $nterm = array (0,0,0,0);
    $sql_term = "SELECT * FROM terminales WHERE FLOTA='$row_flota[0]'";
    $res_term = mysql_query($sql_term) or die ("Error en la consulta de Terminales".mysql_error());
    $tot_term = mysql_num_rows($res_term);
    $row_flota[5] = number_format($tot_term,0,',','.');
    $tterm[0] += $tot_term;
    for($i=0; $i< count($tipos);$i++){
        $sql_term = "SELECT * FROM terminales WHERE FLOTA='$row_flota[0]' AND TIPO LIKE '".$tipos[$i]."'";
        $res_term = mysql_db_query($base_datos,$sql_term) or die ("Error en la consulta de ".$cabecera[$j].": ".mysql_error());
        $nterm[$i] = mysql_num_rows($res_term);
        $row_flota[6 + $i] = number_format($nterm[$i],0,'.',',');
        $tterm[$i+1] += $nterm[$i];
    }
    for ($i = 0; $i < count($cabecera); $i++){
        $celda = $nomcolumna[$i].$fila ;
        $objPHPExcel->getActiveSheet()->setCellValue($celda, $row_flota[$i]);
    }
    if (($j % 2) == 1){
        $rango = "A$fila:J$fila";
        $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloRelleno);
    }
    $fila++;
}
$fila++;

// Fila de recuento de terminales
$objPHPExcel->getActiveSheet()->setCellValue("A$fila",$totales);
$objPHPExcel->getActiveSheet()->getStyle("A$fila")->applyFromArray($estiloTh);
$objPHPExcel->getActiveSheet()->mergeCells("A$fila:E$fila");
for ($i = 0; $i < count($tterm); $i++){
    //$tterm[$i] = number_format($tterm[$i],0,',','.');
    $celda = $nomcolumna[$i + 5].$fila;
    $objPHPExcel->getActiveSheet()->setCellValue($celda, $tterm[$i]);
}
$objPHPExcel->getActiveSheet()->getStyle("A$fila:J$fila")->applyFromArray($estiloCelda);

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);// Fijamos como hoja activa la primera y fijamos el título:
$objPHPExcel->getActiveSheet()->setTitle($flotascomdes);

if ($formato == "xls"){
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$nom_fichero.'.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
}
elseif ($formato == "pdf"){
    // Redirect output to a client’s web browser (PDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="'.$nom_fichero.'.pdf"');
    readfile($nom_fichero.'.pdf');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
}
$objWriter->save('php://output');
exit;
?>
