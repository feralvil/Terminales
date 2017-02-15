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
$lang = "idioma/termdet_$idioma.php";
include ($lang);

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>". mysql_error();
    exit;
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

/* GENERACIÓN DEL PDF */
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
        $sql_permiso = "SELECT * FROM usuarios_flotas WHERE NOMBRE='$usu' AND ID_FLOTA='$id_flota'";
        $res_permiso = mysql_query($sql_permiso) or die(mysql_error());
        $npermiso = mysql_num_rows($res_permiso);
        if ($npermiso > 0) {
            $permiso = 1;
        }
        else {
            if ($flota_usu == $id_flota) {
                $permiso = 1;
            }
        }
    }
}


// Extender la clase TCPDF para crear una cabecera y un pie de página propios
class MYPDF extends TCPDF {

    var $titulo = "";
    var $pagina = "";

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
        $this->Cell(140, 10, $this->titulo, 0, 0, 'C');
        // Logo 2
        $this->Image('imagenes/logo.jpg');
        // Salto de línea
        $this->Ln();
        $this->Cell(0, 0, '', 'T');
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
        $this->Cell(0, 10, $this->pagina . ' ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 'T', 0, 'C');
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
            }
            elseif ($campos[$i] == 0) {
                $this->SetFillColor(0, 64, 122);
                $this->SetTextColor(255, 255, 255);
                $this->SetFont('', 'B', 10);
                $fill = 1;
                $borde = 1;
                $alin = 'C';
            }
            else {
                $fill = 0;
                if ($par) {
                    $fill = 1;
                }
                $borde = 1;
                $alin = 'L';
                $this->SetFillColor(194, 194, 194);
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('', '', 10);
            }
            if ($filas == 1) {
                $this->Cell($anchos[$i], 5, $fila[$i], $borde, 0, $alin, $fill);
            }
            else {
                $this->MultiCell($anchos[$i], 5 * $filas, $fila[$i], $borde, $alin, $fill, 0, '', '', true, 0, false, false);
            }
        }
        $this->Ln();
    }

}

// crear nuevo documento
$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->titulo = $titulo;
$pdf->pagina = $pgtxt;

// Información de documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Oficina COMDES');
$pdf->SetTitle('Terminal de la Red COMDES');
$pdf->SetSubject('Terminal de la Red COMDES');
$pdf->SetKeywords('COMDES, Terminal');

