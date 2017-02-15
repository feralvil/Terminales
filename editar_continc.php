<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contedi_$idioma.php";
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

import_request_variables("gp", "");

// Importamos las variables de formulario:
import_request_variables("gp", "");

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
        <title><?php echo $titulo; ?></title>
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
        <form name="contincid" action="contacto_incid.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
<?php
if ($permiso == 2) {
    if (($detalle=="Incid 1")||($detalle=="Incid 2")||($detalle=="Incid 3")||($detalle=="Incid 4")){
        if ($detalle == "Incid 1") {
            $id_contacto = $id_inc1;
            $nombre = "id_inc1";
            $dettxt = "$contacto 1 $continc";
        }
        if ($detalle == "Incid 2") {
            $id_contacto = $id_inc2;
            $nombre = "id_inc2";
            $dettxt = "$contacto 2 $continc";
        }
        if ($detalle == "Incid 3") {
            $id_contacto = $id_inc3;
            $nombre = "id_inc3";
            $dettxt = "$contacto 3 $continc";
        }
        if ($detalle == "Incid 4") {
            $id_contacto = $id_inc4;
            $nombre = "id_inc4";
            $dettxt = "$contacto 4 $continc";
        }
        $linkpdf = "document.expcontacto.formato.value='pdf';document.expcontacto.submit();";
        $linkxls = "document.expcontacto.formato.value='xls';document.expcontacto.submit();";
?>
        <form name="expcontacto" action="expcontacto.php" method="POST" target="_blank">
            <input type="hidden" name="formato" value="#">
            <input type="hidden" name="id_contacto" value="<?php echo $id_contacto;?>">
            <input type="hidden" name="flota" value="<?php echo $flota;?>">
            <input type="hidden" name="acronimo" value="<?php echo $acronimo;?>">
            <input type="hidden" name="detalle" value="<?php echo $detalle;?>">
        </form>
        <h1>
            <?php echo $detalltxt; ?> del <?php echo $dettxt; ?> de la Flota <?php echo $flota; ?> (<?php echo $acronimo; ?>) &mdash;
            <a href="#" onclick="<?php echo $linkpdf; ?>"><img src="imagenes/pdf.png" alt="PDF" title="PDF"></a> -
            <a href="#" onclick="<?php echo $linkxls; ?>"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
        </h1>
<?php
            // Datos de contactos
            $sql_contacto = "SELECT * FROM contactos WHERE ID=$id_contacto";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto != 0) {
                $row_contacto = mysql_fetch_array($res_contacto);
?>
                <table>
                    <tr>
                        <th class="t5c"><?php echo $nomtxt; ?></th>
                        <td class="t40p"><?php echo $row_contacto["NOMBRE"]; ?></td>
                        <th class="t5c">NIF/CIF</th>
                        <td class="t5c"><?php echo $row_contacto["NIF"]; ?></td>
                    </tr>
                    <tr class="filapar">
                        <th class="t5c"><?php echo $cargo; ?></th>
                        <td class="t40p"><?php echo $row_contacto["CARGO"]; ?></td>
                        <th class="t5c">ID</th>
                        <td class="t5c"><?php echo $row_contacto["ID"]; ?></td>
                    </tr>
                    <tr>
                        <th class="t5c"><?php echo $telefono; ?></th>
                        <td class="t40p"><?php echo $row_contacto["TELEFONO"]; ?></td>
                        <th class="t5c"><?php echo $telefono; ?> GVA</th>
                        <td class="t5c"><?php echo $row_contacto["TLFGVA"]; ?></td>
                    </tr>
                    <tr class="filapar">
                        <th class="t5c"><?php echo $movil; ?></th>
                        <td class="t40p"><?php echo $row_contacto["MOVIL"]; ?></td>
                        <th class="t5c"><?php echo $movil; ?> GVA</th>
                        <td class="t5c"><?php echo $row_contacto["MOVILGVA"]; ?></td>
                    </tr>
                    <tr>
                        <th class="t5c"><?php echo $mail; ?></th>
                        <td class="t40p"><?php echo $row_contacto["MAIL"]; ?></td>
                        <th class="t5c"><?php echo $horario;?></th>
                        <td class="t5c"><?php echo $row_contacto["HORARIO"]; ?></td>
                    </tr>
                </table>
                <form name="detalle_cont" action="editar_continc.php" method="POST">
                    <input type="hidden" name="<?php echo $nombre; ?>" value="<?php echo $id_contacto; ?>">
                    <input type="hidden" name="flota" value="<?php echo $flota; ?>">
                    <input type="hidden" name="acronimo" value="<?php echo $acronimo; ?>">
                    <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                    <input type="hidden" name="editar" value="">
                    <input type="hidden" name="borrar" value="">
                    <table>
                        <tr>
                            <td class="borde">
                                <input type='image' name='imgedit' value="<?php echo $detalle; ?>" src='imagenes/pencil.png' alt='<?php echo $modificar; ?>' title='<?php echo $modificar; ?>' onclick="this.form.editar.value=this.value"><br><?php echo $modificar; ?>
                            </td>
                            <td class="borde">
                                <input type='image' name='imgdel' value="<?php echo $detalle; ?>" src='imagenes/no.png' alt='<?php echo $borrtxt; ?>' title='<?php echo $borrtxt; ?>' onclick="this.form.borrar.value=this.value"><br><?php echo $borrtxt; ?>
                            </td>
                            <td class="borde">
                                <a href='#' onclick='document.contincid.submit();'><img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title='<?php echo $volver; ?>'></a><br><?php echo $volver; ?>
                            </td>
                        </tr>
                    </table>
                </form>
<?php
            }
        }
        if (($editar=="Incid 1")||($editar=="Incid 2")||($editar=="Incid 3")||($editar=="Incid 4")){
            if ($editar=="Incid 1"){
                $id_contacto = $id_inc1;
                $nombre = "id_inc1";
                $editxt = "$contacto 1 $continc";
            }
            if ($editar=="Incid 2"){
                $id_contacto = $id_inc2;
                $nombre = "id_inc2";
                $editxt = "$contacto 2 $continc";
            }
            if ($editar=="Incid 3"){
                $id_contacto = $id_inc3;
                $nombre = "id_inc3";
                $editxt = "$contacto 3 $continc";
            }
            if ($editar=="Incid 4"){
                $id_contacto = $id_inc4;
                $nombre = "id_inc4";
                $editxt = "$contacto 4 $continc";
            }
?>
            <h1><?php echo $modificar; ?> el <?php echo $editxt; ?> de la Flota <?php echo $flota; ?> (<?php echo $acronimo; ?>)</h1>
<?php
            // Datos de contactos
            $sql_contacto = "SELECT * FROM contactos WHERE ID=$id_contacto";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto != 0) {
                $row_contacto = mysql_fetch_array($res_contacto);
?>
                <form name="editarcont" action="update_contacto.php" method="POST">
                    <input type="hidden" name="<?php echo $nombre; ?>" value="<?php echo $id_contacto; ?>">
                    <input type="hidden" name="flota" value="<?php echo $flota; ?>">
                    <input type="hidden" name="acronimo" value="<?php echo $acronimo; ?>">
                    <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                    <input type='hidden' name='editar' value="<?php echo $editar; ?>">
                    <table>
                        <tr>
                            <th class="t5c"><?php echo $nomtxt; ?></th>
                            <td class="t40p"><input name="nombre" type="text" value="<?php echo $row_contacto["NOMBRE"]; ?>" size="50"></td>
                            <th class="t5c">NIF</th>
                            <td class="t5c"><input name="nif" type="text" value="<?php echo $row_contacto["NIF"]; ?>" size="10"></td>
                        </tr>
                        <tr class="filapar">
                            <th class="t5c"><?php echo $cargo; ?></th>
                            <td class="t40p"><input name="cargo" type="text" value="<?php echo $row_contacto["CARGO"]; ?>" size="50"></td>
                            <th class="t5c">ID</th>
                            <td class="t5c"><?php echo $row_contacto["ID"]; ?></td>
                        </tr>
                        <tr>
                            <th class="t5c"><?php echo $telefono; ?></th>
                            <td class="t40p"><input name="telefono" type="text" value="<?php echo $row_contacto["TELEFONO"]; ?>" size="10"></td>
                            <th class="t5c"><?php echo $telefono; ?> GVA</th>
                            <td class="t5c"><input name="tlf_gva" type="text" value="<?php echo $row_contacto["TLFGVA"]; ?>" size="10"></td>
                        </tr>
                        <tr class="filapar">
                            <th class="t5c"><?php echo $movil; ?></th>
                            <td class="t40p"><input name="movil" type="text" value="<?php echo $row_contacto["MOVIL"]; ?>" size="10"></td>
                            <th class="t5c"><?php echo $movil; ?> GVA</th>
                            <td class="t40p"><input name="movilgva" type="text" value="<?php echo $row_contacto["MOVILGVA"]; ?>" size="10"></td>
                        </tr>
                        <tr>
                            <th class="t5c"><?php echo $mail; ?></th>
                            <td><input name="mail" type="text" value="<?php echo $row_contacto["MAIL"]; ?>" size="50"></td>
                            <th class="t5c"><?php echo $horario;?></th>
                            <td class="t5c"><input name="horario" type="text" value="<?php echo $row_contacto["HORARIO"]; ?>" size="10"></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td class="borde">
                                <input type='image' name='editimg' value="<?php echo $editar; ?>" src='imagenes/guardar.png' alt='<?php echo $guardar; ?>' title='<?php echo $guardar; ?>'><br><?php echo $guardar; ?>
                            </td>
                            <td class="borde">
                                <a href='#' onclick='document.editarcont.reset();'><img src='imagenes/no.png' alt='<?php echo $cancel; ?>' title='<?php echo $cancel; ?>'></a><br><?php echo $cancel; ?>
                            </td>
                            <td class="borde">
                                <a href='#' onclick='document.contincid.submit();'><img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title='<?php echo $volver; ?>'></a><br><?php echo $volver; ?>
                            </td>
                        </tr>
                    </table>
                </form>
<?php
            }
        }
        if (($nuevo=="Incid 1")||($nuevo=="Incid 2")||($nuevo=="Incid 3")||($nuevo=="Incid 4")){
            if ($nuevo == "Incid 1") {
                $newtxt = "$contacto 1 $continc";
            }
            if ($nuevo == "Incid 2") {
                $newtxt = "$contacto 2 $continc";
            }
            if ($nuevo == "Incid 3") {
                $newtxt = "$contacto 3 $continc";
            }
            if ($nuevo == "Incid 4") {
                $newtxt = "$contacto 4 $continc";
            }
?>
            <h1><?php echo $new . " " . $newtxt; ?> de la Flota <?php echo $flota; ?> (<?php echo $acronimo; ?>)</h1>
            <form name="nuevocont" action="update_contacto.php" method="POST">
                <input type="hidden" name="flota" value="<?php echo $flota; ?>">
                <input type="hidden" name="acronimo" value="<?php echo $acronimo; ?>">
                <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                <input type="hidden" name='nuevo' value="">
                <input type="hidden" name='nuevoexist' value="">
<?php
            $sql_contactos = "SELECT * FROM contactos ORDER BY NOMBRE ASC";
            $res_contactos = mysql_query($sql_contactos) or die(mysql_error());
            $ncontactos = mysql_num_rows($res_contactos);
            if ($ncontactos > 0) {
?>
                <h3><?php echo $h3exist; ?></h3>
                <table>
                    <tr>
                        <td class="borde">
                            <select name="contactoexist">
<?php
                        for ($j = 0; $j < $ncontactos; $j++) {
                            $row_contacto = mysql_fetch_array($res_contactos);
                            $idc = $row_contacto["ID"];
                            $nombrec = $row_contacto["NOMBRE"];
?>
                            <option value="<?php echo $idc; ?>"><?php echo $nombrec; ?></option>
<?php
                        }
?>
                            </select><br><input type='image' name='imgexist' value="<?php echo $nuevo; ?>" src='imagenes/adelante.png' alt='Continuar' title='Continuar' onclick="this.form.nuevoexist.value=this.value">
                        </td>
                    </tr>
                </table>
<?php
            }
?>
            <h3><?php echo $h3new; ?></h3>
            <table>
                <tr>
                    <th class="t5c"><?php echo $nomtxt; ?></th>
                    <td class="t40p"><input name="nombre" type="text" value="" size="50"></td>
                    <th class="t5c">NIF</th>
                    <td class="t5c"><input name="nif" type="text" value="" size="10"></td>
                </tr>
                <tr class="filapar">
                    <th class="t5c"><?php echo $cargo; ?></th>
                    <td class="t40p"><input name="cargo" type="text" value="" size="50"></td>
                    <th class="t5c">ID</th>
                    <td class="t5c">&nbsp;</td>
                </tr>
                <tr>
                    <th class="t5c"><?php echo $telefono; ?></th>
                    <td class="t40p"><input name="telefono" type="text" value="" size="10"></td>
                    <th class="t5c"><?php echo $telefono; ?> GVA</th>
                    <td class="t5c"><input name="tlf_gva" type="text" value="" size="10"></td>
                </tr>
                <tr class="filapar">
                    <th class="t5c"><?php echo $movil; ?></th>
                    <td class="t40p"><input name="movil" type="text" value="" size="10"></td>
                    <th class="t5c"><?php echo $movil; ?> GVA</th>
                    <td class="t5c"><input name="movilgva" type="text" value="" size="10"></td>
                </tr>
                <tr>
                    <th class="t5c"><?php echo $mail; ?></th>
                    <td class="t40p"><input name="mail" type="text" value="" size="50"></td>
                    <th class="t5c"><?php echo $horario;?></th>
                    <td class="t5c"><input name="horario" type="text" value="" size="10"></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td class="borde">
                        <input type='image' name='imgnew' value="<?php echo $nuevo; ?>" src='imagenes/guardar.png' alt='<?php echo $guardar; ?>' title='<?php echo $guardar; ?>' onclick="this.form.nuevo.value=this.value"><br><?php echo $guardar; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick='document.nuevocont.reset();'><img src='imagenes/no.png' alt='<?php echo $cancel; ?>' title='<?php echo $cancel; ?>'></a><br><?php echo $cancel; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick='document.contincid.submit();'><img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title='<?php echo $volver; ?>'></a><br><?php echo $volver; ?>
                    </td>
                </tr>
            </table>
            </form>

<?php
        }
        if (($borrar=="Incid 1")||($borrar=="Incid 2")||($borrar=="Incid 3")||($borrar=="Incid 4")){
            if ($borrar == "Incid 1") {
                $id_contacto = $id_inc1;
                $nombre = "id_inc1";
                $deltxt = "$contacto 1 $continc";
            }
            if ($borrar == "Incid 2") {
                $id_contacto = $id_inc2;
                $nombre = "id_inc2";
                $deltxt = "$contacto 2 $continc";
            }
            if ($borrar == "Incid 3") {
                $id_contacto = $id_inc3;
                $nombre = "id_inc3";
                $deltxt = "$contacto 3 $continc";
            }
            if ($borrar == "Incid 4") {
                $id_contacto = $id_inc4;
                $nombre = "id_inc4";
                $deltxt = "$contacto 4 $continc";
            }
?>
            <h1><?php echo $borrtxt . " " . $deltxt; ?> de la Flota <?php echo $flota; ?> (<?php echo $acronimo; ?>)</h1>
            <form name="bajacont" action="update_contacto.php" method="POST">
                <div class="centro">
                    <h2><?php echo $h2borr; ?></h2>
                    <input type="hidden" name="<?php echo $nombre; ?>" value="<?php echo $id_contacto; ?>">
                    <input type="hidden" name="flota" value="<?php echo $flota; ?>">
                    <input type="hidden" name="acronimo" value="<?php echo $acronimo; ?>">
                    <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                    <input type="hidden" name='borrar' value="<?php echo $borrar; ?>">
                    <p><img src='imagenes/important.png' alt='Atencion'></p>
                    <p><span class="error"><?php echo $aviso; ?></span></p>
                    <table>
                        <tr>
                            <td class="borde">
                                <input type='image' name='imgdel' value="<?php echo $borrar; ?>" src='imagenes/ok.png' alt='<?php echo $borrtxt; ?>' title="<?php echo $borrtxt; ?>"><br><?php echo $borrtxt; ?>
                            </td>
                            <td class="borde">
                                <a href='#' onclick='document.contincid.submit();'><img src='imagenes/no.png' alt='Cancelar' title="Cancelar"></a><br>Cancelar
                            </td>
                        </tr>
                    </table>
                </div>
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