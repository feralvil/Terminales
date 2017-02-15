<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotabuz_$idioma.php";
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
        <script type="text/javascript" src="js/jquery.js"></script>
        <!-- Funciones JQUERY -->
        <script type="text/javascript">
            $(function(){
                // Botón Cancelar:
                $("a#botcancel").click(function(){
                    document.getElementById("buzonflota").reset();
                });
                // Botón Guardar:
                $("a#botguarda").click(function(){
                    var enviar = false;
                    var idbuzon = $("select#selbuzon").val();
                    var idrol = $("select#selrol").val();
                    if (idbuzon === "NN"){
                        $("select#selbuzon").focus();
                        alert("NO BUZÓN = " + idbuzon);
                    }
                    else if(idrol === "NN"){
                        $("select#selrol").focus();
                        alert("NO ROL = " + idrol);
                    }
                    else{
                        //alert("BUZÓN = " + idbuzon + " - ROL = " + idrol);
                        $("form#buzonflota").submit();
                    }
                });
            });
        </script>        
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
    // Datos de la tabla Buzones-Flotas:
    $sql_buzflota = "SELECT flotas_buzons.ID AS ID, buzons.ID AS BUZON_ID, buzons.NOMBRE, flotas_buzons.ROL ";
    $sql_buzflota .= "FROM flotas_buzons, buzons WHERE (flotas_buzons.BUZON_ID = buzons.ID) ";
    $sql_buzflota .= "AND (flotas_buzons.ID = '$buzon_id')";
    $res_buzflota = mysql_query($sql_buzflota) or die("Error en la consulta del buzón de la flota" . mysql_error());
    $nbuzflota = mysql_num_rows($res_buzflota);
    //datos de la tabla Municipios
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
        <h1>Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
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
        <form name="buzonflota" id="buzonflota" method="POST" action="update_buzflot.php">
            <input type="hidden" name="origen" value="editar">
            <input type="hidden" name="referer" value="editar_flotabuz">
            <input type="hidden" name="flota_id" value="<?php echo $idflota; ?>">
            <input type="hidden" name="buzflota_id" value="<?php echo $buzon_id; ?>">
            <h2><?php echo $h2dots; ?></h2>
            <?php
            if ($nbuzflota > 0){
                $buzflota = mysql_fetch_array($res_buzflota);                
            ?>
                <input type="hidden" name="buzon_id" value="<?php echo $buzflota['BUZON_ID']; ?>">
                <table>
                    <tr>
                        <th class="t5c"><?php echo $thbuzon; ?></th>
                        <th class="t5c"><?php echo $throl; ?></th>
                    </tr>
                    <tr>
                        <td><?php echo $buzflota['NOMBRE']; ?></td>
                        <td>
                            <select id="selrol" name="rol">
                                <option value = "P" <?php if($buzflota['ROL'] == 'P') {echo 'selected';} ?>>
                                    <?php echo $optprop; ?>
                                </option>
                                <option value = "A" <?php if($buzflota['ROL'] == 'A') {echo 'selected';} ?>>
                                    <?php echo $optasoc; ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>
            <?php
            }
            else{
            ?>
                <p class="error">Error: <strong><?php echo $errnobuz; ?></strong></p>
            <?php
            }
            ?>
        </form>
        <form name="detflota" action="buzones_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <table>
                <tr>
                    <td class="borde">
                        <a href="#" id="botguarda">
                            <img src="imagenes/guardar.png" alt="<?php echo $botguarda; ?>" title="<?php echo $botguarda; ?>" />
                        </a>
                        <br><?php echo $botguarda; ?> a Flota
                    </td>
                    <td class="borde">
                        <a href="#" id="botcancel">
                            <img src="imagenes/no.png" alt="<?php echo $botcancel; ?>" title="<?php echo $botcancel; ?>" />
                        </a>
                        <br><?php echo $botcancel; ?>
                    </td>
                    <td class="borde">
                        <input type='image' name='action' src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title="<?php echo $botatras; ?>"><br><?php echo $botatras; ?> de Flota
                    </td>
                </tr>
            </table>
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