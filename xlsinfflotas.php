<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotainf_$idioma.php";
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
    $sql_flotas = "SELECT flotas.ID, flotas.FLOTA, flotas.ACRONIMO, organizaciones.ORGANIZACION";
    $sql_flotas .= " FROM flotas, organizaciones WHERE (flotas.ORGANIZACION = organizaciones.ID)";
    if (($prov != '') && ($prov != "00")) {
        $sql_flotas = $sql_flotas . " AND (flotas.INE LIKE '$prov%')";
    }
    $sql_flotas = $sql_flotas . " ORDER BY organizaciones.ORGANIZACION ASC, flotas.FLOTA ASC";
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
    $objPHPExcel->getActiveSheet()->setTitle("Informe_Flotas");

    // Título de la Hoja
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $titulo);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:I1');

    // Encabezado de la Tabla:
    $objPHPExcel->getActiveSheet()->setCellValue("A3", $thorg);
    $objPHPExcel->getActiveSheet()->setCellValue("B3", $thflota);
    $objPHPExcel->getActiveSheet()->setCellValue("C3", $thacro);
    $objPHPExcel->getActiveSheet()->setCellValue("D3", $thterm);
    $objPHPExcel->getActiveSheet()->setCellValue("E3", $thtbase);
    $objPHPExcel->getActiveSheet()->setCellValue("F3", $thtmov);
    $objPHPExcel->getActiveSheet()->setCellValue("G3", $thport);
    $objPHPExcel->getActiveSheet()->setCellValue("H3", $thdesp);
    $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($estiloTh);

    // Tipos de termninales
    $tterm = array(0, 0, 0, 0, 0);
    $tipos = array("%", "F", "M%", "P%", "D");
    $columna = array('D', 'E', 'F', 'G', 'H');

    // Contador de organizaciones
    $norganiza = 0;
    $orgact = "Nada";
    for ($i = 0; $i < $nflotas; $i++){
        $fila = 4 + $i;
        $flota = mysql_fetch_array($res_flotas);
        if ($flota['ORGANIZACION'] != $orgact){
            $norganiza++;
        }
        $orgact = $flota['ORGANIZACION'];
        $objPHPExcel->getActiveSheet()->setCellValue("A" . $fila, $flota['ORGANIZACION']);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $fila, $flota['FLOTA']);
        $objPHPExcel->getActiveSheet()->setCellValue("C" . $fila, $flota['ACRONIMO']);
        //datos de la tabla Terminales
        $nterm = array(0, 0, 0, 0, 0);
        for ($j = 0; $j < count($tipos); $j++){
            $sql_term = "SELECT * FROM terminales WHERE (FLOTA = '" . $flota['ID'] . "') AND (TIPO LIKE '" . $tipos[$j] . "')";
            $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminales" . mysql_error());
            $nterm = mysql_num_rows($res_term);
            $tterm[$j] = $tterm[$j] + $nterm;
            $objPHPExcel->getActiveSheet()->setCellValue($columna[$j] . $fila, $nterm);
        }
        if (($i % 2) == 1){
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($estiloRelleno);
        }
    }
    $objPHPExcel->getActiveSheet()->getStyle('A3:H' . $fila)->applyFromArray($estiloCelda);
    $fila++;
    $fila++;
    $objPHPExcel->getActiveSheet()->setCellValue("A" . $fila, $totalorg . " - " . $norganiza);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->setCellValue("B" . $fila, $totales . " - " . $nflotas);
    $objPHPExcel->getActiveSheet()->getStyle('B' . $fila)->applyFromArray($estiloTh);
    $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila . ':C' . $fila);
    for ($j = 0; $j < count($tipos); $j++){
        $objPHPExcel->getActiveSheet()->setCellValue($columna[$j] . $fila, $tterm[$j]);
    }
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($estiloCelda);

    // Auto-ajuste de columna
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    //$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel2007)
    $fichero = "Informe_Flotas";

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
