<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/permisosadd_$idioma.php";
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
        <script type="text/javascript">
            function checkAll() {
                var nodoCheck = document.getElementsByTagName("input");
                var varCheck = document.getElementById("seltodo").checked;
                for (i = 0; i < nodoCheck.length; i++) {
                    if (nodoCheck[i].type == "checkbox" && nodoCheck[i].name != "seltodo" && nodoCheck[i].disabled == false) {
                        nodoCheck[i].checked = varCheck;
                    }
                }
            }
        </script>
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
    if ($permiso > 1){
        //datos de la tabla Flotas
        $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
    ?>
        <form name="formtab" action="permisos_floadd.php" method="POST" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
            <h1>
                <?php echo $h1;?> &mdash;
                <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $bottab;?>' title="<?php echo $bottab;?>">
            </h1>
        </form>
        <form name="formdet" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
        </form>
        <h2><?php echo $h2flota;?></h2>
    <?php
        if ($nflota > 0){
            $row_flota = mysql_fetch_array($res_flota);
            $sql_grupos = "SELECT grupos_flotas.*, grupos.MNEMONICO FROM grupos_flotas, grupos";
            $sql_grupos .= " WHERE (grupos_flotas.GISSI = grupos.GISSI) AND (grupos_flotas.FLOTA = " . $idflota . ")";
            $sql_grupos .= " ORDER BY grupos_flotas.CARPETA, grupos_flotas.GISSI";
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
            <form name="formperm" action="permisos_flota.php" method="POST">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
            </form>
            <h2><?php echo $h2grupo;?></h2>
            <?php
            if ($ngrupos > 0){
            ?>
                <form name="formadd" action="permisos_floadd.php" method="POST">
                    <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                    <label for="gssisel">Seleccionar GISSI:</label>
                    <select name="gssisel" id="gssisel" onchange="document.formadd.submit();">
                        <option value="NN">Seleccionar</option>
                        <?php
                        for ($i = 0; $i < $ngrupos; $i++){
                            $grupo_sel = mysql_fetch_array($res_grupos);
                        ?>
                            <option value="<?php echo $grupo_sel['GISSI']; ?>" <?php if ($grupo_sel['GISSI'] == $gssisel) {echo "selected";} ?>>
                                <?php echo $grupo_sel['GISSI'] . ' - ' . $grupo_sel['MNEMONICO']; ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                </form>
                <h2><?php echo $h2perm;?></h2>
                <?php
                $sql_carpterm = "SELECT DISTINCT CARPTERM FROM permisos_flotas WHERE (FLOTA = " . $idflota . ")";
                $sql_carpterm .= " ORDER BY CARPTERM";
                $res_carpterm = mysql_query($sql_carpterm) or die("Error en la consulta de Carpetas: " . mysql_error());
                $ncarpterm = mysql_num_rows($res_carpterm);
                if ($ncarpterm > 0){
                    $carpetas = array();
                    for ($i = 0; $i < $ncarpterm; $i++){
                        $row_carpterm = mysql_fetch_array($res_carpterm);
                        $carpetas[] = $row_carpterm['CARPTERM'];
                    }
                    if (($gssisel > 0) && ($gssisel != "NN")){
                ?>
                        <form name="formupdate" action="update_permiso.php" method="post">
                            <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                            <input name="gissi" type="hidden" value="<?php echo $gssisel; ?>">
                            <input name="origen" type="hidden" value="permadd">
                        <table>
                            <tr>
                                <td>&nbsp;</td>
                                <th colspan="<?php echo ($ncarpterm + 1);?>">
                                    <?php echo $thorg;?> &mdash; <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" />
                                </th>
                            </tr>
                            <tr>
                                <th>GSSI</th>
                                <?php
                                foreach ($carpetas as $carpeta) {
                                ?>
                                    <th><?php echo $carpeta;?></th>
                                <?php
                                }
                                ?>
                                <th><?php echo $thnewcarp;?></th>
                            </tr>
                            <tr>
                                <td class="centro"><?php echo $gssisel;?></td>
                                <?php
                                foreach ($carpetas as $carpeta) {
                                    $sql_perm = "SELECT * FROM permisos_flotas WHERE (FLOTA = " . $idflota . ")";
                                    $sql_perm .= " AND (GISSI = " . $gssisel . ") AND (CARPTERM = '" . $carpeta . "')";
                                    $res_perm = mysql_query($sql_perm) or die("Error en la consulta de Permisos: " . mysql_error());
                                    $nperm = mysql_num_rows($res_perm);
                                    if ($nperm > 0){
                                        $tdcont = 'X';
                                    }
                                    else{
                                        $tdcont = "<input type='checkbox' name='carpterm[]' value='" . $carpeta . "'>";
                                    }
                                ?>
                                    <td class="centro"><?php echo $tdcont;?></td>
                                <?php
                                }
                                ?>
                                <td class="centro"><input type="text" name="newcarp" size="20" /></td>
                            </tr>
                        </table>
                        </form>
                <?php
                    }
                }
                else{
                ?>
                    <p class="error">Error: <b><?php echo $errnoperm;?></b></p>
            <?php
                }
            }
            else{
            ?>
                <p class="error">Error: <b><?php echo $errnogrupos;?></b></p>
        <?php
            }
        ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href='#' onclick="document.formperm.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'>
                        </a><br><?php echo $botatras; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.formupdate.submit();">
                            <img src='imagenes/guardar.png' alt='<?php echo $botguardar; ?>' title='<?php echo $botguardar; ?>'>
                        </a><br><?php echo $botguardar; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.formupdate.reset();">
                            <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title='<?php echo $botcancel; ?>'>
                        </a><br><?php echo $botcancel; ?>
                    </td>
                </tr>
            </table>
        <?php
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
