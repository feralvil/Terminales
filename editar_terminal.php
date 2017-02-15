<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termedi_$idioma.php";
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
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
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
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Editar Terminal COMDES</title>
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
<?php
    if ($permiso != 0) {
        //datos de la tabla terminales
        $sql_terminal = "SELECT * FROM terminales WHERE ID='$idterm'";
        $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
        $nterminal = mysql_num_rows($res_terminal);
        if ($nterminal == 0) {
?>
            <p class='error'>No hay resultados en la consulta del Terminal</p>
<?php
        }
        else {
            $row_terminal = mysql_fetch_array($res_terminal);
            $id_flota = $row_terminal["FLOTA"];
            $tipo = $row_terminal["TIPO"];
            $am = $row_terminal["AM"];
            $dots = $row_terminal["DOTS"];
        }
        //datos de la tabla flotas
        $sql_flota = "SELECT * FROM flotas WHERE ID='$id_flota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota Usuaria: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota == 0) {
?>
            <p class='error'>No hay resultados en la consulta de la Flota</p>
<?php
        }
        else {
            $row_flota = mysql_fetch_array($res_flota);
        }

        //datos de la tabla municipios
        $ine = $row_flota ["INE"];
        $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
        $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
        $nmun = mysql_num_rows($res_mun);
        if ($nmun == 0) {
?>
            <p class='error'>No hay resultados en la consulta del Municipio</p>
<?php
        }
        else {
            $row_mun = mysql_fetch_array($res_mun);
        }
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
        <h1>Terminal TEI: <?php echo $row_terminal["TEI"]; ?> / ISSI: <?php echo $row_terminal["ISSI"]; ?></h1>
        <form action="update_terminal.php" method="POST" name="formterminal">
            <input type="hidden" name="idterm" value="<?php echo $idterm;?>">
            <input type="hidden" name="origen" value="editar">
        <h2><?php echo $h2admin; ?></h2>
        <table>
            <tr>
                <th class="t6c"><?php echo $tipotxt; ?></th>
                <th class="t6c">Marca</th>
                <th class="t6c"><?php echo $modtxt; ?></th>
                <th class="t6c"><?php echo $proveedor; ?></th>
                <th class="t6c"><?php echo $amtxt; ?></th>
                <th class="t6c"><?php echo $dotstxt; ?></th>
            </tr>
            <tr>
                <td class="centro">
                    <select name="tipo">
                        <option value="F" <?php if ($tipo=="F") echo 'selected'; ?>><?php echo $fijo;?></option>
                        <option value="M" <?php if ($tipo=="M") echo 'selected'; ?>><?php echo $movil;?></option>
                        <option value="MB" <?php if ($tipo=="MB") echo 'selected'; ?>><?php echo "- $movilb";?></option>
                        <option value="MA" <?php if ($tipo=="MA") echo 'selected'; ?>><?php echo "- $movila";?></option>
                        <option value="MG" <?php if ($tipo=="MG") echo 'selected'; ?>><?php echo "- $movilg";?></option>
                        <option value="P" <?php if ($tipo=="P") echo 'selected'; ?>><?php echo $portatilb;?></option>
                        <option value="PB" <?php if ($tipo=="PB") echo 'selected'; ?>><?php echo "- $portatilb";?></option>
                        <option value="PA" <?php if ($tipo=="PA") echo 'selected'; ?>><?php echo "- $portatila";?></option>
                        <option value="PX" <?php if ($tipo=="PX") echo 'selected'; ?>><?php echo "- $portatilx";?></option>
                        <option value="D" <?php if ($tipo=="D") echo 'selected'; ?>><?php echo $despacho;?></option>
                    </select>
                </td>
                <td class="centro">
                    <input type="text" name="marca" size="20" value="<?php echo $row_terminal["MARCA"];?>">
                </td>
                <td class="centro">
                    <input type="text" name="modelo" size="20" value="<?php echo $row_terminal["MODELO"];?>">
                </td>
                <td class="centro">
                    <input type="text" name="proveedor" size="20" value="<?php echo $row_terminal["PROVEEDOR"];?>">
                </td>
                <td class="centro">
                    <select name="am" onChange="document.formulario.submit();">
                        <option value="SI" <?php if ($am=="SI") echo 'selected'; ?>>SI</option>
                        <option value="NO" <?php if ($am=="NO") echo 'selected'; ?>>NO</option>
                    </select>
                </td>
                <td class="centro">
                    <select name="dots" onChange="document.formulario.submit();">
                        <option value="SI" <?php if ($dots=="SI") echo 'selected'; ?>>SI</option>
                        <option value="NO" <?php if ($dots=="NO") echo 'selected'; ?>>NO</option>
                    </select>
                </td>
            </tr>
        </table>
        <h2><?php echo $h2flota; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t10c"><?php echo $acroflota; ?></th>
                <th class="t40p"><?php echo $localiza; ?></th>
                <th class="t10c"><?php echo $irflota; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_flota["DOMICILIO"] . " &mdash; " . $row_flota["CP"] . " " . $row_mun["MUNICIPIO"]; ?></td>
                <td class="centro"><a href="#" onclick="document.detflota.submit();"><img src="imagenes/ir.png" alt="Ir"></a></td>
            </tr>
        </table>
        <h3><?php echo $h3flota; ?></h3>
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
        <h2><?php echo $h2term; ?></h2>
        <table>
            <tr>
                <th class="t4c">ISSI</th>
                <td><input type="text" name="issi" size="10" value="<?php echo $row_terminal["ISSI"];?>"></td>
                <th class="t4c">TEI</th>
                <td><input type="text" name="tei" size="20" value="<?php echo $row_terminal["TEI"];?>"></td>
            </tr>
            <tr class="filapar">
                <th class="t4c"><?php echo $cdhw; ?></th>
                <td><input type="text" name="codigohw" size="20" value="<?php echo $row_terminal["CODIGOHW"];?>"></td>
                <th class="t4c"><?php echo $nserie; ?></th>
                <td><input type="text" name="nserie" size="20" value="<?php echo $row_terminal["NSERIE"];?>"></td>
            </tr>
            <tr>
                <th class="t4c">ID</th>
                <td><?php echo $row_terminal["ID"]; ?></td>
                <th class="t4c"><?php echo $mnemo; ?></th>
                <td><input type="text" name="mnemonico" size="20" value="<?php echo $row_terminal["MNEMONICO"];?>"></td>
            </tr>
            <tr class="filapar">
                <th class="t4c"><?php echo $llamada; ?> Semi-Dúplex</th>
                <td>
                    <select name="semid">
                        <option value="SI" <?php if ($row_terminal["SEMID"]=="SI") echo " selected";?>>SI</option>
                        <option value="NO" <?php if ($row_terminal["SEMID"]=="NO") echo " selected";?>>NO</option>
                    </select>
                </td>
                <th class="t4c"><?php echo $llamada; ?> Dúplex</th>
                <td>
                    <select name="duplex">
                        <option value="SI" <?php if ($row_terminal["DUPLEX"]=="SI") echo " selected";?>>SI</option>
                        <option value="NO" <?php if ($row_terminal["DUPLEX"]=="NO") echo " selected";?>>NO</option>
                    </select>
                </td>
            </tr>
