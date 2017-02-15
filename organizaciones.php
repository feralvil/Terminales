<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organiza_$idioma.php";
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
            <?php echo $h1; ?> &mdash; <a href="organizaciones.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <form action="organizaciones.php" name="formulario" method="POST">
            <h4><?php echo $h2crit; ?></h4>
            <table>
                <tr>
                    <td>
                        <select name='orgsel' onChange='document.formulario.submit();'>
                            <option value='NN' <?php if (($carpeta == "00") || ($carpeta == "")) echo ' selected'; ?>>
                                <?php echo $txtselorg; ?>
                            </option>
                            <?php
                            $sql_orgsel = "SELECT ID, ORGANIZACION FROM organizaciones ORDER BY ORGANIZACION ASC";
                            $res_orgsel = mysql_query($sql_orgsel) or die("Error en la Consulta del Selector de Organizaciones: " . mysql_error());
                            $norgsel = mysql_num_rows($res_orgsel);
                            for ($i = 0; $i < $norgsel; $i++) {
                                $orgselect = mysql_fetch_array($res_orgsel);
                            ?>
                                <option value='<?php echo $orgselect['ID']; ?>' <?php if ($orgsel == $orgselect['ID']) echo ' selected'; ?>>
                                    <?php echo $orgselect['ORGANIZACION']; ?>
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
            $sql_organiza = "SELECT ID, ORGANIZACION FROM organizaciones WHERE 1";
            if (($orgsel != '') && ($orgsel != "NN")) {
                $sql_organiza .= " AND (organizaciones.ID ='$orgsel')";
            }
            $sql_organiza .= " ORDER BY ORGANIZACION ASC";
            $res_orgtot = mysql_query($sql_organiza) or die("Error en la consulta total de Organizaciones: " . mysql_error());
            $norgtot = mysql_num_rows($res_orgtot);
            $total_pag = ceil($norgtot / $tam_pagina);
            $sql_organiza .= " LIMIT " . $inicio . "," . $tam_pagina . ";";
            $res_organiza = mysql_query($sql_organiza) or die("Error en la consulta de Organizaciones: " . mysql_error());
            $norganiza = mysql_num_rows($res_organiza);
            ########### Enlaces para la exportación #######
            $linkxls = "document.flotasexp.formato.value='xls';document.flotasexp.submit();";
?>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $norgtot; ?></b>.</td>
                    <td class="borde">
                        Mostrar:
                        <select name='tam_pagina' onChange='document.formulario.submit();'>
                            <option value='25' <?php if (($tam_pagina == "25") || ($tam_pagina == "")) {echo 'selected';}?>>25</option>
                            <option value='50' <?php if ($tam_pagina == "50") {echo 'selected';}?>>50</option>
                            <option value='100' <?php if ($tam_pagina == "100") {echo 'selected';}?>>100</option>
                            <option value='<?php echo $norgtot;?>' <?php if ($tam_pagina == $norgtot) {echo 'selected';}?>>
                                <?php echo $totreg; ?>
                            </option>
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
                        <a href="nueva_organiza.php"><img src="imagenes/nueva.png" alt="<?php echo $neworg; ?>" title="<?php echo $neworg; ?>"></a>
                        <?php
                        if ($norganiza > 0){
                        ?>
                            &mdash;
                            <a href="xlsorganiza.php" target="_blank"><img src="imagenes/xls.png" alt="Exportar a Excel" title="Exportar a Excel"></a>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </form>
        <form name="formdet" action="detalle_organizacion.php" method="POST">
            <input type="hidden" name="idorg" value="#">
        </form>
        <form name="formedi" action="editar_organiza.php" method="POST">
            <input type="hidden" name="idorg" value="#">
        </form>
        <form name="formflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="#">
        </form>
        <form name="orgexport" action="xlsorganiza.php" method="POST" target="_blank">
            <input type="hidden" name="flota" value="<?php echo $flota; ?>">
            <input type="hidden" name="activa" value="<?php echo $activa; ?>">
            <input type="hidden" name="formato" value="#">
        </form>
        <table>
<?php
            if ($norganiza == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
            }
            else {
?>
                <tr>
                    <th><?php echo $thacciones; ?></th>
                    <th><?php echo $thorganiza; ?></th>
                    <th><?php echo $thflotas; ?></th>
            </tr>
<?php
                for ($i = 0; $i < $norganiza; $i++) {
                    $organiza = mysql_fetch_array($res_organiza);
                    $sql_flotas = "SELECT * FROM flotas WHERE (ORGANIZACION = '" . $organiza['ID'] . "')";
                    $res_flotas = mysql_query($sql_flotas) or die("Error en la consulta de flotas: " . mysql_error());
                    $nflotas = mysql_num_rows($res_flotas);
                    $linkdet = "document.formdet.idorg.value='".$organiza['ID']."';document.formdet.submit();";
                    $linkedi = "document.formedi.idorg.value='".$organiza['ID']."';document.formedi.submit();";
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
                        <td><?php echo $organiza['ORGANIZACION']; ?></td>
                        <td>
                            <ol>
                            <?php
                            for ($j = 0; $j < $nflotas; $j++){
                                $flota = mysql_fetch_array($res_flotas);
                                $linkdetf = "document.formflota.idflota.value='".$flota['ID']."';document.formflota.submit();";
                            ?>
                                <li>
                                    <?php echo  $flota['FLOTA']; ?> &mdash;
                                    <a href="#" onclick="<?php echo $linkdetf; ?>">
                                        <img src="imagenes/ir.png" alt="<?php echo $botirflota; ?>" title="<?php echo $botirflota; ?>">
                                    </a>
                                </li>
                            <?php
                            }
                            ?>
                            </ol>
                        </td>
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
