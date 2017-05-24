<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/passflotas_$idioma.php";
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
<html>
<head>
    <title><?php echo $titulo; ?></title>
    <link rel="StyleSheet" type="text/css" href="estilo.css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <?php
    // Si la sesión de Joomla ha caducado, recargamos la página principal
    if ($flota_usu == 0){
    ?>
        <script type="text/javascript">
            window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
        </script>
    <?php
    }
    ?>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/password_flotas.js"></script>
</head>
<body>
    <?php
    if ($permiso > 1){
        // Select de organizaciones:
        $sql_organiza = "SELECT * FROM organizaciones ORDER BY ORGANIZACION ASC";
        $res_organiza = mysql_query($sql_organiza) or die(mysql_error());
        $norganiza = mysql_num_rows($res_organiza);
        // Select de flotas:
        $sql_selflotas = "SELECT * FROM flotas WHERE 1";
        if (($organiza != '') && ($organiza != "00")) {
            $sql_selflotas .= ' AND (ORGANIZACION = ' . $organiza . ')';
        }
        $sql_selflotas .= " ORDER BY FLOTA ASC";
        $res_selflotas = mysql_query($sql_selflotas) or die(mysql_error());
        $nselflotas = mysql_num_rows($res_selflotas);if (isset ($update)){
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
        <h1><?php echo $h1; ?></h1>
        <form action="password_flotas.php" name="formulario" method="POST">
            <h4>
                <?php echo $criterios; ?> &mdash;
                <a href="password_flotas.php">
                    <img src="imagenes/update.png" alt="<?php echo $resetcrit;?>" title="<?php echo $resetcrit;?>">
                </a>
            </h4>
            <table>
                <tr>
                    <td>
                        <label for="selrog"><?php echo $thorg; ?>: </label>
                        <select name='organiza' id="selorg" onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($organiza == "00") || ($organiza == "")) echo ' selected'; ?>>
                                Seleccionar
                            </option>
                            <?php
                            for ($i = 0; $i < $norganiza; $i++){
                                $org = mysql_fetch_array($res_organiza);
                            ?>
                                <option value='<?php echo $org['ID']; ?>' <?php if ($organiza == $org['ID']) echo ' selected'; ?>>
                                    <?php echo $org['ORGANIZACION']; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="selflota">Flota: </label>
                        <select name='selflota' id="selflota" onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($selflota == "00") || ($selflota == "")) echo ' selected'; ?>>
                                Seleccionar
                            </option>
                            <?php
                            for ($i = 0; $i < $nselflotas; $i++){
                                $flotasel = mysql_fetch_array($res_selflotas);
                            ?>
                                <option value='<?php echo $flotasel['ID']; ?>' <?php if ($selflota == $flotasel['ID']) echo ' selected'; ?>>
                                    <?php echo $flotasel['FLOTA']; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="selpassw"><?php echo $txtpassw; ?>: </label>
                        <select name='formpass' id="selpassw" onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($formpass == "00") || ($formpass == "")) echo ' selected'; ?>>Seleccionar</option>
                            <option value='NO' <?php if ($formpass == "NO") echo ' selected'; ?>>No</option>
                            <option value='SI' <?php if ($formpass == "SI") echo ' selected'; ?>>Sí</option>
                            <option value='PDTE' <?php if ($formpass == "PDTE") echo ' selected'; ?>><?php echo $txtpend; ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
        <?php
        $sql_flotas = "SELECT flotas.ID, flotas.FLOTA, flotas.ACRONIMO, flotas.LOGIN, flotas.PASSWORD, flotas.PASSUPDATE, organizaciones.ORGANIZACION";
        $sql_flotas .= " FROM flotas, organizaciones WHERE (flotas.ORGANIZACION = organizaciones.ID)";
        if (($selflota != '') && ($selflota != "00")) {
            $sql_flotas = $sql_flotas . " AND (flotas.ID = $selflota)";
        }
        if (($organiza != '') && ($organiza != "00")) {
            $sql_flotas = $sql_flotas . " AND (flotas.ORGANIZACION = $organiza)";
        }
        if (($formpass != '') && ($formpass != "00")) {
            $sql_flotas = $sql_flotas . ' AND (flotas.PASSRESET = "' . $formpass . '")';
        }
        $sql_flotas .= " ORDER BY organizaciones.ORGANIZACION, flotas.FLOTA ASC";
        $res_flotas = mysql_query($sql_flotas) or die(mysql_error() . ': ' . $sql_flotas);
        $nflotas = mysql_num_rows($res_flotas);
        ?>
        <h4>
            <?php
            echo $h4res;
            if ($nflotas > 0){
                echo ' &mdash; ' . $nflotas . ' ' . $txtflotas;
            }
            ?>
        </h4>
        <?php
        if ($nflotas > 0){
        ?>
            <form name="formflotas" action="update_flota.php" method="post">
                <input type="hidden" name="origen" value="resetpassw" />
                <input type="hidden" name="idorg" value="<?php echo $organiza; ?>" />
                <input type="hidden" name="idflota" value="<?php echo $selflota; ?>" />
                <table>
                    <td class="borde">
                        <a href='flotas.php'>
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'>
                        </a><br><?php echo $botatras; ?>
                    </td>
                    <td class="borde">
                        <a href='#' id="botpassword">
                            <input type="image" src='imagenes/key.png' alt='<?php echo $botpass; ?>' title='<?php echo $botpass; ?>'/>
                        </a><br><?php echo $botpass; ?>
                    </td>
                </table>
                <table>
                    <tr>
                        <th><?php echo $thorg;?></th>
                        <th>Flota</th>
                        <th><?php echo $thacro;?></th>
                        <th><?php echo $thuser;?></th>
                        <th><?php echo $thpass;?></th>
                        <th>
                            <input type="checkbox" name="seltodo" id="seltodo" />
                            &mdash; Seleccionar
                        </th>
                    </tr>
                    <?php
                    for ($i = 0; $i < $nflotas; $i++){
                        $flota = mysql_fetch_array($res_flotas);
                    ?>
                        <?php
                        if (($i % 2) == 1){
                        ?>
                            <tr class="filapar">
                        <?php
                        }
                        else{
                        ?>
                            <tr>
                        <?php
                        }
                        ?>
                            <td><?php echo $flota['ORGANIZACION'];?></td>
                            <td><?php echo $flota['FLOTA'];?></td>
                            <td><?php echo $flota['ACRONIMO'];?></td>
                            <td><?php echo $flota['LOGIN'];?></td>
                            <td><?php echo $flota['PASSWORD'];?></td>
                            <td class="centro">
                                <input type="checkbox" id="flotasel-<?php echo $flota['ID']; ?>" name="flotasel[]" value="<?php echo $flota['ID']; ?>" />
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </form>
        <?php
        }
        else{
        ?>
            <p class='error'><?php echo $errnoflotas; ?></p>
    <?php
        }
    }
    else{
    ?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $permno; ?></p>
    <?php
    }
    ?>
</body>
</html>
