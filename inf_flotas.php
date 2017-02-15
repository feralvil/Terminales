<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotainf_$idioma.php";
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
    if ($permiso == 2) {
        $sql_organizacion = "SELECT * FROM organizaciones ORDER BY ORGANIZACION ASC";
        $res_organizacion = mysql_query($sql_organizacion) or die("Error en la consulta de organizaciones: ".mysql_error());
        $norganiza = mysql_num_rows($res_organizacion);
?>
        <h1>
            <?php echo $h1; ?> &mdash; <a href="inf_flotas.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <form action="inf_flotas.php" name="formulario" method="POST">
            <h4><?php echo $criterios; ?></h4>
            <table>
                <tr>
                    <td>
                        <select name='prov' onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($prov == "00") || ($prov == "")) echo ' selected'; ?>>Seleccione <?php echo $provincia;?></option>
                            <option value='03' <?php if ($prov == "03") echo ' selected'; ?>><?php echo $alc;?></option>
                            <option value='12' <?php if ($prov == "12") echo ' selected'; ?>><?php echo $cas;?></option>
                            <option value='46' <?php if ($prov == "46") echo ' selected'; ?>><?php echo $val;?></option>
                        </select>
                    </td>
                    <td>
                        <select name='organiza' onChange='document.formulario.submit();'>
                            <option value='00' <?php if (($prov == "00") || ($prov == "")) echo ' selected'; ?>>Seleccione <?php echo $thorg;?></option>
                            <?php
                            for ($i = 0; $i < $norganiza; $i++){
                                $organizacion = mysql_fetch_array($res_organizacion);
                            ?>
                                <option value='<?php echo $organizacion['ID'];?>' <?php if ($organiza == $organizacion['ID']) echo ' selected'; ?>>
                                    <?php echo $organizacion['ORGANIZACION'];?>
                                </option>

                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
<?php
            $sql_flotas = "SELECT flotas.ID, flotas.FLOTA, flotas.ACRONIMO, organizaciones.ORGANIZACION";
            $sql_flotas .= " FROM flotas, organizaciones WHERE (flotas.ORGANIZACION = organizaciones.ID)";
            if (($prov != '') && ($prov != "00")) {
                $sql_flotas .= " AND (flotas.INE LIKE '$prov%')";
            }
            if (($organiza != '') && ($organiza != "00")) {
                $sql_flotas .= " AND (flotas.ORGANIZACION = $organiza)";
            }
            $sql_flotas .= " ORDER BY organizaciones.ORGANIZACION ASC, flotas.FLOTA ASC";
            $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
            $nflotas = mysql_num_rows($res_flotas);
?>
        </form>
        <form name="flotas" action="export.php" method="POST" target="_blank">
            <input type="hidden" name="prov" value="<?php echo $prov;?>"/>
            <input type="hidden" name="organiza" value="<?php echo $organiza;?>"/>
            <h4><?php echo $h4res; ?></h4>
            <table>
                <tr class="borde">
                    <td class="borde"><?php echo $nreg; ?>: <b><?php echo $nflotas; ?></b>.</td>
<?php
                    if ($nflotas > 0) {
?>
                    <td class="borde">
                        <a href="#" onclick="document.flotas.action='xlsinfflotas.php';document.flotas.submit()"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
                        &mdash;
                        <a href="#" onclick="alert('<?php echo $txtalert; ?>');document.flotas.action='xlscontflotas.php';document.flotas.submit()"><img src="imagenes/contactosxls.png" alt="<?php echo $txtcont;?>" title="<?php echo $txtcont;?>"></a>
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
                $tterm = array(0, 0, 0, 0, 0);
                //*TABLA CON RESULTADOS*//
?>
                <tr>
                    <th><input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" /></th>
                    <th><?php echo $thorg; ?></th>
                    <th><?php echo $thflota; ?></th>
                    <th><?php echo $thacro; ?></th>
                    <th><?php echo $thterm; ?></th>
                    <th><?php echo $thtbase; ?></th>
                    <th><?php echo $thtmov; ?></th>
                    <th><?php echo $thport; ?></th>
                    <th><?php echo $thdesp; ?></th>
                </tr>
<?php
                $norganiza = 0;
                $orgact = "Nada";
                for ($i = 0; $i < $nflotas; $i++) {
                    $row_flota = mysql_fetch_array($res_flotas);
                    if ($row_flota['ORGANIZACION'] != $orgact){
                        $norganiza++;
                    }
                    $orgact = $row_flota['ORGANIZACION'];
                    $idflota = $row_flota["ID"];
                    //datos de la tabla Terminales
                    // Tipos de termninales
                    $tipos = array("%", "F", "M%", "P%", "D");
                    $nterm = array(0, 0, 0, 0, 0);
                    for ($j = 0; $j < count($tipos); $j++){
                        $sql_term = "SELECT * FROM terminales WHERE (FLOTA='$idflota') AND (TIPO LIKE '" . $tipos[$j] . "')";
                        $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminales" . mysql_error());
                        $nterm[$j] = mysql_num_rows($res_term);
                        $tterm[$j] = $tterm[$j] + $nterm[$j];
                    }
?>
                    <tr <?php if (($i % 2) == 1) {echo " class='filapar'";}?>>
                        <td class='centro'>
                            <input type="checkbox" name="idflota[]" value="<?php echo $idflota;?>" />
                        </td>
                        <td><?php echo $row_flota['ORGANIZACION']; ?></td>
                        <td><?php echo $row_flota['FLOTA']; ?></td>
                        <td><?php echo $row_flota['ACRONIMO']; ?></td>
<?php
                    for ($j = 0; $j < count($tipos); $j++) {
?>
                        <td class='centro'><?php echo number_format($nterm[$j], 0, ',', '.'); ?></td>
<?php
                    }
?>
                </tr>
<?php
                } //primer for
?>
                <tr><td colspan='10'>&nbsp;</td></tr>
                <tr class="filapar">
                    <th colspan="2"><?php echo "$totalorg - $norganiza"; ?></th>
                    <th colspan="2"><?php echo "$totales - $nflotas"; ?></th>
<?php
                    for ($j = 0; $j < count($tterm); $j++) {
?>
                        <td class='centro'><?php echo number_format($tterm[$j], 0, ',', '.'); ?></td>
<?php
                    }
?>
                </tr>
<?php
            }
?>
        </table>
        </form>

<?php
    } // Si el usuario no es el de la Oficina
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
    }
?>
    </body>
</html>