// Márgenes
$pdf->SetMargins(15, PDF_MARGIN_TOP, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Salto automático de página
$pdf->SetAutoPageBreak(TRUE, 15);

// Factor de Escala de las imágenes
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Cadenas de texto dependientes del Idioma
$pdf->setLanguageArray($l);
// Establecemos la fuente por defecto
$pdf->SetFont('helvetica', '', 8);
// Añadir una página
$pdf->AddPage();
// Fuente y color para los errores en la consulta
$pdf->SetTextColor(255, 0, 0);
$pdf->SetFont('', 'B', 10);


if ($permiso != 0) {
    //datos de la tabla terminales
    $sql_terminal = "SELECT * FROM terminales WHERE ID='$idterm'";
    $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
    $nterminal = mysql_num_rows($res_terminal);
    if ($nterminal == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Terminal</p>\n";
    }
    else {
        $row_terminal = mysql_fetch_array($res_terminal);
        $id_flota = $row_terminal["FLOTA"];
        $tipo = $row_terminal["TIPO"];
        switch ($tipo) {
            case ("F"): {
                    $tipo = $fijo;
                    break;
            }
            case ("M"): {
                    $tipo = $movil;
                    break;
            }
            case ("MB"): {
                    $tipo = $movilb;
                    break;
            }
            case ("MA"): {
                    $tipo = $movila;
                    break;
            }
            case ("MG"): {
                    $tipo = $movilg;
                    break;
            }
            case ("P"): {
                    $tipo = $portatil;
                    break;
            }
            case ("PB"): {
                    $tipo = $portatilb;
                    break;
            }
            case ("PA"): {
                    $tipo = $portatila;
                    break;
            }
            case ("PX"): {
                    $tipo = $portatilx;
                    break;
            }
        }
        $row_terminal["TIPO"] = $tipo;
    }
    //datos de la tabla flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$id_flota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota Usuaria: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
    }
    //datos de la tabla municipios
    $ine = $row_flota ["INE"];
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
    }

    // Colores y Fuente para los títulos
    $pdf->SetFillColor(0, 64, 122);
    $pdf->SetTextColor(0, 64, 122);
    $pdf->SetDrawColor(0, 64, 122);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('', 'B', 12);
    $titulo = "Terminal TEI: " . $row_terminal["TEI"] . " / ISSI: " . $row_terminal["ISSI"] . " de la Flota " . $row_flota["FLOTA"] . " (" . $row_flota["ACRONIMO"] . ")";
    $pdf->MultiCell(0, 5, $titulo, 0, 'C', 0);
    $pdf->Ln(5);
    $pdf->SetFont('', 'B', 10);
    $pdf->Cell(0, 5, $h2admin, 0, 0, 'L', 0);
    $pdf->Ln(5);
    $fila_imp = array($tipotxt, 'Marca', $modtxt, $proveedor, $amtxt, $dotstxt);
    $anchos_imp = array(30, 30, 30, 30, 30, 30);
    $campos_imp = array(0, 0, 0, 0, 0, 0);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
    $fila_imp = array($row_terminal["TIPO"], $row_terminal["MARCA"], $row_terminal["MODELO"], $row_terminal["PROVEEDOR"], $row_terminal["AM"], $row_terminal["DOTS"]);
    $campos_imp = array(1, 1, 1, 1, 1, 1);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
    $pdf->Ln(5);

    // Datos de la Flota
    $pdf->SetTextColor(0, 64, 122);
    $pdf->SetFont('', 'B', 10);
    $pdf->Cell(0, 5, $h2flota, 0, 0, 'L', 0);
    $pdf->Ln();
    $fila_imp = array('ID', $nomflota, $acroflota, $localiza);
    $anchos_imp = array(15, 60, 25, 80);
    $campos_imp = array(0, 0, 0, 0);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
    $localizacion = $row_flota["DOMICILIO"] . " - " . $row_flota["CP"] . " " . $row_mun["MUNICIPIO"];
    $fila_imp = array($row_flota["ID"], $row_flota["FLOTA"], $row_flota["ACRONIMO"], $localizacion);
    $campos_imp = array(1, 1, 1, 1);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
    $pdf->Ln(5);

    // Datos de Contactos
    // Consulta a la base de Datos de contacto
    $pdf->SetTextColor(0, 64, 122);
    $pdf->SetFont('', 'B', 10);
    $pdf->Cell(0, 5, $h3flota, 0, 0, 'L', 0);
    $pdf->Ln();
    if (($row_flota["RESPONSABLE"] == "0") && ($row_flota["CONTACTO1"] == "0") && ($row_flota["CONTACTO2"] == "0") && ($row_flota["CONTACTO3"] == "0")) {
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetFont('', 'B', 10);
        $pdf->Cell(0, 5, $nocont, 0, 'L', 0);
        $pdf->Ln();
    }
    else {
        $fila_imp = array('', $nomflota, $cargo, $telefono, $mail);
        $anchos_imp = array(25, 40, 60, 20, 35);
        $campos_imp = array(-1, 0, 0, 0, 0);
        $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
        $relleno = false;
        $idc = array($row_flota["RESPONSABLE"], $row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
        $idcampo = array("Responsable", "$contacto 1", "$contacto 2", "$contacto 3");
        for ($j = 0; $j < count($idc); $j++) {
            if ($idc[$j] != 0) {
                $id_contacto = $idc[$j];
                $sql_contacto = "SELECT * FROM contactos WHERE ID=$id_contacto";
                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
                if ($ncontacto != 0) {
                    $row_contacto = mysql_fetch_array($res_contacto);
                    $nombre = $row_contacto["NOMBRE"];
                    $cargo = $row_contacto["CARGO"];
                    $fila_imp = array($idcampo[$j], $nombre, $cargo, $row_contacto["TELEFONO"], $row_contacto["MOVIL"], $row_contacto["MAIL"]);
                    $campos_imp = array(0, 1, 1, 1, 1);
                    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, $relleno);
                    $relleno = !$relleno;
                }
                else {
                    $pdf->SetTextColor(255, 0, 0);
                    $pdf->SetFont('', 'B', 10);
                    $pdf->Cell(0, 5, "No hay resultado en la consulta del $idcampo[$j]", 0, 'L', $relleno);
                }
            }
        }
    }
    $pdf->Ln(5);

    // Datos Técnicos del Terminal
    $pdf->SetTextColor(0, 64, 122);
    $pdf->SetFont('', 'B', 10);
    $pdf->Cell(0, 5, $h2term, 0, 0, 'L', 0);
    $pdf->Ln();
    $anchos_imp = array(40, 50, 40, 50);
    $campos_imp = array(0, 1, 0, 1);
    $fila_imp = array('ISSI', $row_terminal["ISSI"], 'TEI', $row_terminal["TEI"]);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
    $fila_imp = array($cdhw, $row_terminal["CODIGOHW"], $nserie, $row_terminal["NSERIE"]);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, true);
    $fila_imp = array('ID', $row_terminal["ID"], $mnemo, $row_terminal["MNEMONICO"]);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
    $fila_imp = array("$llamada Dúplex", $row_terminal["DUPLEX"], "$llamada semidúplex", $row_terminal["SEMID"]);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, true);
    switch ($row_terminal["ESTADO"]) {
        case "A": {
                $estado = $alta;
                $fecha_nom = $falta;
                $fecha_val = $row_terminal["FALTA"];
                break;
            }
        case "B": {
                $estado = $baja;
                $fecha_nom = $fbaja;
                $fecha_val = $row_terminal["FBAJA"];
                break;
            }
        case "R": {
                // Se busca la incidencia
                $sql_incid = "SELECT * FROM incidencias WHERE TERMINAL = '$id' ORDER BY ID DESC";
                $res_incid = mysql_query($sql_incid) or die("Error en la consulta de Incidencia: " . mysql_error());
                $nincid = mysql_num_rows($res_incid);
                if ($nflota == 0) {
                    $estado = "<p class='error'>No hay resultados en la consulta de Incidencias</p>\n";
                } else {
                    $row_incid = mysql_fetch_array($res_incid);
                    $id_incid = $row_incid["ID"];
                    $estado = "$rep - Incid. $id";
                    $fecha_val = $row_incid["FAVERIA"];
                }
                $fecha_nom = $frep;
                break;
            }
    }
    $fila_imp = array($estadotxt, $estado, $fecha_nom, $fecha_val);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
    $relleno = true;
    if ($permiso == 2) {
        $fila_imp = array('Número K', $row_terminal["NUMEROK"], 'Carpeta', $row_terminal["CARPETA"]);
        $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, $relleno);
        $relleno = !$relleno;
    }
    $fila_imp = array($observ, $row_terminal["OBSERVACIONES"]);
    $campos_imp = array(0, 1);
    $anchos_imp = array(40, 140);
    $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, $relleno);
} else {
    $error = $permno;
    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetFont('', 'B', 10);
    $pdf->MultiCell(0, 5, $error, 0, 'L', 0);
}

// Generar y enviar el documento PDF
$pdf->Output("Terminal_COMDES-$id.pdf", 'I');
?>