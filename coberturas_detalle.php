<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/cobdetalle_$idioma.php";
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
<!DOCTYPE html>
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
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/coberturas_detalle.js"></script>
</head>
<body>
    <?php
    if ($permiso > 1){
        // Consulta de Emplazamiento:
        $sql_tbs = "SELECT * FROM emplazamientos WHERE id = " . $idemp;
        $res_tbs = mysql_query($sql_tbs) or die(mysql_error());
        $ntbs = mysql_num_rows($res_tbs);
        if ($ntbs > 0){
            $tbs = mysql_fetch_array($res_tbs);
            $nmuncob = 0;
            $idemp = $tbs['id'];
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
            if (!$divoculta) {
                $divoculta = 'cobflotas';
            }
            $sql_cob = "SELECT * FROM coberturas, municipios WHERE (coberturas.emplazamiento_id = " . $idemp . ")";
            if (($selprov != "00") && ($selprov != "")){
                $sql_cob .= " AND (municipios.CPRO = " . $selprov . ")";
            }
            if (($selporcent != "00") && ($selporcent != "")){
                $sql_cob .= " AND (coberturas.porcentaje > " . $selporcent . ")";
                $porcmuni = $selporcent;
            }
            $sql_cob .= " AND (coberturas.municipio_id = municipios.INE) ORDER BY coberturas.porcentaje DESC";
            $res_totcob = mysql_query($sql_cob) or die(mysql_error());
            $ntotcob = mysql_num_rows($res_totcob);
            $sql_cob = $sql_cob . " LIMIT " . $inicio . "," . $tam_pagina . ";";
            $npag = ceil($ntotcob / $tam_pagina);
            $res_cob = mysql_query($sql_cob) or die(mysql_error());
            $nmuncob = mysql_num_rows($res_cob);
        }
        $provincias = array(
            '03' => 'Alicante/Alacant', '12' => 'Castellón/Castelló',
            '46' => 'Valencia/València', '16' => 'Cuenca',
            '43' => 'Tarragona', '44' => 'Teruel'
        );
    ?>
        <h1>
            <?php echo $h1; ?>
            &mdash;
            <a href="#" id="newtab"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
            &mdash;
            <a href="#" id="export"><img src="imagenes/xls.png" alt="Exportar a Excel" title="Exportar a Excel"></a>
            &mdash;
            <a href="coberturas.php"><img src="imagenes/volver.png" alt="<?php echo $botatras;?>" title="<?php echo $botatras;?>"></a>
        </h1>
        <form action="coberturas_detalle.php" name="formcobdetalle" id="formcobdetalle" method="POST" target="_blank">
            <input type="hidden" name="idemp" value="<?php echo $idemp;?>" />
        </form>
        <form action="coberturas_detalle.php" name="fomreset" id="fomreset" method="POST">
            <input type="hidden" name="idemp" value="<?php echo $idemp;?>" />
        </form>
        <form action="xlscobertura.php" name="formcobexport" id="formcobexport" method="POST" target="_blank">
            <input type="hidden" name="idemp" value="<?php echo $idemp;?>" />
            <?php
            $porcmuni = 5;
            if (($selporcent != "00") && ($selporcent != "")){
                $porcmuni = $selporcent;
            }
            ?>
            <input type="hidden" name="porcmuni" value="<?php echo $porcmuni;?>" />
        </form>
        <h2><?php echo $h2emp; ?></h2>
        <?php
        if ($ntbs > 0){
        ?>
            <table>
                <tr>
                    <th><?php echo $themplaza;?></th>
                    <th><?php echo $thprov;?></th>
                    <th><?php echo $thtitular;?></th>
                    <th><?php echo $thlatitud;?></th>
                    <th><?php echo $thlongitud;?></th>
                </tr>
                <tr>
                    <td><?php echo $tbs['emplazamiento']; ?></td>
                    <td><?php echo $provincias[$tbs['provincia']]; ?></td>
                    <td><?php echo $tbs['titular']; ?></td>
                    <td><?php echo $tbs['latitud']; ?></td>
                    <td><?php echo $tbs['longitud']; ?></td>
                </tr>
            </table>
            <div id="contenido">
                <div id="pestanyas">
                    <ul id="tab">
                        <li>
                            <a href="#" id="linkmuni"><?php echo $tabmuni;?></a>
                        </li>
                        <li><a href="#" id="linkflotas"><?php echo $tabflotas;?></a></li>
                    </ul>
                </div>
                <div id="limpia"></div>
                <div id="cobmuni">
                    <h2>
                        <?php
                        $final = ($inicio + $tam_pagina);
                        if ($final > $ntotcob){
                            $final = $ntotcob;
                        }
                        echo $h2cob;
                        if ($ntotcob > 0){
                            echo ' &mdash; ' . ($inicio + 1) . ' a ' . $final . ' de ' . $ntotcob;
                        ?>
                            &mdash;
                            <a href="#" id="reset"><img src="imagenes/update.png" alt="<?php echo $txtreset;?>" title="<?php echo $txtreset;?>"></a>
                        <?php
                        }
                        ?>
                    </h2>
                    <form name="formmuni" id="formmuni" action="coberturas_detalle.php" method="post">
                        <input type="hidden" name="idemp" value="<?php echo $idemp;?>" />
                        <input type="hidden" name="pagina" id="pagina" value="<?php echo $pagina;?>"  />
                        <input type="hidden" name="npag" id="npag" value="<?php echo $npag;?>"  />
                        <table>
                            <tr>
                                <?php
                                if ($npag > 1) {
                                ?>
                                    <td class="borde">
                                        <?php
                                        if ($pagina > 1){
                                        ?>
                                            <a href="#" id="primpag"><img src="imagenes/primera.png" alt="<?php echo $txtprim;?>"  title="<?php echo $txtprim;?>" /></a>
                                            &nbsp;
                                            <a href="#" id="prevpag"><img src="imagenes/anterior.png" alt="<?php echo $txtprev;?>"  title="<?php echo $txtprev;?>" /></a>
                                            &nbsp;
                                        <?php
                                        }
                                        ?>
                                        <?php echo $pgtxt . ' ' . $pagina . ' de ' . $npag;?>
                                        <?php
                                        if ($pagina < $npag){
                                        ?>
                                            &nbsp;
                                            <a href="#" id="sigpag"><img src="imagenes/siguiente.png" alt="<?php echo $txtsig;?>"  title="<?php echo $txtsig   ;?>" /></a>
                                            &nbsp;
                                            <a href="#" id="ultpag"><img src="imagenes/ultima.png" alt="<?php echo $txtult;?>"  title="<?php echo $txtult;?>" /></a>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                <?php
                                }
                                if ($ntotcob > 20) {
                                ?>
                                    <td class="borde">
                                        Mostrar:
                                        <select name='tam_pagina'>
                                            <option value='20' <?php if (($tam_pagina == "20") || ($tam_pagina == "")) {echo 'selected';}?>>20</option>
                                            <option value='50' <?php if ($tam_pagina == "50") {echo 'selected';}?>>50</option>
                                            <option value='<?php echo $ntotcob;?>' <?php if ($tam_pagina == $ntotcob) {echo 'selected';}?>><?php echo $txtodos;?></option>
                                        </select> <?php echo $regpg; ?>
                                    </td>
                                <?php
                                }
                                ?>
                                <td class="borde">
                                    <label for="selprov"><?php echo $thprov; ?>: </label>
                                    <select name='selprov' id="selprov">
                                        <option value='00' <?php if (($selprov == "00") || ($selprov == "")) echo ' selected'; ?>>
                                            Seleccionar
                                        </option>
                                        <?php
                                        $i = 0;
                                        foreach ($provincias as $idprov => $nomprov) {
                                            if ($i < 3){
                                        ?>
                                                <option value='<?php echo $idprov; ?>' <?php if ($selprov == $idprov) echo ' selected'; ?>>
                                                    <?php echo $nomprov; ?>
                                                </option>

                                        <?php
                                                $i++;
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="borde">
                                    <label for="selporcent"><?php echo $thporcent; ?> &gt; </label>
                                    <select name='selporcent' id="selporcent">
                                        <option value='00' <?php if (($selporcent == "00") || ($selporcent == "")) echo ' selected'; ?>>
                                            Seleccionar
                                        </option>
                                        <?php
                                        $porcentajes = array(25, 20, 15, 10, 5, 2);
                                        foreach ($porcentajes as $porcent) {
                                        ?>
                                            <option value='<?php echo $porcent; ?>' <?php if ($selporcent == $porcent) echo ' selected'; ?>>
                                                <?php echo $porcent; ?> %
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </form>
                    <?php
                    if ($ntotcob > 0){
                    ?>
                        <table>
                            <tr>
                                <th><?php echo $thprov;?></th>
                                <th><?php echo $thmun;?></th>
                                <th><?php echo $thpob;?></th>
                                <th><?php echo $thporcent;?></th>
                                <th><?php echo $thpobcob;?></th>
                            </tr>
                            <?php
                            $muniflotas = array();
                            $provflotas = array();
                            for($i = 0; $i < $nmuncob; $i++){
                                $muncob = mysql_fetch_array($res_cob);
                                if ($muncob['porcentaje'] >= $porcmuni){
                                    array_push($muniflotas, $muncob['INE']);
                                }
                                if (!(in_array($muncob['CPRO'], $provflotas))){
                                    array_push($provflotas, $muncob['CPRO']);
                                }
                            ?>
                                <tr <?php if (($i % 2) > 0) {echo "class='filapar'";}?>>
                                    <td><?php echo $muncob['PROVINCIA']; ?></td>
                                    <td><?php echo $muncob['MUNICIPIO']; ?></td>
                                    <td class="centro"><?php echo $muncob['POBLACION']; ?></td>
                                    <td class="centro"><?php echo round($muncob['porcentaje'], 2); ?></td>
                                    <td class="centro"><?php echo round($muncob['porcentaje'] * $muncob['POBLACION']/100); ?></td>
                                </tr>
                            <?php
                            }
                            ?>

                        </table>
                    <?php
                    }
                    else{
                    ?>
                        <p class='error'><?php echo $errnocob; ?></p>

                    </div>
                <?php
                    }
                ?>
                    <div>

                    </div>
                <?php
                }
                else{
                ?>
                    <p class='error'><?php echo $errnotbs; ?></p>
                <?php
                }
                ?>
            </div>
            <div id="cobflotas">
                <h2><?php echo $h2flotas;?></h2>
                <?php
                $ambitos = array('AUT', 'PROV', 'LOC');
                $h4amb = array($h4aut, $h4prov, $h4local);
                $txtamb = array($txtaut, $txtprov, $txtlocal);
                $provcv = array('03', '12', '46');
                for ($j = 0; $j < count($ambitos); $j++){
                    $consulta = TRUE;
                    $sql_flotas = "SELECT * FROM flotas WHERE (AMBITO = '" . $ambitos[$j] . "')";
                    if ($ambitos[$j] == 'PROV'){
                        $nprovflotas = count($provflotas);
                        if ($nprovflotas > 0){
                            if ($nprovflotas == 1){
                                $sql_flotas .= " AND (INE LIKE '" . $provflotas[0] . "%')";
                            }
                            else{
                                $sql_flotas .= " AND (";

                                for ($k = 0; $k < $nprovflotas; $k++){
                                    $sql_flotas .= "(INE LIKE '" . $provflotas[$k] . "%')";
                                    if ($k < ($nprovflotas - 1)){
                                        $sql_flotas .= " OR ";
                                    }
                                }
                                $sql_flotas .= ")";
                            }
                        }
                        else{
                            if (in_array($tbs['provincia'], $provcv)){
                                $sql_flotas .= " AND (INE LIKE '" . $tbs['provincia'] . "%')";
                            }
                            else{
                                $consulta = FALSE;
                            }
                        }
                    }
                    if ($ambitos[$j] == 'LOC'){
                        $nmunflotas = count($muniflotas);
                        if ($nmunflotas > 0){
                            $sql_flotas .= " AND INE IN (";
                            for ($k = 0; $k < $nmunflotas; $k++){
                                $sql_flotas .= $muniflotas[$k];
                                if ($k < ($nmunflotas - 1)){
                                    $sql_flotas .= ", ";
                                }
                            }
                            $sql_flotas .= " )";
                        }
                        else{
                            if (in_array($tbs['provincia'], $provcv)){
                                $sql_flotas .= " AND (INE = '" . $tbs['municipio_id'] . "')";
                            }
                            else{
                                $consulta = FALSE;
                            }
                        }
                    }
                    $sql_flotas .= " ORDER BY FLOTA ASC";
                    $nflotas = 0;
                    if ($consulta){
                        $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
                        $nflotas = mysql_num_rows($res_flotas);
                    }
                ?>
                    <h4>
                        <?php
                        echo $h4amb[$j];
                        if ($nflotas > 0){
                            echo ' &mdash; ' . $nflotas . ' ' . $txtflotas;
                        }
                        if (($ambitos[$j] == 'LOC') && ($nmunflotas > 0)){
                            echo ' &mdash; ' . $thporcent . ' &gt; ' . $porcmuni . ' %';
                        }
                        ?>
                    </h4>
                    <?php
                    if ($nflotas > 0){
                    ?>
                        <table>
                            <tr>
                                <th>Flota</th>
                                <th><?php echo $thacro;?></th>
                                <th><?php echo $thcont;?></th>
                                <th><?php echo $thcargo;?></th>
                                <th><?php echo $thmail;?></th>
                                <th><?php echo $thoficial;?></th>
                            </tr>
                            <?php
                            for ($i = 0; $i < $nflotas; $i++){
                                $flota = mysql_fetch_array($res_flotas);
                                $sql_cont = "SELECT contactos.NOMBRE, contactos.CARGO, contactos.MAIL, contactos_flotas.ROL FROM contactos, contactos_flotas";
                                $sql_cont .= " WHERE (contactos_flotas.FLOTA_ID = " . $flota['ID'] . ") AND (contactos.ID = contactos_flotas.CONTACTO_ID)";
                                $sql_cont .= " AND (contactos_flotas.ROL = 'CONT24H')";
                                $res_cont = mysql_query($sql_cont) or die(mysql_error());
                                $ncont = mysql_num_rows($res_cont);
                            ?>
                                <tr <?php if (($i % 2) > 0) {echo "class='filapar'";}?>>
                                    <td><?php echo $flota['FLOTA'];?></td>
                                    <td><?php echo $flota['ACRONIMO'];?></td>
                                    <?php
                                    if ($ncont > 0){
                                        $contacto = mysql_fetch_array($res_cont);
                                    ?>
                                        <td><?php echo $contacto['NOMBRE'];?></td>
                                        <td><?php echo $contacto['CARGO'];?></td>
                                        <td><?php echo $contacto['MAIL'];?></td>
                                        <td><?php echo $flota['FORMCONT'];?></td>
                                    <?php
                                    }
                                    else{
                                    ?>
                                        <td colspan="4"><?php echo $txtnocont;?></td>
                                    <?php
                                    }
                                    ?>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    <?php
                    }
                    else{
                ?>
                        <p class='error'><?php echo sprintf($txtnoflota, $txtamb[$j]); ?></p>
                <?php
                    }
                }
                ?>
            </div>
    <?php
    }
    else{
    ?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $errnoperm; ?></p>
    <?php
    }
    ?>
</body>
