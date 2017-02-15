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
$lang = "idioma/flotamail_$idioma.php";
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
<?php
    if ($permiso == 2) {
        if (isset($idm)&&isset($update)&&($update == "OK")){
?>
        <p class="flashok">
            <img src="imagenes/okm.png" alt="OK" title="OK"> &mdash; <?php echo $mensflash;?>
        </p>
<?php
        }
?>
        <h1><?php echo $h1; ?></h1>
        <form name="formprov" action="mail_flotas.php" method="POST">
        <h2><?php echo $criterios; ?></h2>
        	<select name='prov' onChange='document.formprov.submit();'>
        		<option value='00' <?php if (($prov == "00") || ($prov == "")) echo ' selected'; ?>>Seleccione <?php echo $provincia;?></option>
        		<option value='03' <?php if ($prov == "03") echo ' selected'; ?>><?php echo $alc;?></option>
        		<option value='12' <?php if ($prov == "12") echo ' selected'; ?>><?php echo $cas;?></option>
        		<option value='46' <?php if ($prov == "46") echo ' selected'; ?>><?php echo $vlc;?></option>
			</select>
        </form>
        <h2><?php echo $h2dest;?></h2>
<?php
            $sql_flotas = "SELECT * FROM flotas ";
            if (($prov != "00") && ($prov != "")){
            	$sql_flotas .= "WHERE (flotas.INE LIKE '$prov%') ";
            }
            $sql_flotas .= "ORDER BY flotas.FLOTA ASC";
            $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
            $nflotas = mysql_num_rows($res_flotas);
?>
        <form name="flotas" action="export.php" method="POST">
            <input type="hidden" name="idm" value="<?php echo $idm;?>"/>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nflotas; ?></b>.</td>
<?php
                    if ($nflotas > 0) {
?>
                    <td class="borde">
                        <a href="#" onclick="document.flotas.action='xlsmailflotas.php';document.flotas.submit()"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a> &mdash;
                        <a href="#" onclick="document.flotas.action='newmailflotas.php';document.flotas.submit()"><img src="imagenes/mail.png" alt="E-mail" title="E-mail"></a>
                    </td>
<?php
                    }
?>
                </tr>
            </table>
        <table>
<?php
            if ($nflotas == 0) {
?>
                <tr><td class='borde'><?php echo $noreg; ?></td></tr>
<?php
            }
            else {
                $ncampos = mysql_num_fields($res_flotas);
                //*TABLA CON RESULTADOS*//
?>
                <tr>
                    <th>
                        <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" />
                    </th>
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
                for ($i = 0; $i < $nflotas; $i++) {
                    $row_flota = mysql_fetch_array($res_flotas);
                    $idflota = $row_flota["ID"];
?>
                <tr <?php if (($i % 2) == 1) {echo " class='filapar'";}?>>
                    <td class='centro'>
                        <input type="checkbox" name="idflota[]" value="<?php echo $idflota;?>" />
                    </td>
                    <td><?php echo $row_flota["FLOTA"]; ?></td>
                    <td><?php echo $row_flota["ACRONIMO"]; ?></td>
<?php
                // Datos de contactos
                $id_contacto = array($row_flota["RESPONSABLE"], $row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
                $nom_contacto = array("Responsable", $contacto . " 1", $contacto . " 2", $contacto . " 3");
                // Datos de contactos
                $mailok = false;
                $idcont = 0;
                $nombre = $email = $cargo = $tipoc = "-";
                for ($j = 0; $j < count($id_contacto); $j++) {
                    if (!$mailok){
                        if ($id_contacto[$j] != 0) {
                            $idc = $id_contacto[$j];
                            $sql_contacto = "SELECT * FROM contactos WHERE ID=$idc";
                            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
                            $ncontacto = mysql_num_rows($res_contacto);
                            if ($ncontacto != 0) {
                                $row_contacto = mysql_fetch_array($res_contacto);
                                if (($row_contacto["NOMBRE"]!="")&&($row_contacto["MAIL"]!="")){
                                    $idcont = $row_contacto["ID"];
                                    $nombre = $row_contacto["NOMBRE"];
                                    $email = $row_contacto["MAIL"];
                                    $cargo = $row_contacto["CARGO"];
                                    $tipoc = $nom_contacto[$j];
                                    $mailok = eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
                                }
                            }
                        }
                    }
                }
?>
                <td><?php echo $tipoc; ?></td>
                <td><?php echo $nombre; ?></td>
                <td><?php echo $cargo; ?></td>
                <td><?php echo $email; ?></td>
            </tr>

                        <input type="hidden" name="idcont[]" value="<?php echo $idcont;?>" />
<?php
                } //primer for
            }
?>
        </table>
        </form>

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
