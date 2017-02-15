<?php
// Revisado 2011-07-07
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
$lang = "idioma/flotagps_$idioma.php";
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
if (($usu != "")&&(($flota_usu == 100) || ($flota_usu == $idflota))) {
    $permiso = 2;
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <script type="text/javascript">
            function validaForm(){            	
                var usuario = document.forms["formacceso"]["usuario"].value;
                if ((usuario==null)||(usuario=="")){
                    alert('<?php echo $uservacio; ?>');
                    return false;
                }
                var pwd = document.forms["formacceso"]["passwd1"].value;
                if ((pwd==null)||(pwd=="")){
                    alert('<?php echo $passvacia; ?>');
                    return false;
                }
                else{
                    var pwd2 = document.forms["formacceso"]["passwd2"].value;
                    if (pwd != pwd2){
                        alert('<?php echo $passconf; ?>');
                        return false;
                    }
                    else{
                        return true;
                    }
                }
            }
        		function eliminarGPS () {
        			var accion =  confirm('<?php echo $delconf; ?>');
        			if (accion == true){
        				document.formacceso.origen.value = 'eliminar';
        				document.formacceso.submit();
        			}
				}
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
if ($permiso == 2) {
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
?>
        <p class='error'><?php echo $noflota; ?></p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $sql_gpsflota = "SELECT * FROM gpsusuarios WHERE FLOTA='$idflota'";
        $res_gpsflota = mysql_query($sql_gpsflota) or die("Error en la consulta de Usuarios GPS: " . mysql_error());
        $ngpsflota = mysql_num_rows($res_gpsflota);
        $origen = "editar";
        $idgps = 0;
        if ($ngpsflota == 0){
        	$origen = "nuevo";
        	$usergps = "";
        	$savetxt = $nuevobot;        	
        }
    		else {
    			$row_gpsflota = mysql_fetch_array($res_gpsflota);
    			$usergps = $row_gpsflota["USUARIO"];
    			$savetxt = $editarbot;
    			$idgps = $row_gpsflota["ID"];
    		}
?>
        <h1><?php echo $h1; ?> <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <form name="formacceso" action="update_gpsflota.php" method="POST" onsubmit="return validaForm();">
            <table>
                <tr>
                    <th><?php echo $usutxt; ?></th>
                    <td colspan="3"><input type='text' name='usuario' size='20' maxlength='20' value="<?php echo $usergps; ?>"></td>
                </tr>
                <tr>
                    <th class="t4c"><?php echo $pwd1; ?></th>
                    <td><input type='password' name='passwd1' size='20' maxlength='20' value=""></td>
                    <th class="t4c"><?php echo $pwd2; ?></th>
                    <td><input type='password' name='passwd2' size='20' maxlength='20' value=""></td>
                </tr>
            </table>
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <input type="hidden" name="idgps" value="<?php echo $idgps; ?>">
            <input type="hidden" name="flota_org" value="<?php echo $row_flota["FLOTA"]; ?>">
            <input type="hidden" name="acro_org" value="<?php echo $row_flota["ACRONIMO"]; ?>">
            <input type="hidden" name="origen" value="<?php echo $origen; ?>">
            <table>
                <tr>
                    <td class='borde'>
                        <input type='image' name='guardar' value="<?php echo $savetxt; ?>" src='imagenes/guardar.png' alt='<?php echo $savetxt; ?>' title="<?php echo $savetxt; ?>"><br><?php echo $savetxt; ?>
                    </td>
                    <td class='borde'>
                        <a href='#' onclick='document.formacceso.reset();'>
                            <img src='imagenes/no.png' alt='<?php echo $cancel; ?>' title="<?php echo $cancel; ?>">
                        </a><br><?php echo $cancel; ?>
                    </td>
                    <td class='borde'>
                        <a href='#' onclick='document.detflota.submit();'>
                            <img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>">
                        </a><br><?php echo $volver; ?>
                    </td>
                    <td class='borde'>
                        <a href='#' onclick="eliminarGPS();">
                            <img src='imagenes/eliminar.png' alt='<?php echo $delbot; ?>' title="<?php echo $delbot; ?>">
                        </a><br><?php echo $delbot; ?>
                    </td>
                </tr>
            </table>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
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