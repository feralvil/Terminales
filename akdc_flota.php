<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotakdc_$idioma.php";
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
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <script type="text/javascript">
            function checkAll() {
                var nodoCheck = document.getElementsByTagName("input");
                var varCheck = document.getElementById("seltodo").checked;
                for (i=0; i<nodoCheck.length; i++){
                    if (nodoCheck[i].type == "checkbox" && nodoCheck[i].name != "seltodo" && nodoCheck[i].disabled == false) {
                        nodoCheck[i].checked = varCheck;
                    }
                }
            }
        </script>
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
if ($permiso != 0) {
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
        $usuario = $row_flota["LOGIN"];
    }
    //datos de la tabla Terminales
    $sql_term = "SELECT * FROM terminales WHERE FLOTA='$idflota'";
    if (($carpeta != '') && ($carpeta != "NN")) {
        $sql_term .= " AND (CARPETA = '".$carpeta."')";
    }
    if ($issi != '') {
        $sql_term .= " AND (ISSI = '".$issi."')";
    }
    if ($tei != '') {
        $sql_term .= " AND (TEI = '".$tei."')";
    }
    $sql_term .= " ORDER BY ISSI ASC";
    $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminales" . mysql_error());
    $nterm = mysql_num_rows($res_term);

    //datos de la tabla Carpeta
    $sql_carpeta = "SELECT DISTINCT CARPETA FROM terminales WHERE FLOTA='$idflota'";
    $res_carpeta = mysql_query($sql_carpeta) or die("Error en la consulta de Carpeta: " . mysql_error());
    $ncarpeta = mysql_num_rows($res_carpeta);
    if ($ncarpeta == 0) {
?>
        <p class='error'>No hay resultados en la consulta de Carpetas</p>
<?php
    }
?>
        <form name="akdcflota" method="POST" action="akdc_flota.php" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
            <h1>
                Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>) &mdash;
                <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $newtab;?>' title="<?php echo $newtab;?>">
            </h1>
        </form>
        <form name="akdcreset" method="POST" action="akdc_flota.php">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
        </form>
        <h2>
            <?php echo $h2selec; ?> &mdash;
            <img src="imagenes/update.png" onclick="document.akdcreset.submit()" alt="Resetear" title="Resetear" />
        </h2>
            <form name="criterios" method="POST" action="akdc_flota.php">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
            <table>
            <tr>
                <td>
                    <select name = "carpeta" id = "carpeta" onchange="document.criterios.submit();">
                        <option value="NN" <?php if (($carpeta == "NN") || ($carpeta == "")) echo 'selected'; ?>><?php echo $selcarpeta; ?></option>
                    <?php
                        for ($i == 0; $i < $ncarpeta; $i++){
                            $row_carpeta = mysql_fetch_array($res_carpeta);
                            if ($row_carpeta[0] != ""){
                    ?>
                                <option <?php if ($carpeta == $row_carpeta[0]){ echo 'selected';}?> value="<?php echo $row_carpeta[0];?>">
                                    <?php echo $row_carpeta[0];?>
                                </option>
                    <?php
                            }
                        }
                    ?>
                    </select>
                </td>
                <td>
                    ISSI:&#160;<input type="text" name="issi" size="6" value="<?php echo $issi; ?>">
                    &#160;<input type='image' name='action' src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                </td>
                <td>
                    TEI:&#160;<input type="text" name="tei" size="12" value="<?php echo $tei; ?>">
                    &#160;<input type='image' name='action' src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                </td>
            </tr>


            </table>
        </form>
        <h2><?php echo $h2term; ?> &mdash; <?php echo $nterm; ?></h2>
        <?php
            $linknumk = "document.getElementById('param').value='numk';document.terminales.submit();";
            $linkitsi = "document.getElementById('param').value='itsi';document.terminales.submit();";
        ?>
        <table>
            <tr>
                <td class="borde">
                    <a href='#' onclick="<?php echo $linknumk; ?>"><img src='imagenes/akdc.png' alt='REF-K' title='REF-K'></a><br>Generar REF-K
                </td>
                <td class="borde">
                    <a href='#' onclick="<?php echo $linkitsi; ?>"><img src='imagenes/akdc.png' alt='REF-ITSI' title='REF-ITSI'></a><br>Generar REF-ITSI
                </td>
                <td class="borde">
                    <a href='#' onclick="document.detflota.submit();"><img src='imagenes/atras.png' alt='<?php echo $terminales; ?>' title='<?php echo $detalle; ?>'></a><br><?php echo $detalle; ?> de Flota
                </td>
            </tr>
        </table>
        <form name="terminales" method="POST" action="akdc_ref.php">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <input type="hidden" name="carpeta" value="<?php echo $carpeta; ?>">
            <input type="hidden" name="parametro" id="param" value="itsi">
            <input type="hidden" name="issi" value="<?php echo $issi; ?>">
            <input type="hidden" name="tei" value="<?php echo $tei; ?>">
        <table>
<?php
        if ($nterm == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
        }
        else {
?>
                <tr>
                    <th>ID</th>
                    <th>ISSI</th>
                    <th>TEI</th>
                    <th>Número K</th>
                    <th><?php echo $mnemo; ?></th>
                    <th>
                        <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" />
                        &mdash; Seleccionar
                    </th>
                </tr>
<?php
            for ($i = 0; $i < $nterm; $i++) {
                $row_term = mysql_fetch_array($res_term);
                $idterm = $row_term["ID"];
?>
                <tr <?php if (($i % 2) == 0) echo "class='filapar'";?>>
                    <td class="centro"><?php echo $idterm; ?></td>
                    <td><?php echo $row_term["ISSI"]; ?></td>
                    <td><?php echo $row_term["TEI"]; ?></td>
                    <td><?php echo $row_term["NUMEROK"]; ?></td>
                    <td><?php echo $row_term["MNEMONICO"]; ?></td>
                    <td class="centro">
                        <input type="checkbox" name="termakdc[]" value="<?php echo $idterm; ?>" />
                    </td>
                </tr>
<?php
            }
        }
?>
        </table>
    </form>
    <form name="detflota" action="detalle_flota.php" method="POST">
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
