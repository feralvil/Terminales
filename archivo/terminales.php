<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/terminales_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>". mysql_error();
    exit;
}
else{
    // Seleccionamos la BBDD y codificamos la conexión en UTF-8:
    if (!mysql_select_db($base_datos, $link)) {
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
        exit;
    }
    mysql_set_charset('utf8',$link);
}
// ------------------------------------------------------------------------------------- //

// Importamos las variables de formulario:
import_request_variables("gp", "");

// Obtenemos el usuario
include_once('auth_user.php');
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    $flota = $flota_usu;
    $permiso = 1;
}
?>
<html>
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <script type="text/javascript">
            function checkAll() {
                var nodoCheck = document.getElementsByTagName("input");
                var varCheck = document.getElementById("seltodo").checked;
                for (i=0; i<nodoCheck.length; i++){
                    if (nodoCheck[i].type == "checkbox" && nodoCheck[i].name != "seltodo" && nodoCheck[i].disabled == false) {
                        nodoCheck[i].checked = varCheck;
                    }
                }
            }
        </script>
    </head>
    <body>
<?php
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
        <form action="terminales.php" name="newtab" method="POST" target="_blank">
            <input type="hidden" name="flota" value="<?php echo $flota;?>">
        <h1>
            <?php echo $h1; ?> &mdash; <input type="image" src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>">
            <?php
            if ($permiso == 2){
            ?>
                &mdash; <a href="xlsdots.php" target="_blank"><img src="imagenes/minidots.png" alt="Exportar al DOTS" title="Exportar al DOTS"></a>
            <?php
            }
            ?>
        </h1>
        </form>
        <form name="proveedor" action="xlstermprov.php" method="POST">
            <input type="hidden" name="idprov" value="<?php echo $prov;?>">
        </form>
        <form action="terminales.php" name="criterios" method="POST">
            <input type="hidden" name="orden" value="<?php echo $orden;?>">
            <h4>
                <?php echo $criterios; ?> &mdash;
                <a href="terminales.php">
                    <img src="imagenes/update.png" alt="<?php echo $resetcrit;?>" title="<?php echo $resetcrit;?>">
                </a>
            </h4>
            <table>
<?php
            if ($permiso == 2) {
?>
                <tr>
                    <td colspan="2">
                        <select name='flota' onChange='document.criterios.submit();'>
                            <option value='NN' <?php if (($flota == "NN") || ($flota == "")) echo ' selected'; ?>>Flota</option>
<?php
                        if ($permiso == 2) {
                            $sql_flotas = "SELECT ID, FLOTA FROM flotas ORDER BY FLOTA ASC";
                        }
                        $res_flotas = mysql_query($sql_flotas, $link) or die(mysql_error());
                        $nflotas = mysql_num_rows($res_flotas);
                        $flotas = array();
                        for ($i = 0; $i < $nflotas; $i++) {
                            $row_flota = mysql_fetch_array($res_flotas);
                            $flotas[$i] = $row_flota[0];
                            $nom_flota = $row_flota[1];
?>
                            <option value='<?php echo $row_flota[0]; ?>' <?php if ($flota == $row_flota[0]) echo ' selected'; ?>>
                                <?php echo $nom_flota; ?>
                            </option>
<?php
                        }
?>
                        </select>
                    </td>
                    <td>
                        <select name="prov" onChange="document.criterios.submit();">
                            <option value="NN" <?php if (($prov == "NN") || ($prov == "")) echo 'selected'; ?>><?php echo $provtxt; ?></option>
<?php
                            //$linkprov = "document.proveedor.idprov.value='$prov';document.proveedor.submit()";
                            $linkprov = "document.proveedor.submit()";
                            $sql_prov = "SELECT DISTINCT PROVEEDOR FROM terminales ORDER BY PROVEEDOR ASC";
                            $res_prov = mysql_query($sql_prov, $link) or die(mysql_error());
                            $nprov = mysql_num_rows($res_prov);
                            for ($i = 0; $i < $nprov; $i++) {
                                $row_prov = mysql_fetch_array($res_prov);
                                $valprov = $row_prov[0];

                                if ($valprov != "") {
?>
                                    <option value="<?php echo $valprov;?>" <?php if ($prov == $valprov) echo 'selected'; ?>>
                                        <?php echo $valprov;?>
                                    </option>
<?php
                                }
                            }
?>
                        </select>
                        &mdash; <a href="#" onclick="<?php echo $linkprov;?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
                    </td>
                </tr>
<?php
            }
