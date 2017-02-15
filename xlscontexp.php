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
    
    // Importamos las variables de formulario:
    import_request_variables("p", "");
    
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

    // Estilos para la hoja:
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
    $objPHPExcel->getActiveSheet()->setTitle('Contactos_COMDES');

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");
    
    // Encabezado:
    $objPHPExcel->getActiveSheet()->setCellValue("A1", "Contactos de las Flotas COMDES");
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($estiloTitulo);
    $objPHPExcel->getActiveSheet()->mergeCells('A1:F1'); 
    
    
    
    $sql_flotas = "SELECT * FROM flotas WHERE 1";
    if ((isset($idflota)) && ($idflota > 0)){
        $sql_flotas .= " AND (flotas.ID = ". $idflota .")";
    }
    $sql_flotas .= " ORDER BY flotas.FLOTA ASC";
    $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de flotas:" . mysql_error());
    $nflotas = mysql_num_rows($res_flotas);
    
    $fila_inicio = 4;
    if ($nflotas > 0){
        $fila = $fila_inicio;
        $objPHPExcel->getActiveSheet()->setCellValue("A$fila", "Acrónimo");
        $objPHPExcel->getActiveSheet()->setCellValue("B$fila", "Flota");
        $objPHPExcel->getActiveSheet()->setCellValue("C$fila", "Rol");    
        $objPHPExcel->getActiveSheet()->setCellValue("D$fila", "Nombre");
        $objPHPExcel->getActiveSheet()->setCellValue("E$fila", "Cargo");
        $objPHPExcel->getActiveSheet()->setCellValue("F$fila", "E-mail");
        $objPHPExcel->getActiveSheet()->getStyle("A$fila:F$fila")->applyFromArray($estiloTh);
        for ($i = 0; $i < $nflotas ; $i++){
            $flota = mysql_fetch_array($res_flotas);
            $idflota = $flota['ID'];
            $sql_contflota = "SELECT * FROM contactos_flotas WHERE (FLOTA_ID = " . $idflota .")";
            $res_contflota = mysql_db_query($base_datos, $sql_contflota) or die("Error en la consulta de Contactos de Flota:" . mysql_error() . $sql_contflota);
            $ncontflota = mysql_num_rows($res_contflota);
            if ($ncontflota > 0){
                $contactos_flota = array();
                $responsables = array();
                $operativos = array();
                for ($j = 0; $j < $ncontflota; $j++){
                    $contflota = mysql_fetch_array($res_contflota);
                    if (($contflota['ROL'] == 'RESPONSABLE') || ($contflota['ROL'] == 'OPERATIVO')){
                        $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $contflota['CONTACTO_ID'];
                        $res_contacto = mysql_db_query($base_datos, $sql_contacto) or die("Error en la consulta de Contacto:" . mysql_error());
                        $ncontacto = mysql_num_rows($res_contacto);
                        if ($ncontacto > 0){
                            $contacto = mysql_fetch_array($res_contacto);
                            if ($contflota['ROL'] == 'RESPONSABLE'){
                                $responsables[$contflota['CONTACTO_ID']] = $contacto;
                            }
                            elseif ($contflota['ROL'] == 'OPERATIVO'){
                                $operativos[$contflota['CONTACTO_ID']] = $contacto;
                            }
                        }
                    }
                }
                if (count($responsables) > 0){
                    $contactos_flota['RESPONSABLE'] = $responsables;
                }
                if (count($operativos) > 0){
                    $contactos_flota['OPERATIVO'] = $operativos;
                }
            }
            $idcontactos = array();
            foreach ($contactos_flota as $tipocont => $tipo_contactos){
                foreach ($tipo_contactos as $idcontacto => $contacto){
                    if (!in_array($idcontacto, $idcontactos)){
                        $idcontactos[] = $idcontacto;
                        $fila++;
                        $objPHPExcel->getActiveSheet()->setCellValue("A$fila", $flota['ACRONIMO']);                
                        $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $flota['FLOTA']);
                        $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $tipocont);
                        $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $contacto['NOMBRE']);
                        $objPHPExcel->getActiveSheet()->setCellValue("E$fila", $contacto['CARGO']);
                        $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $contacto['MAIL']);                
                        // Relleno de celda:
                        if (($fila % 2) == 1){
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':'.'F'.$fila)->applyFromArray($estiloRelleno);
                        }
                    }
                }
            }
        }
        // Bordes de Celda: 
        $objPHPExcel->getActiveSheet()->getStyle('A'.$fila_inicio.':'.'F'.$fila)->applyFromArray($estiloCelda);
        
        // Auto-ajuste de columna
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        
        // Fijamos la primera hoja como la activa, al abrir Excel
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        $fichero = "Flotas_Contactos";

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