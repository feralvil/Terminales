<?php

// ------------ Obtención del usuario Joomla! --------------------------------------- //
// Le decimos que estamos en Joomla
define('_JEXEC', 1);
// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..');

// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

// Iniciamos nuestra aplicación (site: frontend)
$mainframe = & JFactory::getApplication('site');

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/terminales_$idioma.php";
include ($lang);

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;
// ------------------------------------------------------------------------------------- //
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
}
else{
    // Seleccionamos la BBDD y codificamos la conexión en UTF-8:
    if (!mysql_select_db($base_datos, $link)) {
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
        exit;
    }
    mysql_set_charset('utf8',$link);
}
// ------------------------------------------------------------------------------------- //

/* LIBRERÍAS PARA LA GENERACIÓN DEL PDF */
require_once('tcpdf/config/lang/esp.php');
require_once('tcpdf/tcpdf.php');

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_query($sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación
 */
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    if ($usu != ""){
        $sql_permiso = "SELECT * FROM usuarios_flotas WHERE NOMBRE='$usu'";
        $res_permiso = mysql_query($sql_permiso) or die(mysql_error());
        $npermiso = mysql_num_rows($res_permiso);
        if ($npermiso > 1) {
            $permiso = 1;
        }
    }
}

// Extender la clase TCPDF para crear una cabecera y un pie de página propios
class MYPDF extends TCPDF {

    var $titulo = "";
    var $textos = "";
    var $cabecera = "";  // Cabecera de la tabla
    var $anchos = "";  // Anchos de las celdas de la tabla
    var $flota = "";  // Variables de los criterios de búsqueda
    var $tipoterm = "";
    var $marca = "";    
    var $estado = "";
    var $carpeta = "";
    var $tei = "";
    var $issi = "";
    var $nserie = "";
    var $amarco = "";
    var $dots = "";
    var $permisos = "";
    var $filas = 0; // Resultados de la consulta

    //Cabecera