<?php
            switch ($row_terminal["ESTADO"]) {
                case "A": {
                    $estado = $alta;
                    $fecha_nom = $falta;
                    $fecha_val = $row_terminal["FALTA"];
                    $fecha_col = "FALTA";
                    break;
                }
                case "B": {
                    $estado = $baja;
                    $fecha_nom = $fbaja;
                    $fecha_val = $row_terminal["FBAJA"];
                    $fecha_col = "FBAJA";
                    break;
                }
                /*case "R": {
                    // Se busca la incidencia
                    $sql_incid = "SELECT * FROM incidencias WHERE TERMINAL = '$id' ORDER BY ID DESC";
                    $res_incid = mysql_query($sql_incid) or die("Error en la consulta de Incidencia: " . mysql_error());
                    $nincid = mysql_num_rows($res_incid);
                    if ($nincid == 0) {
                        $estado = "<span class='error'>No hay resultados en la consulta de Incidencias</span>\n";
                    }
                    else {
                        $row_incid = mysql_fetch_array($res_incid);
                        $id_incid = $row_incid["ID"];
                        $estado = "$rep - <a href='detalle_incidencia.php?id=$id_incid'><img src='imagenes/consulta.png'></a>";
                        $fecha_val = $row_incid["FAVERIA"];
                    }
                    $fecha_nom = $frep;
                    break;
                }*/
            }
            $aval = substr($fecha_val, 0, 4);
            $mval = substr($fecha_val, 5, 2);
            $dval = substr($fecha_val, 8, 2);
            $anyact = date('Y');
            $arrip = array('','','','');
            if ($row_terminal["DIRIP"] != ""){
                $arrip = explode('.', $row_terminal["DIRIP"]);
            }
