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
            <?php echo $h1; ?> &mdash; <a href="carpetas.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <form action="carpetas.php" name="formulario" method="POST">
            <h4><?php echo $h2crit; ?></h4>
            <table>
                <tr>
                    <td>
                        <select name='carpeta' onChange='document.formulario.submit();'>
                            <option value='NN' <?php if (($carpeta == "00") || ($carpeta == "")) echo ' selected'; ?>>
                                Seleccionar Carpeta
                            </option>
<?php
                            $sql_carptot = "SELECT DISTINCT CARPETA FROM terminales ORDER BY CARPETA ASC";
                            $res_carptot = mysql_query($sql_carptot) or die(mysql_error());
                            $ncarptot = mysql_num_rows($res_carptot);
                            for ($i = 0; $i < $ncarptot; $i++) {
                                $carptot = mysql_fetch_array($res_carptot);
?>
                                <option value='<?php echo $carptot['CARPETA']; ?>' <?php if ($carpeta == $carptot['CARPETA']) echo ' selected'; ?>>
                                    <?php echo $carptot['CARPETA']; ?>
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
            $sql_carpetas = "SELECT DISTINCT CARPETA FROM terminales WHERE 1";
            if (($carpeta != '') && ($carpeta != "NN")) {
                $sql_carpetas .= " AND (terminales.CARPETA='$carpeta')";
            }
            $sql_carpetas .= " ORDER BY terminales.CARPETA ASC";
            $res_carptot = mysql_query($sql_carpetas) or die("Error en la consulta total de carpetas: " . mysql_error());
            $ncarptot = mysql_num_rows($res_carptot);
            $total_pag = ceil($nflotastot / $tam_pagina);
            $sql_carpetas .= " LIMIT " . $inicio . "," . $tam_pagina . ";";
            $res_carpetas = mysql_query($sql_carpetas) or die("Error en la consulta de carpetas: " . mysql_error());
            $ncarpetas = mysql_num_rows($res_carpetas);
            ########### Enlaces para la exportación #######
            $linkxls = "document.flotasexp.formato.value='xls';document.flotasexp.submit();";
?>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $ncarptot; ?></b>.</td>
                    <td class="borde">
    			Mostrar:
                        <select name='tam_pagina' onChange='document.formulario.submit();'>
                            <option value='25' <?php if (($tam_pagina == "25") || ($tam_pagina == "")) {echo 'selected';}?>>25</option>
                            <option value='50' <?php if ($tam_pagina == "50") {echo 'selected';}?>>50</option>
                            <option value='100' <?php if ($tam_pagina == "100") {echo 'selected';}?>>100</option>
                            <option value='<?php echo $ncarptot;?>' <?php if ($tam_pagina == $ncarptot) {echo 'selected';}?>>Todas</option>
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
                </tr>
            </table>
        </form>
        <form name="formdet" action="detalle_carpeta.php" method="POST">
            <input type="hidden" name="carpeta" value="#">
        </form>
        <form name="formedi" action="editar_carpeta.php" method="POST">
            <input type="hidden" name="carpeta" value="#">
        </form>
        <form name="flotasexp" action="xlscarpetas.php" method="POST" target="_blank">
            <input type="hidden" name="flota" value="<?php echo $flota; ?>">
            <input type="hidden" name="activa" value="<?php echo $activa; ?>">
            <input type="hidden" name="formato" value="#">
        </form>
        <table>
<?php
            if ($ncarptot == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
            }
            else {
?>
                <tr>
                    <th><?php echo $thacciones; ?></th>
                    <th><?php echo $thcarpeta; ?></th>
                    <th><?php echo $thflotas; ?></th>
            </tr>
<?php
                for ($i = 0; $i < $ncarpetas; $i++) {
                    $carpeta = mysql_fetch_array($res_carpetas);
                    $sql_flotas = "SELECT DISTINCT FLOTA FROM terminales WHERE (CARPETA = '" . $carpeta['CARPETA'] . "')";
                    $res_flotas = mysql_query($sql_flotas) or die("Error en la consulta de flotas: " . mysql_error());
                    $nflotas = mysql_num_rows($res_flotas);
                    $linkdet = "document.formdet.carpeta.value='".$carpeta['CARPETA']."';document.formdet.submit();";                    
                    $linkedi = "document.formedi.carpeta.value='".$carpeta['CARPETA']."';document.formedi.submit();";
?>
                    <tr <?php if (($i % 2) == 1) {echo " class='filapar'";}?>>
                        <td class='centro'>
                            <a href='#' onclick="<?php echo $linkdet; ?>">
                                <img src='imagenes/consulta.png' alt="<?php echo $botdetalle;?>" title="<?php echo $botdetalle;?>"></a>
                            &mdash;
                            <a href='#' onclick="<?php echo $linkedi; ?>">
                                <img src='imagenes/editar.png' alt="<?php echo $boteditar;?>" title="<?php echo $boteditar;?>">
                            </a>
                        </td>
                        <td><?php echo $carpeta['CARPETA']; ?></td>
                        <td><?php echo $nflotas; ?></td>
<?php
                        //datos de la tabla Terminales
                        
?>
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
