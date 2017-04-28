<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotascont_$idioma.php";
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
        $nselflotas = mysql_num_rows($res_selflotas);
    ?>
        <h1><?php echo $h1; ?></h1>
        <form action="contactos_flotas.php" name="formulario" method="POST">
            <h4>
                <?php echo $criterios; ?> &mdash;
                <a href="contactos_flotas.php">
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
                    <td>
                        <label for="selprov"><?php echo $txtprov; ?>: </label>
                        <select name='selprov' id="selprov" onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($selprov == "00") || ($selprov == "")) echo ' selected'; ?>>Seleccionar</option>
                            <option value='03' <?php if ($selprov == "03") echo ' selected'; ?>>Alacant/Alicante</option>
                            <option value='12' <?php if ($selprov == "12") echo ' selected'; ?>>Castelló/Castellón</option>
                            <option value='46' <?php if ($selprov == "46") echo ' selected'; ?>>València/Valencia</option>
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
                        <label for="selcont"><?php echo $txtcontof; ?>: </label>
                        <select name='formcont' id="selcont" onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($formcont == "00") || ($formcont == "")) echo ' selected'; ?>>Seleccionar</option>
                            <option value='SI' <?php if ($formcont == "SI") echo ' selected'; ?>>Sí</option>
                            <option value='NO' <?php if ($formcont == "NO") echo ' selected'; ?>>No</option>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
        <form name="expcontactos" action="xlscontresp.php" target="_blank" method="post">
            <input type="hidden" name="idorg" value="<?php echo $organiza; ?>" />
            <input type="hidden" name="idflota" value="<?php echo $selflota; ?>" />
            <input type="hidden" name="formcont" value="<?php echo $formcont; ?>" />
            <input type="hidden" name="idprov" value="<?php echo $selprov; ?>" />
        </form>
        <?php
        $sql_flotas = "SELECT flotas.ID, flotas.FLOTA, flotas.ACRONIMO, flotas.FORMCONT, organizaciones.ORGANIZACION";
        $sql_flotas .= " FROM flotas, organizaciones WHERE (flotas.ORGANIZACION = organizaciones.ID)";
        if (($selflota != '') && ($selflota != "00")) {
            $sql_flotas = $sql_flotas . " AND (flotas.ID = $selflota)";
        }
        if (($organiza != '') && ($organiza != "00")) {
            $sql_flotas = $sql_flotas . " AND (flotas.ORGANIZACION = $organiza)";
        }
        if (($selprov != '') && ($selprov != "00")) {
            $sql_flotas = $sql_flotas . " AND (flotas.INE LIKE '$selprov%')";
        }
        if (($formcont != '') && ($formcont != "00")) {
            $sql_flotas = $sql_flotas . ' AND (flotas.FORMCONT = "' . $formcont . '")';
        }
        $sql_flotas .= " ORDER BY organizaciones.ORGANIZACION, flotas.FLOTA ASC";
        $res_flotas = mysql_query($sql_flotas) or die(mysql_error() . ': ' . $sql_flotas);
        $nflotas = mysql_num_rows($res_flotas);
        ?>
        <h4>
            <?php
            echo $h4res;
            if ($nflotas > 0){
            ?>
            &mdash; <?php echo ' ' . $nflotas . ' ' . $txtflotas;?>
            &mdash;
            <a href="#" onclick='document.expcontactos.submit();'>
                <img src="imagenes/xls.png" alt="<?php echo $botexcel;?>" title="<?php echo $botexcel;?>">
            </a>
            <?php
            }
            ?>
        </h4>
        <?php
        if ($nflotas > 0){
        ?>
            <table>
                <tr>
                    <th><?php echo $thorg;?></th>
                    <th>Flota</th>
                    <th><?php echo $thoficial;?></th>
                    <th><?php echo $thresp;?></th>
                    <th><?php echo $thcargo;?></th>
                    <th><?php echo $thcorreo;?></th>
                </tr>
                <?php
                for ($i = 0; $i < $nflotas; $i++){
                    $flota = mysql_fetch_array($res_flotas);
                    $idflota = $flota['ID'];
                    $sql_contflota = 'SELECT * FROM contactos_flotas WHERE (FLOTA_ID = ' . $idflota . ') AND (ROL = "RESPONSABLE")';
                    $res_contflota = mysql_query($sql_contflota) or die(mysql_error());
                    $ncontflota = mysql_num_rows($res_contflota);
                    if ($ncontflota > 0){
                        $contflota = mysql_fetch_array($res_contflota);
                        $idcontacto = $contflota['CONTACTO_ID'];
                        if ($idcontacto > 0){
                            $sql_contacto = 'SELECT * FROM contactos WHERE (ID = ' . $idcontacto . ')';
                            $res_contacto = mysql_query($sql_contacto) or die(mysql_error());
                            $ncontacto = mysql_num_rows($res_contacto);
                            if ($ncontacto > 0){
                                $contacto = mysql_fetch_array($res_contacto);
                            }
                        }
                    }

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
                        <td><?php echo $flota['FORMCONT'];?></td>
                        <?php
                        if ($ncontacto > 0){
                        ?>
                            <td><?php echo $contacto['NOMBRE'];?></td>
                            <td><?php echo $contacto['CARGO'];?></td>
                            <td><?php echo $contacto['MAIL'];?></td>
                        <?php
                        }
                        else{
                        ?>
                            <td colspan="3">
                                <span class="error"><?php echo $txtnocont;?></span>
                            </td>
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
