<?php
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos=$dbbdatos;
$link=mysql_connect($dbserv,$dbusu,$dbpaso);
if(!link) {
    echo "<b>ERROR MySQL:</b>".mysql_error();
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

if($permiso==2) {
    $fecha = date("Ymd");
    $hora = date("His");
    //datos de la tabla Flotas
    $sql_flota="SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota=mysql_query($sql_flota) or die ("Error en la consulta de Flota: ".mysql_error());
    $nflota=mysql_num_rows($res_flota);
    if($nflota==0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota=mysql_fetch_array($res_flota);
        $sql_term = "SELECT * FROM terminales WHERE FLOTA='$idflota'";
        if (($carpeta != "") && ($carpeta != "NN")) {
            $nom_fichero = "Ref-ITSI_".$row_flota["ACRONIMO"]."_".$carpeta.".txt";
            $sql_term .= " AND (CARPETA = '".$carpeta."')";
        }
        if ($issi != '') {
            $sql_term .= " AND (ISSI = '".$issi."')";
        }
        if ($tei != '') {
            $sql_term .= " AND (TEI = '".$tei."')";
        }
        if (!(empty ($termsel))){
            $sql_term .= " AND terminales.ID IN (";
            for ($i = 0; $i < count($termsel); $i++){
                $sql_term .= $termsel[$i];
                if ($i < (count($termsel) - 1)){
                    $sql_term .= ", ";
                }
            }
            $sql_term .= ")";
        }
        $sql_term .= " ORDER BY ISSI ASC";
        $res_term = mysql_query($sql_term) or die ("Error en la consulta de Terminales".mysql_error());
        $nterm = mysql_num_rows($res_term);
        $nom_fichero = "Ref-ITSI_".$row_flota["ACRONIMO"].".txt";
        $param_fichero = 2;
        if ($parametro == "numk"){
            $nom_fichero = "Ref-K_".$row_flota["ACRONIMO"].".txt";
            $param_fichero = 1;
        }
        $fichero = fopen($nom_fichero, "w+") or die ("Error al crear el archivo");
        fputs($fichero, "1,1;\r\n");
        fputs($fichero, "$fecha;\r\n");
        fputs($fichero, "$hora;\r\n");
        fputs($fichero, "Nokia Offline Authentication Key Tool;\r\n");
        fputs($fichero, "EADS Spain;\r\n");
        fputs($fichero, "5870856-1;\r\n");
        fputs($fichero, "NONE;\r\n");
        fputs($fichero, "$param_fichero;\r\n");
        fputs($fichero, ";;;;;;;;\r\n");
        fputs($fichero, "$nterm;\r\n");
        for ($i = 0; $i < $nterm; $i++){
            $row_terminal=mysql_fetch_array($res_term);
            $tei = $row_terminal["TEI"];
            $issi = $row_terminal["ISSI"];
            $itsi = "0x35800A".sprintf('%06X', $issi);
            $marca = strtoupper(substr($row_terminal["MARCA"],0,1));
            $numerok = $row_terminal["NUMEROK"];
            $par = $marca."0x$tei,$itsi";
            if ($parametro == "numk"){
                $par = $marca."0x$tei,$numerok";
            }
            fputs ($fichero, "$par;\r\n");
        }
        fputs($fichero, ":");
        fclose($fichero);
        header("Content-Type: text/plain; name=\"$nom_fichero\"");
        header("Content-Disposition: attachment; filename=\"$nom_fichero\"");
        $fichero = fopen($nom_fichero, "rb");
        fpassthru($fichero);
        fclose($fichero);
        unlink($nom_fichero);
    }
}
?>
