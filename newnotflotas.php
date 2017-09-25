<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotanot_$idioma.php";
include ($lang);

// Clase PHPMailer para enviar mail:
require_once 'PHPMailer/PHPMailerAutoload.php';

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

// Iniciamos las funciones de correo:
//ini_set("SMTP", "smtp.difusio.gva.es");
//ini_set('sendmail_from', "comdes_informa@difusio.gva.es");
$ndestinatarios = 0;
$destinatarios = "";
$mailnom = array("Oficina COMDES", "Fernando Alfonso", "Manuel Cava", "Santiago Vieco", "Vicente Saurí", "Laura Segura");
$mailadr = array("comdes@gva.es", "alfonso_fer@externos.gva.es", "cava_man@gva.es", "vieco_san@externos.gva.es", "segura_lau@gva.es");
// Obtenemos el mensaje de la BBDD
if (isset ($idm)){
    $sql_mensaje = "SELECT * FROM mensajes WHERE ID='$idm'";
    $res_mens = mysql_query($sql_mensaje) or die($errmens.": ".mysql_error());
    $nmens = mysql_num_rows($res_mens);
    if ($nmens > 0){
        $row_mens = mysql_fetch_array($res_mens);
        $mensbbdd = $row_mens["MENSAJE"];
        $asunto = $row_mens["ASUNTO"];
    }
    $htmlhead = '<!DOCTYPE html><html><head>';
    $htmlhead .= '<title>'.$asunto.'</title>';
    $htmlhead .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    $htmlhead .= '<style>';
    $htmlhead .= 'body {
        color : #000000;
        font-family : Arial, Helvetica, sans-serif;
        font-size : 8pt;
        line-height : 150%;
    }
    h1 {
        border-bottom-color : #00407A;
        border-bottom-style : solid;
        border-bottom-width : 1px;
        color : #00407A;
        font-size : 10pt;
        font-weight : bold;
        line-height : 16px;
        margin-left : 3px;
        margin-top : 0px;
        padding-bottom : 1px;
        padding-left : 1px;
        padding-right : 1px;
        padding-top : 1px;
        text-align : left;
        text-indent : 0px;
        text-transform : uppercase;
        width : 100%;
    }
    h2 {
        font-size : 9pt;
        color : #666666;
    }
    h3 {
        font-size : 8pt;
        color : #00407A;
    }

    h4 {
        font-size : 7pt;
        color : #00407A;
    }

    table {
        width : 100%;
    }
    th {
        background-color : #00407A;
        color : #ffffff;
        font-size : 8pt;
        font-weight : bold;
    }
    td {
        font-size : 8pt;
        color: #00407A;
    }
    hr{
        border: 1px solid red;
    }
    .minifirma{
        font-size : 6pt;
        color: #00407A;
    }
    ';
    $htmlhead .= '</style>';
    $htmlhead .= '</head>';
    $htmlbody .= '<body>';
    $htmlmail = "<h1>".$asunto."</h1>";
    $htmlmail .= $mensbbdd;
    $htmlbody2 = '</body>';
    $htmlpie = '</html>';
    $tabladest = "
        <table>
            <tr>
                <th>Flota</th>
                <th>Nombre</th>
                <th>Mail</th>
            </tr>
    ";
    if (!empty($idflotacont)){
       // Identificador de flota previo
        $idfprev = 0;
        $par = 0;
        // Obtenemos los contactos del formulario (idflota-idcontacto)
        $destmail = array();
        $destnom = array();
        for ($i = 0; $i < count($idflotacont); $i++){
            $idfc = explode("-", $idflotacont[$i]);
            $idflota = $idfc["0"];
            $idc = $idfc["1"];
            if ($idflota != $idfprev){
                $sql_flota = "SELECT FLOTA FROM flotas WHERE ID = '$idflota'";
                $res_flota = mysql_query($sql_flota) or die("Error en la consulta de flota: ". mysql_error());
                $nflota = mysql_num_rows($res_flota);
                if ($nflota > 0) {
                    $row_flota = mysql_fetch_array($res_flota);
                    $flota_nom = $row_flota["FLOTA"];
                }
            }
            $sql_contacto = "SELECT NOMBRE, MAIL FROM contactos WHERE ID = '$idc'";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0) {
                $row_contacto = mysql_fetch_array($res_contacto);
                $emailbd = $row_contacto["MAIL"];
                $emailv = explode(" / ", $emailbd);
                $email = $emailv[0];
                $email = trim($email);
                array_push($destmail, $email);
                $nombre = $row_contacto["NOMBRE"];
                array_push($destnom, $nombre);
                $dest = $nombre." <".$email.">";
                $destinatarios .= $dest.", ";
                $tr = "<tr>";
                if (($par % 2) == 1){
                    $tr = "<tr class='filapar'>";
                }
                $tabladest .= $tr;
                $tabladest .= "
                                    <td>$flota_nom</td>
                                    <td>$nombre</td>
                                    <td>$email</td>
                                </tr>
                            ";
                $par++;
            }
        }
        $tabladest .= "
                        </table>
                    " ;
        // Creamos el mensaje con PHPMailer:
        $mail = new PHPMailer;
        $mail->SMTPDebug = 0;//2;
        $mail->Debugoutput = 'html';
        $mail->CharSet = 'UTF-8';                       // Fijamos la codificación de caracteres
        $mail->isSMTP();                                // Usamos SMTP para el envío
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.difusio.gva.es';            // Servidor SMTP GVA Difusión
        $mail->Username = 'comdes_informa';                 // Establecemos el Remitente
        $mail->Password = "sU4TwTw7Dw";
        $mail->From = 'comdes_informa@difusio.gva.es';  // Establecemos el Remitente
        $mail->FromName = 'COMDES Difussió';        
        // Incrustamos la imagen:
        $mail->AddEmbeddedImage('imagenes/logochmev.jpg', 'logoimg', 'logochmev.jpg');
        $htmlfirma .= '<p>&nbsp;</p>';
        $htmlfirma = '<hr />';
        $htmlfirma .= '<table>';
        $htmlfirma .= '<tr>';
        $htmlfirma .= '<td style="widht: 40%; text-align: center">';
        $htmlfirma .= '<img src="cid:logoimg" />';
        $htmlfirma .= '</td>';
        $htmlfirma .= '<td>';
        $htmlfirma .= '<strong>Oficina COMDES</strong> <br />';
        $htmlfirma .= '<strong>Servei de Telecomunicacions i Societat Digital</strong> <br />';
        $htmlfirma .= '<strong>Direcció General de Tecnologies de la Informació i les Comunicacions</strong> <br />';
        $htmlfirma .= 'C/ Castán Tobeñas, 77 - 46018 - València <br />';
        $htmlfirma .= 'Ciutat Administrativa 9 d\'Octubre - Edifici A <br />';
        $htmlfirma .= 'Tfn: 963985300 - Correu: <a href="mailto:comdes@gva.es">comdes@gva.es</a> <br />';
        $htmlfirma .= 'Twitter: <a href="https://twitter.com/GVAcomdes">@GVAcomdes</a> - ';
        $htmlfirma .= 'Web: <a href="http://www.comdes.gva.es/">www.comdes.gva.es</a>';
        $htmlfirma .= '</td>';
        $htmlfirma .= '</tr>';
        $htmlfirma .= '</table>';
        $htmlfirma .= '<hr />';
        $htmlfirma .= '<p class="minifirma">';
        $htmlfirma .= 'Correu electrònic amb informació confidencial. Si no és el destinatari no està autoritzat a utilitzar-lo,. Si l\'ha rebut per error, per favor destruisca\'l. <br />';
        $htmlfirma .= 'Correo electrónico con información confidencial. Si no es el destinatario no está autorizado a su uso. Si lo ha recibido por error, por favor destrúyalo. <br />';
        $htmlfirma .= 'Confidential information contained in this e-mail. Any use of this mail prohibited other than its intended recipient. If you received this in error, please delete it. <br />';
        $htmlfirma .= '</p>';
        $htmlmens = $htmlhead.$htmlbody.$htmlmail.$htmlbody2.$htmlfirma.$htmlpie;
        $mail->addAddress('comdes_informa@difusio.gva.es', 'Difusión COMDES');
        for ($i=0; $i < count($destmail); $i++) {
            $mail->addBCC($destmail[$i], $destnom[$i]);
        }
        $mail->WordWrap = 50;                           // Set word wrap to 80 characters
        $mail->isHTML(true);                            // Formato del Mail HTML

        // Componemos el correo
        $mail->Subject = $asunto;
        $mail->Body    = $htmlmens;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        // Enviamos el mensaje
        $res_mail = $mail->send();
    }
    else{
        $res_mail = false;
        $mailerror = "$mailerror $errnoinc";
    }
}
else{
    $res_mail = false;
    $mailerror = "$mailerror $errnomens";
}
?>
<html>
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
    if ($res_mail){
?>
        <p class="centro"><img src='imagenes/clean.png' alt='OK' title="OK"></p>
        <p><?php echo $mailenv; ?></p>
        <hr />
        <?php echo $mensbbdd; ?>
        <hr />
        <p><?php echo $maildest; ?></p>
        <?php echo $tabladest; ?>
        <table>
            <tr>
                <td class="borde">
                    <a href="compnot.php">
                        <img src="imagenes/atras.png" title="<?php echo $volver;?>" alt="<?php echo $volver;?>">
                    </a><br>
                    <?php echo $volver;?>
                </td>
            </tr>
        </table>
<?php
    }
    else{
?>
        <p class="centro"><img src='imagenes/error.png' alt='Error' title="Error"></p>
        <p class="centro">
            <span class="error"><?php echo $mailerror; ?></span><br />
            <?php echo $mail->ErrorInfo;?>
        </p>
<?php
    }
?>
    </body>
</html>
