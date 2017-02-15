<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotadet_$idioma.php";
include ($lang);

// ------------------------------------------------------------------------------------- //
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

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
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
else {    
    if ($idflota > 0){
        if ($flota_usu == $idflota) {
            $permiso = 1;
        }
    }
    else{
        $permiso = 1;
        $idflota = $flota_usu;   
    }   
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
<?php
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
<?php
if ($permiso > 0) {
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $usuario = $row_flota["LOGIN"];
    }
    //datos de la tabla Terminales
    // Tipos de termninales
    $tipos = array("F", "M%", "MB", "MA", "MG", "P%", "PB", "PA", "PX", "D");
    $nterm = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
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
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
    }
    //datos de las tablas buzons y flotas_buzons:
    $sql_buzflota = "SELECT flotas_buzons.ID, flotas_buzons.ROL, buzons.NOMBRE FROM flotas_buzons, buzons ";
    $sql_buzflota .= "WHERE (flotas_buzons.FLOTA_ID = $idflota) AND (flotas_buzons.BUZON_ID = buzons.ID)";
    $res_buzflota = mysql_query($sql_buzflota) or die("Error en la consulta de Buzones" . mysql_error());
    $nbuzflota = mysql_num_rows($res_buzflota);
    $buzprop = array();
    $buzasoc = array();
    for ($i = 0; $i < $nbuzflota; $i++) {
        $row_buzflota = mysql_fetch_array($res_buzflota);
        if ($row_buzflota['ROL'] == 'P'){
            array_push($buzprop, $row_buzflota);
        }
        elseif ($row_buzflota['ROL'] == 'A'){
            array_push($buzasoc, $row_buzflota);
        }
    }
    

    ############# Enlaces para la exportación #######
    $linkpdf = "document.exportar.action='pdfflota.php';document.exportar.submit();";
    $linkxls = "document.exportar.action='xlsflota.php';document.exportar.submit();";
    
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
        <form name="exportar" action="#" method="POST" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
        </form>
        <form name="detalleflota" method="POST" action="detalle_flota.php" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
            <h1>
                Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>) &mdash;
                <a href="#" onclick="<?php echo $linkpdf; ?>"><img src="imagenes/pdf.png" alt="PDF" title="PDF"></a> &mdash;
                <a href="#" onclick="<?php echo $linkxls; ?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a> &mdash;
                <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $newtab;?>' title="<?php echo $newtab;?>">
            </h1>
        </form>
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
        <h2><?php echo $h2localiza; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $domicilio; ?></th>
                <th class="t10c"><?php echo $cp; ?></th>
                <th class="t30"><?php echo $ciudad; ?></th>
                <th class="t5c"><?php echo $provincia; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["DOMICILIO"]; ?></td>
                <td><?php echo $row_flota["CP"]; ?></td>
                <td><?php echo $row_mun["MUNICIPIO"]; ?></td>
                <td><?php echo $row_mun["PROVINCIA"]; ?></td>
            </tr>
        </table>
        <h2><?php echo $h3flota; ?></h2>
