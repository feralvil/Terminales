<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/leerflotas_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
} else {
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
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu == 0) {
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
        if ($permiso > 1){
            if (isset ($update)){
                if ($update == "KO"){
                    $clase = "flashko";
                    $imagen = "imagenes/cancelar.png";
                    $alt = "Error";
                }
                if ($update == "OK"){
                    $clase = "flashok";
                    $imagen = "imagenes/okm.png";
                    $alt = "OK";
                }
        ?>
                <p class="<?php echo $clase;?>">
                <img src="<?php echo $imagen;?>" alt="<?php echo $alt;?>" title="<?php echo $alt;?>"> &mdash; <?php echo $mensflash;?>
                </p>
        <?php
            }
        ?>
            <h1><?php echo $h1; ?></h1>
            <?php
            // Intentamos leer el fichero:
            $ficherocdd = fopen("flotas/flotas_cdd.txt", "r");
            $bloques = array();
            while (($linea = fgets($ficherocdd)) !== false){
                if (substr($linea, 0, 2) == "00") {
                    $indice = 0;
                    $elementos = explode(' ', $linea);
                    $limpios = array();
                    foreach ($elementos as $elemento){
                        if ($elemento != ""){
                            if ($indice < 2){
                                $limpios[$indice] = $elemento;
                                $indice++;
                            }
                            else{
                                $limpios[$indice] .= utf8_encode($elemento). " ";
                            }
                        }
                    }
                    $limpios[2] = trim($limpios[2]);
                    $bloques[] = $limpios;
                }
            }
            ?>
            <h2><?php echo $h2bloques . " &mdash; " . count($bloques);?></h2>
            <?php
            $longant = 0;
            $ibloque = 0;
            foreach ($bloques as $bloque) {
                $longbloques = explode('-', $bloque[0]);
                $nlongbloques = count($longbloques);
                $item = "<li>" . $bloque[0] . " &mdash; " . $bloque[2] . "</li>" ;
                if ($nlongbloques <> $longant){
                    if ($nlongbloques > $longant){
                        $item = "\n" . "<ul>" . $item;
                    }
                    else{
                        if ($ibloque > 0){
                            $diflong = $longant - $nlongbloques;
                            $prefijo = "";
                            // Cerramos los bloques anteriores:
                            for ($i = 0; $i < $diflong; $i++){
                                $prefijo .= "\n" . "</ul>";
                            }
                            $item = $prefijo . "\n" . $item;
                        }
                    }
                    $longant = $nlongbloques;
                }
                echo $item;
                $ibloque++;
            }
            ?>
            </ul>
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
