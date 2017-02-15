<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organizacon_$idioma.php";
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
    //datos de la tabla Organización
    $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
    $res_org = mysql_query($sql_org) or die("Error en la consulta de Organización: " . mysql_error());
    $norg = mysql_num_rows($res_org);
    if ($norg > 0) {
        $organiza = mysql_fetch_array($res_org);
        // Consulta del Municipio
        $idmuni = $organiza['INE'];
        if ($idmuni > 0){
            $sql_muni = "SELECT * FROM municipios WHERE INE = " . $idmuni;
            $res_muni = mysql_query($sql_muni) or die("Error en la consulta de Municipio: " . mysql_error());
            $nmuni = mysql_num_rows($res_muni);
            if ($nmuni > 0){
                $municipio = mysql_fetch_array($res_muni);
            }
        }
        // Consulta del Responsable:
        $idresp = $organiza['RESPONSABLE'];
        $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idresp;
        $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de responsable: " . mysql_error());
        $ncontacto = mysql_num_rows($res_contacto);
        if ($ncontacto > 0){
            $contacto = mysql_fetch_array($res_contacto);
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
        <h1>Organización <?php echo $organiza['ORGANIZACION']; ?></h1>
        <table>
            <tr>
                <th class="t5c"><?php echo $thdomicilio; ?></th>
                <td class="t40p"><?php echo $organiza["DOMICILIO"]; ?></td>
                <th class="t5c"><?php echo $thcp; ?></th>
                <td class="t5c"><?php echo $organiza["CP"]; ?></td>
            </tr>
            <tr class="filapar">
                <th class="t5c"><?php echo $thciudad; ?></th>
                <td class="t40p"><?php echo $municipio["MUNICIPIO"]; ?></td>
                <th class="t5c"><?php echo $thprovincia; ?></th>
                <td class="t5c"><?php echo $municipio["PROVINCIA"]; ?></td>
            </tr>
        </table>
        <h2>
            <?php
            echo $h2cont;
            if ($ncontacto == 0){
            ?>
                &mdash;
                <a href="#" onclick="document.addcont.submit();">
                    <img src="imagenes/nueva.png" alt="<?php echo $botadd;?>" title="<?php echo $botadd;?>">
                </a>
            <?php
            }
            ?>
        </h2>
        <form name="addcont" action="nuevo_contorg.php" method="post">
            <input type="hidden" name="idorg" value="<?php echo $idorg;?>">
        </form>
        <?php
        if ($ncontacto > 0){
            $linkedi = "document.edicont.submit();";
            $linkdel = "document.delcont.submit();";
        ?>
            <form name="edicont" action="editar_contorg.php" method="post">
                <input type="hidden" name="idorg" value="<?php echo $idorg;?>">
                <input type="hidden" name="idcont" value="<?php echo $idresp;?>">
                <input type="hidden" name="rol" value="RES">
            </form>
            <form name="delcont" action="eliminar_contorg.php" method="post">
                <input type="hidden" name="idorg" value="<?php echo $idorg;?>">
                <input type="hidden" name="idcont" value="<?php echo $idresp;?>">
                <input type="hidden" name="rol" value="RES">
            </form>
            <table>
                <tr>
                    <th class="t5c"><?php echo $thnombre; ?></th>
                    <th class="t10c">DNI</th>
                    <th class="t5c"><?php echo $thcargo; ?></th>
                    <th class="t5c"><?php echo $thmail; ?></th>
                    <th class="t5c"><?php echo $thtelef; ?></th>
                    <th class="t10c"><?php echo $thacciones; ?></th>
                </tr>
                <tr>
                    <td><?php echo $contacto['NOMBRE']; ?></td>
                    <td><?php echo $contacto['NIF']; ?></td>
                    <td><?php echo $contacto['CARGO']; ?></td>
                    <td><?php echo $contacto['MAIL']; ?></td>
                    <td><?php echo $contacto['TELEFONO']; ?></td>
                    <td class="centro">
                        <a href="#" onclick="<?php echo $linkedi; ?>">
                            <img src="imagenes/editar.png" alt="Editar" title="Editar"></a>
                        &mdash;
                        <a href="#" onclick="<?php echo $linkdel; ?>">
                            <img src="imagenes/cancelar.png" alt="Eliminar" title="Eliminar">
                        </a>
                    </td>
                </tr>
            </table>
        <?php
        }
        else{
        ?>
            <p class='error'><?php echo $errnocont; ?>
        <?php
        }
        ?>
<?php
    }
    else{
?>
        <p class='error'><?php echo $errnoflota; ?></p>
<?php
    }
?>
            <input type="hidden" name="detalle" value="">
            <input type="hidden" name="editar" value="">
            <input type="hidden" name="borrar" value="">
            <input type="hidden" name="nuevo" value="">
        </form>
        <table>
            <tr>
                <td class="borde">
                    <a href="#" onclick="document.detorg.submit();">
                        <img src='imagenes/atras.png' alt='<?php echo $volver;?>' title="<?php echo $volver;?>">
                    </a><br><?php echo $volver;?>
                </td>
            </tr>
        </table>
        <form name="detorg" action="detalle_organizacion.php" method="POST">
            <input type="hidden" name="idorg" value="<?php echo $idorg; ?>">
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
