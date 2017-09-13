<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotasdetexp_$idioma.php";
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
$objPHPExcel->getProperties()->setKeywords("Oficina COMDES Flota terminales");
$objPHPExcel->getProperties()->setCategory("Flota COMDES");

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

// Fijamos los estilos generales de la hoja:
$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

// Fijamos como hoja activa la primera (Datos de la Flota):
$objPHPExcel->setActiveSheetIndex(0);

// Permisos de Usuario:
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    if (isset($_POST['idflota'])){
        if ($flota_usu == $_POST['idflota']){
            $permiso = 1;
        }
    }
}

if ($permiso > 0){
    // Consultas a la BBDD
    require_once 'sql/flotas_detexportar.php';

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&$txtpagina &P de &N");

    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle('(1) DATOS');

    if ($nflota > 0){
        // Incrementamos la memoria:
        ini_set('memory_limit', "256M");

        // Hoja de Contactos
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'FLOTA ' . $flota["FLOTA"]);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:M1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);

        // Fecha:
        $fecha = date('d-m-Y');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', $thfecha);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $fecha);
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($estiloCelda);

        // Datos de organización:
        $objPHPExcel->getActiveSheet()->setCellValue('A5', $thorganiza);
        $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
        $objPHPExcel->getActiveSheet()->mergeCells('C5:M5');
        $objPHPExcel->getActiveSheet()->getStyle('A5:M5')->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->setCellValue('A7', $txtresporg);
        $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->getStyle('A8:L12')->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->setCellValue('A8', $thnombre);
        $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A8:B8');
        $objPHPExcel->getActiveSheet()->mergeCells('C8:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('I8', 'NIF');
        $objPHPExcel->getActiveSheet()->getStyle('I8')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('J8:L8');
        $objPHPExcel->getActiveSheet()->setCellValue('A9', $thcargo);
        $objPHPExcel->getActiveSheet()->getStyle('A9')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A9:B9');
        $objPHPExcel->getActiveSheet()->mergeCells('C9:L9');
        $objPHPExcel->getActiveSheet()->setCellValue('A10', $thdomicilio);
        $objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A10:B10');
        $objPHPExcel->getActiveSheet()->mergeCells('C10:L10');
        $objPHPExcel->getActiveSheet()->setCellValue('A11', 'CP-' . $thciudad);
        $objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A11:B11');
        $objPHPExcel->getActiveSheet()->mergeCells('C11:G11');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', $thprovincia);
        $objPHPExcel->getActiveSheet()->getStyle('H11')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('H11:I11');
        $objPHPExcel->getActiveSheet()->mergeCells('J11:L11');
        $objPHPExcel->getActiveSheet()->setCellValue('A12', $thtelef);
        $objPHPExcel->getActiveSheet()->getStyle('A12')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A12:B12');
        $objPHPExcel->getActiveSheet()->mergeCells('C12:F12');
        $objPHPExcel->getActiveSheet()->setCellValue('G12', $thmail);
        $objPHPExcel->getActiveSheet()->getStyle('G12')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('G12:H12');
        $objPHPExcel->getActiveSheet()->mergeCells('I12:L12');
        if ($norganiza > 0){
             $objPHPExcel->getActiveSheet()->setCellValue('C5', $organiza["ORGANIZACION"]);
             // Contactos de responsable
             if (count($contactos['RESPORG']) > 0){
                 $contacto = $contactos['RESPORG'][0];
                 $objPHPExcel->getActiveSheet()->setCellValue('C8', $contacto["NOMBRE"]);
                 $objPHPExcel->getActiveSheet()->setCellValue('J8', $contacto["NIF"]);
                 $objPHPExcel->getActiveSheet()->setCellValue('C9', $contacto["CARGO"]);
                 $objPHPExcel->getActiveSheet()->setCellValue('C12', $contacto["TELEFONO"]);
                 $objPHPExcel->getActiveSheet()->setCellValue('I12', $contacto["MAIL"]);
             }
             // Datos de organización
             $objPHPExcel->getActiveSheet()->setCellValue('C10', $organiza["DOMICILIO"]);
             $objPHPExcel->getActiveSheet()->setCellValue('C11', $organiza["CP"] . ' - ' . $munorg["MUNICIPIO"]);
             $objPHPExcel->getActiveSheet()->setCellValue('J11', $munorg["PROVINCIA"]);
        }

        // Responsable de la Flota:
        $objPHPExcel->getActiveSheet()->setCellValue('A14', $txtrespflota);
        $objPHPExcel->getActiveSheet()->getStyle('A14')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->getStyle('A15:L19')->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->setCellValue('A15', $thnombre);
        $objPHPExcel->getActiveSheet()->getStyle('A15')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A15:B15');
        $objPHPExcel->getActiveSheet()->mergeCells('C15:H15');
        $objPHPExcel->getActiveSheet()->setCellValue('I15', 'NIF');
        $objPHPExcel->getActiveSheet()->getStyle('I15')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('J15:L15');
        $objPHPExcel->getActiveSheet()->setCellValue('A16', $thcargo);
        $objPHPExcel->getActiveSheet()->getStyle('A16')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A16:B16');
        $objPHPExcel->getActiveSheet()->mergeCells('C16:L16');
        $objPHPExcel->getActiveSheet()->setCellValue('A17', $thdomicilio);
        $objPHPExcel->getActiveSheet()->getStyle('A17')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A17:B17');
        $objPHPExcel->getActiveSheet()->mergeCells('C17:L17');
        $objPHPExcel->getActiveSheet()->setCellValue('A18', 'CP-' . $thciudad);
        $objPHPExcel->getActiveSheet()->getStyle('A18')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A18:B18');
        $objPHPExcel->getActiveSheet()->mergeCells('C18:G18');
        $objPHPExcel->getActiveSheet()->setCellValue('H18', $thprovincia);
        $objPHPExcel->getActiveSheet()->getStyle('H18')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('H18:I18');
        $objPHPExcel->getActiveSheet()->mergeCells('J18:L18');
        $objPHPExcel->getActiveSheet()->setCellValue('A19', $thtelef);
        $objPHPExcel->getActiveSheet()->getStyle('A19')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A19:B19');
        $objPHPExcel->getActiveSheet()->mergeCells('C19:F19');
        $objPHPExcel->getActiveSheet()->setCellValue('G19', $thmail);
        $objPHPExcel->getActiveSheet()->getStyle('G19')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('G19:H19');
        $objPHPExcel->getActiveSheet()->mergeCells('I19:L19');
        if (count($contactos['RESPONSABLE']) > 0){
            $contacto = $contactos['RESPONSABLE'][0];
            $objPHPExcel->getActiveSheet()->setCellValue('C15', $contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('J15', $contacto["NIF"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C16', $contacto["CARGO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C19', $contacto["TELEFONO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('I19', $contacto["MAIL"]);
        }
        // Datos de Flota
        $objPHPExcel->getActiveSheet()->setCellValue('C17', $flota["DOMICILIO"]);
        $objPHPExcel->getActiveSheet()->setCellValue('C18', $flota["CP"] . ' - ' . $municipio["MUNICIPIO"]);
        $objPHPExcel->getActiveSheet()->setCellValue('J18', $municipio["PROVINCIA"]);

        // Contactos:
        $indices = array('OPERATIVO', 'TECNICO', 'CONT24H');
        $h3 = array('OPERATIVO' => $txtoperativo, 'TECNICO' => $txttecnico, 'CONT24H' => $txt24h);
        $errores = array('OPERATIVO' => $errnoop,'TECNICO' => $errnotec, 'CONT24H' => $errno24h);
        $fila = 21;
        foreach ($indices as $indice) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $h3[$indice]);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloCriterio);
            $fila++;
            if ($indice != "CONT24H"){
                $fila_ini = $fila;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $thnombre);
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':C' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, 'NIF');
                $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila . ':E' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $thcargo);
                $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila . ':H' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $fila, $thmail);
                $objPHPExcel->getActiveSheet()->mergeCells('I' . $fila . ':K' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $fila, $thtelef);
                $objPHPExcel->getActiveSheet()->mergeCells('L' . $fila . ':M' . $fila);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($estiloTh);
                $fila++;
                $relleno = false;
                foreach ($contactos[$indice] as $contacto) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $contacto['NOMBRE']);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':C' . $fila);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $contacto['NIF']);
                    $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila . ':E' . $fila);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $contacto['CARGO']);
                    $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila . ':H' . $fila);
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $fila, $contacto['MAIL']);
                    $objPHPExcel->getActiveSheet()->mergeCells('I' . $fila . ':K' . $fila);
                    $objPHPExcel->getActiveSheet()->setCellValue('L' . $fila, $contacto['TELEFONO']);
                    $objPHPExcel->getActiveSheet()->mergeCells('L' . $fila . ':M' . $fila);
                    if ($relleno){
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($estiloRelleno);
                    }
                    $fila++;
                    $relleno = !($relleno);
                }
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_ini . ':M' . ($fila - 1))->applyFromArray($estiloCelda);
            }
            else{
                $fila_ini = $fila;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $thnombre);
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':E' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $thmail);
                $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila . ':J' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $fila, $thtelef);
                $objPHPExcel->getActiveSheet()->mergeCells('K' . $fila . ':M' . $fila);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($estiloTh);
                $fila++;
                $relleno = false;
                foreach ($contactos[$indice] as $contacto) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $contacto['NOMBRE']);
                    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':E' . $fila);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $contacto['MAIL']);
                    $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila . ':J' . $fila);
                    $objPHPExcel->getActiveSheet()->setCellValue('K' . $fila, $contacto['TELEFONO']);
                    $objPHPExcel->getActiveSheet()->mergeCells('K' . $fila . ':M' . $fila);
                    if ($relleno){
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($estiloRelleno);
                    }
                    $fila++;
                    $relleno = !($relleno);
                }
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_ini . ':M' . ($fila - 1))->applyFromArray($estiloCelda);
            }
            $fila++;
        }

        // Hoja de Terminales:
        // Fijamos como hoja activa la segunda (Terminales):
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);

        // Fijamos el título de la Hoja:
        $objPHPExcel->getActiveSheet()->setTitle("(2) ISSI");

        // Tamaño de papel (A4) y orientación (Apaisado)
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Pie de Página
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&$txtpagina &P de &N");

        // Título de la Página
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $h2termflota . ' ' . $flota["FLOTA"]);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:V1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);

        // Fecha:
        $objPHPExcel->getActiveSheet()->setCellValue('A3', $thfecha);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $fecha);
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($estiloCelda);

        // Imprimir el número de terminales:
        $objPHPExcel->getActiveSheet()->setCellValue('A5', $h3nterm);
        $objPHPExcel->getActiveSheet()->mergeCells('A5:C5');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValue('D5', $ntermflota);
        $objPHPExcel->getActiveSheet()->getStyle('A5:D5')->applyFromArray($estiloCelda);

        // Añadimos el Rango de ISSI de la Flota:
        $objPHPExcel->getActiveSheet()->setCellValue('G5', $h3rangoterm);
        $objPHPExcel->getActiveSheet()->mergeCells('G5:I5');
        $objPHPExcel->getActiveSheet()->getStyle('G5')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValue('J5', $flota["RANGO"]);
        $objPHPExcel->getActiveSheet()->mergeCells('J5:L5');
        $objPHPExcel->getActiveSheet()->getStyle('G5:L5')->applyFromArray($estiloCelda);

        // Tabla de Terminales
        $fila_inicio = 7;
        $fila = $fila_inicio;
        // Ajustamos los estilos para los terminales
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(8);
        // Imprimimos la cabecera:
        $objPHPExcel->getActiveSheet()->setCellValue('A7', 'Nº');
        $objPHPExcel->getActiveSheet()->mergeCells('A7:A8');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', 'TERMINAL');
        $objPHPExcel->getActiveSheet()->mergeCells('B7:G7');
        $objPHPExcel->getActiveSheet()->setCellValue('H7', 'ISSI');
        $objPHPExcel->getActiveSheet()->mergeCells('H7:H8');
        $objPHPExcel->getActiveSheet()->setCellValue('I7', 'TEI');
        $objPHPExcel->getActiveSheet()->mergeCells('I7:I8');
        $objPHPExcel->getActiveSheet()->setCellValue('J7', $thnserie);
        $objPHPExcel->getActiveSheet()->mergeCells('J7:J8');
        $objPHPExcel->getActiveSheet()->setCellValue('K7', $thmnemo);
        $objPHPExcel->getActiveSheet()->mergeCells('K7:K8');
        $objPHPExcel->getActiveSheet()->setCellValue('L7', 'Carpeta');
        $objPHPExcel->getActiveSheet()->mergeCells('L7:L8');
        $objPHPExcel->getActiveSheet()->setCellValue('M7', $thllam);
        $objPHPExcel->getActiveSheet()->mergeCells('M7:N7');
        $objPHPExcel->getActiveSheet()->setCellValue('O7', 'Alta DOTS');
        $objPHPExcel->getActiveSheet()->mergeCells('O7:O8');
        $objPHPExcel->getActiveSheet()->setCellValue('P7', $thaut);
        $objPHPExcel->getActiveSheet()->mergeCells('P7:P8');
        $objPHPExcel->getActiveSheet()->setCellValue('Q7', $thencripta);
        $objPHPExcel->getActiveSheet()->mergeCells('Q7:Q8');
        $objPHPExcel->getActiveSheet()->setCellValue('R7', $thdirip);
        $objPHPExcel->getActiveSheet()->mergeCells('R7:R8');
        $objPHPExcel->getActiveSheet()->setCellValue('S7', $thversion);
        $objPHPExcel->getActiveSheet()->mergeCells('S7:S8');
        $objPHPExcel->getActiveSheet()->setCellValue('T7', $thalta);
        $objPHPExcel->getActiveSheet()->mergeCells('T7:T8');
        $objPHPExcel->getActiveSheet()->setCellValue('U7', $thobserv);
        $objPHPExcel->getActiveSheet()->mergeCells('U7:U8');
        $objPHPExcel->getActiveSheet()->setCellValue('V7', 'Número K');
        $objPHPExcel->getActiveSheet()->mergeCells('V7:V8');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', 'Marca');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', $thmodelo);
        $objPHPExcel->getActiveSheet()->setCellValue('D8', $thtipo);
        $objPHPExcel->getActiveSheet()->setCellValue('E8', $thproveedor);
        $objPHPExcel->getActiveSheet()->setCellValue('F8', 'Cod. HW');
        $objPHPExcel->getActiveSheet()->setCellValue('G8', 'A.M.');
        $objPHPExcel->getActiveSheet()->setCellValue('M8', 'D');
        $objPHPExcel->getActiveSheet()->setCellValue('N8', 'S-D');
        $objPHPExcel->getActiveSheet()->getStyle('A7:V8')->applyFromArray($estiloTh);
        // Repetimos la cabecera de la tabla;
        $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7, 8);

        // Imprimimos los terminales:
        $fila = 9;
        foreach ($terminales as $terminal) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $terminal['ID']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $terminal["MARCA"]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $terminal["MODELO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $terminal["TIPO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $terminal["PROVEEDOR"]);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $terminal["CODIGOHW"]);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $fila, $terminal["AM"]);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $fila, $terminal["ISSI"]);
            $objPHPExcel->getActiveSheet()->getCell('I' . $fila)->setValueExplicit($terminal["TEI"], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $fila, $terminal["NSERIE"]);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $fila, $terminal["MNEMONICO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $fila, $terminal["CARPETA"]);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $fila, $terminal["DUPLEX"]);
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $fila, $terminal["SEMID"]);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $fila, $terminal["DOTS"]);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $fila, $terminal["AUTENTICADO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $fila, $terminal["ENCRIPTADO"]);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $fila, $terminal["DIRIP"]);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $fila, $terminal["VERSION"]);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $fila, $terminal["FALTA"]);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $fila, $terminal["OBSERVACIONES"]);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . $fila, $terminal["NUMEROK"]);
            if (($fila % 2) == 0){
                $rango = 'A' . $fila . ':V' . $fila;
                $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloRelleno);
            }
            $fila++;
        }
        $fila_fin = $fila - 1;
        $rango = 'A' . $fila_inicio . ':V' . $fila_fin;
        $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloCelda);

        // Hoja de Grupos:
        // Fijamos como hoja activa la tercera (Grupos):
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(2);

        // Fijamos el título de la Hoja:
        $objPHPExcel->getActiveSheet()->setTitle("(3) GSSI-TEL");

        // Tamaño de papel (A4) y orientación (Apaisado)
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Volvemos al estilo por defecto
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        // Pie de Página
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&$txtpagina &P de &N");

        // Título de la Página
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'GSSI ' . $flota["FLOTA"]);

        // Fecha:
        $objPHPExcel->getActiveSheet()->setCellValue('A3', $thfecha);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $fecha);
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($estiloCelda);

        // Imprimimos los grupos:
        $fila_inicio = 5;
        $columna = -2;
        $colmax = -1;
        if ($ngrupos > 0){
            // Encabezados:
            for ($i = 1; $i <= $ncarpetas; $i++){
                $fila = $fila_inicio;
                $col_inicio = $columna + 2;
                $columna = $columna + 2;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, 'CARPETA' . ' ' . $i);
                $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columna, $fila, $columna + 1, $fila);
                $fila++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $grupos[$i]['NOMBRE']);
                $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columna, $fila, $columna + 1, $fila);
                $fila++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, 'GSSI');
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna + 1, $fila, strtoupper($thmnemo));
                $fila++;
                $carpeta = $grupo['CARPETA'];
            }
            // Imprimimos los grupos:
            $columna = -2;
            $fila_grupos = $fila;
            foreach ($grupos as $grupocarpeta) {
                $fila = $fila_grupos;
                $columna = $columna + 2;
                foreach ($grupocarpeta['GISSI'] as $indice => $vectgssi) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $vectgssi['GISSI']);
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna + 1, $fila, $vectgssi['MNEMO']);
                    $fila++;
                }

            }
            // Estilos:
            $filamax = $objPHPExcel->getActiveSheet()->getHighestRow();
            $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
            // Título:
            $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $colmax .'1');
            // Celdas:
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicio . ':' . $colmax . $filamax)->applyFromArray($estiloCelda);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicio . ':' . $colmax . ($fila_inicio + 2))->applyFromArray($estiloTh);
            $relleno = FALSE;
            for ($fila = $fila_grupos; $fila <= $filamax; $fila++){
                if ($relleno){
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila .':' . $colmax . $fila)->applyFromArray($estiloRelleno);
                }
                $relleno = !($relleno);
            }
            $maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
            for ($i = 0; $i < $maxcol; $i++){
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
            }
        }
        else{
            $objPHPExcel->getActiveSheet()->setCellValue("A8", $errnogrupos);
            $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloError);
            $objPHPExcel->getActiveSheet()->mergeCells('A8:L8');
        }

        // Fijamos como hoja activa la cuarta (Permisos):
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(3);
        // Fijamos el título de la Hoja:
        $objPHPExcel->getActiveSheet()->setTitle("(4) ISSIs - PERMISOS");

        // Volvemos al estilo por defecto
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        // Tamaño de papel (A4) y orientación (Apaisado)
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Pie de Página
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&$txtpagina &P de &N");

        // Añadimos los datos de la Flota
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $h1permisos . " " . $flota["FLOTA"]);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);

        // Fecha:
        $objPHPExcel->getActiveSheet()->setCellValue('A3', $thfecha);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $fecha);
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($estiloCelda);

        // Imprimimos los permisos
        if ($ncarpterm > 0){
            // Encabezado:
            $objPHPExcel->getActiveSheet()->setCellValue('D5', strtoupper($thorganiza));
            $objPHPExcel->getActiveSheet()->setCellValue('B6', 'GSSI');
            $objPHPExcel->getActiveSheet()->setCellValue('C6', strtoupper($thmnemo));
            $columna = 3;
            foreach ($carpetas as $carpeta) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, 6, $carpeta);
                $columna++;
            }
            $fila = 6;
            foreach ($grupos_consulta as $grupo) {
                $gssi = $grupo['GISSI'];
                $fila++;
                $columna= 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $gssi);
                $columna++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $grupo['MNEMONICO']);
                foreach ($carpetas as $carpeta) {
                    $columna++;
                    if ($permisos[$gssi][$carpeta] > 0){
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, 'X');
                    }
                }
            }
            // Estilos
            $filamax = $objPHPExcel->getActiveSheet()->getHighestRow();
            $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
            $objPHPExcel->getActiveSheet()->mergeCells('D5:' . $colmax . '5');
            $objPHPExcel->getActiveSheet()->getStyle('D5:' . $colmax . '5')->applyFromArray($estiloTh);
            $objPHPExcel->getActiveSheet()->getStyle('D5:' . $colmax . '5')->applyFromArray($estiloCelda);
            $objPHPExcel->getActiveSheet()->getStyle('B6:' . $colmax . '6')->applyFromArray($estiloTh);
            $objPHPExcel->getActiveSheet()->getStyle('B6:' . $colmax . '6')->applyFromArray($estiloCelda);
            $objPHPExcel->getActiveSheet()->getStyle('B7:' . $colmax . $filamax)->applyFromArray($estiloCelda);
            $objPHPExcel->getActiveSheet()->getStyle('B7:' . $colmax . $filamax)->applyFromArray($estiloCentro);
            for ($fila = 8; $fila <= $filamax; $fila++){
                if (($fila % 2) == 0){
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $fila .':' . $colmax . $fila)->applyFromArray($estiloRelleno);
                }
            }
            $maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
            for ($i = 0; $i < $maxcol; $i++){
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
            }
        }
        else{
            $objPHPExcel->getActiveSheet()->setCellValue("A5", $errnocarpterm);
            $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($estiloError);
            $objPHPExcel->getActiveSheet()->mergeCells('A5:L5');
        }
    }
    else{
        // Título de la Hoja
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $errnoresult);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloError);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:M1');
    }
}
else{
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $h3perm . ": " . $errnoperm);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloError);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
}

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
$fichero = 'Flota_' . $flota['ACRONIMO'] . '.xlsx';
header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
header('Content-Disposition: attachment;filename="' . $fichero . '"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
 ?>
