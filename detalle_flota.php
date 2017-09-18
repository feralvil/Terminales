<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotasdet_$idioma.php";
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
    if ($idflota == 0){
        $idflota = $flota_usu;
    }
}
else {
    $permiso = 1;
    $idflota = $flota_usu;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
<?php
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/flotas_detalle.js"></script>
    </head>
    <body>
<?php
if ($permiso > 0) {
    require_once 'sql/flotas_detalle.php';
?>
        <form name="exportar" id="export" action="#" method="POST" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
        </form>
        <form name="detalleflota" method="POST" action="detalle_flota.php" target="_blank">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
            <h1>
                <?php echo $h1 . ' ' . $flota["FLOTA"] . ' (' . $flota["ACRONIMO"] . ') &mdash; ';?>
                <a href="#" id="linkpdf"><img src="imagenes/pdf.png" alt="<?php echo $txtpdf;?>" title="<?php echo $txtpdf;?>"></a> &mdash;
                <a href="#" id="linkexcel"><img src="imagenes/xls.png" alt="<?php echo $txtexcel;?>" title="<?php echo $txtexcel;?>"></a> &mdash;
                <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $txtnewtab;?>' title="<?php echo $txtnewtab;?>">
            </h1>
        </form>
        <?php
        if (isset ($_POST['update'])){
            require_once 'mensflash.php';
        }
        // Select de Flotas
        if ($permiso > 1){
        ?>
            <h2><?php echo $h2selflota;?></h2>
            <form name="selflota" id="selflota" action="detalle_flota.php" method="post">
                <select name="idflota" id="idflota">
                <?php
                foreach ($selflotas as $flota_id => $flota_nombre) {
                ?>
                    <option value="<?php echo $flota_id;?>" <?php if ($idflota == $flota_id) {echo "selected";} ?>>
                        <?php echo $flota_nombre;?>
                    </option>
                <?php
                }
                ?>
                </select>
            </form>
        <?php
        }
        if ($nflota > 0){
            $ambitos = array('NADA' => $txtambnada, 'LOC' => $txtambloc, 'PROV' => $txtambprov, 'AUT' => $txtambaut);
        ?>
            <form name="modflota" id="modflota" action="#" method="POST">
                <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            </form>
            <form name="formterm" id="formterm" action="terminales.php" method="POST">
                <input type="hidden" name="flota" value="<?php echo $idflota; ?>">
            </form>
            <div id="contenido">
                <div id="pestanyas">
                    <ul id="tab">
                        <li>
                            <a href="#" id="linkhome" class="activo"><?php echo $tabhome;?></a>
                        </li>
                        <li><a href="#" id="linkcont"><?php echo $tabcont;?></a></li>
                        <li><a href="#" id="linkterm"><?php echo $tabterm;?></a></li>
                    </ul>
                </div>
                <div id="limpia"></div>
                <div id="inicio">
                    <h2><?php echo $h2flota; ?></h2>
                    <table>
                        <tr>
                            <th class="t40p"><?php echo $thflota; ?></th>
                            <th class="t5c"><?php echo $thacronimo; ?></th>
                            <th class="t5c"><?php echo $thusuario; ?></th>
                            <th class="t10c"><?php echo $thambito; ?></th>
                            <th class="t10c"><?php echo $thencripta; ?></th>
                        </tr>
                        <tr>
                            <td><?php echo $flota["FLOTA"]; ?></td>
                            <td><?php echo $flota["ACRONIMO"]; ?></td>
                            <td><?php echo $flota["LOGIN"]; ?></td>
                            <td><?php echo $ambitos[$flota["AMBITO"]]; ?></td>
                            <td><?php echo $flota["ENCRIPTACION"]; ?></td>
                        </tr>
                    </table>
                    <h2><?php echo $h2localiza; ?></h2>
                    <table>
                        <tr>
                            <th class="t40p"><?php echo $thdomicilio; ?></th>
                            <th class="t10c">C.P.</th>
                            <th class="t30"><?php echo $thciudad; ?></th>
                            <th class="t5c"><?php echo $thprovincia; ?></th>
                        </tr>
                        <tr>
                            <td><?php echo $flota["DOMICILIO"]; ?></td>
                            <td><?php echo $flota["CP"]; ?></td>
                            <td><?php echo $municipio["MUNICIPIO"]; ?></td>
                            <td><?php echo $municipio["PROVINCIA"]; ?></td>
                        </tr>
                    </table>
                    <h2><?php echo $h2organiza; ?></h2>
                    <?php
                    if ($norganiza > 0){
                    ?>
                        <form name="detorg" id="detorg" action="detalle_organizacion.php" method="POST">
                            <input type="hidden" name="idorg" value="<?php echo $organiza['ID'];?>">
                        </form>
                        <table>
                            <tr>
                                <th><?php echo $thorganiza; ?></th>
                                <th><?php echo $thciudad; ?></th>
                                <th><?php echo $thprovincia; ?></th>
                                <th><?php echo $thirorg; ?></th>
                            </tr>
                            <tr>
                                <td><?php echo $organiza['ORGANIZACION']; ?></td>
                                <td><?php echo $munorg["MUNICIPIO"]; ?></td>
                                <td><?php echo $munorg["PROVINCIA"]; ?></td>
                                <td class="centro">
                                    <a href="#" id="linkorg">
                                        <img src="imagenes/ir.png" alt="<?php echo $thirorg; ?>" title="<?php echo $thirorg; ?>">
                                    </a>
                                </td>
                            </tr>
                        </table>
                    <?php
                    }
                    else{
                    ?>
                        <p class='error'><?php echo $errnoorg; ?></p>
                    <?php
                    }
                    ?>
                    <table>
                        <tr>
                            <td class="borde">
                                <a href='flotas.php'>
                                    <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'>
                                </a><br><?php echo $botatras; ?>
                            </td>
                            <td class="borde">
                                <a href='#' id="linkacceso">
                                    <img src='imagenes/key.png' alt='<?php echo $botacceso; ?>' title='<?php echo $botacceso; ?>'>
                                </a><br><?php echo $botacceso; ?>
                            </td>
                            <td class="borde">
                                <a href='#' id="linkgrupos">
                                    <img src='imagenes/grupos.png' alt='<?php echo $botgrupos; ?>' title='<?php echo $botgrupos; ?>'>
                                </a><br><?php echo $botgrupos; ?>
                            </td>
                            <td class="borde">
                                <a href='#' id="linkperm">
                                    <img src='imagenes/permisos.png' alt='<?php echo $botpermiso; ?>' title='<?php echo $botpermiso; ?>'>
                                </a><br><?php echo $botpermiso; ?>
                            </td>
                            <?php
                            if ($permiso > 1){
                            ?>
                                <td class="borde">
                                    <a href='#' id="linkeditar">
                                        <img src='imagenes/pencil.png' alt='<?php echo $boteditar; ?>' title='<?php echo $boteditar; ?>'>
                                    </a><br><?php echo $boteditar; ?>
                                </td>
                                <td class="borde">
                                    <a href='#' id="linkimpexcel">
                                        <img src='imagenes/impexcel.png' alt='<?php echo $botimportar; ?>' title="<?php echo $botimportar; ?>">
                                    </a><br><?php echo $botimportar; ?>
                                </td>
                            <?php
                            }
                            ?>
                        </tr>
                    </table>
                </div>
                <div id="contactos">
                    <h2><?php echo $h2contflota; ?></h2>
                    <?php
                    if (count($contactos) > 0){
                        if ($flota['FORMCONT'] == 'NO'){
                            $clase = "flashko";
                            $imagen = "imagenes/cancelar.png";
                            $alt = "Error";
                            $textoflash = $menscontno;
                        }
                        else{
                            $clase = "flashok";
                            $imagen = "imagenes/okm.png";
                            $alt = "OK";
                            $fechacomp = explode('-', $flota['UPDCONT']);
                            $textoflash = $menscontok . " " . $fechacomp[2] . '/' . $fechacomp[1] . '/' . $fechacomp[0];
                        }
                    ?>
                        <p class="<?php echo $clase;?>">
                            <img src="<?php echo $imagen;?>" alt="<?php echo $alt;?>" title="<?php echo $alt;?>"> &mdash; <?php echo $textoflash;?>
                        </p>
                        <?php
                        $indices = array('RESPORG', 'RESPONSABLE', 'OPERATIVO', 'TECNICO', 'CONT24H');
                        $h3 = array(
                            'RESPORG' => $h3resporg, 'RESPONSABLE' => $h3respflota, 'OPERATIVO' => $h3operativo,
                            'TECNICO' => $h3tecnico, 'CONT24H' => $h3cont24h
                        );
                        $errores = array(
                            'RESPORG' => $errnoresporg, 'RESPONSABLE' => $errnorespflota, 'OPERATIVO' => $errnoop,
                            'TECNICO' => $errnotec, 'CONT24H' => $errno24h
                        );
                        foreach ($indices as $indice) {
                    ?>
                            <h3><?php echo $h3[$indice];?></h3>
                            <?php
                            if (count($contactos[$indice]) > 0){
                            ?>
                                <table>
                                    <tr>
                                        <?php
                                        if ($indice == "RESPORG"){
                                        ?>
                                            <th><?php echo $thorganiza;?></th>
                                        <?php
                                        }
                                        ?>
                                        <th><?php echo $thnombre;?></th>
                                        <?php
                                        if ($indice != "CONT24H"){
                                        ?>
                                            <th>DNI</th>
                                            <th><?php echo $thcargo;?></th>
                                        <?php
                                        }
                                        ?>
                                        <th><?php echo $thmail;?></th>
                                        <th><?php echo $thtelef;?></th>
                                    </tr>
                                    <?php
                                    foreach ($contactos[$indice] as $contacto){
                                    ?>
                                        <tr>
                                            <?php
                                            if ($indice == "RESPORG"){
                                            ?>
                                                <td><?php echo $organiza['ORGANIZACION'];?></td>
                                            <?php
                                            }
                                            ?>
                                            <td><?php echo $contacto['NOMBRE'];?></td>
                                            <?php
                                            if ($indice != "CONT24H"){
                                            ?>
                                                <td><?php echo $contacto['NIF'];?></td>
                                                <td><?php echo $contacto['CARGO'];?></td>
                                            <?php
                                            }
                                            ?>
                                            <td><?php echo $contacto['MAIL'];?></td>
                                            <td><?php echo $contacto['TELEFONO'];?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </table>
                            <?php
                            }
                            else {
                            ?>
                                <p class='error'><?php echo $errores[$indice]; ?></p>
                    <?php
                            }
                        }
                    }
                    else {
                    ?>
                        <p class='error'><?php echo $errnocont; ?></p>
                    <?php
                    }
                    if ($permiso > 1){
                    ?>
                        <table>
                            <tr>
                                <td class="borde">
                                    <a href='#' id="linkcontactos">
                                        <img src='imagenes/editacont.png' alt='<?php echo $botcontactos; ?>' title='<?php echo $botcontactos; ?>'>
                                    </a><br><?php echo $botcontactos; ?>
                                </td>
                            </tr>
                        </table>
                    <?php
                    }
                    ?>
                </div>
                <div id="term">
                    <h2><?php echo $h2termflota; ?></h2>
                    <h3><?php echo $h3rangoterm; ?></h3>
                    <?php
                    if ($flota['RANGO'] != ""){
                    ?>
                        <p><?php echo $flota['RANGO']; ?></p>
                    <?php
                    }
                    else{
                    ?>
                        <p class="error"><?php echo $mensrangono; ?></p>
                    <?php
                    }
                    ?>
                    <h3><?php echo $h3nterm; ?></h3>
                    <table>
                        <tr>
                            <th colspan="8"><?php echo $thtotterm; ?></th>
                        </tr>
                        <tr>
                            <td colspan="8" class="centro"><?php echo $ntermflota; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $thtermbase; ?></th>
                            <th colspan="3"><?php echo $thtermmov; ?></th>
                            <th colspan="3"><?php echo $thtermport; ?></th>
                            <th><?php echo $thtermdesp; ?></th>
                        </tr>
                        <tr>
                            <td class="centro" rowspan="3"><?php echo $nterminales['F']; ?></td>
                            <td class="centro" colspan="3"><?php echo $nterminales['M%']; ?></td>
                            <td class="centro" colspan="3"><?php echo $nterminales['P%']; ?></td>
                            <td class="centro" rowspan="3"><?php echo $nterminales['D']; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo $thtermmb; ?></th>
                            <th><?php echo $thtermma; ?></th>
                            <th><?php echo $thtermmg; ?></th>
                            <th><?php echo $thtermpb; ?></th>
                            <th><?php echo $thtermpa; ?></th>
                            <th><?php echo $thtermpx; ?></th>
                        </tr>
                        <tr>
                            <td class="centro"><?php echo $nterminales['MB']; ?></td>
                            <td class="centro"><?php echo $nterminales['MA']; ?></td>
                            <td class="centro"><?php echo $nterminales['MG']; ?></td>
                            <td class="centro"><?php echo $nterminales['PB']; ?></td>
                            <td class="centro"><?php echo $nterminales['PA']; ?></td>
                            <td class="centro"><?php echo $nterminales['PX']; ?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <?php
                            if ($permiso == 2) {
                            ?>
                                <td class="borde">
                                    <a href='#' id="linkakdc">
                                        <img src='imagenes/akdc.png' alt='<?php echo $botakdc; ?>' title="<?php echo $botakdc; ?>">
                                    </a><br><?php echo $botakdc; ?>
                                </td>
                                <td class="borde">
                                    <a href='#' id="linkbase">
                                        <img src='imagenes/base_add.png' alt='<?php echo $botbase; ?>' title="<?php echo $botbase; ?>">
                                    </a><br><?php echo $botbase; ?>
                                </td>
                                <td class="borde">
                                    <a href='#' id="linkaut">
                                        <img src='imagenes/autterm.png' alt='<?php echo $botaut; ?>' title='<?php echo $botaut; ?>'>
                                    </a><br><?php echo $botaut; ?>
                                </td>
                                <td class="borde">
                                    <a href='#' id="linkdots">
                                        <img src='imagenes/dots.png' alt='<?php echo $botdots; ?>' title='<?php echo $botdots; ?>'>
                                    </a><br><?php echo $botdots; ?>
                                </td>
                            <?php
                            }
                            ?>
                            <td class="borde">
                                <a href='#' id="linktermflota">
                                    <img src='imagenes/lista.png' alt='<?php echo $botterm; ?>' title='<?php echo $botterm; ?>'>
                                </a><br><?php echo $botterm; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php
        }
        else{
        ?>
            <p class='error'><?php echo $errnoflota; ?></p>
    <?php
        }
    }
    else {
    ?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $errnoperm; ?></p>
    <?php
    }
    ?>
    </body>
</html>
