<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/coberturas_$idioma.php";
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

    $estiloVertical = array(
        'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
    );

    $estiloError = array(
        'font' => array('bold' => true, 'size' => 11, 'color' => array('argb' => 'FFFF0000',),),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
    );

    // Incrementamos la memoria:
    ini_set('memory_limit', "128M");

    // Fijamos como hoja activa la primera (Datos de la Flota):
    $objPHPExcel->setActiveSheetIndex(0);

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle($tabtbs);

    // Título de la Hoja
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');

    // Consulta de TBS
    $sql_tbs = "SELECT * FROM emplazamientos ORDER BY emplazamiento ASC";
    $res_tbs = mysql_query($sql_tbs) or die(mysql_error());
    $ntbs = mysql_num_rows($res_tbs);

    if ($ntbs > 0){
        $objPHPExcel->getActiveSheet()->setCellValue("A3", $h4res . ' - ' . $ntbs . ' ' . $txtemp);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:H3');

        // Datos de las TBS:
        $fila = 5;
        $provincias = array(
            '03' => 'Alicante/Alacant', '12' => 'Castellón/Castelló',
            '46' => 'Valencia/València', '16' => 'Cuenca',
            '43' => 'Tarragona', '44' => 'Teruel'
        );
        for ($i = 0; $i < $ntbs; $i++){
            $tbs = mysql_fetch_array($res_tbs);
            $nmuncob = 0;
            $idemp = $tbs['id'];
            $sql_cob = "SELECT * FROM coberturas, municipios WHERE (coberturas.emplazamiento_id = " . $idemp . ")";
            $sql_cob .= " AND (coberturas.municipio_id = municipios.INE) ORDER BY coberturas.porcentaje DESC";
            $res_cob = mysql_query($sql_cob) or die(mysql_error());
            $ncob = mysql_num_rows($res_cob);
            $fila_initbs = $fila;
            $objPHPExcel->getActiveSheet()->setCellValue('A'. $fila, $thdatemp);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':C' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('D'. $fila, $thmuncob . ' - ' . $ncob);
            $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila . ':H' . $fila);
            $fila++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'. $fila, $themplaza);
            $objPHPExcel->getActiveSheet()->setCellValue('B'. $fila, $thprov);
            $objPHPExcel->getActiveSheet()->setCellValue('C'. $fila, $thtitular);
            $objPHPExcel->getActiveSheet()->setCellValue('D'. $fila, $thprov);
            $objPHPExcel->getActiveSheet()->setCellValue('E'. $fila, $thmun);
            $objPHPExcel->getActiveSheet()->setCellValue('F'. $fila, $thpob);
            $objPHPExcel->getActiveSheet()->setCellValue('G'. $fila, $thporcent);
            $objPHPExcel->getActiveSheet()->setCellValue('H'. $fila, $thpobcob);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_initbs. ':H' . $fila)->applyFromArray($estiloTh);
            $fila++;
            $fila_inimun = $fila;
            $objPHPExcel->getActiveSheet()->setCellValue('A'. $fila, $tbs['emplazamiento']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'. $fila, $provincias[$tbs['provincia']]);
            $objPHPExcel->getActiveSheet()->setCellValue('C'. $fila, $tbs['titular']);
            if ($ncob > 0){
                for ($j = 0; $j < $ncob; $j++){
                    $fila = $fila_inimun + $j;
                    $muncob = mysql_fetch_array($res_cob);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'. $fila, $muncob['PROVINCIA']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'. $fila, $muncob['MUNICIPIO']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'. $fila, $muncob['POBLACION']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'. $fila, round($muncob['porcentaje'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('H'. $fila, round($muncob['porcentaje'] * $muncob['POBLACION']/100));
                }
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila_inimun . ':A' . $fila);
                $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila_inimun . ':B' . $fila);
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $fila_inimun . ':C' . $fila);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inimun . ':C' . $fila_inimun)->applyFromArray($estiloVertical);
            }
            else{
                $objPHPExcel->getActiveSheet()->setCellValue('D'. $fila, $errnocob);
                $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila . ':H' . $fila);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_initbs . ':H' . $fila)->applyFromArray($estiloCelda);
            $fila++;
            $fila++;
        }
        // Ajustamos los anchos:
        $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
        for ($i = 0; $i < $maxcol; $i++){
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        // Página de Municipios:
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);

        // Tamaño de papel (A4) y orientación (Apaisado)
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Pie de Página
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

        // Fijamos el título de la Hoja:
        $objPHPExcel->getActiveSheet()->setTitle($tabmuni);

        // Título de la Hoja
        $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');

        $sql_muni = "SELECT * FROM municipios ORDER BY CPRO ASC, MUNICIPIO ASC";
        $res_muni = mysql_query($sql_muni) or die(mysql_error());
        $nmuni = mysql_num_rows($res_muni);

        $objPHPExcel->getActiveSheet()->setCellValue("A3", $h4res . ' - ' . $nmuni . ' ' . $thmuni . 's');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:H3');

        $fila = 5;
        for ($i = 0; $i < $nmuni; $i++){
            $muni = mysql_fetch_array($res_muni);
            $ntbscob = 0;
            $idmuni = $muni['INE'];
            $sql_muncob = "SELECT coberturas.porcentaje, emplazamientos.* FROM coberturas, emplazamientos";
            $sql_muncob .= " WHERE (coberturas.municipio_id = " . $idmuni . ") AND (coberturas.emplazamiento_id = emplazamientos.id)";
            $sql_muncob .= " ORDER BY coberturas.porcentaje DESC";
            $res_muncob = mysql_query($sql_muncob) or die(mysql_error());
            $nmuncob = mysql_num_rows($res_muncob);
            $fila_inimun = $fila;
            $objPHPExcel->getActiveSheet()->setCellValue('A'. $fila, $thdatmuni);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':D' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('E'. $fila, $thnumtbs . ' - ' . $nmuncob);
            $objPHPExcel->getActiveSheet()->mergeCells('E' . $fila . ':H' . $fila);
            $fila++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'. $fila, 'INE');
            $objPHPExcel->getActiveSheet()->setCellValue('B'. $fila, $thprov);
            $objPHPExcel->getActiveSheet()->setCellValue('C'. $fila, $thmun);
            $objPHPExcel->getActiveSheet()->setCellValue('D'. $fila, $thpob);
            $objPHPExcel->getActiveSheet()->setCellValue('E'. $fila, $themplaza);
            $objPHPExcel->getActiveSheet()->setCellValue('F'. $fila, $thtitular);
            $objPHPExcel->getActiveSheet()->setCellValue('G'. $fila, $thporcent);
            $objPHPExcel->getActiveSheet()->setCellValue('H'. $fila, $thpobcob);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inimun . ':H' . $fila)->applyFromArray($estiloTh);
            $fila++;
            $fila_initbs = $fila;;
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'. $fila, $muni['INE'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('B'. $fila, $muni['PROVINCIA']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'. $fila, $muni['MUNICIPIO']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'. $fila, $muni['POBLACION']);
            if ($nmuncob > 0){
                $pormun = 0;
                for ($j = 0; $j < $nmuncob; $j++){
                    $cobmuni = mysql_fetch_array($res_muncob);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'. $fila, $cobmuni['emplazamiento']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'. $fila, $cobmuni['titular']);
                    $objPHPExcel->getActiveSheet()->setCellValue('G'. $fila, round($cobmuni['porcentaje'], 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('H'. $fila, round($cobmuni['porcentaje'] * $muni['POBLACION']/100));
                    $pormun = $pormun + $cobmuni['porcentaje'];
                    $fila++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('E'. $fila, $thtotales);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $fila)->applyFromArray($estiloTh);
                $objPHPExcel->getActiveSheet()->mergeCells('E' . $fila . ':F' . $fila);
                $objPHPExcel->getActiveSheet()->setCellValue('G'. $fila, round($pormun, 2));
                $objPHPExcel->getActiveSheet()->setCellValue('H'. $fila, round($pormun * $muni['POBLACION']/100));
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila_initbs . ':A' . $fila);
                $objPHPExcel->getActiveSheet()->mergeCells('B' . $fila_initbs . ':B' . $fila);
                $objPHPExcel->getActiveSheet()->mergeCells('C' . $fila_initbs . ':C' . $fila);
                $objPHPExcel->getActiveSheet()->mergeCells('D' . $fila_initbs . ':D' . $fila);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_initbs . ':D' . $fila_initbs)->applyFromArray($estiloVertical);
            }
            else{
                $objPHPExcel->getActiveSheet()->setCellValue('E'. $fila, $errnocobtbs);
                $objPHPExcel->getActiveSheet()->mergeCells('E' . $fila . ':H' . $fila);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inimun . ':H' . $fila)->applyFromArray($estiloCelda);
            $fila++;
            $fila++;
        }

        // Ajustamos los anchos:
        $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
        for ($i = 0; $i < $maxcol; $i++){
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
        }




    }
    else{
        $objPHPExcel->getActiveSheet()->setCellValue("A3", 'Error: ' . $errnotbs);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloError);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:H3');
    }




    // Generamos el fichero:
    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
    header('Content-Disposition: attachment;filename="' . $txtfichero . '.xlsx"');
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
            <h1><?php echo $h1perm; ?></h1>
            <p class='error'><?php echo $errnoperm; ?></p>
        </body>
    </html>
<?php
}
?>
