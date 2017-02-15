<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/buzondet_$idioma.php";
include ($lang);

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
?>
<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
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
    if ($permiso == 2){
        // Consultas de Buzones
        $sql_buzon = "SELECT * FROM buzons WHERE ID = " . $buzon_id;
        $res_buzon = mysql_query($sql_buzon) or die("Error en la consulta del buzón: " . mysql_error());
        $nbuzon = mysql_num_rows($res_buzon);
        if ($nbuzon > 0){
            $buzon = mysql_fetch_array($res_buzon);
?>
            <h1><?php echo $h1; ?></h1>
            <h2><?php echo $h2buzon; ?></h2>
            <table>
                <tr>
                    <th class="t30p"><?php echo $thacro; ?></th>
                    <th class="t2c"><?php echo $thnombre; ?></th>
                    <th class="t5c"><?php echo $thactivo; ?></th>
                </tr>
                <tr>
                    <td><?php echo $buzon['ACRONIMO']; ?></td>
                    <td><?php echo $buzon['NOMBRE']; ?></td>
                    <td><?php echo $buzon['ACTIVO']; ?></td>
                </tr>
            </table>
            <h2><?php echo $h2flotas; ?></h2>
            <?php
                $sql_buzflot = "SELECT flotas.FLOTA, flotas_buzons.ROL, flotas_buzons.ID, flotas_buzons.RANGO FROM flotas, flotas_buzons";
                $sql_buzflot .= " WHERE (flotas_buzons.ID = " . $buzon_id . ") AND (flotas.ID = flotas_buzons.flota_id)";
                $res_buzflot = mysql_query($sql_buzflot) or die("Error en la consulta del buzón: " . mysql_error());
                $nbuzflot = mysql_num_rows($res_buzflot);
                if ($nbuzflot > 0){
            ?>
                    <table>
                        <tr>
                            <th class="t5c"><?php echo $thacciones; ?></th>
                            <th class="t30p"><?php echo $thflota; ?></th>
                            <th class="t5c"><?php echo $throl; ?></th>
                            <th class="t30p"><?php echo $thrango; ?></th>
                        </tr>
                        <?php
                        for ($i = 0; $i < $nbuzflot; $i++){
                            $buzflot = mysql_fetch_array($res_buzflot);
                        ?>
                            <tr>
                                <td><?php echo $buzflot['ID']; ?></td>
                                <td><?php echo $buzflot['FLOTA']; ?></td>
                                <td><?php echo $buzflot['ROL']; ?></td>
                                <td><?php echo $buzflot['RANGO']; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
            <?php
                }
                else{
            ?>
                    <p class='error'><?php echo $errnoflotas; ?></p>
            <?php
                }
            ?>
            <form id="formbuzon" method="POST" action="nuevo_buzflot.php">
                <input type="hidden" name="buzon_id" id="idbuzon" value="<?php echo $buzon_id; ?>">
                <table>
                    <tr class="borde">
                        <td class="borde">
                            <input type='image' name='action' src='imagenes/nuevo.png' alt='<?php echo $botadd; ?>' title="<?php echo $botadd; ?>">
                            <br><?php echo $botadd; ?>
                        </td>
                        <td class="borde">
                            <a href='#' id='botreset'>
                                <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title="<?php echo $botcancel; ?>">
                            </a>
                            <br><?php echo $botcancel; ?>
                        </td>
                        <td class="borde">
                            <a href='buzones.php'>
                                <img src='imagenes/atras.png' alt='<?php echo $botvolver; ?>' title="<?php echo $botvolver; ?>">
                            </a>
                            <br><?php echo $botvolver; ?>
                        </td>
                    </tr>
                </table>
            </form>
            
<?php
        }
        else{
?>
            <h1><?php echo $h1; ?></h1>
            <p class='error'><?php echo $errnobuzon; ?></p>
<?php
        }
    }
    else{
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
