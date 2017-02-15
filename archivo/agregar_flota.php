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

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotaperm_$idioma.php";
include ($lang);
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
?>
<html>
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
        // Si la sesión de Joomla ha caducado, recargamos la página
        if ($usu == ""){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
    </head>
    <body>
<?php
if ($permiso != 0) {
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
?>
        <p class='error'>No hay resultados en la consulta de la Flota</p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $usuario = $row_flota["LOGIN"];
    }
?>
        <h1>Permisos de la Flota <?php echo $row_flota["FLOTA"]; ?></h1>
        <h2><?php echo $h2admin; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t5c"><?php echo $acroflota; ?></th>
                <th class="t5c"><?php echo $usuflota; ?></th>
                <th class="t10c"><?php echo $activa; ?></th>
                <th class="t10c"><?php echo $encripta; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_flota["LOGIN"]; ?></td>
                <td><?php echo $row_flota["ACTIVO"]; ?></td>
                <td><?php echo $row_flota["ENCRIPTACION"]; ?></td>
            </tr>
        </table>
        <form action="update_usuflota.php" name="formulario" method="POST">
            <h2><?php echo $h2add; ?></h2>
<?php
        $sql_flotas = "SELECT * FROM flotas ORDER BY flotas.FLOTA ASC";
        $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
?>
            <select name="flota">
<?php
            for ($i = 0; $i < $nflotas; $i++) {
                $row_flotas = mysql_fetch_array($res_flotas);
                $id_perm = $row_flotas["ID"];
                $sql_perm = "SELECT * FROM usuarios_flotas WHERE NOMBRE ='$usuario' AND ID_FLOTA='$id_perm'";
                $res_perm = mysql_query($sql_perm) or die(mysql_error());
                $nperm = mysql_num_rows($res_perm);
                if (($nperm == 0) && ($id_perm != 100)) {
?>
                    <option value="<?php echo $row_flotas["ID"]; ?>"><?php echo $row_flotas["FLOTA"]; ?></option>
<?php
                }
            }
?>
            </select>
            <input type="hidden" name="origen" value="agregar">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <input type="hidden" name="usuflota" value="<?php echo $row_flota["LOGIN"]; ?>">
            <input type="hidden" name="flota_org" value="<?php echo $row_flota["FLOTA"]; ?>">
            <input type="hidden" name="acro_org" value="<?php echo $row_flota["ACRONIMO"]; ?>">
            <table>
                <tr>
                    <td class="borde">
                        <input type='image' name='action' src='imagenes/activa.png' alt='<?php echo $botagr; ?>' title="<?php echo $botagr; ?>"><br><?php echo $botagr; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.detflota.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'>
                        </a><br><?php echo $botatras; ?>
                        </td>
                </tr>
            </table>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
<?php
} // Si el usuario no es el de la Oficina
else {
?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
}
?>
    </body>
</html>
