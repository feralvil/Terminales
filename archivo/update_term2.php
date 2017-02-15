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
        $lang = "idioma/termupd_$idioma.php";
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
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $title;?></title>
 <link rel="StyleSheet" type="text/css" href="estilo.css">
</head>
<body>
<?php
	if($permiso==2){
		$enlace = "detalle_terminal.php?id=$idterm";
		if($origen=="editar"){
			$observaciones = utf8_decode($observaciones);
			$sql_update = "UPDATE terminales SET ISSI='$issi', TEI='$tei', NUMEROK='$numk', TIPO='$tipo', CARPETA='$carpeta', ";
			$sql_update = $sql_update."MNEMONICO='$mnemonico', OBSERVACIONES='$observaciones', ";
			$sql_update = $sql_update."MARCA='$marca', MODELO='$modelo', PROVEEDOR='$proveedor', AM='$am', ";
			$sql_update = $sql_update."DOTS='$dots', SEMID='$semid', DUPLEX='$duplex' WHERE ID=$idterm";
			$titulo = $titedi.$idterm;
			$mensaje = $mensedi;
			$error = $erredi.$idterm.":";
		}
		if($origen=="baja"){
			$fecha = date ("Y-m-d");
			$sql_update = "UPDATE terminales SET ESTADO='B', FBAJA='$fecha' WHERE ID=$idterm";
			$titulo = $titbaja.$idterm;
			$mensaje = "Terminal ID='$idterm' ".$mensbaja;
			$error = $errbaja.$idterm.":";
		}
		if($origen=="alta"){
			$fecha = date ("Y-m-d");
			$sql_update = "UPDATE terminales SET ESTADO='A', FALTA='$fecha' WHERE ID=$idterm";
			$titulo = $titalta.$idterm;
			$mensaje = "Terminal ID='$idterm' ".$mensalta;
			$error = $erralta.$idterm.":";
		}
		if($origen=="nuevo"){
			$fecha = date ("Y-m-d");
			$error = $errnew;
			switch ($estado){
				case ("A"):{
					$falta = $fecha;
					$fbaja = '0000-00-00';
					break;
				}
				case ("B"):{
					$fbaja = $fecha;
					$falta = '0000-00-00';
					break;
				}
			}
                        $repetido = false;
                        $sql_terminales = "SELECT * FROM terminales WHERE ISSI='$issi'";
                        $res_terminales = mysql_db_query($base_datos, $sql_terminales) or die(mysql_error());
                        $nterminales = mysql_numrows($res_terminales);
                        if ($nterminales > 0){
                            $repetido = true;
                            $terminal = mysql_fetch_array($res_terminales);
                            $idr = $terminal["ID"];
                            $teir = $terminal["TEI"];
                            $flotar = $terminal["FLOTA"];
                            $sql_flotar = "SELECT * FROM flotas WHERE ID='$flotar'";
                            $res_flotar = mysql_db_query($base_datos, $sql_flotar) or die(mysql_error());
                            $nflotar = mysql_numrows($res_flotar);
                            if($nflotar > 0){
                                $row_flota = mysql_fetch_array($res_flotar);
                                $flotar_nom = utf8_encode($row_flota["FLOTA"]);
                            }
                            $error = "<br />$repet1 ($issi) $repet2:";
                            $error = $error."<br />  &mdash Flota: $flotar_nom";
                            $error = $error."<br />  &mdash Terminal ID: $idr";
                            $error = $error."<br />  &mdash Terminal TEI: $idr";
                        }
			$observaciones = utf8_decode($observaciones);
			$sql_update = "INSERT INTO terminales (ISSI, TEI, CODIGOHW, NSERIE, NUMEROK, TIPO, MARCA, MODELO, ";
			$sql_update = $sql_update."PROVEEDOR, FLOTA, MNEMONICO, DOTS, AM, OBSERVACIONES, CARPETA, DUPLEX, SEMID, ";
			$sql_update = $sql_update."ESTADO, FALTA, FBAJA) VALUES ";
			$sql_update = $sql_update."('$issi', '$tei', '$codigohw', '$nserie', '$numerok', '$tipo', '$marca', '$modelo', ";
			$sql_update = $sql_update."'$proveedor', '$flota', '$mnemonico', '$dots', '$am', '$observaciones', '$carpeta', ";
			$sql_update = $sql_update."'$duplex', '$semid', '$estado', '$falta', '$fbaja') ";
			$titulo = $titnew;
			$mensaje = $mensnew;
			$enlace = "terminales.php?flota=$flota";
		}
                if($origen=="dots"){
                    if ($action == "add"){
                        $dots = "SI";
                        $titulo = $titadd;
                        $error = $erradd;
                        $mensaje = "Terminal ID='$idterm' ".$mensadd;
                    }
                    else {
                        $dots = "NO";
                        $titulo = $titdel;
                        $error = $errdel;
                        $mensaje = "Terminal ID='$idterm' ".$mensdel;
                    }
                    $sql_update = "UPDATE terminales SET DOTS = '$dots' WHERE ID = $idterm";

		}
                if (($origen=="nuevo")&&($repetido)){
			$res_update=false;
			$enlace = "#\" onclick=\"history.go(-1);\"";
		}
		else{
			$res_update=mysql_db_query($base_datos,$sql_update);
		}
?>
<h1><?php echo $titulo;?></h1>
	<div class="centro">
<?php
		if ($res_update){
?>
			<p><img src='imagenes/clean.png' alt='OK'></p>
			<p><?php echo $mensaje;?></p>
			<p><a href="<?php echo $enlace?>"><img src='imagenes/atras.png' alt='Volver'></a><BR>Volver</p>
<?php
		}
		else {
?>
			<p><img src='imagenes/error.png' alt='Error'></p>
			<p><span class="error"><b><?php echo $error;?></b> <?php echo mysql_error();?></span></p>
			<p><a href="<?php echo $enlace?>"><img src='imagenes/atras.png' alt='Volver'></a><BR>Volver</p>
<?php
		}
?>
	</div>
<?php
	}
	else{
?>
	<h1><?php echo $h1perm?></h1>
	<p class='error'><?php echo $permno?></p>
<?php
	}
?>
</body>
</html>
