<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/gruposdet_$idioma.php";
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
        <script type="text/javascript" src="js/detalle_grupo.js"></script>
    </head>
    <body>
    <?php
    if ($permiso == 2){
        $sql_grupo = "SELECT * FROM grupos WHERE GISSI = " . $gissi;
        $res_grupo = mysql_query($sql_grupo) or die("Error en la consulta de Grupo: " . mysql_error());
        $ngrupo = mysql_num_rows($res_grupo);
        $sql_flotas = "SELECT flotas.ID, flotas.ACRONIMO, flotas.FLOTA, grupos_flotas.GISSI FROM flotas, grupos_flotas";
        $sql_flotas .= " WHERE (grupos_flotas.GISSI = " . $gissi . ") AND (grupos_flotas.FLOTA = flotas.ID)";
        $res_flotas = mysql_query($sql_flotas) or die("Error en la consulta de Flotas: " . mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
    ?>
        <h1><?php echo $titulo; ?></h1>
        <?php
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
        <h2><?php echo $h2grupo;?></h2>
        <?php
        if ($ngrupo > 0) {
            $grupo = mysql_fetch_array($res_grupo);
        ?>
            <table>
                <tr>
                    <th>GSSI</th>
                    <th><?php echo $thmnemo;?></th>
                    <th><?php echo $thtipo;?></th>
                    <th><?php echo $thdesc;?></th>
                </tr>
                <tr>
                    <td><?php echo $grupo['GISSI'];?></td>
                    <td><?php echo $grupo['MNEMONICO'];?></td>
                    <td><?php echo $grupo['TIPO'];?></td>
                    <td><?php echo $grupo['DESCRIPCION'];?></td>
                </tr>
            </table>
            <h2><?php echo $h2flota . " (" . $nflotas . ")";?></h2>
            <?php
            if ($nflotas > 0){
                $nfilas = ceil($nflotas / 2);
            ?>
                <form name="formflota" id="formflota" action="detalle_flota.php" method="post">
                    <input type="hidden" name="idflota" id="idflota" value="0">
                </form>
                <table>
                    <tr>
                        <th>Flota</th>
                        <th><?php echo $thacro; ?></th>
                        <th><?php echo $thiraflota; ?></th>
                        <th>&nbsp;</th>
                        <th>Flota</th>
                        <th><?php echo $thacro; ?></th>
                        <th><?php echo $thiraflota; ?></th>
                    </tr>
                    <?php
                    for ($i = 0; $i < $nfilas; $i++){
                        $flota = mysql_fetch_array($res_flotas);
                        $nflotas--;
                    ?>
                        <tr>
                            <td><?php echo $flota['FLOTA']; ?></td>
                            <td><?php echo $flota['ACRONIMO']; ?></td>
                            <td class="centro">
                                <a href="#" id="det-<?php echo $flota['ID'];?>">
                                    <img src="imagenes/ir.png" title="<?php echo $thiraflota;?>"></a>
                            </td>
                            <td>&nbsp;</td>
                            <?php
                            if ($nflotas > 0){
                                $flota = mysql_fetch_array($res_flotas);
                                $nflotas--;
                            ?>
                                <td><?php echo $flota['FLOTA']; ?></td>
                                <td><?php echo $flota['ACRONIMO']; ?></td>
                                <td class="centro">
                                    <a href="#" id="det-<?php echo $flota['ID'];?>">
                                        <img src="imagenes/ir.png" title="<?php echo $thiraflota;?>"></a>
                                </td>
                            <?php
                            }
                            else{
                            ?>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            <?php
                            }
                            ?>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class='error'><?php echo $errnoflota; ?></p>
        <?php
            }
        }
        else {
        ?>
            <p class='error'><?php echo $errnogrupo; ?></p>
        <?php
        }
        ?>
        <form name="formgrupo" id="formgrupo" action="detalle_grupo.php" method="post">
            <input type="hidden" id="gissi" name="gissi" value="<?php echo $gissi; ?>">
        </form>
        <table>
            <tr>
                <td class="borde">
                    <a href='grupos.php'>
                        <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'></a>
                        <br><?php echo $botatras; ?>
                </td>
                <td class="borde">
                    <a href='#' id="botedit">
                        <img src='imagenes/pencil.png' alt='<?php echo $botedi; ?>' title='<?php echo $botedi; ?>'></a>
                        <br><?php echo $botedi; ?>
                </td>
                <td class="borde">
                    <a href='#' id="botdel">
                        <img src='imagenes/no.png' alt='<?php echo $botdel; ?>' title='<?php echo $botdel; ?>'></a>
                        <br><?php echo $botdel; ?>
                </td>
            </tr>
        </table>

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
