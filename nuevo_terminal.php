<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termnew_$idioma.php";
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

$txt_flota = "";
if ($idflota != "") {
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota);
    $row_flota = mysql_fetch_array($res_flota);
    $id_flota = $row_flota["ID"];
    $txt_flota = " de la Flota ".$row_flota["FLOTA"]." (".$row_flota["ACRONIMO"].")";
    $sql_marca = "SELECT DISTINCT MARCA FROM terminales ORDER BY MARCA ASC";
    $res_marca = mysql_query($sql_marca, $link) or die(mysql_error());
    $nmarca = mysql_num_rows($res_marca);
    $sql_modelo = "SELECT DISTINCT MODELO FROM terminales ORDER BY MODELO ASC";
    $res_modelo = mysql_query($sql_modelo, $link) or die(mysql_error());
    $nmodelo = mysql_num_rows($res_modelo);
    $sql_prov = "SELECT DISTINCT PROVEEDOR FROM terminales ORDER BY PROVEEDOR ASC";
    $res_prov = mysql_query($sql_prov, $link) or die(mysql_error());
    $nprov = mysql_num_rows($res_prov);
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
if ($permiso == 2) {
?>
        <h1><?php echo $h1; ?><?php echo $txt_flota; ?></h1>
        <form action="update_terminal.php" method="POST" name="formterminal">
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
                            <option value="F"><?php echo $fijo; ?></option>
                            <option value="M"><?php echo $movil; ?></option>
                            <option value="MB"><?php echo "- $movilb"; ?></option>
                            <option value="MA"><?php echo "- $movila"; ?></option>
                            <option value="MG"><?php echo "- $movilg"; ?></option>
                            <option value="P"><?php echo $portatilb; ?></option>
                            <option value="PB"><?php echo "- $portatilb"; ?></option>
                            <option value="PA"><?php echo "- $portatila"; ?></option>
                            <option value="PX"><?php echo "- $portatilx"; ?></option>
                            <option value="D"><?php echo $despacho; ?></option>
                        </select>
                    </td>
                    <td class="centro">
                        <select name="marca">
                            <option value="NN">Seleccionar</option>
                            <?php
                            for ($i = 0; $i < $nmarca; $i++) {
                                $row_marca = mysql_fetch_array($res_marca);
                                if ($row_marca[0] != "") {
                            ?>
                                    <option value="<?php echo $row_marca[0];?>"><?php echo $row_marca[0];?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td class="centro">
                        <select name="modelo">
                            <option value="NN">Seleccionar</option>
                            <?php
                            for ($i = 0; $i < $nmodelo; $i++) {
                                $row_modelo = mysql_fetch_array($res_modelo);
                                if ($row_modelo[0] != "") {
                            ?>
                                    <option value="<?php echo $row_modelo[0];?>"><?php echo $row_modelo[0];?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td class="centro">
                        <select name="proveedor">
                            <option value="NN">Seleccionar</option>
                            <?php
                            for ($i = 0; $i < $nprov; $i++) {
                                $row_prov = mysql_fetch_array($res_prov);
                                if ($row_prov[0] != "") {
                            ?>
                                    <option value="<?php echo $row_prov[0];?>"><?php echo $row_prov[0];?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td class="centro">
                        <select name="am">
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </td>
                    <td class="dots">
                        <select name="dots">
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </td>
                </tr>
            </table>
            <h2><?php echo $h2flota; ?></h2>
            <table>
                <tr>
                    <th><?php echo $thflota; ?></th>
                    <td>
                        <select name="flota">
<?php
                        $sql_flotas = "SELECT * from flotas ORDER BY FLOTA ASC";
                        $res_flotas = mysql_query($sql_flotas) or die("Error en la consulta de Flotas usuarias: " . mysql_error());
                        $nflotas = mysql_num_rows($res_flotas);
                        for ($j = 1; $j <= $nflotas; $j++) {
                            $row_flotas = mysql_fetch_array($res_flotas);
                            $id_flota = $row_flotas["ID"];
?>
                            <option value="<?php echo $id_flota; ?>" <?php if ($id_flota == $idflota) echo 'selected'; ?>><?php echo $row_flotas["FLOTA"]; ?></option>
<?php
                        }
?>
                        </select>
                    </td>
                </tr>
            </table>
            <h2><?php echo $h2term; ?></h2>
            <table>
                <tr class="filapar">
                    <th class="t4c">ISSI</th>
                    <td><input type="text" name="issi" size="20" value=""></td>
                    <th class="t4c">TEI</th>
                    <td><input type="text" name="tei" size="20" value=""></td>
                </tr>
                <tr>
                    <th class="t4c"><?php echo $cdhw; ?></th>
                    <td><input type="text" name="codigohw" size="20" value=""></td>
                    <th class="t4c"><?php echo $nserie; ?></th>
                    <td><input type="text" name="nserie" size="20" value=""></td>
                </tr>
                <tr class="filapar">
                    <th class="t4c"><?php echo $mnemo; ?></th>
                    <td><input type="text" name="mnemonico" size="20" value=""></td>
                    <th class="t4c"><?php echo $estadotxt; ?></th>
                    <td>
                        <select name="estado">
                            <option value="A"><?php echo $alta; ?></option>
                            <option value="B"><?php echo $baja; ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="t4c"><?php echo $llamada; ?> Semi-Dúplex</th>
                    <td>
                        <select name="semid">
                            <option value="SI">Sí</option>
                            <option value="NO" selected>No</option>
                        </select>
                    </td>
                    <th class="t4c"><?php echo $llamada; ?> Dúplex</th>
                    <td>
                        <select name="duplex">
                            <option value="SI">Sí</option>
                            <option value="NO" selected>No</option>
                        </select>
                    </td>
                </tr>
                <tr class="filapar">
                    <th class="t4c"><?php echo $autent; ?></th>
                    <td>
                        <select name="autenticado">
                            <option value="NO">No</option>
                            <option value="SI" selected>Sí</option>
                        </select>
                    </td>
                    <th class="t4c"><?php echo $encripta; ?></th>
                    <td>
                        <select name="encriptado">
                            <option value="NO">No</option>
                            <option value="SI">Sí</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="t4c"><?php echo $dirip; ?></th>
                    <td>
                        <input type="text" name="ipa" size="3" maxlength="3" value="">.<input type="text" name="ipb" size="3" maxlength="3" value="">.<input type="text" name="ipc" size="3" maxlength="3" value="">.<input type="text" name="ipd" size="3" maxlength="3" value="">
                    </td>
                    <th class="t4c">Carpeta</th>
                    <td><input type="text" name="carpeta" size="20" value=""></td>
                </tr>
                <tr class="filapar">
                    <th class="t4c"><?php echo $observ; ?></th>
                    <td>
                        <input type="text" name="observaciones" size="40" value="">
                    </td>
                    <th class="t4c">Número K</th>
                    <td><input type="text" name="numerok" size="20" value=""></td>
                </tr>
            </table>
            <input type="hidden" name="origen" value="nuevo">
            <table>
                <tr>
                    <td class="borde">
                        <input type='image' name='action' src='imagenes/guardar.png' alt='<?php echo $botguarda; ?>' title="<?php echo $botguarda; ?>">
                        <br><?php echo $botguarda; ?>
                    </td>
                    <td class="borde">
                        <a href='terminales.php'>
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title="<?php echo $botatras; ?>">
                        </a><br><?php echo $botatras; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick='document.formterminal.reset();'>
                            <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title="<?php echo $botcancel; ?>">
                        </a><br><?php echo $botcancel; ?>
                    </td>
                </tr>
            </table>
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
