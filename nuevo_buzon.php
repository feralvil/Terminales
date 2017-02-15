<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/buzonnew_$idioma.php";
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
                        var nombre = $('input#inputNom').val();
                        var acronimo = $('input#inputAcro').val();
                        if ((nombre == null) || (nombre === "")){
                            valido = false;
                            alert('<?php echo $errnomvac;?>');
                            $('input#inputNom').focus();
                        }
                        if ((acronimo == null) || (acronimo === "")){
                            valido = false;
                            alert('<?php echo $erracrovac;?>');
                            $('input#inputAcro').focus();
                        }
                        if (valido){
                            $('form#formbuzon').submit();
                        }
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
?>
        <h1><?php echo $h1; ?></h1>
        <h2><?php echo $h2buzon; ?></h2>
        <form id="formbuzon" name="formbuzon" method="POST" action="update_buzon.php">
            <input type="hidden" name="origen" value="nuevo">
            <table>
                <tr>
                    <th class="t30p"><?php echo $thacro; ?></th>
                    <th class="t2c"><?php echo $thnombre; ?></th>
                    <th class="t5c"><?php echo $thactivo; ?></th>
                </tr>
                <tr>
                    <td><input type="text" name="acronimo" id="inputAcro" value="" size="20"></td>
                    <td><input type="text" name="nombre" id="inputNom" value="" size="40"></td>
                    <td>
                        <select name="activo">
                            <option value="SI">Sí</option>
                            <option value="NO">No</option>
                        </select>
                    </td>
                </tr>
            </table>
            <table>
                <tr class="borde">
                    <td class="borde">
                        <a href='#' id='botguarda'>
                            <img src='imagenes/guardar.png' alt='<?php echo $botguardar; ?>' title="<?php echo $botguardar; ?>">
                        </a>
                        <br><?php echo $botguardar; ?>
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
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
