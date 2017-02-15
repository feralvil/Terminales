<?php
// ------------ Obtención del usuario Joomla! --------------------------------------- //
        // Le decimos que estamos en Joomla
        define( '_JEXEC', 1 );

	// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
	define( 'DS', DIRECTORY_SEPARATOR );
	define('JPATH_BASE', dirname(__FILE__).DS.'..' );

	// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
	require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
	require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

	// Iniciamos nuestra aplicación (site: frontend)
	$mainframe =& JFactory::getApplication('site');

        // Obtenemos el idioma de la cookie de JoomFish
        $idioma = $_COOKIE['jfcookie']['lang'];
        $lang = "idioma/flotadet_$idioma.php";
        include ($lang);


	// Obtenemos los parámetros de Joomla
	$user =& JFactory::getUser();
	$usu = $user->username;
// ------------------------------------------------------------------------------------- //

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
        include("conexion.php");
        $base_datos=$dbbdatos;
        $link=mysql_connect($dbserv,$dbusu,$dbpaso);
        if(!link){
            echo "<b>ERROR MySQL:</b>".mysql_error();
        }
// ------------------------------------------------------------------------------------- //

import_request_variables("gp","");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina="SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina=mysql_db_query($base_datos,$sql_oficina);
$row_oficina=mysql_fetch_array($res_oficina);
$flota_usu=$row_oficina["ID"];
if ($id==""){
    $id = $flota_usu;
}
/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación
 */
$permiso=0;
if($flota_usu==100){
    $permiso = 2;
}
 else {
    if ($flota_usu != ""){
        $permiso = 1;
    }
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Autorización DOTS</title>
         <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
<?php
        if ($permiso != 0){
            $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
            $fecha = date("d-m-Y");
            $dia = substr($fecha, 0, 2);
            if (substr($dia, 0, 1) == 0){
                $dia = substr($dia, 1, 1);
            }
            $mest = substr($fecha, 3, 2);
            if (substr($mest, 0, 1) == 0){
                $mest = substr($mest, 1, 1);
                $mest = $mest - 1;
            }
            $mes = $meses[$mest];
            $anyo = substr($fecha, 6, 4);
            //datos de la tabla flotas
            $sql_flota="SELECT * FROM flotas WHERE ID='$id'";
            $res_flota=mysql_db_query($base_datos,$sql_flota) or die ("Error en la consulta de Flota: ".mysql_error());
            $nflota=mysql_num_rows($res_flota);
            if($nflota==0){
                echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
            }
            else{
                $row_flota=mysql_fetch_array($res_flota);
                $flota = utf8_encode($row_flota["FLOTA"]);
            }
            //datos de la tabla Municipios
            $ine =$row_flota["INE"];
            $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
            $res_mun = mysql_db_query($base_datos,$sql_mun) or die ("Error en la consulta de Municipio".mysql_error());
            $nmun = mysql_num_rows($res_mun);
            if($nmun==0){
                echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
            }
            else{
                $row_mun = mysql_fetch_array($res_mun);
            }
            //datos de la tabla Contactos
            $row_resp = "";
            if ($row_flota["RESPONSABLE"]!=0){
                $resp = $row_flota["RESPONSABLE"];
                $sql_resp = "SELECT * FROM contactos WHERE ID='$resp'";
                $res_resp = mysql_db_query($base_datos,$sql_resp) or die ("Error en la consulta del responsable".mysql_error());
                $nresp = mysql_num_rows($res_resp);
                if($nresp > 0){
                    $row_resp = mysql_fetch_array($res_resp);
                    $cargo = utf8_encode($row_resp["CARGO"]);
                }
            }
?>
            <h1>Solicitud de Autorización para gestión de posicionamiento GPS en servidor DOTS</h1>
            <form name="autdots" method="POST" action="pdfdots.php" target="_blank">
                <p>
                    <input name="municipio" type="text" size="50" value="<?php echo $row_mun["MUNICIPIO"];?>"/>, &nbsp; a
                    <input name="dia" type="text" size="2" value="<?php echo $dia;?>"/> &nbsp; de
                    <input name="mes" type="text" size="10" value="<?php echo $mes;?>"/> &nbsp; de
                    <input name="anyo" type="text" size="4" value="<?php echo $anyo;?>"/>
                </p>
                <p>
                    Yo, &nbsp;
                    <select name="trat">
                        <option value="D">D.</option>
                        <option value="D">Dña.</option>
                    </select> &nbsp;
                    <input name="nombre" type="text" size="80" value="<?php echo $row_resp["NOMBRE"];?>"/> (Nombre Completo)
                </p>
                <p>
                    Con Documento de identidad número &nbsp; <input name="nif" type="text" size="10" value="<?php echo $row_resp["NIF"];?>"/>
                </p>

                <p>
                    en calidad de &nbsp;
                    <input name="cargo" type="text" size="100" value="<?php echo $cargo;?>"/>
                    de la flota &nbsp;
                    <input name="flota" type="text" size="100" value="<?php echo $flota;?>"/>
                </p>
                <p>
                    autorizo a la <br>
                    Dirección General de Tecnologías de la Información <br>
                    Secretaria Autonómica de Administración Pública <br>
                    Conselleria de Hacienda y Administración Pública
                </p>
                <p>
                    para que ejerza, en su calidad de gestor de la Red de Comunicaciones Móviles de Emergencia y Seguridad de la Comunidad Valenciana (RED COMDES), la siguiente actuación en relación a los terminales de esta flota que acceden a la citada red:
                </p>
                <ol>
                    <li>Dar de alta/baja a los terminales en el buzón de la Generalitat del servidor DOTS, donde se almacenan todos los mensajes de estado emitidos por los  terminales. Con esta información, la flota podrá acceder al posicionamiento de sus terminales mediante una aplicación desarrollada por la Generalitat, y gratuita para las flotas. El acceso a este servidor conlleva asimismo el conocimiento del gestor de la red del posicionamiento de los terminales. En todo caso, la Generalitat garantiza la absoluta confidencialidad de estos datos, que sólo podrán ser utilizados a petición expresa de la propia flota.</li>
                </ol>
                <table>
                    <tr>
                        <td class="borde">
                            <a href='detalle_flota.php'><img src='imagenes/atras.png' alt='Volver' title='Volver'></a><br>Volver a detalle
                        </td>
                        <td class="borde">
				<a href='#' onclick='document.autdots.reset();'><img src='imagenes/no.png' alt='Cancelar' title="Cancelar"></a><br>Cancelar cambios
			</td>
                        <td class="borde">
				<input type='image' name='action' src='imagenes/pdfimp.png' alt='Generar' title="Generar"><br>Generar Petición (PDF)
			</td>
                    </tr>
                </table>
            </form>
<?php
        }
        else{
?>
            <h1>Acceso denegado</h1>
            <p class="error">No está autorizado a solicitar esta autorización</p>
<?php
        }
?>
    </body>
</html>
