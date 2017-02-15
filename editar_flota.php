<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotaedi_$idioma.php";
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
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        if ($row_flota["RANGO"] != ""){
            $rango = explode('-', $row_flota["RANGO"]);
        }
    }
    //datos de la tabla Terminales
    // Tipos de termninales
    $tipos = array("F", "M%", "MB", "MA", "MG", "P%", "PB", "PA", "PX");
    $nterm = array(0, 0, 0, 0, 0, 0, 0, 0, 0);
    $sql_term = "SELECT * FROM terminales WHERE FLOTA='$idflota'";
    $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminales" . mysql_error());
    $tot_term = mysql_num_rows($res_term);
    for ($j = 0; $j < count($tipos); $j++) {
        $sql_term = "SELECT * FROM terminales WHERE FLOTA='$idflota' AND TIPO LIKE '" . $tipos[$j] . "'";
        $res_term = mysql_query($sql_term) or die("Error en la consulta de " . $cabecera[$j] . ": " . mysql_error());
        $nterm[$j] = mysql_num_rows($res_term);
    }
    //datos de la tabla Municipio
    // INE
    $ine = $row_flota["INE"];
?>
        <h1>Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <form name="formflota" action="update_flota.php" method="POST">
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
                    <td><input type="text" name="flota" value="<?php echo $row_flota["FLOTA"]; ?>" size="40"></td>
                    <td><input type="text" name="acronimo" value="<?php echo $row_flota["ACRONIMO"]; ?>" size="10"></td>
                    <td><input type="text" name="login" value="<?php echo $row_flota["LOGIN"]; ?>" size="10"></td>
                    <td>
                        <select name="activa">
                            <option value="SI" <?php if ($row_flota["ACTIVO"] == "SI") {echo 'selected';} ?>>SI</option>
                            <option value="NO" <?php if ($row_flota["ACTIVO"] == "NO") {echo 'selected';} ?>>NO</option>
                        </select>
                    </td>
                    <td>
                        <select name="encriptacion">
                            <option value="SI" <?php if ($row_flota["ENCRIPTACION"] == "SI") echo 'selected'; ?>>SI</option>
                            <option value="NO" <?php if ($row_flota["ENCRIPTACION"] == "NO") echo 'selected'; ?>>NO</option>
                        </select>
                    </td>
                </tr>
            </table>
            <h2><?php echo $h2localiza; ?></h2>
            <table>
                <tr>
                    <th class="t40p"><?php echo $domicilio; ?></th>
                    <th class="t5c"><?php echo $cp; ?></th>
                    <th class="t40p"><?php echo $ciudad; ?></th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="domicilio" value="<?php echo $row_flota["DOMICILIO"]; ?>" size="40">
                    </td>
                    <td><input type="text" name="cp" value="<?php echo $row_flota["CP"]; ?>" size="10"></td>
                    <td>
                        <select name="ine">
<?php
                            $sql_mun = "SELECT * FROM municipios ORDER BY MUNICIPIO ASC";
                            $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
                            $nmun = mysql_num_rows($res_mun);
                            if ($nmun == 0) {
                                echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
                            }
                            else {
                                for ($i = 0; $i < $nmun; $i++) {
                                    $row_mun = mysql_fetch_array($res_mun);
                                    $ine_mun = $row_mun["INE"];
                                    $nom_mun = $row_mun["MUNICIPIO"];
?>
                                    <option value="<?php echo $ine_mun; ?>" <?php if ($ine == $ine_mun) echo 'selected'; ?>>
                                        <?php echo $nom_mun; ?>
                                    </option>
<?php
                                    }
                                }
?>
                            </select>
                    </tr>
                </table>
                <h2><?php echo $h2org; ?></h2>
                <?php
                $sql_org = "SELECT * FROM organizaciones ORDER BY ORGANIZACION ASC";
                $res_org = mysql_query($sql_org) or die("Error en la consulta de Organización" . mysql_error());
                $norg = mysql_num_rows($res_org);
                ?>
                <select name="organiza">
                <?php
                for ($i = 0; $i < $norg; $i++){
                    $row_org = mysql_fetch_array($res_org);
                ?>
                    <option value="<?php echo $row_org['ID']; ?>" <?php if ($row_org['ID'] == $row_flota['ORGANIZACION']) {echo 'selected';} ?>>
                        <?php echo $row_org['ORGANIZACION']; ?>
                    </option>
                <?php
                }
                ?>
                </select>
                <h2><?php echo $h2term; ?></h2>
                <h3><?php echo $h3rango; ?></h3>
                    <p>                        
                        <input type="text" name="rangoini" value="<?php echo $rango[0]; ?>" size="10"> &mdash;
                        <input type="text" name="rangofin" value="<?php echo $rango[1]; ?>" size="10">
                    </p>
                    <table>
                        <tr>
                            <th class="t10c"><?php echo $totalterm; ?></th>
<?php
                            for ($i = 0; $i < count($tipos); $i++) {
?>
                                <th class="t10c"><?php echo $cabecera[$i]; ?></th>
<?php
                            }
?>
                        </tr>
                        <tr>
                            <td class="centro"><?php echo $tot_term; ?></td>
<?php
                            for ($i = 0; $i < count($tipos); $i++) {
?>
                                <td class="centro"><?php echo $nterm[$i]; ?></td>
<?php
                            }
?>
                        </tr>
                    </table>
                    <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                    <input type="hidden" name="flota_org" value="<?php echo $row_flota["FLOTA"]; ?>">
                    <input type="hidden" name="acro_org" value="<?php echo $row_flota["ACRONIMO"]; ?>">
                    <input type="hidden" name="origen" value="editar">
                    <table>
                        <tr>
                            <td class="borde">
                                <input type='image' name='action' src='imagenes/guardar.png' alt='<?php echo $guardar; ?>' title="<?php echo $guardar; ?>"><br><?php echo $guardar; ?>
                            </td>
                            <td class="borde">
                                <a href='#' onclick='document.formflota.reset();'>
                                    <img src='imagenes/no.png' alt='<?php echo $cancel; ?>' title="<?php echo $cancel; ?>">
                                </a><br><?php echo $cancel; ?>
                            </td>
                            <td class="borde">
                                <a href='#' onclick='document.detflota.submit();'>
                                    <img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>">
                                </a><br><?php echo $volver; ?>
                            </td>
                        </tr>
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