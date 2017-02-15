<?php
// Revisado 2011-07-12
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
$lang = "idioma/flotateik_$idioma.php";
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
if ($id == "") {
    $id = $flota_usu;
}
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
    if ($permiso != 0) {
        //datos de la tabla Flotas
        $sql_flota = "SELECT * FROM flotas WHERE ID='$id'";
        $res_flota = mysql_db_query($base_datos, $sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota == 0) {
            echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
        }
        else {
            $row_flota = mysql_fetch_array($res_flota);
            $usuario = $row_flota["LOGIN"];
        }
?>
        <h1><?php echo $h1; ?> <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <h2><?php echo $h2admin; ?></h2>
        <table>
            <tr>
                <th class="t10c">ID</th>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t5c"><?php echo $acroflota; ?></th>
                <th class="t5c"><?php echo $usuflota; ?></th>
                <th class="t10c"><?php echo $activa; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["ID"]; ?></td>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_flota["LOGIN"]; ?></td>
                <td><?php echo $row_flota["ACTIVO"]; ?></td>
            </tr>
        </table>
        <form name="updteik" action="update_archexcel.php" method="POST" enctype="multipart/form-data">
            <input name="idflota" type="hidden" value="<?php echo $id;?>">
        <h2><?php echo $h2cargar; ?></h2>
            <p>
                <label for="archivo">Seleccionar:</label>
                <input type="file" name="archivo" id="archivo" />
            </p>
        <table>
            <tr>
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/guardar.png' alt='Guardar' title="Guardar"><br>Guardar <?php echo "$tot_term $terminales";?>
                </td>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
                <input name="id" type="hidden" value="<?php echo $id;?>">
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/atras.png' alt='<?php echo $detalle;?>' title="<?php echo $detalle;?>"><br><?php echo $detalle." de Flota";?>
                </td>
        </form>
            </tr>
        </table>
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