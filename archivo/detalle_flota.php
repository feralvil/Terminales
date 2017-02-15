<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotadet_$idioma.php";
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
    if ($idflota == 0){
        $idflota = $flota_usu;
    }
}
else {
    $permiso = 1;
    $idflota = $flota_usu;
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
<?php
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/detalle_flota.js"></script>
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
    //datos de la tabla Organizaciones:
    $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $row_flota['ORGANIZACION'];
    $res_org = mysql_query($sql_org) or die("Error en la consulta de Organización: " . mysql_error());
    $norg = mysql_num_rows($res_org);
    if ($norg > 0) {
        $row_org = mysql_fetch_array($res_org);
        $ineorg = $row_org['INE'];
        $sql_munorg = "SELECT * FROM municipios WHERE INE = '$ineorg'";
        $res_munorg = mysql_query($sql_munorg) or die("Error en la consulta de Municipio de la Organización" . mysql_error());
        $nmunorg = mysql_num_rows($res_munorg);
        if ($nmunorg > 0) {
            $row_munorg = mysql_fetch_array($res_munorg);
        }
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

    // Datos de contactos
    $sql_contactos = "SELECT * FROM contactos_flotas WHERE FLOTA_ID = $idflota";
    $res_contactos = mysql_query($sql_contactos) or die("Error en la consulta de contacto: " . mysql_error());
    $ncontactos = mysql_num_rows($res_contactos);
    $responsable = 0;
    $operativos = array();
    $tecnicos = array();
    $cont24h = array();
    if ($ncontactos > 0){
        for ($i = 0; $i < $ncontactos; $i++){
            $row_cont = mysql_fetch_array($res_contactos);
            switch ($row_cont['ROL']){
                case 'RESPONSABLE':{
                    $responsable = $row_cont['CONTACTO_ID'];
                    break;
                }
                case 'OPERATIVO':{
                    array_push($operativos, $row_cont['CONTACTO_ID']);
                    break;
                }
                case 'TECNICO':{
                    array_push($tecnicos, $row_cont['CONTACTO_ID']);
                    break;
                }
                case 'CONT24H':{
                    array_push($cont24h, $row_cont['CONTACTO_ID']);
                    break;
                }
            }
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
        <div id="contenido">
            <div id="pestanyas">
                <ul id="tab">
                    <li>
                        <a href="#" id="linkhome" class="activo"><?php echo $tabhome;?></a>
                    </li>
                    <li><a href="#" id="linkcont"><?php echo $tabcont;?></a></li>
                    <li><a href="#" id="linkterm"><?php echo $tabterm;?></a></li>
                </ul>
            </div>
            <div id="limpia"></div>
            <div id="inicio">
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
                <h2><?php echo $h2organiza; ?></h2>
                <?php
                if ($norg > 0){
                ?>
                    <form name="detorg" action="detalle_organizacion.php" method="POST">
                        <input type="hidden" name="idorg" value="<?php echo $row_org['ID'];?>">
                    </form>
                    <table>
                        <tr>
                            <th><?php echo $thorg; ?></th>
                            <th><?php echo $ciudad; ?></th>
                            <th><?php echo $provincia; ?></th>
                            <th><?php echo $thirorg; ?></th>
                        </tr>
                        <tr>
                            <td><?php echo $row_org['ORGANIZACION']; ?></td>
                            <td><?php echo $row_munorg["MUNICIPIO"]; ?></td>
                            <td><?php echo $row_munorg["PROVINCIA"]; ?></td>
                            <td class="centro">
                                <a href="#" onclick="document.detorg.submit();">
                                    <img src="imagenes/ir.png" alt="<?php echo $thirorg; ?>" title="<?php echo $thirorg; ?>">
                                </a>
                            </td>
                        </tr>
                    </table>
                <?php
                }
                else{
                ?>
                    <p class='error'><?php echo $errnoorg; ?></p>
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
                        <td class="borde">
                            <a href='#' onclick="document.modflota.action='grupos_flota.php';document.modflota.submit();">
                                <img src='imagenes/grupos.png' alt='<?php echo $grupflota; ?>' title='<?php echo $grupflota; ?>'>
                            </a><br><?php echo $grupflota; ?>
                        </td>
                        <td class="borde">
                            <a href='#' onclick="document.modflota.action='permisos_flota.php';document.modflota.submit();">
                                <img src='imagenes/permisos.png' alt='Permisos de Flota' title='Permisos de Flota'>
                            </a><br>Permisos de Flota
                        </td>
                        <?php
                        if ($permiso > 1){
                        ?>
                            <td class="borde">
                                <a href='#' onclick="document.modflota.action='editar_flota.php';document.modflota.submit();">
                                    <img src='imagenes/pencil.png' alt='<?php echo $editflota; ?>' title='<?php echo $editflota; ?>'>
                                </a><br><?php echo $editflota; ?>
                            </td>
                            <td class="borde">
                                <a href='#' onclick="document.modflota.action='excel_flota.php';document.modflota.submit();">
                                    <img src='imagenes/impexcel.png' alt='Importar Excel' title="Importar Excel">
                                </a><br><?php echo $datexcel; ?>
                            </td>
                        <?php
                        }
                        ?>
                    </tr>
                </table>
            </div>
            <div id="contactos">
                <h2><?php echo $h2cont; ?></h2>
                <?php
                if ($ncontactos == 0){
                ?>
                    <p class='error'><?php echo $errnocont; ?></p>
                <?php
                }
                else {
                    if ($row_flota['FORMCONT'] == 'NO'){
                        $clase = "flashko";
                        $imagen = "imagenes/cancelar.png";
                        $alt = "Error";
                        $textoflash = $contnoupd;
                    }
                    else{
                        $clase = "flashok";
                        $imagen = "imagenes/okm.png";
                        $alt = "OK";
                        $fechacomp = explode('-', $row_flota['UPDCONT']);
                        $textoflash = $contupd . " " . $fechacomp[2] . '/' . $fechacomp[1] . '/' . $fechacomp[0];
                    }
                ?>
                    <p class="<?php echo $clase;?>">
                        <img src="<?php echo $imagen;?>" alt="<?php echo $alt;?>" title="<?php echo $alt;?>"> &mdash; <?php echo $textoflash;?>
                    </p>
                    <h3><?php echo $h3resp; ?></h3>
                    <?php
                    if ($responsable > 0){
                        $sql_contacto = "SELECT * FROM contactos WHERE ID = $responsable";
                        $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de responsable: " . mysql_error());
                        $ncontacto = mysql_num_rows($res_contacto);
                        if ($ncontacto > 0){
                            $contacto = mysql_fetch_array($res_contacto);
                        }

                    ?>
                        <table>
                            <tr>
                                <th class="t4c"><?php echo $nomflota; ?></th>
                                <th class="t10c">DNI</th>
                                <th class="t4c"><?php echo $cargo; ?></th>
                                <th class="t5c"><?php echo $mail; ?></th>
                                <th class="t5c"><?php echo $telefono; ?></th>
                            </tr>
                            <tr>
                                <td><?php echo $contacto['NOMBRE']; ?></td>
                                <td><?php echo $contacto["NIF"]; ?></td>
                                <td><?php echo $contacto["CARGO"]; ?></td>
                                <td><?php echo $contacto["MAIL"]; ?></td>
                                <td><?php echo $contacto["TELEFONO"]; ?></td>
                            </tr>
                        </table>
                    <?php
                    }
                    else{
                    ?>
                        <p class="error"><?php echo $errnoresp; ?></p>
                    <?php
                    }
                    ?>
                    <h3><?php echo $h3operativo; ?></h3>
                    <?php
                    if (count($operativos) > 0){
                    ?>
                        <table>
                            <tr>
                                <th class="t4c"><?php echo $nomflota; ?></th>
                                <th class="t10c">DNI</th>
                                <th class="t4c"><?php echo $cargo; ?></th>
                                <th class="t5c"><?php echo $mail; ?></th>
                                <th class="t5c"><?php echo $telefono; ?></th>
                            </tr>
                            <?php
                            $i = 0;
                            foreach ($operativos as $operativo){
                                $sql_contacto = "SELECT * FROM contactos WHERE ID = $operativo";
                                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de operativos: " . mysql_error());
                                $ncontacto = mysql_num_rows($res_contacto);
                                if ($ncontacto > 0){
                                    $contacto = mysql_fetch_array($res_contacto);
                                }
                            ?>
                                <tr <?php if (($i % 2) == 1) {echo "class = 'filapar'";} ?>>
                                    <td><?php echo $contacto['NOMBRE']; ?></td>
                                    <td><?php echo $contacto["NIF"]; ?></td>
                                    <td><?php echo $contacto["CARGO"]; ?></td>
                                    <td><?php echo $contacto["MAIL"]; ?></td>
                                    <td><?php echo $contacto["TELEFONO"]; ?></td>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                        </table>
                    <?php
                    }
                    else{
                    ?>
                        <p class="error"><?php echo $errnoop; ?></p>

                    <?php
                    }
                    ?>
                    <h3><?php echo $h3tecnico; ?></h3>
                    <?php
                    if (count($tecnicos) > 0){
                    ?>
                        <table>
                            <tr>
                                <th class="t4c"><?php echo $nomflota; ?></th>
                                <th class="t10c">DNI</th>
                                <th class="t4c"><?php echo $cargo; ?></th>
                                <th class="t5c"><?php echo $mail; ?></th>
                                <th class="t5c"><?php echo $telefono; ?></th>
                            </tr>
                            <?php
                            $i = 0;
                            foreach ($tecnicos as $tecnico){
                                $sql_contacto = "SELECT * FROM contactos WHERE ID = $tecnico";
                                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de técnicos: " . mysql_error());
                                $ncontacto = mysql_num_rows($res_contacto);
                                if ($ncontacto > 0){
                                    $contacto = mysql_fetch_array($res_contacto);
                                }
                            ?>
                                <tr <?php if (($i % 2) == 1) {echo "class = 'filapar'";} ?>>
                                    <td><?php echo $contacto['NOMBRE']; ?></td>
                                    <td><?php echo $contacto["NIF"]; ?></td>
                                    <td><?php echo $contacto["CARGO"]; ?></td>
                                    <td><?php echo $contacto["MAIL"]; ?></td>
                                    <td><?php echo $contacto["TELEFONO"]; ?></td>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                        </table>
                    <?php
                    }
                    else{
                    ?>
                        <p class="error"><?php echo $errnotec; ?></p>
                    <?php
                    }
                    ?>
                    <h3><?php echo $h3cont24h; ?></h3>
                    <?php
                    if (count($cont24h) > 0){
                    ?>
                        <table>
                            <tr>
                                <th class="t4c"><?php echo $nomflota; ?></th>
                                <th class="t5c"><?php echo $mail; ?></th>
                                <th class="t5c"><?php echo $telefono; ?></th>
                            </tr>
                            <?php
                            $i = 0;
                            foreach ($cont24h as $cont){
                                $sql_contacto = "SELECT * FROM contactos WHERE ID = $cont";
                                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto 24x7: " . mysql_error());
                                $ncontacto = mysql_num_rows($res_contacto);
                                if ($ncontacto > 0){
                                    $contacto = mysql_fetch_array($res_contacto);
                                }
                            ?>
                                <tr <?php if (($i % 2) == 1) {echo "class = 'filapar'";} ?>>
                                    <td><?php echo $contacto['NOMBRE']; ?></td>
                                    <td><?php echo $contacto["MAIL"]; ?></td>
                                    <td><?php echo $contacto["TELEFONO"]; ?></td>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                        </table>
                    <?php
                    }
                    else{
                    ?>
                        <p class="error"><?php echo $errno24h; ?></p>
                    <?php
                    }
                    ?>
                <?php
                }
                if ($permiso > 1){
                ?>
                    <table>
                        <tr>
                            <td class="borde">
                                <a href='#' onclick="document.modflota.action='contactos_flota.php';document.modflota.submit();">
                                    <img src='imagenes/editacont.png' alt='<?php echo $editcont; ?>' title='<?php echo $editcont; ?>'>
                                </a><br><?php echo $editcont; ?>
                            </td>
                        </tr>
                    </table>
                <?php
                }
                ?>
            </div>
            <div id="term">
                <h2><?php echo $h2term; ?></h2>
                <h3><?php echo $h3rango; ?></h3>
                <?php
                if ($row_flota['RANGO'] != ""){
                ?>
                    <p><?php echo $row_flota['RANGO']; ?></p>
                <?php
                }
                else{
                ?>
                    <p class="error"><?php echo $errnorango; ?></p>
                <?php
                }
                ?>
                <h3><?php echo $h3nterm; ?></h3>
                <table>
                    <tr>
                        <th colspan="8"><?php echo $totalterm; ?></th>
                    </tr>
                    <tr>
                        <td colspan="8" class="centro"><?php echo $tot_term; ?></td>
                    </tr>
                    <tr>
                        <th><?php echo $cabecera[0]; ?></th>
                        <th colspan="3"><?php echo $cabecera[1]; ?></th>
                        <th colspan="3"><?php echo $cabecera[5]; ?></th>
                        <th><?php echo $cabecera[9]; ?></th>
                    </tr>
                    <tr>
                        <td class="centro" rowspan="3"><?php echo $nterm[0]; ?></td>
                        <td class="centro" colspan="3"><?php echo $nterm[1]; ?></td>
                        <td class="centro" colspan="3"><?php echo $nterm[5]; ?></td>
                        <td class="centro" rowspan="3"><?php echo $nterm[9]; ?></td>
                    </tr>
                    <tr>
                        <th><?php echo $cabecera[2]; ?></th>
                        <th><?php echo $cabecera[3]; ?></th>
                        <th><?php echo $cabecera[4]; ?></th>
                        <th><?php echo $cabecera[6]; ?></th>
                        <th><?php echo $cabecera[7]; ?></th>
                        <th><?php echo $cabecera[8]; ?></th>
                    </tr>
                    <tr>
                        <td class="centro"><?php echo $nterm[2]; ?></td>
                        <td class="centro"><?php echo $nterm[3]; ?></td>
                        <td class="centro"><?php echo $nterm[4]; ?></td>
                        <td class="centro"><?php echo $nterm[6]; ?></td>
                        <td class="centro"><?php echo $nterm[7]; ?></td>
                        <td class="centro"><?php echo $nterm[8]; ?></td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <?php
                        if ($permiso == 2) {
                        ?>
                            <td class="borde">
                                <a href='#' onclick="document.modflota.action='akdc_flota.php';document.modflota.submit();">
                                    <img src='imagenes/akdc.png' alt='AKDC' title='AKDC'>
                                </a><br>Generar AKDC
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
                        <?php
                        }
                        ?>
                        <td class="borde">
                            <a href='#' onclick="document.termflota.submit();">
                                <img src='imagenes/lista.png' alt='<?php echo $terminales; ?>' title='<?php echo $terminales; ?>'>
                            </a><br><?php echo $terminales; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

                <form name="modflota" action="#" method="POST">
                    <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
                </form>
                <form name="formdet" action="detalle_flota.php" method="POST">
                    <input type="hidden" name="idflota" value="#">
                </form>
                <form name="termflota" action="terminales.php" method="POST">
                    <input type="hidden" name="flota" value="<?php echo $idflota ?>">
                </form>
<?php
}
else {
?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $permno; ?> &mdash; Permiso: <?php echo $permiso; ?>; IdFlota = <?php echo $idflota; ?>; Flota_uSu = <?php echo $flota_usu; ?></p>
<?php
}
?>
    </body>
</html>
