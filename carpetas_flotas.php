<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/carpetas_$idioma.php";
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
?>
        <h1>
            <?php echo $h1; ?> &mdash; <a href="carpetas_flotas.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <form action="carpetas.php" name="formulario" method="POST">
            <h4><?php echo $h2crit; ?></h4>
            <table>
                <tr>
                    <td>
                        <select name='flota' onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($flota == "00") || ($flota == "")) echo ' selected'; ?>>Flota</option>
<?php
                            $sql_flotas = "SELECT ID, flotas.FLOTA, ACRONIMO, ENCRIPTACION FROM flotas WHERE 1";
                            $sql_select = $sql_flotas . " ORDER BY flotas.FLOTA ASC";
                            $res_select = mysql_query($sql_select) or die(mysql_error());
                            $nselect = mysql_num_rows($res_select);
                            for ($i = 0; $i < $nselect; $i++) {
                                $row_flota = mysql_fetch_array($res_select);
?>
                                <option value='<?php echo $row_flota['ID']; ?>' <?php if ($flota == $row_flota['ID']) echo ' selected'; ?>>
                                    <?php echo $row_flota['FLOTA']; ?>
                                </option>
<?php
                            }
?>
                        </select>
                    </td>
                </tr>
            </table>
<?php
            if ($tam_pagina == "") {
                $tam_pagina = 25;
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
            $sql_no_limit = $sql_flotas . " ORDER BY flotas.FLOTA ASC";
            $sql_limit = $sql_no_limit . " LIMIT " . $inicio . "," . $tam_pagina . ";";
            $res_flotas = mysql_query($sql_no_limit) or die(mysql_error());
            $nflotastot = mysql_num_rows($res_flotas);
            $total_pag = ceil($nflotastot / $tam_pagina);
            ########### Enlaces para la exportación #######
            $linkpdf = "document.flotasexp.formato.value='pdf';document.flotasexp.submit();";
            $linkxls = "document.flotasexp.formato.value='xls';document.flotasexp.submit();";
?>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nflotastot; ?></b>.</td>
                    <td class="borde">
    			Mostrar:
                        <select name='tam_pagina' onChange='document.formulario.submit();'>
                            <option value='25' <?php if (($tam_pagina == "25") || ($tam_pagina == "")) {echo 'selected';}?>>25</option>
                            <option value='50' <?php if ($tam_pagina == "50") {echo 'selected';}?>>50</option>
                            <option value='100' <?php if ($tam_pagina == "100") {echo 'selected';}?>>100</option>
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
                    if ($nflotastot > 0) {
?>
                        <td class="borde">
                            <a href="#" onclick="<?php echo $linkxls; ?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
                        </td>
<?php
                    }
?>
                </tr>
            </table>
        </form>
        <form name="formdet" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="#">
        </form>
        <form name="flotasexp" action="xlscarpetas.php" method="POST" target="_blank">
            <input type="hidden" name="flota" value="<?php echo $flota; ?>">
            <input type="hidden" name="activa" value="<?php echo $activa; ?>">
            <input type="hidden" name="formato" value="#">
        </form>
        <table>
<?php
            if ($nflotastot == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
            }
            else {
                $res_flotas = mysql_query($sql_limit) or die("Error en la Consulta de Flotas: " . mysql_error());
                $nflotas = mysql_num_rows($res_flotas);
                $ncampos = mysql_num_fields($res_flotas);
                $tterm = array(0, 0, 0, 0);
                //*TABLA CON RESULTADOS*//
?>
                <tr>
                    <th><?php echo $thdetalle; ?></th>
                    <th><?php echo $thflota; ?></th>
                    <th><?php echo $thacro; ?></th>                    
                    <th><?php echo $thcarpetas; ?></th>
            </tr>
<?php
                for ($i = 0; $i < $nflotas; $i++) {
                    $flota = mysql_fetch_array($res_flotas);
                    $linkdet = "document.formdet.idflota.value='".$flota['ID']."';document.formdet.submit();";
?>
                    <tr <?php if (($i % 2) == 1) {echo " class='filapar'";}?>>
                        <td class='centro'><a href='#' onclick="<?php echo $linkdet; ?>">
                            <img src='imagenes/consulta.png' alt="<?php echo $detalle;?>"></a>
                        </td>
                        <td><?php echo $flota['FLOTA']; ?></td>
                        <td><?php echo $flota['ACRONIMO']; ?></td>
<?php
                        //datos de la tabla Terminales
                        // Carpetas
                        $sql_carpetas = "SELECT DISTINCT CARPETA FROM terminales WHERE (FLOTA = ".$flota['ID'].")";
                        $sql_carpetas .= " ORDER BY CARPETA ASC";
                        $res_carpetas = mysql_query($sql_carpetas) or die("Error en la consulta de Carpetas" . mysql_error());
                        $ncarpetas = mysql_num_rows($res_carpetas);
?>
                        <td><?php echo $ncarpetas; ?></td>
                </tr>
<?php
                } //primer for
?>               
        </table>
<?php
            }
    } // Si el usuario no es el de la Oficina
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $errnoperm ?></p>
<?php
    }
?>
    </body>
</html>