<?php
        if (($row_flota["RESPONSABLE"] == "0") && ($row_flota["CONTACTO1"] == "0") && ($row_flota["CONTACTO2"] == "0") && ($row_flota["CONTACTO3"] == "0")) {
?>
            <p class='error'><?php echo $nocont; ?></p>
<?php
        }
        else {
?>
            <table>
                <tr>
                    <td class="t10c">&nbsp;</td>
                    <th class="t4c"><?php echo $nomflota; ?></th>
                    <th class="t4c"><?php echo $cargo; ?></th>
                    <th class="t10c"><?php echo $telefono; ?></th>
                    <th class="t10c"><?php echo $movil; ?></th>
                    <th class="t5c"><?php echo $mail; ?></th>
                </tr>

<?php
            // Datos de contactos
            $id_contacto = array($row_flota["RESPONSABLE"], $row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
            $nom_contacto = array("Responsable", $contacto . " 1", $contacto . " 2", $contacto . " 3");
            $par = 0;
            // Datos de contactos
            for ($i = 0; $i < count($id_contacto); $i++) {
                if ($id_contacto[$i] != 0) {
                    $idc = $id_contacto[$i];
                    $sql_contacto = "SELECT * FROM contactos WHERE ID=$idc";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto != 0) {
                        $row_contacto = mysql_fetch_array($res_contacto);
?>
                        <tr <?php if (($par % 2) == 1) echo "class='filapar'"; ?>>
                            <th><?php echo $nom_contacto[$i]; ?></th>
                            <td><?php echo $row_contacto["NOMBRE"]; ?></td>
                            <td><?php echo $row_contacto["CARGO"]; ?></td>
                            <td><?php echo $row_contacto["TELEFONO"]; ?></td>
                            <td><?php echo $row_contacto["MOVIL"]; ?></td>
                            <td><?php echo $row_contacto["MAIL"]; ?></td>
                    </tr>
<?php
                        $par++;
                    }
                }
            }
?>
        </table>
            <?php
        }
            ?>
        <h3><?php echo $h3incid; ?></h3>
            <?php
            if (($row_flota["INCID1"] == "0") && ($row_flota["INCID2"] == "0") && ($row_flota["INCID2"] == "0") && ($row_flota["INCID3"] == "0")) {
            ?>
                <p class='error'><?php echo $noincid; ?></p>
        <?php
            }
            else {
        ?>
                <table>
                    <tr>
                        <td class="t10c">&nbsp;</td>
                        <th class="t4c"><?php echo $nomflota; ?></th>
                        <th class="t4c"><?php echo $cargo; ?></th>
                        <th class="t10c"><?php echo $telefono; ?></th>
                        <th class="t10c"><?php echo $movil; ?></th>
                        <th class="t5c"><?php echo $horario; ?></th>
                    </tr>

<?php
                // Datos de contactos
                $id_contacto = array($row_flota["INCID1"], $row_flota["INCID2"], $row_flota["INCID3"], $row_flota["INCID4"]);
                $nom_contacto = array($contacto . " 1", $contacto . " 2", $contacto . " 3", $contacto . " 4");
                $par = 0;
                // Datos de contactos
                for ($i = 0; $i < count($id_contacto); $i++) {
                    if ($id_contacto[$i] != 0) {
                        $idc = $id_contacto[$i];
                        $sql_contacto = "SELECT * FROM contactos WHERE ID=$idc";
                        $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
                        $ncontacto = mysql_num_rows($res_contacto);
                        if ($ncontacto != 0) {
                            $row_contacto = mysql_fetch_array($res_contacto);
?>
                            <tr <?php if (($par % 2) == 1) echo "class='filapar'"; ?>>
                                <th><?php echo $nom_contacto[$i]; ?></th>
                                <td><?php echo $row_contacto["NOMBRE"]; ?></td>
                                <td><?php echo $row_contacto["CARGO"]; ?></td>
                                <td><?php echo $row_contacto["TELEFONO"]; ?></td>
                                <td><?php echo $row_contacto["MOVIL"]; ?></td>
                                <td><?php echo $row_contacto["HORARIO"]; ?></td>
                            </tr>
<?php
                            $par++;
                        }
                    }
                }
?>
        </table>
<?php
            }
