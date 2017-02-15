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
        $lang = "idioma/termedi_$idioma.php";
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
<title>Editar Terminal COMDES</title>
 <link rel="StyleSheet" type="text/css" href="estilo.css">
</head>
<?php
	//datos de la tabla terminales
	$sql_terminal="SELECT * FROM terminales WHERE ID='$id'";
	$res_terminal=mysql_db_query($base_datos,$sql_terminal) or die ("Error en la consulta de terminal: ".mysql_error());
	$nterminal=mysql_num_rows($res_terminal);
	if($nterminal==0){
		echo "<p class='error'>No hay resultados en la consulta del Terminal</p>\n";
	}
	else{
		$row_terminal=mysql_fetch_array($res_terminal);
		$id_flota = $row_terminal["FLOTA"];
	}
	//datos de la tabla flotas
	$sql_flota="SELECT * FROM flotas WHERE ID='$id_flota'";
	$res_flota=mysql_db_query($base_datos,$sql_flota) or die ("Error en la consulta de Flota Usuaria: ".mysql_error());
	$nflota=mysql_num_rows($res_flota);
	if($nflota==0){
		echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
	}
	else{
		$row_flota=mysql_fetch_array($res_flota);
	}
	//datos de la tabla municipios
	$ine = $row_flota ["INE"];
	$sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
	$res_mun = mysql_db_query($base_datos,$sql_mun) or die ("Error en la consulta de Municipio".mysql_error());
	$nmun = mysql_num_rows($res_mun);
	if($nmun==0){
		echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
	}
	else{
		$row_mun = mysql_fetch_array($res_mun);
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Editar Terminal COMDES</title>
 <link rel="StyleSheet" type="text/css" href="estilo.css">
</head>
<?php
	//datos de la tabla terminales
	$sql_terminal="SELECT * FROM terminales WHERE ID='$id'";
	$res_terminal=mysql_db_query($base_datos,$sql_terminal) or die ("Error en la consulta de terminal: ".mysql_error());
	$nterminal=mysql_num_rows($res_terminal);
	if($nterminal==0){
		echo "<p class='error'>No hay resultados en la consulta del Terminal</p>\n";
	}
	else{
		$row_terminal=mysql_fetch_array($res_terminal);
		$id_flota = $row_terminal["FLOTA"];
		$tipo = $row_terminal["TIPO"];
		$am = $row_terminal["AM"];
		$dots = $row_terminal["DOTS"];
	}
	//datos de la tabla flotas
	$sql_flota="SELECT * FROM flotas WHERE ID='$id_flota'";
	$res_flota=mysql_db_query($base_datos,$sql_flota) or die ("Error en la consulta de terminal: ".mysql_error());
	$nflota=mysql_num_rows($res_flota);
	if($nflota==0){
		echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
	}
	else{
		$row_flota=mysql_fetch_array($res_flota);
	}
	//datos de la tabla municipios
	$ine = $row_flota ["INE"];
	$sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
	$res_mun = mysql_db_query($base_datos,$sql_mun) or die ("Error en la consulta de Municipio".mysql_error());
	$nmun = mysql_num_rows($res_mun);
	if($nmun==0){
		echo "<p class='error'>No hay resultados en la consulta del Municipio</p>\n";
	}
	else{
		$row_mun = mysql_fetch_array($res_mun);
	}
?>
<body>
<?php
	if($permiso==2){
?>
<h1>Editar Terminal TEI: <?php echo $row_terminal["TEI"];?> / ISSI: <?php echo $row_terminal["ISSI"];?></h1>
<form action="update_terminal.php" method="POST" name="formterminal">
<h2><?php echo $h2admin;?></h2>
	<table>
		<TR>
			<TH class="t6c"><?php echo $tipotxt;?></TH>
			<TH class="t6c">Marca</TH>
			<TH class="t6c"><?php echo $modtxt;?></TH>
			<TH class="t6c"><?php echo $proveedor;?></TH>
			<TH class="t6c"><?php echo $amtxt;?></TH>
			<TH class="t6c"><?php echo $dotstxt;?></TH>
		</TR>
		<TR>
			<TD class="centro">
				<select name="tipo">
					<option value="F" <?php if ($tipo=="F") echo 'selected'; ?>><?php echo $fijo;?></option>
					<option value="M" <?php if ($tipo=="M") echo 'selected'; ?>><?php echo $movil;?></option>
					<option value="MB" <?php if ($tipo=="MB") echo 'selected'; ?>><?php echo "- $movilb";?></option>
					<option value="MA" <?php if ($tipo=="MA") echo 'selected'; ?>><?php echo "- $movila";?></option>
					<option value="MG" <?php if ($tipo=="MG") echo 'selected'; ?>><?php echo "- $movilg";?></option>
					<option value="P" <?php if ($tipo=="P") echo 'selected'; ?>><?php echo $portatilb;?></option>
					<option value="PB" <?php if ($tipo=="PB") echo 'selected'; ?>><?php echo "- $portatilb";?></option>
					<option value="PA" <?php if ($tipo=="PA") echo 'selected'; ?>><?php echo "- $portatila";?></option>
					<option value="PX" <?php if ($tipo=="PX") echo 'selected'; ?>><?php echo "- $portatilx";?></option>
                                        <option value="D" <?php if ($tipo=="D") echo 'selected'; ?>><?php echo $despacho;?></option>
				</select>
			</TD>
			<TD class="centro">
				<input type="text" name="marca" size="20" value="<?php echo $row_terminal["MARCA"];?>">
			</TD>
			<TD class="centro">
				<input type="text" name="modelo" size="20" value="<?php echo $row_terminal["MODELO"];?>">
			</TD>
			<TD class="centro">
				<input type="text" name="proveedor" size="20" value="<?php echo $row_terminal["PROVEEDOR"];?>">
			</TD>
			<TD class="centro">
				<select name="am" onChange="document.formulario.submit();">
					<option value="SI" <?php if ($am=="SI") echo 'selected'; ?>>SI</option>
					<option value="NO" <?php if ($am=="NO") echo 'selected'; ?>>NO</option>
				</select>
			</TD>
			<TD class="centro">
				<select name="dots" onChange="document.formulario.submit();">
					<option value="SI" <?php if ($dots=="SI") echo 'selected'; ?>>SI</option>
					<option value="NO" <?php if ($dots=="NO") echo 'selected'; ?>>NO</option>
				</select>
			</TD>
		</TR>
	</table>
<h2>Datos de la Flota</h2>
	<table>
		<TR>
			<TH class="t40p"><?php echo $nomflota;?></TH>
			<TH class="t10c"><?php echo $acroflota;?></TH>
			<TH class="t40p"><?php echo $localiza;?></TH>
			<TH class="t10c"><?php echo $irflota;?></TH>
		</TR>
		<TR>
			<TD><?php echo utf8_encode($row_flota["FLOTA"]);?></TD>
			<TD><?php echo $row_flota["ACRONIMO"];?></TD>
			<TD><?php echo utf8_encode($row_flota["DOMICILIO"])." &mdash; ".$row_flota["CP"]." ".utf8_encode($row_mun["MUNICIPIO"]);?></TD>
			<TD class="centro"><a href="detalle_flota.php?id=<?php echo $row_flota["ID"];?>"><img src="imagenes/ir.png" alt="Ir"></a></TD>
		</TR>
	</table>
<h3>Contactos de la Flota Usuaria</h3>
<?php
	if (($row_flota["RESPONSABLE"]=="0")&&($row_flota["CONTACTO1"]=="0")&&($row_flota["CONTACTO2"]=="0")&&($row_flota["CONTACTO3"]=="0")){
?>
		<p class='error'><?php echo $nocont;?></p>
<?php
	}
	else{
?>
	<table>
		<TR>
			<TD class="t10c">&nbsp;</TD>
			<TH class="t4c"><?php echo $nomflota;?></TH>
			<TH class="t4c"><?php echo $cargo;?></TH>
			<TH class="t10c"><?php echo $telefono;?></TH>
			<TH class="t10c"><?php echo $movil;?></TH>
			<TH class="t5c"><?php echo $mail;?></TH>
		</TR>

<?php
		// Datos de contactos
		$id_contacto = array($row_flota["RESPONSABLE"], $row_flota["CONTACTO1"], $row_flota["CONTACTO2"], $row_flota["CONTACTO3"]);
		$nom_contacto = array("Responsable", $contacto." 1", $contacto." 2", $contacto." 3");
		$par = 0;
		// Datos de contactos
		for ($i = 0; $i < count($id_contacto); $i++){
			if($id_contacto[$i] != 0){
				$idc = $id_contacto[$i];
				$sql_contacto = "SELECT * FROM contactos WHERE ID=$idc";
				$res_contacto=mysql_db_query($base_datos,$sql_contacto) or die ("Error en la consulta de contacto: ".mysql_error());
				$ncontacto=mysql_num_rows($res_contacto);
				if($ncontacto!=0){
					$row_contacto=mysql_fetch_array($res_contacto);
?>
					<TR <?php if (($par % 2)==1) echo "class='filapar'";?>>
						<TH><?php echo $nom_contacto[$i]; ?></TH>
						<TD><?php echo utf8_encode($row_contacto["NOMBRE"]);?></TD>
						<TD><?php echo utf8_encode($row_contacto["CARGO"]);?></TD>
						<TD><?php echo utf8_encode($row_contacto["TELEFONO"]);?></TD>
						<TD><?php echo utf8_encode($row_contacto["MOVIL"]);?></TD>
						<TD><?php echo utf8_encode($row_contacto["MAIL"]);?></TD>
					</TR>
<?php
					$par++;
				}
			}
		}
?>
	</table>
<?php
	}
?>
<h2><?php echo $h2term;?></h2>
	<table>
            <TR class="filapar">
			<TH class="t4c">ISSI</TH>
			<TD><input type="text" name="issi" size="10" value="<?php echo $row_terminal["ISSI"];?>"></TD>
			<TH class="t4c">TEI</TH>
			<TD><input type="text" name="tei" size="20" value="<?php echo $row_terminal["TEI"];?>"></TD>
		</TR>
                <TR>
			<TH class="t4c"><?php echo $cdhw;?></TH>
			<TD><input type="text" name="codigohw" size="20" value="<?php echo $row_terminal["CODIGOHW"];?>"></TD>
			<TH class="t4c"><?php echo $nserie;?></TH>
			<TD><input type="text" name="nserie" size="20" value="<?php echo $row_terminal["NSERIE"];?>"></TD>
		</TR>
		<TR class="filapar">
			<TH class="t4c">ID</TH>
			<TD><?php echo $row_terminal["ID"];?></TD>
			<TH class="t4c"><?php echo $mnemo;?></TH>
			<TD><input type="text" name="mnemonico" size="20" value="<?php echo $row_terminal["MNEMONICO"];?>"></TD>
		</TR>
		<TR>
			<TH class="t4c"><?php echo $llamada;?> Semi-Dúplex</TH>
			<TD>
				<select name="semid">
					<option value="SI" <?php if ($row_terminal["SEMID"]=="SI") echo " 'selected'";?>>SI</option>
					<option value="NO" <?php if ($row_terminal["SEMID"]=="NO") echo " 'selected'";?>>NO</option>
				</select>
			</TD>
			<TH class="t4c"><?php echo $llamada;?> Dúplex</TH>
			<TD>
				<select name="duplex">
					<option value="SI" <?php if ($row_terminal["DUPLEX"]=="SI") echo " 'selected'";?>>SI</option>
					<option value="NO" <?php if ($row_terminal["DUPLEX"]=="NO") echo " 'selected'";?>>NO</option>
				</select>
		</TR>
		<TR class="filapar">
			
	<?php
		switch ($row_terminal["ESTADO"]){
			case "A":{
				$estado = $alta;
				$fecha_nom = $falta;
				$fecha_val = $row_terminal["FALTA"];
				break;
			}
			case "B":{
				$estado = $baja;
				$fecha_nom = $fbaja;
				$fecha_val = $row_terminal["FBAJA"];
				break;
			}
			case "R":{
				// Se busca la incidencia
				$sql_incid = "SELECT * FROM incidencias WHERE TERMINAL = '$id' ORDER BY ID DESC";
				$res_incid = mysql_db_query($base_datos,$sql_incid) or die ("Error en la consulta de Incidencia: ".mysql_error());
				$nincid=mysql_num_rows($res_incid);
				if($nflota==0){
					$estado = "<p class='error'>No hay resultados en la consulta de Incidencias</p>\n";
				}
				else{
					$row_incid = mysql_fetch_array($res_incid);
					$id_incid = $row_incid["ID"];
					$estado = "$rep - <a href='detalle_incidencia.php?id=$id_incid'><img src='imagenes/consulta.png'></a>";
					$fecha_val = $row_incid["FAVERIA"];
				}
				$fecha_nom = $frep;
				break;
			}
		}
	?>
		<TR>
			<TH class="t4c">Estado</TH>
			<TD><?php echo $estado;?></TD>
			<TH class="t4c"><?php echo $fecha_nom;?></TH>
			<TD><?php echo $fecha_val;?></TD>
		</TR>
		<TR class="filapar">
		

	<?php
		if($permiso==2){
	?>
			<TH class="t4c">Carpeta</TH>
			<TD><input type="text" name="carpeta" size="20" value="<?php echo $row_terminal["CARPETA"];?>"></TD>
			<TH class="t4c">Nº K</TH>
			<TD><input type="text" name="numk" size="35" value="<?php echo $row_terminal["NUMEROK"];?>"></TD>
			</TR>
			<TR>
				<TH class="t4c"><?php echo $observ;?></TH>
				<TD colspan='3'>
				<input type="text" name="observaciones" size="40" value="<?php echo utf8_encode($row_terminal["OBSERVACIONES"]);?>"></TD>
			</TR>
	<?php
		}
		else{
	?>
				<TH class="t4c">Carpeta</TH>
				<TD><input type="text" name="carpeta" size="20" value="<?php echo $row_terminal["CARPETA"];?>"></TD>
				<TH class="t4c"><?php echo $observ;?></TH>
				<TD>
				<input type="text" name="observaciones" size="40" value="<?php echo utf8_encode($row_terminal["OBSERVACIONES"]);?>"></TD>
	<?php
		}
	?>
		</TR>
	</table>
	<input type="hidden" name="idterm" value="<?php echo $id;?>">
	<input type="hidden" name="origen" value="editar">
<?php
	if($permiso==2){
?>
	<table>
		<tr>
			<TD class="borde">
				<input type='image' name='action' src='imagenes/guardar.png' alt='<?php echo $botguarda;?>' title="<?php echo $botguarda;?>">
				<br><?php echo $botguarda;?>
			</TD>
			<TD class="borde">
				<a href='detalle_terminal.php?id=<?php echo $id;?>'>
					<img src='imagenes/atras.png' alt='<?php echo $botatras;?>' title="<?php echo $botatras;?>">
				</a><br><?php echo $botatras;?>
			</TD>
			<TD class="borde">
				<a href='#' onclick='document.formterminal.reset();'>
					<img src='imagenes/no.png' alt='<?php echo $botcancel;?>' title="<?php echo $botcancel;?>">
				</a><br><?php echo $botcancel;?>
			</TD>
		</tr>
	</table>
<?php
	}
?>
</form>
<?php
	}
	else{
?>
	<h1><?php echo $h1perm;?></h1>
	<p class='error'><?php echo $permno;?></p>
<?php
	}
?>
</body>
</html>
