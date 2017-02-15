<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotadet_$idioma.php";
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
else {    
    if ($idflota > 0){
        if ($flota_usu == $idflota) {
            $permiso = 1;
        }
    }
    else{
        $permiso = 1;
        $idflota = $flota_usu;   
    }   
}

/* CLASE PARA LA GENERACIÓN DEL PDF */
require_once('tcpdf/config/lang/esp.php');
require_once('tcpdf/tcpdf.php');

// Extender la clase TCPDF para crear una cabecera y un pie de página propios
class MYPDF extends TCPDF {

    var $pagina = "";

    //Cabecera
    public function Header() {
        // Logo
        $this->Image('imagenes/comdes2.png', 20);
        // Establecemos la fuente y colores
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('helvetica', 'B', 12);
        // Nos desplazamos a a la derecha
        $this->Cell(20);
        // Espacio en Blanco: Determinamos si es página Vertical u Horizontal
        $ancho = 140;
        if ($this->CurOrientation == "L"){
            $ancho = 230;
        }
        $this->Cell($ancho, 10, '', 0, 0, 'C');
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
        $this->SetDrawColor(0, 0, 0);
        $this->SetTextColor(0, 0, 0);
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
                $this->SetFillColor(192, 192, 192);
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

// Información de documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Oficina COMDES');
$pdf->SetTitle($titulo);
$pdf->SetSubject($titulo);
$pdf->SetKeywords('COMDES, Flota');

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
// Título
$pdf->pagina = $pagina;
// Añadir una página
$pdf->AddPage();

// ---------------------------------------------------------

if ($permiso > 0) {
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
        $error = $errnoflota;
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetFont('', 'B', 10);
        $pdf->MultiCell(0, 5, $error, 0, 'L', 0);
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $usuario = $row_flota["LOGIN"];
        //datos de la tabla Terminales
        $sql_term = "SELECT * FROM terminales WHERE FLOTA='$idflota' ORDER BY ISSI ASC";
        $res_term = mysql_query($sql_term) or die("Error en la consulta de $terminales: " . mysql_error());
        $nterm = mysql_num_rows($res_term);
        //datos de la tabla Municipio
        // INE
        $ine = $row_flota["INE"];
        $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
        $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
        $nmun = mysql_num_rows($res_mun);
        if ($nmun == 0) {
            $error = $errnoflota;
            $pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont('', 'B', 10);
            $pdf->MultiCell(0, 5, $error, 0, 'L', 0);
        }
        else {
            $row_mun = mysql_fetch_array($res_mun);
        }

        // Imprimimos la cabecera
        $pdf->SetFillColor(192, 192, 192);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B', 12);
        $titulo = substr($h2admin, 0, 5)." DE FLOTA";
        $pdf->MultiCell(0, 5, $titulo, 0, 'C', TRUE);
        $pdf->Ln(5);
        $pdf->SetFont('', '', 10);
        $fila_imp = array($organiza, $row_flota["FLOTA"], $acroflota, $row_flota["ACRONIMO"]);
        $anchos_imp = array(30, 100, 20, 30);
        $campos_imp = array(0, 1, 0, 1);
        $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
        $pdf->Ln(5);
        $pdf->SetFont('', 'B', 10);
        $pdf->MultiCell(0, 5, $h2resp, 0, 'L', 0);
        $pdf->SetFont('', '', 10);
        
        if ($row_flota["RESPONSABLE"] != 0){
            $idc = $row_flota["RESPONSABLE"];
            // Consulta a la base de datos - Tabla Contactos
            $sql_contacto = "SELECT * FROM contactos  WHERE ID = $idc";
            $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0){
                $row_contacto = mysql_fetch_array($res_contacto);
                $anchos_imp = array(30, 100, 20, 30);
                $fila_imp = array($nomflota, $row_contacto["NOMBRE"], "N.I.F.", $row_contacto["NIF"]);
                $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
                $anchos_imp = array(30, 150);
                $fila_imp = array($cargo, $row_contacto["CARGO"]);
                $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
                $fila_imp = array($domicilio, $row_flota["DIRECCION"]);
                $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
                $anchos_imp = array(30, 90, 20, 40);
                $fila_imp = array($ciudad, $row_mun["MUNICIPIO"], $provincia, $row_mun["PROVINCIA"]);
                $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
                $anchos_imp = array(30, 40, 30, 80);
                $fila_imp = array($telefono, $row_contacto["TELEFONO"], substr($mail, 0, 6)."-e", $row_contacto["MAIL"]);
                $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
            }
            else {
                $pdf->SetTextColor(255, 0, 0);
                $pdf->SetFont('', 'B', 10);
                $pdf->MultiCell(0, 5, $errnoresp, 0, 'L', 0);
            }
        }
        else{
            $pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont('', 'B', 10);
            $pdf->MultiCell(0, 5, $errnoresp, 0, 'L', 0);
        }
        $pdf->Ln(5);

        // Datos de Contactos
        $pdf->SetFont('', 'B', 10);
        $pdf->MultiCell(0, 5, $h3flota, 0, 'L', 0);
        $pdf->SetFont('', '', 10);
        if (($row_flota["CONTACTO1"] == "0") && ($row_flota["CONTACTO2"] == "0") && ($row_flota["CONTACTO3"] == "0")) {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont('', 'B', 10);
            $pdf->Cell(0, 5, $nocont, 0, 'L', 0);
            $pdf->Ln();
        }
        else {
            $idc = array($row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
            for ($j = 0; $j < count($idc); $j++) {
                $itera = $j+1;
                if ($idc[$j] != 0) {
                    $id_contacto = $idc[$j];
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $id_contacto";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de $contacto: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto != 0) {
                        $row_contacto = mysql_fetch_array($res_contacto);
                        $anchos_imp = array(25, 85, 20, 50);
                        $fila_imp = array("$nomflota $itera", $row_contacto["NOMBRE"], $cargo, $row_contacto["CARGO"]);
                        $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
                        $campos_imp = array (0, 1, 0, 1, 0, 1);
                        $anchos_imp = array(25, 30, 25, 30, 20, 50);
                        $fila_imp = array($telefono, $row_contacto["TELEFONO"], $movil, $row_contacto["MOVIL"], substr($mail, 0, 6)."-e", $row_contacto["MAIL"]);
                        $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
                    }
                    else {
                        $pdf->SetTextColor(255, 0, 0);
                        $pdf->SetFont('', 'B', 10);
                        $pdf->Cell(0, 5, "No hay resultados en la consulta del $contacto $itera", 0, 'L', FALSE);
                    }
                }
                $pdf->Ln(5);
            }
        }

        // Datos de Contactos para incidencias
        $pdf->SetFont('', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, $h3incid, 0, 'L', 0);
        $pdf->SetFont('', '', 10);
        if (($row_flota["INCID1"] == "0") && ($row_flota["INCID2"] == "0") && ($row_flota["INCID2"] == "0") && ($row_flota["INCID3"] == "0")) {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->SetFont('', 'B', 10);
            $pdf->Cell(0, 5, $noincid, 0, 'L', 0);
            $pdf->Ln();
        }
        else {
            // Cabecera de la tabla:
            $campos_imp = array(0,0,0,0,0);
            $anchos_imp = array(60,30,25,35,30);
            $fila_imp = array($nomflota, $cargo, $telefono, substr($mail, 0, 6)."-e", $horario);
            $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
            // Contactos
            $campos_imp = array(1,1,1,1,1);
            $idc = array($row_flota["INCID1"], $row_flota["INCID2"], $row_flota["INCID3"], $row_flota["INCID4"]);
            for ($j = 0; $j < count($idc); $j++) {
                $itera = $j+1;
                if ($idc[$j] != 0) {
                    $id_contacto = $idc[$j];
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $id_contacto";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de $contacto: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto != 0) {
                        $row_contacto = mysql_fetch_array($res_contacto);
                        $fila_imp = array($row_contacto["NOMBRE"], $row_contacto["CARGO"], $row_contacto["MOVIL"], $row_contacto["MAIL"], $row_contacto["HORARIO"]);
                        $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, $relleno);
                    }
                    else {
                        $pdf->SetTextColor(255, 0, 0);
                        $pdf->SetFont('', 'B', 10);
                        $pdf->Cell(0, 5, "No hay resultados en la consulta del $contacto $itera", 0, 'L', $relleno);
                    }
                    $relleno = !$relleno;
                }
            }
        }

        // Datos de Terminales
        // Añadimos página en Apaisado:
        $pdf->AddPage('L','A4');
        $pdf->SetFont('', 'B', 12);
        $titulo = strtoupper($terminales);
        $pdf->MultiCell(0, 5, $titulo, 0, 'C', TRUE);
        $pdf->Ln(5);
        $pdf->SetFont('', '', 10);
        $fila_imp = array($organiza, $row_flota["FLOTA"]);
        $anchos_imp = array(50, 150);
        $campos_imp = array(0, 1);
        $pdf->ImprimeFila($fila_imp, $anchos_imp, $campos_imp, false);
        $pdf->Ln(5);
        $pdf->MultiCell(0, 5, strtoupper("$totalterm: $nterm"), 0, 'L', FALSE);
        $pdf->Ln(5);
        $pdf->SetFont('', '', 8);
        if ($nterm > 0){
            $tablaterm = <<<THEAD
            <table width="100%" border="1">
                <thead>
                    <tr style="background-color:#C0C0C0";>
                        <th rowspan="2" align="center"><b>Nº</b></th>
                        <th colspan="6" align="center"><b>TERMINAL</b></th>
                        <th rowspan="2" align="center"><b>ISSI</b></th>
                        <th rowspan="2" align="center"><b>TEI</b></th>
                        <th rowspan="2" align="center"><b>Nº Serie</b></th>
                        <th rowspan="2" align="center"><b>$cabeceraexp[0]</b></th>
                        <th rowspan="2" align="center"><b>Carpeta</b></th>
                        <th colspan="2" align="center"><b>$cabeceraexp[1]</b></th>
                        <th rowspan="2" align="center"><b>$cabeceraexp[2]</b></th>
                        <th rowspan="2" align="center"><b>$cabeceraexp[3]</b></th>
                        <th rowspan="2" align="center"><b>Alta DOTS</b></th>
                        <th rowspan="2" align="center"><b>K</b></th>
                    </tr>
                    <tr style="background-color:#C0C0C0";>
                        <th align="center"><b>Marca</b></th>
                        <th align="center"><b>$cabeceraexp[4]</b></th>
                        <th align="center"><b>$cabeceraexp[5]</b></th>
                        <th align="center"><b>$cabeceraexp[6]</b></th>
                        <th align="center"><b>Cod. Hw.</b></th>
                        <th align="center"><b>A.M.</b></th>
                        <th align="center"><b>D</b></th>
                        <th align="center"><b>S-D</b></th>
                    </tr>
                </thead>
THEAD;
            for ($i = 0; $i < $nterm; $i++){
                $row_term = mysql_fetch_array($res_term);
                $tablaterm .= "<tr";
                if (($i % 2) == 1){
                    $tablaterm .= " style=\"background-color:#F0F2F5\";";
                }
                $tablaterm .= ">";
                $tablaterm .= "<td align='center'>".($i + 1)."</td>";
                $tablaterm .= "<td>".$row_term["MARCA"]."</td>";
                $tablaterm .= "<td>".$row_term["MODELO"]."</td>";
                $tablaterm .= "<td>".$row_term["TIPO"]."</td>";
                $tablaterm .= "<td>".$row_term["PROVEEDOR"]."</td>";
                $tablaterm .= "<td>".$row_term["CODIGOHW"]."</td>";
                $tablaterm .= "<td>".$row_term["AM"]."</td>";
                $tablaterm .= "<td>".$row_term["ISSI"]."</td>";
                $tablaterm .= "<td>".$row_term["TEI"]."</td>";
                $tablaterm .= "<td>".$row_term["NSERIE"]."</td>";
                $tablaterm .= "<td>".$row_term["MENMONICO"]."</td>";
                $tablaterm .= "<td>".$row_term["CARPETA"]."</td>";
                $tablaterm .= "<td>".$row_term["DUPLEX"]."</td>";
                $tablaterm .= "<td>".$row_term["SEMID"]."</td>";
                $tablaterm .= "<td>".$row_term["OBSERVACIONES"]."</td>";
                $tablaterm .= "<td>".$row_term["FALTA"]."</td>";
                $tablaterm .= "<td>".$row_term["DOTS"]."</td>";
                $tablaterm .= "<td>".$row_term["NUMEROK"]."</td>";
                $tablaterm .= "</tr>";
            }
            $tablaterm .= "</table>";
            $pdf->writeHTML($tablaterm, true, true, true, false, '');;
        }
    }
}
else {
    $error = "$h1perm. $permno";
    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetFont('', 'B', 10);
    $pdf->MultiCell(0, 5, $error, 0, 'L', 0);
}

// Generar y enviar el documento PDF
$pdf->Output("Flota_COMDES-$id.pdf", 'I');
?>
