<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = 'idioma/flotaseditar_' . $idioma . '.php';
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
// Conexión a la BBDD:
require_once 'conectabbdd.php';

// Obtenemos el usuario
include_once('autenticacion.php');

/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación (Oficina COMDES)
 */
$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
?>
<!DOCTYPE html>
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
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/flotas_editar.js"></script>
    </head>
    <body>
    <?php
    if ($permiso > 1) {
        //datos de la tabla Flotas
        require_once 'sql/flotas_editar.php';
    ?>
        <h1><?php echo $h1; ?></h1>
        <?php
        if ($nflota > 0){
        ?>
            <form name="detflota" id="detflota" action="detalle_flota.php" method="POST">
                <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            </form>
            <form name="formflota" id="formflota" action="update_flota.php" method="POST">
                <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
                <input type="hidden" name="origen" value="editar">
                <input type="hidden" name="errflotalong" id="errflotalong" value="<?php echo $errflotalong; ?>">
                <input type="hidden" name="erracrolong" id="erracrolong" value="<?php echo $erracrolong; ?>">
                <h2><?php echo $h2flota; ?></h2>
                <table>
                    <tr>
                        <th class="t40p"><?php echo $thnombre; ?></th>
                        <th class="t5c"><?php echo $thacronimo; ?></th>
                        <th class="t10c"><?php echo $thambito; ?></th>
                        <th class="t10c"><?php echo $thencripta; ?></th>
                    </tr>
                    <tr>
                        <td><input type="text" name="flota" id="flota" value="<?php echo $flota["FLOTA"]; ?>" size="40"></td>
                        <td><input type="text" name="acronimo" id="acronimo" value="<?php echo $flota["ACRONIMO"]; ?>" size="10"></td>
                        <td>
                            <select name="ambito" id="selambito">
                                <?php
                                $ambitos = array(
                                    'NADA' => $txtambnada, 'LOC' => $txtambloc, 'PROV' => $txtambprov, 'AUT' => $txtambaut
                                );
                                foreach ($ambitos as $idamb => $txtamb) {
                                ?>
                                    <option value="<?php echo $idamb;?>" <?php if ($flota['AMBITO'] == $idamb) {echo "selected";} ?>>
                                        <?php echo $txtamb;?>
                                    </option>
                                <?php
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <select name="encriptacion">
                                <option value="SI" <?php if ($flota["ENCRIPTACION"] == "SI") echo 'selected'; ?>>SI</option>
                                <option value="NO" <?php if ($flota["ENCRIPTACION"] == "NO") echo 'selected'; ?>>NO</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <h2><?php echo $h2localiza; ?></h2>
                <table>
                    <tr>
                        <th class="t40p"><?php echo $thdomicilio; ?></th>
                        <th class="t5c"><?php echo $thcp; ?></th>
                        <th class="t40p"><?php echo $thciudad; ?></th>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="domicilio" value="<?php echo $flota["DOMICILIO"]; ?>" size="40">
                        </td>
                        <td><input type="text" name="cp" value="<?php echo $flota["CP"]; ?>" size="10"></td>
                        <td>
                            <select name="ine">
                            <?php
                            foreach ($selmuni as $mun_ine => $mun_nom) {                        
                            ?>
                                <option value="<?php echo $mun_ine; ?>" <?php if ($flota['INE'] == $mun_ine) echo 'selected'; ?>>
                                        <?php echo $mun_nom; ?>
                                </option>
                            <?php
                            }
                            ?>
                            </select>
                        </tr>
                    </table>
                    <h2><?php echo $h2org; ?></h2>
                    <select name="organiza">
                    <?php
                    foreach ($selorg as $org_id => $org_nom) {                        
                    ?>
                        <option value="<?php echo $org_id; ?>" <?php if ($flota['ORGANIZACION'] == $org_id) echo 'selected'; ?>>
                            <?php echo $org_nom; ?>
                        </option>
                    <?php
                    }
                    ?>
                    </select>
                    <h2><?php echo $h2term; ?></h2>
                        <?php
                        $rango = explode('-', $flota['RANGO']);
                        ?>
                        <p>
                            <input type="text" name="rangoini" value="<?php echo $rango[0]; ?>" size="10"> &mdash;
                            <input type="text" name="rangofin" value="<?php echo $rango[1]; ?>" size="10">
                        </p>
                        <table>
                            <tr>
                                <td class="borde">
                                    <input type='image' name='action' src='imagenes/guardar.png' alt='<?php echo $botguardar; ?>' title="<?php echo $guardar; ?>"><br><?php echo $botguardar; ?>
                                </td>
                                <td class="borde">
                                    <a href='#' id="botreset">
                                        <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title="<?php echo $botcancel; ?>">
                                    </a><br><?php echo $botcancel; ?>
                                </td>
                                <td class="borde">
                                    <a href='#' id="botvolver">
                                        <img src='imagenes/atras.png' alt='<?php echo $botvolver; ?>' title="<?php echo $botvolver; ?>">
                                    </a><br><?php echo $botvolver; ?>
                                </td>
                            </tr>
                        </table>
            </form>
        <?php
        }
        else {
    ?>
            <p class='error'><?php echo $errnoflota; ?></p>
    <?php
        }
    }
    else {
    ?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $errnoperm; ?></p>
    <?php
    }
    ?>
    </body>
</html>