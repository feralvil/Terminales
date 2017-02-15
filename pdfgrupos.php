<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/grupos_$idioma.php";
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
import_request_variables("p", "");

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
    /* CLASE PARA LA GENERACIÓN DEL PDF */
    require_once('tcpdf/config/lang/esp.php');
    require_once('tcpdf/tcpdf.php');

    // Extender la clase TCPDF para crear una cabecera y un pie de página propios
    class MYPDF extends TCPDF {

        var $pagina = "";
        var $titulo = "";

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
            $ancho = 130;
            if ($this->CurOrientation == "L"){
                $ancho = 220;
            }
            $this->Cell($ancho, 10, $this->titulo, 0, 0, 'C');
            // Logo 2
            //$this->Image('imagenes/logo.jpg');
            $this->Image('imagenes/logo_chap.png');
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
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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

    // Cadenas de texto dependientes del Idioma
    $pdf->setLanguageArray($l);
    // Establecemos la fuente por defecto
    $pdf->SetFont('helvetica', '', 8);
    // Título
    $pdf->titulo = $titulo;
    $pdf->pagina = $txtpag;
    // Añadir una página
    $pdf->AddPage();

    // Consulta a la base de datos - Tabla Flotas
    $sql_grupos = "SELECT * FROM grupos ORDER BY GISSI ASC";
    $res_grupos = mysql_query($sql_grupos) or die(mysql_error());
    $ngrupos = mysql_num_rows($res_grupos);

    if ($ngrupos > 0){
        // Número de Grupos
        $h3 = "<h3>Total: " . $ngrupos . " " . $nreg ."</h3>";
        $pdf->writeHTML($h3, true, true, true, false, '');
        $pdf->Ln(5);

        // Tabla con los grupos:
        $tablagrupos = <<<THEAD
        <table style="width:100%;" border="1">
            <thead>
                <tr style="background-color:#C0C0C0";>
                    <th style="font-weight:bold;text-align:center;width:20mm;">GISSI</th>
                    <th style="font-weight:bold;text-align:center;width:40mm;">$thmnemo</th>
                    <th style="font-weight:bold;text-align:center;width:30mm;">$thtipo</th>
                    <th style="font-weight:bold;text-align:center;width:90mm;">$thdesc</th>
                </tr>
            </thead>
THEAD;
        for ($i = 0; $i < $ngrupos; $i++){
            $grupo = mysql_fetch_array($res_grupos);
            if (($i % 2) == 1){
                $tablagrupos .= "<tr style='background-color:#EFEFEF;'>";
            }
            else{
                $tablagrupos .= "<tr>";
            }
            $tablagrupos .= "<td style='width:20mm;'>" . $grupo['GISSI'] . "</td>";
            $tablagrupos .= "<td style='width:40mm;'>" . $grupo['MNEMONICO'] . "</td>";
            $tablagrupos .= "<td style='width:30mm;'>" . $grupo['TIPO'] . "</td>";
            $tablagrupos .= "<td style='width:90mm;'>" . $grupo['DESCRIPCION'] . "</td>";
            $tablagrupos .= "</tr>";
        }
        $tablagrupos .= "</table>";
        $pdf->writeHTML($tablagrupos, true, true, true, false, '');
    }
    else{

    }



    // Generar y enviar el documento PDF
    $nomFichero = $nreg . "_COMDES.pdf";
    $pdf->Output($nomFichero, 'I');
}
else{
?>
    <!DOCTYPE html>
    <html>
        <head>
            <title><?php echo $titulo; ?></title>
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
            <h1><?php echo $h1perm ?></h1>
            <p class='error'><?php echo $errnoperm; ?></p>
        </body>
    </html>
<?php
}
?>