?>
            <input type="hidden" name="fecha" size="20" value="<?php echo $fecha_col;?>">
            <tr>
                <th class="t4c"><?php echo $estadotxt; ?></th>
                <td><?php echo $estado; ?></td>
                <th class="t4c"><?php echo $fecha_nom; ?></th>
                <td>
                    <select name="dia">
<?php
                    for ($i = 1; $i <=31; $i++){
                        if ($i < 10){
                            $d = "0$i";
                        }
                        else{
                            $d = "$i";
                        }
?>
                        <option value="<?php echo $d;?>" <?php if ($d == $dval) {echo "selected";}?>><?php echo $d;?></option>
<?php
                    }
?>
                    </select> &mdash;
                    <select name="mes">
<?php
                    for ($i = 1; $i <=12; $i++){
                        if ($i < 10){
                            $m = "0$i";
                        }
                        else{
                            $m = "$i";
                        }
?>
                        <option value="<?php echo $m;?>" <?php if ($m == $mval) {echo "selected";}?>><?php echo $m;?></option>
<?php
                    }
?>
                    </select> &mdash; 
                    <select name="anyo">
<?php
                    for ($i = 2008; $i <= $anyact; $i++){
?>
                        <option value="<?php echo $i;?>" <?php if ($i == $aval) {echo "selected";}?>><?php echo $i;?></option>
<?php
                    }
?>
                    </select>
                </td>
            </tr>
            <tr class="filapar">
                <th class="t4c"><?php echo $autent; ?></th>
                <td>
                    <select name="autenticado">
                        <option value="NO" <?php if ($row_terminal["AUTENTICADO"]=="NO") echo " selected";?>>NO</option>
                        <option value="SI" <?php if ($row_terminal["AUTENTICADO"]=="SI") echo " selected";?>>SI</option>
                    </select>
                </td>
                <th class="t4c"><?php echo $encripta; ?></th>
                <td>
                    <select name="encriptado">
                        <option value="NO" <?php if ($row_terminal["ENCRIPTADO"]=="NO") echo " selected";?>>NO</option>
                        <option value="SI" <?php if ($row_terminal["ENCRIPTADO"]=="SI") echo " selected";?>>SI</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="t4c"><?php echo $dirip; ?></th>
                <td>
                    <input type="text" name="ipa" size="3" maxlength="3" value="<?php echo $arrip[0];?>">.<input type="text" name="ipb" size="3" maxlength="3" value="<?php echo $arrip[1];?>">.<input type="text" name="ipc" size="3" maxlength="3" value="<?php echo $arrip[2];?>">.<input type="text" name="ipd" size="3" maxlength="3" value="<?php echo $arrip[3];?>">
                </td>
                <th class="t4c">Carpeta</th>
                <td><input type="text" name="carpeta" size="20" value="<?php echo $row_terminal["CARPETA"];?>"></td>
            </tr>
            <tr class="filapar">
                <th class="t4c"><?php echo $observ; ?></th>
                <td><input type="text" name="observaciones" size="40" value="<?php echo $row_terminal["OBSERVACIONES"]; ?>"></td>
                <th class="t4c">Número K</th>
                <td><input type="text" name="numk" size="35" value="<?php echo $row_terminal["NUMEROK"];?>"></td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="borde">
                    <input type='image' name='action' src='imagenes/guardar.png' alt='<?php echo $botguarda;?>' title="<?php echo $botguarda;?>">
                    <br><?php echo $botguarda;?>
                </td>
                <td class="borde">
                    <a href='#' onclick="document.detterm.submit();">
                        <img src='imagenes/atras.png' alt='<?php echo $botatras;?>' title="<?php echo $botatras;?>">
                    </a><br><?php echo $botatras;?>
                </td>
                <td class="borde">
                    <a href='#' onclick='document.formterminal.reset();'>
                        <img src='imagenes/no.png' alt='<?php echo $botcancel;?>' title="<?php echo $botcancel;?>">
                    </a><br><?php echo $botcancel;?>
                </td>
            </tr>
	</table>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $id_flota;?>">
        </form>
        <form name="detterm" action="detalle_terminal.php" method="POST">
            <input type="hidden" name="idterm" value="<?php echo $idterm;?>">
        </form>
<?php
    }
    else {
?>
        <h1><?php echo $h1perm;?></h1>
	<p class='error'><?php echo $permno;?></p>
<?php
    }
?>
    </body>
</html>
