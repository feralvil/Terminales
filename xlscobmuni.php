<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/cobdetmuni_$idioma.php";
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

    $estiloError = array(
        'font' => array('bold' => true, 'size' => 11, 'color' => array('argb' => 'FFFF0000',),),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT),
    );

    // Fijamos como hoja activa la primera (Datos de la Flota):
    $objPHPExcel->setActiveSheetIndex(0);

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

    // Fijamos el título de la Hoja:
    $objPHPExcel->getActiveSheet()->setTitle("Cobertura_TBS");

    // Título de la Hoja
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');

    // Consulta de Municipio:
    $sql_muni = "SELECT * FROM municipios WHERE INE = " . $idmuni;
    $res_muni = mysql_query($sql_muni) or die(mysql_error());
    $nmuni = mysql_num_rows($res_muni);
    $provincias = array(
        '03' => 'Alicante/Alacant', '12' => 'Castellón/Castelló',
        '46' => 'Valencia/València', '16' => 'Cuenca',
        '43' => 'Tarragona', '44' => 'Teruel'
    );

    if ($nmuni > 0){
        $muni = mysql_fetch_array($res_muni);

        // Datos del municipio
        $objPHPExcel->getActiveSheet()->setCellValue("A3", $h2muni);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', $thine);
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', $thmuni);
        $objPHPExcel->getActiveSheet()->setCellValue('D4', $thpob);
        $objPHPExcel->getActiveSheet()->getStyle('A4:D4')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A5', $muni['INE'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('B5',  $muni['PROVINCIA']);
        $objPHPExcel->getActiveSheet()->setCellValue('C5',  $muni['MUNICIPIO']);
        $objPHPExcel->getActiveSheet()->setCellValue('D5',  $muni['POBLACION']);
        $objPHPExcel->getActiveSheet()->getStyle('A4:D5')->applyFromArray($estiloCelda);

        // Datos de TBS
        $sql_muncob = "SELECT coberturas.porcentaje, emplazamientos.* FROM coberturas, emplazamientos";
        $sql_muncob .= " WHERE (coberturas.municipio_id = " . $idmuni . ") AND (coberturas.emplazamiento_id = emplazamientos.id)";
        $sql_muncob .= " ORDER BY coberturas.porcentaje DESC";
        $res_muncob = mysql_query($sql_muncob) or die(mysql_error());
        $nmuncob = mysql_num_rows($res_muncob);
        $objPHPExcel->getActiveSheet()->setCellValue("A7", $h2tbs . ': ' . $nmuncob . ' TBS');
        $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A7:F7');
        if ($nmuncob > 0){
            $objPHPExcel->getActiveSheet()->setCellValue('A8', $themplaza);
            $objPHPExcel->getActiveSheet()->setCellValue('B8', $thtitular);
            $objPHPExcel->getActiveSheet()->setCellValue('C8', $thlatitud);
            $objPHPExcel->getActiveSheet()->setCellValue('D8', $thlongitud);
            $objPHPExcel->getActiveSheet()->setCellValue('E8', $thporcent);
            $objPHPExcel->getActiveSheet()->setCellValue('F8', $thpobcob);
            $objPHPExcel->getActiveSheet()->getStyle('A8:F8')->applyFromArray($estiloTh);
            $fila = $fila_ini = 8;
            $porcob = 0;
            $relleno = FALSE;
            for ($i = 0; $i < $nmuncob; $i++){
                $fila++;
                $muncob = mysql_fetch_array($res_muncob);
                $porcob += $muncob['porcentaje'];
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $muncob['emplazamiento']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $muncob['titular']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $muncob['latitud']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $muncob['longitud']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, round($muncob['porcentaje'], 2));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, round($muncob['porcentaje'] * $muni['POBLACION']/100));
                if ($relleno){
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($estiloRelleno);
                }
                $relleno = !($relleno);
            }
            $fila++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $thtotales);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloTh);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':D' . $fila);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, round($porcob, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, round($porcob * $muni['POBLACION']/100));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_ini . ':F' . $fila)->applyFromArray($estiloCelda);
        }
        else{
            $objPHPExcel->getActiveSheet()->setCellValue("A8", 'Error: ' . $errnotbs);
            $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloError);
            $objPHPExcel->getActiveSheet()->mergeCells('A8:F8');
        }

        // Ajustamos los anchos:
        $colmax = $objPHPExcel->getActiveSheet()->getHighestColumn();
        $maxcol = PHPExcel_Cell::columnIndexFromString($colmax);
        for ($i = 0; $i < $maxcol; $i++){
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        // Fijamos como hoja activa la segunda (Flotas):
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);

        // Tamaño de papel (A4) y orientación (Apaisado)
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

        // Pie de Página
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");

        // Fijamos el título de la Hoja:
        $objPHPExcel->getActiveSheet()->setTitle("Cobertura_" . $txtflotas);

        // Título de la Hoja
        $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');

        // Datos del municipio
        $objPHPExcel->getActiveSheet()->setCellValue("A3", $h2muni);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', $thine);
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', $thmuni);
        $objPHPExcel->getActiveSheet()->setCellValue('D4', $thpob);
        $objPHPExcel->getActiveSheet()->getStyle('A4:D4')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A5', $muni['INE'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('B5',  $muni['PROVINCIA']);
        $objPHPExcel->getActiveSheet()->setCellValue('C5',  $muni['MUNICIPIO']);
        $objPHPExcel->getActiveSheet()->setCellValue('D5',  $muni['POBLACION']);
        $objPHPExcel->getActiveSheet()->getStyle('A4:D5')->applyFromArray($estiloCelda);

        // Datos de las flotas:
        // Flotas cubiertos:
        $objPHPExcel->getActiveSheet()->setCellValue("A7", $h2flotas);
        $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A7:F7');

        $fila = 9;
        $ambitos = array('AUT', 'PROV', 'LOC');
        $h4amb = array($h4aut, $h4prov, $h4local);
        $txtamb = array($txtaut, $txtprov, $txtlocal);
        for ($j = 0; $j < count($ambitos); $j++){
            $sql_flotas = "SELECT * FROM flotas WHERE (AMBITO = '" . $ambitos[$j] . "')";
            if ($ambitos[$j] == 'PROV'){
                $idprov = $muni['CPRO'];
                $sql_flotas .= " AND (INE LIKE '" . $idprov[0] . "%')";
            }
            if ($ambitos[$j] == 'LOC'){
                $sql_flotas .= " AND (INE = '" . $idmuni . "')";
            }
            $sql_flotas .= " ORDER BY FLOTA ASC";
            $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
            $nflotas = mysql_num_rows($res_flotas);
            $h4 = $h4amb[$j] . ' - ' . $nflotas . ' ' . $txtflotas;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $h4);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloCriterio);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':F' . $fila);
            $fila++;
            $fila_ini = $fila;
            if ($nflotas > 0){
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, 'Flota');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $thacro);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $thcont);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $thcargo);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $thmail);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $thoficial);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($estiloTh);
                $fila++;
                $relleno = FALSE;
                for ($i = 0; $i < $nflotas; $i++){
                    $flota = mysql_fetch_array($res_flotas);
                    $sql_cont = "SELECT contactos.NOMBRE, contactos.CARGO, contactos.MAIL, contactos_flotas.ROL FROM contactos, contactos_flotas";
                    $sql_cont .= " WHERE (contactos_flotas.FLOTA_ID = " . $flota['ID'] . ") AND (contactos.ID = contactos_flotas.CONTACTO_ID)";
                    $sql_cont .= " AND (contactos_flotas.ROL = 'CONT24H')";
                    $res_cont = mysql_query($sql_cont) or die(mysql_error());
                    $ncont = mysql_num_rows($res_cont);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $flota['FLOTA']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $flota['ACRONIMO']);
                    if ($ncont > 0){
                        $contacto = mysql_fetch_array($res_cont);
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $contacto['NOMBRE']);
                        $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $contacto['CARGO']);
                        $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $contacto['MAIL']);
                        $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $flota['FORMCONT']);
                    }
                    else{
                        $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $txtnocont);
                        $objPHPExcel->getActiveSheet()->mergeCells('C' . $fila . ':F' . $fila);
                    }
                    if ($relleno){
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($estiloRelleno);
                    }
                    $relleno = !($relleno);
                    $fila++;
                }
            }
            else{
                $errnoflota = sprintf($txtnoflota, $txtamb[$j]);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, 'Error: ' . $errnoflota);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloError);
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':F' . $fila);
                $fila++;
            }
            $fila_fin = $fila - 1;
            $objPHPExcel->getActiveSheet()->getStyle('A'. $fila_ini . ':F' . $fila_fin)->applyFromArray($estiloCelda);
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
        // Datos del emplazamiento:
        $objPHPExcel->getActiveSheet()->setCellValue("A3", 'Error: ' . $errnomuni);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloError);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
    }

    // Generamos el fichero:
    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: Application/vnd.openxmlformats-officedocument.SpreadsheetML.Sheet');
    header('Content-Disposition: attachment;filename="' . $txtfichero . '_' . $idmuni . '.xlsx"');
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
