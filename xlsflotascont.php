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
    $fichero = "plantillas/empresas_contactos.xls";
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
    $objPHPExcel->getProperties()->setKeywords("Oficina COMDES Terminales");
    $objPHPExcel->getProperties()->setCategory("Terminales COMDES");

    // Fijamos los estilos generales de la hoja:
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
    // Fondo Gris de fila de datos
    $estilgris = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                'argb' => 'FFF0F2F5'
            ),
        ),
    );
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

    // Fijamos como hoja activa la primera (Datos de la Flota):
    $objPHPExcel->setActiveSheetIndex(0);

    // Tamaño de papel (A4) y orientación (Apaisado)
    $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

    // Pie de Página
    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter("&C$pgtxt &P de &N");
    
    $sql_flotas = "SELECT * FROM flotas ORDER BY flotas.FLOTA ASC";
    $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de flotas:" . mysql_error());
    $nflotas = mysql_num_rows($res_flotas);
    if ($nflotas > 0){
        $fila = 2;
        for ($i = 0; $i < $nflotas ; $i++){
            $flota = mysql_fetch_array($res_flotas);
            $contactos_flota = array();
            $contactos = array(
                'RESPONSABLE' => $flota['RESPONSABLE'], 'CONTACTO1' => $flota['CONTACTO1'], 
                'CONTACTO2' => $flota['CONTACTO2'], 'CONTACTO3' => $flota['CONTACTO3'], 
                'INCID1' => $flota['INCID1'], 'INCID2' => $flota['INCID2'], 
                'INCID3' => $flota['INCID3'], 'INCID4' => $flota['INCID4']
            );
            
            // Limpiamos el array
            foreach ($contactos as $tipocont => $idcont){
                if ($idcont > 0){
                    if (!in_array($idcont, $contactos_flota)){
                        $contactos_flota[$tipocont] = $idcont;
                    }
                }
            }
            
            foreach ($contactos_flota as $tipocont => $idcont){
                $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idcont;
                $res_contacto = mysql_db_query($base_datos, $sql_contacto) or die("Error en la consulta de Contacto:" . mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
                if ($ncontacto > 0){
                    $contacto = mysql_fetch_array($res_contacto);
                }
                $objPHPExcel->getActiveSheet()->setCellValue("A$fila", ($fila - 1));
                $objPHPExcel->getActiveSheet()->setCellValue("B$fila", $flota['ID']);
                $objPHPExcel->getActiveSheet()->setCellValue("C$fila", $flota['ACRONIMO']);
                $objPHPExcel->getActiveSheet()->setCellValue("D$fila", $contacto['ID']);
                $objPHPExcel->getActiveSheet()->setCellValue("E$fila", $contacto['NOMBRE']);
                $objPHPExcel->getActiveSheet()->setCellValue("F$fila", $contacto['CARGO']);
                $fila++;
            }
        }
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