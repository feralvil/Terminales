<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/carpetas_$idioma.php";
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
    $objPHPExcel->getProperties()->setCategory("Terminales COMDES");

    // Fijamos los estilos generales de la hoja:
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
    $estiloTitulo = array(
        'font' => array('bold' => true, 'size' => 12),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
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
    
    // Encabezado:
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); 
    
    $sql_flotas = "SELECT * FROM flotas ORDER BY flotas.FLOTA ASC";
    $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de flotas:" . mysql_error());
    $nflotas = mysql_num_rows($res_flotas);
    if ($nflotas > 0){
        $fila_inicio = 3;
        $fila = 3;
        $objPHPExcel->getActiveSheet()->setCellValue("A$fila", 'ID');
        $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $thflota);
        $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $thacro);
        $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $thcarpetas);
        $nmaxc = 0;        
        $colinicio = 4;
        for ($i = 0; $i < $nflotas ; $i++){
            $fila = 4 + $i;
            $flota = mysql_fetch_array($res_flotas);
            $idflota = $flota['ID'];
            $sql_carpetas = "SELECT DISTINCT CARPETA FROM terminales WHERE (FLOTA = ".$flota['ID'].")";
            $sql_carpetas .= " ORDER BY CARPETA ASC";
            $res_carpetas = mysql_query($sql_carpetas) or die("Error en la consulta de Carpetas" . mysql_error());
            $ncarpetas = mysql_num_rows($res_carpetas);
            $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $flota['ID']);
            $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $flota['FLOTA']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $flota['ACRONIMO']);
            $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $ncarpetas);
            if ($ncarpetas > 0){
                if ($ncarpetas > $nmaxc){
                    $nmaxc = $ncarpetas;
                }
                for ($j = 0; $j < $ncarpetas; $j++){
                    $columna = $colinicio + $j;
                    $carpeta = mysql_fetch_array($res_carpetas); 
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila, $carpeta['CARPETA']);
                }
            }
        }
        for ($j = 0; $j < $nmaxc; $j++){
            $columna = $colinicio + $j;
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($columna, $fila_inicio, $thcarpeta);
        }
        
        // Fijamos la primera hoja como la activa, al abrir Excel
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        $fichero = $nom_fichero;

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fichero.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
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
        <h1>Permiso denegado</h1>
        <p class="error">No tiene permiso para acceder a esta información</p>
    </body>
<?php
}
?>