<?php
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
$lang = "idioma/dotsterm_$idioma.php";
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
    <h1><?php echo $titulo; ?></h1>
    <h2>
        <?php echo $h2modelo; ?> &mdash; <a href="xlsdots.php"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
    </h2>
    <?php
    if ($permiso == 2) {
        $totmarca = 0;
        $totmodelo = 0;
        $totterm = 0;
        //datos de la tabla Terminales - Obtenemos las marcas
        $sql_marca = "SELECT DISTINCT MARCA FROM terminales ORDER BY MARCA ASC";
        $res_marca = mysql_query($sql_marca) or die("Error en la consulta de Marca" . mysql_error());
        $nmarca = mysql_num_rows($res_marca);
        $totmarca = $nmarca;
        if ($nmarca == 0){
    ?>
            <p class='error'><?php echo $errnomarca; ?></p>
    <?php
        }
        else{
    ?>
            <ul>
    <?php         
            for ($i = 0; $i < $nmarca; $i++){
                $row_marca = mysql_fetch_array($res_marca);
                $marca = $row_marca[0];
                //Datos de la tabla Terminales - Obtenemos los modelos
                $sql_modelo = "SELECT DISTINCT MODELO FROM terminales WHERE MARCA='$marca' ORDER BY MODELO ASC";
                $res_modelo = mysql_query($sql_modelo) or die("Error en la consulta de Modelo" . mysql_error());
                $nmodelo = mysql_num_rows($res_modelo);
                $totmodelo += $nmodelo;
                if ($nmodelo == 0){
    ?>
                    <p class='error'><?php echo $errnomodelo; ?></p>
    <?php
                }
                else{
                    for ($j = 0; $j < $nmodelo; $j++){
                        $row_modelo = mysql_fetch_array($res_modelo);
                        $modelo = $row_modelo[0];
                        $sql_terminal = "SELECT terminales.ISSI, flotas.ACRONIMO, terminales.MNEMONICO FROM terminales, flotas ";
                        $sql_terminal .= "WHERE (terminales.MARCA = '".$marca."') AND (terminales.MODELO = '".$modelo."') ";
                        $sql_terminal .= "AND (terminales.FLOTA = flotas.ID) ";
                        $sql_terminal .= "ORDER BY flotas.FLOTA ASC, terminales.ISSI ASC";
                        $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta (parcial) de Terminal: " . mysql_error());
                        $nterminal = mysql_num_rows($res_terminal);
                        $totterm += $nterminal;

    ?>
                            <li>
    <?php
                                echo $marca.'-'.$modelo.' &mdash; '.$nterminal
    ?>                            
                            </li>
    <?php
                    }
                }
            }
    ?>
            </ul>
    <?php
        }
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