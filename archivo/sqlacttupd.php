<?php
// Revisado 2011-08-09
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
$lang = "idioma/actterm_$idioma.php";
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
elseif ($flota_usu == $idflota) {
    $permiso = 1;
}
if ($permiso > 0) {
    // Nueva Actuación
    if ($origen == "nueva"){
        $repetido = false;
        if ($tipoact == "ALTA") {
            $enlaceok = "actterminales.php";
            $mensok = $mensnewact;
            $enlacefail = "nueva_actuacion.php";
            $mensfail = $errnewact;
            
            // Comprobamos que el nuevo ISSI no está repetido:
            $sql_issi = "SELECT * FROM terminales WHERE ISSI='$issi'";
            $res_issi = mysql_query($sql_issi) or die("Error en la consulta de ISSI: " . mysql_error());
            $nissi = mysql_num_rows($res_issi);
            if ($nissi > 0) {
                $repetido = true;
                $mensfail = $errissirepalta;
            }
            else {
                // Construimos la consulta para guardar la Actuación:
                // Fecha
                $fecha = date("Y-m-d");
                // Campos
                $campos = "(ACTUACION_ID, TERMINAL_ID, TIPOACT, FPETICION, ISSIOLD, ISSINEW, MARCA, MODELO, TIPO, PROVEEDOR, AM, ";
                $campos .= "VERSION, TEI, NSERIE, MNEMONICO, CARPETA, DUPLEX, SEMID, OBSERVACIONES, NUMEROK, DIRIP)";
                // Valores
                $valores = "($idactuacion, 0, 'ALTA', '$fecha', '-', '$issi', '$marca', '$modelo', '$tipo', '$proveedor', '$am', ";
                $valores .= "'$version', '$tei', '$mnemonico', '$carpeta', '$duplex', '$semid', '$observaciones', '$numerok', '$dirip')";
            }
        }
        if ($tipoact == "MOD") {
            // Comprobamos que el terminal a dar de baja existe:
            $sql_term = "SELECT * FROM terminales WHERE ID='$idterm'";
            $res_term = mysql_query($sql_term) or die("Error en la consulta de ISSI: " . mysql_error());
            $nterm = mysql_num_rows($res_term);
            if ($nterm == 0) {
                $repetido = true;
                $mensfail = $errnotermmod;
            }
            else {
                $row_term = mysql_fetch_array($res_term);
                $issiold = $row_term["ISSI"];
                // Construimos la consulta para guardar la Actuación:
                if ($issi != ""){
                    // Comprobamos que el nuevo ISSI no está repetido:
                    $sql_issi = "SELECT * FROM terminales WHERE ISSI='$issi'";
                    $res_issi = mysql_query($sql_issi) or die("Error en la consulta de ISSI: " . mysql_error());
                    $nissi = mysql_num_rows($res_issi);
                    if ($nissi > 0) {
                        $repetido = true;
                        $mensfail = $errissirepmod;
                    }
                }
                if (!$repetido){
                    // Fecha
                    $fecha = date("Y-m-d");
                    // Campos
                    $campos = "(ACTUACION_ID, TERMINAL_ID, TIPOACT, FPETICION, ISSIOLD, ISSINEW, MARCA, MODELO, TIPO, PROVEEDOR, AM, ";
                    $campos .= "VERSION, TEI, NSERIE, MNEMONICO, CARPETA, DUPLEX, SEMID, OBSERVACIONES, NUMEROK, DIRIP)";
                    // Valores
                    $valores = "($idactuacion, 0, 'ALTA', '$fecha', '$issiold', '$issi', '$marca', '$modelo', '$tipo', '$proveedor', '$am', ";
                    $valores .= "'$version', '$tei', '$mnemonico', '$carpeta', '$duplex', '$semid', '$observaciones', '$numerok', '$dirip')";
                }
            }
        }
        
        if ($tipoact == "BAJA") {
            // Comprobamos que el terminal a dar de baja existe:
            $sql_term = "SELECT * FROM terminales WHERE ID='$idterm'";
            $res_term = mysql_query($sql_term) or die("Error en la consulta de ISSI: " . mysql_error());
            $nterm = mysql_num_rows($res_term);
            if ($nterm == 0) {
                $repetido = true;
                $mensfail = $errnotermbaja;
            }
            else {
                $row_term = mysql_fetch_array($res_term);
                // Construimos la consulta para guardar la Actuación:
                // Fecha
                $fecha = date("Y-m-d");
                // Campos
                $campos = "(ACTUACION_ID, TERMINAL_ID, TIPOACT, FPETICION, ISSIOLD, ISSINEW, MARCA, MODELO, TIPO, PROVEEDOR, AM, ";
                $campos .= "VERSION, TEI, NSERIE, MNEMONICO, CARPETA, DUPLEX, SEMID, OBSERVACIONES, NUMEROK, DIRIP)";
                // Valores
                $valores = "($idactuacion, $idterm, 'BAJA', '$fecha', '".$row_term["ISSI"]."', '-', '".$row_term["MARCA"]."', ";
                $valores .= "'".$row_term["MODELO"]."', '".$row_term["TIPO"]."', '".$row_term["PROVEEDOR"]."', '".$row_term["AM"]."', ";
                $valores .= "'".$row_term["VERSION"]."', '".$row_term["TEI"]."', '".$row_term["NSERIE"]."', '".$row_term["MNEMONICO"]."', ";
                $valores .= "'".$row_term["CARPETA"]."', '".$row_term["DUPLEX"]."', '".$row_term["SEMID"]."', '".$row_term["OBSERVACIONES"]."', ";
                $valores .= "'".$row_term["NUMEROK"]."', '".$row_term["DIRIP"]."')";
            }
        }
        // Construimos la consulta:
        $sql_update = "INSERT INTO actterminales $campos VALUES $valores";
    }
    // $res_update = mysql_query($sql_update) or die (mysql_error($link));
    if ($res_update){
        $enlace = $enlaceok;
        $mensflash = $mensok;
        $update = "OK";
    }
    else{
        $enlace = $enlacefail;
        $mensflash = $mensfail;
        $update = "KO";
    }
?>
        <form name="formupd" id="formupd" action="<?php echo $enlace;?>" method="POST">
            <input name="idactuacion" type="hidden" value="<?php echo $idactuacion;?>">
<?php
            if ($permiso == 2){
?>
                <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
<?php
            }
?>
            <input name="update" type="hidden" value="<?php echo $update;?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
        </form>
        <h2>Consulta generada</h2>
        <p>SQL UPDATE = <?php echo $sql_update; ?></p>
        
<?php
    }
    else {
?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
    }
?>
