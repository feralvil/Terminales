<?php
// Select de Flotas
$sql_selflotas = "SELECT ID, FLOTA FROM flotas WHERE 1";
if ((isset($_POST['idorg']))&&($_POST['idorg'] > 0)){
    $sql_selflotas .=  " AND (flotas.ORGANIZACION = " . $_POST['idorg'] .")";
}
$sql_selflotas .= " ORDER BY flotas.FLOTA ASC";
$res_selflotas = mysqli_query($link, $sql_selflotas) or die($errsqlselflo . ": " . mysqli_error($link));
$nselflotas = mysqli_num_rows($res_selflotas);
// Construimos el Select de Flotas:
$selflotas = array();
while ($flotasel = mysqli_fetch_assoc($res_selflotas)){
    $selflotas[$flotasel['ID']] = $flotasel['FLOTA'];
}
mysqli_free_result($res_selflotas);

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
    // Consulta de la tabla organizaciones:
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
        $tipos = array('F', 'M%', 'MB', 'MA', 'MG', 'P%', 'PB', 'PA', 'PX', 'D');
        $nterminales = array(
            'F' => 0, 'M%' => 0, 'MB' => 0, 'MA' => 0, 'MG' => 0,
            'P%' => 0, 'PB' => 0, 'PA' => 0, 'PX' => 0, 'D' => 0
        );
        // Consulta total de Terminales:
        $sql_termflota = 'SELECT COUNT(*) AS NTERM FROM terminales WHERE (FLOTA = ' . $idflota . ') ORDER BY terminales.ISSI';
        $res_termflota = mysqli_query($link, $sql_termflota) or die($errsqltermflota. '": ' . mysqli_error($link));
        $nterm = mysqli_fetch_assoc($res_termflota);
        $ntermflota = $nterm['NTERM'];
        foreach ($tipos as $tipo) {
            $sql_termflota = 'SELECT COUNT(*) AS NTERM FROM terminales WHERE (FLOTA = ' . $idflota . ') AND (TIPO LIKE "' . $tipo . '")';
            $res_termflota = mysqli_query($link, $sql_termflota) or die($errsqltermflota . ' "' . $tipo . '": ' . mysqli_error($link));
            $nterm = mysqli_fetch_assoc($res_termflota);
            $nterminales[$tipo] = $nterm['NTERM'];
        }
        mysqli_free_result($res_termflota);
    }
}

mysqli_close($link);
?>
