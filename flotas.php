<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotas_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
// Conexión a la BBDD:
require_once 'conectabbdd.php';

// Obtenemos el usuario
include_once('autenticacion.php');

$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!-- JavaScript: Funciones jQyery -->
        <script src="js/jquery.js"></script>
        <script type="text/javascript" src="js/flotas.js"></script>
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
    if ($permiso > 1) {
        require_once 'sql/flotas.php';
        $nflotas = count($flotas);
        if (isset ($update)){
            require_once 'mensflash.php';
        }
    ?>
        <h1>
            <?php echo $h1; ?> &mdash; <a href="flotas.php" target="_blank"><img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>"></a>
        </h1>
        <?php
        if (isset ($_POST['update'])){
            require_once 'mensflash.php';
        }
        ?>
        <form action="flotas.php" name="formflotas" id="formflotas" method="POST">
            <input type="hidden" name="pagina" id="inputpag" value="<?php echo $pagina;?>" />
            <input type="hidden" name="npaginas" id="inputnpag" value="<?php echo $npaginas;?>" />
            <h4>
                <?php echo $criterios; ?> &mdash;
                <a href="flotas.php">
                    <img src="imagenes/update.png" alt="<?php echo $resetcrit;?>" title="<?php echo $resetcrit;?>">
                </a>
            </h4>
            <table>
                <tr>
                    <td>
                        <label for="selrog"><?php echo $thorg; ?>: </label>
                        <select name="idorg" id="selorg" class="form-control">
                            <option value="0">Seleccionar</option>
                            <?php
                            foreach ($selorganiza as $organiza) {
                            ?>
                                <option value="<?php echo $organiza['ID'];?>" <?php if ($_POST['idorg'] == $organiza['ID']) {echo "selected";} ?>>
                                    <?php echo $organiza['ORGANIZACION'];?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>

                    </td>
                    <td>
                        <label for="selambito"><?php echo $txtambito; ?>: </label>
                        <select name="ambito" id="selambito">
                            <option value='00' <?php if (($_POST['ambito'] == "00") || ($_POST['ambito'] == "")) echo ' selected'; ?>>
                                Seleccionar
                            </option>
                            <?php
                            $ambitos = array(
                                'NADA' => $txtambnada, 'LOC' => $txtambloc, 'PROV' => $txtambprov, 'AUT' => $txtambaut
                            );
                            foreach ($ambitos as $idamb => $txtamb) {
                            ?>
                                <option value="<?php echo $idamb;?>" <?php if ($_POST['ambito'] == $idamb) {echo "selected";} ?>>
                                    <?php echo $txtamb;?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="selflota">Flota: </label>
                        <select name="idflota" id="selflota">
                            <option value="0">Seleccionar</option>
                            <?php
                            foreach ($selflotas as $flota) {
                            ?>
                                <option value="<?php echo $flota['ID'];?>" <?php if ($_POST['idflota'] == $flota['ID']) {echo "selected";} ?>>
                                    <?php echo $flota['FLOTA'];?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="selcont"><?php echo $txtcontof; ?>: </label>
                        <select name='formcont' id="selcont">
                            <option value='00' <?php if (($_POST['formcont'] == "00") || ($_POST['formcont'] == "")) echo ' selected'; ?>>Seleccionar</option>
                            <option value='SI' <?php if ($_POST['formcont'] == "SI") echo ' selected'; ?>>Sí</option>
                            <option value='NO' <?php if ($_POST['formcont'] == "NO") echo ' selected'; ?>>No</option>
                        </select>
                    </td>

                </tr>
            </table>
            <h4>
                <?php
                echo $h4res;
                if ($nftotal > 0){
                    $final = ($inicio + $tampagina);
                    if ($final > $nftotal){
                        $final = $nftotal;
                    }
                    echo ' &mdash; ' . ($inicio + 1) . ' a ' . $final . ' de ' . $nftotal . ' ' . $txtflotas;
                }
                ?>
            </h4>
            <table>
                <tr>
                    <?php
                    if ($npaginas > 1) {
                    ?>
                        <td class="borde">
                            <?php
                            if ($pagina > 1){
                            ?>
                                <a href="#" id="primera"><img src="imagenes/primera.png" alt="<?php echo $txtprim;?>"  title="<?php echo $txtprim;?>" /></a>
                                &nbsp;
                                <a href="#" id="anterior"><img src="imagenes/anterior.png" alt="<?php echo $txtprev;?>"  title="<?php echo $txtprev;?>" /></a>
                                &nbsp;
                            <?php
                            }
                            ?>
                            <?php echo $pgtxt . ' ' . $pagina . ' de ' . $npaginas;?>
                            <?php
                            if ($pagina < $npaginas){
                            ?>
                                &nbsp;
                                <a href="#" id="siguiente"><img src="imagenes/siguiente.png" alt="<?php echo $txtsig;?>"  title="<?php echo $txtsig   ;?>" /></a>
                                &nbsp;
                                <a href="#" id="ultima"><img src="imagenes/ultima.png" alt="<?php echo $txtult;?>"  title="<?php echo $txtult;?>" /></a>
                            <?php
                            }
                            ?>
                        </td>
                    <?php
                    }
                    ?>
                    <td class="borde">
                        Mostrar:
                        <select name='tampagina'>
                            <option value='30' <?php if ($tampagina == "30") {echo 'selected';}?>>30</option>
                            <option value='50' <?php if ($tampagina == "50") {echo 'selected';}?>>50</option>
                            <option value='100' <?php if ($tampagina == "100") {echo 'selected';}?>>100</option>
                            <option value='<?php echo $nftotal;?>' <?php if ($tampagina == $nftotal) {echo 'selected';}?>>Todas</option>
                        </select> <?php echo $regpg; ?>
                    </td>
                    <td class="borde">
                        <a href="nueva_flota.php"><img src="imagenes/nueva.png" alt="<?php echo $newflota; ?>" title="<?php echo $newflota; ?>"></a> &mdash;
                        <a href="contactos_flotas.php"><img src="imagenes/contactosxls.png" alt="<?php echo $contflota; ?>" title="<?php echo $contflota; ?>"></a> &mdash;
                        <a href="password_flotas.php"><img src="imagenes/llave.png" alt="<?php echo $passflotas; ?>" title="<?php echo $passflotas; ?>"></a>
                    </td>
<?php
                    if ($nftotal > 0) {
?>
                        <td class="borde">
                            <a href="#" id="pdfflotas"><img src="imagenes/pdf.png" alt="PDF" title="PDF"></a> &mdash;
                            <a href="#" id="xlsflotas"><img src="imagenes/xls.png" alt="Excel" title="Excel"></a>
                        </td>
<?php
                    }
?>
                </tr>
            </table>
        </form>
        <form name="formdet" id="formdetalle" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" id="idflota" value="0">
        </form>
        <form name="formupd" action="update_flota.php" method="POST">
            <input type="hidden" name="origen" value="rename">
        </form>
        <?php
        if ($nflotas == 0) {
        ?>
            <p class='error'><?php echo $errrnoflotas ; ?></p>
        <?php
        }
        else {
        //*TABLA CON RESULTADOS*//
        ?>
            <table>
                <tr>
                    <th><?php echo $thdetalle;?></th>
                    <th><?php echo $thorg;?></th>
                    <th><?php echo $thflota;?></th>
                    <th><?php echo $thacro;?></th>
                    <th><?php echo $thencripta?></th>
                    <th><?php echo $thterm;?></th>
                </tr>
                <?php
                $relleno = TRUE;
                $totterm = 0;
                foreach ($flotas as $flota) {
                    $relleno = !($relleno);
                    $totterm += $flota['NTERM'];
                ?>
                    <tr <?php if ($relleno) {echo "class='filapar'";}?>>
                        <td class='centro'>
                            <a href='#' name="det-<?php echo $flota["ID"];?>" id="<?php echo $flota["ID"];?>" title="<?php echo $thdetalle;?>">
                                <img src='imagenes/consulta.png' alt="<?php echo $detalle;?>">
                            </a>
                        </td>
                        <td><?php echo $flota['ORGANIZACION'];?></td>
                        <td><?php echo $flota['FLOTA'];?></td>
                        <td><?php echo $flota['ACRONIMO'];?></td>
                        <td><?php echo $flota['ENCRIPTACION'];?></td>
                        <td><?php echo $flota['NTERM'];?></td>
                    </tr>
<?php
                } //primer for
?>
                <tr><td colspan='9'>&nbsp;</td></tr>
                <tr class="filapar">
                    <th colspan="5"><?php echo $thtotales; ?></th>
                    <td class='centro'><?php echo number_format($totterm, 0, ',', '.'); ?></td>
                </tr>
<?php
            }
?>
        </table>
<?php
    } // Si el usuario no es el de la Oficina
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $errnoperm; ?></p>
<?php
    }
?>
    </body>
</html>
