<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/importarorg_$idioma.php";
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
<html lang="es">
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
    </head>
    <body>
        <?php
        if ($permiso == 2){
            $sql_flotas = "SELECT ID, FLOTA FROM flotas ORDER BY FLOTA ASC";
            $res_flotas = mysql_query($sql_flotas) or die("Error en la Consulta de Flotas: " . mysql_error());
            $nflotas = mysql_num_rows($res_flotas);
        ?>
            <h1>
                <?php echo $h1; ?> &mdash; 
                <a href="importar_org.php" target="_blank">
                        <img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>">
                </a>
            </h1>
            <h2><?php echo $h2; ?></h2>
            <?php
            if ($nflotas > 0){
            ?>
                <form name="formimport" action="update_org.php" method="post">
                    <input type="hidden" name="origen" value="impflotas">
                <table>
                    <tr>
                        <td class="borde"><?php echo $txtnflotas;?>:<?php echo $nflotas;?></td>                        
                        <td class="borde">
                            <input type="image" src="imagenes/importar.png" alt="<?php echo $botupdate;?>" title="<?php echo $botupdate;?>">
                            &mdash; <?php echo $botupdate;?>
                        </td>
                    </tr>
                </table>
                </form>
                <table>
                    <tr>
                        <th>ID Flota</th>
                        <th>Flota</th>
                        <th><?php echo $thorg;?></th>
                        <th>ID <?php echo $thorg;?></th>
                    </tr>
                    <?php
                    for ($i = 0; $i < $nflotas ; $i++){
                        $flota = mysql_fetch_array($res_flotas);
                        $sql_org = "SELECT ID, ORGANIZACION FROM organizaciones WHERE (FLOTA_ID = '" . $flota['ID'] . "')";
                        $res_org = mysql_query($sql_org) or die("Error en la Consulta de Organizaciones: " . mysql_error());
                        $norg = mysql_num_rows($res_org);
                        $idorg = "-";
                        $nomorg = "--";
                        if ($norg > 0){
                            if ($norg == 1){
                                $org = mysql_fetch_array($res_org);
                                $idorg = $org['ID'];
                                $nomorg = $org['ORGANIZACION'];
                            }
                            else{
                                $nomorg = $norg;
                            }
                        }                
                    ?>
                        <tr <?php if (($i % 2) == 1) {echo 'class = "filapar"';} ?>>
                            <td><?php echo $flota['ID']; ?></td>
                            <td><?php echo $flota['FLOTA']; ?></td>
                            <td><?php echo $nomorg; ?></td>
                            <td><?php echo $idorg; ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
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
</html>
