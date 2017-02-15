<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contactosimp_$idioma.php";
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
        <title><?php echo $titulo;?></title>
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
        if ($permiso > 1){
            // Consulta total de flotas
            $sql_flotassel = "SELECT ID, FLOTA FROM FLOTAS ORDER BY FLOTA ASC";
            $res_flotassel = mysql_db_query($base_datos, $sql_flotassel) or die("Error en la consulta total de flotas:" . mysql_error());
            $nflotassel = mysql_num_rows($res_flotassel);
            $sql_flotas = "SELECT * FROM flotas WHERE 1";
            if ((isset($_POST['flotassel'])) && ($_POST['flotassel'] > 0)){
                $sql_flotas .= " AND (flotas.ID = ". $_POST['flotassel'] .")";
            }
            $sql_flotas .= " ORDER BY flotas.FLOTA ASC";
            $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de flotas:" . mysql_error());
            $nflotas = mysql_num_rows($res_flotas);
            $nomcont = array(
                'RESPONSABLE' => 'Responsable de Flota', 'CONTACTO1' => $txtcontacto.' 1', 
                'CONTACTO2' => $txtcontacto.' 2', 'CONTACTO3' => $txtcontacto.' 3',
                'INCID1' => $txtcontacto.' 1 ' .$txtincid, 'INCID2' => $txtcontacto.' 2 ' .$txtincid, 
                'INCID3' => $txtcontacto.' 3 ' .$txtincid, 'INCID4' => $txtcontacto.' 4 ' .$txtincid
            );
        ?>
            <h1><?php echo $h1;?></h1>
            <h2><?php echo $h2crit;?></h2>
            <form name="formFlotas" id="formFlotas" action="importar_contactos.php" method="post">
            <table>
                <tr>
                    <td class="borde">
                        <select name="flotassel" id="selFlota" onchange="formFlotas.submit();">
                            <option value="0">Seleccionar Flota</option>
                            <?php 
                            for ($i = 0; $i < $nflotassel; $i++){
                                $flotasel = mysql_fetch_array($res_flotassel);
                            ?>
                                <option value="<?php echo $flotasel['ID'];?>" <?php if($_POST['flotassel'] == $flotasel['ID']){echo 'selected';} ?>>
                                    <?php echo $flotasel['FLOTA'];?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td class="borde">
                        <select name="contactsel" id="selContacto" onchange="formFlotas.submit();">
                            <option value="NN"><?php echo $txtseltipo;?></option>
                        <?php
                        foreach ($nomcont as $indice => $texto){
                        ?>
                            <option value="<?php echo $indice;?>" <?php if($contactsel == $indice) {echo 'selected';} ?>>
                                <?php echo $texto;?>
                            </option>
                        <?php
                        }
                        ?>
                        </select>
                    </td>                
                </tr>
            </table>
            </form>
            <h2><?php echo $h2res;?></h2>
            <?php
            if ($nflotas > 0){
            ?>
                <form name="impcontactos" method="post" action="update_contflotas.php">
                    <input type="hidden" name="origen" value="importar">
                <table>
                    <tr>
                        <td class="borde"><?php echo $nreg; ?>: <?php echo $nflotas; ?></td>
                        <td class="borde">
                            <input type="image" src="imagenes/sig.png" alt="Importar" title="<?php echo $botimportar;?>"> &mdash;
                            <?php echo $botimportar;?>
                        </td>
                    </tr>
                </table>
                </form>
                <table>
                    <tr>
                        <th>ID Flota</th>
                        <th><?php echo $thacro;?></th>
                        <th>ID <?php echo $txtcontacto;?></th>
                        <th><?php echo $thtipo;?></th>
                        <th><?php echo $thnombre;?></th>
                        <th><?php echo $thcargo;?></th>
                        <th>Rol</th>
                    </tr>
                    <?php
                    $idcf = 1;                    
                    for ($i = 0; $i < $nflotas ; $i++){
                        $flota = mysql_fetch_array($res_flotas);
                        $contactos = array(
                            'RESPONSABLE' => $flota['RESPONSABLE'], 'CONTACTO1' => $flota['CONTACTO1'], 
                            'CONTACTO2' => $flota['CONTACTO2'], 'CONTACTO2' => $flota['CONTACTO2'], 
                            'INCID1' => $flota['INCID1'], 'INCID2' => $flota['INCID2'],
                            'INCID3' => $flota['INCID3'], 'INCID4' => $flota['INCID4']
                        );
                        $contactos_flota = array();
                        // Limpiamos el array
                        foreach ($contactos as $tipocont => $idcont){
                            if ($idcont > 0){
                                $contactos_flota[$tipocont] = $idcont;
                                $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idcont;
                                $res_contacto = mysql_db_query($base_datos, $sql_contacto) or die("Error en la consulta de Contacto:" .        mysql_error());
                                $ncontacto = mysql_num_rows($res_contacto);
                                if ($ncontacto > 0){
                                    $contacto = mysql_fetch_array($res_contacto);
                                    $rolcontacto = $tipocont;
                                    if ($tipocont == "RESPONSABLE"){
                                        $rolcontacto =  "RESPONSABLE";
                                    }
                                    else{
                                        // Aquí
                                        $posicion = strpos($tipocont, "CONTACTO");
                                        if ($posicion === FALSE){
                                            $rolcontacto = "CONT24H";
                                        }
                                        else{
                                            $rolcontacto = "OP + TEC";
                                        }
                                    }
                    ?>
                                    <tr <?php if (($idcf % 2) == 0) {echo " class='filapar'";}?>>
                                        <td class="centro"><?php echo $flota['ID']; ?></td>
                                        <td class="centro"><?php echo $flota['ACRONIMO']; ?></td>
                                        <td class="centro"><?php echo $idcont; ?></td>
                                        <td class="centro"><?php echo $tipocont; ?></td>                                
                                        <td><?php echo $contacto['NOMBRE']; ?></td>
                                        <td><?php echo $contacto['CARGO']; ?></td>
                                        <td><?php echo $rolcontacto; ?></td>
                                    </tr>                     
                <?php
                                $idcf++;
                            }
                        }
                    }
                }
                ?>
                </table>
            <?php                        
            }
            else{
            ?>
                <p class="error">No se han encontrado flotas con los criterios seleccionados</p>
        <?php
            }
        }
        else{
        ?>
            <h1><?php echo $h1noperm;?></h1>
            <p class="error">Error: <strong><?php echo $errnoperm;?></strong></p>
        <?php
        }
        ?>
        
    </body>
</html>