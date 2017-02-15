<?php
// Revisado 2011-07-14
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

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de permisos */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_query($sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
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
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
    if ($permiso == 2) {
?>
        <h1>
            <?php echo $h1; ?> &mdash; <a href="flotas.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <form action="contactos.php" name="formulario" method="POST">
            <h4><?php echo $criterios; ?></h4>
            <table>
                <tr>
                    <td>
                        <?php echo $nomtxt; ?>:&#160;<input type="text" name="nombre" size="40">&#160;<input type='image' name='action' src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                    </td>
                </tr>
            </table>
<?php
            if ($tam_pagina == "") {
                $tam_pagina = 30;
            }
            if (!$pagina) {
                $inicio = 0;
                $pagina = 1;
            }
            else {
                $inicio = ($pagina - 1) * $tam_pagina;
            }
            $sql_contactos = "SELECT * FROM contactos";
            if ($nombre != '') {
                $sql_contactos = $sql_contactos . " WHERE (contactos.NOMBRE LIKE '%$nombre%')";
            }
            $sql_no_limit = $sql_contactos . " ORDER BY contactos.NOMBRE ASC";
            $sql_limit = $sql_no_limit . " LIMIT " . $inicio . "," . $tam_pagina . ";";
            $res = mysql_query($sql_no_limit) or die(mysql_error());
            $nfilas = mysql_num_rows($res);
            $total_pag = ceil($nfilas / $tam_pagina);
?>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nfilas; ?></b>.</td>
                    <td class="borde">
    			Mostrar:
                        <select name='tam_pagina' onChange='document.formulario.submit();'>
                            <option value='30' <?php if (($tam_pagina == "30") || ($tam_pagina == "")) {echo 'selected';}?>>30</option>
                            <option value='50' <?php if ($tam_pagina == "50") {echo 'selected';}?>>50</option>
                            <option value='1000' <?php if ($tam_pagina == "100") {echo 'selected';}?>>100</option>
                            <option value='<?php echo $nfilas;?>' <?php if ($tam_pagina == $nfilas) {echo 'selected';}?>>Todas</option>
                        </select> <?php echo $regpg; ?>
                    </td>
<?php
                    if ($total_pag > 1) {
?>
                        <td class="borde">
                            <?php echo "$pgtxt:";?>
                            <select name='pagina' onChange='document.formulario.submit();'>
<?php
                            for ($k = 1; $k <= $total_pag; $k++) {
?>
                                <option value='<?php echo $k;?>' <?php if ($pagina == $k) {echo 'selected';}?>><?php echo $k;?></option>;
<?php
                            }
?>
                            </select>
                            de <?php echo $total_pag;?>
                        </td>
<?php
                    }
?>
                    <td class="borde">
                        <a href="nueva_flota.php"><img src="imagenes/nueva.png" alt="<?php echo $newflota; ?>"></a> &mdash; <?php echo $newflota; ?>
                    </td>
                </tr>
            </table>
        </form>
        <form name="formdet" action="detalle_contacto.php" method="POST">
            <input type="hidden" name="idcontacto" value="#">
        </form>
        <table>
<?php
            if ($nfilas == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
            }
            else {
                $res_contactos = mysql_query($sql_limit) or die("Error en la Consulta de Contactos: " . mysql_error());
                $ncontactos = mysql_num_rows($res_contactos);
                //*TABLA CON RESULTADOS*//
?>
                <tr>
<?php
                //* CABECERA  *//
                for ($i = 0; $i < count($campos); $i++) {
?>
                    <th><?php echo $campos[$i]; ?></th>
<?php
                }
?>
                </tr>
<?php
                for ($i = 0; $i < $ncontactos; $i++) {
                    $row_contacto = mysql_fetch_array($res_contactos);
                    $linkdet = "document.formdet.idcontacto.value='".$row_contacto['ID']."';document.formdet.submit();";
                    $idcontacto = $row_contacto['ID'];
                    $sql_flotas = "SELECT * FROM flotas WHERE (RESPONSABLE = ".$idcontacto.") OR (CONTACTO1 = ".$idcontacto.")";
                    $sql_flotas .= " OR (CONTACTO2 = ".$idcontacto.") OR (CONTACTO3 = ".$idcontacto.")";
                    $sql_flotas .= " OR (INCID1 = ".$idcontacto.") OR (INCID2 = ".$idcontacto.")";
                    $sql_flotas .= " OR (INCID3 = ".$idcontacto.") OR (INCID4 = ".$idcontacto.")";
                    $res_flotas = mysql_query($sql_flotas) or die("Error en la Consulta de Flotas: " . mysql_error());
                    $nflotas = mysql_num_rows($res_flotas);
?>
                <tr <?php if (($i % 2) == 1) {echo " class='filapar'";}?>>
                    <td class='centro'><a href='#' onclick="<?php echo $linkdet; ?>"><img src='imagenes/consulta.png' alt="<?php echo $detalle;?>"></a>
                    <td><?php echo $row_contacto['NOMBRE'];?></td>
                    <td><?php echo $row_contacto['CARGO'];?></td>
                    <td><?php echo $row_contacto['MAIL'];?></td>
                    <td><?php echo $nflotas;?></td>
                </tr>
<?php
                } //primer for
            }
?>
        </table>
<?php
    } // Si el usuario no es el de la Oficina
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
