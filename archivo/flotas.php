<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotas_$idioma.php";
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
    if ($permiso == 2) {
        if (isset ($update)){
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
        <h1>
            <?php echo $h1; ?> &mdash; <a href="flotas.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <form action="flotas.php" name="formulario" method="POST">
            <h4>
                <?php echo $criterios; ?> &mdash;
                <a href="flotas.php">
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
                            $sql_organiza = "SELECT * FROM organizaciones ORDER BY ORGANIZACION ASC";
                            $res_organiza = mysql_query($sql_organiza) or die(mysql_error());
                            $norganiza = mysql_num_rows($res_organiza);
                            for ($i = 0; $i < $norganiza; $i++) {
                                $row_org = mysql_fetch_array($res_organiza);
?>
                                <option value='<?php echo $row_org['ID']; ?>' <?php if ($organiza == $row_org['ID']) echo ' selected'; ?>>
                                    <?php echo $row_org['ORGANIZACION']; ?>
                                </option>
<?php
                            }
?>
                    </td>
                    <td>
                        <label for="selflota">Flota: </label>
                        <select name='flota' id="selflota" onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($flota == "00") || ($flota == "")) echo ' selected'; ?>>Seleccionar</option>
<?php
                            $sql_select = "SELECT * FROM flotas WHERE 1";
                            if (($organiza != "00") && ($organiza != "")){
                                $sql_select .= " AND (ORGANIZACION = ". $organiza . ")";
                            }
                            $sql_select .= " ORDER BY flotas.FLOTA ASC";
                            $res_select = mysql_query($sql_select) or die(mysql_error());
                            $nselect = mysql_num_rows($res_select);
                            for ($i = 0; $i < $nselect; $i++) {
                                $row_flota = mysql_fetch_array($res_select);
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
            // Consulta a la tabla de Flotas
            $sql_flotas = "SELECT ID, FLOTA, ACRONIMO, ORGANIZACION, ENCRIPTACION FROM flotas WHERE 1";
            if ($tam_pagina == "") {
                $tam_pagina = 10;
            }
            if (!$pagina) {
                $inicio = 0;
                $pagina = 1;
            }
            else {
                $inicio = ($pagina - 1) * $tam_pagina;
            }
            if (($flota != '') && ($flota != "00")) {
                $sql_flotas = $sql_flotas . " AND (flotas.ID='$flota')";
            }
            if (($organiza != '') && ($organiza != "00")) {
                $sql_flotas = $sql_flotas . " AND (flotas.ORGANIZACION='$organiza')";
            }
            $sql_no_limit = $sql_flotas . " ORDER BY flotas.FLOTA ASC";
            $sql_limit = $sql_no_limit . " LIMIT " . $inicio . "," . $tam_pagina . ";";
            $res = mysql_query($sql_no_limit) or die(mysql_error());
            $nfilas = mysql_num_rows($res);
            $total_pag = ceil($nfilas / $tam_pagina);
            ########### Enlaces para la exportación #######
            $linkpdf = "document.flotasexp.formato.value='pdf';document.flotasexp.submit();";
            $linkxls = "document.flotasexp.formato.value='xls';document.flotasexp.submit();";
?>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nfilas; ?></b>.</td>
                    <td class="borde">
    			Mostrar:
                        <select name='tam_pagina' onChange='document.formulario.submit();'>
                            <option value='10' <?php if (($tam_pagina == "10") || ($tam_pagina == "")) {echo 'selected';}?>>10</option>
                            <option value='20' <?php if ($tam_pagina == "20") {echo 'selected';}?>>20</option>
                            <option value='30' <?php if ($tam_pagina == "30") {echo 'selected';}?>>30</option>
                            <option value='<?php echo $nfilas;?>' <?php if ($tam_pagina == $nfilas) {echo 'selected';}?>>Todas</option>
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
?>
                    <td class="borde">
                        <a href="nueva_flota.php"><img src="imagenes/nueva.png" alt="<?php echo $newflota; ?>"></a> &mdash; <?php echo $newflota; ?>
                    </td>
<?php
                    if ($nfilas > 0) {
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
        <form name="formdet" action="detalle_flota.php" method="POST" target="_blank">
            <input type="hidden" name="idflota" value="#">
        </form>
        <form name="formupd" action="update_flota.php" method="POST">
            <input type="hidden" name="origen" value="rename">
        </form>
        <form name="flotasexp" action="flotasexp.php" method="POST" target="_blank">
            <input type="hidden" name="flota" value="<?php echo $flota; ?>">
            <input type="hidden" name="activa" value="<?php echo $activa; ?>">
            <input type="hidden" name="formato" value="#">
        </form>
        <table>
<?php
            if ($nfilas == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
            }
            else {
                $res_flotas = mysql_query($sql_limit) or die("Error en la Consulta de Flotas: " . mysql_error());
                $nflotas = mysql_num_rows($res_flotas);
                $ncampos = mysql_num_fields($res_flotas);
                $tterm = 0;
                //*TABLA CON RESULTADOS*//
?>
                <tr>
<?php
                //* CABECERA  *//
                for ($i = 0; $i < count($campos); $i++) {
?>
                    <th><?php echo $campos[$i]; ?></th>
<?php
                }
?>
                </tr>
<?php
                for ($i = 0; $i < $nflotas; $i++) {
                    $row_flota = mysql_fetch_array($res_flotas);
                    $linkdet = "document.formdet.idflota.value='$row_flota[0]';document.formdet.submit();";
?>
                <tr <?php if (($i % 2) == 1) {echo " class='filapar'";}?>>
                    <td class='centro'>
                        <a href='#' onclick="<?php echo $linkdet; ?>">
                            <img src='imagenes/consulta.png' alt="<?php echo $detalle;?>">
                        </a>
                    </td>
                    <td><?php echo $row_flota['FLOTA']; ?></td>
                    <td><?php echo $row_flota['ACRONIMO']; ?></td>
                    <td><?php echo $row_flota['ENCRIPTACION']; ?></td>
<?php
                // Datos de la Tabla organizaciones:
                $sql_org = "SELECT ID, ORGANIZACION FROM organizaciones WHERE (ID = " . $row_flota['ORGANIZACION'] . ")";
                $res_org = mysql_query($sql_org) or die("Error en la Consulta de Organizaciones: " . mysql_error());
                $norg = mysql_num_rows($res_org);
                if ($norg > 0){
                    $org = mysql_fetch_array($res_org);
                }
                //datos de la tabla Terminales
                $sql_term = "SELECT * FROM terminales WHERE FLOTA='$row_flota[0]'";
                $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminales" . mysql_error());
                $nterm = mysql_num_rows($res_term);
                $tterm += $nterm;
?>
                <td><?php echo $org['ORGANIZACION']; ?></td>
                <td class='centro'><?php echo $nterm; ?></td>
                </tr>
<?php
                } //primer for
?>
                <tr><td colspan='9'>&nbsp;</td></tr>
                <tr class="filapar">
                    <th colspan="5"><?php echo $totales; ?></th>
                    <td class='centro'><?php echo number_format($tterm, 0, ',', '.'); ?></td>
                </tr>
<?php
            }
?>
        </table>
<?php
    } // Si el usuario no es el de la Oficina
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
