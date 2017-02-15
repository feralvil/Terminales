<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/incidencias_$idioma.php";
include ($lang);
// -------------------------------------------------------------------------- //

// ------------ Conexión a BBDD de Terminales ------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusuread, $dbpasoread);
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
// -------------------------------------------------------------------------- //

// ----------  Obtenemos el listado de flotas ------------------------------  //
$sql_flotas = "SELECT ID, flotas.FLOTA, ACRONIMO, ENCRIPTACION FROM flotas WHERE 1";
$sql_select = $sql_flotas . " ORDER BY flotas.FLOTA ASC";
$res_select = mysql_query($sql_select) or die(mysql_error());
$nselect = mysql_num_rows($res_select);
// -------------------------------------------------------------------------- //
?>
<html>
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript">
             // Funciones JQuery
            $(function(){
                // Empezamos ocultando los botones y los campos variables:
                $("#botones").hide();
                $("#extraform").hide()
                // Seleccionamos una incidencia y mostramos los botones:
                $("#selinc").change(function(){
                    var selinc = $("#selinc").val();
                    if (selinc != "NN"){
                        $("#botones").show();
                        $("#extracob").hide();
                        $("#extrarep").hide();
                        $("#extraotra").hide();
                        $("#inptipo").val(selinc);
                        if (selinc == "cobertura"){
                            $("#extraform").show();
                            $("#extracob").show();
                        }
                        if (selinc == "repara"){
                            $("#extraform").show();
                            $("#extrarep").show();
                        }
                        if (selinc == "otra"){
                            $("#extraform").show();
                            $("#extraotra").show();
                            $("#textotra").click(function(){
                                $(this).html("");
                            });
                        }
                    }
                    else {
                        $("#botones").hide();
                        $("#extraform").hide();
                    }
                });
                
                // Si pulsamos el botón enviar, comprobamos los campos obligatorios y enviamos el formulario
                $("#enviar").click(function(){
                    // Variable para comprobar los campos:
                    var completo = true;
                    // Variable con el mensaje de error:
                    var error = "Todo OK";
                    // Comprobamos la Flota:
                    if (($("#selflota").val() == "NN") || ($("#selflota").val() == "")){
                        completo = false;
                        error = "<?php echo $errflota;?>";
                    }
                    // Comprobamos el nombre:
                    else if ($("#inpnom").val() == ""){
                        completo = false;
                        error = "<?php echo $errnom;?>";
                    }
                    // Comprobamos el teléfono:
                    else if ($("#inptelef").val() == ""){
                        completo = false;
                        error = "<?php echo $errtelef;?>";
                    }                    
                    // Comprobamos el mail:
                    else if ($("#inpmail").val() == ""){
                        completo = false;
                        error = "<?php echo $errmail;?>";
                    }
                    // Comprobamos el ISSI:
                    else if ($("#inpissi").val() == ""){
                        completo = false;
                        error = "<?php echo $errissi;?>";
                    }
                    // Comprobamos el TEI:
                    else if ($("#inptei").val() == ""){
                        completo = false;
                        error = "<?php echo $errtei;?>";
                    }
                    // Comprobamos la Marca:
                    else if ($("#inpmarca").val() == ""){
                        completo = false;
                        error = "<?php echo $errmarca;?>";
                    }
                    // Comprobamos el Modelo:
                    else if ($("#inpmodelo").val() == ""){
                        completo = false;
                        error = "<?php echo $errmodelo;?>";
                    }
                    // Comprobamos la Incidencia:
                    else if (($("#tipoinc").val() == "NN") || ($("#tipoinc").val() == "")){
                        completo = false;
                        error = "<?php echo $errinc;?>";
                    }

                    if (completo){
                        $("#frominc").submit();
                    }
                    else{
                        alert(error);
                    }
                });
            });
        </script>
    </head>
    <body>
    	<h1><?php echo $h1; ?></h1>
        <form id="frominc" method="POST" action="mailincidencia.php" enctype="multipart/form-data">
            <input type="hidden" name="tipoinc" id="inptipo" value="NN">
            <h2><?php echo $h2datos.' (*: '.$h2campos.')';?></h2>
            <table>
                <tr>
                    <td colspan="2">
                        <b>Flota(*):</b> &nbsp;
                        <select name="flota" id="selflota">                       
                            <option value="NN" <?php if (($flota == "NN") || ($flota == "")) echo ' selected'; ?>>
                                <?php echo $opselflota; ?>
                            </option>
                            <?php
                            for ($i=0; $i < $nselect; $i++) { 
                                $row_flota = mysql_fetch_array($res_select);
                            ?>
                                <option value="<?php echo $row_flota["ID"];?>"  <?php if ($flota == $row_flota["ID"]){echo ' selected';} ?>> 
                                    <?php echo $row_flota["FLOTA"];?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <b><?php echo $inpnom; ?>(*):</b> &nbsp;
                        <input type="text" name="nombre" id="inpnom" size="60">
                    </td>
                </tr>
                <tr>
                    <td>
                        <b><?php echo $inptelef; ?>(*):</b> &nbsp;
                        <input type="text" name="telef" id="inptelef" size="10">
                    </td>
                    <td>
                        <b><?php echo $inpmail; ?>(*):</b> &nbsp;
                        <input type="text" name="mail" id="inpmail" size="30">
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>ISSI(*): &nbsp;</b>
                        <input type="text" name="issi" id="inpissi" size="10">
                    </td>
                    <td>
                        <b>TEI(*): &nbsp;</b>
                        <input type="text" name="tei" id="inptei" size="30">
                    </td>
                </tr>
                <tr>
                    <td>
                        <b><?php echo $inpmarca; ?>(*):</b> &nbsp;
                        <input type="text" name="marcaform" id="inpmarca" size="30">
                    </td>
                    <td>
                        <b><?php echo $inpmodelo; ?>(*):</b> &nbsp;
                        <input type="text" name="modeloform" id="inpmodelo" size="30">
                    </td>
                </tr>
            </table>
            <h2><?php echo $h2inc; ?>(*)</h2>
            <fieldset>
                <select name="tipoinc" id="selinc">
                    <option value="NN"><?php echo $opselinc; ?></option>
                    <option value="registro"><?php echo $opselreg; ?></option>
                    <option value="cobertura"><?php echo $opselcob; ?></option>
                    <option value="calidad"><?php echo $opselcal; ?></option>                    
                    <option value="repara"><?php echo $opselrep; ?></option>
                    <option value="otra"><?php echo $opselotra; ?></option>
                </select>
            </fieldset>
            <!-- Div con campos del formulario que se muestran/ocultan según el tipo de incidencia -->
            <div id="extraform">
                <!-- Div con campos del formulario si Incidencia = 'cobertura' -->
                <div id="extracob">
                    <h3><?php echo $h3tipot; ?></h3>
                    <table>
                        <tr>
                            <td>
                                <b>Marca: &nbsp;</b>
                                <input type="text" name="marca" size="20">
                            </td>
                            <td>
                                <b><?php echo $inpmodelo; ?>: &nbsp;</b>
                                <input type="text" name="modelo" size="20">
                            </td>
                        </tr>
                    </table>
                    <h3><?php echo $h3ubica; ?></h3>
                    <table>
                        <tr>
                            <td>
                                <b><?php echo $inpmuni; ?>: &nbsp;</b>
                                <input type="text" name="muni" size="80">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b><?php echo $inpgps; ?>: &nbsp;</b>
                                <input type="text" name="gps" size="80">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b><?php echo $inpdom; ?>: &nbsp;</b>
                                <input type="text" name="domicilio" size="80">
                            </td>
                        </tr>   
                    </table>
                </div>
                <!-- Div con campos del formulario si Incidencia = 'repara' -->
                <div id="extrarep">
                    <h3><?php echo $h3repara;?></h3>
                    <input type="radio" name="radrep" id="radeste" value="este" checked> &nbsp; <?php echo $indradeste;?> <br />
                    <input type="radio" name="radrep" id="radrel" value="relacion"> &nbsp; <?php echo $indradrel;?>
                    <div id="reprad">
                        <input type="file" name="repexcel" id="repexcel">
                    </div>
                </div>
                <!-- Div con campos del formulario si Incidencia = 'repara' -->
                <div id="extraotra">
                    <h3><?php echo $h3otra;?></h3>
                    <textarea name = "incotra" id="textotra" rows="6" cols="80"><?php echo $textotra;?></textarea>
                </div>
            </div>
            <!-- Div con botones Submit/Reset. No se muestra mientras no se selecciona un tipo de incidencia -->
            <div id="botones">
                <table>
                    <tr>
                        <td class="borde">
                            <button type="button" name="enviar" id="enviar"><img src="imagenes/mail.png" alt="<?php echo $botsubmit; ?>" title="<?php echo $botsubmit; ?>" /></button>
                            <br><?php echo $botsubmit; ?>
                        </td>
                        <td class="borde">
                            <button type="reset" name="cancelar" id="cancelar"><img src="imagenes/cancelar.png" alt="<?php echo $botcancel; ?>" title="<?php echo $botsubmit; ?>" /></button>
                            <br><?php echo $botcancel; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </body>
</html>
