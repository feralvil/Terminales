<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/gruposflonew_$idioma.php";
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
else {
    if ($idflota > 0){
        if ($flota_usu == $idflota) {
            $permiso = 1;
        }
    }
    else{
        $permiso = 1;
        $idflota = $flota_usu;
    }
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
    </head>
    <body>
    <?php
    if (isset($update)) {
        if ($update == "KO") {
            $clase = "flashko";
            $imagen = "imagenes/cancelar.png";
            $alt = "Error";
    }
    if ($update == "OK") {
        $clase = "flashok";
        $imagen = "imagenes/okm.png";
        $alt = "OK";
    }
    ?>
        <p class="<?php echo $clase; ?>">
            <img src="<?php echo $imagen; ?>" alt="<?php echo $alt; ?>" title="<?php echo $alt; ?>"> &mdash; <?php echo $mensflash; ?>
        </p>
    <?php
    }
    if ($permiso > 0){
        //datos de la tabla Flotas
        $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
    ?>
        <form name="formtab" action="grupos_flonew.php" method="POST" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
            <h1>
                <?php echo $h1;?> &mdash;
                <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $bottab;?>' title="<?php echo $bottab;?>">
            </h1>
        </form>
        <form name="modgrupo" action="#" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
        </form>
        <form name="formdet" action="grupos_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
        </form>
        <h2><?php echo $h2flota;?></h2>
    <?php
        if ($nflota > 0){
            $row_flota = mysql_fetch_array($res_flota);
            $sql_grupos = "SELECT * FROM grupos ORDER BY grupos.GISSI";
            $res_grupos = mysql_query($sql_grupos) or die("Error en la consulta de Grupos: " . mysql_error());
            $ngrupos = mysql_num_rows($res_grupos);
    ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th><?php echo $thflota;?></th>
                    <th><?php echo $thacro;?></th>
                    <th><?php echo $thlogin;?></th>
                </tr>
                <tr>
                    <td><?php echo $row_flota['ID'];?></td>
                    <td><?php echo $row_flota['FLOTA'];?></td>
                    <td><?php echo $row_flota['ACRONIMO'];?></td>
                    <td><?php echo $row_flota['LOGIN'];?></td>
                </tr>
            </table>
            <form name="detflota" action="grupos_flota.php" method="POST">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
            </form>
            <h2><?php echo $h2grupos;?></h2>
            <?php
            if ($ngrupos > 0){
            ?>
            <form name="formsel" id="formgrupos" action="grupos_flonew.php" method="post">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                <label for="gissisel">GSSI:</label>
                <select name="gissisel" id="gissisel" onchange="document.formsel.submit();">
                    <option value="NN">Seleccionar</option>
                    <?php
                    for ($i = 0; $i < $ngrupos; $i++){
                        $row_grupo = mysql_fetch_array($res_grupos);
                    ?>
                        <option value="<?php echo $row_grupo['GISSI']; ?>" <?php if($gissisel == $row_grupo['GISSI']) {echo "selected";} ?>>
                            <?php echo $row_grupo['GISSI'];?> - <?php echo $row_grupo['MNEMONICO'];?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </form>
            <h2><?php echo $h2carpeta;?></h2>
            <?php
            if (($gissisel > 0) && ($gissisel != "")){
                $sql_gissi = "SELECT * FROM grupos_flotas WHERE (FLOTA = " . $idflota . ")";
                $sql_gissi .= " AND (GISSI = " . $gissisel . ")";
                $res_gissi = mysql_query($sql_gissi) or die("Error en la consulta de GSSI: " . mysql_error());
                $ngissi = mysql_num_rows($res_gissi);
                if ($ngissi > 0){
                    $row_gissi = mysql_fetch_array($res_gissi);
            ?>
                    <p class="error">
                        <b><?php echo sprintf($errgissirep, $ngissi);?></b>
                    </p>
            <?php
                }
                    $sql_carpetas = "SELECT DISTINCT CARPETA, NOMBRE FROM grupos_flotas WHERE (FLOTA = " . $idflota . ")";
                    $res_carpetas = mysql_query($sql_carpetas) or die("Error en la consulta de Carpetas: " . mysql_error());
                    $ncarpetas = mysql_num_rows($res_carpetas);
            ?>
                <form name="formgissi" id="formgissi" action="update_grupo.php" method="post">
                    <input name="origen" type="hidden" value="addflota">
                    <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                    <input name="gissi" type="hidden" value="<?php echo $gissisel; ?>">
                    <table>
                        <tr>
                            <td>
                                <label for="gissihid">GSSI:</label>
                                <input type="text" name="gissihid" id="gissihid" value="<?php echo $gissisel; ?>" disabled>
                            </td>
                            <?php
                            if ($ncarpetas > 0){
                                $maxcarp = 0;
                            ?>
                                <td>
                                    <label for="carpexist"><?php echo $txtcarpexi; ?>:</label>
                                    <select id="carpexist" name="carpexist">
                                        <option value="NN">Seleccionar</option>
                                        <?php
                                        for ($i = 0; $i < $ncarpetas; $i++){
                                            $carpeta = mysql_fetch_array($res_carpetas);
                                            if ($carpeta['CARPETA'] > $maxcarp){
                                                $maxcarp = $carpeta['CARPETA'];
                                            }
                                        ?>
                                            <option value="<?php echo $carpeta['CARPETA'] . ";" . $carpeta['NOMBRE']; ?>">
                                                <?php echo $carpeta['CARPETA'];?> - <?php echo $carpeta['NOMBRE'];?>
                                            </option>
                                        <?php
                                        }
                                        $maxcarp++;
                                        ?>
                                    </select>
                                </td>
                            <?php
                            }
                            ?>
                            <td>
                                <input type="hidden" name="carpnew" id="carpnew" value="<?php echo $maxcarp; ?>">
                                <label for="carphid"><?php echo $txtcarpnew; ?>:</label>
                                <input type="text" name="carphid" id="carphid" value="<?php echo $maxcarp; ?>" size="2" disabled> &mdash;
                                <input type="text" name="nomnew" id="nomnew" value="" size="20">
                            </td>
                        </tr>
                    </table>
                </form>
            <?php
            }
            ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href='#' onclick="document.formdet.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'>
                        </a><br><?php echo $botatras; ?>
                    </td>
                    <?php
                    if ($permiso > 1){
                    ?>
                        <td class="borde">
                            <a href='#' onclick="document.formgissi.submit();">
                                <img src='imagenes/guardar.png' alt='<?php echo $botguardar; ?>' title='<?php echo $botguardar; ?>'>
                            </a><br><?php echo $botguardar; ?>
                        </td>
                        <td class="borde">
                            <a href='#' onclick="document.formgissi.reset();">
                                <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title='<?php echo $botcancel; ?>'>
                            </a><br><?php echo $botcancel; ?>
                        </td>
                        <td class="borde">
                            <a href='nuevo_grupo.php'>
                                <img src='imagenes/addgrupo.png' alt='<?php echo $botcrear; ?>' title='<?php echo $botcrear; ?>'>
                            </a><br><?php echo $botcrear; ?>
                        </td>
                    <?php
                    }
                    ?>
                </tr>
            </table>
            <?php
            }
            else{
            ?>
                <p class="error">Error: <b><?php echo $errnogrupos;?></b></p>
        <?php
            }
        }
        else{
    ?>
            <p class="error"><?php echo $errnoflota;?></p>
    <?php
        }
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
