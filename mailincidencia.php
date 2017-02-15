<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/incidencias_$idioma.php";
include ($lang);

// Clase PHPMailer para enviar mail:
require_once 'PHPMailer/PHPMailerAutoload.php';

// Obtenemos los parámetros de Joomla
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
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
// ------------------------------------------------------------------------------------- //
import_request_variables("p", "");

// Listado de flotas
$sql_flota = "SELECT * FROM flotas WHERE ID = '" . $flota ."'";
$res_flota = mysql_query($sql_flota) or die(mysql_error());
$nflota = mysql_num_rows($res_flota);
if ($nflota > 0){
	$row_flota = mysql_fetch_array($res_flota);
	$flotatext = $row_flota["FLOTA"];
}
else{
	$flotatext = $errnoflota;
}

$htmlhead = '
<!DOCTYPE html>
<html>
<head>';
$htmlhead .= '<title>'.$titulo.'</title>';
$htmlhead .= '<link rel="StyleSheet" type="text/css" href="estilo.css">';
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
  }
  ';
$htmlhead .= '</style>';
$htmlhead .= '</head>';
$htmlbody .= '<body>';
$htmlmail = "<h1>".$h1mail."</h1>";
$htmlmail .= "<h2>".$h2datos."</h2>";
$htmlmail .= "<table><tr><th>Flota</th>";
$htmlmail .= '<td colspan="3">'.$flotatext.'</td>';
$htmlmail .= '</tr><tr>';
$htmlmail .= '<th>'.$inpnom.'</th>';
$htmlmail .= '<td colspan="3">'.$nombre.'</td>';
$htmlmail .= '</tr><tr>';
$htmlmail .= '<th>'.$inptelef.'</th>';
$htmlmail .= '<td>'.$telef.'</td>';
$htmlmail .= '<th>'.$inpmail.'</th>';
$htmlmail .= '<td>'.$mail.'</td>';
$htmlmail .= '</tr><tr>';
$htmlmail .= '<th>'.$inpmarca.'</th>';
$htmlmail .= '<td>'.$marcaform.'</td>';
$htmlmail .= '<th>'.$inpmodelo.'</th>';
$htmlmail .= '<td>'.$modeloform.'</td>';
$htmlmail .= '</tr><tr>';
$htmlmail .= '<th>ISSI</th>';
$htmlmail .= '<td>'.$issi.'</td>';
$htmlmail .= '<th>TEI</th>';
$htmlmail .= '<td>'.$tei.'</td>';
$htmlmail .= '</tr></table>';
$htmlmail .= '<h2>'.$h2inc.'</h2>';
$htmlmail .= '<h3>'.$h3tipoinc.'</h3>';
$tipofich = "Nada";
switch ($tipoinc) {
	case 'registro':
		$htmlmail .= "<p>".$opselreg."</p>";
		break;

	case 'cobertura':
		$htmlmail .= "<p>".$opselcob."</p>";
		break;

	case 'calidad':
		$htmlmail .= "<p>".$opselcal."</p>";
		break;

	case 'repara':
		$htmlmail .= "<p>".$opselrep."</p>";
		break;

	case 'otra':
		$htmlmail .= "<p>".$opselotra."</p>";
		break;
			
	default:
		$htmlmail .= "<p>".$errnoinc."</p>";
		break;
}
if ($tipoinc == "cobertura"){
	$htmlmail .= '<h4>'.$h3tipot.'</h4>';
	$htmlmail .= '<table><tr><th>Marca</th>';
	$htmlmail .= '<td>'.$marca.'</td>';
	$htmlmail .= '<th>'.$inpmodelo.'</th>';
	$htmlmail .= '<td>'.$modelo.'</td>.</tr>';
	$htmlmail .= '</table>';
	$htmlmail .= '<h4>'.$h3ubica.'</h4>';
	$htmlmail .= '<table><tr>';
	$htmlmail .= '<th>'.$inpmuni.'</th>';
	$htmlmail .= '<td>'.$muni.'</td>';
	$htmlmail .= '</tr><tr>';
	$htmlmail .= '<th>'.$inpgps.'</th>';
	$htmlmail .= '<td>'.$gps.'</td>';
	$htmlmail .= '</tr><tr>';
	$htmlmail .= '<th>'.$inpdom.'</th>';
	$htmlmail .= '<td>'.$domicilio.'</td>';
	$htmlmail .= '</tr></table>';
}
elseif ($tipoinc == "repara") {
	$htmlmail .= '<h4>'.$h3tipot.'</h4>';
	$htmlmail .= '<ul>';
	if ($radrep == "relacion"){
		$errarch = false;
		if ($_FILES["repexcel"]["error"] > 0){
			$errarch = true;
			$mensaje = $errnoarch;
		}
		elseif ($_FILES["repexcel"]["size"] > 5000000) {
			$errarch = true;
			$mensaje = $errtamarch;	
		}
		elseif ((strpos($_FILES["repexcel"]["type"], 'excel') == FALSE) && ((strpos($_FILES["repexcel"]["type"], 'spreadsheetml') == FALSE))) {
			$errarch = true;
			$mensaje = $errtipoarch . ' - ' . $_FILES["repexcel"]["type"];	
		}
		else{										
			// Construimos el nombre del fichero
			$fecha = date("Y-m-d");
			$numfich = 1;
			$nomfich = "incidencias/" . $_FILES["repexcel"]["name"];
			if (move_uploaded_file($_FILES["repexcel"]["tmp_name"], $nomfich)){
				$mensaje = $mensarch . " " . $_FILES["repexcel"]["name"];
			}
			else{						
				$errarch = true;
				$mensaje = $errmvarch;
			}			
		}
		$htmlmail .= '<li>'.$indradrel.'</li>';
		$htmlmail .= '<li>'.$mensaje.'</li>';
	}
	else{
		$htmlmail .= '<li>'.$indradeste.'</li>';
	}	
	$htmlmail .= '</ul>';
}
elseif ($tipoinc == "otra") {
	$htmlmail .= '<h4>'.$h4otra.'</h4>';
	$htmlmail .= '<p>'.$incotra.'</p>';
}
$htmlbody2 = '</body>';
$htmlpie = '</html>';

