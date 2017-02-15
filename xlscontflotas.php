<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contflotas_$idioma.php";
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

// Clases para generar el Excel
/** Error reporting */
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
/** PHPExcel */
require_once 'Classes/PHPExcel.php';

if ($permiso > 1){
    // Consulta a la base de datos - Tabla Flotas
    $sql_flotas = "SELECT flotas.*, organizaciones.ORGANIZACION AS NOMORG";
    $sql_flotas .= " FROM flotas, organizaciones WHERE (flotas.ORGANIZACION = organizaciones.ID)";
    if (($prov != '') && ($prov != "00")) {
        $sql_flotas = $sql_flotas . " AND (flotas.INE LIKE '$prov%')";
    }
    if (($organiza != '') && ($organiza != "00")) {
        $sql_flotas .= " AND (flotas.ORGANIZACION = $organiza)";
    }
    if (!(empty($idflota))){
        $sql_flotas .= " AND flotas.ID IN (";
        for ($i = 0; $i < count($idflota); $i++){
            $sql_flotas .= $idflota[$i];
            if ($i < (count($idflota) - 1)){
                $sql_flotas .= ", ";
            }
        }
        $sql_flotas .= ")";
    }
    $sql_flotas .= " ORDER BY organizaciones.ORGANIZACION ASC, flotas.FLOTA ASC";
    $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
    $nflotas = mysql_num_rows($res_flotas);

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
    $objPHPExcel->getProperties()->setCategory("Flotas COMDES");

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

    if ($nflotas > 25){
        $nflotas = 25;
    }
    for ($j = 0; $j < $nflotas; $j++){
        // Fijamos como hoja activa la primera (Datos de la Flota):
        $objPHPExcel->setActiveSheetIndex($j);
        $flota = mysql_fetch_array($res_flotas);
        $idflota = $flota['ID'];
        // Organización
        $idorg = $flota['ORGANIZACION'];
        $sql_organizacion = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
        $res_organizacion = mysql_query($sql_organizacion) or die("Error en la consulta de flota: ".mysql_error());
        $norganiza = mysql_num_rows($res_organizacion);

        // Tamaño de papel (A4) y orientación (Apaisado)
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Fijamos el título de la Hoja:
        $objPHPExcel->getActiveSheet()->setTitle($flota['ACRONIMO']);

        // Título de la Hoja
        $objPHPExcel->getActiveSheet()->setCellValue("A1", 'FLOTA' . ' ' . $flota['FLOTA']);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:M1');

        // Fecha:
        $fecha = date('d-m-Y');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', $thfecha);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $fecha);
        $objPHPExcel->getActiveSheet()->mergeCells('B3:C3');
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($estiloCelda);

        // Organización:
        $objPHPExcel->getActiveSheet()->setCellValue('A5', $thorg);
        $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A5:B5');
        $objPHPExcel->getActiveSheet()->mergeCells('C5:M5');
        $objPHPExcel->getActiveSheet()->getStyle('A5:M5')->applyFromArray($estiloCelda);
        $objPHPExcel->getActiveSheet()->setCellValue('A7', $h2org . ':');
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
        $objPHPExcel->getActiveSheet()->setCellValue('A10', $thdir);
        $objPHPExcel->getActiveSheet()->getStyle('A10')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A10:B10');
        $objPHPExcel->getActiveSheet()->mergeCells('C10:L10');
        $objPHPExcel->getActiveSheet()->setCellValue('A11', 'CP-' . $thmuni);
        $objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A11:B11');
        $objPHPExcel->getActiveSheet()->mergeCells('C11:G11');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', $thprov);
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
            $organizacion = mysql_fetch_array($res_organizacion);
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $organizacion['ORGANIZACION']);
            $idresporg = $organizacion['RESPONSABLE'];
            if ($idorg > 0){
                $sql_resporg = "SELECT * FROM contactos WHERE ID = " . $idresporg;
                $res_resporg = mysql_query($sql_resporg) or die("Error en la consulta del responsable de organización: ".mysql_error());
                $nresporg = mysql_num_rows($res_resporg);
                if ($nresporg > 0){
                    $resporg = mysql_fetch_array($res_resporg);
                    $objPHPExcel->getActiveSheet()->setCellValue('C8', $resporg['NOMBRE']);
                    $objPHPExcel->getActiveSheet()->setCellValue('J8', $resporg['NIF']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C9', $resporg['CARGO']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C10', $organizacion['DOMICILIO']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C12', $resporg['TELEFONO']);
                    $objPHPExcel->getActiveSheet()->setCellValue('I12', $resporg['MAIL']);
                }
                $idmunorg = $organizacion['INE'];
                $sql_munorg = "SELECT * FROM municipios WHERE INE = " . $idmunorg;
                $res_munorg = mysql_query($sql_munorg) or die("Error en la consulta del municipio de organización: ".mysql_error());
                $nmunorg = mysql_num_rows($res_munorg);
                if ($nmunorg > 0){
                    $munorg = mysql_fetch_array($res_munorg);
                    $objPHPExcel->getActiveSheet()->setCellValue('C11', $organizacion['CP'] . '-' . $munorg['MUNICIPIO']);
                    $objPHPExcel->getActiveSheet()->setCellValue('J11', $munorg['PROVINCIA']);
                }
            }
        }

        // Responsable de Flota:
        $objPHPExcel->getActiveSheet()->setCellValue('A14', $h2flota . ':');
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
        $objPHPExcel->getActiveSheet()->setCellValue('A17', $thdir);
        $objPHPExcel->getActiveSheet()->getStyle('A17')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A17:B17');
        $objPHPExcel->getActiveSheet()->mergeCells('C17:L17');
        $objPHPExcel->getActiveSheet()->setCellValue('A18', 'CP-' . $thmuni);
        $objPHPExcel->getActiveSheet()->getStyle('A18')->applyFromArray($estiloHeader);
        $objPHPExcel->getActiveSheet()->mergeCells('A18:B18');
        $objPHPExcel->getActiveSheet()->mergeCells('C18:G18');
        $objPHPExcel->getActiveSheet()->setCellValue('H18', $thprov);
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
        // Buscamos el Responsable
        $sql_contresp = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'RESPONSABLE')";
        $res_contresp = mysql_query($sql_contresp) or die(mysql_error());
        $ncontresp = mysql_num_rows($res_contresp);
        // Consulta a la base de datos - Tabla Municipios
        $idmunflo = $flota["INE"];
        if ($idmunflo <> $idmunorg){
            $sql_munflo = "SELECT * FROM municipios WHERE INE='$idmunflo'";
            $res_munflo = mysql_query($sql_munflo) or die("Error en la consulta de Municipio de la Flota" . ': ' . mysql_error());
            $nmunflo = mysql_num_rows($res_munflo);
            if ($nmunflo > 0) {
                $munflo = mysql_fetch_array($res_munflo);
            }
        }
        else{
            $munflo = $munorg;
        }
        if ($ncontresp > 0){
            $contresp = mysql_fetch_array($res_contresp);
            $idrespflo = $contresp['CONTACTO_ID'];
            if ($idrespflo <> $idresporg){
                $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idrespflo";
                $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
                if ($ncontacto > 0){
                    $respflo = mysql_fetch_array($res_contacto);
                }
            }
            else{
                $ncontacto = 1;
                $respflo = $resporg;
            }
            if ($ncontacto > 0){
                $objPHPExcel->getActiveSheet()->setCellValue('C15', $respflo["NOMBRE"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J15', $respflo["NIF"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C16', $respflo["CARGO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C17', $flota["DOMICILIO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C18', $flota["CP"] . ' - ' . $munflo["MUNICIPIO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('J18', $munflo["PROVINCIA"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C19', $respflo["TELEFONO"]);
                $objPHPExcel->getActiveSheet()->setCellValue('I19', $respflo["MAIL"]);
            }
        }

        // Contactos Operativos:
        $objPHPExcel->getActiveSheet()->setCellValue('A21', $h2op . ':');
        $objPHPExcel->getActiveSheet()->getStyle('A21')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->setCellValue('A22', $thnombre);
        $objPHPExcel->getActiveSheet()->mergeCells('A22:C22');
        $objPHPExcel->getActiveSheet()->setCellValue('D22', 'NIF');
        $objPHPExcel->getActiveSheet()->mergeCells('D22:E22');
        $objPHPExcel->getActiveSheet()->setCellValue('F22', $thcargo);
        $objPHPExcel->getActiveSheet()->mergeCells('F22:H22');
        $objPHPExcel->getActiveSheet()->setCellValue('I22', $thmail);
        $objPHPExcel->getActiveSheet()->mergeCells('I22:K22');
        $objPHPExcel->getActiveSheet()->setCellValue('L22', $thtelef);
        $objPHPExcel->getActiveSheet()->mergeCells('L22:M22');
        $objPHPExcel->getActiveSheet()->getStyle('A22:M22')->applyFromArray($estiloTh);
        $sql_contop = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'OPERATIVO')";
        $res_contop = mysql_query($sql_contop) or die(mysql_error());
        $ncontop = mysql_num_rows($res_contop);
        $fila = $fila_op = 22;
        for ($i = 0 ; $i < $ncontop; $i++){
            $fila = $fila_op + 1 + $i;
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
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_op . ':M' . $fila)->applyFromArray($estiloCelda);

        // Contactos Técnicos:
        $fila_tec = $fila + 2;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila_tec, $h2tec);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_tec)->applyFromArray($estiloCriterio);
        $fila_tec++;
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
        $sql_conttec = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'TECNICO')";
        $res_conttec = mysql_query($sql_conttec) or die(mysql_error());
        $nconttec = mysql_num_rows($res_conttec);
        $fila = $fila_tec;
        for ($i = 0 ; $i < $nconttec; $i++){
            $fila = $fila_tec + $i + 1;
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
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_tec . ':M' . $fila)->applyFromArray($estiloCelda);

        // Contactos 24x7:
        $fila_24h = $fila + 2;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila_24h, $h224h . ':');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_24h)->applyFromArray($estiloCriterio);
        $fila_24h++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila_24h, $thnom24h);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila_24h . ':E' . $fila_24h);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila_24h, $thmail);
        $objPHPExcel->getActiveSheet()->mergeCells('F' . $fila_24h . ':J' . $fila_24h);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $fila_24h, $thtelef);
        $objPHPExcel->getActiveSheet()->mergeCells('K' . $fila_24h . ':M' . $fila_24h);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_24h . ':M' . $fila_24h)->applyFromArray($estiloTh);
        $fila = $fila_24h;
        $sql_cont24h = "SELECT * FROM contactos_flotas  WHERE (FLOTA_ID = $idflota) AND (ROL = 'CONT24H')";
        $res_cont24h = mysql_query($sql_cont24h) or die(mysql_error());
        $ncont24h = mysql_num_rows($res_cont24h);
        for ($i = 0 ; $i < $ncont24h; $i++){
            $fila = $fila_24h + $i + 1;
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
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_24h . ':M' . $fila)->applyFromArray($estiloCelda);

        if ($j < $nflotas - 1){
            $objPHPExcel->createSheet();
        }
    }
    // Fijamos como activa la primera
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel2007)
    $fichero = "Contactos_Flotas";

    header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
    header('Content-Disposition: attachment;filename="'.$fichero.'.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}
else{
?>
    <!DOCTYPE html>
    <html>
        <head>
            <title><?php echo $titulo; ?></title>
            <link rel="StyleSheet" type="text/css" href="estilo.css">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <?php
            // Si la sesión de Joomla ha caducado, recargamos la página principal
            if ($flota_usu == 0){
            ?>
                <script type="text/javascript">
                    window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
                </script>
            <?php
            }
            ?>
        </head>
        <body>
            <h1><?php echo $h1perm ?></h1>
            <p class='error'><?php echo $permno; ?></p>
        </body>
    </html>
<?php
}
?>
