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

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotas_$idioma.php";
include ($lang);

// Obtenemos los parámetros de Joomla
    $user =& JFactory::getUser();
    $usu = $user->username;
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
$permiso = 0;
if($flota_usu == 100){
    $permiso = 2;
}
// Extender la clase TCPDF para crear una cabecera y un pie de página propios
class MYPDF extends TCPDF {
        var $titulo = "";
        var $textos = "";
	var $cabecera= ""; 	// Cabecera de la tabla
	var $anchos="";		// Anchos de las celdas de la tabla
	var $flota="";		// Variables de los criterios de búsqueda
	var $activa="";		// Variables de los criterios de búsqueda
	var $filas=0; // Resultados de la consulta
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
		// Salto de línea
		//$this->Ln(10);
		if ($this->getPage()==1){
			$this->Intro();
		}
		// Colores, ancho de línea y negrita
		$this->SetFillColor(0, 64, 122);
		$this->SetTextColor(255, 255, 255);
		$this->SetDrawColor(0, 64, 122);
		$this->SetLineWidth(0.3);
		$this->SetFont('', 'B',8);
                $camps = array (0,0,0,0,0,0,0,0);
                $this->ImprimeFila($this->cabecera, $this->anchos, $camps, 0);
		/*for($i = 0; $i < count($this->cabecera); $i++) {
			$this->Cell($this->anchos[$i], 5, $this->cabecera[$i], 1, 0, 'C', 1);
		}*/
		//$this->Ln();
		$this->SetTopMargin($this->GetY());
	}

	public function Intro(){
		if ($this->flota!="") {
			$this->SetFont('','B',8);
			$this->SetTextColor(0, 64, 122);
			$this->Cell (0,5,$this->textos["criterios"],0,0,'L');
			$this->Ln();
			$this->SetFont('','',8);
			$this->Cell ($this->GetStringWidth('- Flota: '),6,'- Flota: ',0,0,'L');
			$this->SetFont('','B',8);
			$this->Cell (0,6,$this->flota,0,0,'L');
			$this->Ln();
		}
		if ($this->activa!="") {
			$this->SetFont('','B',8);
			$this->SetTextColor(0, 64, 122);
			$this->Cell (0,5,$this->textos["criterios"],0,0,'L');
			$this->Ln();
			$this->SetFont('','',8);
			$this->Cell ($this->GetStringWidth('- Activa: '),6,'- Activa: ',0,0,'L');
			$this->SetFont('','B',8);
			$this->Cell (0,6,$this->activa,0,0,'L');
			$this->Ln();
		}
		$this->SetFont('','B',8);
		$this->SetTextColor(0, 64, 122);
		$this->Cell (0,5,"- ".$this->textos["nreg"].": ".number_format($this->filas,0,',','.'),0,0,'L');
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
		$this->Cell(0, 10, $this->textos["pagina"].' '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 'T', 0, 'C');
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
		for ($i=0; $i < count ($anchos); $i++){
			$filas_celda = $this->GetNumLines ($fila[$i], $anchos[$i]);
			if ($filas_celda > $filas) {
				$filas = $filas_celda;
			}
		}
		for ($i=0; $i < count ($anchos); $i++){
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
				if ($par){
					$fill = 1;
				}
				$borde = 1;
				$alin = 'C';
				$this->SetFillColor(194, 194, 194);
				$this->SetTextColor(0, 0, 0);
				$this->SetFont('', '',8);
				/*if ($this->GetStringWidth($fila[$i]) > $anchos[$i]) {
					$this->MultiCell($anchos[$i], 5, $fila[$i], 1, 'L', 0, 0, '', '', true, 0);
				}
				else {
					$this->Cell($anchos[$i], 5*$filas, $fila[$i], 1, 0, 'L', $fill);
				}*/
			}
			if ($filas == 1){
				$this->Cell($anchos[$i], 5, $fila[$i], $borde, 0, $alin, $fill);
			}
			else{
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
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Oficina COMDES');
$pdf->SetTitle($h1);
$pdf->SetSubject($h1);
$pdf->SetKeywords('COMDES, Flotas, Flotes');

// Márgenes
$pdf->SetMargins(13, PDF_MARGIN_TOP, 14);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Campos de texto
$pdf->titulo = $h1;
$pdf->textos["pagina"] = $pgtxt;
$pdf->textos["nreg"] = $nreg;
$pdf->textos["criterios"] = $criterios;

// Salto automático de página
$pdf->SetAutoPageBreak(TRUE, 15);

// Factor de Escala de las imágenes
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

// Cadenas de texto dependientes del Idioma
$pdf->setLanguageArray($l); 

if ($permiso == 2){
	// Cabecera y anchos de tabla
	$pdf->cabecera= $campospdf;
	$pdf->anchos = array(10,80,25,25,30,25,25,25,25);
	// ---------------------------------------------------------
	
	// Consulta a la base de datos
	$sql = "SELECT ID, FLOTA, ACRONIMO, ENCRIPTACION FROM flotas WHERE 1";
	if (($flota!='')&&($flota!="00")){
		$sql.=" AND (flotas.ID='$flota')";
		$sql_flota = "SELECT * FROM flotas WHERE ID='$flota'";
		$res_flota=mysql_db_query($base_datos,$sql_flota) or die(mysql_error());
		$row_flota=mysql_fetch_array($res_flota);
		$pdf->flota = utf8_encode($row_flota["FLOTA"]);
                $pdf->activa = "";
	}
	if (($activa!='')&&($activa!="00")){
                $pdf->flota = "";
		$sql.=" AND (flotas.ACTIVO='$activa')";
                if ($activa=="SI"){
                    $pdf->activa = "Sí";
                }
                else{
                    $pdf->activa = "No";
                }
	}
	$sql.=" ORDER BY flotas.FLOTA ASC";
	$res=mysql_db_query($base_datos,$sql) or die(mysql_error());
	$nfilas=mysql_num_rows($res);
	$pdf->filas = $nfilas;
	
	// Establecemos la fuente por defecto
	$pdf->SetFont('helvetica', '', 8);
	// Añadir una página
	$pdf->AddPage();
	
	// Restauramos colores y fuente
	$pdf->SetFillColor(194, 194, 194);
	$pdf->SetDrawColor(0, 64, 122);
	$pdf->SetTextColor(0);
	$pdf->SetFont('');
        $campos = array (1, 1, 1, 1, 1, 1, 1, 1, 1);
	$fill = 0;
        $total = 0;
        $tterm = array (0, 0, 0, 0, 0);
	// Imprimir los datos de la consulta
	for($j=0;$j<$nfilas;$j++){
		$ncampos = mysql_num_fields($res);
		$fila=mysql_fetch_array($res);
		$filas = 1;
		for($i=0;$i<$ncampos;$i++){
                    $fila [$i] = utf8_encode($fila[$i]);
		}
		//datos de la tabla Terminales
		// Tipos de termninales
		$tipos = array("F","M%", "P%", "D");
		$nterm = array (0,0,0,0);
		$sql_term = "SELECT * FROM terminales WHERE FLOTA='$fila[0]'";
		$res_term = mysql_db_query($base_datos,$sql_term) or die ("Error en la consulta de Terminales".mysql_error());
                $tot_term = mysql_num_rows($res_term);
                $fila [4] = number_format($tot_term,0,',','.');
                $tterm[0] += $tot_term;
		for($i=0; $i< count($tipos);$i++){
                    $sql_term = "SELECT * FROM terminales WHERE FLOTA='$fila[0]' AND TIPO LIKE '".$tipos[$i]."'";
                    $res_term = mysql_db_query($base_datos,$sql_term) or die ("Error en la consulta de ".$cabecera[$j].": ".mysql_error());
                    $nterm[$i] = mysql_num_rows($res_term);
                    $fila [5 + $i] = number_format($nterm[$i],0,',','.');
                    $tterm[$i+1] += $nterm[$i];
		}
                $total = number_format($total,0,',','.');
                $pdf->ImprimeFila($fila, $pdf->anchos, $campos, $fill);
		$fill=!$fill;
	} //primer for
	$pdf->Cell(180, 0, '', 'T');
	$pdf->Ln(10);
        for ($i = 0; $i < count($tterm); $i++){
            $tterm[$i] = number_format($tterm[$i],0,',','.');
        }
        $fila = array ($totales, $tterm[0], $tterm[1], $tterm[2], $tterm[3], $tterm[4]);
        $anchos = array (140, 30, 25, 25, 25, 25);
        $campos = array (0, 1, 1, 1, 1, 1);
        $pdf->ImprimeFila($fila, $anchos, $campos, true);
	// Restauramos colores de fondo y texto
	/*$pdf->SetFillColor(92, 116, 61);
	$pdf->SetTextColor(213, 235, 179);
	$pdf->SetFont('', 'B',8);
	$pdf->Cell(90, 5, 'Totales', 1, 0, 'C', 1);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->Cell(30, 5, number_format($tterminales,0,',','.'), 1, 0, 'C', 0);
	$pdf->Cell(20, 5, number_format($tport,0,',','.'), 1, 0, 'C', 0);
	$pdf->Cell(20, 5, number_format($tmov,0,',','.'), 1, 0, 'C', 0);
	$pdf->Cell(20, 5, number_format($tbase,0,',','.'), 1, 0, 'C', 0);*/
}
else {
	$error = $permno;
	$pdf->SetTextColor(255, 0, 0);
	$pdf->SetFont('', 'B',10);
	$pdf->MultiCell(0,5,$error,0,'L',0);
	
}

//Close and output PDF document
$pdf->Output($nom_fichero.'.pdf', 'I');

?>