    public function Header() {
        // Logo
        $this->Image('imagenes/comdes2.png', 20);
        // Establecemos la fuente y colores
        $this->SetDrawColor(0, 64, 122);
        $this->SetTextColor(0, 64, 122);
        $this->SetFont('helvetica', 'B', 12);
        // Nos desplazamos a a la derecha
        $this->Cell(20);
        // Título
        $this->Cell(230, 10, $this->titulo, 0, 0, 'C');
        // Logo 2
        $this->Image('imagenes/logo.jpg');
        // Salto de línea
        $this->Ln();
        $this->Cell(0, 0, '', 'T');
        $this->Ln();
        // Salto de línea
        //$this->Ln(10);
        if ($this->getPage() == 1) {
            $this->Intro();
        }
        // Colores, ancho de línea y negrita
        $this->SetFillColor(0, 64, 122);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(0, 64, 122);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B', 8);
        for ($i = 0; $i < count($this->cabecera); $i++) {
            $this->Cell($this->anchos[$i], 5, $this->cabecera[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        $this->SetTopMargin($this->GetY());
    }

    public function Intro() {
        $celdas = 0;
        if (($this->flota != "") || ($this->tipoterm != "") || ($this->marca != "") || ($this->estado != "") || ($this->issi != "") || ($this->tei != "") || ($this->nserie != "") || ($this->amarco != "") || ($this->dots != "") || ($this->permisos != "")) {
            $this->SetFont('', 'B', 10);
            $this->SetTextColor(0, 64, 122);
            $this->Cell(0, 5, $this->textos["criterios"], 0, 0, 'L');
            $this->Ln();
            if ($this->flota != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth('- Flota: '), 6, '- Flota: ', 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth('- Flota: '), 6, $this->flota, 0, 0, 'L');
                $this->Ln();
            }
        }
        if (($this->tei != "") || ($this->issi != "") || ($this->nserie != "")) {
            $this->tipoterm = $this->marca = $this->estado = $this->amarco = $this->dots = $this->permisos = "";
            $this->SetFont('', '', 8);
            if ($this->tei != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth('- TEI: '), 6, '- TEI: ', 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth('- TEI: '), 6, $this->tei, 0, 0, 'L');
            }
            if ($this->issi != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth('- ISSI: '), 6, '- ISSI: ', 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth('- ISSI: '), 6, $this->issi, 0, 0, 'L');
            }
            if ($this->nserie != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth('- Núm Serie: '), 6, '- Núm Serie: ', 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth('- Núm Serie: '), 6, $this->nserie, 0, 0, 'L');
            }
            $this->Ln();
        } else {
            if ($this->tipoterm != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth("- " . $this->textos["tipotxt"] . ": "), 6, "- " . $this->textos["tipotxt"] . ": ", 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth("- " . $this->textos["tipotxt"] . ": "), 6, $this->tipoterm, 0, 0, 'L');
                $celdas++;
            }
            if ($this->marca != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth('- Marca de Terminal: '), 6, '- Marca de Terminal: ', 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth('- Marca de Terminal: '), 6, $this->marca, 0, 0, 'L');
                $celdas++;
            }
            if ($this->estado != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth("- " . $this->textos["carpetatxt"] . ": "), 6, "- " . $this->textos["carpetatxt"] . ": ", 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth("- " . $this->textos["carpetatxt"] . ": "), 6, $this->carpeta, 0, 0, 'L');
                $celdas++;
            }
            if ($celdas > 0) {
                $this->Ln();
                $celdas = 0;
            }
            if ($this->amarco != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth("- " . $this->textos["amtxt"] . ": "), 6, "- " . $this->textos["amtxt"] . ": ", 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth("- " . $this->textos["amtxt"] . ": "), 6, $this->amarco, 0, 0, 'L');
                $celdas++;
            }
            if ($this->dots != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth("- " . $this->textos["dotstxt"] . ": "), 6, "- " . $this->textos["dotstxt"] . ": ", 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth("- $this->textos['dotstxt']: "), 6, $this->dots, 0, 0, 'L');
                $celdas++;
            }
            if ($this->permisos != "") {
                $this->SetFont('', '', 8);
                $this->Cell($this->GetStringWidth("- " . $this->textos["llamtxt"] . ": "), 6, "- " . $this->textos["llamtxt"] . ": ", 0, 0, 'L');
                $this->SetFont('', 'B', 8);
                $this->Cell(90 - $this->GetStringWidth("- " . $this->textos["llamtxt"] . ": "), 6, $this->permisos, 0, 0, 'L');
                $celdas++;
            }
            if ($celdas > 0) {
                $this->Ln();
                $celdas = 0;
            }
        }
        $this->SetFont('', 'B', 10);
        $this->SetTextColor(0, 64, 122);
        $this->Cell(0, 5, "- " . $this->textos["nreg"] . ": " . number_format($this->filas, 0, ',', '.'), 0, 0, 'L');
        $this->Ln();
    }

    // Pie de página
    public function Footer() {
        // Posición at 1.5 cm del fin de página
        $this->SetY(-15);
        // Establecemos la fuente y colores
        $this->SetDrawColor(0, 64, 122);
        $this->SetTextColor(0, 64, 122);
        $this->SetFont('helvetica', 'B', 8);
        // Número de Página
        $this->Cell(0, 10, $this->textos["pagina"] . " " . $this->getAliasNumPage() . " de " . $this->getAliasNbPages(), 'T', 0, 'C');
    }

    /*
      Función para simplificar la impresión de tablas. Imprime 1 fila de tabla.
      Argumentos:
      - $fila = Fila a imprimir. Incluye datos y/o cabeceras
      - $anchos = Ancho de cada columna (en mm)
      - $campos = Tipo de celda a imprimir:
      - -1 : Celda en Blanco (Celda vacía sin relleno)
      - 0 : Celda de Cabecera (Negrita, con relleno, centrada)
      - 1: Celda de Datos  (Sin negrita, relleno alterno, alineada a la la izquierda)
      - $par = Indica si es una fila par (con relleno gris)
     */

    public function ImprimeFila($fila, $anchos, $campos, $par) {
        $filas = 1;
        // Comprobamos los anchos
        for ($i = 0; $i < count($anchos); $i++) {
            $filas_celda = $this->GetNumLines($fila[$i], $anchos[$i]);
            if ($filas_celda > $filas) {
                $filas = $filas_celda;
            }
        }
        for ($i = 0; $i < count($anchos); $i++) {
            if ($campos[$i] == -1) {
                $fill = 0;
                $borde = 0;
            } elseif ($campos[$i] == 0) {
                $this->SetFillColor(0, 64, 122);
                $this->SetTextColor(255, 255, 255);
                $this->SetFont('', 'B', 8);
                $fill = 1;
                $borde = 1;
                $alin = 'C';
            } else {
                $fill = 0;
                if ($par) {
                    $fill = 1;
                }
                $borde = 1;
                $alin = 'L';
                $this->SetFillColor(194, 194, 194);
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('', '', 8);
            }
            if ($filas == 1) {
                $this->Cell($anchos[$i], 5, $fila[$i], $borde, 0, $alin, $fill);
            } else {
                $this->MultiCell($anchos[$i], 5 * $filas, $fila[$i], $borde, $alin, $fill, 0, '', '', true, 0, false, false);
            }
        }
        $this->Ln();
    }

}

