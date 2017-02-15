<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termdet_$idioma.php";
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

//datos de la tabla terminales
$sql_terminal = "SELECT * FROM terminales WHERE ID='$idterm'";
$res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
$nterminal = mysql_num_rows($res_terminal);
if ($nterminal > 0) {
    $row_terminal = mysql_fetch_array($res_terminal);
    $id_flota = $row_terminal["FLOTA"];
}

$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    if ($flota_usu == $id_flota) {
        $permiso = 1;
    }
}
if ($permiso != 0){
    // Clases para generar el Excel
        /** Error reporting */
    //error_reporting(E_ALL);
    date_default_timezone_set('Europe/Madrid');
    /** PHPExcel */
    require_once 'Classes/PHPExcel.php';

    // Consulta a la base de datos
    //datos de la tabla terminales
    $sql_terminal = "SELECT * FROM terminales WHERE ID='$idterm'";
    $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
    $nterminal = mysql_num_rows($res_terminal);
    if ($nterminal == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Terminal</p>\n";
    }
    else {
        $tipo = $row_terminal["TIPO"];
        switch ($tipo) {
            case ("F"): {
                    $tipo = $fijo;
                    break;
            }
            case ("M"): {
                    $tipo = $movil;
                    break;
            }
            case ("MB"): {
                    $tipo = $movilb;
                    break;
            }
            case ("MA"): {
                    $tipo = $movila;
                    break;
            }
            case ("MG"): {
                    $tipo = $movilg;
                    break;
            }
            case ("P"): {
                    $tipo = $portatil;
                    break;
            }
            case ("PB"): {
                    $tipo = $portatilb;
                    break;
            }
            case ("PA"): {
                    $tipo = $portatila;
                    break;
            }
            case ("PX"): {
                    $tipo = $portatilx;
                    break;
            }
        }
        $row_terminal["TIPO"] = $tipo;
    }
    //datos de la tabla flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$id_flota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota Usuaria: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
    }
    //datos de la tabla municipios
    $ine = $row_flota ["INE"];
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
    }

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

    // Fijamos como hoja activa la primera y fijamos el título:
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle($termcomdes);


    $cabecera = $campxls;
    $nomcolumna = array("A","B","C","D","E","F","G","H","I","J","K","L");
    $anchos = array(15, 10, 10, 10, 10, 10, 10, 10, 10, 10, 15, 15);

    // Fijamos los estilos generales de la hoja:
    // Crietrios
    $estiltit = array(
        'font' => array(
            'bold' => true,
            'size' => 12,
            'color' => array(
                'argb' => 'FF00407A',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    // Crietrios
    $estilcrit = array(
        'font' => array(
            'bold' => true,
            'color' => array(
                'argb' => 'FF00407A',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
    );
    // Error
    $estilerr = array(
        'font' => array(
            'bold' => true,
            'color' => array(
                'argb' => 'FFFF0000',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => 'FF000000'),
            ),
        ),
    );
    // Selecciones
    $estilsel = array(
        'font' => array(
            'bold' => true,
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
    );
    // Cabecera de Tabla
    $estilth = array(
        'font' => array(
            'bold' => true,
            'color' => array(
                'argb' => 'FFFFFFFF',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'argb' => 'FF00407A'
            ),
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_NONE,
            ),
        ),
    );
    // Fondo Gris de fila de datos
    $estilgris = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'argb' => 'FFF0F2F5'
            ),
        ),
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => 'FF000000'),
            ),
        ),
    );
    // Estilo de borde de datos
    $estilborde = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => 'FF000000'),
            ),
        ),
    );

    // Tamaño de papel (A4) y orientación (Vertical)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    //Ajustamos 1 Página de Ancho x N de Alto
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

    $objPHPExcel->getActiveSheet()->setShowGridlines(false);

    //Cabecera de Página
    $objDrawing = new PHPExcel_Worksheet_HeaderFooterDrawing();
    $objDrawing->setName("Logo COMDES");
    $objDrawing->setPath("./imagenes/comdes2.png");
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->addImage($objDrawing, PHPExcel_Worksheet_HeaderFooter::IMAGE_FOOTER_LEFT);
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader("&G &CTerminal COMDES");

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

    // Fijamos los anchos de columnas:
    for ($i = 0; $i < count($anchos); $i++){
        $objPHPExcel->getActiveSheet()->getColumnDimension($nomcolumna[$i])->setWidth($anchos[$i]);
    }

    // Primera hoja: Título de la tabla de datos
    $h1 = "Terminal ISSI: ".$row_terminal["ISSI"]." / TEI: ".$row_terminal["TEI"];
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiltit);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', $h1);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:L1');
    $fila = 3;

    // Cabecera h2admin
    $objPHPExcel->getActiveSheet()->setCellValue('A3', $h2admin);
    $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estilcrit);
    $objPHPExcel->getActiveSheet()->mergeCells('A3:D3');
    $fila++;
    // Datos Admin:
    $colini = array("A","C","E","G","I","K");
    $colfin = array("B","D","F","H","J","L");
    $fila_imp = array($tipotxt, 'Marca', $modtxt, $proveedor, $amtxt, $dotstxt);
    for ($i = 0; $i < count($colini); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
    }
    $rangofila = "A$fila:L$fila";
    $objPHPExcel->getActiveSheet()->getStyle($rangofila)->applyFromArray($estilth);
    $fila++;
    $fila_imp = array($row_terminal["TIPO"], $row_terminal["MARCA"], $row_terminal["MODELO"], $row_terminal["PROVEEDOR"], $row_terminal["AM"], $row_terminal["DOTS"]);
    for ($i = 0; $i < count($colini); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
    }
    $rangofila = "A$fila:L$fila";
    $objPHPExcel->getActiveSheet()->getStyle($rangofila)->applyFromArray($estilborde);
    $fila = $fila + 2;

    // Cabecera h2flota
    $objPHPExcel->getActiveSheet()->setCellValue('A7', $h2flota);
    $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estilcrit);
    $objPHPExcel->getActiveSheet()->mergeCells('A7:D7');
    // Datos Flota:
    $fila_imp = array($nomflota, $acronimo, $localiza);
    $colini = array("A","E","G");
    $colfin = array("D","F","L");
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i]."8";
        $celdafin = $colfin[$i]."8";
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
    }
    $objPHPExcel->getActiveSheet()->getStyle('A8:L8')->applyFromArray($estilth);
    $localizacion = $row_flota["DOMICILIO"] . " - " . $row_flota["CP"] . " " . $row_mun["MUNICIPIO"];
    $fila_imp = array($row_flota["FLOTA"], $row_flota["ACRONIMO"], $localizacion);
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i]."9";
        $celdafin = $colfin[$i]."9";
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
    }
    $objPHPExcel->getActiveSheet()->getStyle('A9:L9')->applyFromArray($estilborde);

    // Datos de Contactos
    // Cabecera h2
    $objPHPExcel->getActiveSheet()->setCellValue('A11', $h3flota);
    $objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($estilcrit);
    $objPHPExcel->getActiveSheet()->mergeCells('A11:D11');
    $fila = 12;
     if (($row_flota["RESPONSABLE"] == "0") && ($row_flota["CONTACTO1"] == "0") && ($row_flota["CONTACTO2"] == "0") && ($row_flota["CONTACTO3"] == "0")) {
        $objPHPExcel->getActiveSheet()->setCellValue('A11', $nocont);
        $objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($estilerr);
        $objPHPExcel->getActiveSheet()->mergeCells('A11:F11');
    }
    else{
        $fila_imp = array('', $nomflota, $cargo, $telefono, $mail);
        $colini = array("A","B","F","J","K");
        $colfin = array("A","E","I","J","L");
        $colerri = array("A","B");
        $colerrf = array("A","L");
        for ($i = 0; $i < count($fila_imp); $i++){
            $celdaini = $colini[$i].$fila;
            $celdafin = $colfin[$i].$fila;
            $celdamerg = "$celdaini:$celdafin";
            $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
            $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
        }
        $rangofila = "B$fila:L$fila";
        $objPHPExcel->getActiveSheet()->getStyle($rangofila)->applyFromArray($estilth);
        $fila++;
        $idc = array($row_flota["RESPONSABLE"], $row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
        $idcampo = array("Responsable", "$contacto 1", "$contacto 2", "$contacto 3");
        for ($j = 0; $j < count($idc); $j++) {
             if ($idc[$j] != 0) {
                $id_contacto = $idc[$j];
                $sql_contacto = "SELECT * FROM contactos WHERE ID=$id_contacto";
                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
                if ($ncontacto != 0) {
                    $row_contacto = mysql_fetch_array($res_contacto);
                    $nombre = $row_contacto["NOMBRE"];
                    $cargo = $row_contacto["CARGO"];
                    $fila_imp = array($idcampo[$j], $nombre, $cargo, $row_contacto["TELEFONO"], $row_contacto["MAIL"]);
                    for ($i = 0; $i < count($fila_imp); $i++){
                        $celdaini = $colini[$i].$fila;
                        $celdafin = $colfin[$i].$fila;
                        $celdamerg = "$celdaini:$celdafin";
                        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
                        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
                    }
                    $rangoth = "A$fila";
                    $rangofila = "B$fila:L$fila";
                    $objPHPExcel->getActiveSheet()->getStyle($rangoth)->applyFromArray($estilth);
                    if (($j % 2) == 0){
                        $estilo = $estilborde;
                    }
                    else{
                        $estilo = $estilgris;
                    }
                    $objPHPExcel->getActiveSheet()->getStyle($rangofila)->applyFromArray($estilo);
                }
                else {
                    $fila_imp = array($idcampo[$j], $nocontenc." ".$idcampo[$j]." de la Flota");
                    $rangoth = "A$fila";
                    $rangofila = "B$fila:L$fila";
                    for ($i = 0; $i < count($fila_imp); $i++){
                        $celdaini = $colerri[$i].$fila;
                        $celdafin = $colerrf[$i].$fila;
                        $celdamerg = "$celdaini:$celdafin";
                        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
                        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
                    }
                    $objPHPExcel->getActiveSheet()->getStyle($rangoth)->applyFromArray($estilth);
                    $objPHPExcel->getActiveSheet()->getStyle($rangofila)->applyFromArray($estilerr);
                }
                $fila++;
             }
        }
    }
    $fila++;

    // Datos del terminal
    // Cabecera h2
    $celdaini = "A$fila";
    $celdamerg = "A$fila:D$fila";
    $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $h2term);
    $objPHPExcel->getActiveSheet()->getStyle($celdaini)->applyFromArray($estilcrit);
    $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
    $fila++;
    $colini = array("A","C","G","I");
    $colfin = array("B","F","H","L");
    $fila_imp = array('ISSI', $row_terminal["ISSI"], 'TEI', $row_terminal["TEI"]);
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
        if (($i % 2) == 0){
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilth);
        }
        else {
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilborde);
        }
    }
    $fila++;
    $fila_imp = array($cdhw, $row_terminal["CODIGOHW"], $nserie, $row_terminal["NSERIE"]);
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
        if (($i % 2) == 0){
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilth);
        }
        else {
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilgris);
        }
    }
    $fila++;
    $fila_imp = array('ID', $row_terminal["ID"], $mnemo, $row_terminal["MNEMONICO"]);
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
        if (($i % 2) == 0){
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilth);
        }
        else {
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilborde);
        }
    }
    $fila++;
    $fila_imp = array("$llamada Dúplex", $row_terminal["DUPLEX"], "$llamada semidúplex", $row_terminal["SEMID"]);
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
        if (($i % 2) == 0){
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilth);
        }
        else {
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilgris);
        }
    }
    $fila++;
    switch ($row_terminal["ESTADO"]) {
        case "A": {
            $estado = $alta;
            $fecha_nom = $falta;
            $fecha_val = $row_terminal["FALTA"];
            break;
        }
        case "B": {
            $estado = $baja;
            $fecha_nom = $fbaja;
            $fecha_val = $row_terminal["FBAJA"];
            break;
        }
        case "R": {
            // Se busca la incidencia
            $sql_incid = "SELECT * FROM incidencias WHERE TERMINAL = '$id' ORDER BY ID DESC";
            $res_incid = mysql_query($sql_incid) or die("Error en la consulta de Incidencia: " . mysql_error());
            $nincid = mysql_num_rows($res_incid);
            if ($nflota == 0) {
                $estado = "<p class='error'>No hay resultados en la consulta de Incidencias</p>\n";
            }
            else {
                $row_incid = mysql_fetch_array($res_incid);
                $id_incid = $row_incid["ID"];
                $estado = "$rep - Incid. $id";
                $fecha_val = $row_incid["FAVERIA"];
            }
            $fecha_nom = $frep;
            break;
        }
    }
    $fila_imp = array($estadotxt, $estado, $fecha_nom, $fecha_val);
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
        if (($i % 2) == 0){
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilth);
        }
        else {
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilborde);
        }
    }
    $fila++;
    $estilo = $estilgris;
    if ($permiso == 2) {
        $fila_imp = array('Número K', $row_terminal["NUMEROK"], 'Carpeta', $row_terminal["CARPETA"]);
        for ($i = 0; $i < count($fila_imp); $i++){
            $celdaini = $colini[$i].$fila;
            $celdafin = $colfin[$i].$fila;
            $celdamerg = "$celdaini:$celdafin";
            $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
            $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
            if (($i % 2) == 0){
                $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilth);
            }
            else {
                $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilgris);
            }
        }
        $fila++;
        $estilo = $estilborde;
    }
    $fila_imp = array($observ, $row_terminal["OBSERVACIONES"]);
    $colini = array("A","C");
    $colfin = array("B","L");
    for ($i = 0; $i < count($fila_imp); $i++){
        $celdaini = $colini[$i].$fila;
        $celdafin = $colfin[$i].$fila;
        $celdamerg = "$celdaini:$celdafin";
        $objPHPExcel->getActiveSheet()->setCellValue($celdaini, $fila_imp[$i]);
        $objPHPExcel->getActiveSheet()->mergeCells($celdamerg);
        if (($i % 2) == 0){
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilth);
        }
        else {
            $objPHPExcel->getActiveSheet()->getStyle($celdamerg)->applyFromArray($estilo);
        }
    }


    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="Terminal_COMDES-'.$idterm.'.pdf"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
    $objWriter->save('php://output');
    exit;
}
else{
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
        <p class='error'><?php echo $permno; ?></p>
    </body>
</html>
<?php
}
?>
