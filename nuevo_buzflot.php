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
                        if ((flota == null) || (flota === "NN")){
                            valido = false;
                            alert('<?php echo $errflotavac;?>');
                            $('select#selFlota').focus();
                        }
                        else{
                            if ((rol == null) || (rol === "NN")){
                                valido = false;
                                alert('<?php echo $errrolvac;?>');
                                $('select#selRol').focus();
                            }
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
                <input type="hidden" name="origen" value="nuevo">
                <input type="hidden" name="buzon_id" id="idbuzon" value="<?php echo $buzon_id; ?>">
            <?php
                $sql_flotas = "SELECT flotas.ID, flotas.FLOTA FROM flotas ORDER BY flotas.FLOTA ASC";
                $res_flotas = mysql_query($sql_flotas) or die("Error en la consulta de flotas: " . mysql_error());
                $nflotas = mysql_num_rows($res_flotas);
                if ($nflotas > 0){
            ?>
                    <table>
                        <tr>
                            <th class="t40p"><?php echo $thflota; ?></th>
                            <th class="t5c"><?php echo $throl; ?></th>
                        </tr>
                        <tr>
                            <td>
                                <select name="flota_id" id="selFlota">
                                    <option value='NN'>Seleccionar flota</option>
                                    <?php
                                    for ($i = 0; $i < $nflotas; $i++){
                                        $flota = mysql_fetch_array($res_flotas);
                                    ?>
                                        <option value='<?php echo $flota['ID']; ?>'><?php echo $flota['FLOTA']; ?></option>
                                    <?php
                                    }
                                    ?>                                    
                                </select>
                                
                            </td>
                            <td>
                                <select name="rol" id="selRol">
                                    <option value='NN'>Seleccionar rol</option>
                                    <option value='P'><?php echo $rolprop; ?></option>
                                    <option value='A'><?php echo $rolasoc; ?></option>                                    
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
                                <img src='imagenes/nuevo.png' alt='<?php echo $botadd; ?>' title="<?php echo $botadd; ?>">
                            </a>
                            <br><?php echo $botadd; ?>
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
