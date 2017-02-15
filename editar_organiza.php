<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organizaedi_$idioma.php";
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
<?php
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
    <?php
    if ($permiso > 1){
        //datos de la tabla Organizaciones:
        $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
        $res_org = mysql_query($sql_org) or die("Error en la consulta de Organización: " . mysql_error());
        $norg = mysql_num_rows($res_org);
        if ($norg > 0) {
            $row_org = mysql_fetch_array($res_org);
            $sql_munorg = "SELECT * FROM municipios ORDER BY MUNICIPIO ASC";
            $res_munorg = mysql_query($sql_munorg) or die("Error en la consulta de Municipios" . mysql_error());
            $nmunorg = mysql_num_rows($res_munorg);
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
        <form name="orgtab" action="editar_organiza.php" method="post" target="_blank">
            <input type="hidden" name="idorg" value="<?php echo $idorg; ?>">
        <h1>
            <?php echo $h1;?>  &mdash;
            <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $newtab;?>' title="<?php echo $newtab;?>">
        </h1>
        </form>
        <h2><?php echo $h2organiza;?></h2>
        <?php
        if ($norg > 0){
        ?>
            <form name="orgedit" action="update_org.php" method="post">
                <input type="hidden" name="idorg" value="<?php echo $idorg; ?>">
                <input type="hidden" name="origen" value="editar">
            <table>
                <tr>
                    <th><?php echo $thorganiza; ?></th>
                    <th><?php echo $thdomicilio; ?></th>
                    <th><?php echo $thcp; ?></th>
                    <th><?php echo $thciudad; ?></th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="organiza" size="30" value="<?php echo $row_org['ORGANIZACION'];?>">
                    </td>
                    <td>
                        <input type="text" name="domicilio" size="30" value="<?php echo $row_org['DOMICILIO'];?>">
                    </td>
                    <td>
                        <input type="text" name="cp" size="5" value="<?php echo $row_org['CP'];?>">
                    </td>
                    <td>
                        <select name="ine">
                        <?php
                        for ($i = 0; $i < $nmunorg; $i++){
                            $row_muni = mysql_fetch_array($res_munorg);                            
                        ?>
                            <option <?php if ($row_org['INE'] == $row_muni['INE']) {echo 'selected';} ?> value="<?php echo $row_muni['INE'];?>">
                                <?php echo $row_muni['MUNICIPIO'];?>
                            </option>
                        <?php
                        }
                        ?>
                        </select>
                    </td>
                </tr>
            </table>
        <?php
        }
        else{
        ?>
            <p class="error"><?php echo $errnoorg;?></p>
        <?php
        }
        ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href='#' onclick="document.orgdet.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'>
                        </a><br><?php echo $botatras; ?>
                    </td>
                <?php
                if ($norg > 0){
                ?>
                    <td class="borde">
                        <a href='#' onclick="document.orgedit.submit();">
                            <img src='imagenes/guardar.png' alt='<?php echo $botguarda; ?>' title='<?php echo $botguarda; ?>'>
                        </a><br><?php echo $botguarda; ?>
                    </td><td class="borde">
                        <a href='#' onclick="document.orgedit.reset()">
                            <img src='imagenes/no.png' alt='<?php echo $botreset; ?>' title='<?php echo $botreset; ?>'>
                        </a><br><?php echo $botreset; ?>
                    </td>
                <?php
                }
                ?>
                </tr>
            </table>
        </form>
        <form name="orgdet" action="detalle_organizacion.php" method="post">
            <input type="hidden" name="idorg" value="<?php echo $idorg;?>">
        </form>
    <?php
    }
    else{
    ?>
        <h1><?php echo $h1noperm;?></h1>
        <p class="error"><?php echo $errnoperm;?></p>
    <?php
    }
    ?>
    </body>
</html>
