<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organiza_$idioma.php";
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
    // CONSULTAS DE FLOTAS:
    $sql_org = "SELECT * FROM organizaciones ORDER BY ORGANIZACION ASC";
    $res_org = mysql_query($sql_org) or die("Error en la consulta de Organizaciones: " . mysql_error());
    $norg = mysql_num_rows($res_org);


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

    // Fijamos como hoja activa la primera (Datos de la Flota):
    $objPHPExcel->setActiveSheetIndex(0);

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

    // Fijamos el título de la Hoja:
    $fichero = $nomFichero . "_COMDES";
    $objPHPExcel->getActiveSheet()->setTitle($fichero);

    // Título de la Hoja
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');

    // Número de Organizaciones
    $objPHPExcel->getActiveSheet()->setCellValue("A3", $txtnorg . ": " . $norg);
    $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
    $objPHPExcel->getActiveSheet()->mergeCells('A3:D3');

    if ($norg > 0){
        $objPHPExcel->getActiveSheet()->setCellValue('A5', "INE-ORG");
        $objPHPExcel->getActiveSheet()->setCellValue('B5', $txtprov . " " . $thorganiza);
        $objPHPExcel->getActiveSheet()->setCellValue('C5', $txtmuni . " " . $thorganiza);
        $objPHPExcel->getActiveSheet()->setCellValue('D5', $thorganiza);
        $objPHPExcel->getActiveSheet()->setCellValue('E5', "INE-FLOTA");
        $objPHPExcel->getActiveSheet()->setCellValue('F5', $txtprov . " Flota");
        $objPHPExcel->getActiveSheet()->setCellValue('G5', $txtmuni . " Flota");
        $objPHPExcel->getActiveSheet()->setCellValue('H5', "Flota");
        $objPHPExcel->getActiveSheet()->getStyle('A5:H5')->applyFromArray($estiloTh);
        $fila = 5;
        $relleno = FALSE;
        for ($i = 0; $i < $norg; $i++){
            $organizacion = mysql_fetch_array($res_org);
            $sql_muniorg =  "SELECT * FROM municipios WHERE INE = '" . $organizacion['INE'] . "'";
            $res_muniorg = mysql_query($sql_muniorg) or die("Error en la consulta del Municipio de la Organización: " . mysql_error());
            $nmuniorg = mysql_num_rows($res_muniorg);
            if ($nmuniorg > 0){
                $muni_org = mysql_fetch_array($res_muniorg);
            }
            $sql_flotas = "SELECT * FROM flotas WHERE ORGANIZACION = '" . $organizacion['ID'] . "' ORDER BY FLOTA ASC";
            $res_flotas = mysql_query($sql_flotas) or die("Error en la consulta de Flotas de la Organización: " . mysql_error());
            $nflotas = mysql_num_rows($res_flotas);
            if ($nflotas > 0){
                for ($j = 0; $j < $nflotas; $j++){
                    $flota =  mysql_fetch_array($res_flotas);
                    $sql_muniflo =  "SELECT * FROM municipios WHERE INE = '" . $flota['INE'] . "'";
                    $res_muniflo = mysql_query($sql_muniflo) or die("Error en la consulta del Municipio de la Flota: " . mysql_error());
                    $nmuniflo = mysql_num_rows($res_muniflo);
                    if ($nmuniflo > 0){
                        $muni_flo = mysql_fetch_array($res_muniflo);
                    }
                    $fila++;
                    $objPHPExcel->getActiveSheet()->getCell('A' . $fila)->setValueExplicit($organizacion["INE"], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $muni_org['PROVINCIA']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $muni_org['MUNICIPIO']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $organizacion['ORGANIZACION']);
                    $objPHPExcel->getActiveSheet()->getCell('E' . $fila)->setValueExplicit($flota["INE"], PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $muni_flo['PROVINCIA']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G' . $fila, $muni_flo['MUNICIPIO']);
                    $objPHPExcel->getActiveSheet()->setCellValue('H' . $fila, $flota['FLOTA']);
                }
            }
            else{
                $fila++;
                $objPHPExcel->getActiveSheet()->getCell('A' . $fila)->setValueExplicit($organizacion["INE"], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $muni_org['PROVINCIA']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $muni_org['MUNICIPIO']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $organizacion['ORGANIZACION']);
            }
            if ($relleno){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($estiloRelleno);
            }
            $relleno = !($relleno);

        }
        // Auto-ajuste de columna
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:H' . $fila)->applyFromArray($estiloCelda);
    }
    else{
        // Mensaje de error:
        $objPHPExcel->getActiveSheet()->setCellValue("A5", "Error: " . $noreg);
        $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A5:D5');
    }

    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel2007)
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
        <title>Relación de Contactos y Flotas</title>
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
        <h1><?php echo $h1perm; ?></h1>
        <p class="error"><?php echo $permno;?></p>
    </body
<?php
}
?>
