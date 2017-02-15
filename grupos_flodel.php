<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/gruposflodel_$idioma.php";
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
        <form name="formtab" action="grupos_flodel.php" method="POST" target="_blank">
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
            // Aquí:
            $sql_grupos = "SELECT grupos_flotas.GISSI, grupos.MNEMONICO FROM grupos_flotas, grupos";
            $sql_grupos .= " WHERE (FLOTA = " . $idflota . ") AND (grupos.GISSI = grupos_flotas.GISSI) ORDER BY grupos_flotas.GISSI";
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
            <form name="formsel" id="formgrupos" action="grupos_flodel.php" method="post">
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
            if (($gissisel > 0) && ($gissisel != "NN")){
                $sql_gissi = "SELECT * FROM grupos_flotas WHERE (FLOTA = " . $idflota . ")";
                $sql_gissi .= " AND (GISSI = " . $gissisel . ") ORDER BY CARPETA ASC";
                $res_gissi = mysql_query($sql_gissi) or die("Error en la consulta de GISSI: " . mysql_error());
                $ngissi = mysql_num_rows($res_gissi);
            ?>
                <form name="formgissi" id="formgissi" action="update_grupo.php" method="post">
                    <input name="origen" type="hidden" value="delflota">
                    <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                    <input name="gissi" type="hidden" value="<?php echo $gissisel; ?>">
                    <table>
                        <tr>
                            <td>
                                <label for="gissihid">GSSI:</label>
                                <input type="text" name="gissihid" id="gissihid" value="<?php echo $gissisel; ?>" disabled>
                            </td>
                            <?php
                            if ($ngissi > 0){
                            ?>
                                <td>
                                    <label for="idgrupflo">Carpeta:</label>
                                    <select id="idgrupflo" name="idgrupflo">
                                        <option value="NN">Seleccionar</option>
                                        <?php
                                        for ($i = 0; $i < $ngissi; $i++){
                                            $row_gissi = mysql_fetch_array($res_gissi);
                                        ?>
                                            <option value="<?php echo $row_gissi['ID']; ?>">
                                                <?php echo $row_gissi['CARPETA'];?> - <?php echo $row_gissi['NOMBRE'];?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            <?php
                            }
                            ?>
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
                        if ($gissisel > 0){
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
                    <?php
                        }
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
