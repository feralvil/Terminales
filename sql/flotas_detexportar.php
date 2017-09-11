<?php
// Fijamos la Flota si es un usuario restringido o si se ha elegido una
$idflota = 0;
if ($permiso < 2){
    $idflota = $flota_usu;
}
else{
    if (isset($_POST['idflota'])){
        $idflota = $_POST['idflota'];
    }
}
// Consulta de tabla de flotas (limitada)
$sql_flota = "SELECT * FROM flotas WHERE (flotas.ID = $idflota)";
$res_flota = mysqli_query($link, $sql_flota) or die($errsqlflota . ': ' . mysqli_error($link));
$nflota = mysqli_num_rows($res_flota);
if ($nflota > 0){
    $flota = mysqli_fetch_assoc($res_flota);
    mysqli_free_result($res_flota);
    // Consulta de la tabla municipios:
    $idmuni = $flota['INE'];
    $sql_muni = "SELECT * FROM municipios WHERE (municipios.INE = $idmuni)";
    $res_muni = mysqli_query($link, $sql_muni) or die($errsqlmuni . ': ' . mysqli_error($link));
    $nmuni = mysqli_num_rows($res_muni);
    if ($nmuni > 0){
        $municipio = mysqli_fetch_assoc($res_muni);
        mysqli_free_result($res_muni);
    }
    // Consulta de la tabla municipios:
    $idorg = $flota['ORGANIZACION'];
    $sql_organiza = "SELECT * FROM organizaciones WHERE (organizaciones.ID = $idorg)";
    $res_organiza = mysqli_query($link, $sql_organiza) or die($errsqlorganiza . ': ' . mysqli_error($link));
    $norganiza = mysqli_num_rows($res_organiza);
    if ($norganiza > 0){
        $organiza = mysqli_fetch_assoc($res_organiza);
        mysqli_free_result($res_organiza);
        $idmunorg = $organiza['INE'];
        $munorg = array();
        if ($idmuni == $idmunorg){
            $munorg['INE'] = $municipio['INE'];
            $munorg['MUNICIPIO'] = $municipio['MUNICIPIO'];
            $munorg['PROVINCIA'] = $municipio['PROVINCIA'];
        }
        else{
            // Consulta del municipio de organización
            $sql_muni = "SELECT * FROM municipios WHERE (municipios.INE = $idmunorg)";
            $res_muni = mysqli_query($link, $sql_muni) or die($errsqlmuni . ': ' . mysqli_error($link));
            $nmuni = mysqli_num_rows($res_muni);
            if ($nmuni > 0){
                $munorg = mysqli_fetch_assoc($res_muni);
                mysqli_free_result($res_muni);
            }
        }
        // Consulta de contactos:
        $sql_contflota = "SELECT * FROM contactos_flotas WHERE (FLOTA_ID = $idflota) ORDER BY ROL ASC, ORDEN ASC";
        $res_contflota = mysqli_query($link, $sql_contflota) or die($errsqlcontflota . ': ' . mysqli_error($link));
        $ncontflota = mysqli_num_rows($res_contflota);
        $idcont = array();
        if ($organiza['RESPONSABLE'] > 0){
            $idcont['RESPORG'][0] = $organiza['RESPONSABLE'];
        }
        if ($ncontflota > 0){
            for ($i = 0; $i < $ncontflota; $i++){
                $contflota = mysqli_fetch_assoc($res_contflota);
                $idcont[$contflota['ROL']][$contflota['ORDEN']] = $contflota['CONTACTO_ID'];
            }
            mysqli_free_result($res_contflota);
            $contactos = array();
            $contunicos = array();
            foreach ($idcont as $rol => $arraycont) {
                foreach ($arraycont as $orden => $idcontacto) {
                    if (array_key_exists($idcontacto, $contunicos)){
                        $contacto = $contunicos[$idcontacto];
                    }
                    else{
                        $sql_contacto = "SELECT * FROM contactos WHERE (ID = $idcontacto)";
                        $res_contacto = mysqli_query($link, $sql_contacto) or die($errsqlcontacto . ': ' . mysqli_error($link));
                        $ncontacto = mysqli_num_rows($res_contacto);
                        if ($ncontacto > 0){
                            $contacto = mysqli_fetch_assoc($res_contacto);
                            $contunicos[$idcontacto] = $contacto;
                        }
                    }
                    $contactos[$rol][$orden] = $contacto;
                }
            }
            mysqli_free_result($res_contacto);
        }
        // Consulta de terminales:
        $sql_termflota = 'SELECT * FROM terminales WHERE (FLOTA = ' . $idflota . ') ORDER BY terminales.ISSI';
        $res_termflota = mysqli_query($link, $sql_termflota) or die($errsqltermflota. '": ' . mysqli_error($link));
        $ntermflota = mysqli_num_rows($res_termflota);
        $terminales = array();
        while ($terminal = mysqli_fetch_assoc($res_termflota)){
            $terminales[] = $terminal;
        }
        mysqli_free_result($res_termflota);
    }
}

mysqli_close($link);
?>
