<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termdet_$idioma.php";
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
import_request_variables("p", "");

/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación (Oficina COMDES)
 */
// Obtenemos el usuario
include_once('auth_user.php');

//datos de la tabla terminales
$sql_terminal = "SELECT * FROM terminales WHERE ID='$idterm'";
$res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
$nterminal = mysql_num_rows($res_terminal);
if ($nterminal > 0) {
    $row_terminal = mysql_fetch_array($res_terminal);
    $id_flota = $row_terminal["FLOTA"];
}

/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación
 */
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
else {
    if ($flota_usu == $id_flota) {
        $permiso = 1;
    }
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
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
    if ($permiso > 0) {
        if ($nterminal == 0) {
?>
            <p class='error'>No hay resultados en la consulta del Terminal: <?php echo $nterminal; ?></p>
<?php
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
        //datos de la tabla terminales para dar siguiente y previo
        $sql_pre = "SELECT ID FROM terminales WHERE (FLOTA = $id_flota) AND (ISSI < '" . $row_terminal["ISSI"] . "') ORDER BY ISSI DESC LIMIT 1";
        $res_pre = mysql_query($sql_pre) or die("Error en la consulta del Terminal previo: " . mysql_error());
        $npre = mysql_num_rows($res_pre);
        if ($npre > 0) {
            $row_pre = mysql_fetch_array($res_pre);
            $idpre = $row_pre["ID"];
            $linkpre = "document.formpresig.idterm.value='$idpre';document.formpresig.submit()";
        }
        $sql_sig = "SELECT ID FROM terminales WHERE (FLOTA = $id_flota) AND (ISSI > '" . $row_terminal["ISSI"] . "') ORDER BY ISSI ASC LIMIT 1";
        $res_sig = mysql_query($sql_sig) or die("Error en la consulta del Terminal siguiente: " . mysql_error());
        $nsig = mysql_num_rows($res_sig);
        if ($nsig > 0) {
            $row_sig = mysql_fetch_array($res_sig);
            $idsig = $row_sig["ID"];
            $linksig = "document.formpresig.idterm.value='$idsig';document.formpresig.submit()";
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
        //datos de la tabla bases
        $sql_base = "SELECT * FROM bases WHERE TERMINAL='$idterm'";
        $res_base = mysql_query($sql_base) or die("Error en la consulta de Bases: " . mysql_error());
        $nbase = mysql_num_rows($res_base);

        ############# Enlaces para la exportación #######
        $linkpdf = "document.exportar.action='pdfterminal.php';document.exportar.submit();";
        $linkxls = "document.exportar.action='xlsterminal.php';document.exportar.submit();";

?>
        <form name="exportar" action="#" method="POST" target="_blank">
            <input type="hidden" name="idterm" value="<?php echo $idterm;?>">
        </form>
        <form name="formpresig" action="detalle_terminal.php" method="POST">
            <input type="hidden" name="idterm" value="#">
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $id_flota;?>">
        </form>
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
        <form name="buscaterm" action="busca_terminal.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $id_flota;?>">
            <input type="hidden" name="prevterm" value="<?php echo $idterm;?>">
        <h1>
            Terminal TEI: <?php echo $row_terminal["TEI"]; ?> / ISSI: <?php echo $row_terminal["ISSI"]; ?> &mdash;
            <a href="#" onclick="<?php echo $linkpdf; ?>"><img src="imagenes/pdf.png" alt="PDF" title="PDF"></a> &mdash;
            <a href="#" onclick="<?php echo $linkxls; ?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
<?php
        if ($npre > 0) {
?>
            &mdash; <a href="#" onclick="<?php echo $linkpre; ?>"><img src="imagenes/pre.png" alt="<?php echo $previ; ?>" title="<?php echo $previ; ?>"></a>
<?php
        }
        if ($nsig > 0) {
?>
            &mdash; <a href="#" onclick="<?php echo $linksig; ?>"><img src="imagenes/sig.png" alt="<?php echo $seg; ?>" title="<?php echo $seg; ?>"></a>
            <?php
        }
            ?>
            &mdash; ISSI:&#160;<input type="text" name="issi" size="6">&#160;<input type='image' name='action' src="imagenes/consulta.png" alt="Buscar" title="Buscar">
        </h1>
        </form>
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
<?php
            $tipo = $row_terminal["TIPO"];
            switch ($tipo) {
                case ("F"): {
                        $tipo = $fijo;
                        break;
                }
                case ("M"): {
                        $tipo = $movil;
                        break;
                }
                case ("MB"): {
                        $tipo = $movilb;
                        break;
                }
                case ("MA"): {
                        $tipo = $movila;
                        break;
                }
                case ("MG"): {
                        $tipo = $movilg;
                        break;
                }
                case ("P"): {
                        $tipo = $portatil;
                        break;
                }
                case ("PB"): {
                        $tipo = $portatilb;
                        break;
                }
                case ("PA"): {
                        $tipo = $portatila;
                        break;
                }
                case ("PX"): {
                        $tipo = $portatilx;
                        break;
                }
                case ("D"): {
                        $tipo = $despacho;
                        break;
                }
            }
?>
                <td class="centro"><?php echo $tipo; ?></td>
                <td class="centro"><?php echo $row_terminal["MARCA"]; ?></td>
                <td class="centro"><?php echo $row_terminal["MODELO"]; ?></td>
                <td class="centro"><?php echo $row_terminal["PROVEEDOR"]; ?></td>
                <td class="centro"><?php echo $row_terminal["AM"]; ?></td>
                <td class="centro"><?php echo $row_terminal["DOTS"]; ?></td>
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
                <td><?php echo $row_terminal["ISSI"]; ?></td>
                <th class="t4c">TEI</th>
                <td><?php echo $row_terminal["TEI"]; ?></td>
            </tr>
            <tr class="filapar">
                <th class="t4c"><?php echo $cdhw; ?></th>
                <td><?php echo $row_terminal["CODIGOHW"]; ?></td>
                <th class="t4c"><?php echo $nserie; ?></th>
                <td><?php echo $row_terminal["NSERIE"]; ?></td>
            </tr>
            <tr>
                <th class="t4c">ID</th>
                <td><?php echo $row_terminal["ID"]; ?></td>
                <th class="t4c"><?php echo $mnemo; ?></th>
                <td><?php echo $row_terminal["MNEMONICO"]; ?></td>
            </tr>
            <tr class="filapar">
                <th class="t4c"><?php echo $llamada; ?> Semi-Dúplex</th>
                <td><?php echo $row_terminal["SEMID"]; ?></td>
                <th class="t4c"><?php echo $llamada; ?> Dúplex</th>
                <td><?php echo $row_terminal["DUPLEX"]; ?></td>
            </tr>
<?php
            switch ($row_terminal["ESTADO"]) {
                case "A": {
                    $estado = $alta;
                    $fecha_nom = $falta;
                    $fecha_val = $row_terminal["FALTA"];
                    break;
                }
                case "B": {
                    $estado = $baja;
                    $fecha_nom = $fbaja;
                    $fecha_val = $row_terminal["FBAJA"];
                    break;
                }
            }
?>
            <tr>
                <th class="t4c"><?php echo $estadotxt; ?></th>
                <td><?php echo $estado; ?></td>
                <th class="t4c"><?php echo $fecha_nom; ?></th>
                <td><?php echo $fecha_val; ?></td>
            </tr>
            <tr class="filapar">
                <th class="t4c"><?php echo $autent; ?></th>
                <td><?php echo $row_terminal["AUTENTICADO"]; ?></td>
                <th class="t4c"><?php echo $encripta; ?></th>
                <td><?php echo $row_terminal["ENCRIPTADO"]; ?></td>
            </tr>
            <tr>
                <th class="t4c"><?php echo $dirip; ?></th>
                <td><?php echo $row_terminal["DIRIP"]; ?></td>
                <th class="t4c">Carpeta</th>
                <td><?php echo $row_terminal["CARPETA"]; ?></td>
            </tr>
<?php
            if ($flota_usu == 100) {
?>
                <tr class="filapar">
                    <th class="t4c"><?php echo $observ; ?></th>
                    <td><?php echo $row_terminal["OBSERVACIONES"]; ?></td>
                    <th class="t4c">Número K</th>
                    <td><?php echo $row_terminal["NUMEROK"]; ?></td>
                </tr>
<?php
            }
            else {
?>
                <tr class="filapar">
                    <th class="t4c"><?php echo $observ; ?></th>
                    <td colspan='3'><?php echo $row_terminal["OBSERVACIONES"]; ?></td>
                </tr>
<?php
            }
?>
            </table>
<?php
            if ($permiso == 2) {
                $linkedit = "document.accterm.action='editar_terminal.php';document.accterm.submit();";
                $linkbase = "document.accterm.action='editar_terminal.php';document.accterm.submit();";
                $linknumk = "document.accterm.action='ref_terminal.php';document.getElementById('param').value='numk';document.accterm.submit();";
                $linkitsi = "document.accterm.action='ref_terminal.php';document.getElementById('param').value='itsi';document.accterm.submit();";
                $linkdel = "document.accterm.action='eliminar_terminal.php';document.accterm.submit();";
                if ($row_terminal["ESTADO"] == "B") {
                    $linkayb = "document.accterm.action='alta_terminal.php';document.accterm.submit();";
                    $imgayb = "imagenes/nuevo.png";
                    $txtayb = $botalta;
                }
                else {
                    $linkayb = "document.accterm.action='baja_terminal.php';document.accterm.submit();";
                    $imgayb = "imagenes/eliminar.png";
                    $txtayb = $botbaja;
                }
                if ($nbase == 0) {
                    $linkbase = "document.accterm.action='base_terminal.php';document.accterm.accion.value='add';document.accterm.submit();";
                    $imgbase = "imagenes/base_add.png";
                    $txtbase = $baseadd;
                }
                else {
                    $linkbase = "document.accterm.action='base_terminal.php';document.accterm.accion.value='del';document.accterm.submit();";
                    $imgbase = "imagenes/base_del.png";
                    $txtbase = $basedel;
                }
?>
                <form name="accterm" action="#" method="POST">
                    <input type="hidden" name="idterm" value="<?php echo $idterm;?>">
                    <input type="hidden" name="accion" value="#">
                    <input type="hidden" name="parametro" id="param" value="numk">
                </form>
                <form name="termflota" action="terminales.php" method="POST">
                    <input type="hidden" name="flota" value="<?php echo $id_flota;?>">
                </form>
                <table>
                    <tr>

                    <td class="borde">
                        <a href='#' onclick="<?php echo $linkedit;?>">
                            <img src='imagenes/pencil.png' alt='Editar'>
                        </a><br>Editar Terminal
                    </td>
                    <td class="borde">
                        <a href='#' onclick="<?php echo $linkayb; ?>">
                            <img src='<?php echo $imgayb; ?>' alt='<?php echo $txtayb; ?>'>
                        </a><br><?php echo $txtayb; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="<?php echo $linkbase; ?>">
                            <img src='<?php echo $imgbase; ?>' alt='<?php echo $txtbase; ?>'>
                        </a><br><?php echo $txtbase; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="<?php echo $linkdel; ?>">
                            <img src='imagenes/no.png' alt='Eliminar Terminal'>
                        </a><br>Eliminar Terminal
                    </td>
                    <td class="borde">
                        <a href='#' onclick="<?php echo $linknumk; ?>">
                            <img src='imagenes/akdc.png' alt='REF-K' title='REF-K'>
                        </a><br>Generar REF-K
                    </td>
                    <td class="borde">
                        <a href='#' onclick="<?php echo $linkitsi; ?>">
                            <img src='imagenes/akdc.png' alt='REF-ITSI' title='REF-ITSI'>
                        </a><br>Generar REF-ITSI
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.termflota.submit();">
                            <img src='imagenes/lista.png' alt='<?php echo $txtterm; ?>' title='<?php echo $txtterm; ?>'>
                        </a><br><?php echo $txtterm; ?>
                    </td>
                </tr>
            </table>
<?php
            }
    }
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
    }
?>
    </body>
</html>
