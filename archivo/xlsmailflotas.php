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
$lang = "idioma/flotamail_$idioma.php";
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
} else {
    // Codificación de carácteres de la conexión a la BBDD
    mysql_set_charset('utf8', $link);
}
// ------------------------------------------------------------------------------------- //

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_db_query($base_datos, $sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}

// Clases para generar el Excel
set_time_limit(10);
require_once "excel/class.writeexcel_workbook.inc.php";
require_once "excel/class.writeexcel_worksheet.inc.php";

// Consulta a la base de datos
$sql_flotas = "SELECT * FROM flotas WHERE 1";
$txtprov = "";
if (!(empty($idflota))) {
    $sql_flotas .= " AND flotas.ID IN (";
    for ($i = 0; $i < count($idflota); $i++) {
        $sql_flotas .= $idflota[$i];
        if ($i < (count($idflota) - 1)) {
            $sql_flotas .= ", ";
        }
    }
    $sql_flotas .= ")";
}
$sql_flotas.=" ORDER BY flotas.FLOTA ASC";
$res = mysql_db_query($base_datos, $sql_flotas) or die(mysql_error());
$nfilas = mysql_num_rows($res);
$inicio = 2;

# Creamos el objeto Excel
$fname = tempnam("/tmp", "$nom_fichero.xls");
$workbook = &new writeexcel_workbook($fname);
$workbook->set_custom_color(59, 0x00, 0x40, 0x7A); # Encabezado y Fondo de Cabecera (Azul Conselleria)
$workbook->set_custom_color(57, 0xF0, 0xF2, 0xF5); # Color Personalizado: Fondo de Fila de Tabla
# Formatos Comunes
$gris = & $workbook->addformat();
$gris->set_fg_color(57);
$heading = & $workbook->addformat();
$heading->set_bold();
$heading->set_color(59);
$heading->set_merge();
$heading->set_align('center');

$criterio = & $workbook->addformat();
$criterio->set_bold();
$criterio->set_color(59);
$criterio->set_text_wrap();
$criterio->set_align('left');

$worksheet1 = & $workbook->addworksheet($flotascomdes); # Se añade una nueva Hoja
$worksheet1->fit_to_pages(1, 0); # 1 página de ancho por N de Alto
$worksheet1->set_header('&C&"Helvetica,Bold"' . $h1, 0.50); # Cabecera de la Página
$worksheet1->set_footer("&C" . iconv("UTF-8", "LATIN1", "$pgtxt ") . "&P de &N", 0.50); # Pie de Página

$cabecera = $campospdf;
$anchos = array(5, 40, 15, 20, 40, 40, 40);
$worksheet1->write(0, 0, $h1, $heading);
for ($i = 1; $i < count($cabecera); $i++) {
    $worksheet1->write_blank(0, $i, $heading);
}
$worksheet1->merge_cells(0, 0, 0, count($cabecera) - 1);

if (($prov != '') && ($prov != "00")) {
    $worksheet1->write(2, 0, iconv("UTF-8", "LATIN1", "$criterios:"), $heading);
    $worksheet1->write_blank(2, 1, $heading);
    $worksheet1->merge_cells(2, 0, 2, 1);
    $fila_inicio = 3;
    $col_inicio = 1;
    $worksheet1->write($fila_inicio, $col_inicio, iconv("UTF-8", "LATIN1", "- $provincia: "), $criterio);
    $worksheet1->write($fila_inicio, $col_inicio + 1, iconv("UTF-8", "LATIN1", $txtprov));
    $inicio+=2;
}
$worksheet1->write($inicio, 0, iconv("UTF-8", "LATIN1", "$nreg: ") . number_format($nfilas, 0, ',', '.'), $heading);
$worksheet1->write_blank($inicio, 1, $heading);
$worksheet1->write_blank($inicio, 2, $heading);
$worksheet1->merge_cells($inicio, 0, $inicio, 2);
$inicio +=2;
$worksheet1->repeat_rows($inicio);    # Repetimos la cabecera de la Tabla en cada hoja al imprimir
$format = & $workbook->addformat();
$format->set_fg_color(59);
$format->set_bold();
$format->set_pattern(0x1);
$format->set_color('white');
$format->set_align('center');
$worksheet1->freeze_panes($inicio + 1, 0);
for ($i = 0; $i < count($cabecera); $i++) {
    $worksheet1->set_column($i, $i, $anchos[$i]);
    $worksheet1->write($inicio, $i, iconv("UTF-8", "LATIN1", $cabecera[$i]), $format);
}
$inicio++;
// Imprimir los datos de la consulta
$fila = array("","","","","","","");
for ($j = 0; $j < $nfilas; $j++) {
    $row_flota = mysql_fetch_array($res);
    $fila [0] = $row_flota["ID"];
    $fila [1] = $row_flota["FLOTA"];
    $fila [2] = $row_flota["ACRONIMO"];
    // Datos de contactos
    $id_contacto = array($row_flota["RESPONSABLE"], $row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
    $nom_contacto = array("Responsable", $contacto . " 1", $contacto . " 2", $contacto . " 3");
    // Datos de contactos
    $mailok = false;
    $nombre = $email = $cargo = $tipoc = "-";
    for ($i = 0; $i < count($id_contacto); $i++) {
        if (!$mailok){
            if ($id_contacto[$i] != 0) {
                $idc = $id_contacto[$i];
                $sql_contacto = "SELECT * FROM contactos WHERE ID=$idc";
                $res_contacto = mysql_db_query($base_datos, $sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
                if ($ncontacto != 0) {
                    $row_contacto = mysql_fetch_array($res_contacto);
                    if (($row_contacto["NOMBRE"]!="")&&($row_contacto["MAIL"]!="")){
                        $nombre = $row_contacto["NOMBRE"];
                        $email = $row_contacto["MAIL"];
                        $cargo = $row_contacto["CARGO"];
                        $tipoc = $nom_contacto[$i];
                        $mailok = true;//eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
                    }
                }
            }
        }
    }
    $fila [3] = $tipoc;
    $fila [4] = $nombre;
    $fila [5] = $cargo;
    $fila [6] = $email;
    for ($i = 0; $i < count($cabecera); $i++) {
        if (($j % 2) == 0) {
            $worksheet1->write($inicio + $j, 0 + $i, iconv("UTF-8", "LATIN1", $fila[$i]));
        } else {
            $worksheet1->write($inicio + $j, 0 + $i, iconv("UTF-8", "LATIN1", $fila[$i]), $gris);
        }
    }//segundo for
} //primer for
$inicio += $nfilas;

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"$nom_fichero.xls\"");
header("Content-Disposition: inline; filename=\"$nom_fichero.xls\"");
$fh = fopen($fname, "rb");
fpassthru($fh);
unlink($fname);
?>
