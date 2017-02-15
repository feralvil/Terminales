<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotas_$idioma.php";
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
if ($flota_usu > 0){
    if ($flota_usu == 100) {
        $permiso = 2;
    }
}

// Clases para generar el Excel
set_time_limit(10);
require_once "excel/class.writeexcel_workbook.inc.php";
require_once "excel/class.writeexcel_worksheet.inc.php";

// Consulta a la base de datos
$sql = "SELECT ID, FLOTA, ACRONIMO, ENCRIPTACION FROM flotas WHERE 1";
if (($flota!='')&&($flota!="00")) {
    $sql.=" AND (flotas.ID='$flota')";
    $sql_flota = "SELECT * FROM flotas WHERE ID='$flota'";
    $res_flota=mysql_db_query($base_datos,$sql_flota) or die(mysql_error());
    $row_flota=mysql_fetch_array($res_flota);
    $flota_txt = $row_flota["FLOTA"];
}
if (($activa!='')&&($activa!="00")) {
    $sql.=" AND (flotas.ACTIVO='$activa')";
    if ($activa=="SI"){
        $activa = "Sí";
    }
    else{
        $activa = "No";
    }
}
$sql.=" ORDER BY flotas.FLOTA ASC";
$res=mysql_db_query($base_datos,$sql) or die(mysql_error());
$nfilas=mysql_num_rows($res);
$inicio = 2;

# Creamos el objeto Excel
$fname = tempnam("/tmp", "$nom_fichero.xls");
$workbook = &new writeexcel_workbook($fname);
$workbook->set_custom_color(59,0x00,0x40,0x7A); # Encabezado y Fondo de Cabecera (Azul Conselleria)
$workbook->set_custom_color(57,0xF0,0xF2,0xF5); # Color Personalizado: Fondo de Fila de Tabla

# Formatos Comunes
$gris  =& $workbook->addformat();
$gris->set_fg_color(57);
$heading =& $workbook->addformat();
$heading->set_bold();
$heading->set_color(59);
$heading->set_merge();
$heading->set_align('center');

$criterio =& $workbook->addformat();
$criterio->set_bold();
$criterio->set_color(59);
$criterio->set_text_wrap();
$criterio->set_align('left');

$worksheet1 =& $workbook->addworksheet($flotascomdes); # Se añade una nueva Hoja
$worksheet1->fit_to_pages(1, 0); # 1 página de ancho por N de Alto
$worksheet1->set_header('&C&"Helvetica,Bold"'.$h1,0.50); # Cabecera de la Página
$worksheet1->set_footer("&C".iconv("UTF-8","LATIN1","$pgtxt ")."&P de &N",0.50); # Pie de Página

$cabecera = $campospdf;
$anchos = array(5, 40, 15, 15, 20, 15, 15 , 15, 15);
$worksheet1->write(0, 0, $h1, $heading);
for($i = 1; $i < count($cabecera); $i++) {
    $worksheet1->write_blank(0, $i,  $heading);
}
$worksheet1->merge_cells (0,0,0,count($cabecera)-1);

if (($flota!='')&&($flota!="00")) {
    $worksheet1->write(2, 0, iconv("UTF-8","LATIN1", "$criterios:"), $heading);
    $worksheet1->write_blank(2, 1,  $heading);
    $worksheet1->merge_cells (2,0,2,1);
    $fila_inicio = 3;
    $col_inicio = 1;
    $worksheet1->write($fila_inicio, $col_inicio, "- Flota: ", $criterio);
    $worksheet1->write($fila_inicio, $col_inicio+1, $flota_txt);
    $inicio+=2;
}
if (($activa!='')&&($activa!="00")) {
    $worksheet1->write(2, 0, iconv("UTF-8","LATIN1", "$criterios:"), $heading);
    $worksheet1->write_blank(2, 1,  $heading);
    $worksheet1->merge_cells (2,0,2,1);
    $fila_inicio = 3;
    $col_inicio = 1;
    $worksheet1->write($fila_inicio, $col_inicio, "- Activa: ", $criterio);
    $worksheet1->write($fila_inicio, $col_inicio+1, $activa);
    $inicio+=2;
}
$worksheet1->write($inicio, 0, iconv("UTF-8","LATIN1","$nreg: ").number_format($nfilas,0,',','.'), $heading);
$worksheet1->write_blank($inicio, 1,  $heading);
$worksheet1->write_blank($inicio, 2,  $heading);
$worksheet1->merge_cells ($inicio,0,$inicio,2);
$inicio +=2;
$worksheet1->repeat_rows($inicio);    # Repetimos la cabecera de la Tabla en cada hoja al imprimir
$format =& $workbook->addformat();
$format->set_fg_color(59);
$format->set_bold();
$format->set_pattern(0x1);
$format->set_color('white');
$format->set_align('center');
$worksheet1->freeze_panes($inicio+1,0);
for($i = 0; $i < count($cabecera); $i++) {
    $worksheet1->set_column($i, $i, $anchos[$i]);
    $worksheet1->write($inicio, $i, iconv("UTF-8","LATIN1",$cabecera[$i]), $format);
}
$inicio++;
$tterm = array (0,0,0,0,0);
// Imprimir los datos de la consulta
for($j=0;$j<$nfilas;$j++) {
    $fila=mysql_fetch_array($res);
    $tipos = array("F", "M%", "P%", "D");
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
        $fila [5 + $i] = number_format($nterm[$i],0,'.',',');
        $tterm[$i+1] += $nterm[$i];
    }
    for($i=0 ; $i<count ($cabecera) ; $i++) {
        if (($j%2)==0){
            $worksheet1->write($inicio+$j, 0+$i, iconv("UTF-8","LATIN1",$fila[$i]));
        }
        else{
            $worksheet1->write($inicio+$j, 0+$i, iconv("UTF-8","LATIN1",$fila[$i]),$gris);
        }
    }//segundo for
} //primer for
$inicio += $nfilas;
for($i=0 ; $i<count ($cabecera) ; $i++) {
    $worksheet1->write($inicio, 0+$i, "");
}
$inicio++;
$worksheet1->write($inicio, 0, $totales, $format);
for($i = 1; $i < 4; $i++) {
    $worksheet1->write_blank($inicio, $i,  $format);
}
$worksheet1->merge_cells ($inicio,0,$inicio,3);
for ($i=0 ; $i < count($tterm); $i++){
    $worksheet1->write($inicio, 4+$i, $tterm[$i],$gris);
}

$workbook->close();

header("Content-Type: application/x-msexcel; name=\"$nom_fichero.xls\"");
header("Content-Disposition: inline; filename=\"$nom_fichero.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);

?>