?>
                <tr>
                    <td>
                        <select name="tipoterm" onChange="document.criterios.submit();">
                            <option value="00" <?php if (($tipoterm == "00") || ($tipoterm == "")) echo 'selected'; ?>><?php echo $tipotxt; ?></option>
                            <option value="F" <?php if ($tipoterm == "F") echo 'selected'; ?>><?php echo $fijo; ?></option>
                            <option value="M%" <?php if ($tipoterm == "M%") echo 'selected'; ?>><?php echo $movil; ?></option>
                            <option value="MB" <?php if ($tipoterm == "MB") echo 'selected'; ?>><?php echo "- $movilb"; ?></option>
                            <option value="MA" <?php if ($tipoterm == "MA") echo 'selected'; ?>><?php echo "- $movila"; ?></option>
                            <option value="MG" <?php if ($tipoterm == "MG") echo 'selected'; ?>><?php echo "- $movilg"; ?></option>
                            <option value="P%" <?php if ($tipoterm == "P%") echo 'selected'; ?>><?php echo "$portatil"; ?></option>
                            <option value="PB" <?php if ($tipoterm == "PB") echo 'selected'; ?>><?php echo "- $portatilb"; ?></option>
                            <option value="PA" <?php if ($tipoterm == "PA") echo 'selected'; ?>><?php echo "- $portatila"; ?></option>                                <option value="PX" <?php if ($tipoterm == "PX") echo 'selected'; ?>><?php echo "- $portatilx"; ?></option>
                            <option value="D" <?php if ($tipoterm == "D") echo 'selected'; ?>><?php echo $despacho; ?></option>
                        </select>
                    </td>
                    <td>
                        <select name="marca" onChange="document.criterios.submit();">
                            <option value="00" <?php if (($marca == "00") || ($marca == "")) echo 'selected'; ?>>Marca del Terminal</option>
<?php
                            $sql_marca = "SELECT DISTINCT MARCA FROM terminales ORDER BY MARCA ASC";
                            $res_marca = mysql_query($sql_marca, $link) or die(mysql_error());
                            $nmarca = mysql_num_rows($res_marca);
                            for ($i = 0; $i < $nmarca; $i++) {
                                $row_marca = mysql_fetch_array($res_marca);
                                if ($row_marca[0] != "") {
?>
                                    <option value="<?php echo $row_marca[0];?>" <?php if ($marca == $row_marca[0]) echo 'selected'; ?>><?php echo $row_marca[0];?></option>
<?php
                                }
                            }
