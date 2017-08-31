<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/coberturas_$idioma.php";
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
if ($permiso > 1){
    // Select de Emplazamientos:
    $sql_tbssel = "SELECT id, emplazamiento FROM emplazamientos WHERE 1";
    if (($selprov != "00") && ($selprov != "")){
        $sql_tbssel .= " AND (emplazamientos.provincia = " . $selprov . ")";
    }
    $sql_tbssel .= " ORDER BY emplazamiento ASC";
    $res_tbsssel = mysql_query($sql_tbssel) or die(mysql_error());
    $ntbssel = mysql_num_rows($res_tbsssel);
    // Select de Municipios:
    $sql_munisel = "SELECT * FROM municipios WHERE 1";
    if (($selprovmuni != "00") && ($selprovmuni != "")){
        $sql_munisel .= " AND (municipios.CPRO = " . $selprovmuni . ")";
    }
    $sql_munisel .= " ORDER BY CPRO ASC, MUNICIPIO ASC";
    $res_munisel = mysql_query($sql_munisel) or die(mysql_error());
    $nmunisel = mysql_num_rows($res_munisel);
    // Consulta de emplazamientos:
    if ($tam_pagina == "") {
        $tam_pagina = 30;
    }
    if (!$pagina) {
        $inicio = 0;
        $pagina = 1;
    }
    else {
        $inicio = ($pagina - 1) * $tam_pagina;
    }
    if (!$divoculta) {
        $divoculta = 'cobmuni';
    }
    $sql_tbs = "SELECT * FROM emplazamientos WHERE 1";
    if (($seltbs != "00") && ($seltbs != "")){
        $sql_tbs .= " AND (emplazamientos.id = " . $seltbs . ")";
    }
    if (($selprov != "00") && ($selprov != "")){
        $sql_tbs .= " AND (emplazamientos.provincia = " . $selprov . ")";
    }
    $sql_tbs .= " ORDER BY emplazamiento ASC";
    $res_tbstotal = mysql_query($sql_tbs) or die(mysql_error());
    $ntbstotal = mysql_num_rows($res_tbstotal);
    $sql_tbs = $sql_tbs . " LIMIT " . $inicio . "," . $tam_pagina . ";";
    $npag = ceil($ntbstotal / $tam_pagina);
    $res_tbs = mysql_query($sql_tbs) or die(mysql_error());
    $ntbs = mysql_num_rows($res_tbs);
    // Consulta de municipios
    if ($tam_pagmun == "") {
        $tam_pagmun = 30;
    }
    if (!$pagmun) {
        $inimun = 0;
        $pagmun = 1;
    }
    else {
        $inimun = ($pagmun - 1) * $tam_pagmun;
    }
    $sql_muni = "SELECT * FROM municipios WHERE 1";
    if (($selprovmuni != "00") && ($selprovmuni != "")){
        $sql_muni .= " AND (municipios.CPRO = " . $selprovmuni . ")";
    }
    if (($selmuni != "00") && ($selmuni != "")){
        $sql_muni .= " AND (municipios.INE = " . $selmuni . ")";
    }
    $sql_muni .= " ORDER BY CPRO ASC, MUNICIPIO ASC";
    $res_munitotal = mysql_query($sql_muni) or die(mysql_error());
    $nmunitotal = mysql_num_rows($res_munitotal);
    $sql_muni = $sql_muni . " LIMIT " . $inimun . "," . $tam_pagmun . ";";
    $npagmun = ceil($nmunitotal / $tam_pagmun);
    $res_muni = mysql_query($sql_muni) or die(mysql_error());
    $nmuni = mysql_num_rows($res_muni);
    $provincias = array(
        '03' => 'Alicante/Alacant', '12' => 'Castellón/Castelló',
        '46' => 'Valencia/València', '16' => 'Cuenca',
        '43' => 'Tarragona', '44' => 'Teruel'
    );
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
    <script type="text/javascript" src="js/coberturas.js"></script>

</head>
<body>
    <?php
    if ($permiso > 1){
    ?>
        <h1>
            <?php echo $h1; ?>
            &mdash; <a href="coberturas.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
            &mdash; <a href="xlscoberturas.php" target="_blank"><img src="imagenes/xls.png" alt="<?php echo $expxls;?>" title="<?php echo $expxls;?>"></a>
        </h1>
        <div id="contenido">
            <div id="pestanyas">
                <ul id="tab">
                    <li>
                        <a href="#" id="linkhome"><?php echo $tabtbs;?></a>
                    </li>
                    <li><a href="#" id="linkmuni"><?php echo $tabmuni;?></a></li>
                </ul>
            </div>
            <div id="limpia"></div>
            <div id="cobtbs">
                <form action="coberturas_detalle.php" name="formcobdetalle" id="formcobdetalle"  method="POST">
                    <input type="hidden" name="idemp" value="0" />
                </form>
                <form action="coberturas.php" name="formcoberturas" id="formcoberturas" method="POST">
                    <input type="hidden" name="divoculta" id="divoculta" value="<?php echo $divoculta;?>"  />
                    <input type="hidden" name="pagina" id="pagtbs" value="<?php echo $pagina;?>"  />
                    <input type="hidden" name="npag" id="npag" value="<?php echo $npag;?>"  />
                    <h4>
                        <?php echo $txtcriterios; ?> &mdash;
                        <a href="coberturas.php">
                            <img src="imagenes/update.png" alt="<?php echo $txtreset;?>" title="<?php echo $txtreset;?>">
                        </a>
                    </h4>
                    <table>
                        <tr>
                            <td>
                                <label for="selprov"><?php echo $thprov; ?>: </label>
                                <select name='selprov' id="selprov">
                                    <option value='00' <?php if (($selprov == "00") || ($selprov == "")) echo ' selected'; ?>>
                                        Seleccionar
                                    </option>
                                    <?php
                                    foreach ($provincias as $idprov => $nomprov) {
                                    ?>
                                        <option value='<?php echo $idprov; ?>' <?php if ($selprov == $idprov) echo ' selected'; ?>>
                                            <?php echo $nomprov; ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <label for="seltbs"><?php echo $txtseltbs; ?>: </label>
                                <select name='seltbs' id="seltbs">
                                    <option value='00' <?php if (($seltbs == "00") || ($seltbs == "")) echo ' selected'; ?>>
                                        Seleccionar
                                    </option>
                                    <?php
                                    for ($i = 0; $i < $ntbssel; $i++){
                                        $tbssel = mysql_fetch_array($res_tbsssel);
                                    ?>
                                        <option value='<?php echo $tbssel['id']; ?>' <?php if ($seltbs == $tbssel['id']) echo ' selected'; ?>>
                                            <?php echo $tbssel['emplazamiento']; ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <h4>
                        <?php
                        echo $h4res;
                        if ($ntbstotal > 0){
                            $final = ($inicio + $tam_pagina);
                            if ($final > $ntbstotal){
                                $final = $ntbstotal;
                            }
                            echo ' &mdash; ' . ($inicio + 1) . ' a ' . $final . ' de ' . $ntbstotal . ' ' . $txtemp;?>
                        <?php
                        }
                        ?>
                    </h4>
                    <table>
                        <tr>
                            <?php
                            if ($npag > 1) {
                            ?>
                                <td class="borde">
                                    <?php
                                    if ($pagina > 1){
                                    ?>
                                        <a href="#" id="primtbs"><img src="imagenes/primera.png" alt="<?php echo $txtprim;?>"  title="<?php echo $txtprim;?>" /></a>
                                        &nbsp;
                                        <a href="#" id="prevtbs"><img src="imagenes/anterior.png" alt="<?php echo $txtprev;?>"  title="<?php echo $txtprev;?>" /></a>
                                        &nbsp;
                                    <?php
                                    }
                                    ?>
                                    <?php echo $pgtxt . ' ' . $pagina . ' de ' . $npag;?>
                                    <?php
                                    if ($pagina < $npag){
                                    ?>
                                        &nbsp;
                                        <a href="#" id="sigtbs"><img src="imagenes/siguiente.png" alt="<?php echo $txtsig;?>"  title="<?php echo $txtsig   ;?>" /></a>
                                        &nbsp;
                                        <a href="#" id="ulttbs"><img src="imagenes/ultima.png" alt="<?php echo $txtult;?>"  title="<?php echo $txtult;?>" /></a>
                                    <?php
                                    }
                                    ?>
                                </td>
                            <?php
                            }
                            if ($ntbstotal > 30) {
                            ?>
                                <td class="borde">
                                    Mostrar:
                                    <select name='tam_pagina' id="tampagtbs">
                                        <option value='30' <?php if (($tam_pagina == "30") || ($tam_pagina == "")) {echo 'selected';}?>>30</option>
                                        <option value='50' <?php if ($tam_pagina == "50") {echo 'selected';}?>>50</option>
                                        <option value='100' <?php if ($tam_pagina == "100") {echo 'selected';}?>>100</option>
                                        <option value='<?php echo $ntbssel;?>' <?php if ($tam_pagina == $ntbssel) {echo 'selected';}?>><?php echo $txtodos;?></option>
                                    </select> <?php echo $regpg; ?>
                                </td>
                            <?php
                            }
                            ?>
                        </tr>
                    </table>
                </form>
                <?php
                if ($ntbstotal > 0){
                ?>
                    <table>
                        <tr>
                            <th><?php echo $thdetalle;?></th>
                            <th><?php echo $themplaza;?></th>
                            <th><?php echo $thprov;?></th>
                            <th><?php echo $thtitular;?></th>
                            <th><?php echo $thmuncob;?></th>
                        </tr>
                        <?php
                        for ($i = 0; $i < $ntbs; $i++){
                            $tbs = mysql_fetch_array($res_tbs);
                            $nmuncob = 0;
                            $idemp = $tbs['id'];
                            $sql_cob = "SELECT COUNT(*) FROM coberturas WHERE (coberturas.emplazamiento_id = " . $idemp . ")";
                            $res_cob = mysql_query($sql_cob) or die(mysql_error());
                            $ncob = mysql_num_rows($res_cob);
                            if ($ncob > 0){
                                $filacob =  mysql_fetch_array($res_cob);
                                $nmuncob = $filacob[0];
                            }

                        ?>
                            <tr <?php if (($i % 2) > 0) {echo "class='filapar'";}?>>
                                <td class="centro">
                                    <a href='#' id="dettbs-<?php echo $idemp;?>">
                                        <img src='imagenes/consulta.png' alt="<?php echo $txtdetalle;?>" title="<?php echo $txtdetalle;?>" >
                                    </a>
                                </td>
                                <td><?php echo $tbs['emplazamiento']; ?></td>
                                <td><?php echo $provincias[$tbs['provincia']]; ?></td>
                                <td><?php echo $tbs['titular']; ?></td>
                                <td class="centro"><?php echo $nmuncob; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                <?php
                }
                else{
                ?>
                    <p class='error'><?php echo $errnotbs; ?></p>
                <?php
                }
                ?>
            </div>
            <div id="cobmuni">
				<form action="coberturas_detmuni.php" name="formmunidetalle" method="POST">
                    <input type="hidden" name="idmuni" value="0" />
                </form>
                <form action="coberturas.php" name="formmunreset" method="POST">
                    <input type="hidden" name="divoculta" id="divoculta" value="<?php echo $divoculta;?>"  />
                </form>
                <form action="coberturas.php" name="formmunicipios" id="formmunicipios" method="POST">
                    <input type="hidden" name="pagmun" id="pagmun" value="<?php echo $pagmun;?>"  />
                    <input type="hidden" name="npagmun" id="npagmun" value="<?php echo $npagmun;?>"  />
                    <input type="hidden" name="divoculta" id="divoculta" value="<?php echo $divoculta;?>"  />
                    <h4>
                        <?php echo $txtcriterios; ?> &mdash;
                        <a href="#" id="resetmun">
                            <img src="imagenes/update.png" alt="<?php echo $txtreset;?>" title="<?php echo $txtreset;?>">
                        </a>
                    </h4>
                    <table>
                        <tr>
                            <td>
                                <label for="selprovmuni"><?php echo $thprov; ?>: </label>
                                <select name='selprovmuni' id="selprov">
                                    <option value='00' <?php if (($selprovmuni == "00") || ($selprovmuni == "")) echo ' selected'; ?>>
                                        Seleccionar
                                    </option>
                                    <?php
                                    $i = 0;
                                    foreach ($provincias as $idprov => $nomprov) {
                                        if ($i < 3){
                                    ?>
                                            <option value='<?php echo $idprov; ?>' <?php if ($selprovmuni == $idprov) echo ' selected'; ?>>
                                                <?php echo $nomprov; ?>
                                            </option>
                                    <?php
                                            $i++;
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <label for="selmuni"><?php echo $thmuni; ?>: </label>
                                <select name='selmuni' id="selmuni">
                                    <option value='00' <?php if (($selmuni == "00") || ($selmuni == "")) echo ' selected'; ?>>
                                        Seleccionar
                                    </option>
                                    <?php
                                    for ($i = 0; $i < $nmunisel; $i++){
                                        $munisel = mysql_fetch_array($res_munisel);
                                    ?>
                                        <option value='<?php echo $munisel['INE']; ?>' <?php if ($selmuni == $munisel['INE']) echo ' selected'; ?>>
                                            <?php echo $munisel['MUNICIPIO']; ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <h4>
                        <?php
                        echo $h4res;
                        if ($nmunitotal > 0){
                            $finmun = ($inimun + $tam_pagmun);
                            if ($finmun > $nmunitotal){
                                $finmun = $nmunitotal;
                            }
                            echo ' &mdash; ' . ($inimun + 1) . ' a ' . $finmun . ' de ' . $nmunitotal . ' ' . $thmuni . 's';?>
                        <?php
                        }
                        ?>
                    </h4>
                    <table>
                        <tr>
                            <?php
                            if ($npagmun > 1) {
                            ?>
                                <td class="borde">
                                    <?php
                                    if ($pagmun > 1){
                                    ?>
                                        <a href="#" id="primmun"><img src="imagenes/primera.png" alt="<?php echo $txtprim;?>"  title="<?php echo $txtprim;?>" /></a>
                                        &nbsp;
                                        <a href="#" id="prevmun"><img src="imagenes/anterior.png" alt="<?php echo $txtprev;?>"  title="<?php echo $txtprev;?>" /></a>
                                        &nbsp;
                                    <?php
                                    }
                                    ?>
                                    <?php echo $pgtxt . ' ' . $pagmun . ' de ' . $npagmun;?>
                                    <?php
                                    if ($pagmun < $npagmun){
                                    ?>
                                        &nbsp;
                                        <a href="#" id="sigmun"><img src="imagenes/siguiente.png" alt="<?php echo $txtsig;?>"  title="<?php echo $txtsig   ;?>" /></a>
                                        &nbsp;
                                        <a href="#" id="ultmun"><img src="imagenes/ultima.png" alt="<?php echo $txtult;?>"  title="<?php echo $txtult;?>" /></a>
                                    <?php
                                    }
                                    ?>
                                </td>
                            <?php
                            }
                            if ($nmunitotal > 30) {
                            ?>
                                <td class="borde">
                                    Mostrar:
                                    <select name='tam_pagmun'>
                                        <option value='30' <?php if (($tam_pagmun == "30") || ($tam_pagina == "")) {echo 'selected';}?>>30</option>
                                        <option value='50' <?php if ($tam_pagmun == "50") {echo 'selected';}?>>50</option>
                                        <option value='100' <?php if ($tam_pagmun == "100") {echo 'selected';}?>>100</option>
                                        <option value='<?php echo $nmunisel;?>' <?php if ($tam_pagmun == $nmunisel) {echo 'selected';}?>><?php echo $txtodos;?></option>
                                    </select> <?php echo $regpg; ?>
                                </td>
                            <?php
                            }
                            ?>
                            <td class="borde">
                                <label for="selporcent"><?php echo $thporcent; ?> &gt; </label>
                                <select name='selporcent' id="selporcent">
                                    <option value='00' <?php if (($selporcent == "00") || ($selporcent == "")) echo ' selected'; ?>>
                                        Seleccionar
                                    </option>
                                    <?php
                                    $porcentajes = array(25, 20, 15, 10, 5);
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
                    <table>
                        <tr>
                            <th><?php echo $thdetalle;?></th>
                            <th><?php echo $thprov;?></th>
                            <th><?php echo $thmuni;?></th>
                            <th><?php echo $thnumtbs;?></th>
                        </tr>
                        <?php
                        for ($i = 0; $i < $nmuni; $i++){
                            $muni = mysql_fetch_array($res_muni);
                            $ntbscob = 0;
                            $idmuni = $muni['INE'];
							$sql_muncob = "SELECT COUNT(*) FROM coberturas WHERE (coberturas.municipio_id = " . $idmuni . ")";
                            if (($selporcent != "00") && ($selporcent != "")){
                                $sql_muncob .= " AND (coberturas.porcentaje > " . $selporcent . ")";
                            }
                            $res_muncob = mysql_query($sql_muncob) or die(mysql_error());
                            $nmuncob = mysql_num_rows($res_muncob);
                            if ($nmuncob > 0){
                                $filacob =  mysql_fetch_array($res_muncob);
                                $ntbscob = $filacob[0];
                            }
                        ?>
                            <tr <?php if (($i % 2) > 0) {echo "class='filapar'";}?>>
                                <td class="centro">
                                    <a href='#' id="detmun-<?php echo $idmuni;?>">
                                        <img src='imagenes/consulta.png' alt="<?php echo $txtdetalle;?>" title="<?php echo $txtdetalle;?>" >
                                    </a>
                                </td>
                                <td><?php echo $muni['PROVINCIA']; ?></td>
                                <td><?php echo $muni['MUNICIPIO']; ?></td>
                                <td><?php echo $ntbscob; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </form>
            </div>
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
