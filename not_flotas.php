<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotanot_$idioma.php";
include ($lang);

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
        if (isset($idm)&&isset($update)&&($update == "OK")){
?>
        <p class="flashok">
            <img src="imagenes/okm.png" alt="OK" title="OK"> &mdash; <?php echo $mensflash;?>
        </p>
<?php
        }
?>
        <h1><?php echo $h1; ?></h1>
        <h2><?php echo $h2dest;?></h2>
        <h4><?php echo $h4selec;?></h4>
        <form name="criterios" action="not_flotas.php" method="POST">
            <input type="hidden" name="idm" value="<?php echo $idm;?>">
            <table>
                <tr>
<?php


?>
                    <td>
                        Flota: &nbsp;
                        <select name="flotasel" onchange="document.criterios.submit();">
                            <option value="NN">Seleccionar</option>
<?php
                            $sql_selflotas = "SELECT * FROM flotas ORDER BY FLOTA ASC";
                            $res_selflotas = mysql_query($sql_selflotas) or die(mysql_error());
                            $nselflotas = mysql_num_rows($res_selflotas);
                            for ($i = 0; $i < $nselflotas; $i ++){
                                $row_selflota = mysql_fetch_array($res_selflotas);
                                $idf = $row_selflota["ID"];
?>
                                <option value="<?php echo $idf;?>" <?php if ($flotasel == $idf){echo 'selected';}?>>
                                    <?php echo $row_selflota["FLOTA"];?>
                                </option>
<?php
                            }
?>
                        </select>
                    </td>
<?php
                    if (($flotasel=="")||($flotasel=="NN")){
?>
                        <td>
                            <?php echo $labprov;?>: &nbsp;
                            <select name="prov" onchange="document.criterios.submit();">
                                <option value="NN">Seleccionar</option>
                                <option value="03" <?php if ($prov == "03"){echo 'selected';}?>>Alicante\Alacant</option>
                                <option value="12" <?php if ($prov == "12"){echo 'selected';}?>>Castellón\Castelló</option>
                                <option value="46" <?php if ($prov == "46"){echo 'selected';}?>>Valencia\València</option>
                            </select>
                        </td>
<?php
                        $sql_muni = "SELECT * FROM MUNICIPIOS ";
                        if (($prov!="")&&($prov!="NN")){
                            $sql_muni = $sql_muni."WHERE INE LIKE '$prov%' ";
                        }
                        $sql_muni = $sql_muni."ORDER BY PROVINCIA, MUNICIPIO ASC";
?>
                        <td>
                            <?php echo $labmuni;?>: &nbsp;
                            <select name="muni" onchange="document.criterios.submit();">
                                <option value="NN">Seleccionar</option>
<?php
                                $res_muni = mysql_query($sql_muni) or die(mysql_error());
                                $nmuni = mysql_num_rows($res_muni);
                                for ($i = 0; $i < $nmuni; $i ++){
                                    $row_muni = mysql_fetch_array($res_muni);
                                    $ine = $row_muni["INE"];
                                    $municipio = $row_muni["MUNICIPIO"];
?>
                                    <option value="<?php echo $ine;?>" <?php if ($muni == $ine){echo 'selected';}?>>
                                        <?php echo $municipio;?>
                                    </option>
<?php
                                }
?>
                            </select>
                        </td>
<?php
                    }
?>
                </tr>
            </table>
        </form>
        <form name="flotas" action="newnotflotas.php" method="POST">
            <input type="hidden" name="idm" value="<?php echo $idm;?>">
        <h2>
            <?php echo $h2env;?> &mdash;
            <input type="image" src="imagenes/mail.png" alt="E-mail" title="E-mail">
        </h2>
<?php
        $sql_flotas = "SELECT * FROM flotas ";
        if (($flotasel!="")&&($flotasel!="NN")){
            $sql_flotas = $sql_flotas."WHERE ID = '$flotasel' ";

        }
        elseif (($muni!="")&&($muni!="NN")){
            $sql_flotas = $sql_flotas."WHERE INE = '$muni' ";
        }
        elseif (($prov!="")&&($prov!="NN")){
            $sql_flotas = $sql_flotas."WHERE INE LIKE '$prov%' ";
        }
        $sql_flotas = $sql_flotas."ORDER BY flotas.FLOTA ASC";
        $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
        $nflotas = mysql_num_rows($res_flotas);
?>
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
<?php
                //* CABECERA  *//
                for ($i = 0; $i < count($campos); $i++) {
?>
                    <th><?php echo $campos[$i]; ?></th>
<?php
                    if ($i == 0){
?>
                        <th><input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" /></th>
<?php
                    }
                }
?>
                </tr>
<?php
               for ($i = 0; $i < $nflotas; $i++) {
                    $row_flota = mysql_fetch_array($res_flotas);
                    $idflota = $row_flota["ID"];
                    $sql_contflotas = "SELECT * FROM contactos_flotas WHERE (FLOTA_ID = " . $idflota .")";
                    $sql_contflotas .= " AND ((ROL = 'RESPONSABLE') OR (ROL = 'OPERATIVO')) ORDER BY ROL DESC";
                    $res_contflotas = mysql_query($sql_contflotas) or die(mysql_error() . $sql_contflotas);
                    $ncontflotas = mysql_num_rows($res_contflotas);
                    $idcont = array();
                    for ($j = 0; $j < $ncontflotas; $j ++){
                        $cont_flota = mysql_fetch_array($res_contflotas);
                        $idc = $cont_flota['CONTACTO_ID'];
                        if (!in_array($idc, $idcont)){
                            array_push($idcont, $idc);
                        }
                    }
                    $ncont = 0;
                    if (count($idcont) > 0){
                        $idvector = "(";
                        for ($j = 0; $j < count($idcont); $j ++){
                            if ($j > 0){
                                $idvector = $idvector . ", ";
                            }
                            $idvector = $idvector . $idcont[$j];
                        }
                        $idvector = $idvector . ")";
                        $sql_cont = "SELECT * FROM contactos WHERE ID IN $idvector AND MAIL <> ''";
                        $res_cont = mysql_query($sql_cont) or die(mysql_error().$sql_cont);
                        $ncont = mysql_num_rows($res_cont);
                    }
                    if ($ncont <= 1){
                        $nfilas = 1;
                    }
                    else {
                        $nfilas = $ncont;
                    }
                    for ($j = 0; $j < $nfilas ; $j++){
                       if ($ncont > 0){
                           $row_cont = mysql_fetch_array($res_cont);
                       }
?>
                        <tr <?php if (($i % 2) == 0) {echo " class='filapar'";}?>>

<?php
                        if ($j == 0){
?>
                            <td <?php if ($nfilas > 1){echo 'rowspan="'.$nfilas.'"';}?>><?php echo $row_flota["FLOTA"]; ?></td>
<?php
                        }
                        if ($ncont > 0){
?>
                            <td class='centro'>
                                <input type="checkbox" name="idincid[]" value="<?php echo $idflota."-".$row_cont["ID"];?>" />
                            </td>
                            <td><?php echo $row_cont["NOMBRE"]; ?></td>
                            <td><?php echo $row_cont["CARGO"]; ?></td>
                            <td><?php echo $row_cont["MAIL"]; ?></td>
<?php
                        }
                        else{
?>
                            <td colspan="4"><?php echo $errnomail; ?></td>
<?php
                        }
?>
                        </tr>
<?php
                    } //
                }
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
