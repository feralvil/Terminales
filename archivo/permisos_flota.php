<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/permisosflo_$idioma.php";
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
        <form name="formtab" action="permisos_flota.php" method="POST" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
            <h1>
                <?php echo $h1;?> &mdash;
                <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $bottab;?>' title="<?php echo $bottab;?>">
            </h1>
        </form>
        <form name="modgrupo" action="#" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
            <input type="hidden" name="gssisel" id="gssisel" value="0">
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
            <form name="detflota" action="detalle_flota.php" method="POST">
                <input name="origen" type="hidden" value="leerexcel">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
            </form>
            <form name="excelflota" action="excel_flota.php" method="POST">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                <input name="accion" type="hidden" value="IMPPERMISOS">
            </form>
            <h2><?php echo $h2perm;?></h2>
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
                            <a href='#' onclick="document.modgrupo.action='permisos_floadd.php';document.modgrupo.submit();">
                                <img src='imagenes/nuevo.png' alt='<?php echo $botadd; ?>' title='<?php echo $botadd; ?>'>
                            </a><br><?php echo $botadd; ?>
                        </td>
                        <td class="borde">
                            <a href='#' onclick="document.modgrupo.action='permisos_flodel.php';document.modgrupo.submit();">
                                <img src='imagenes/no.png' alt='<?php echo $botdel; ?>' title='<?php echo $botdel; ?>'>
                            </a><br><?php echo $botdel; ?>
                        </td>
                        <td class="borde">
                            <a href='#' onclick="document.excelflota.submit();">
                                <img src='imagenes/impexcel.png' alt='<?php echo $botexcel; ?>' title='<?php echo $botexcel; ?>'>
                            </a><br><?php echo $botexcel; ?>
                        </td>
                    <?php
                    }
                    ?>
                </tr>
            </table>
            <?php
            if ($ngrupos > 0){
                $sql_carpterm = "SELECT DISTINCT CARPTERM FROM permisos_flotas WHERE (FLOTA = " . $idflota . ")";
                $res_carpterm = mysql_query($sql_carpterm) or die("Error en la consulta de Carpetas: " . mysql_error());
                $ncarpterm = mysql_num_rows($res_carpterm);
                if ($ncarpterm > 0){
                    $carpetas = array();
                    for ($i = 0; $i < $ncarpterm; $i++){
                        $carpterm = mysql_fetch_array($res_carpterm);
                        $carpetas[] = $carpterm['CARPTERM'];
                    }
            ?>
                    <!-- Generar contadores e imprimir grupos -->
                    <table>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <th colspan="<?php echo $ncarpterm;?>"><?php echo $thorg;?></th>
                        </tr>
                        <tr>
                            <th>GSSI</th>
                            <th><?php echo $thmnemo;?></th>
                            <?php
                            foreach ($carpetas as $carpeta) {
                            ?>
                                <th><?php echo $carpeta;?></th>
                            <?php
                            }
                            ?>
                        </tr>
                        <?php
                        for ($i = 0; $i < $ngrupos; $i++){
                            $grupo = mysql_fetch_array($res_grupos);
                            $gssi = $grupo['GISSI'];
                            $linkgssi = "document.getElementById('gssisel').value='" . $gssi . "';";
                            $linksubmit = "document.modgrupo.submit();";
                            $linkadd = $linkgssi . "document.modgrupo.action='permisos_floadd.php';" . $linksubmit;
                            $linkdel = $linkgssi . "document.modgrupo.action='permisos_flodel.php';" . $linksubmit;
                        ?>
                            <tr <?php if (($i % 2) == 1) {echo "class = 'filapar'";} ?>>
                                <td class="centro">
                                    <?php echo  $grupo['GISSI'];?> &mdash;
                                    <a href='#' onclick="<?php echo $linkadd;?>">
                                        <img src='imagenes/nueva.png' alt='<?php echo $botadd; ?>' title='<?php echo $botadd; ?>'></a>
                                    -
                                    <a href='#' onclick="<?php echo $linkdel;?>">
                                        <img src='imagenes/nom.png' alt='<?php echo $botdel; ?>' title='<?php echo $botdel; ?>'>
                                    </a>
                                </td>
                                <td class="centro"><?php echo  $grupo['MNEMONICO'];?></td>
                                <?php
                                foreach ($carpetas as $carpeta) {
                                    $permiso = "&nbsp;";
                                    $sql_permiso = "SELECT * FROM permisos_flotas WHERE (FLOTA = " . $idflota .")";
                                    $sql_permiso .= " AND (GISSI = " . $grupo['GISSI'] .") AND (CARPTERM = '" . $carpeta . "')";
                                    $res_permiso = mysql_query($sql_permiso) or die("Error en la consulta de Carpetas: " . mysql_error());
                                    $npermiso = mysql_num_rows($res_permiso);
                                    if ($npermiso > 0){
                                        $permiso = "X";
                                    }
                                ?>
                                    <td class="centro"><?php echo $permiso;?></td>
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
                    <p class="error">Error: <b><?php echo $errpermisos;?></b></p>
            <?php
                }
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
