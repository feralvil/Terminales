<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/grupos_$idioma.php";
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

if ($permiso > 1){
    // Consulta a la base de datos - Tabla Flotas
    $sql_grupos = "SELECT * FROM grupos ORDER BY GISSI ASC";
    $res_grupos = mysql_query($sql_grupos) or die(mysql_error());
    $ngrupos = mysql_num_rows($res_grupos);

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
    $estiloCentro = array(
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
    );

    // Fijamos como hoja activa la primera (Datos de la Flota):
    $objPHPExcel->setActiveSheetIndex(0);

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$txtpag &P de &N");

    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle($nreg . "_COMDES");

    // Título de la Hoja
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $titulo);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:D1');

    // Número de Grupos:
    $objPHPExcel->getActiveSheet()->setCellValue("A3", "Total: " . $ngrupos . " " . $nreg);
    $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
    $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');

    // Encabezado de la Tabla:
    $objPHPExcel->getActiveSheet()->setCellValue("A5", "GISSI");
    $objPHPExcel->getActiveSheet()->setCellValue("B5", $thmnemo);
    $objPHPExcel->getActiveSheet()->setCellValue("C5", $thtipo);
    $objPHPExcel->getActiveSheet()->setCellValue("D5", $thdesc);
    $objPHPExcel->getActiveSheet()->getStyle('A5:D5')->applyFromArray($estiloTh);

    if ($ngrupos > 0){
        for ($i = 0; $i < $ngrupos; $i++){
            $fila = 6 + $i;
            $grupo = mysql_fetch_array($res_grupos);
            $objPHPExcel->getActiveSheet()->setCellValue("A" . $fila, $grupo['GISSI']);
            $objPHPExcel->getActiveSheet()->setCellValue("B" . $fila, $grupo['MNEMONICO']);
            $objPHPExcel->getActiveSheet()->setCellValue("C" . $fila, $grupo['TIPO']);
            $objPHPExcel->getActiveSheet()->setCellValue("D" . $fila, $grupo['DESCRIPCION']);
            if (($i % 2) == 1){
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':D' . $fila)->applyFromArray($estiloRelleno);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle('A5:D' . $fila)->applyFromArray($estiloCelda);
    }
    else{
        $objPHPExcel->getActiveSheet()->setCellValue("A3" , $errnogrupos);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:D3');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloError);
    }

    // Auto-ajuste de columna
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel2007)
    $fichero = $nreg . "_COMDES";

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
            <p class='error'><?php echo $errnoperm; ?></p>
        </body>
    </html>
<?php
}
?>
