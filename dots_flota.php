<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotadots_$idioma.php";
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
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
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
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
?>
        <p class='error'><?php echo $errnflota; ?></p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $usuario = $row_flota["LOGIN"];
    }
    //datos de la tabla Terminales
    $sql_term = "SELECT * FROM terminales WHERE FLOTA='$idflota' ORDER BY ISSI ASC";
    $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminales" . mysql_error());
    $nterm = mysql_num_rows($res_term);
    //datos de la tabla Municipio
    // INE
    $ine = $row_flota["INE"];
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
?>
        <p class='error'><?php echo $errnomun; ?></p>
<?php
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
        $municipio = $row_mun["MUNICIPIO"];
    }
?>
         <form name="dotsflota" method="POST" action="dots_flota.php" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
            <h1>
                Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>) &mdash;
                <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $newtab;?>' title="<?php echo $newtab;?>">
                &mdash; &nbsp; <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" /> Seleccionar
            </h1>
        </form>
        <h2><?php echo $h2admin; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t5c"><?php echo $acroflota; ?></th>
                <th class="t5c"><?php echo $ciudad; ?></th>
                <th class="t5c"><?php echo $provincia; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_mun["MUNICIPIO"]; ?></td>
                <td><?php echo $row_mun["PROVINCIA"]; ?></td>
            </tr>
        </table>
        <form name="terminales" method="POST" action="xlsdotsflota.php" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
            <h2>
                <?php echo sprintf($termdots, $nterm);?> &mdash;
                <input type='image' name='action' src='imagenes/xls.png' alt='Exportar a Excel' title="Exportar a Excel">
            </h2>
<?php
        //datos de la tabla Terminales - Obtenemos las marcas
        $sql_marca = "SELECT DISTINCT MARCA FROM terminales WHERE FLOTA='$idflota' ORDER BY MARCA ASC";
        $res_marca = mysql_query($sql_marca) or die("Error en la consulta de Marca" . mysql_error());
        $nmarca = mysql_num_rows($res_marca);
        if ($nmarca == 0){
?>
            <p class='error'><?php echo $errnomarca; ?></p>
<?php
        }
        else{
            for ($i = 0; $i < $nmarca; $i++){
                $row_marca = mysql_fetch_array($res_marca);
                $marca = $row_marca[0];
                //Datos de la tabla Terminales - Obtenemos los modelos
                $sql_modelo = "SELECT DISTINCT MODELO FROM terminales WHERE FLOTA='$idflota' AND MARCA='$marca' ORDER BY MODELO ASC";
                $res_modelo = mysql_query($sql_modelo) or die("Error en la consulta de Modelo" . mysql_error());
                $nmodelo = mysql_num_rows($res_modelo);
                if ($nmodelo == 0){
?>
                    <p class='error'><?php echo $errnomodelo; ?></p>
<?php
                }
                else{
                    for ($j = 0; $j < $nmodelo; $j++){
                        $row_modelo = mysql_fetch_array($res_modelo);
                        $modelo = $row_modelo[0];
                        $sql_terminal = "SELECT * FROM terminales WHERE FLOTA='$idflota' AND MARCA='$marca' AND MODELO='$modelo' ORDER BY ISSI ASC";
                        $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta (parcial) de Terminal" . mysql_error());
                        $nterminal = mysql_num_rows($res_terminal);
?>
                    <h3><?php echo sprintf($h3tabla, $marca, $modelo, $nterminal) ;?></h3>
<?php
                        if ($nterminal == 0){
?>
                            <p class='error'><?php echo $errnoterm; ?></p>
<?php
                        }
                        else{
?>
                            <table>
                                <tr>
                                    <th>Seleccionar</th>
                                    <th><?php echo $detalle; ?></th>
                                    <th>ISSI</th>
                                    <th><?php echo $thterm; ?></th>
                                </tr>
<?php
                            for ($k = 0; $k < $nterminal; $k++){
                                $row_terminal = mysql_fetch_array($res_terminal);
                                $idterm = $row_terminal["ID"];
                                $linkterm = "document.detterm.idterm.value='$idterm';document.detterm.submit();";
                                $encabezado = strtoupper($row_flota["ACRONIMO"]."-".$row_terminal["PROVEEDOR"]."-".$row_terminal["MNEMONICO"]);
?>
                                <tr <?php if (($k % 2)== 1) {echo "class='filapar'";}?>>
                                    <td class='centro'>
                                        <input type="checkbox" name="termsel[]" value="<?php echo $idterm;?>" />
                                    </td>
                                    <td class="centro">
                                        <a href='#' onclick="<?php echo $linkterm;?>"><img src='imagenes/consulta.png' alt="Consulta" title="Consulta"></a>
                                    </td>
                                    <td class="centro"><?php echo $row_terminal["ISSI"]; ?></td>
                                    <td><?php echo $encabezado; ?></td>
                                </tr>
<?php
                            }
?>
                            </table>
<?php
                        }
                    }
                }
            }
        }
?>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <table>
                <tr>
                    <td class="borde">
                        <input type='image' name='action' src='imagenes/atras.png' alt='<?php echo $detalle; ?>' title="<?php echo $detalle; ?>"><br><?php echo $detalle; ?> de Flota
                    </td>
                </tr>
            </table>
        </form>
        <form name="detterm" action="detalle_terminal.php" method="POST">
            <input type="hidden" name="idterm" value="#">
        </form>
<?php
}
else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
}
?>
    </body>
</html>
