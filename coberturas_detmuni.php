<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/cobdetmuni_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
}
else{
    // Codificación de carácteres de la conexión a la BBDD
    mysql_set_charset('utf8',$link);
}
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //

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
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $titulo; ?></title>
    <link rel="StyleSheet" type="text/css" href="estilo.css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <?php
    // Si la sesión de Joomla ha caducado, recargamos la página principal
    if ($flota_usu = 0){
    ?>
        <script type="text/javascript">
            window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
        </script>
    <?php
    }
    ?>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/coberturas_detmuni.js"></script>
</head>
<body>
	<?php
    if ($permiso > 1){
		// Consulta de Municipio:
        $sql_muni = "SELECT * FROM municipios WHERE INE = " . $idmuni;
        $res_muni = mysql_query($sql_muni) or die(mysql_error() . ' - ' . $sql_muni);
        $nmuni = mysql_num_rows($res_muni);
        $provincias = array(
            '03' => 'Alicante/Alacant', '12' => 'Castellón/Castelló',
            '46' => 'Valencia/València', '16' => 'Cuenca',
            '43' => 'Tarragona', '44' => 'Teruel'
        );
	?>
		<h1>
            <?php echo $h1; ?>
            &mdash;
            <a href="#" id="newtab"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
            &mdash;
            <a href="#" id="export"><img src="imagenes/xls.png" alt="Exportar a Excel" title="Exportar a Excel"></a>
            &mdash;
            <a href="coberturas.php"><img src="imagenes/volver.png" alt="<?php echo $botatras;?>" title="<?php echo $botatras;?>"></a>
        </h1>
        <form action="coberturas_detmuni.php" name="formmunidetalle" id="formmunidetalle" method="POST" target="_blank">
            <input type="hidden" name="idmuni" value="<?php echo $idmuni;?>" />
        </form>
        <form action="xlscobmuni.php" name="formmuniexport" id="formmuniexport" method="POST" target="_blank">
            <input type="hidden" name="idmuni" value="<?php echo $idmuni;?>" />
        </form>
		<h2><?php echo $h2muni; ?></h2>
		<?php
		if ($nmuni > 0){
			$muni = mysql_fetch_array($res_muni);
		?>
			<table>
                <tr>
                    <th><?php echo $thine;?></th>
                    <th><?php echo $thprov;?></th>
                    <th><?php echo $thmuni;?></th>
                    <th><?php echo $thpob;?></th>
                </tr>
                <tr>
                    <td><?php echo $muni['INE']; ?></td>
                    <td><?php echo $muni['PROVINCIA']; ?></td>
                    <td><?php echo $muni['MUNICIPIO']; ?></td>
                    <td><?php echo $muni['POBLACION']; ?></td>
                </tr>
            </table>
            <div id="contenido">
                <div id="pestanyas">
                    <ul id="tab">
                        <li><a href="#" id="linktbs"><?php echo $tabtbs;?></a></li>
                        <li><a href="#" id="linkflotas"><?php echo $tabflotas;?></a></li>
                    </ul>
                </div>
                <div id="limpia"></div>
                <div id="cobtbs">
                    <?php
                    $sql_muncob = "SELECT coberturas.porcentaje, emplazamientos.* FROM coberturas, emplazamientos";
                    $sql_muncob .= " WHERE (coberturas.municipio_id = " . $idmuni . ") AND (coberturas.emplazamiento_id = emplazamientos.id)";
                    $sql_muncob .= " ORDER BY coberturas.porcentaje DESC";
                    $res_muncob = mysql_query($sql_muncob) or die(mysql_error());
                    $nmuncob = mysql_num_rows($res_muncob)
                    ?>
                    <h2><?php echo $h2tbs . ' &mdash; ' . $nmuncob . ' TBS';?></h2>
                    <?php
                    if ($nmuncob > 0){
                    ?>
                        <table>
                            <tr>
                                <th><?php echo $themplaza; ?></th>
                                <th><?php echo $thtitular; ?></th>
                                <th><?php echo $thlatitud; ?></th>
                                <th><?php echo $thlongitud; ?></th>
                                <th><?php echo $thporcent; ?></th>
                                <th><?php echo $thpobcob; ?></th>
                            </tr>
                            <?php
                            $porcob = 0;
                            for ($i = 0; $i < $nmuncob; $i++){
                                $muncob = mysql_fetch_array($res_muncob);
                                $porcob += $muncob['porcentaje'];
                            ?>
                                <tr <?php if(($i % 2) == 1) {echo "class='filapar'";}?>>
                                    <td><?php echo $muncob['emplazamiento']; ?></td>
                                    <td><?php echo $muncob['titular']; ?></td>
                                    <td><?php echo $muncob['latitud']; ?></td>
                                    <td><?php echo $muncob['longitud']; ?></td>
                                    <td><?php echo round($muncob['porcentaje'], 2); ?></td>
                                    <td><?php echo round($muncob['porcentaje'] * $muni['POBLACION']/100); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                            <tr>
                                <th colspan="4"><?php echo $thtotales; ?></th>
                                <td><?php echo round($porcob, 2); ?></td>
                                <td><?php echo round($porcob * $muni['POBLACION']/100); ?></td>
                            </tr>
                        </table>
                    <?php
                    }
                    else{
                    ?>
                        <p class='error'><?php echo $errnotbs; ?></p>
                    <?php
                    }
                    ?>
                </div>
                <div id="cobflotas">
                    <h2>
                        <?php
                        echo $h2flotas;
                        ?>
                    </h2>
                    <?php
                    $ambitos = array('AUT', 'PROV', 'LOC');
                    $h4amb = array($h4aut, $h4prov, $h4local);
                    $txtamb = array($txtaut, $txtprov, $txtlocal);
                    for ($j = 0; $j < count($ambitos); $j++){
                        $sql_flotas = "SELECT * FROM flotas WHERE (AMBITO = '" . $ambitos[$j] . "')";
                        if ($ambitos[$j] == 'PROV'){
                            $idprov = $muni['CPRO'];
                            $sql_flotas .= " AND (INE LIKE '" . $idprov[0] . "%')";
                        }
                        if ($ambitos[$j] == 'LOC'){
                            $sql_flotas .= " AND (INE = '" . $idmuni . "')";
                        }
                        $sql_flotas .= " ORDER BY FLOTA ASC";
                        $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
                        $nflotas = mysql_num_rows($res_flotas);
                    ?>
                    <h4>
                        <?php
                        echo $h4amb[$j];
                        if ($nflotas > 0){
                            echo ' &mdash; ' . $nflotas . ' ' . $txtflotas;
                        }
                        ?>
                    </h4>
                    <?php
                    if ($nflotas > 0){
                    ?>
                        <table>
                            <tr>
                                <th>Flota</th>
                                <th><?php echo $thacro;?></th>
                                <th><?php echo $thcont;?></th>
                                <th><?php echo $thcargo;?></th>
                                <th><?php echo $thmail;?></th>
                                <th><?php echo $thoficial;?></th>
                            </tr>
                            <?php
                            for ($i = 0; $i < $nflotas; $i++){
                                $flota = mysql_fetch_array($res_flotas);
                                $sql_cont = "SELECT contactos.NOMBRE, contactos.CARGO, contactos.MAIL, contactos_flotas.ROL FROM contactos, contactos_flotas";
                                $sql_cont .= " WHERE (contactos_flotas.FLOTA_ID = " . $flota['ID'] . ") AND (contactos.ID = contactos_flotas.CONTACTO_ID)";
                                $sql_cont .= " AND (contactos_flotas.ROL = 'CONT24H')";
                                $res_cont = mysql_query($sql_cont) or die(mysql_error());
                                $ncont = mysql_num_rows($res_cont);
                            ?>
                                <tr <?php if (($i % 2) > 0) {echo "class='filapar'";}?>>
                                    <td><?php echo $flota['FLOTA'];?></td>
                                    <td><?php echo $flota['ACRONIMO'];?></td>
                                    <?php
                                    if ($ncont > 0){
                                        $contacto = mysql_fetch_array($res_cont);
                                    ?>
                                        <td><?php echo $contacto['NOMBRE'];?></td>
                                        <td><?php echo $contacto['CARGO'];?></td>
                                        <td><?php echo $contacto['MAIL'];?></td>
                                        <td><?php echo $flota['FORMCONT'];?></td>
                                    <?php
                                    }
                                    else{
                                    ?>
                                        <td colspan="4"><?php echo $txtnocont;?></td>
                                    <?php
                                    }
                                    ?>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    <?php
                    }
                    else{
                ?>
                        <p class='error'><?php echo sprintf($txtnoflota, $txtamb[$j]); ?></p>
                <?php
                    }
                }
                ?>
                </div>
            </div>
		<?php
		}
		else{
		?>
			<p class='error'><?php echo $errnomuni; ?></p>

		<?php
		}
		?>
	<?php
	}
	else{
	?>
		<h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $errnoperm; ?></p>
	<?php
	}
	?>
</body>
