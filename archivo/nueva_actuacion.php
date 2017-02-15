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
$lang = "idioma/newactuacion_$idioma.php";
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
if (($idflota == "NN") || ($idflota == "")) {
    $idflota = $flota_usu;
}
/*
 *  $permiso = variable de permisos de flota:
 *      1: Flota normal - Sólo puede actuar sobre sus terminales
 *      2: Oficina COMDES - Puede actuar sobre cualquier flota
 */
$permiso = 1;
if ($flota_usu == 100) {
    $permiso = 2;
}
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
                
                // Click en el botón de enviar:
                $("#enviar").click(function(){
                    var nomcont = $("#contnom").val();
                    if (nomcont.length == 0){
                        alert("<?php echo $errnosolic;?>");
                    }
                    else {
                        $("form#actuacion").submit();
                    }
                })
            })
        </script>
<?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($usu == ""){
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
        <h1><?php echo $titulo; ?></h1>
<?php
    // Si el usuario es la oficina COMDES, generamos un SELECT con todas las flotas:
    if ($permiso == 2){
        $sql_flotas = "SELECT ID, FLOTA FROM flotas ORDER BY FLOTA ASC";
        $res_flotas = mysql_query($sql_flotas, $link) or die(mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
?>
        <h2><?php echo $h2selflota; ?></h2>
        <form name="selflota" id="formselflota" action="nueva_actuacion.php" method="POST">
            <select name='idflota' id="selflotas">
                <option value='NN' <?php if (($idflota == "NN") || ($idflota == "")) echo ' selected'; ?>>Flota</option>
<?php
                for ($i = 0; $i < $nflotas; $i++) {
                    $row_flotas = mysql_fetch_array($res_flotas);
?>
                    <option value='<?php echo $row_flotas["ID"]; ?>' <?php if ($idflota == $row_flotas["ID"]) echo ' selected'; ?>>
                        <?php echo $row_flotas["FLOTA"]; ?>
                    </option>
<?php
                }
?>
            </select>
        </form>
<?php
    }
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $usuario = $row_flota["LOGIN"];
    }
    //datos de la tabla Contactos
    // Todos los contactos de la flota:
    $contbruto = array(
        $row_flota["RESPONSABLE"], $row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"],
        $row_flota["INCID1"], $row_flota["INCID2"], $row_flota["INCID3"], $row_flota["INCID4"]
    );
   // Contactos reales y no repetidos;
    $contneto = array();
    foreach ($contbruto as $idcont) {
        if (($idcont != 0) && (!in_array($idcont, $contneto))){
            array_push($contneto, $idcont);
        }
    }
    if (count($contneto) > 0){        
        $sql_contactos = "SELECT * FROM CONTACTOS WHERE ID IN (";
        foreach ($contneto as $idc) {
            $sql_contactos .= "$idc,";
        }
        $sql_contactos = substr($sql_contactos, 0, -1).")";
        $res_contactos = mysql_query($sql_contactos) or die("Error en la consulta de Contactos" . mysql_error());
        $ncontactos = mysql_num_rows($res_contactos);
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
?>
        <form name="actuacion" id="actuacion" method="POST" action="update_actuacion.php">            
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
            <input type="hidden" name="origen" value="nueva" />
        <h2><?php echo $h2flota; ?></h2>
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
<?php
        if (count($contneto) > 0){
?>
            <label for="contexist"><?php echo $h3contexist; ?>: </label>
            <select name="contexist" id="contexist">
<?php
            for ($i = 0; $i < $ncontactos; $i++){
                $row_contacto = mysql_fetch_array($res_contactos);
?>
                <option value="<?php echo $row_contacto["ID"]; ?>">
                    <?php echo $row_contacto["NOMBRE"]; ?>
                </option>
<?php

            }
?>
            </select> &nbsp;
            <a href="#" id="loadcont"><img src="imagenes/ir.png" alt="<?php echo $loadcont;?>" title="<?php echo $loadcont;?>"></a> &mdash;
            <a href="#" id="delcont"><img src="imagenes/cancelar.png" alt="<?php echo $delcont;?>" title="<?php echo $delcont;?>"></a>
<?php
        }
?>
            <div id="datacont"></div>
        <table>
            <tr>
                <td class="borde">
                    <a href='#' id="enviar">
                        <img src='imagenes/guardar.png' alt='<?php echo $guardar; ?>' title='<?php echo $guardar; ?>'>
                    </a><br><?php echo $guardar; ?>
                </td>
            </tr>
        </table>
    </form>
    </body>
</html>