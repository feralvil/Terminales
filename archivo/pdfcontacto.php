<?php
// ------------ Obtención del usuario Joomla! --------------------------------------- //
// Le decimos que estamos en Joomla
define( '_JEXEC', 1 );
// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
define( 'DS', DIRECTORY_SEPARATOR );
define('JPATH_BASE', dirname(__FILE__).DS.'..' );

// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

// Iniciamos nuestra aplicación (site: frontend)
$mainframe =& JFactory::getApplication('site');

// Obtenemos los parámetros de Joomla
$user =& JFactory::getUser();
$usu = $user->username;

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contedi_$idioma.php";
include ($lang);
// ------------------------------------------------------------------------------------- //

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos=$dbbdatos;
$link = mysql_connect($dbserv,$dbusu,$dbpaso);
if(!link) {
    echo "<b>ERROR MySQL:</b>".mysql_error();
}
// ------------------------------------------------------------------------------------- //

/* GENERACIÓN DEL PDF */
require_once('tcpdf/config/lang/esp.php');
require_once('tcpdf/tcpdf.php');

import_request_variables("gp","");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina="SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina=mysql_db_query($base_datos,$sql_oficina);
$row_oficina=mysql_fetch_array($res_oficina);
$flota_usu=$row_oficina["ID"];
/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación
*/
$permiso = 0;
if($flota_usu == 100) {
    $permiso = 2;
}
else {
    $sql_permiso = "SELECT * FROM usuarios_flotas WHERE NOMBRE='$usu'";
    $res_permiso = mysql_db_query($base_datos,$sql_permiso) or die(mysql_error());
    $npermiso = mysql_num_rows($res_permiso);
    if ($npermiso > 1) {
        $permiso = 1;
    }
}

// Extender la clase TCPDF para crear una cabecera y un pie de página propios
class MYPDF extends TCPDF {
    var $titulo = "";
    var $pagina = "";

    //Cabecera
    public function Header() {
        // Logo
        $this->Image('imagenes/comdes2.png',20);
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
        $this->Cell(0, 10, $this->pagina.' '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 'T', 0, 'C');
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
        for ($i=0; $i < count ($anchos); $i++) {
            $filas_celda = $this->GetNumLines ($fila[$i], $anchos[$i]);
            if ($filas_celda > $filas) {
                $filas = $filas_celda;
            }
        }
        for ($i=0; $i < count ($anchos); $i++) {
            if ($campos[$i]==-1) {
                //$this->Cell($anchos[$i], 5*$filas, '', 0, 'C', 0);
                $fill = 0;
                $borde = 0;
            }
            elseif ($campos[$i]==0) {
                $this->SetFillColor(0, 64, 122);
                $this->SetTextColor(255, 255, 255);
                $this->SetFont('', 'B',10);
                $fill = 1;
                $borde = 1;
                $alin = 'C';
                //$this->Cell($anchos[$i], 5*$filas, $fila[$i], 1, 0, 'C', 1);
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
                $this->SetFont('', '',10);
                /*if ($this->GetStringWidth($fila[$i]) > $anchos[$i]) {
					$this->MultiCell($anchos[$i], 5, $fila[$i], 1, 'L', 0, 0, '', '', true, 0);
				}
				else {
					$this->Cell($anchos[$i], 5*$filas, $fila[$i], 1, 0, 'L', $fill);
				}*/
            }
            if ($filas == 1) {
                $this->Cell($anchos[$i], 5, $fila[$i], $borde, 0, $alin, $fill);
            }
            else {
                //$this->MultiCell($anchos[$i], 5, $fila[$i], $borde, 'L', 0, 0, '', '', true, 0);
                $this->MultiCell($anchos[$i], 5*$filas, $fila[$i], $borde, $alin, $fill, 0, '', '', true, 0, false, false);
            }
        }
        $this->Ln();
    }
}

// crear nuevo documento
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información de documento
$titexp = "$detalltxt de $contacto de la Flota COMDES";
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Oficina COMDES');
$pdf->SetTitle($titexp);
$pdf->SetSubject($titexp);
$pdf->SetKeywords("COMDES, Flota, $contacto");
$pdf->titulo = $titexp;

// Márgenes
$pdf->SetMargins(13.5, PDF_MARGIN_TOP, 13.5);
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

// ---------------------------------------------------------

if ($permiso!=0) {
    if ($detalle=="Responsable") {
        $dettxt = "Responsable";
    }
    if ($detalle=="Contacto 1") {
        $dettxt = "$contacto 1";
    }
    if ($detalle=="Contacto 2") {
        $dettxt = "$contacto 2";
    }
    if ($detalle=="Contacto 3") {
        $dettxt = "$contacto 3";
    }
    $sql_contacto = "SELECT * FROM contactos WHERE ID=$id_contacto";
    $res_contacto=mysql_db_query($base_datos,$sql_contacto) or die ("Error en la consulta de contacto: ".mysql_error());
    $ncontacto=mysql_num_rows($res_contacto);
    if($ncontacto!=0){
	$row_contacto=mysql_fetch_array($res_contacto);
    }
    // Colores y Fuente para los títulos
    $pdf->SetFillColor(0, 64, 122);
    $pdf->SetTextColor(0, 64, 122);
    $pdf->SetDrawColor(0, 64, 122);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('', 'B',12);
    $titulo = "$detalltxt del $dettxt de la Flota $flota ($acronimo)";
    $pdf->MultiCell(0,5,$titulo,0,'C',0);
    $pdf->Ln(5);
    $anchos_imp = array(40, 140, 30, 60);
    $campos_imp = array(0,1,0,1);
    $fila_imp = array($nomtxt, utf8_encode($row_contacto["NOMBRE"]),"NIF/CIF", $row_contacto["NIF"]);
    $pdf->ImprimeFila ($fila_imp, $anchos_imp, $campos_imp, false);
    $fila_imp = array($cargo, utf8_encode($row_contacto["CARGO"]),"ID", $row_contacto["ID"]);
    $pdf->ImprimeFila ($fila_imp, $anchos_imp, $campos_imp, true);
    $fila_imp = array($telefono, $row_contacto["TELEFONO"],"$telefono GVA", $row_contacto["TLFGVA"]);
    $pdf->ImprimeFila ($fila_imp, $anchos_imp, $campos_imp, false);
    $fila_imp = array($movil, $row_contacto["MOVIL"],"$movil GVA", $row_contacto["MOVILGVA"]);
    $pdf->ImprimeFila ($fila_imp, $anchos_imp, $campos_imp, true);
    $fila_imp = array($mail, $row_contacto["MAIL"],"Fax", $row_contacto["FAX"]);
    $pdf->ImprimeFila ($fila_imp, $anchos_imp, $campos_imp, false);
}

else {
    $error = $permno;
    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetFont('', 'B',10);
    $pdf->MultiCell(0,5,$error,0,'L',0);

}

// Generar y enviar el documento PDF
$pdf->Output($contacto."_COMDES-$id_contacto.pdf", 'I');
?>
