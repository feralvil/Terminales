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
    $lang = "idioma/flotadet_$idioma.php";
    include ($lang);
// ------------------------------------------------------------------------------------- //

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
    include("conexion.php");
    $base_datos=$dbbdatos;
    $link = mysql_connect($dbserv,$dbusu,$dbpaso);
    if(!link){
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
$permiso=0;
if($flota_usu==100){
    $permiso = 2;
}
 else {
    if ($flota_usu != ""){
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
		$this->Cell(0, 10, $this->getAliasNumPage(), 'T', 0, 'C');
	}	
}

// crear nuevo documento
$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información de documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Oficina COMDES');
$pdf->SetTitle("Autorización DOTS");
$pdf->SetSubject("Autorización DOTS");
$pdf->SetKeywords('COMDES, DOTS');

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
$pdf->SetFont('helvetica', '', 10);
// Título
$pdf->titulo = "AUTORIZACIÓN";
// Añadir una página
$pdf->AddPage();

// ---------------------------------------------------------

if ($permiso!=0){
       $fecha = "En <b>$municipio</b>, a <b>$dia</b> de <b>$mes</b> de <b>$anyo</b>";
       $pdf->writeHTML($fecha, true, false, false, FALSE, 'R');
       $pdf->Ln(20);
       $par = "Yo, $trat <b>$nombre</b> en calidad de <b>$cargo</b> de la Flota <b>$flota</b>";
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       $par = "Con Documento de identidad número <b>$nif</b>";
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       $pdf->Ln(5);
       $par = "<b>autorizo</b> a la";       
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       // Nos desplazamos a a la derecha
       $pdf->Cell(30);
       $par = "Dirección General de Tecnologías de la Información";
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       $pdf->Cell(30);
       $par = "Secretaria Autonómica de Administración Pública";
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       $pdf->Cell(30);
       $par = "Conselleria de Hacienda y Administración Pública";
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       $pdf->Ln(5);
       $par = "para que ejerza, en su calidad de gestor de la Red de Comunicaciones Móviles de Emergencia y Seguridad de la Comunidad Valenciana (RED COMDES), la siguiente actuación en relación a los terminales de esta flota que acceden a la citada red:";
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       $pdf->Ln(10);
       $par = "<ol><li>Dar de alta/baja a los terminales en el buzón de la Generalitat del servidor DOTS, donde se almacenan todos los mensajes de estado emitidos por los  terminales. Con esta información, la flota podrá acceder al posicionamiento de sus terminales mediante una aplicación desarrollada por la Generalitat, y gratuita para las flotas. El acceso a este servidor conlleva asimismo el conocimiento del gestor de la red del posicionamiento de los terminales. En todo caso, la Generalitat garantiza la absoluta confidencialidad de estos datos, que sólo podrán ser utilizados a petición expresa de la propia flota.</li></ol>";
       $pdf->writeHTML($par, true, false, false, FALSE, 'L');
       $pdf->Ln(20);
       $par = "Atentamente,";
       $pdf->writeHTML($par, true, false, false, FALSE, 'C');
       $pdf->Ln(20);
       $par = "<i>$nombre</i>";
       $pdf->writeHTML($par, true, false, false, FALSE, 'C');
       $par = "<i>$cargo de la Flota $flota</i>";
       $pdf->writeHTML($par, true, false, false, FALSE, 'C');
       $pdf->Ln(5);
       $par = "Fdo.:";
}
else {
	$error = "Acceso denegado. No está autorizado a solicitar esta autorización";
	$pdf->SetTextColor(255, 0, 0);
	$pdf->SetFont('', 'B',10);
	$pdf->MultiCell(0,5,$error,0,'L',0);
	
}

// Generar y enviar el documento PDF
$pdf->Output("Autorizacion_DOTS.pdf", 'I');
?>
