<?php
// Revisado 2011-07-14
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
$lang = "idioma/flotainf_$idioma.php";
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
else{
    // Codificación de carácteres de la conexión a la BBDD
    mysql_set_charset('utf8',$link);
}
// ------------------------------------------------------------------------------------- //

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_db_query($base_datos, $sql_oficina);
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
?>
<html>
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body>
<?php
    if ($permiso == 2) {
?>
        <h1><?php echo $h1; ?></h1>
        <form action="inf_geoflotas.php" name="formulario" method="POST">
            <h4><?php echo $criterios; ?></h4>
            <table>
                <tr>
                    <td>
                        <select name='prov' onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($prov == "00") || ($prov == "")) echo ' selected'; ?>>Seleccione <?php echo $provincia;?></option>
                            <option value='03' <?php if ($prov == "03") echo ' selected'; ?>><?php echo $alc;?></option>
                            <option value='12' <?php if ($prov == "12") echo ' selected'; ?>><?php echo $cas;?></option>
                            <option value='46' <?php if ($prov == "46") echo ' selected'; ?>><?php echo $val;?></option>
                        </select>
                    </td>
                </tr>
            </table>
<?php
            $sql_flotas = "SELECT ID, flotas.FLOTA, ACRONIMO FROM flotas WHERE 1";
            if (($prov != '') && ($prov != "00")) {
                $sql_flotas = $sql_flotas . " AND (flotas.INE LIKE '$prov%')";
            }
            $sql_flotas = $sql_flotas . " ORDER BY flotas.FLOTA ASC";
            $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die(mysql_error());
            $nflotas = mysql_num_rows($res_flotas);
            ########### Enlaces para la exportación #######
            $linkpdf = "pdfflotas.php?flota=$flota&activa=$activa";
            $linkxls = "xlsflotas.php?flota=$flota&activa=$activa";
            $linkrtf = "rtfflotas.php?flota=$flota&activa=$activa";
?>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nflotas; ?></b>.</td>
<?php
                    if ($nflotas > 0) {
?>
                    <td class="borde">
                        <a href="<?php echo $linkpdf; ?>"><img src="imagenes/pdf.png" alt="PDF" title="PDF"></a> &mdash;
                        <a href="<?php echo $linkxls; ?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
                    </td>
<?php
                    }
?>
                </tr>
            </table>
        </form>
        <table>
<?php
            if ($nflotas == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
            }
            else {
                $ncampos = mysql_num_fields($res_flotas);
                $tterm = array(0, 0, 0, 0);
                //*TABLA CON RESULTADOS*//
?>
                <tr>
<?php
                //* CABECERA  *//
                for ($i = 0; $i < count($campos); $i++) {
?>
                    <th><?php echo $campos[$i]; ?></th>
<?php
                }
?>
                </tr>
<?php
                for ($i = 0; $i < $nflotas; $i++) {
                    $row_flota = mysql_fetch_array($res_flotas);
?>
                <tr <?php if (($i % 2) == 1) {echo " class='filapar'";}?>>
<?php
                for ($j = 0; $j < $ncampos; $j++) {
                    //$campo_num = mysql_field_name($row_flota, $j);
                    if ($j == 0) {   //enlace a detalle
?>
                        <td class='centro'><a href='detalle_flota.php?id=<?php echo $row_flota[0]; ?>'><img src='imagenes/consulta.png' alt="<?php echo $detalle;?>"></a>
<?php
                    }
                    else {
?>
                        <td><?php echo $row_flota[$j]; ?></td>
<?php
                    }
                }
                //datos de la tabla Terminales
                // Tipos de termninales
                $tipos = array("F", "M%", "P%", "D");
                $nterm = array(0, 0, 0, 0);
                $sql_term = "SELECT * FROM terminales WHERE FLOTA='$row_flota[0]'";
                $res_term = mysql_db_query($base_datos, $sql_term) or die("Error en la consulta de Terminales" . mysql_error());
                $tot_term = mysql_num_rows($res_term);
                $tterm[0] += $tot_term;
?>
                <td class='centro'><?php echo $tot_term; ?></td>
<?php
                    for ($j = 0; $j < count($tipos); $j++) {
                        $sql_term = "SELECT * FROM terminales WHERE FLOTA='$row_flota[0]' AND TIPO LIKE '" . $tipos[$j] . "'";
                        $res_term = mysql_db_query($base_datos, $sql_term) or die("Error en la consulta de " . $cabecera[$j] . ": " . mysql_error());
                        $nterm[$j] = mysql_num_rows($res_term);
                        $tterm [$j + 1] += $nterm[$j];
?>
                        <td class='centro'><?php echo number_format($nterm[$j], 0, ',', '.'); ?></td>
<?php
                    }
?>
                </tr>
<?php
                } //primer for
?>
                <tr><td colspan='9'>&nbsp;</td></tr>
                <tr class="filapar">
                    <th colspan="3"><?php echo "$totales - $nflotas"; ?></th>
<?php
                    for ($j = 0; $j < count($tterm); $j++) {
?>
                        <td class='centro'><?php echo number_format($tterm[$j], 0, ',', '.'); ?></td>
<?php
                    }
?>
                </tr>
<?php
            }
?>
        </table>
<?php
    } // Si el usuario no es el de la Oficina
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
