<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/cobdetalle_$idioma.php";
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
    $objPHPExcel->getActiveSheet()->setTitle("Cobertura_" . $thmun . 's');

    // Título de la Hoja
    $objPHPExcel->getActiveSheet()->setCellValue("A1", $h1);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');

    // Consulta de Emplazamiento:
    $sql_tbs = "SELECT * FROM emplazamientos WHERE id = " . $idemp;
    $res_tbs = mysql_query($sql_tbs) or die(mysql_error());
    $ntbs = mysql_num_rows($res_tbs);
    // Vector de provincias
    $provincias = array(
        '03' => 'Alicante/Alacant', '12' => 'Castellón/Castelló',
        '46' => 'Valencia/València', '16' => 'Cuenca',
        '43' => 'Tarragona', '44' => 'Teruel'
    );
    if ($ntbs > 0){
        $tbs = mysql_fetch_array($res_tbs);
        $nmuncob = 0;
        $sql_cob = "SELECT * FROM coberturas, municipios WHERE (coberturas.emplazamiento_id = " . $idemp . ")";
        $sql_cob .= " AND (coberturas.municipio_id = municipios.INE) ORDER BY coberturas.porcentaje DESC";
        $res_cob = mysql_query($sql_cob) or die(mysql_error());
        $nmuncob = mysql_num_rows($res_cob);

        // Datos del emplazamiento:
        $objPHPExcel->getActiveSheet()->setCellValue("A3", $h2emp);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');

        $objPHPExcel->getActiveSheet()->setCellValue('A4', $themplaza);
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', $thtitular);
        $objPHPExcel->getActiveSheet()->setCellValue('D4', $thlatitud);
        $objPHPExcel->getActiveSheet()->setCellValue('E4', $thlongitud);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E4')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValue('A5', $tbs['emplazamiento']);
        $objPHPExcel->getActiveSheet()->setCellValue('B5', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('C5', $tbs['titular']);
        $objPHPExcel->getActiveSheet()->setCellValue('D5',  $tbs['latitud']);
        $objPHPExcel->getActiveSheet()->setCellValue('E5', $tbs['longitud']);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E5')->applyFromArray($estiloCelda);

        // Municipios cubiertos:
        $objPHPExcel->getActiveSheet()->setCellValue("A7", $h2cob . ': ' . $nmuncob);
        $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A7:E7');
        $objPHPExcel->getActiveSheet()->setCellValue('A8', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('B8', $thmun);
        $objPHPExcel->getActiveSheet()->setCellValue('C8', $thpob);
        $objPHPExcel->getActiveSheet()->setCellValue('D8', $thporcent);
        $objPHPExcel->getActiveSheet()->setCellValue('E8', $thpobcob);
        $objPHPExcel->getActiveSheet()->getStyle('A8:E8')->applyFromArray($estiloTh);
        if ($nmuncob > 0){
            $relleno = FALSE;
            $muniflotas = array();
            $provflotas = array();
            for($i = 0; $i < $nmuncob; $i++){
                $muncob = mysql_fetch_array($res_cob);
                if ($muncob['porcentaje'] >= $porcmuni){
                    array_push($muniflotas, $muncob['INE']);
                }
                if (!(in_array($muncob['CPRO'], $provflotas))){
                    array_push($provflotas, $muncob['CPRO']);
                }
                $fila = 9 + $i;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $muncob['PROVINCIA']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $muncob['MUNICIPIO']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $muncob['POBLACION']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, round($muncob['porcentaje'], 2));
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, round($muncob['porcentaje'] * $muncob['POBLACION']/100));
                if ($relleno){
                    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':E' . $fila)->applyFromArray($estiloRelleno);
                }
                $relleno = !($relleno);
            }
            $objPHPExcel->getActiveSheet()->getStyle('A8:E' . $fila)->applyFromArray($estiloCelda);
        }
        else {
            $objPHPExcel->getActiveSheet()->setCellValue("A8", 'Error: ' . $errnocob);
            $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray($estiloError);
            $objPHPExcel->getActiveSheet()->mergeCells('A8:E8');
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

        // Datos del emplazamiento:
        $objPHPExcel->getActiveSheet()->setCellValue("A3", $h2emp);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');

        $objPHPExcel->getActiveSheet()->setCellValue('A4', $themplaza);
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('C4', $thtitular);
        $objPHPExcel->getActiveSheet()->setCellValue('D4', $thlatitud);
        $objPHPExcel->getActiveSheet()->setCellValue('E4', $thlongitud);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E4')->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValue('A5', $tbs['emplazamiento']);
        $objPHPExcel->getActiveSheet()->setCellValue('B5', $thprov);
        $objPHPExcel->getActiveSheet()->setCellValue('C5', $tbs['titular']);
        $objPHPExcel->getActiveSheet()->setCellValue('D5',  $tbs['latitud']);
        $objPHPExcel->getActiveSheet()->setCellValue('E5', $tbs['longitud']);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E5')->applyFromArray($estiloCelda);

        // Flotas cubiertos:
        $objPHPExcel->getActiveSheet()->setCellValue("A7", $h2flotas);
        $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->mergeCells('A7:F7');

        $fila = 9;
        $ambitos = array('AUT', 'PROV', 'LOC');
        $h4amb = array($h4aut, $h4prov, $h4local);
        $txtamb = array($txtaut, $txtprov, $txtlocal);
        $provcv = array('03', '12', '46');
        for ($j = 0; $j < count($ambitos); $j++){
            $consulta = TRUE;
            $sql_flotas = "SELECT * FROM flotas WHERE (AMBITO = '" . $ambitos[$j] . "')";
            if ($ambitos[$j] == 'PROV'){
                $nprovflotas = count($provflotas);
                if ($nprovflotas > 0){
                    if ($nprovflotas == 1){
                        $sql_flotas .= " AND (INE LIKE '" . $provflotas[0] . "%')";
                    }
                    else{
                        $sql_flotas .= " AND (";

                        for ($k = 0; $k < $nprovflotas; $k++){
                            $sql_flotas .= "(INE LIKE '" . $provflotas[$k] . "%')";
                            if ($k < ($nprovflotas - 1)){
                                $sql_flotas .= " OR ";
                            }
                        }
                        $sql_flotas .= ")";
                    }
                }
                else{
                    if (in_array($tbs['provincia'], $provcv)){
                        $sql_flotas .= " AND (INE LIKE '" . $tbs['provincia'] . "%')";
                    }
                    else{
                        $consulta = FALSE;
                    }
                }
            }
            if ($ambitos[$j] == 'LOC'){
                $nmunflotas = count($muniflotas);
                if ($nmunflotas > 0){
                    $sql_flotas .= " AND INE IN (";
                    for ($k = 0; $k < $nmunflotas; $k++){
                        $sql_flotas .= $muniflotas[$k];
                        if ($k < ($nmunflotas - 1)){
                            $sql_flotas .= ", ";
                        }
                    }
                    $sql_flotas .= " )";
                }
                else{
                    if (in_array($tbs['provincia'], $provcv)){
                        $sql_flotas .= " AND (INE = '" . $tbs['municipio_id'] . "')";
                    }
                    else{
                        $consulta = FALSE;
                    }
                }
            }
            $sql_flotas .= " ORDER BY FLOTA ASC";
            $nflotas = 0;
            if ($consulta){
                $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
                $nflotas = mysql_num_rows($res_flotas);
            }
            $h4 = $h4amb[$j];
            if ($nflotas > 0){
                $h4 .= ' - ' . $nflotas . ' ' . $txtflotas;
            }
            if (($ambitos[$j] == 'LOC') && ($nmunflotas > 0)){
                $h4 .= ' - ' . $thporcent . ' > ' . $porcmuni . ' %';
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, $h4);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila)->applyFromArray($estiloCriterio);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $fila . ':F' . $fila);
            $fila++;
            $fila_ini = $fila;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $fila, 'Flota');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $fila, $thacro);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $fila, $thcont);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $fila, $thcargo);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $fila, $thmail);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $fila, $thoficial);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($estiloTh);
            $fila++;
            $relleno = FALSE;
            if ($nflotas > 0){
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
        $objPHPExcel->getActiveSheet()->setCellValue("A3", 'Error: ' . $errnotbs);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloError);
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
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
else {
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
