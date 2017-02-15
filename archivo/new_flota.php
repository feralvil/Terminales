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
$lang = "idioma/flotanew_$idioma.php";
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
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
<?php
if ($permiso == 2) {
    //datos de la tabla Flotas
    $sql_flotas = "SELECT MAX(ID) FROM flotas";
    $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de Flota: " . mysql_error());
    $nflotas = mysql_num_rows($res_flotas);
    if ($nflotas == 0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flotas);
        $id_flota = $row_flota[0] + 1;
    }
?>
        <h1><?php echo $h1; ?></h1>
        <form name="formflota" action="update_flota.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="idflota" value="<?php echo $id_flota; ?>">
            <input type="hidden" name="origen" value="nueva">
            <h2><?php echo $h2admin; ?></h2>
            <table>
                <tr>
                    <th class="t40p"><?php echo $nomflota; ?></th>
                    <th class="t5c"><?php echo $acroflota; ?></th>
                    <th class="t5c"><?php echo $usuflota; ?></th>
                    <th class="t5c"><?php echo $passflota; ?></th>
                </tr>
                <tr>
                    <td><input type="text" name="flota" value="" size="40"></td>
                    <td><input type="text" name="acronimo" value="" size="10"></td>
                    <td><input type="text" name="usuario" value="" size="10"></td>
                    <td><input type="password" name="password" value="" size="10"></td>
                </tr>
            </table>
            <h2><?php echo $h2otros; ?></h2>
            <table>
                <tr>
                    <th class="t40p"><?php echo $ciudad; ?></th>
                    <th class="t5c"><?php echo $cp; ?></th>
                    <th class="t5c"><?php echo $activa; ?></th>
                    <th class="t5c"><?php echo $encripta; ?></th>
                </tr>
                <tr>
                    <td>
                        <select name="ine">
<?php
                            $sql_mun = "SELECT * FROM municipios ORDER BY MUNICIPIO ASC";
                            $res_mun = mysql_db_query($base_datos, $sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
                            $nmun = mysql_num_rows($res_mun);
                            if ($nmun == 0) {
                                echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
                            } else {
                                for ($i = 0; $i < $nmun; $i++) {
                                    $row_mun = mysql_fetch_array($res_mun);
                                    $ine_mun = $row_mun["INE"];
                                    $nom_mun = $row_mun["MUNICIPIO"];
?>
                                    <option value="<?php echo $ine_mun; ?>"><?php echo $nom_mun; ?></option>
<?php
                                }
                            }
?>
                        </select>
                    </td>
                    <td><input type="text" name="cp" value="" size="10"></td>
                    <td>
                        <select name="activa">
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </td>
                    <td>
                        <select name="encriptacion">
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </td>
                </tr>
            </table>
            <h2><?php echo $h2arch; ?></h2>
            <p>
                <label for="archivo">Seleccionar:</label>
                <input type="file" name="archivo" id="archivo" />
            </p>
            
            <table>
                <tr>
                    <td class="borde"><input type='image' name='nueva' src='imagenes/guardar.png' alt='Guardar' title="Guardar"><br>Guardar Flota</td>
                    <td class="borde"><a href='flotas.php'><img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>"></a><br><?php echo $volver; ?></td>
                    <td class="borde"><a href='#' onclick='document.formflota.reset();'><img src='imagenes/no.png' alt='<?php echo $cancel; ?>' title="<?php echo $cancel; ?>"></a><br><?php echo $cancel; ?></td>
                </tr>
            </table>
        </form>
<?php
}
 else {
?>
    <h1><?php echo $h1perm ?></h1>
    <p class='error'><?php echo $permno ?></p>
<?php
 }
 ?>
    </body>
</html>