$htmlmens = $htmlhead.$htmlbody.$htmlmail.$htmlbody2.$htmlpie; 

// Componemos el mail con PHPMailer
$mail = new PHPMailer;

$mail->CharSet = 'UTF-8';						// Fijamos la codificación de caracteres
$mail->isSMTP();                                // Usamos SMTP para el envío
$mail->Host = 'smtp.gva.es';  					// Servidor SMTP GVA
$mail->SMTPAuth = false;

$mail->From = 'info_comdes@gva.es';				// Establecemos el Remitente
$mail->FromName = 'Incidencias COMDES';
$mail->addAddress('incidencias_comdes@gva.es');		// Establecemos el Destinatario
//$mail->addAddress('alfonso_fer@gva.es');		// Establecemos el Destinatario

$mail->WordWrap = 50;							// Set word wrap to 80 characters
if (($tipoinc == 'repara') && ($radrep == "relacion") && (!$errarch)){
	$mail->addAttachment($nomfich);    			//  Adjuntamos el ficheros:
}
$mail->isHTML(true);							// Formato del Mail HTML

// Componemos el correo
$mail->Subject = $titulo;
$mail->Body    = $htmlmens;
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

// Enviamos el mensaje
$res_mail = $mail->send();
?>
<html>
	<head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body>
    	<h1><?php echo $h1mail; ?></h1>
    	<?php
    	if ($res_mail){
    	?>
    		<h2><?php echo $h2mailok.' ';?> incidencias_comdes@gva.es</h2>
    		<img src="imagenes/clean.png" alt="<?php echo $botok; ?>" title="<?php echo $botok; ?>" />
    	<?php
    		echo $htmlmail;
    	}
    	else{
    	?>    		
    		<h2><?php echo $h2mailerr.' ';?> incidencias_comdes@gva.es</h2>
    		<p>Error: <?php echo $mail->ErrorInfo;?></p>
    		<img src="imagenes/error.png" alt="<?php echo $boterror; ?>" title="<?php echo $boterror; ?>" />
    	<?php
    	}
    	?>
    	<table>
            <tr>
                <td class="borde">
                	<a href="incidencias.php">
                    	<img src="imagenes/atras.png" alt="<?php echo $botatras; ?>" title="<?php echo $botatras; ?>" />
                    </a>
                    <br><?php echo $botatras; ?>
                </td>
            </tr>
        </table>
    </body>
</html>