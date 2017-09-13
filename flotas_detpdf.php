<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotasdetexp_$idioma.php";
include ($lang);

// Conexión a la BBDD:
require_once 'conectabbdd.php';

// Obtención del usuario
require_once 'autenticacion.php';

// Clases para generar el Excel
/** Error reporting */
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');

/** Clases TCPDF */
require_once 'tcpdf/tcpdf.php';

// Extender la clase TCPDF para crear una cabecera y un pie de página propios
class MYPDF extends TCPDF {
    var $pagina = "";
    var $titulo = "";

    //Cabecera
    public function Header() {
        // Logo
        $this->Image('imagenes/comdes2.png', 20, 5, 30);
        // Establecemos la fuente y colores
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('helvetica', 'B', 12);
        // Nos desplazamos a a la derecha
        $this->Cell(20);

        // Espacio en Blanco: Determinamos si es página Vertical u Horizontal
        $ancho = 130;
        if ($this->CurOrientation == "L"){
            $ancho = 210;
        }
        $this->Cell($ancho, 10, $this->titulo, 0, 0, 'C');

        // Logo 2
        $this->Image('imagenes/logo_chap.png', '', '', 40);
        // Salto de línea
        $this->Ln();
        $this->Cell(0, 0, '', 'T');
    }

    // Pie de página
    public function Footer() {
        // Posición at 1.5 cm del fin de página
        $this->SetY(-15);
        // Establecemos la fuente y colores
        $this->SetDrawColor(0, 0, 0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', 'B', 8);
        // Número de Página
        $this->Cell(0, 10, $this->pagina . ' ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 'T', 0, 'C');
    }
}

// crear nuevo documento
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información de documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Oficina COMDES');
$pdf->SetTitle($titulo);
$pdf->SetSubject($titulo);
$pdf->SetKeywords('COMDES, Flota');

// Márgenes
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


// Salto automático de página
$pdf->SetAutoPageBreak(TRUE, 15);

// Factor de Escala de las imágenes
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Establecemos la fuente por defecto
$pdf->SetFont('helvetica', '', 10);
// Título
$pdf->titulo = $titulo;
$pdf->pagina = $txtpagina;
// Añadir una página
$pdf->AddPage();

// Permisos de Usuario:
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    if (isset($_POST['idflota'])){
        if ($flota_usu == $_POST['idflota']){
            $permiso = 1;
        }
    }
}
$nflota = 0;
if ($permiso > 0){
    // Consultas a la BBDD
    require_once 'sql/flotas_detexportar.php';
    if ($nflota > 0){
        ini_set('memory_limit', "256M");
        // Cabecera:
        $h1txt = '<h1>' . $h1 . ' ' .$flota['FLOTA'] . '</h1>';
        $pdf->writeHTML($h1txt, true, true, true, false, '');
        $pdf->Ln(5);

        // Fecha
        $fecha = date('d-m-Y');
        // Tabla con la fecha:
        $tablahtml = '<table border = "1">';
        $tablahtml .= '<tr>';
        $tablahtml .= '<th style="font-weight:bold;width:25mm;background-color:#CCCCCC;">' . $thfecha . '</th>';
        $tablahtml .= '<td style="width:35mm;">' . $fecha . '</td>';
        $tablahtml .= '</tr>';
        $tablahtml .= '</table>';
        $pdf->writeHTML($tablahtml, true, true, true, false, '');
        $pdf->Ln(5);

        // Tabla con la organización:
        $tablahtml = '<table border = "1">';
        $tablahtml .= '<tr>';
        $tablahtml .= '<th style="font-weight:bold;width:50mm;background-color:#CCCCCC;">' . $thorganiza . '</th>';
        $tablahtml .= '<td style="width:220mm;">' . $organiza['ORGANIZACION'] . '</td>';
        $tablahtml .= '</tr>';
        $tablahtml .= '</table>';
        $pdf->writeHTML($tablahtml, true, true, true, false, '');
        $pdf->Ln(5);

        // Responsable de la Organización:
        $h2cont = '<h2>' . $txtresporg . '</h2>';
        $pdf->writeHTML($h2cont, true, true, true, false, '');
        if ((isset($contactos['RESPORG'])) && (count($contactos['RESPORG']) > 0)){
            $contacto = $contactos['RESPORG'][0];
            // Tabla del responsable de la organización:
            $tablahtml = '<table border = "1">';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thnombre . '</th>';
            $tablahtml .= '<td style="width:120mm;">' . $contacto['NOMBRE'] . '</td>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">NIF</th>';
            $tablahtml .= '<td style="width:70mm;">' . $contacto['NIF'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thcargo . '</th>';
            $tablahtml .= '<td style="width:230mm;">' . $contacto['CARGO'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thdomicilio . '</th>';
            $tablahtml .= '<td style="width:230mm;">' . $organiza['DOMICILIO'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">CP -' . $thciudad . '</th>';
            $tablahtml .= '<td style="width:120mm;">' . $organiza['CP'] . '-' . $munorg['MUNICIPIO'] . '</td>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thprovincia . '</th>';
            $tablahtml .= '<td style="width:70mm;">' . $munorg['PROVINCIA'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thtelef . '</th>';
            $tablahtml .= '<td style="width:120mm;">' . $contacto['TELEFONO'] . '</td>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thmail . '</th>';
            $tablahtml .= '<td style="width:70mm;">' . $contacto['MAIL'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '</table>';
            $pdf->writeHTML($tablahtml, true, true, true, false, '');
        }
        else{
            $txterror =  '<p style="color:#FF0000;">' . $errnoresporg . '</p>';
            $pdf->writeHTML($txterror, true, true, true, false, '');
        }
        $pdf->Ln(5);

        // Responsable de la Flota:
        $h2cont = '<h2>' . $txtrespflota . '</h2>';
        $pdf->writeHTML($h2cont, true, true, true, false, '');
        if ((isset($contactos['RESPONSABLE'])) && (count($contactos['RESPONSABLE']) > 0)){
            $contacto = $contactos['RESPONSABLE'][0];
            // Tabla del responsable de la organización:
            $tablahtml = '<table border = "1">';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thnombre . '</th>';
            $tablahtml .= '<td style="width:120mm;">' . $contacto['NOMBRE'] . '</td>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">NIF</th>';
            $tablahtml .= '<td style="width:70mm;">' . $contacto['NIF'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thcargo . '</th>';
            $tablahtml .= '<td style="width:230mm;">' . $contacto['CARGO'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thdomicilio . '</th>';
            $tablahtml .= '<td style="width:230mm;">' . $organiza['DOMICILIO'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">CP -' . $thciudad . '</th>';
            $tablahtml .= '<td style="width:120mm;">' . $organiza['CP'] . '-' . $munorg['MUNICIPIO'] . '</td>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thprovincia . '</th>';
            $tablahtml .= '<td style="width:70mm;">' . $munorg['PROVINCIA'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thtelef . '</th>';
            $tablahtml .= '<td style="width:120mm;">' . $contacto['TELEFONO'] . '</td>';
            $tablahtml .= '<th style="font-weight:bold;width:40mm;background-color:#CCCCCC;">' . $thmail . '</th>';
            $tablahtml .= '<td style="width:70mm;">' . $contacto['MAIL'] . '</td>';
            $tablahtml .= '</tr>';
            $tablahtml .= '</table>';
            $pdf->writeHTML($tablahtml, true, true, true, false, '');
        }
        else{
            $txterror =  '<p style="color:#FF0000;">' . $errnorespflota . '</p>';
            $pdf->writeHTML($txterror, true, true, true, false, '');
        }
        $pdf->Ln(5);

        // Contactos:
        $indices = array('OPERATIVO', 'TECNICO', 'CONT24H');
        $h3 = array('OPERATIVO' => $txtoperativo, 'TECNICO' => $txttecnico, 'CONT24H' => $txt24h);
        $errores = array('OPERATIVO' => $errnoop,'TECNICO' => $errnotec, 'CONT24H' => $errno24h);
        foreach ($indices as $indice) {
            // Responsable de la Flota:
            $h2cont = '<h2>' . $h3[$indice] . '</h2>';
            $pdf->writeHTML($h2cont, true, true, true, false, '');
            if ((isset($contactos[$indice])) && (count($contactos[$indice]) > 0)){
                if ($indice != "CONT24H"){
                    // Tabla de contactos:
                    $tablahtml = '<table border = "1">';
                    $tablahtml .= '<tr>';
                    $tablahtml .= '<th style="font-weight:bold;width:80mm;background-color:#EEEEEE;text-align:center">' . $thnombre . '</th>';
                    $tablahtml .= '<th style="font-weight:bold;width:30mm;background-color:#EEEEEE;text-align:center">NIF</th>';
                    $tablahtml .= '<th style="font-weight:bold;width:60mm;background-color:#EEEEEE;text-align:center">' . $thcargo . '</th>';
                    $tablahtml .= '<th style="font-weight:bold;width:50mm;background-color:#EEEEEE;text-align:center">' . $thmail . '</th>';
                    $tablahtml .= '<th style="font-weight:bold;width:50mm;background-color:#EEEEEE;text-align:center">' . $thtelef . '</th>';
                    $tablahtml .= '</tr>';
                    $relleno = false;
                    foreach ($contactos[$indice] as $contacto) {
                        $tablahtml .= '<tr';
                        if ($relleno){
                            $tablahtml .= ' style="background-color:#EEEEEE;"';
                        }
                        $tablahtml .= '>';
                        $tablahtml .= '<td style="width:80mm;">' . $contacto['NOMBRE'] . '</td>';
                        $tablahtml .= '<td style="width:30mm;">' . $contacto['NIF'] . '</td>';
                        $tablahtml .= '<td style="width:60mm;">' . $contacto['CARGO'] . '</td>';
                        $tablahtml .= '<td style="width:50mm;">' . $contacto['MAIL'] . '</td>';
                        $tablahtml .= '<td style="width:50mm;">' . $contacto['TELEFONO'] . '</td>';
                        $tablahtml .= '</tr>';
                        $relleno = !($relleno);
                    }
                    $tablahtml .= '</table>';
                    $pdf->writeHTML($tablahtml, true, true, true, false, '');
                }
                else{
                    // Tabla de contactos:
                    $tablahtml = '<table border = "1">';
                    $tablahtml .= '<tr>';
                    $tablahtml .= '<th style="font-weight:bold;width:120mm;background-color:#EEEEEE;text-align:center">' . $thnombre . '</th>';
                    $tablahtml .= '<th style="font-weight:bold;width:80mm;background-color:#EEEEEE;text-align:center">' . $thmail . '</th>';
                    $tablahtml .= '<th style="font-weight:bold;width:70mm;background-color:#EEEEEE;text-align:center">' . $thtelef . '</th>';
                    $tablahtml .= '</tr>';
                    $relleno = false;
                    foreach ($contactos[$indice] as $contacto) {
                        $tablahtml .= '<tr';
                        if ($relleno){
                            $tablahtml .= ' style="background-color:#EEEEEE;"';
                        }
                        $tablahtml .= '>';
                        $tablahtml .= '<td style="width:120mm;">' . $contacto['NOMBRE'] . '</td>';
                        $tablahtml .= '<td style="width:80mm;">' . $contacto['MAIL'] . '</td>';
                        $tablahtml .= '<td style="width:70mm;">' . $contacto['TELEFONO'] . '</td>';
                        $tablahtml .= '</tr>';
                        $relleno = !($relleno);
                    }
                    $tablahtml .= '</table>';
                    $pdf->writeHTML($tablahtml, true, true, true, false, '');
                }
            }
            else{
                $txterror =  '<p style="color:#FF0000;">' . $errores[$indice] . '</p>';
                $pdf->writeHTML($txterror, true, true, true, false, '');
            }
            $pdf->Ln(5);
        }

        // Datos de Terminales:
        // Añadir una página
        $pdf->AddPage();

        // Cabecera:
        $h1txt = '<h1>' . $h2termflota . ' ' .$flota['FLOTA'] . '</h1>';
        $pdf->writeHTML($h1txt, true, true, true, false, '');
        $pdf->Ln(5);

        // Tabla con la fecha:
        $tablahtml = '<table border = "1">';
        $tablahtml .= '<tr>';
        $tablahtml .=  '<th style="font-weight:bold;width:25mm;background-color:#CCCCCC;">' . $thfecha . '</th>';
        $tablahtml .= '<td style="width:35mm;">' . $fecha . '</td>';
        $tablahtml .= '</tr>';
        $tablahtml .= '</table>';
        $pdf->writeHTML($tablahtml, true, true, true, false, '');
        $pdf->Ln(5);

        // Tabla con nº de terminales
        $tablahtml = '<table>';
        $tablahtml .= '<tr>';
        $tablahtml .= '<th style="font-weight:bold;width:60mm;border: 1px solid black;">' . $h3nterm . '</th>';
        $tablahtml .= '<td style="width:40mm;border: 1px solid black;text-align:right">' . $ntermflota . '</td>';
        $tablahtml .= '<td style="width:40mm;">&nbsp;</td>';
        $tablahtml .= '<th style="font-weight:bold;width:60mm;border: 1px solid black;">' . $h3rangoterm . '</th>';
        $tablahtml .= '<td style="width:40mm;border: 1px solid black;">' . $flota['RANGO'] . '</td>';
        $tablahtml .= '</tr>';
        $tablahtml .= '</table>';
        $pdf->writeHTML($tablahtml, true, true, true, false, '');
        $pdf->Ln(5);

        // Tabla de Terminales
        if ($ntermflota > 0){
            // Reducimos la fuente
            $pdf->SetFont('helvetica', '', 8);
            $tablahtml = '<table border = "1">';
            $tablahtml .= '<thead>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th colspan="6" style="font-weight:bold;width:100mm;text-align:center;">TERMINAL</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:12mm;text-align:center;">ISSI</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:25mm;text-align:center;">TEI</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:30mm;text-align:center;">' . $thnserie . '</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:20mm;text-align:center;">' . $thmnemo . '</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:40mm;text-align:center;">Carpeta</th>';
            $tablahtml .= '<th colspan="2" style="font-weight:bold;width:16mm;text-align:center;">' . $thllam . '</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:8mm;text-align:center;">DOTS</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:6mm;text-align:center;">Aut.</th>';
            $tablahtml .= '<th rowspan="2" style="font-weight:bold;width:10mm;text-align:center;">Encript</th>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:20mm;text-align:center;">Marca</th>';
            $tablahtml .= '<th style="font-weight:bold;width:20mm;text-align:center;">' . $thmodelo . '</th>';
            $tablahtml .= '<th style="font-weight:bold;width:10mm;text-align:center;">' . $thtipo . '</th>';
            $tablahtml .= '<th style="font-weight:bold;width:20mm;text-align:center;">' . $thproveedor . '</th>';
            $tablahtml .= '<th style="font-weight:bold;width:20mm;text-align:center;">Cod. HW</th>';
            $tablahtml .= '<th style="font-weight:bold;width:10mm;text-align:center;">A.M.</th>';
            $tablahtml .= '<th style="font-weight:bold;width:8mm;text-align:center;">D</th>';
            $tablahtml .= '<th style="font-weight:bold;width:8mm;text-align:center;">S-D</th>';
            $tablahtml .= '</tr>';
            $tablahtml .= '</thead>';
            $relleno = false;
            foreach ($terminales as $terminal) {
                $tablahtml .= '<tr';
                if ($relleno){
                    $tablahtml .= ' style="background-color:#EEEEEE;"';
                }
                $tablahtml .= '>';
                $tablahtml .= '<td style="width:20mm;">' . $terminal['MARCA'] . '</td>';
                $tablahtml .= '<td style="width:20mm;">' . $terminal['MODELO'] . '</td>';
                $tablahtml .= '<td style="width:10mm;">' . $terminal['TIPO'] . '</td>';
                $tablahtml .= '<td style="width:20mm;">' . $terminal['PROVEEDOR'] . '</td>';
                $tablahtml .= '<td style="width:20mm;">' . $terminal['CODIGOHW'] . '</td>';
                $tablahtml .= '<td style="width:10mm;">' . $terminal['AM'] . '</td>';
                $tablahtml .= '<td style="width:12mm;">' . $terminal['ISSI'] . '</td>';
                $tablahtml .= '<td style="width:25mm;">' . $terminal['TEI'] . '</td>';
                $tablahtml .= '<td style="width:30mm;">' . $terminal['NSERIE'] . '</td>';
                $tablahtml .= '<td style="width:20mm;">' . $terminal['MNEMONICO'] . '</td>';
                $tablahtml .= '<td style="width:40mm;">' . $terminal['CARPETA'] . '</td>';
                $tablahtml .= '<td style="width:8mm;">' . $terminal['DUPLEX'] . '</td>';
                $tablahtml .= '<td style="width:8mm;">' . $terminal['SEMID'] . '</td>';
                $tablahtml .= '<td style="width:8mm;">' . $terminal['DOTS'] . '</td>';
                $tablahtml .= '<td style="width:6mm;">' . $terminal['AUTENTICADO'] . '</td>';
                $tablahtml .= '<td style="width:10mm;">' . $terminal['ENCRIPTADO'] . '</td>';
                $tablahtml .= '</tr>';
                $relleno = !($relleno);
            }
            $tablahtml .= '</table>';
            $pdf->writeHTML($tablahtml, true, true, true, false, '');
            $pdf->Ln(5);
        }
        else{
            $h1txt = '<p style="color:#FF0000;">' . $errrnoterm . '</p>';
            $pdf->writeHTML($h1txt, true, true, true, false, '');
            $pdf->Ln(5);
        }

        // Datos de Grupos:
        // Añadir una página
        $pdf->AddPage();

        // Cabecera:
        $h1txt = '<h1> GSSI - ' . $flota['FLOTA'] . '</h1>';
        $pdf->writeHTML($h1txt, true, true, true, false, '');
        $pdf->Ln(5);

        // Tabla con la fecha:
        $tablahtml = '<table border = "1">';
        $tablahtml .= '<tr>';
        $tablahtml .=  '<th style="font-weight:bold;width:25mm;background-color:#CCCCCC;">' . $thfecha . '</th>';
        $tablahtml .= '<td style="width:35mm;">' . $fecha . '</td>';
        $tablahtml .= '</tr>';
        $tablahtml .= '</table>';
        $pdf->writeHTML($tablahtml, true, true, true, false, '');
        $pdf->Ln(5);

        // Imprimimos los grupos:
        if ($ngrupos > 0){
            $ancho = floor(270/$ncarpetas/2);
            $pdf->SetFont('helvetica', '', 8);
            $tablahtml = '<table border = "1">';
            // Encabezados;
            $tablahtml .= '<thead>';
            $tablahtml .= '<tr>';
            for ($i = 1; $i <= $ncarpetas; $i++){
                $tablahtml .= '<th colspan="2" style="font-weight:bold;width:' . $ancho * 2 . 'mm;text-align:center;">CARPETA ' . $i . '</th>';
            }
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            for ($i = 1; $i <= $ncarpetas; $i++){
                $tablahtml .= '<th colspan="2" style="font-weight:bold;width:' . $ancho * 2 . 'mm;text-align:center;">' .  $grupos[$i]['NOMBRE'] . '</th>';
            }
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            for ($i = 1; $i <= $ncarpetas; $i++){
                $tablahtml .= '<th style="font-weight:bold;width:' . $ancho . 'mm;text-align:center;">GSSI</th>';
                $tablahtml .= '<th style="font-weight:bold;width:' . $ancho . 'mm;text-align:center;">' . strtoupper($thmnemo) . '</th>';
            }
            $tablahtml .= '</tr>';
            $tablahtml .= '</thead>';
            $relleno = false;
            for ($i = 0; $i < $ngcmax; $i++){
                $tablahtml .= '<tr';
                if ($relleno){
                    $tablahtml .= ' style="background-color:#EEEEEE;"';
                }
                $tablahtml .= '>';
                for($j = 1; $j <= $ncarpetas; $j++){
                    if ($i < count($grupos[$j]['GISSI'])){
                        $tablahtml .= '<td style="width:' . $ancho . 'mm;">' . $grupos[$j]['GISSI'][$i]['GISSI'] . '</td>';
                        $tablahtml .= '<td style="width:' . $ancho . 'mm;">' . $grupos[$j]['GISSI'][$i]['MNEMO'] . '</td>';
                    }
                    else{
                        $tablahtml .= '<td style="width:' . $ancho . 'mm;">&nbsp;</td>';
                        $tablahtml .= '<td style="width:' . $ancho . 'mm;">&nbsp;</td>';
                    }
                }
                $tablahtml .= '</tr>';
                $relleno = !($relleno);
            }
            $tablahtml .= '</table>';
            $pdf->writeHTML($tablahtml, true, true, true, false, '');
            $pdf->Ln(5);
        }
        else{
            $h1txt = '<p style="color:#FF0000;">' . $errnogrupos . '</p>';
            $pdf->writeHTML($h1txt, true, true, true, false, '');
            $pdf->Ln(5);
        }

        // Datos de Permisos:
        // Añadir una página
        $pdf->AddPage();

        // Cabecera:
        $h1txt = '<h1>' . $h1permisos . ' ' . $flota['FLOTA'] . '</h1>';
        $pdf->writeHTML($h1txt, true, true, true, false, '');
        $pdf->Ln(5);

        // Tabla con la fecha:
        $tablahtml = '<table border = "1">';
        $tablahtml .= '<tr>';
        $tablahtml .=  '<th style="font-weight:bold;width:25mm;background-color:#CCCCCC;">' . $thfecha . '</th>';
        $tablahtml .= '<td style="width:35mm;">' . $fecha . '</td>';
        $tablahtml .= '</tr>';
        $tablahtml .= '</table>';
        $pdf->writeHTML($tablahtml, true, true, true, false, '');
        $pdf->Ln(5);

        if ($ncarpterm > 0){

            $ancho = floor(220/$ncarpterm);
            $tablahtml = '<table border = "1">';
            // Encabezados;
            $tablahtml .= '<thead>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th colspan="2" style="font-weight:bold;width:50mm;text-align:center;">&nbsp;</th>';
            $tablahtml .= '<th colspan="' . $ncarpterm . '" style="font-weight:bold;width:' . $ancho * $ncarpterm .'mm;text-align:center;">' . strtoupper($thorganiza) . '</th>';
            $tablahtml .= '</tr>';
            $tablahtml .= '<tr>';
            $tablahtml .= '<th style="font-weight:bold;width:15mm;text-align:center;">GSSI</th>';
            $tablahtml .= '<th style="font-weight:bold;width:35mm;text-align:center;">' . strtoupper($thmnemo) . '</th>';
            foreach ($carpetas as $carpeta) {
                $tablahtml .= '<th style="font-weight:bold;width:' . $ancho . 'mm;text-align:center;">' . $carpeta . '</th>';
            }
            $tablahtml .= '</tr>';
            $tablahtml .= '</thead>';
            // Imprimimos los permisos:
            $relleno = FALSE;
            foreach ($grupos_consulta as $grupo) {
                $tablahtml .= '<tr';
                if ($relleno){
                    $tablahtml .= ' style="background-color:#EEEEEE;"';
                }
                $tablahtml .= '>';
                $gssi = $grupo['GISSI'];
                $tablahtml .= '<td style="width:15mm;">' . $gssi . '</td>';
                $tablahtml .= '<td style="width:35mm;">' . $grupo['MNEMONICO'] . '</td>';
                foreach ($carpetas as $carpeta) {
                    if ($permisos[$gssi][$carpeta] > 0){
                        $tablahtml .= '<td style="width:' . $ancho . 'mm;text-align:center;">X</td>';
                    }
                    else{
                        $tablahtml .= '<td style="width:' . $ancho . 'mm;">&nbsp;</td>';
                    }
                }
                $tablahtml .= '</tr>';
                $relleno = !($relleno);
            }
            $tablahtml .= '</table>';
            $pdf->writeHTML($tablahtml, true, true, true, false, '');
            $pdf->Ln(5);
        }
        else{
            $h1txt = '<p style="color:#FF0000;">' . $errnocarpterm . '</p>';
            $pdf->writeHTML($h1txt, true, true, true, false, '');
            $pdf->Ln(5);
        }
    }
    else{
        $h1txt = '<h1>' . $h1 . '</h1>';
        $h1txt .=  '<p style="color:#FF0000;">' . $errnoresult . '</p>';
        $pdf->writeHTML($h1txt, true, true, true, false, '');
        $pdf->Ln(5);
    }
}
else{
    $h1txt = '<h1>' . $h3perm . '</h1>';
    $h1txt .=  '<p style="color:#FF0000;">' . $errnoperm . '</p>';
    $pdf->writeHTML($h1txt, true, true, true, false, '');
    $pdf->Ln(5);
}

// Generamos el PDF:
$nomFichero = 'Flota-';
if ($nflota > 0){
    $nomFichero .= $flota['ACRONIMO'];
}
$nomFichero .= '.pdf';
$pdf->Output($nomFichero, 'I');
?>
