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
$lang = "idioma/contactos_$idioma.php";
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
if ($idflota == "") {
    $idflota = $flota_usu;
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
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
<?php
        if ($usu == ""){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
<?php
if ($permiso != 0) {
    //datos de la tabla Flotas
    $sql_contacto = "SELECT * FROM contactos WHERE ID ='$idcontacto'";
    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
    $ncontacto = mysql_num_rows($res_contacto);
    if ($ncontacto == 0) {
        echo "<p class='error'>No hay resultados en la consulta de Contacto</p>\n";
    }
    else {
        $row_contacto = mysql_fetch_array($res_contacto);
    }
    //datos de la tabla Flotas
    $sql_flotas = "SELECT * FROM flotas WHERE (RESPONSABLE = ".$idcontacto.") OR (CONTACTO1 = ".$idcontacto.")";
    $sql_flotas .= " OR (CONTACTO2 = ".$idcontacto.") OR (CONTACTO3 = ".$idcontacto.")";
    $sql_flotas .= " OR (INCID1 = ".$idcontacto.") OR (INCID2 = ".$idcontacto.")";
    $sql_flotas .= " OR (INCID3 = ".$idcontacto.") OR (INCID4 = ".$idcontacto.")";
    $res_flotas = mysql_query($sql_flotas) or die("Error en la Consulta de Flotas: " . mysql_error());
    $nflotas = mysql_num_rows($res_flotas);
?>
        <h1>Contacto de Flota COMDES</h1>
        <h2>Datos del Contacto</h2>
        <table>
            <tr>
                <th class="t40p">Nombre</th>
                <td class="t5c"><?php echo $row_contacto["NOMBRE"]; ?></td>
                <th class="t5c">Teléfono</th>
                <td class="t5c"><?php echo $row_contacto["TELEFONO"]; ?></td>
            </tr>
            <tr>
                <th class="t40p">Cargo</th>
                <td class="t5c"><?php echo $row_contacto["CARGO"]; ?></td>
                <th class="t5c">Móvil</th>
                <td class="t5c"><?php echo $row_contacto["MOVIL"]; ?></td>
            </tr>
            <tr>
                <th class="t40p">Mail</th>
                <td class="t5c"><?php echo $row_contacto["MAIL"]; ?></td>
                <th class="t5c">Horario</th>
                <td class="t5c"><?php echo $row_contacto["HORARIO"]; ?></td>
            </tr>
        </table>
        <h2>Flotas del Contacto</h2>
        <?php
            if ($nflotas > 0){
        ?>
            <table>
                <tr>            
                    <th class="t5c">Nº</th>
                    <th class="t40p">Flota</th>
                    <th class="t5c">Acrónimo</th>
                    <th class="t5c">Tipo Contacto</th>
                </tr>
        <?php

                for ($i = 0; $i < $nflotas; $i++){
                    $row_flota = mysql_fetch_array($res_flotas);
                    $idcont = array(
                        $row_flota['RESPONSABLE'], $row_flota['CONTACTO1'], 
                        $row_flota['CONTACTO2'], $row_flota['CONTACTO3'], 
                        $row_flota['INCID1'], $row_flota['INCID2'], 
                        $row_flota['INCID3'], $row_flota['INCID4']
                    );
                    $tipocont = array(
                        'RESPONSABLE', 'CONTACTO1', 'CONTACTO2', 'CONTACTO3', 
                        'INCID1', 'INCID2', 'INCID3', 'INCID4'  
                    );
                    $indice = array_search($idcontacto, $idcont);

        ?>
                <tr <?php if (($i % 2) == 1){ echo 'class = "filapar"';} ?>>
                    <td><?php echo ($i + 1); ?></td>
                    <td><?php echo $row_flota["FLOTA"]; ?></td>
                    <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                    <td><?php echo $tipocont[$indice]; ?></td>
                </tr>
        <?php
                }
        ?>
            </table>
        <?php
            }
            else{
        ?>
                <p class="error">Este contacto no está asociado a ninguna flota</p>
        <?php
            }
        ?>
        <table>
            <tr>
<?php
                if ($permiso == 2) {
?>
                    <td class="borde">
                        <a href='contactos.php'>
                            <img src='imagenes/atras.png' alt='Volver a Contactos' title='Volver a Contactos'>
                        </a><br>Volver a Contactos
                    </td>
<?php
                }
?>
                </tr>
            </table>
                <form name="modflota" action="#" method="POST">
                    <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
                </form>
                <form name="formdet" action="detalle_flota.php" method="POST">
                    <input type="hidden" name="idflota" value="#">
                </form>
                <form name="termflota" action="terminales.php" method="POST">
                    <input type="hidden" name="flota" value="<?php echo $idflota ?>">
                </form>
                <form name="gpsflota" action="gpsflota.php" method="POST">
                    <input type="hidden" name="idflota" value="<?php echo $idflota ?>">
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