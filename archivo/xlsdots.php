<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/dotsterm_$idioma.php";
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
import_request_variables("gp", "");

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

if ($permiso = 2){
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

    // Leemos el fichero de la plantilla:
    $fichero = "plantillas/vacio.xls";
    $tipoFich = PHPExcel_IOFactory::identify($fichero);
    $objReader = PHPExcel_IOFactory::createReader($tipoFich);
    // Cargamos el fichero
    try {
        $objPHPExcel = $objReader->load($fichero);
    }
    catch(Exception $e){
        die("$errtemp: ".$e->getMessage());
    }

    // Set properties
    $objPHPExcel->getProperties()->setCreator("Oficina COMDES");
    $objPHPExcel->getProperties()->setLastModifiedBy("Oficina COMDES");
    $objPHPExcel->getProperties()->setTitle("Oficina COMDES");
    $objPHPExcel->getProperties()->setSubject("Oficina COMDES");
    $objPHPExcel->getProperties()->setDescription("Oficina COMDES");
    $objPHPExcel->getProperties()->setKeywords("Oficina COMDES Terminales DOTS");
    $objPHPExcel->getProperties()->setCategory("Terminales COMDES");

    // Fijamos como hoja activa la primera y fijamos el título:
    $objPHPExcel->setActiveSheetIndex(0);

    // Fijamos los estilos generales de la hoja:
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
    // Estilo de error
    $estilerr = array(
        'font' => array(
            'bold' => true,
            'color' => array(
                'argb' => 'FFFF0000',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    // Estilo del borde de tabla
    $estilborde =  array(
        'borders' =>  array(
            'outline' => array(
                'style' => PHPExcel_Style_Border::BORDER_THICK,
                'color' => array('argb' => 'FF000000')
            )
        )
    );
    // Fondo Gris de fila de datos
    $estiltdgris = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'argb' => 'FFF0F2F5'
            ),
        ),
        'borders' =>  array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => 'FF000000')
            )
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ),
    );
    // Celda Normal
    $estiltd = array(
        'borders' =>  array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => 'FF000000')
            )
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ),
    );
     //datos de la tabla Terminales - Obtenemos las marcas
    $sql_marca = "SELECT DISTINCT MARCA FROM terminales ORDER BY MARCA ASC";
    $res_marca = mysql_query($sql_marca) or die("Error en la consulta de Marca" . mysql_error());
    $nmarca = mysql_num_rows($res_marca);
    $totmarca = $nmarca;
    if ($nmarca > 0){
        $hoja = 0;
        for ($i = 0; $i < $nmarca; $i++){
            $row_marca = mysql_fetch_array($res_marca);
            $marca = $row_marca[0];
            // Datos de la tabla Terminales - Obtenemos los modelos
            $sql_modelo = "SELECT DISTINCT MODELO FROM terminales WHERE MARCA='$marca' ORDER BY MODELO ASC";
            $res_modelo = mysql_query($sql_modelo) or die("Error en la consulta de Modelo" . mysql_error());
            $nmodelo = mysql_num_rows($res_modelo);
            if ($nmodelo > 0){
                for ($j = 0; $j < $nmodelo; $j++){  
                    $row_modelo = mysql_fetch_array($res_modelo);
                    $modelo = $row_modelo[0];
                    $sql_terminal = "SELECT terminales.ISSI, flotas.ACRONIMO, terminales.MNEMONICO, terminales.PROVEEDOR ";
                    $sql_terminal .= "FROM terminales, flotas  WHERE (terminales.MARCA = '".$marca."') ";
                    $sql_terminal .= "AND (terminales.MODELO = '".$modelo."') AND (terminales.FLOTA = flotas.ID) ";
                    $sql_terminal .= "ORDER BY flotas.FLOTA ASC, terminales.ISSI ASC";
                    $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta (parcial) de Terminal: " . mysql_error());
                    $nterminal = mysql_num_rows($res_terminal);
                    if ($nterminal > 0){
                        if ($hoja > 0){
                            $worksheet1 = $objPHPExcel->createSheet($hoja);
                        }
                        // Fijamos la Hoja Activa
                        $objPHPExcel->setActiveSheetIndex($hoja);

                        // Título de la Hoja
                        $objPHPExcel->getActiveSheet()->setTitle("$marca-$modelo");

                        // Tamaño de papel (A4) y orientación (Vertical)
                        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
                        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

                        // Pie de Página
                        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C".$pgtxt." &P de &N");

                        // Imprimir los terminales
                        for ($fila = 1; $fila <= $nterminal; $fila++){
                            $row_terminal = mysql_fetch_array($res_terminal);
                            $tupla = strtoupper($row_terminal["ACRONIMO"]."-".$row_terminal["PROVEEDOR"]."-".$row_terminal["MNEMONICO"]);
                            $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $row_terminal["ISSI"]);
                            $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $tupla);$estilo = $estiltd;
                            if (($fila % 2) == 0){
                                $estilo = $estiltdgris;
                            }
                            $rango = "A$fila:B$fila";
                            $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilo);
                            //$objPHPExcel->getActiveSheet()->mergeCells("B$fila:L$fila");
                        }
                        $rango = "A1:B$nterminal";
                        $objPHPExcel->getActiveSheet()->getStyle($rango)->applyFromArray($estilborde);
                        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                        $hoja++;
                    }
                }
            }
        }
    }
    else{
        $objPHPExcel->getActiveSheet()->removeRow(3, 31);
        $objPHPExcel->getActiveSheet()->setCellValue("A4", $errnomarca);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->applyFromArray($estilerr);
        $objPHPExcel->getActiveSheet()->mergeCells('A4:O4');
    }

    // Fijamos la primera hoja como la activa, al abrir Excel
    $objPHPExcel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel5)
    $fichero = "COMDES_DOTS";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$fichero.'.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}
else{
?>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <title><?php echo $titulo; ?></title>
            <link rel="StyleSheet" type="text/css" href="estilo.css">
        </head>
        <body>
            <h1><?php echo $h1perm ?></h1>
            <p class='error'><?php echo $permno ?></p>
        </body>
    </html>
<?php
}
?>
