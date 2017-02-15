<?php
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
?>
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
            <h2><?php echo $h2acceso; ?></h2>
<?php
        $sql_flotas = "SELECT ID, ACRONIMO, FLOTA, ENCRIPTACION FROM flotas, usuarios_flotas WHERE ";
        $sql_flotas .= "usuarios_flotas.NOMBRE='$usuario' AND flotas.ID = usuarios_flotas.ID_FLOTA";
        $sql_flotas .= " ORDER BY flotas.FLOTA ASC";
        $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
?>
            <select name="flota">
<?php
            for ($i = 0; $i < $nflotas; $i++) {
                $row_flotas = mysql_fetch_array($res_flotas);
                if ($row_flotas["ID"]!=$idflota) {
?>
                    <option value="<?php echo $row_flotas["ID"]; ?>"><?php echo $row_flotas["FLOTA"]; ?></option>
<?php
                }
            }
?>
            </select>
            <input type="hidden" name="origen" value="eliminar">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <input type="hidden" name="usuflota" value="<?php echo $row_flota["LOGIN"]; ?>">
            <input type="hidden" name="flota_org" value="<?php echo $row_flota["FLOTA"]; ?>">
            <input type="hidden" name="acro_org" value="<?php echo $row_flota["ACRONIMO"]; ?>">
            <table>
                <tr>
                    <td class="borde">
                        <input type='image' name='action' src='imagenes/desactiva.png' alt='<?php echo $botdel; ?>' title="<?php echo $botdel; ?>"><br><?php echo $botdel; ?>
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
