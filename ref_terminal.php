<?php
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
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
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

if ($permiso == 2) {
    $fecha = date("Ymd");
    $hora = date("His");
    //datos de la tabla Flotas
    $sql_term = "SELECT * FROM terminales WHERE ID='$idterm'";
    $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminal: " . mysql_error());
    $nterm = mysql_num_rows($res_term);
    if ($nterm == 0) {
        echo "<p class='error'>No hay resultados en la consulta del terminal</p>\n";
    }
    else {
        $row_terminal = mysql_fetch_array($res_term);
        $numerok = $row_terminal["NUMEROK"];
        $tei = $row_terminal["TEI"];
        $issi = $row_terminal["ISSI"];        
        $itsi = "0x35800A" . sprintf('%06X', $issi);
        $marca = strtoupper(substr($row_terminal["MARCA"], 0, 1));
        $par = $marca . "0x$tei,$numerok";
        $nom_fichero = "Ref-K_$issi.txt";        
        $param_fichero = 1;        
        if ($parametro == "itsi"){
            $nom_fichero = "Ref-ITSI_".$issi.".txt";
            $param_fichero = 2;
            $par = $marca . "0x$tei,$itsi";
        }
        $fichero = fopen($nom_fichero, "w+") or die("Error al crear el archivo");
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
        fputs($fichero, "$par;\r\n");
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