?>
                        </select>
                    </td>
                    <td>
                    <?php
                        $sql_carpeta = "SELECT DISTINCT CARPETA FROM terminales ";
                        if (($flota != "") && ($flota != "NN")){
                            $sql_carpeta .= "WHERE terminales.FLOTA = '". $flota."' ";
                        }
                        $sql_carpeta .= "ORDER BY CARPETA ASC";
                        $res_carpeta = mysql_query($sql_carpeta, $link) or die(mysql_error());
                        $ncarpeta = mysql_num_rows($res_carpeta);
                    ?>
                        <select name="carpeta" onChange="document.criterios.submit();">
                            <option value="00" <?php if (($carpeta == "00") || ($carpeta == "")) echo 'selected'; ?>>Carpeta</option>
                            <?php
                            for ($i = 0; $i < $ncarpeta; $i++){
                                $row_carpeta = mysql_fetch_array($res_carpeta);
                                $optcarpeta = $row_carpeta[0];
                                if ($optcarpeta != "") {
                            ?>
                                    <option value="<?php echo $optcarpeta;?>" <?php if ($carpeta == $optcarpeta) echo 'selected'; ?>><?php echo $optcarpeta;?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        ISSI:&#160;<input type="text" name="issi" size="6">&#160;<input type='image' name='action' src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                    </td>
                    <td>
                        TEI:&#160;<input type="text" name="tei" size="20">&#160;<input type='image' name='action' src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                    </td>
                    <td>
                        N. Serie:&#160;<input type="text" name="nserie" size="20">&#160;<input type='image' name='action' src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                    </td>
                </tr>
                <tr>
                    <td>
                        <select name="autentica" onChange="document.criterios.submit();">
                            <option value="00" <?php if (($autentica == "00") || ($autentica == "")) echo 'selected'; ?>><?php echo $txtaut; ?></option>
                            <option value="SI" <?php if ($autentica == "SI") echo 'selected'; ?>>Sí</option>
                            <option value="NO" <?php if ($autentica == "NO") echo 'selected'; ?>>No</option>
                        </select>
                    </td>
                    <td>
                        <select name="dots" onChange="document.criterios.submit();">
                            <option value="00" <?php if (($dots == "00") || ($dots == "")) echo 'selected'; ?>><?php echo $dotstxt; ?></option>
                            <option value="SI" <?php if ($dots == "SI") echo 'selected'; ?>>Sí</option>
                            <option value="NO" <?php if ($dots == "NO") echo 'selected'; ?>>No</option>
                        </select>
                    </td>
                    <td>
                        <select name="permisos" onChange="document.criterios.submit();">
                            <option value="00" <?php if (($permisos == "00") || ($permisos == "")) echo 'selected'; ?>><?php echo $llamtxt; ?></option>
                            <option value="NO" <?php if ($permisos == "NO") echo 'selected'; ?>>No</option>
                            <option value="SEMID" <?php if ($permisos == "SEMID") echo 'selected'; ?>><?php echo $llamop2; ?></option>
                            <option value="DUPLEX" <?php if ($permisos == "DUPLEX") echo 'selected'; ?>><?php echo $llamop3; ?></option>
                            <option value="SYD" <?php if ($permisos == "SYD") echo 'selected'; ?>><?php echo $llamop4; ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php
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
                $sql = "SELECT terminales.*, flotas.ACRONIMO ";
                $sql = $sql . "FROM terminales, flotas WHERE (terminales.FLOTA = flotas.ID) ";
                if ($permiso == 2) {
                    if (($flota != '') && ($flota != "NN")) {
                        $sql = $sql . "AND (terminales.FLOTA='$flota') ";
                    }
                } /*
                elseif ($permiso == 1) {
                    if (($flota != '') && ($flota != "NN")) {
                        $sql = $sql . "AND (terminales.FLOTA='$flota') ";
                    }
                    else {
                        $sql = $sql . "AND terminales.FLOTA IN (";
                        for ($i = 0; $i < $nflotas; $i++) {
                            $sql = $sql . $flotas[$i];
                            if ($i < ($nflotas - 1)) {
                                $sql = $sql . ",";
                            }
                        }
                    }
                    $sql = $sql . ") ";
                }*/
                else {
                    $sql = $sql . "AND (terminales.FLOTA='$flota') ";
                }
                if (($tei != '') || ($issi != '') || ($nserie != '')) {
                    $amarco = $tipoterm = $marca = $dots = "00";
                    if ($tei != '') {
                        $sql = $sql . "AND (terminales.TEI='$tei') ";
                    }
                    if ($issi != '') {
                        $sql = $sql . "AND (terminales.ISSI='$issi') ";
                    }
                    if ($nserie != '') {
                        $sql = $sql . "AND (terminales.NSERIE='$nserie') ";
                    }
                }
                if (($autentica != '') && ($autentica != "00")) {
                    $sql = $sql . "AND (terminales.AUTENTICADO='$autentica') ";
                }
                if (($estado != '') && ($estado != "00")) {
                    $sql = $sql . "AND (terminales.ESTADO='$estado') ";
                }
                if (($tipoterm != '') && ($tipoterm != "00")) {
                    $sql = $sql . "AND (terminales.TIPO LIKE '$tipoterm') ";
                }
                if (($marca != '') && ($marca != "00")) {
                    $sql = $sql . "AND (terminales.MARCA='$marca') ";
                }
                if (($prov != '') && ($prov != "NN")) {
                    $sql = $sql . "AND (terminales.PROVEEDOR='$prov') ";
                }
                if (($permisos != '') && ($permisos != "00")) {
                    switch ($permisos) {
                        case "NO": {
                            $sql = $sql . "AND (terminales.SEMID='NO') AND (terminales.DUPLEX='NO') ";
                            break;
                        }
                        case "SEMID": {
                            $sql = $sql . "AND (terminales.SEMID='SI') ";
                            break;
                        }
                        case "DUPLEX": {
                            $sql = $sql . "AND (terminales.DUPLEX='SI') ";
                            break;
                        }
                        case "SYD": {
                            $sql = $sql . "AND (terminales.DUPLEX='SI') AND (terminales.SEMID='SI') ";
                            break;
                        }
                    }
                }/*
                if (($estado != '') && ($estado != "00")) {
                    $sql = $sql . "AND (terminales.ESTADO='$estado') ";
                }*/
                if (($carpeta != '') && ($carpeta != "00")) {
                    $sql = $sql . "AND (terminales.CARPETA='".$carpeta."') ";
                }
                if (($dots != '') && ($dots != "00")) {
                    $sql = $sql . "AND (terminales.DOTS='$dots') ";
                }
                if ($orden == 'TEI'){
                    $ordena = 'TEI';
                }
                else{
                    $ordena = 'ISSI';
                }
                $sql_no_limit = $sql . "ORDER BY flotas.ACRONIMO ASC, terminales.".$ordena." ASC";
                $sql_term = $sql_no_limit . " LIMIT " . $inicio . "," . $tam_pagina . ";";
                $res = mysql_query($sql_no_limit) or die(mysql_error());
                $nterm = mysql_num_rows($res);
                $total_pag = ceil($nterm / $tam_pagina);
                $linkpdf = "document.exportar.formato.value='pdf';document.exportar.submit()";
                $linkxls = "document.exportar.formato.value='xls';document.exportar.submit()";
?>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nterm; ?></b>.</td>
                    <td class="borde">
			Mostrar:
                        <select name='tam_pagina' onChange='document.criterios.submit();'>
                            <option value="30" <?php if (($tam_pagina == 30) || ($tam_pagina == "")) echo 'selected'; ?>>30</option>
                            <option value="50" <?php if ($tam_pagina == 50) echo 'selected'; ?>>50</option>
                            <option value="100" <?php if ($tam_pagina == 100) echo 'selected'; ?>>50</option>
                            <option value="<?php echo $nterm; ?>" <?php if ($tam_pagina == $nterm) echo 'selected'; ?>><?php echo $todos; ?></option>
                        </select> <?php echo $regpg; ?>
                    </td>
<?php
                    if ($total_pag > 1) {
?>
                        <td class="borde">
                            <?php echo $pgtxt; ?> <select name='pagina' onChange='document.criterios.submit();'>
<?php
                            for ($k = 1; $k <= $total_pag; $k++){
?>
                                <option value="<?php echo $k; ?>" <?php if ($pagina == $k) echo 'selected'; ?>><?php echo $k; ?></option>
<?php
                            }
?>
                            </select>
                        </td>
<?php
                    }
                    if ($permiso == 2) {
?>
                        <td class="borde">
                                <a href="#" onclick="document.newterminal.submit();"><img src="imagenes/nueva.png" alt="<?php echo $newter; ?>"></a> &mdash; <?php echo $newter; ?>
                        </td>
            <?php
                    }
                    if (($nterm > 0)&&(($flota != '') && ($flota != "NN"))) {
            ?>
                        <td class="borde">
                            <a href="#" onclick="<?php echo $linkpdf; ?>"><img src="imagenes/pdf.png" alt="PDF" title="PDF"></a> -
                            <a href="#" onclick="<?php echo $linkxls; ?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
                        </td>
            <?php
                    }
            ?>
                </tr>
            </table>
        </form>
        <form name="newterminal" action="nuevo_terminal.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $flota;?>">
        </form>
        <form name="termdet" action="detalle_terminal.php" method="POST">
            <input type="hidden" name="idterm" value="">
        </form>
        <form name="exportar" action="expterminales.php" method="POST" target="_blank">
            <input type="hidden" name="formato" value="#">
            <input type="hidden" name="flota" value="<?php echo $flota;?>">
            <input type="hidden" name="tei" value="<?php echo $tei;?>">
            <input type="hidden" name="issi" value="<?php echo $issi;?>">
            <input type="hidden" name="nserie" value="<?php echo $nserie;?>">
            <input type="hidden" name="tipoterm" value="<?php echo $tipoterm;?>">
            <input type="hidden" name="marca" value="<?php echo $marca;?>">
            <input type="hidden" name="estado" value="00">
            <input type="hidden" name="carpeta" value="<?php echo $carpeta;?>">
            <input type="hidden" name="amarco" value="<?php echo $amarco;?>">
            <input type="hidden" name="autentica" value="<?php echo $autentica;?>">
            <input type="hidden" name="permisos" value="<?php echo $permisos;?>">
        <table>
<?php
            //$linkxls = "&marca=$marca&estado=$estado&amarco=$amarco&dots=$dots&permisos=$permisos";
            if ($nterm == 0) {
?>
            <tr><td class='borde'><?php echo $noreg;?></td></tr>
<?php
            }
            else {
                $res_term = mysql_query($sql_term) or die(mysql_error());
                $nterm2 = mysql_num_rows($res_term);
                //*TABLA CON RESULTADOS*//
?>
            <tr>
<?php
                $ncampos = count($campos);
                for ($i = 0; $i < $ncampos; $i++) {
                    if (($i == 5)||($i == 7)){
                        if ($i == 5){
                            $critord = "ISSI";
                        }
                        else{
                            $critord = "TEI";
                        }
                        $linkord = "document.criterios.orden.value='".$critord."';document.criterios.submit();";
?>
                        <th>
                            <a href="#" onclick="<?php echo $linkord;?>"><?php echo $campos[$i];?></a>
                            <?php
                            if ($i == 5){
                            ?>
                                &mdash; <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();">
                            <?php
                            }
                            ?>
                        </th>
<?php
                    }
                    else{
?>
                    <th><?php echo $campos[$i];?></th>
<?php
                    }
                }
?>
            </tr>
<?php
            for ($i = 0; $i < $nterm2; $i++) {
                $row_term = mysql_fetch_array($res_term);
                $idt = $row_term["ID"];
                $linkterm = "document.termdet.idterm.value='$idt';document.termdet.submit();";
?>
                <tr <?php if (($i % 2) == 1)  echo " class='filapar'" ?>>
                    <td><?php echo $row_term["ACRONIMO"];?></td>
                    <td><?php echo $row_term["MARCA"];?></td>
                    <td><?php echo $row_term["MODELO"];?></td>
                    <td><?php echo $row_term["TIPO"];?></td>
                    <td><?php echo $row_term["PROVEEDOR"];?></td>
                    <td class='centro'>
                        <?php echo $row_term["ISSI"];?> -
                        <a href='#' onclick="<?php echo $linkterm;?>"><img src='imagenes/detalle.png' alt="<?php echo $detalle;?>" title="<?php echo $detalle;?>"></a> -
                        <input type="checkbox" name="issiterm[]" value="<?php echo $row_term["ID"]; ?>" />
                    </td>
                    <td><?php echo $row_term["TEI"];?></td>
<?php
                    $duplex = "N";
                    if ($row_term["DUPLEX"] == "SI") {
                        $duplex = "D";
                        if ($row_term["SEMID"] == "SI") {
                            $duplex = "D + S";
                        }
                    }
                    else {
                        if ($row_term["SEMID"] == "SI") {
                            $duplex = "S";
                        }
                    }
?>
                    <td><?php echo $row_term["MNEMONICO"];?></td>
                    <td><?php echo $row_term["CARPETA"];?></td>
                    <td class='centro'><?php echo $duplex;?></td>
                    <td><?php echo $row_term["DOTS"];?></td>
                    <td><?php echo $row_term["AUTENTICADO"];?></td>
                    <td><?php echo $row_term["VERSION"];?></td>
                </tr>
<?php
            }  //primer for
        } //else if ($nterm == 0)
?>
        </table>
        </form>
    </body>
</html>