// crear nuevo documento
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información de documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Oficina COMDES');
$pdf->SetTitle($h1);
$pdf->SetSubject($h1);
$pdf->SetKeywords('COMDES, Terminales, Terminals');

// Márgenes
$pdf->SetMargins(13, PDF_MARGIN_TOP, 14);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// Campos de texto
$pdf->titulo = $h1;
$pdf->textos["pagina"] = $pgtxt;
$pdf->textos["nreg"] = $nreg;
$pdf->textos["tipotxt"] = $tipotxt;
$pdf->textos["estadotxt"] = $estadotxt;
$pdf->textos["carpetatxt"] = "Carpeta";
$pdf->textos["amtxt"] = $amtxt;
$pdf->textos["dotstxt"] = $dotstxt;
$pdf->textos["llamtxt"] = $llamtxt;
$pdf->textos["criterios"] = $criterios;

// Salto automático de página
$pdf->SetAutoPageBreak(TRUE, 15);

// Factor de Escala de las imágenes
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Cadenas de texto dependientes del Idioma
$pdf->setLanguageArray($l);

// Cabecera y anchos de tabla
$pdf->cabecera = $campospdf;
$pdf->anchos = array(10, 25, 15, 30, 35, 25, 25, 25, 15, 15, 35, 15);
$campos_imp = array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
// Establecemos la fuente por defecto
$pdf->SetFont('helvetica', '', 8);

