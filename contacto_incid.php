<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotainc_$idioma.php";
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
import_request_variables("gp", "");

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
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
?>
    </head>
    <body>
<?php
if ($permiso == 2) {
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
?>
        <p class='error'>No hay resultados en la consulta de la Flota</p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
    }
?>
        <h1>Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>

        <h2><?php echo $h2admin; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t5c"><?php echo $acroflota; ?></th>
                <th class="t5c"><?php echo $usuflota; ?></th>
                <th class="t10c"><?php echo $activa; ?></th>
                <th class="t10c"><?php echo $encripta; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_flota["LOGIN"]; ?></td>
                <td><?php echo $row_flota["ACTIVO"]; ?></td>
                <td><?php echo $row_flota["ENCRIPTACION"]; ?></td>
            </tr>
        </table>
        <h2><?php echo $h3flota; ?></h2>
        <form name="contincid" action="editar_continc.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <input type="hidden" name="flota" value="<?php echo $row_flota["FLOTA"]; ?>">
            <input type="hidden" name="acronimo" value="<?php echo $row_flota["ACRONIMO"]; ?>">
            <table>
                <tr>
                    <td class="t5c">&nbsp;</td>
                    <th class="t40p"><?php echo $nomflota; ?></th>
                    <th class="t10c"><?php echo $detalle; ?></th>
                    <th class="t10c"><?php echo $modificar; ?></th>
                    <th class="t10c"><?php echo $borrar; ?></th>
                    <th class="t10c"><?php echo $nuevo; ?></th>
                </tr>
<?php
        // Datos de contactos
        $contactos = array($row_flota["INCID1"], $row_flota["INCID2"], $row_flota["INCID3"], $row_flota["INCID4"]);
        $nom_contacto = array($contacto." 1", $contacto." 2", $contacto." 3", $contacto." 4");
        $var_contacto = array("Incid 1", "Incid 2", "Incid 3", "Incid 4");
        $hid_cont = array("id_inc1", "id_inc2", "id_inc3", "id_inc4");
        for ($j = 0; $j < count($contactos); $j++) {
            $ncontacto = 0;
            if ($contactos[$j] != 0) {
                $id_contacto = $contactos[$j];
                $sql_contacto = "SELECT * FROM contactos WHERE ID=$id_contacto";
                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
            }
            if ($ncontacto != 0) {
                $row_contacto = mysql_fetch_array($res_contacto);
?>
                <tr <?php if (($j % 2) == 1) echo "class='filapar'"; ?>>
                    <th><?php echo $nom_contacto[$j]; ?></th>
                    <td><?php echo $row_contacto["NOMBRE"]; ?></td>
                    <td class="centro"><input type='image' name='imgdet' value="<?php echo $var_contacto[$j]; ?>" src='imagenes/consulta.png' alt='<?php echo $detalle; ?>' title='<?php echo $detalle; ?>' onclick="document.contincid.detalle.value=this.value"></td>
                    <td class="centro"><input type='image' name='imgedir' value="<?php echo $var_contacto[$j]; ?>" src='imagenes/editar.png' alt='<?php echo $modificar; ?>' title='<?php echo $modificar; ?>' onclick="document.contincid.editar.value=this.value"></td>
                    <td class="centro"><input type='image' name='imgdel' value="<?php echo $var_contacto[$j]; ?>" src='imagenes/cancelar.png' alt='<?php echo $borrar; ?>' title='<?php echo $borrar; ?>' onclick="document.contincid.borrar.value=this.value"></td>
                    <td class="centro">-</td>
                </tr>
                <input type="hidden" name="<?php echo $hid_cont[$j]; ?>" value="<?php echo $id_contacto; ?>">
<?php
            }
            else {
?>
                <tr <?php if (($j % 2) == 1) echo "class='filapar'"; ?>>
                    <th><?php echo $nom_contacto[$j]; ?></th>
                    <td><span class="error"><?php echo $nocont . " " . $nom_contacto[$j]; ?> de Flota</span></td>
                    <td class="centro">-</td>
                    <td class="centro">-</td>
                    <td class="centro">-</td>
                    <td class="centro"><input type='image' name='imgnew' value="<?php echo $var_contacto[$j]; ?>" src='imagenes/nueva.png' alt='<?php echo $nuevo; ?>' title='<?php echo $nuevo; ?>' onclick="document.contincid.nuevo.value=this.value"></td>
                </tr>
                <?php
            }
        }
                ?>
            </table>
            <input type="hidden" name="detalle" value="">
            <input type="hidden" name="editar" value="">
            <input type="hidden" name="borrar" value="">
            <input type="hidden" name="nuevo" value="">
        </form>
        <table>
            <tr>
                <td class="borde">
                    <a href="#" onclick="document.detflota.submit();">
                        <img src='imagenes/atras.png' alt='<?php echo $volver;?>' title="<?php echo $volver;?>">
                    </a><br><?php echo $detalle." de Flota";?>
                </td>
                <td class="borde">
                    <a href="#" onclick="document.excflota.submit();">
                        <img src='imagenes/impexcel.png' alt='<?php echo $datexcel;?>' title="<?php echo $datexcel;?>">
                    </a><br><?php echo $datexcel;?>
                </td>
            </tr>
        </table>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
        <form name="excflota" action="excel_flota.php" method="POST">
            <input type="hidden" name="accion" value="ACTCONT">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
<?php
}
else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
}
?>
    </body>
</html>