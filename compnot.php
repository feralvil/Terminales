<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/compmens_$idioma.php";
include ($lang);
// Fijamos el editor de Joomla
switch ($idioma) {
    case "es": {
            $tinymce_lang = "es";
            break;
        }
    case "va": {
            $tinymce_lang = "ca";
            break;
        }
}

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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $titnot; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <script type="text/javascript" src="../plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript">
            tinyMCE.init({
                // General options
                mode : "textareas",
                theme : "advanced",
                language: "<?php echo $tinymce_lang ;?>",
                plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",
                
                // Theme options
               theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
               theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,forecolor,backcolor",
               theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,advhr,|,preview,print",
               theme_advanced_toolbar_location : "top",
               theme_advanced_toolbar_align : "left",
               theme_advanced_statusbar_location : "bottom",
               theme_advanced_resizing : true,
               
               // Example content CSS (should be your site CSS)
               content_css : "../templates/joomla_dgm/css/template.css",
               
               // Drop lists for link/image/media/template dialogs
               template_external_list_url : "js/template_list.js",
               external_link_list_url : "js/link_list.js",
               external_image_list_url : "js/image_list.js",
               media_external_list_url : "js/media_list.js",
               
               // Replace values for the template plugin
              template_replace_values : {
                  username : "Some User",
                  staffid : "991234"
              }
            });
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
    if ($permiso==2){
        $titmail = $cuerpo = "";
        $sql_mens = "SELECT * FROM mensajes WHERE TIPO = 'N' ORDER BY FMODIFICA DESC";
        $res_mens = mysql_query($sql_mens) or die(mysql_error());
        $nmens = mysql_num_rows($res_mens);
        if (isset ($update)){
            if ($update == "KO"){
?>
                <p class="error">
                    <img src="imagenes/cancelar.png" alt="Error" title="Error"> &mdash; <?php echo $mensflash;?>
                </p>
<?php
            }
        }
?>
        <h1>
            <?php echo $h1not; ?>&mdash; <a href="compnot.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <h2><?php echo $notexist; ?></h2>
        <form name="mailexist" action="compnot.php" method="POST">
            <p>
                <?php echo $labelexist; ?>: &nbsp;
                <select name="idmexist" onchange="document.mailexist.submit();">
                    <option value="NN"><?php echo $mailexist; ?></option>
<?php 
                    for ($i = 0; $i < $nmens; $i++){
                        $row_mens = mysql_fetch_array($res_mens);                        
                        $idmens = $row_mens["ID"];
                        $asmens = $row_mens["ASUNTO"];
                        if (strlen($asmens) > 50){
                            $asmens = substr($asmens, 0, 50)."...";
                        }
                        $fmmens = $row_mens["FMODIFICA"];
                        $fmmens = substr($fmmens, 0, 10);
                        $txtmens = "$asmens ($fmmens)";
?>
                        <option value="<?php echo $idmens; ?>" <?php if ($idmexist == $idmens) echo "selected"; ?>><?php echo $txtmens; ?></option>
<?php
                    }
?>                    
                </select><br />
                <input type="checkbox" name="conservar" value="ON" onchange="document.mailexist.submit();" <?php if ($conservar == "ON") echo "checked"; ?>> <?php echo $notcheck; ?>
            </p>
        </form>
<?php
        $origen = "new";
        $idm = 0;
        if (($idmexist!="")&&($idmexist!="NN")){
            if ($conservar != "ON"){
                $origen = "editar";
            }
            $idm = $idmexist;
            $sql_mail = "SELECT * FROM mensajes WHERE ID = '$idm'";
            $res_mail = mysql_query($sql_mail) or die(mysql_error());
            $nmail = mysql_num_rows($res_mail);
            if ($nmail > 0){
                 $row_mail = mysql_fetch_array($res_mail);
                 $asunto = $row_mail["ASUNTO"];
                 $cuerpo = $row_mail["MENSAJE"];
            }
        }
?>
        <h2><?php echo $asuntonot; ?></h2>
        <form name="mail" action="update_mens.php" method="POST">
            <input type="hidden" name="origen" value="<?php echo $origen; ?>">
            <input type="hidden" name="idm" value="<?php echo $idm; ?>">
            <input type="hidden" name="tipo" value="N">
            <input type="text" name="asunto" size="100" value="<?php echo $asunto; ?>">
        <h2><?php echo $compnot; ?></h2>
            <textarea name="mensaje" rows="15" cols="80"><?php echo $cuerpo; ?></textarea><br />
            <table>
                <tr>
                    <td class="borde">
                        <input type="image" src='imagenes/contactos.png' alt='<?php echo $enviar;?>' title='<?php echo $enviar;?>'><br><?php echo $enviar;?>
                    </td>
                    <td class="borde">
                        <a href='flotas.php'><img src='imagenes/atras.png' alt='<?php echo $volver;?>' title='<?php echo $volver;?>'></a><br><?php echo $volver;?>
                    </td>
                </tr>
            </table>
        </form>
<?php
    }
    else{
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
