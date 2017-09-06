<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotas_$idioma.php";
include ($lang);

// Conexión a la BBDD:
require_once 'conectabbdd.php';

// Obtención del usuario
require_once 'autenticacion.php';

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
$objPHPExcel->getProperties()->setKeywords("Oficina COMDES Organizaciones");
$objPHPExcel->getProperties()->setCategory("Organizaciones COMDES");

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
$estiloCentro = array(
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
);
$estiloError = array(
    'font' => array('bold' => true, 'color' => array('argb' => 'FFFF0000')),
);

// Fijamos como hoja activa la primera (Datos de la Flota):
$objPHPExcel->setActiveSheetIndex(0);

// Permisos de Usuario:
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}

if ($permiso > 1){
    // Consultas a la BBDD
    require_once 'sql/flotas_exportar.php';

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&$pgtxt &P de &N");

    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle($nom_fichero . '_COMDES');

    // Título de la Hoja
    $objPHPExcel->getActiveSheet()->setCellValue('A1', $h1);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');

    // Criterios de selección:
    $fila = 4;
    $ncrit = 0;
    $ncol = 0;
    $colcrit = array('B', 'F');
    $colfusion = array('D', 'J');
    if ((isset($_POST['idorg']))&&($_POST['idorg'] > 0)){
        $objPHPExcel->getActiveSheet()->setCellValue($colcrit[$ncol] . $fila, $thorg . ': ' . $selorg['ORGANIZACION']);
        $objPHPExcel->getActiveSheet()->getStyle($colcrit[$ncol] . $fila)->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells($colcrit[$ncol] . $fila . ':' . $colfusion[$ncol] . $fila);
        $ncrit++;
        $ncol++;
    }
    if ($idflota > 0){
        $objPHPExcel->getActiveSheet()->setCellValue($colcrit[$ncol] . $fila, 'Flota: ' . $selflota['FLOTA']);
        $objPHPExcel->getActiveSheet()->getStyle($colcrit[$ncol] . $fila)->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells($colcrit[$ncol] . $fila . ':' . $colfusion[$ncol] . $fila);
        $ncrit++;
        $ncol++;
    }
    if ((isset($_POST['formcont']))&&($_POST['formcont'] != "00")){
        if ($ncrit > 1){
            $ncol = 0;
            $fila++;
        }
        $valcont = array('SI' => 'Sí', 'NO' => 'NO');
        $objPHPExcel->getActiveSheet()->setCellValue($colcrit[$ncol] . $fila, $txtcontof . ': ' . $valcont[$_POST['formcont']]);
        $objPHPExcel->getActiveSheet()->getStyle($colcrit[$ncol] . $fila)->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells($colcrit[$ncol] . $fila . ':' . $colfusion[$ncol] . $fila);
        $ncrit++;
        $ncol++;
    }
    if ((isset($_POST['ambito']))&&($_POST['ambito'] != "00")){
        if ($ncrit > 1){
            $ncol = 0;
            $fila++;
        }
        $ambitos = array('NADA' => $txtambnada, 'LOC' => $txtambloc, 'PROV' => $txtambprov, 'AUT' => $txtambaut);
        $objPHPExcel->getActiveSheet()->setCellValue($colcrit[$ncol] . $fila, $txtambito . ': ' . $ambitos[$_POST['ambito']]);
        $objPHPExcel->getActiveSheet()->getStyle($colcrit[$ncol] . $fila)->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells($colcrit[$ncol] . $fila . ':' . $colfusion[$ncol] . $fila);
        $ncrit++;
        $ncol++;
    }

    $fila = 3;
    if ($ncrit > 0){
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $criterios);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':' . 'E' . $fila);
        $fila = $fila + 3;
        if ($ncrit > 2){
            $fila++;
        }
    }

    // Número de las Flotas:
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $h4res . ': ' . $nflotas . ' ' . $nom_fichero);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloCriterio);
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':' . 'E' . $fila);
    $fila = $fila + 2;

    // Cabecera:
    $fila_initabla = $fila;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, 'ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $thorg);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, 'Flota');
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $thacro);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $thencripta);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $thterm);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $fila, $thtbase);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $fila, $thtmov);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $fila, $thtport);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $fila, $thtdesp);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':' . 'J' . $fila)->applyFromArray($estiloTh);
    $fila++;

    // Datos de flotas:
    $relleno = false;
    foreach ($flotas as $flota) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $flota['ID']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $flota['ORGANIZACION']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $flota['FLOTA']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $flota['ACRONIMO']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $flota['ENCRIPTACION']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $flota['NTERM']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $fila, $flota['NBASE']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $fila, $flota['NMOV']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $fila, $flota['NPORT']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $fila, $flota['NDESP']);
        if  ($relleno){
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':' . 'J' . $fila)->applyFromArray($estiloRelleno);
        }
        $relleno = !($relleno);
        $fila++;
    }
    $fila_fintabla = $fila - 1;
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_initabla . ':' . 'J' . $fila_fintabla)->applyFromArray($estiloCelda);
    $fila++;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $thtotales);
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':' . 'E' . $fila);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $totterm[0]);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $fila, $totterm[1]);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $fila, $totterm[2]);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $fila, $totterm[3]);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $fila, $totterm[4]);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':' . 'J' . $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':' . 'J' . $fila)->applyFromArray($estiloCelda);

    // Ajustamos ancho de columnas
    $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
    $maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
    for ($i = 0; $i < $maxcol; $i++){
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
    }


}
else{
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1perm . ": " . $errnoperm);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloError);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
}

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
$fichero = $nom_fichero .'_COMDES.xlsx';
header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
header('Content-Disposition: attachment;filename="' . $fichero . '"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
