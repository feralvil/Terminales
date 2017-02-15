<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/gruposdel_$idioma.php";
include ($lang);

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
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //

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
<html lang="es">
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu = 0){
        ?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
        <?php
        }
        ?>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/eliminar_grupo.js"></script>
    </head>
    <body>
    <?php
    if ($permiso == 2){
        $sql_grupo = "SELECT * FROM grupos WHERE GISSI = " . $gissi;
        $res_grupo = mysql_query($sql_grupo) or die("Error en la consulta de Grupo: " . mysql_error());
        $ngrupo = mysql_num_rows($res_grupo);

    ?>
        <h1><?php echo $h1; ?></h1>
        <?php
        if ($ngrupo > 0) {
            $grupo = mysql_fetch_array($res_grupo);
        ?>
            <form name="formedit" id="formedit" action="update_grupo.php" method="post">
                <input type="hidden" name="origen" value="eliminar">
                <input type="hidden" name="gissi" value="<?php echo $gissi;?>">
                <div class="centro">
                    <p><img src='imagenes/important.png' alt='Error'></p>
                    <p class="error"><?php echo $mensdel . " " . $grupo['GISSI'] . ' (' . $grupo['MNEMONICO'] . ' )'; ?></p>
                    <table>
                        <tr>
                            <td class="borde">
                                <input type="image" src='imagenes/adelante.png' alt='<?php echo $botguarda; ?>' title='<?php echo $botguarda; ?>'>
                                <br><?php echo $botguarda; ?>
                            </td>
                            <td class="borde">
                                <a href='#' id="botcancel">
                                    <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title='<?php echo $botcancel; ?>'></a>
                                    <br><?php echo $botcancel; ?>
                            </td>
                        </tr>
                    </table>
                </div>
        <?php
        }
        else {
        ?>
            <p class='error'><?php echo $errnogrupo; ?></p>
            <table>
                <tr>
                    <td class="borde">
                        <a href='#' id="botcancel">
                            <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title='<?php echo $botcancel; ?>'></a>
                            <br><?php echo $botcancel; ?>
                    </td>
                </tr>
            </table>
        <?php
        }
        ?>

        </form>
        <form name="formdetalle" id="formdetalle" action="detalle_grupo.php" method="post">
            <input type="hidden" id="gissi" name="gissi" value="<?php echo $gissi; ?>">
        </form>
    <?php
    }
    else{
    ?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $errnoperm; ?></p>
    <?php
    }
    ?>
    </body>
</html>