// ---------------------------------------------------------
$sql = "SELECT terminales.ID, flotas.ACRONIMO, terminales.ISSI, terminales.TEI, terminales.TIPO, ";
$sql = $sql . "terminales.MARCA, terminales.MODELO, terminales.PROVEEDOR, terminales.DOTS, ";
$sql = $sql . "terminales.AM, terminales.MNEMONICO, terminales.DUPLEX, terminales.SEMID ";
$sql = $sql . "FROM terminales, flotas WHERE (terminales.FLOTA = flotas.ID) ";
if ($permiso == 2) {
    if (($flota != '') && ($flota != "NN")) {
        $sql = $sql . "AND (terminales.FLOTA='$flota') ";
        $sql_flota = "SELECT * FROM flotas WHERE ID='$flota'";
        $res_flota = mysql_query($sql_flota) or die(mysql_error());
        $row_flota = mysql_fetch_array($res_flota);
        $pdf->flota = $row_flota["FLOTA"];
    }
}
elseif ($permiso == 1) {
    if (($flota != '') && ($flota != "NN")) {
        $sql = $sql . "AND (terminales.FLOTA='$flota') ";
        $sql_flota = "SELECT * FROM flotas WHERE ID='" . $pdf->flota . "'";
        $res_flota = mysql_query($sql_flota) or die(mysql_error());
        $row_flota = mysql_fetch_array($res_flota);
        $pdf->flota = $row_flota["FLOTA"];
    }
    else {
        $sql_flotas = "SELECT ID, ACRONIMO FROM flotas, usuarios_flotas WHERE ";
        $sql_flotas = $sql_flotas . "usuarios_flotas.NOMBRE='$usu' AND flotas.ID = usuarios_flotas.ID_FLOTA";
        $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
        $flotas = array();
        for ($i = 0; $i < $nflotas; $i++) {
            $row_flotas = mysql_fetch_array($res_flotas);
            $flotas[$i] = $row_flotas[0];
        }
        $sql = $sql . "AND terminales.FLOTA IN (";
        for ($i = 0; $i < $nflotas; $i++) {
            $sql = $sql . $flotas[$i];
            if ($i < ($nflotas - 1)) {
                $sql = $sql . ",";
            }
        }
        $sql = $sql . ") ";
        $sql_flota = "SELECT * FROM flotas WHERE ID='$flota_usu'";
        $res_flota = mysql_query($sql_flota) or die(mysql_error());
        $row_flota = mysql_fetch_array($res_flota);
        $pdf->flota = $row_flota["FLOTA"];
    }
}
else {
    $sql = $sql . "AND (terminales.FLOTA='$flota_usu') ";
    $sql_flota = "SELECT * FROM flotas WHERE ID='$flota_usu'";
    $res_flota = mysql_query($sql_flota) or die(mysql_error());
    $row_flota = mysql_fetch_array($res_flota);
    $pdf->flota = $row_flota["FLOTA"];
}
if (($tei != '') || ($issi != '') || ($nserie != '')) {
    $amarco = $tipoterm = $marca = $dots = "00";
    if ($tei != '') {
        $sql = $sql . "AND (terminales.TEI='$tei') ";
        $pdf->tei = $tei;
    }
    if ($issi != '') {
        $sql = $sql . "AND (terminales.ISSI='$issi') ";
        $pdf->issi = $issi;
    }
    if ($nserie != '') {
        $sql = $sql . "AND (terminales.NSERIE='$nserie') ";
        $pdf->nserie = $nserie;
    }
}
if (($amarco != '') && ($amarco != "00")) {
    $sql = $sql . "AND (terminales.AM='$amarco') ";
    $pdf->amarco = $amarco;
}
if (($estado != '') && ($estado != "00")) {
    $sql = $sql . "AND (terminales.ESTADO='$estado') ";
    $pdf->estado = $estado;
}
if (($tipoterm != '') && ($tipoterm != "00")) {
    $sql = $sql . "AND (terminales.TIPO LIKE '$tipoterm') ";
    switch ($tipoterm) {
        case ("F"): {
                $tipoterm = $fijo;
                break;
            }
        case ("M%"): {
                $tipoterm = $movil;
                break;
            }
        case ("MB"): {
                $tipoterm = $movilb;
                break;
            }
        case ("MA"): {
                $tipoterm = $movila;
                break;
            }
        case ("MG"): {
                $tipoterm = $movilg;
                break;
            }
        case ("P%"): {
                $tipoterm = $portatil;
                break;
            }
        case ("PB"): {
                $tipoterm = $portatilb;
                break;
            }
        case ("PA"): {
                $tipoterm = $portatila;
                break;
            }
        case ("PX"): {
                $tipoterm = $portatilx;
                break;
            }
    }
    $pdf->tipoterm = $tipoterm;
}
if (($marca != '') && ($marca != "00")) {
    $sql = $sql . "AND (terminales.MARCA='$marca') ";
    $pdf->marca = $marca;
}
if (($permisos != '') && ($permisos != "00")) {
    switch ($permisos) {
        case "NO": {
                $sql = $sql . "AND (terminales.SEMID='NO') AND (terminales.DUPLEX='NO') ";
                $pdf->permisos = $permno;
                break;
        }
        case "SEMID": {
                $sql = $sql . "AND (terminales.SEMID='SI') ";
                $pdf->permisos = $perms;
                break;
        }
        case "DUPLEX": {
                $sql = $sql . "AND (terminales.DUPLEX='SI') ";
                $pdf->permisos = $permd;
                break;
        }
        case "SYD": {
                $sql = $sql . "AND (terminales.DUPLEX='SI') AND (terminales.SEMID='SI') ";
                $pdf->permisos = $permsd;
                break;
        }
    }
}
if (($estado != '') && ($estado != "00")) {
    $sql = $sql . "AND (terminales.ESTADO='$estado') ";
    switch ($estado) {
        case "A": {
                $estado = $alta;
                break;
        }
        case "B": {
                $estado = $baja;
                break;
        }
        case "R": {
                $estado = $rep;
                break;
        }
    }
    $pdf->estado = $estado;
}
if (($dots != '') && ($dots != "00")) {
    $sql = $sql . "AND (terminales.DOTS='$dots') ";
    $pdf->dots = $dots;
}
$sql_term = $sql . " ORDER BY flotas.ACRONIMO ASC, terminales.ID ASC";
$res_term = mysql_query($sql_term) or die(mysql_error());
$nterm = mysql_num_rows($res_term);
$pdf->filas = $nterm;

