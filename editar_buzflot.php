<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/buzonflot_$idioma.php";
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
        else{
?>
            <script type="text/javascript" src="js/jquery.js"></script>
            <!-- Funciones JQUERY -->
            <script type="text/javascript">
                $(function(){
                    // Reseteamos el formulario
                    $('a#botreset').click(function(){
                       document.formbuzon.reset();
                    });
                    // Validamos el formulario y mandamos:
                    $('a#botguarda').click(function(){
                        var valido = true;
                        var flota = $('select#selFlota').val();
                        var rol = $('select#selRol').val();
                        if ((rol == null) || (rol === "NN")){
                            valido = false;
                            alert('<?php echo $errrolvac;?>');
                            $('select#selRol').focus();
                        }
                        if (valido){
                            $('form#formbuzon').submit();
                        }
                    });
                    // Cambiamos el formulario para ir a editar
                    $('a#botvolver').click(function(){
                        $('form#formbuzon').attr('action', 'detalle_buzon.php');
                        $('form#formbuzon').submit();
                    });
                });
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
            // Consultas de Buzones
            $sql_buzflot = "SELECT * FROM flotas_buzons WHERE ID = " . $buzflota_id;
            $res_buzflot = mysql_query($sql_buzflot) or die("Error en la consulta de la flota del buzón: " . mysql_error());
            $nbuzflot = mysql_num_rows($res_buzflot);
            if ($nbuzflot > 0){
                $buzflot = mysql_fetch_array($res_buzflot);
                $rango = explode("-", $buzflot['RANGO']);
            }
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
            <form id="formbuzon" method="POST" action="update_buzflot.php">
                <input type="hidden" name="origen" value="editar">
                <input type="hidden" name="buzon_id" id="idbuzon" value="<?php echo $buzon_id; ?>">
                <input type="hidden" name="flota_id" value="<?php echo $buzflot['FLOTA_ID']; ?>">
                <input type="hidden" name="buzflota_id" value="<?php echo $buzflota_id; ?>">
            <?php
                $sql_flota = "SELECT flotas.ID, flotas.FLOTA FROM flotas WHERE flotas.id = " . $buzflot['FLOTA_ID'];
                $res_flota = mysql_query($sql_flota) or die("Error en la consulta de flotas: " . mysql_error());
                $nflota = mysql_num_rows($res_flota);
                if ($nflota > 0){
                    $flota = mysql_fetch_array($res_flota);
            ?>
                    <table>
                        <tr>
                            <th class="t40p"><?php echo $thflota; ?></th>
                            <th class="t5c"><?php echo $throl; ?></th>
                        </tr>
                        <tr>
                            <td><?php echo $flota['FLOTA']; ?></td>
                            <td>
                                <select name="rol" id="selRol">
                                    <option value='NN'>Seleccionar rol</option>
                                    <option value='P' <?php if ($buzflot['ROL'] == 'P') {echo 'selected';} ?>><?php echo $rolprop; ?></option>
                                    <option value='A' <?php if ($buzflot['ROL'] == 'A') {echo 'selected';} ?>><?php echo $rolasoc; ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
            <?php
                }
                else{
            ?>
                    <p class='error'><?php echo $errnoflotas; ?></p>
            <?php
                }
            ?>
            
                <table>
                    <tr class="borde">
                        <td class="borde">
                            <a href='#' id='botguarda'>
                                <img src='imagenes/guardar.png' alt='<?php echo $botguarda; ?>' title="<?php echo $botguarda; ?>">
                            </a>
                            <br><?php echo $botguarda; ?>
                        </td>
                        <td class="borde">
                            <a href='#' id='botreset'>
                                <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title="<?php echo $botcancel; ?>">
                            </a>
                            <br><?php echo $botcancel; ?>
                        </td>
                        <td class="borde">
                            <a href='#' id='botvolver'>
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