?>
        <h2>
        		<?php echo $h2term; ?> &mdash;
        		<a href='#' onclick="document.termflota.submit();"><img src='imagenes/ir.png' alt="<?php echo $terminales; ?>" title='<?php echo $terminales; ?>'></a>         
        </h2>
        <?php 
        if ($row_flota["RANGO"] != ""){
        ?>
            <p><strong><?php echo $h3rango; ?>:</strong> <?php echo $row_flota["RANGO"]; ?></p>
        <?php 
        }
        ?>
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
        <h2><?php echo $h2dots; ?></h2>        
        <h3><?php echo $h3dotsprop; ?></h3>
        <?php
        if (count($buzprop) > 0){
        ?>
            <ul>
                <?php
                foreach ($buzprop as $buzon){
                ?>
                    <li><?php echo $buzon['NOMBRE']; ?></li>
                <?php
                }
                ?>
            </ul>
        <?php
        }
        else{
        ?>
            <p class="error"><?php echo $errnoprop; ?></p>
        <?php
        }
        ?>
        <h3><?php echo $h3dotsasoc; ?></h3>
        <?php
        if (count($buzasoc) > 0){
        ?>
            <ul>
                <?php
                foreach ($buzasoc as $buzon){
                ?>
                    <li><?php echo $buzon['NOMBRE']; ?></li>
                <?php
                }
                ?>
            </ul>
        <?php
        }
        else{
        ?>
            <p class="error"><?php echo $errnoasoc; ?></p>
        <?php
        }
        ?>
        <table>
            <tr>
                <td class="borde">
                    <a href='#' onclick="document.modflota.action='acceso_flota.php';document.modflota.submit();">
                        <img src='imagenes/key.png' alt='<?php echo $acceso; ?>' title='<?php echo $acceso; ?>'>
                    </a><br><?php echo $acceso; ?>
                </td>
<?php
                if ($permiso == 2) {
?>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='editar_flota.php';document.modflota.submit();">
                            <img src='imagenes/pencil.png' alt='<?php echo $editflota; ?>' title='<?php echo $editflota; ?>'>
                        </a><br><?php echo $editflota; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='contacto_flota.php';document.modflota.submit();">
                            <img src='imagenes/editacont.png' alt='<?php echo $editcont; ?>' title='<?php echo $editcont; ?>'>
                        </a><br><?php echo $editcont; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='contacto_incid.php';document.modflota.submit();">
                            <img src='imagenes/contincid.png' alt='<?php echo $h3incid; ?>' title='<?php echo $h3incid; ?>'>
                        </a><br><?php echo $h3incid; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='akdc_flota.php';document.modflota.submit();">
                            <img src='imagenes/akdc.png' alt='AKDC' title='AKDC'>
                        </a><br>Generar AKDC
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='excel_flota.php';document.modflota.submit();">
                            <img src='imagenes/impexcel.png' alt='Importar Excel' title="Importar Excel">
                        </a><br><?php echo $datexcel; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='base_flota.php';document.modflota.submit();">
                            <img src='imagenes/base_add.png' alt='<?php echo $addbase; ?>' title="<?php echo $addbase; ?>">
                        </a><br><?php echo $addbase; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='aut_flota.php';document.modflota.submit();">
                            <img src='imagenes/autterm.png' alt='<?php echo $autterm; ?>' title='<?php echo $autterm; ?>'>
                        </a><br><?php echo $autterm; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='dots_flota.php';document.modflota.submit();">
                            <img src='imagenes/dots.png' alt='<?php echo $dots; ?>' title='<?php echo $dots; ?>'>
                        </a><br><?php echo $dots; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.modflota.action='buzones_flota.php';document.modflota.submit();">
                            <img src='imagenes/clientes.png' alt='<?php echo $buzones; ?>' title='<?php echo $buzones; ?>'>
                        </a><br><?php echo $buzones; ?>
                    </td>
<?php
                }
?>
                </tr>
            </table>
                <form name="modflota" action="#" method="POST">
                    <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
                </form>
                <form name="formdet" action="detalle_flota.php" method="POST">
                    <input type="hidden" name="idflota" value="#">
                </form>
                <form name="termflota" action="terminales.php" method="POST">
                    <input type="hidden" name="flota" value="<?php echo $idflota ?>">
                </form>
                <form name="gpsflota" action="gpsflota.php" method="POST">
                    <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
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