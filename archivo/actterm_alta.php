<?php
// Revisado 2011-07-12
// ------------ Obtención del usuario Joomla! --------------------------------------- //
// Le decimos que estamos en Joomla
define('_JEXEC', 1);

// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..');

// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

// Iniciamos nuestra aplicación (site: frontend)
$mainframe = & JFactory::getApplication('site');

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/actterm_$idioma.php";
include ($lang);

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;
// ------------------------------------------------------------------------------------- //
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

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_query($sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript">
            // Funciones JQuery
            $(function(){
                // Cargar el div con los datos del contacto de inicio:
                $("#datacont").load("sqlcontacto.php",$(this).serializeArray());
                
                // Cambio del selector de Flotas
                $("#selflotas").change(function(){
                    $("form#formselflota").submit();
                })
                
                // Cambio del select de contactos:
                $("#contexist").change(function(){
                    $("#datacont").load("sqlcontacto.php",$("form#actuacion").serializeArray());
                })
                
                // Click en la carga de datos del contacto:
                $("#loadcont").click(function(){
                    $("#datacont").load("sqlcontacto.php",$("form#actuacion").serializeArray());                    
                })
                
                // Click en el borrado de datos del contacto:
                $("#delcont").click(function(){
                    $("[type=text]").val("");
                })
                
                // Click en el botón de alta:
                $("#actalta").click(function(){
                    var nomcont = $("#contnom").val();
                    if (nomcont.length == 0){
                        alert("<?php echo $errnosolic;?>");
                    }
                    else {
                        $("#destino").val("alta")
                        $("form#actuacion").submit();
                    }
                })
                
                // Click en el botón de Baja:
                $("#actbaja").click(function(){
                    var nomcont = $("#contnom").val();
                    if (nomcont.length == 0){
                        alert("<?php echo $errnosolic;?>");
                    }
                    else {
                        $("#destino").val("baja")
                        $("form#actuacion").submit();
                    }
                })
                
                // Click en el botón de Modificación:
                $("#actmod").click(function(){
                    var nomcont = $("#contnom").val();
                    if (nomcont.length == 0){
                        alert("<?php echo $errnosolic;?>");
                    }
                    else {
                        $("#destino").val("mod")
                        $("form#actuacion").submit();
                    }
                })
            })
        </script>
<?php
        if ($usu == ""){
?>
            <script type="text/javascript">
                window.top.location.href = "https://comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
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
    $permiso = 0;
    $idflota = -1;
    $sql_actuacion = "SELECT * FROM actuaciones WHERE ID='$idactuacion'";
    $res_actuacion = mysql_query($sql_actuacion) or die("Error en la consulta de la actuación: " . mysql_error());
    $nactuacion = mysql_num_rows($res_actuacion);
    if ($nactuacion == 0){
        echo "<p class='error'>No hay resultados en la consulta de la Actuación</p>\n";
    }
    else {
        $row_actuacion = mysql_fetch_array($res_actuacion);
        $idflota = $row_actuacion["FLOTA_ID"];
    }
    if ($flota_usu == 100){
        $permiso = 2;
    }
    elseif ($flota_usu == $idflota) {
        $permiso = 1;
    }
    if ($permiso > 0){
?>
        <h1><?php echo $titulo; ?></h1>
        <h2><?php echo $h2flota; ?></h2>
<?php
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
    }
    // datos de la tabla Municipio
    // INE
    $ine = $row_flota["INE"];
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
    }
    // Datos de la tabla actterminales:
    $sql_actterm = "SELECT * FROM accterminales WHERE ACTUACION_ID='$idactuacion'";
    $res_actterm = mysql_query($sql_actterm) or die("Error en la consulta de la actuación: " . mysql_error());
    $nactterm = mysql_num_rows($res_actterm);
?>
        <table>
            <tr>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t10c"><?php echo $acroflota; ?></th>
                <th class="t2c"><?php echo $locflota; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_flota["DOMICILIO"];?> &mdash; <?php echo $row_flota["CP"]; ?> <?php echo $row_mun["MUNICIPIO"]; ?></td>
            </tr>
        </table>
        <h2><?php echo $h2contacto; ?></h2>
        <table>
            <tr>
                <th><?php echo $nomflota;  ?></th>
                <td><?php echo $row_actuacion["NOMBRE"]; ?></td>
                <th><?php echo $telefono; ?></th>
                <td><?php echo $row_actuacion["TELEFONO"]; ?></td>
            </tr>
            <tr>
                <th><?php echo $cargo; ?></th>
                <td><?php echo $row_actuacion["CARGO"]; ?></td>
                <th><?php echo $mail; ?></th>
                <td><?php echo $row_contacto["MAIL"]; ?></td>
            </tr>
        </table>
        <form name="newactuacion" id="newactuacion" action="update_actterm.php" method="POST">
            <div id="datosact">
            </div>
            <div id="newacterm">
                <table>
                    <tr>
                        <td class="borde">
                            <a href='#' id="actalta">
                                <img src='imagenes/base_add.png' alt='<?php echo $alta; ?>' title='<?php echo $alta; ?>'>
                            </a><br><?php echo $alta; ?>
                        </td>
                        <td class="borde">
                            <a href='#' id="actbaja">
                                <img src='imagenes/base_del.png' alt='<?php echo $baja; ?>' title='<?php echo $baja; ?>'>
                            </a><br><?php echo $baja; ?>
                        </td>
                    </tr>
                </table>
            </div>
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
    </body>
</html>