// Añadir una página
$pdf->AddPage();

// Restauramos colores y fuente
$pdf->SetFillColor(194, 194, 194);
$pdf->SetDrawColor(0, 64, 122);
$pdf->SetTextColor(0);
$pdf->SetFont('');
$fill = 0;
// Imprimir los datos de la consulta
$ncampos = count($campos_imp);
for ($i = 0; $i < $nterm; $i++) {
    $terminal = mysql_fetch_array($res_term);
    for ($j = 0; $j < $ncampos; $j++) {
        $nombre = mysql_field_name($res_term, $j);
        if ($nombre == "TIPO") {
            $tipot = $terminal[$j];
            switch ($tipot) {
                case ("F"): {
                        $tipot = $fijo;
                        break;
                }
                case ("M"): {
                        $tipot = $movil;
                        break;
                }
                case ("MB"): {
                        $tipot = $movilb;
                        break;
                }
                case ("MA"): {
                        $tipot = $movila;
                        break;
                }
                case ("MG"): {
                        $tipot = $movilg;
                        break;
                }
                case ("P"): {
                        $tipot = $portatil;
                        break;
                }
                case ("PB"): {
                        $tipot = $portatilb;
                        break;
                }
                case ("PA"): {
                        $tipot = $portatila;
                        break;
                }
                case ("PX"): {
                        $tipot = $portatilx;
                        break;
                }
                case ("D"): {
                        $tipot = $despacho;
                        break;
                }
            }
            $terminal[$j] = $tipot;
        }
        elseif ($nombre == "DUPLEX") {
            $duplex = "NO";
            if ($terminal[$j] == "SI") {
                $duplex = "D";
                if ($terminal[12] == "SI") {
                    $duplex = "D + S";
                }
            } else {
                if ($terminal[12] == "SI") {
                    $duplex = "S";
                }
            }
            $terminal[$j] = $duplex;
        } else {
            $terminal[$j] = $terminal[$j];
        }
    }
    $relleno = false;
    if (($i % 2) == 1) {
        $relleno = true;
    }
    $pdf->ImprimeFila($terminal, $pdf->anchos, $campos_imp, $relleno);
}
// Generar y enviar el documento PDF
$pdf->Output("$nomarch.pdf", 'I');
?>
