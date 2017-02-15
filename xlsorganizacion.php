<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organizadet_$idioma.php";
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
    $objPHPExcel->getProperties()->setCategory("Terminales COMDES");

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

    $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
    $res_org = mysql_query($sql_org) or die("Error en la consulta de Organización: " . mysql_error());
    $norg = mysql_num_rows($res_org);
    if ($norg > 0) {
        $row_org = mysql_fetch_array($res_org);
        // Consulta de Municipio
        $ineorg = $row_org['INE'];
        $sql_munorg = "SELECT * FROM municipios WHERE INE = '$ineorg'";
        $res_munorg = mysql_query($sql_munorg) or die("Error en la consulta de Municipio de la Organización" . mysql_error());
        $nmunorg = mysql_num_rows($res_munorg);
        if ($nmunorg > 0) {
            $row_munorg = mysql_fetch_array($res_munorg);
        }
        // Título de la Hoja
        $objPHPExcel->getActiveSheet()->setCellValue("A1", $thorganiza . " " . $row_org['ORGANIZACION']);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');

        // Completamos los datos de la Organización:
        $objPHPExcel->getActiveSheet()->setCellValue("A3", $h2organiza);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->setCellValue("A4", $thorganiza);
        $objPHPExcel->getActiveSheet()->setCellValue("B4", $thdomicilio);
        $objPHPExcel->getActiveSheet()->setCellValue("C4", $thcp);
        $objPHPExcel->getActiveSheet()->setCellValue("D4", $thciudad);
        $objPHPExcel->getActiveSheet()->setCellValue("E4", $thprovincia);
        $objPHPExcel->getActiveSheet()->getStyle("A4:E4")->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValue("A5", $row_org['ORGANIZACION']);
        $objPHPExcel->getActiveSheet()->setCellValue("B5", $row_org['DOMICILIO']);
        $objPHPExcel->getActiveSheet()->setCellValue("C5", $row_org['CP']);
        $objPHPExcel->getActiveSheet()->setCellValue("D5", $row_munorg['MUNICIPIO']);
        $objPHPExcel->getActiveSheet()->setCellValue("E5", $row_munorg['PROVINCIA']);
        $objPHPExcel->getActiveSheet()->getStyle("A4:E5")->applyFromArray($estiloCelda);

        // Consulta de Responsable:
        $idresp = $row_org['RESPONSABLE'];
        $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idresp;
        $res_contacto = mysql_db_query($base_datos, $sql_contacto) or die("Error en la consulta de Contacto:" . mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $contacto = mysql_fetch_array($res_contacto);
        }
        // Datos del Responsable:
        $objPHPExcel->getActiveSheet()->setCellValue("A7", $h2resp);
        $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->setCellValue("A8", $thnomresp);
        $objPHPExcel->getActiveSheet()->setCellValue("B8", $thcargo);
        $objPHPExcel->getActiveSheet()->setCellValue("C8", $thtelef);
        $objPHPExcel->getActiveSheet()->setCellValue("D8", $thmail);
        $objPHPExcel->getActiveSheet()->getStyle("A8:D8")->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->setCellValue("A9", $contacto['NOMBRE']);
        $objPHPExcel->getActiveSheet()->setCellValue("B9", $contacto['CARGO']);
        $objPHPExcel->getActiveSheet()->setCellValue("C9", $contacto['TELEFONO']);
        $objPHPExcel->getActiveSheet()->setCellValue("D9", $contacto['MAIL']);
        $objPHPExcel->getActiveSheet()->getStyle("A8:D9")->applyFromArray($estiloCelda);

        // Datos de las Flotas:
        $objPHPExcel->getActiveSheet()->setCellValue("A11", $h2flotas);
        $objPHPExcel->getActiveSheet()->getStyle('A11')->applyFromArray($estiloCriterio);
        $objPHPExcel->getActiveSheet()->setCellValue("A12", $thflota);
        $objPHPExcel->getActiveSheet()->setCellValue("B12", $thacro);
        $objPHPExcel->getActiveSheet()->setCellValue("C12", $thnterm);
        $objPHPExcel->getActiveSheet()->setCellValue("D12", $thnflota);
        $objPHPExcel->getActiveSheet()->getStyle("A12:D12")->applyFromArray($estiloTh);
        $sql_flotas = "SELECT * FROM flotas WHERE (ORGANIZACION = $idorg) ORDER BY flotas.FLOTA ASC";
        $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de flotas:" . mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
        $flotas = array();
        $terminales = array();
        $ntermorg = 0;
        for ($i = 0; $i < $nflotas; $i++){
            $fila = 13 + $i;
            $flota = mysql_fetch_array($res_flotas);
            array_push($flotas, $flota);
            $idflota = $flota['ID'];
            $sql_term = "SELECT * FROM terminales WHERE FLOTA = " . $flota['ID'];
            $res_term = mysql_db_query($base_datos, $sql_term) or die("Error en la consulta de Terminales:" . mysql_error());
            $nterm = mysql_num_rows($res_term);
            $termflota = array();
            for ($j = 0; $j < $nterm; $j++){
                $terminal = mysql_fetch_array ($res_term);
                $termflota[] = $terminal;
            }
            $terminales[$flota['ID']] = $termflota;
            $ntermorg = $ntermorg + $nterm;
            $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $flota['FLOTA']);
            $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $flota['ACRONIMO']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $nterm);
            $objPHPExcel->getActiveSheet()->setCellValue("D$fila", ($i + 1));
            if (($fila % 2) == 0){
                $objPHPExcel->getActiveSheet()->getStyle("A$fila:D$fila")->applyFromArray($estiloRelleno);
            }
        }
        $objPHPExcel->getActiveSheet()->getStyle("A12:D$fila")->applyFromArray($estiloCelda);
        $fila = $fila+2;
        $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $thtotterm);
        $objPHPExcel->getActiveSheet()->getStyle("A$fila")->applyFromArray($estiloTh);
        $objPHPExcel->getActiveSheet()->mergeCells("A$fila:B$fila");
        $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $ntermorg);
        $objPHPExcel->getActiveSheet()->getStyle("A$fila:C$fila")->applyFromArray($estiloCelda);

        // Auto-ajuste de columna
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

        // Fijamos el título de la Hoja:
        $objPHPExcel->getActiveSheet()->setTitle($thorganiza);

        $hojaActiva = 1;
        foreach ($flotas as $flota){
            // Agregamos la hoja:
            $objPHPExcel->createSheet();

            // La Fjamos como activa:
            $objPHPExcel->setActiveSheetIndex($hojaActiva);

            // Fijamos el título de la Hoja:
            $nomHoja = "Flota_".$flota['ACRONIMO'];
            $objPHPExcel->getActiveSheet()->setTitle($nomHoja);

            // Título de la Hoja
            $objPHPExcel->getActiveSheet()->setCellValue("A1", "Flota " . $flota['FLOTA']);
            $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->mergeCells('A1:J1');

            // Terminales
            $termflota = $terminales[$flota['ID']];
            $objPHPExcel->getActiveSheet()->setCellValue("A3", $thnterm . ": " . count($termflota));
            $objPHPExcel->getActiveSheet()->getStyle('A3')->applyFromArray($estiloCriterio);
            $objPHPExcel->getActiveSheet()->setCellValue("A5", 'Marca');
            $objPHPExcel->getActiveSheet()->setCellValue("B5", $thmodelo);
            $objPHPExcel->getActiveSheet()->setCellValue("C5", $thtipo);
            $objPHPExcel->getActiveSheet()->setCellValue("D5", $thproveedor);
            $objPHPExcel->getActiveSheet()->setCellValue("E5", 'ISSI');
            $objPHPExcel->getActiveSheet()->setCellValue("F5", 'TEI');
            $objPHPExcel->getActiveSheet()->setCellValue("G5", $thmnemo);
            $objPHPExcel->getActiveSheet()->setCellValue("H5", 'Carpeta');
            $objPHPExcel->getActiveSheet()->setCellValue("I5", $thllamind);
            $objPHPExcel->getActiveSheet()->setCellValue("J5", 'DOTS');
            $objPHPExcel->getActiveSheet()->getStyle("A5:J5")->applyFromArray($estiloTh);
            $fila = $fila_inicio = 5;
            foreach ($termflota as $terminal){
                $fila++;
                $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $terminal['MARCA']);
                $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $terminal['MODELO']);
                $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $terminal['TIPO']);
                $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $terminal['PROVEEDOR']);
                $objPHPExcel->getActiveSheet()->setCellValue("E$fila", $terminal['ISSI']);
                $objPHPExcel->getActiveSheet()->getCell("F$fila")->setValueExplicit($terminal['TEI'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValue("G$fila", $terminal['MNEMONICO']);
                $objPHPExcel->getActiveSheet()->setCellValue("H$fila", $terminal['CARPETA']);
                $llamind = "NO";
                if ($terminal['SEMID'] == 'SI'){
                    $llamind = 'S';
                    if ($terminal['DUPLEX'] == 'SI'){
                        $llamind .= " + D";
                    }
                }
                else{
                    if ($terminal['DUPLEX'] == 'SI'){
                        $llamind = "D";
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue("I$fila", $llamind);
                $objPHPExcel->getActiveSheet()->setCellValue("J$fila", $terminal['DOTS']);
                if (($fila % 2) == 1){
                    $objPHPExcel->getActiveSheet()->getStyle("A$fila:J$fila")->applyFromArray($estiloRelleno);
                }
            }
            $objPHPExcel->getActiveSheet()->getStyle("A$fila_inicio:J$fila")->applyFromArray($estiloCelda);

            // Auto-ajuste de columna
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);

            // Incrementamos el número de hoja:
            $hojaActiva++;
        }
    }
    else{
        // Mensaje de error:
        $objPHPExcel->getActiveSheet()->setCellValue("A1", "Error: " . $errnoorg);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
    }

    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel5)
    $fichero = "Organizacion";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$fichero.'.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
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
            <p class="error"><?php echo $errnoperm;?></p>
        </body>
    </html>
<?php
}
?>
