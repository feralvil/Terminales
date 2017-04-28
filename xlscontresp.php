<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotascont_$idioma.php";
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

if ($permiso > 1){
    $sql_flotas = "SELECT flotas.ID, flotas.FLOTA, flotas.ACRONIMO, flotas.FORMCONT, organizaciones.ORGANIZACION";
    $sql_flotas .= " FROM flotas, organizaciones WHERE (flotas.ORGANIZACION = organizaciones.ID)";
    $critsel = 0;
    if (($idflota != '') && ($idflota != "00")) {
        $sql_flotas = $sql_flotas . " AND (flotas.ID = $idflota)";
        $critsel++;
        $sql_flota = 'SELECT * FROM flotas WHERE (ID = ' . $idflota . ')';
        $res_flota = mysql_query($sql_flota) or die(mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota > 0){
            $flotasel = mysql_fetch_array($res_flota);
            $flotatxt = $flotasel['FLOTA'];
        }
    }
    if (($idorg != '') && ($idorg != "00")) {
        $sql_flotas = $sql_flotas . " AND (flotas.ORGANIZACION = $idorg)";
        $critsel++;
        $sql_organiza = 'SELECT * FROM organizaciones WHERE (ID = ' . $idorg . ')';
        $res_organiza = mysql_query($sql_organiza) or die(mysql_error());
        $norganiza = mysql_num_rows($res_organiza);
        if ($norganiza > 0){
            $orgsel = mysql_fetch_array($res_organiza);
            $orgtxt = $orgsel['ORGANIZACION'];
        }
    }
    if (($idprov != '') && ($idprov != "00")) {
        $sql_flotas = $sql_flotas . " AND (flotas.INE LIKE '$idprov%')";
        $critsel++;
        $provincias = array(
            '03' => 'Alacant/Alicante', '12' => 'Castelló/Castellón', '46' => 'València/Valencia'
        );
        $provtxt = $provincias[$idprov];
    }
    if (($formcont != '') && ($formcont != "00")) {
        $sql_flotas = $sql_flotas . ' AND (flotas.FORMCONT = "' . $formcont . '")';
        $critsel++;
        $formarray = array('SI' => 'Sí', 'NO' => 'No');
        $formtxt = $formarray[$formcont];
    }
    $sql_flotas .= " ORDER BY organizaciones.ORGANIZACION, flotas.FLOTA ASC";
    $res_flotas = mysql_query($sql_flotas) or die(mysql_error() . ': ' . $sql_flotas);
    $nflotas = mysql_num_rows($res_flotas);

    // Incrementamos el tamaño de la memoria disponible:
    ini_set("memory_limit","64M");

    // Clases para generar el Excel
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
    $objPHPExcel->getProperties()->setKeywords("Oficina COMDES Flotas");
    $objPHPExcel->getProperties()->setCategory("Flotas COMDES");

    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

    // Fijamos como hoja activa la primera y fijamos el título:
    $objPHPExcel->setActiveSheetIndex(0);

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

    // Fijamos como hoja activa la primera (Datos de la Flota):
    $objPHPExcel->setActiveSheetIndex(0);

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

    // Fijamos el título de la Hoja:
    $fichero = $txtflotas . "_COMDES";
    $objPHPExcel->getActiveSheet()->setTitle($fichero);

    // Título de la Hoja
    $fila = 1;
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $h1);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':F' . $fila);

    // criterios de Selección:
    $fila = $fila + 2;
    if ($critsel > 0){
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $fila, $criterios);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':C' . $fila);
        $fila++;
        if (($idorg != '') && ($idorg != "00")) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila,  $thorg . ': ' . $orgtxt);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $fila)->applyFromArray($estiloCriterio);
            $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila . ':D' . $fila);
            $fila++;
        }
        if (($idflota != '') && ($idflota != "00")) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila,  'Flota: ' . $flotatxt);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $fila)->applyFromArray($estiloCriterio);
            $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila . ':D' . $fila);
            $fila++;
        }
        if (($idprov != '') && ($idprov != "00")) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila,  $txtprov . ': ' . $provtxt);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $fila)->applyFromArray($estiloCriterio);
            $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila . ':D' . $fila);
            $fila++;
        }
        if (($formcont != '') && ($formcont != "00")) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila,  $txtcontof . ': ' . $formtxt);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $fila)->applyFromArray($estiloCriterio);
            $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila . ':D' . $fila);
            $fila++;
        }
    }


    // Número de Flotas
    if ($critsel > 0){
        $fila++;
    }
    $objPHPExcel->getActiveSheet()->setCellValue("A" . $fila, $h4res . ": " . $nflotas . ' ' . $txtflotas);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloCriterio);
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':F' . $fila);
    $fila = $fila + 2;

    // Imprimimos las flotas:
    if ($nflotas > 0){
        $fila_inicio = $fila;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $thorg);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, 'Flota');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $thoficial);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $thresp);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $thcargo);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $thcorreo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($estiloTh);
        $relleno = false;
        for ($i = 0; $i < $nflotas; $i++){
            $fila++;
            $flota = mysql_fetch_array($res_flotas);
            $idflota = $flota['ID'];
            $sql_contflota = 'SELECT * FROM contactos_flotas WHERE (FLOTA_ID = ' . $idflota . ') AND (ROL = "RESPONSABLE")';
            $res_contflota = mysql_query($sql_contflota) or die(mysql_error());
            $ncontflota = mysql_num_rows($res_contflota);
            if ($ncontflota > 0){
                $contflota = mysql_fetch_array($res_contflota);
                $idcontacto = $contflota['CONTACTO_ID'];
                if ($idcontacto > 0){
                    $sql_contacto = 'SELECT * FROM contactos WHERE (ID = ' . $idcontacto . ')';
                    $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto > 0){
                        $contacto = mysql_fetch_array($res_contacto);
                    }
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $flota['ORGANIZACION']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $flota['FLOTA']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $flota['FORMCONT']);
            if ($ncontacto > 0){
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $contacto['NOMBRE']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $contacto['CARGO']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $contacto['MAIL']);
            }
            else{
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $txtnocont);
                $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila . ':F' . $fila);
            }
            if($relleno){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($estiloRelleno);
            }
            $relleno = !($relleno);
        }
        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicio . ':F' . $fila)->applyFromArray($estiloCelda);
    }
    // Auto-ajuste de columna
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);// Fijamos como hoja activa la primera y fijamos el título:
    $objPHPExcel->getActiveSheet()->setTitle($txtflotas . '_COMDES');

    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
    header('Content-Disposition: attachment;filename="' . $txtflotas . '_COMDES.xlsx"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}
?>
