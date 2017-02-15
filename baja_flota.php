<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotaayb_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
        include("conexion.php");
        $base_datos=$dbbdatos;
        $link=mysql_connect($dbserv,$dbusu,$dbpaso);
        if(!link){
            echo "<b>ERROR MySQL:</b>".mysql_error();
        }
// ------------------------------------------------------------------------------------- //

// Importamos las variables de formulario:
import_request_variables("p", "");

/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación (Oficina COMDES)
 */
// Obtenemos el usuario
include_once('auth_user.php');

$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $titbaja;?> de la Flota COMDES</title>
    <link rel="StyleSheet" type="text/css" href="estilo.css">
<?php
    // Si la sesión de Joomla ha caducado, recargamos la página principal
    if ($flota_usu == 0){
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

	if($permiso==2){
		//datos de la tabla Flotas
		$sql_flota="SELECT * FROM flotas WHERE ID='$id'";
		$res_flota=mysql_db_query($base_datos,$sql_flota) or die ("Error en la consulta de Flota: ".mysql_error());
		$nflota=mysql_num_rows($res_flota);
		if($nflota==0){
			echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
		}
		else{
			$row_flota=mysql_fetch_array($res_flota);
                        $flota_org = utf8_encode($row_flota["FLOTA"]);
                        $acro_org = utf8_encode($row_flota["ACRONIMO"]);
		}
?>
    <h1>Desactivar la Flota <?php echo $flota_org;?> (<?php echo $acro_org;?>)</h1>
<?php
                if ($row_flota["ACTIVO"]=="NO"){
?>
                    <h2>Flota <?php echo $flota_org;?> (<?php echo $acro_org;?>) desactivada</h2>
                    <div id="resultado">
                        <p><img src='imagenes/error.png' alt='Error'></p>
                        <p><span class="error"><?php echo $errbaja;?></span></p>
                        <p><a href="detalle_flota.php?id=<?php echo $id?>"><img src='imagenes/back.png' alt='<?php echo $botatras;?>' title='<?php echo $botatras;?>'></a><BR><?php echo $botatras;?></p>
                    </div>
<?php
                }
                else{
?>
                    <h2><?php echo $h2baja;?></h2>
                    <form name="formaflota" action="update_flota.php" method="POST">
                        <input type="hidden" name="idflota" value="<?php echo $id;?>">
                        <input type="hidden" name="origen" value="baja">
                        <input type="hidden" name="flota_org" value="<?php echo $flota_org;?>">
                        <input type="hidden" name="acro_org" value="<?php echo $acro_org;?>">
                        <div class="centro">
                            <p><img src='imagenes/important.png' alt='Error'></p>
                            <p><span class="error"><?php echo $mensbaja;?></span></p>
                            <table>
                                    <TR>
                                            <TD class="borde">
                                                    <input type='image' name='action' src='imagenes/ok.png' alt='<?php echo $botacept;?>' title='<?php echo $botacept;?>'><br><?php echo $botacept;?>
                                            </TD>
                                            <TD class="borde">
                                                    <a href='detalle_flota.php?id=<?php echo $id?>'>
                                                            <img src='imagenes/no.png' alt='<?php echo $botcancel;?>' title='<?php echo $botcancel;?>'>
                                                    </a><br><?php echo $botcancel;?>
                                            </TD>
                                    </TR>
                            </table>
                        </div>
                    </form>
<?php
            }
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