<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/gruposedi_$idioma.php";
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
        <script type="text/javascript" src="js/editar_grupo.js"></script>
    </head>
    <body>
    <?php
    if ($permiso == 2){
        $sql_grupo = "SELECT * FROM grupos WHERE GISSI = " . $gissi;
        $res_grupo = mysql_query($sql_grupo) or die("Error en la consulta de Grupo: " . mysql_error());
        $ngrupo = mysql_num_rows($res_grupo);

    ?>
        <h1><?php echo $titulo; ?></h1>
        <?php
        if ($ngrupo > 0) {
            $grupo = mysql_fetch_array($res_grupo);
            ############# Enlaces para la exportación #######
            $linkpdf = "document.exportar.action='pdfflota.php';document.exportar.submit();";
            $linkxls = "document.exportar.action='xlsflota.php';document.exportar.submit();";

            if (isset ($update)){
                if ($update == "KO"){
                    $clase = "flashko";
                    $imagen = "imagenes/cancelar.png";
                    $alt = "Error";
                }
                if ($update == "OK"){
                    $clase = "flashok";
                    $imagen = "imagenes/okm.png";
                    $alt = "OK";
                }
            ?>
                <p class="<?php echo $clase;?>">
                    <img src="<?php echo $imagen;?>" alt="<?php echo $alt;?>" title="<?php echo $alt;?>"> &mdash; <?php echo $mensflash;?>
                </p>
            <?php
            }
            ?>
            <form name="formedit" id="formedit" action="update_grupo.php" method="post">
                <input type="hidden" name="origen" value="editar">
                <input type="hidden" name="gissi" value="<?php echo $gissi;?>">
            <table>
                <tr>
                    <th>GSSI</th>
                    <th><?php echo $thmnemo;?></th>
                    <th><?php echo $thtipo;?></th>
                    <th><?php echo $thdesc;?></th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="gissihid" value="<?php echo $grupo['GISSI'];?>" disabled>
                    </td>
                    <td>
                        <input type="text" name="mnemonico" value="<?php echo $grupo['MNEMONICO'];?>">
                    </td>
                    <td>
                        <input type="text" name="tipo" value="<?php echo $grupo['TIPO'];?>">
                    </td>
                    <td>
                        <input type="text" name="descripcion" value="<?php echo $grupo['DESCRIPCION'];?>" size="60">
                    </td>
                </tr>
            </table>
        <?php
        }
        else {
        ?>
            <p class='error'><?php echo $errnogrupo; ?></p>
        <?php
        }
        ?>
        <table>
            <tr>
                <td class="borde">
                    <a href='#' id="botdetalle">
                        <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'></a>
                        <br><?php echo $botatras; ?>
                </td>
                <td class="borde">
                        <input type="image" src='imagenes/guardar.png' alt='<?php echo $botguarda; ?>' title='<?php echo $botguarda; ?>'>
                        <br><?php echo $botguarda; ?>
                </td>
                <td class="borde">
                    <a href='#' id="botcancel">
                        <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title='<?php echo $botcancel; ?>'></a>
                        <br><?php echo $botcancel; ?>
                </td>
            </tr>
        </table>
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
