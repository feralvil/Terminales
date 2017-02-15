<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/baseflota_$idioma.php";
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
if ($permiso != 0) {
    //datos de la tabla flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota =  mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
?>
        <p class='error'><?php echo $errnoflota; ?></p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $ine = $row_flota ["INE"];
        $flotatxt = $row_flota ["FLOTA"];
    }
    //datos de la tabla municipios
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun =  mysql_query($sql_mun) or die("Error en la consulta de Municipio:" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
?>
        <p class='error'><?php echo $errnomun; ?></p>
<?php
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
        $sql_munbase = "SELECT * FROM municipios";
        $res_munbase =  mysql_query($sql_munbase) or die("Error en la consulta de Municipio" . mysql_error());
        $nmunbase = mysql_num_rows($res_munbase);
        if ($nmunbase == 0) {
?>
            <p class='error'><?php echo $errnomb; ?></p>
<?php
        }
    }
?>
        <h1><?php echo "$h1 $flotatxt"; ?></h1>
        <h2><?php echo $h2flota; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t5c"><?php echo $acroflota; ?></th>
                <th class="t40p"><?php echo $localiza; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_flota["DOMICILIO"] . " &mdash; " . $row_flota["CP"] . " " . $row_mun["MUNICIPIO"]; ?></td>
            </tr>
        </table>
        <h2><?php echo $h2term; ?></h2>
        <form action="base_flota.php" method="POST" name="selterminal">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <h4><?php echo $criterios; ?></h4>
            <table>
                <tr>
                    <td>
                        ISSI:&#160;<input type="text" name="issicuad" size="6">&#160;<input type='image' name='action' src="imagenes/consulta.png">
                    </td>
                    <td>
                        <select name='issisel' onChange='document.selterminal.submit();'>
                            <option value='00' <?php if (($flota == "00") || ($flota == "")) echo ' selected'; ?>>ISSI</option>
<?php
                            //datos de la tabla terminales
                            $sql_terminales = "SELECT * FROM terminales WHERE FLOTA='$idflota' ORDER BY ISSI ASC";
                            $res_terminales =  mysql_query($sql_terminales) or die("Error en la consulta de terminal: " . mysql_error());
                            $nterminales = mysql_num_rows($res_terminales);
                            if ($nterminales == 0) {
?>
                                <p class='error'>No hay resultados en la consulta de Terminales</p>
<?php
                            }
                            else {
                                for ($i = 0; $i < $nterminales; $i++) {
                                    $row_terminales = mysql_fetch_array($res_terminales);
                                    $issiterm = $row_terminales["ISSI"];
?>
                                    <option value='<?php echo $issiterm; ?>' <?php if ($issiterm == $issisel) echo ' selected'; ?>><?php echo $issiterm; ?></option>
<?php
                                }
                            }
?>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
<?php
        $issi = 0;
        if ($issicuad != "") {
            $issi = $issicuad;
            }
            else {
                if (($issisel != "") && ($issisel != "00")) {
                    $issi = $issisel;
                }
            }
            //datos de la tabla terminales
            if ($issi == 0) {
?>
                <p class='error'><?php echo $noterm; ?></p>
<?php
            }
            else {
                $sql_terminal = "SELECT * FROM terminales WHERE FLOTA='$idflota' AND ISSI = '$issi'";
                $res_terminal =  mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
                $nterminal = mysql_num_rows($res_terminal);
                if ($nterminal == 0) {
?>
                <p class='error'><?php echo $errnoterm; ?></p>
<?php
                }
                else {
                    $row_terminal = mysql_fetch_array($res_terminal);
                    $id_term = $row_terminal["ID"];
                    $tipo = $row_terminal["TIPO"];
                    switch ($tipo) {
                        case ("F"): {
                            $tipo = $fijo;
                            break;
                        }
                        case ("M"): {
                            $tipo = $movil;
                            break;
                        }
                        case ("MB"): {
                            $tipo = $movilb;
                            break;
                        }
                        case ("MA"): {
                            $tipo = $movila;
                            break;
                        }
                        case ("MG"): {
                            $tipo = $movilg;
                            break;
                        }
                        case ("P"): {
                            $tipo = $portatil;
                            break;
                        }
                        case ("PB"): {
                            $tipo = $portatilb;
                            break;
                        }
                        case ("PA"): {
                            $tipo = $portatila;
                            break;
                        }
                        case ("PX"): {
                            $tipo = $portatilx;
                            break;
                        }
                        case ("D"): {
                            $tipo = $despacho;
                            break;
                        }
                    }
                }
            }
?>
                <table>
                    <tr>
                        <th class="t5c">ISSI</th>
                        <th class="t5c"><?php echo $tipotxt; ?></th>
                        <th class="t5c">Marca</th>
                        <th class="t5c"><?php echo $modtxt; ?></th>
                        <th class="t5c"><?php echo $proveedor; ?></th>
                    </tr>
                    <tr>
                        <td class="centro"><?php echo $row_terminal["ISSI"]; ?></td>
                        <td class="centro"><?php echo $tipo; ?></td>
                        <td class="centro"><?php echo $row_terminal["MARCA"]; ?></td>
                        <td class="centro"><?php echo $row_terminal["MODELO"]; ?></td>
                        <td class="centro"><?php echo $row_terminal["PROVEEDOR"]; ?></td>
                    </tr>
                </table>
                <form action="update_bases.php" method="POST" name="formmunil">
                    <h2><?php echo $h2muni; ?></h2>
                    <table>
                        <tr>
                            <th><?php echo $munselect; ?></th>
                        </tr>
                        <tr>
                            <td class="centro">
                                <select name="ineselect">
<?php
                                for ($i = 0; $i < $nmunbase; $i++) {
                                    $row_munbase = mysql_fetch_array($res_munbase);
                                    $inetxt = $row_munbase["INE"];
                                    $muntxt = $row_munbase["MUNICIPIO"];
?>
                                    <option value='<?php echo $inetxt; ?>' <?php if ($inetxt == $ine)echo "selected"; ?>><?php echo $muntxt; ?></option>
<?php
                                }
?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="idterm" value="<?php echo $id_term; ?>">
                    <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                    <input type="hidden" name="issi" value="<?php echo $row_terminal["ISSI"]; ?>">
                    <input type="hidden" name="flotatxt" value="<?php echo $row_flota["FLOTA"]; ?>">
                    <input type="hidden" name="origen" value="flota">
<?php
                    if ($permiso == 2) {
?>
                    <table>
                        <tr>
<?php
                        if ($issi != 0) {
?>
                            <td class="borde">
                                <input type='image' name='action' src='imagenes/nuevo.png' alt='<?php echo $boton; ?>' title="<?php echo $boton; ?>">
                                <br><?php echo $boton; ?>
                            </td>
<?php
                        }
?>
                            <td class="borde">
                                <a href='#' onclick="document.formdet.submit();">
                                    <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title="<?php echo $botatras; ?>">
                                </a><br><?php echo $botatras; ?>
                            </td>
                        </tr>
                    </table>
<?php
                    }
?>
                </form>
                <form name="formdet" action="detalle_flota.php" method="POST">
                    <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                </form>
<?php
}
else {
?>
                <h1><?php echo $h1perm; ?></h1>
                <p class='error'><?php echo $permno; ?></p>
<?php
}
?>
    </body>
</html>
