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

// Consulta a la base de datos - Tabla Flotas
$sql_flota = "SELECT * FROM flotas WHERE ID = $idflota";
$res_flota = mysql_query($sql_flota) or die("Error en la consulta de flota: ".mysql_error());
$nflota = mysql_num_rows($res_flota);

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

// Fijamos como hoja activa la primera (Datos de la Flota):
$objPHPExcel->setActiveSheetIndex(0);

// Fijamos el título de la Hoja:
$objPHPExcel->getActiveSheet()->setTitle("(2) DATOS");

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
// Datos de la flota:
if ($nflota > 0){
    $flota = mysql_fetch_array($res_flota);
    $idorg = $flota['ORGANIZACION'];
    $sql_organizacion = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
    $res_organizacion = mysql_query($sql_organizacion) or die("Error en la consulta de flota: ".mysql_error());
    $norganiza = mysql_num_rows($res_organizacion);

    // Añadimos los datos de la Flota
    $objPHPExcel->getActiveSheet()->setCellValue('A4', 'FLOTA ' . $flota["FLOTA"]);
    $objPHPExcel->getActiveSheet()->mergeCells('A4:M4');
    $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estiloTitulo);

    // Fecha:
    $fecha = date('d-m-Y');
    $objPHPExcel->getActiveSheet()->setCellValue('A6', $thfecha);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($estiloHeader);
    $objPHPExcel->getActiveSheet()->setCellValue('B6', $fecha);
    $objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
    $objPHPExcel->getActiveSheet()->getStyle('A6:C6')->applyFromArray($estiloCelda);

    if ($norganiza > 0){
        $organizacion = mysql_fetch_array($res_organizacion);
        // Datos de la Organización:
        $objPHPExcel->getActiveSheet()->setCellValue('A8', $thorg);
        $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A8:B8');
        $objPHPExcel->getActiveSheet()->setCellValue('C8', $organizacion["ORGANIZACION"]);
        $objPHPExcel->getActiveSheet()->mergeCells('C8:M8');
        $objPHPExcel->getActiveSheet()->getStyle('A8:M8')->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->setCellValue('A10', $h2org);
        $objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($estiloCriterio);
        // Municipio de la Organización:
        $ineorg = $organizacion["INE"];
        $sql_mun = "SELECT * FROM municipios WHERE INE='$ineorg'";
        $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio de la Organización" . mysql_error());
        $nmun = mysql_num_rows($res_mun);
        if ($nmun > 0) {
            $row_mun = mysql_fetch_array($res_mun);
        }
        // Responsable de la Organizacion:
        $objPHPExcel->getActiveSheet()->getStyle('A11:L15')->applyFromArray($estiloCelda);
        if ($organizacion["RESPONSABLE"] != 0){
            // Consulta a la base de datos - Tabla Contactos
            $idc = $organizacion["RESPONSABLE"];
            $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idc";
            $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0){
                $resporg = mysql_fetch_array($res_contacto);
                $objPHPExcel->getActiveSheet()->setCellValue('A11', $thnombre);
                $objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->mergeCells('A11:B11');
                $objPHPExcel->getActiveSheet()->setCellValue('C11', $resporg["NOMBRE"]);
                $objPHPExcel->getActiveSheet()->mergeCells('C11:H11');
                $objPHPExcel->getActiveSheet()->setCellValue('I11', 'NIF');
                $objPHPExcel->getActiveSheet()->getStyle('I11')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->setCellValue('J11', $resporg["NIF"]);
                $objPHPExcel->getActiveSheet()->mergeCells('J11:L11');
                $objPHPExcel->getActiveSheet()->setCellValue('A12', $thcargo);
                $objPHPExcel->getActiveSheet()->getStyle('A12')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->mergeCells('A12:B12');
                $objPHPExcel->getActiveSheet()->setCellValue('C12', $resporg["CARGO"]);
                $objPHPExcel->getActiveSheet()->mergeCells('C12:L12');
                $objPHPExcel->getActiveSheet()->setCellValue('A13', $thdirec);
                $objPHPExcel->getActiveSheet()->getStyle('A13')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->mergeCells('A13:B13');
                $objPHPExcel->getActiveSheet()->setCellValue('C13', $organizacion["DOMICILIO"]);
                $objPHPExcel->getActiveSheet()->mergeCells('C13:L13');
                $objPHPExcel->getActiveSheet()->setCellValue('A14', $thmuni);
                $objPHPExcel->getActiveSheet()->getStyle('A14')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->mergeCells('A14:B14');
                $objPHPExcel->getActiveSheet()->setCellValue('C14', $organizacion["CP"] . ' - ' . $row_mun["MUNICIPIO"]);
                $objPHPExcel->getActiveSheet()->mergeCells('C14:G14');
                $objPHPExcel->getActiveSheet()->setCellValue('H14', $thprov);
                $objPHPExcel->getActiveSheet()->getStyle('H14')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->mergeCells('H14:I14');
                $objPHPExcel->getActiveSheet()->setCellValue('J14', $row_mun["PROVINCIA"]);
                $objPHPExcel->getActiveSheet()->mergeCells('J14:L14');
                $objPHPExcel->getActiveSheet()->setCellValue('A15', $thtelef);
                $objPHPExcel->getActiveSheet()->getStyle('A15')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->mergeCells('A15:B15');
                $objPHPExcel->getActiveSheet()->setCellValue('C15', $resporg["TELEFONO"]);
                $objPHPExcel->getActiveSheet()->mergeCells('C15:F15');
                $objPHPExcel->getActiveSheet()->setCellValue('G15', $thmail);
                $objPHPExcel->getActiveSheet()->getStyle('G15')->applyFromArray($estiloHeader);
                $objPHPExcel->getActiveSheet()->mergeCells('G15:H15');
                $objPHPExcel->getActiveSheet()->setCellValue('I15', $resporg["MAIL"]);
                $objPHPExcel->getActiveSheet()->mergeCells('I15:L15');
            }
        }
    }
    else {
        $ineorg = 0;
    }

    // Contactos de la Flota:
    // Responsable de la Flota:
    $objPHPExcel->getActiveSheet()->getStyle('A18:L22')->applyFromArray($estiloCelda);
    $objPHPExcel->getActiveSheet()->setCellValue('A17', $h2resp);
    $objPHPExcel->getActiveSheet()->getStyle('A17')->applyFromArray($estiloCriterio);
    $sql_contresp = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'RESPONSABLE')";
    $res_contresp = mysql_query($sql_contresp) or die(mysql_error());
    $ncontresp = mysql_num_rows($res_contresp);
    // Consulta a la base de datos - Tabla Municipios
    $ineflo = $flota["INE"];
    if ($ineflo <> $ineorg){
        $sql_mun = "SELECT * FROM municipios WHERE INE='$ineflo'";
        $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio de la Organización" . mysql_error());
        $nmun = mysql_num_rows($res_mun);
        if ($nmun > 0) {
            $row_mun = mysql_fetch_array($res_mun);
        }
    }
    if ($ncontresp > 0){
        $contresp = mysql_fetch_array($res_contresp);
        $idrespflo = $contresp['CONTACTO_ID'];
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idrespflo";
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $respflo = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('A18', $thnombre);
            $objPHPExcel->getActiveSheet()->getStyle('A18')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->mergeCells('A18:B18');
            $objPHPExcel->getActiveSheet()->setCellValue('C18', $respflo["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->mergeCells('C18:H18');
            $objPHPExcel->getActiveSheet()->setCellValue('I18', 'NIF');
            $objPHPExcel->getActiveSheet()->getStyle('I18')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->setCellValue('J18', $respflo["NIF"]);
            $objPHPExcel->getActiveSheet()->mergeCells('J18:L18');
            $objPHPExcel->getActiveSheet()->setCellValue('A19', $thcargo);
            $objPHPExcel->getActiveSheet()->getStyle('A19')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->mergeCells('A19:B19');
            $objPHPExcel->getActiveSheet()->setCellValue('C19', $respflo["CARGO"]);
            $objPHPExcel->getActiveSheet()->mergeCells('C19:L19');
            $objPHPExcel->getActiveSheet()->setCellValue('A20', $thdirec);
            $objPHPExcel->getActiveSheet()->getStyle('A20')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->mergeCells('A20:B20');
            $objPHPExcel->getActiveSheet()->setCellValue('C20', $flota["DOMICILIO"]);
            $objPHPExcel->getActiveSheet()->mergeCells('C20:L20');
            $objPHPExcel->getActiveSheet()->setCellValue('A21', $thmuni);
            $objPHPExcel->getActiveSheet()->getStyle('A21')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->mergeCells('A21:B21');
            $objPHPExcel->getActiveSheet()->setCellValue('C21', $flota["CP"] . ' - ' . $row_mun["MUNICIPIO"]);
            $objPHPExcel->getActiveSheet()->mergeCells('C21:G21');
            $objPHPExcel->getActiveSheet()->setCellValue('H21', $thprov);
            $objPHPExcel->getActiveSheet()->getStyle('H21')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->mergeCells('H21:I21');
            $objPHPExcel->getActiveSheet()->setCellValue('J21', $row_mun["PROVINCIA"]);
            $objPHPExcel->getActiveSheet()->mergeCells('J21:L21');
            $objPHPExcel->getActiveSheet()->setCellValue('A22', $thtelef);
            $objPHPExcel->getActiveSheet()->getStyle('A22')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->mergeCells('A22:B22');
            $objPHPExcel->getActiveSheet()->setCellValue('C22', $respflo["TELEFONO"]);
            $objPHPExcel->getActiveSheet()->mergeCells('C22:F22');
            $objPHPExcel->getActiveSheet()->setCellValue('G22', $thmail);
            $objPHPExcel->getActiveSheet()->getStyle('G22')->applyFromArray($estiloHeader);
            $objPHPExcel->getActiveSheet()->mergeCells('G22:H22');
            $objPHPExcel->getActiveSheet()->setCellValue('I22', $respflo["MAIL"]);
            $objPHPExcel->getActiveSheet()->mergeCells('I22:L22');
        }
    }

    // Contactos Operativos:
    $objPHPExcel->getActiveSheet()->setCellValue('A24', $h2op);
    $objPHPExcel->getActiveSheet()->getStyle('A24')->applyFromArray($estiloCriterio);
    $objPHPExcel->getActiveSheet()->setCellValue('A25', $thnombre);
    $objPHPExcel->getActiveSheet()->mergeCells('A25:C25');
    $objPHPExcel->getActiveSheet()->setCellValue('D25', 'NIF');
    $objPHPExcel->getActiveSheet()->mergeCells('D25:E25');
    $objPHPExcel->getActiveSheet()->setCellValue('F25', $thcargo);
    $objPHPExcel->getActiveSheet()->mergeCells('F25:H25');
    $objPHPExcel->getActiveSheet()->setCellValue('I25', $thmail);
    $objPHPExcel->getActiveSheet()->mergeCells('I25:K25');
    $objPHPExcel->getActiveSheet()->setCellValue('L25', $thtelef);
    $objPHPExcel->getActiveSheet()->mergeCells('L25:M25');
    $objPHPExcel->getActiveSheet()->getStyle('A25:M25')->applyFromArray($estiloTh);
    $sql_contop = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'OPERATIVO')";
    $res_contop = mysql_query($sql_contop) or die(mysql_error());
    $ncontop = mysql_num_rows($res_contop);
    $fila = $fila_op = 26;
    if ($ncontop > 0){
        for ($i = 0 ; $i < $ncontop; $i++){
            $fila = $fila_op + $i;
            $contop = mysql_fetch_array($res_contop);
            $sql_contacto = "SELECT * FROM contactos  WHERE ID = " . $contop['CONTACTO_ID'];
            $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0){
                $contacto = mysql_fetch_array($res_contacto);
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $contacto["NOMBRE"]);
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':C' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$fila, $contacto["NIF"]);
                $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila . ':E' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $contacto["CARGO"]);
                $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila . ':H' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$fila, $contacto["MAIL"]);
                $objPHPExcel->getActiveSheet()->mergeCells('I' . $fila . ':K' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('L'.$fila, $contacto["TELEFONO"]);
                $objPHPExcel->getActiveSheet()->mergeCells('L' . $fila . ':M' . $fila);
            }
            if (($i % 2) == 1){
                $rango = "A$fila:M$fila";
                $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloRelleno);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicio . ':M' . $fila)->applyFromArray($estiloCelda);
    }

    // Contactos Técnicos:
    $fila_tec = $fila + 2;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila_tec, $h2op);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_tec)->applyFromArray($estiloCriterio);
    $fila_tec++;
    $fila_inicio = $fila_tec;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila_tec, $thnombre);
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila_tec . ':C' . $fila_tec);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila_tec, 'NIF');
    $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila_tec . ':E' . $fila_tec);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila_tec, $thcargo);
    $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila_tec . ':H' . $fila_tec);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $fila_tec, $thmail);
    $objPHPExcel->getActiveSheet()->mergeCells('I' . $fila_tec . ':K' . $fila_tec);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $fila_tec, $thtelef);
    $objPHPExcel->getActiveSheet()->mergeCells('L' . $fila_tec . ':M' . $fila_tec);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_tec . ':M' . $fila_tec)->applyFromArray($estiloTh);
    $fila_tec++;
    $sql_conttec = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'TECNICO')";
    $res_conttec = mysql_query($sql_conttec) or die(mysql_error());
    $nconttec = mysql_num_rows($res_conttec);
    $fila = $fila_tec;
    for ($i = 0 ; $i < $nconttec; $i++){
        $fila = $fila_tec + $i;
        $conttec = mysql_fetch_array($res_conttec);
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = " . $conttec['CONTACTO_ID'];
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $contacto = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':C' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$fila, $contacto["NIF"]);
            $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila . ':E' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $contacto["CARGO"]);
            $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila . ':H' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$fila, $contacto["MAIL"]);
            $objPHPExcel->getActiveSheet()->mergeCells('I' . $fila . ':K' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$fila, $contacto["TELEFONO"]);
            $objPHPExcel->getActiveSheet()->mergeCells('L' . $fila . ':M' . $fila);
        }
        if (($i % 2) == 1){
            $rango = "A$fila:M$fila";
            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloRelleno);
        }
    }
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicio . ':M' . $fila)->applyFromArray($estiloCelda);

    // Contactos 24x7:
    $fila_24h = $fila_inicio = $fila + 2;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila_24h, $h224h);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_24h)->applyFromArray($estiloCriterio);
    $fila_24h++;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila_24h, $thnom24h);
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila_24h . ':E' . $fila_24h);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila_24h, $thmail);
    $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila_24h . ':J' . $fila_24h);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $fila_24h, $thtelef);
    $objPHPExcel->getActiveSheet()->mergeCells('K' . $fila_24h . ':M' . $fila_24h);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_24h . ':M' . $fila_24h)->applyFromArray($estiloTh);
    $fila_24h++;
    $fila = $fila_24h;
    $sql_cont24h = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'CONT24H')";
    $res_cont24h = mysql_query($sql_cont24h) or die(mysql_error());
    $ncont24h = mysql_num_rows($res_cont24h);
    for ($i = 0 ; $i < $ncont24h; $i++){
        $fila = $fila_24h + $i;
        $cont24h = mysql_fetch_array($res_cont24h);
        $sql_contacto = "SELECT * FROM contactos  WHERE ID = " . $cont24h['CONTACTO_ID'];
        $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $contacto = mysql_fetch_array($res_contacto);
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $contacto["NOMBRE"]);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':E' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $contacto["MAIL"]);
            $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila . ':J' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$fila, $contacto["TELEFONO"]);
            $objPHPExcel->getActiveSheet()->mergeCells('K' . $fila . ':M' . $fila);
        }
        if (($i % 2) == 1){
            $rango = "A$fila:M$fila";
            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloRelleno);
        }
    }
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicio . ':M' . $fila)->applyFromArray($estiloCelda);

    /* Datos de Terminales */
    // Consulta a la base de datos - Tabla Terminales
    $sql_term = "SELECT * FROM terminales WHERE terminales.FLOTA = $idflota ";
    $sql_term = $sql_term."ORDER BY terminales.ISSI ASC";
    $res_term = mysql_query($sql_term) or die("Error en la consulta de $terminales: ".mysql_error());
    $nterm = mysql_num_rows($res_term);
    // Fijamos como hoja activa la segunda (Terminales):
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1);

    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle("(3) ISSI");

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

    // Añadimos los datos de la Flota
    $objPHPExcel->getActiveSheet()->setCellValue('A4', $h1term . " " . $flota["FLOTA"]);
    $objPHPExcel->getActiveSheet()->mergeCells('A4:V4');
    $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estiloTitulo);

    // Fecha:
    $objPHPExcel->getActiveSheet()->setCellValue('A6', $thfecha);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($estiloHeader);
    $objPHPExcel->getActiveSheet()->setCellValue('B6', $fecha);
    $objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
    $objPHPExcel->getActiveSheet()->getStyle('A6:C6')->applyFromArray($estiloCelda);

    // Imprimir el número de terminales:
    $objPHPExcel->getActiveSheet()->setCellValue('A8', $h2nterm);
    $objPHPExcel->getActiveSheet()->mergeCells('A8:C8');
    $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValue('D8', $nterm);
    $objPHPExcel->getActiveSheet()->getStyle('A8:D8')->applyFromArray($estiloCelda);

    // Añadimos el Rango de ISSI de la Flota:
    $objPHPExcel->getActiveSheet()->setCellValue('G8', $h2rango);
    $objPHPExcel->getActiveSheet()->mergeCells('G8:H8');
    $objPHPExcel->getActiveSheet()->getStyle('G8')->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValue('I8', $flota["RANGO"]);
    $objPHPExcel->getActiveSheet()->getStyle('G8:I8')->applyFromArray($estiloCelda);

    // Imprimir los resultados
    // Ajustamos los estilos para los terminales
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(8);
    // Imprimimos la cabecera:
    $objPHPExcel->getActiveSheet()->setCellValue('A10', 'Nº');
    $objPHPExcel->getActiveSheet()->mergeCells('A10:A11');
    // Aquí:
    $objPHPExcel->getActiveSheet()->setCellValue('B10', 'TERMINAL');
    $objPHPExcel->getActiveSheet()->mergeCells('B10:G10');
    $objPHPExcel->getActiveSheet()->setCellValue('H10', 'ISSI');
    $objPHPExcel->getActiveSheet()->mergeCells('H10:H11');
    $objPHPExcel->getActiveSheet()->setCellValue('I10', 'TEI');
    $objPHPExcel->getActiveSheet()->mergeCells('I10:I11');
    $objPHPExcel->getActiveSheet()->setCellValue('J10', $thnserie);
    $objPHPExcel->getActiveSheet()->mergeCells('J10:J11');
    $objPHPExcel->getActiveSheet()->setCellValue('K10', $thmnemo);
    $objPHPExcel->getActiveSheet()->mergeCells('K10:K11');
    $objPHPExcel->getActiveSheet()->setCellValue('L10', 'Carpeta');
    $objPHPExcel->getActiveSheet()->mergeCells('L10:L11');
    $objPHPExcel->getActiveSheet()->setCellValue('M10', $thllam);
    $objPHPExcel->getActiveSheet()->mergeCells('M10:N10');
    $objPHPExcel->getActiveSheet()->setCellValue('O10', 'Alta DOTS');
    $objPHPExcel->getActiveSheet()->mergeCells('O10:O11');
    $objPHPExcel->getActiveSheet()->setCellValue('P10', $thaut);
    $objPHPExcel->getActiveSheet()->mergeCells('P10:P11');
    $objPHPExcel->getActiveSheet()->setCellValue('Q10', $thenc);
    $objPHPExcel->getActiveSheet()->mergeCells('Q10:Q11');
    $objPHPExcel->getActiveSheet()->setCellValue('R10', $thdirip);
    $objPHPExcel->getActiveSheet()->mergeCells('R10:R11');
    $objPHPExcel->getActiveSheet()->setCellValue('S10', $thversion);
    $objPHPExcel->getActiveSheet()->mergeCells('S10:S11');
    $objPHPExcel->getActiveSheet()->setCellValue('T10', $thalta);
    $objPHPExcel->getActiveSheet()->mergeCells('T10:T11');
    $objPHPExcel->getActiveSheet()->setCellValue('U10', $thobserv);
    $objPHPExcel->getActiveSheet()->mergeCells('U10:U11');
    $objPHPExcel->getActiveSheet()->setCellValue('V10', 'Número K');
    $objPHPExcel->getActiveSheet()->mergeCells('V10:V11');
    $objPHPExcel->getActiveSheet()->setCellValue('B11', 'Marca');
    $objPHPExcel->getActiveSheet()->setCellValue('C11', $thmodelo);
    $objPHPExcel->getActiveSheet()->setCellValue('D11', $thtipo);
    $objPHPExcel->getActiveSheet()->setCellValue('E11', $thproveedor);
    $objPHPExcel->getActiveSheet()->setCellValue('F11', 'Cod. HW');
    $objPHPExcel->getActiveSheet()->setCellValue('G11', 'A.M.');
    $objPHPExcel->getActiveSheet()->setCellValue('M11', 'D');
    $objPHPExcel->getActiveSheet()->setCellValue('N11', 'S-D');
    $objPHPExcel->getActiveSheet()->getStyle('A10:V11')->applyFromArray($estiloTh);
    // Repetimos la cabecera de la tabla;
    $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(10, 11);

    // Imprimir los terminales
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
            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloRelleno);
        }
    }
    $rango = 'A10:V' . $fila;
    $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estiloCelda);
    $maxcol = PHPExcel_Cell::columnIndexFromString('V');
    /*for ($i = 0; $i < $maxcol; $i++){
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
    }*/

    /* Datos de Grupos */
    $sql_grupos = "SELECT grupos_flotas.*, grupos.MNEMONICO FROM grupos_flotas, grupos";
    $sql_grupos .= " WHERE (grupos_flotas.GISSI = grupos.GISSI) AND (grupos_flotas.FLOTA = " . $idflota . ")";
    $sql_grupos .= " ORDER BY grupos_flotas.CARPETA, grupos_flotas.GISSI";
    $res_grupos = mysql_query($sql_grupos) or die("Error en la consulta de Grupos: " . mysql_error());
    $ngrupos = mysql_num_rows($res_grupos);
    // Fijamos como hoja activa la tercera (Grupos):
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(2);
    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle("(4) GSSI-TEL");

    // Volvemos al estilo por defecto
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

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

    // Añadimos los datos de la Flota
    $objPHPExcel->getActiveSheet()->setCellValue('A4', "GSSI " . $flota["FLOTA"]);

    // Fecha:
    $objPHPExcel->getActiveSheet()->setCellValue('A6', $thfecha);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($estiloHeader);
    $objPHPExcel->getActiveSheet()->setCellValue('B6', $fecha);
    $objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
    $objPHPExcel->getActiveSheet()->getStyle('A6:C6')->applyFromArray($estiloCelda);

    // Imprimimos los grupos:
    $grupos = array();
    $carpeta = 0;
    $ngcmax = 0;
    $ngc = 0;
    $gissicarpeta = array();
    $grupos_consulta = array();
    if ($ngrupos > 0){
        for ($i = 0; $i < $ngrupos; $i++){
            $row_grupo = mysql_fetch_array($res_grupos);
            $grupos[] = $row_grupo;
        }
        // Encabezados -> Aquí:
        $fila_inicio = 8;
        $columna = -2;
        $colmax = -1;
        $carpeta = 0;
        foreach ($grupos as $grupo) {
            if ($grupo['CARPETA'] > $carpeta){
                $fila = $fila_inicio;
                $col_inicio = $columna + 2;
                $columna = $columna + 2;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, 'CARPETA' . ' ' . $grupo['CARPETA']);
                $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columna, $fila, $columna + 1, $fila);
                $fila++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $grupo['NOMBRE']);
                $objPHPExcel->getActiveSheet()->mergeCellsByColumnAndRow($columna, $fila, $columna + 1, $fila);
                $fila++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, 'GSSI');
                $columna++;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, strtoupper($thmnemo));
                $fila++;
                $carpeta = $grupo['CARPETA'];
            }
            $columna = $col_inicio;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $grupo['GISSI']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna + 1, $fila, $grupo['MNEMONICO']);
            $fila++;
        }
        $filamax = $objPHPExcel->getActiveSheet()->getHighestRow();
        $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A4:' . $colmax . '4');
        $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->getStyle('A8:' . $colmax . $filamax)->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->getStyle('A8:' . $colmax . '10')->applyFromArray($estiloTh);
        for ($fila = 11; $fila <= $filamax; $fila++){
            if (($fila % 2) == 0){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila .':' . $colmax . $fila)->applyFromArray($estiloRelleno);
            }
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

    /* Datos de Permisos */
    $sql_carpterm = "SELECT DISTINCT CARPTERM FROM permisos_flotas WHERE (FLOTA = " . $idflota . ")";
    $sql_carpterm .= " ORDER BY CARPTERM";
    $res_carpterm = mysql_query($sql_carpterm) or die("Error en la consulta de Carpetas: " . mysql_error());
    $ncarpterm = mysql_num_rows($res_carpterm);
    // Fijamos como hoja activa la cuarta (Permisos):
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(3);
    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle("(5) ISSIs - PERMISOS");

    // Volvemos al estilo por defecto
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

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

    // Añadimos los datos de la Flota
    $objPHPExcel->getActiveSheet()->setCellValue('A4', $h1permisos . " " . $flota["FLOTA"]);

    // Fecha:
    $objPHPExcel->getActiveSheet()->setCellValue('A6', $thfecha);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($estiloHeader);
    $objPHPExcel->getActiveSheet()->setCellValue('B6', $fecha);
    $objPHPExcel->getActiveSheet()->mergeCells('B6:C6');
    $objPHPExcel->getActiveSheet()->getStyle('A6:C6')->applyFromArray($estiloCelda);

    if ($ncarpterm > 0){
        $carpetas = array();
        for ($i = 0; $i < $ncarpterm; $i++){
            $row_carpterm = mysql_fetch_array($res_carpterm);
            $carpetas[] = $row_carpterm['CARPTERM'];
        }
        // Encabezado:
        $objPHPExcel->getActiveSheet()->setCellValue('D8', strtoupper($thorg));
        $objPHPExcel->getActiveSheet()->setCellValue('B9', 'GSSI');
        $objPHPExcel->getActiveSheet()->setCellValue('C9', strtoupper($thmnemo));
        $columna = 3;
        foreach ($carpetas as $carpeta) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, 9, $carpeta);
            $columna++;
        }
        $fila = 9;
        foreach ($grupos as $grupo) {
            $gssi = $grupo['GISSI'];
            $fila++;
            $columna= 1;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $gssi);
            $columna++;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $grupo['MNEMONICO']);
            foreach ($carpetas as $carpeta) {
                $columna++;
                $sql_perm = "SELECT * FROM permisos_flotas WHERE (FLOTA = " . $idflota . ")";
                $sql_perm .= " AND (GISSI = " . $gssi . ") AND (CARPTERM = '" . $carpeta . "')";
                $res_perm = mysql_query($sql_perm) or die("Error en la consulta de Permisos: " . mysql_error());
                $nperm = mysql_num_rows($res_perm);
                if ($nperm > 0){
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, 'X');
                }
            }
        }
        // Estilos
        $filamax = $objPHPExcel->getActiveSheet()->getHighestRow();
        $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A4:' . $colmax . '4');
        $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->mergeCells('D8:' . $colmax . '8');
        $objPHPExcel->getActiveSheet()->getStyle('D8:' . $colmax . '8')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->getStyle('D8:' . $colmax . '8')->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->getStyle('B9:' . $colmax . '9')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->getStyle('B9:' . $colmax . '9')->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->getStyle('B10:' . $colmax . $filamax)->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->getStyle('B10:' . $colmax . $filamax)->applyFromArray($estiloCentro);
        for ($fila = 11; $fila <= $filamax; $fila++){
            if (($fila % 2) == 0){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila .':' . $colmax . $fila)->applyFromArray($estiloRelleno);
            }
        }
        $maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
        for ($i = 0; $i < $maxcol; $i++){
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
        }
    }
    else{
        $objPHPExcel->getActiveSheet()->setCellValue("A8", $errnoperm);
        $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloError);
        $objPHPExcel->getActiveSheet()->mergeCells('A8:L8');
    }


}
else{
    $objPHPExcel->getActiveSheet()->removeRow(3, 31);
    $objPHPExcel->getActiveSheet()->setCellValue("A4", $errnoflota);
    $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estiloError);
    $objPHPExcel->getActiveSheet()->mergeCells('A4:O4');
}

// Fijamos la primera hoja como la activa, al abrir Excel
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
$fichero = "Flota_".$flota["ACRONIMO"];

header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
header('Content-Disposition: attachment;filename="'.$fichero.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
