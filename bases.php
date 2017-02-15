<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/bases_$idioma.php";
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
elseif ($flota_usu > 0){
    $permiso = 1;
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
        <h1>
            <?php echo $h1; ?> &mdash; <a href="bases.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <form action="bases.php" name="formulario" method="POST">
            <h4><?php echo $criterios; ?></h4>
            <table>
                <tr>
                    <td>
                        <select name='flota' onChange='document.formulario.submit();'> <option value='00' <?php if (($flota == "00") || ($flota == ""))     echo ' selected'; ?>>Flota</option>
<?php
                        $sql_flotas = "SELECT ID, FLOTA, ACRONIMO, ENCRIPTACION FROM flotas ORDER BY FLOTA ASC";
                        $res_flotas =  mysql_query($sql_flotas) or die(mysql_error());
                        $nflotas = mysql_num_rows($res_flotas);
                        for ($i = 0; $i < $nflotas; $i++) {
                            $row_flota = mysql_fetch_array($res_flotas);
?>
                            <option value='<?php echo $row_flota[0]; ?>' <?php if ($flota == $row_flota[0]) echo ' selected'; ?>><?php echo $row_flota[1]; ?></option>
<?php
                        }
?>
                        </select>
                    </td>
                </tr>
            </table>
<?php
            //datos de la tabla Bases
            $sql_bases = "SELECT terminales.ISSI, flotas.FLOTA, municipios.MUNICIPIO, bases.TERMINAL, bases.FLOTA AS 'IDFLOTA' FROM bases, terminales, flotas, municipios ";
            $sql_bases = $sql_bases . "WHERE terminales.ID = bases.TERMINAL AND flotas.ID = bases.FLOTA AND municipios.ine = bases.MUNICIPIO ";
            if ($tam_pagina == "") {
                $tam_pagina = 20;
            }
            if (!$pagina) {
                $inicio = 0;
                $pagina = 1;
            }
            else {
                $inicio = ($pagina - 1) * $tam_pagina;
            }
            if (($flota != '') && ($flota != "00")) {
                $sql_bases = $sql_bases . " AND (bases.FLOTA='$flota') ";
            }
            $sql_bases = $sql_bases . " ORDER BY flotas.FLOTA ASC";
            $sql_limit = $sql_bases . " LIMIT " . $inicio . "," . $tam_pagina . ";";
            $res_tot =  mysql_query($sql_bases) or die("Error en la consulta de Bases: " . mysql_error());
            $nbasestot = mysql_num_rows($res_tot);
            $total_pag = ceil($nbasestot / $tam_pagina);
            ########### Enlaces para la exportación #######
            //$linkpdf = "pdfbases.php?flota=$flota";
            //$linkxls = "xlsbases.php?flota=$flota";
            $linkpdf = "document.flotasexp.formato.value='pdf';document.flotasexp.submit();";
            $linkxls = "document.flotasexp.formato.value='xls';document.flotasexp.submit();";
            if ($nbasestot == 0) {
?>
                <p class='error'><?php echo $nobases; ?></p>
<?php
            }
            else {
                $res_bases =  mysql_query($sql_limit) or die("Error en la consulta limitada de Bases: " . mysql_error());
                $nbases = mysql_num_rows($res_bases);
?>
                <h4><?php echo $h4res; ?></h4>
                <table>
                    <tr class="borde">
                        <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nbasestot; ?></b>.</td>
                        <td class="borde">
                           Mostrar:
                            <select name='tam_pagina' onChange='document.formulario.submit();'>
                                <option value='10' <?php if ($tam_pagina == "10") {echo 'selected';}?>>10</option>
                                <option value='20' <?php if ($tam_pagina == "20") {echo 'selected';}?>>20</option>
                                <option value='30' <?php if ($tam_pagina == "30") {echo 'selected';}?>>30</option>
                                <option value='<?php echo $nbasestot;?>' <?php if ($tam_pagina == $nbasestot) {echo 'selected';}?>>Todas</option>
                            </select> <?php echo $regpg; ?>
                        </td>
<?php
                        if ($total_pag > 1) {
?>
                        <td class="borde">
                           <?php echo "$pgtxt:";?>
                            <select name='pagina' onChange='document.formulario.submit();'>
<?php
                            for ($k = 1; $k <= $total_pag; $k++) {
?>
                                <option value='<?php echo $k;?>' <?php if ($pagina == $k) {echo 'selected';}?>><?php echo $k;?></option>;
<?php
                            }
?>
                            </select>
                            de <?php echo $total_pag;?>
                        </td>
<?php
                        }
                        if ($nbasestot > 0) {
?>
                        <td class="borde">
                            <a href="#" onclick="<?php echo $linkpdf; ?>"><img src="imagenes/pdf.png" alt="PDF" title="PDF"></a> &mdash;
                            <a href="#" onclick="<?php echo $linkxls; ?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
                        </td>
<?php
                        }
?>
                    </tr>
                </table>
        </form>
        <form name="flotasexp" action="basesexp.php" method="POST" target="_blank">
            <input type="hidden" name="flota" value="<?php echo $flota; ?>">
            <input type="hidden" name="formato" value="#">
        </form>
                <table>
                    <tr>
                        <th class="t30p"><?php echo $ciudad; ?></th>
                        <th class="t2c">Flota</th>
                        <th class="t5c">ISSI</th>
                    </tr>
<?php
                for ($i = 0; $i < $nbases; $i++) {
                    $row_base = mysql_fetch_array($res_bases);
                    $flotatxt = $row_base["FLOTA"];
                    $issi = $row_base["ISSI"];
                    $municipio = $row_base["MUNICIPIO"];
                    $linkterm = "document.termdet.idterm.value='".$row_base["TERMINAL"]."';document.termdet.submit();";
                    $linkflota = "document.flotadet.idflota.value='".$row_base["IDFLOTA"]."';document.flotadet.submit();";
?>
                    <tr <?php if (($i % 2) == 1) echo "class='filapar'"; ?>>
                        <td><?php echo $municipio; ?></td>
                        <td><?php echo $flotatxt; ?> - <a href="#" onclick="<?php echo $linkflota; ?>"><img src="imagenes/ir.png" alt="Ir" title="Ir"></a></td>
                        <td><?php echo $issi; ?> - <a href="#" onclick="<?php echo $linkterm; ?>"><img src="imagenes/ir.png" alt="Ir" title="Ir"></a></td>
                    </tr>
<?php
                }
?>
                </table>
                <form name="flotadet" action="detalle_flota.php" method="POST">
                    <input type="hidden" name="idflota" value="#">
                </form>
                <form name="termdet" action="detalle_terminal.php" method="POST">
                    <input type="hidden" name="idterm" value="#">
                </form>
<?php
            }
?>
    </body>
</html>