<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/baseterm_$idioma.php";
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
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
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
<?php
//datos de la tabla terminales
$sql_terminal = "SELECT * FROM terminales WHERE ID='$idterm'";
$res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
$nterminal = mysql_num_rows($res_terminal);
if ($nterminal == 0) {
    echo "<p class='error'>No hay resultados en la consulta del Terminal</p>\n";
}
else {
    $row_terminal = mysql_fetch_array($res_terminal);
    $id_flota = $row_terminal["FLOTA"];
    $tipo = $row_terminal["TIPO"];
    $am = $row_terminal["AM"];
    $dots = $row_terminal["DOTS"];
}
//datos de la tabla flotas
$sql_flota = "SELECT * FROM flotas WHERE ID='$id_flota'";
$res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota Usuaria: " . mysql_error());
$nflota = mysql_num_rows($res_flota);
if ($nflota == 0) {
    echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
}
else {
    $row_flota = mysql_fetch_array($res_flota);
    $ine = $row_flota ["INE"];
}
//datos de la tabla municipios
$sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
$res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
$nmun = mysql_num_rows($res_mun);
if ($nmun == 0) {
    echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
}
else {
    $row_mun = mysql_fetch_array($res_mun);
}
//datos de la tabla bases
if ($accion == "del") {
    $h1_p1 = $h1_p1_del;
    $sql_base = "SELECT * FROM bases WHERE TERMINAL='$idterm' AND FLOTA='$id_flota'";
    $res_base = mysql_query($sql_base) or die("Error en la consulta de Bases: " . mysql_error());
    $nbase = mysql_num_rows($res_base);
    if ($nbase == 0) {
        echo "<p class='error'>No hay resultados en la consulta de Bases</p>\n";
    }
    else {
        $row_base = mysql_fetch_array($res_base);
        //$id_base =$row_base["ID"];
        $inebase = $row_base["MUNICIPIO"];
        $sql_munbase = "SELECT * FROM municipios WHERE INE='$inebase'";
        $res_munbase = mysql_query($sql_munbase) or die("Error en la consulta de Municipio" . mysql_error());
        $nmunbase = mysql_num_rows($res_mun);
        if ($nmunbase == 0) {
            echo "<p class='error'>No hay resultados en la consulta del Municipio de la Base</p>\n";
        }
        else {
            $row_munbase = mysql_fetch_array($res_munbase);
        }
    }
    $imagen = "imagenes/eliminar.png";
    $boton = $botdel;
}
else {
    $h1_p1 = $h1_p1_add;
    $inebase = $row_flota ["INE"];
    $imagen = "imagenes/nuevo.png";
    $boton = $botadd;
    $sql_munbase = "SELECT * FROM municipios";
    $res_munbase = mysql_query($sql_munbase) or die("Error en la consulta de Municipio" . mysql_error());
    $nmunbase = mysql_num_rows($res_munbase);
    if ($nmunbase == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Municipio de la Base</p>\n";
    }
}
?>
    <body>
<?php
    if ($permiso != 0) {
?>
        <h1><?php echo $h1_p1 . " " . $row_terminal["ISSI"] . " " . $h1_p2; ?></h1>
        <form action="update_bases.php" method="POST" name="formterminal">
            <h2><?php echo $h2term; ?></h2>
            <table>
                <tr>
                    <th class="t4c"><?php echo $tipotxt; ?></th>
                    <th class="t4c">Marca</th>
                    <th class="t4c"><?php echo $modtxt; ?></th>
                    <th class="t4c"><?php echo $proveedor; ?></th>
                </tr>
                <tr>
<?php
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
?>
                    <td class="centro"><?php echo $tipo; ?></td>
                    <td class="centro"><?php echo $row_terminal["MARCA"]; ?></td>
                    <td class="centro"><?php echo $row_terminal["MODELO"]; ?></td>
                    <td class="centro"><?php echo $row_terminal["PROVEEDOR"]; ?></td>
                </tr>
            </table>
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
                    <td><?php echo $row_flota["DOMICILIO"]." &mdash; ". $row_flota["CP"] . " " . $row_mun["MUNICIPIO"]; ?></td>
                </tr>
            </table>
            <h2><?php echo $h2muni; ?></h2>
            <table>
<?php
            if ($accion == "add") {
?>
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
                                <option value='<?php echo $inetxt; ?>' <?php if ($inetxt == $inebase) echo "selected"; ?>><?php echo $muntxt; ?></option>
<?php
                        }
?>
                            </select>
                        </td>
                    <tr>
<?php
            }
            else {
?>
                    <tr>
                        <th><?php echo $prov; ?></th>
                        <th><?php echo $ciudad; ?></th>
                    </tr>
                    <tr>
                        <td class="centro"><?php echo $row_munbase["PROVINCIA"]; ?></td>
                        <td class="centro"><?php echo $row_munbase["MUNICIPIO"]; ?></td>
                    </tr>
<?php
            }
?>
            </table>
            <input type="hidden" name="idterm" value="<?php echo $idterm; ?>">
            <input type="hidden" name="idflota" value="<?php echo $id_flota; ?>">
            <input type="hidden" name="issi" value="<?php echo $row_terminal["ISSI"]; ?>">
            <input type="hidden" name="flotatxt" value="<?php echo $row_flota["FLOTA"]; ?>">
            <input type="hidden" name="origen" value="terminal">
            <input type="hidden" name="accbase" value="<?php echo $accion; ?>">
            <table>
                <tr>
                    <td class="borde">
                        <input type='image' name='action' src='<?php echo $imagen; ?>' alt='<?php echo $boton; ?>' title="<?php echo $boton; ?>">
                        <br><?php echo $boton; ?>
                    </td>
                    <td class="borde">
                         <a href='#' onclick="document.detterm.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title="<?php echo $botatras; ?>">
                        </a><br><?php echo $botatras; ?>
                    </td>
                </tr>
            </table>
        </form>
        <form name="detterm" action="detalle_terminal.php" method="POST">
            <input type="hidden" name="idterm" value="<?php echo $idterm;?>">
        </form>
<?php
    }
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
    }
?>
    </body>
</html>
