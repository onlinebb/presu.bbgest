<?php
/**
 * Created by PhpStorm.
 * User: Judit
 * Date: 17/10/14
 * Time: 12:53
 */
require_once('header.php');
?>
<div class="page-header">
    <a class="logo" href="index.php">
        <h3>Control Board</h3>
    </a>
</div>

<div class="row">
    <div class="col-md-10">
        <form id="control-form" class="form-inline" method="get" action="">
            <fieldset>
                <div class="form-group">
                    <input type="text" class="form-control input-lg" id="meses" name="meses" placeholder="Núm. meses análisis">
                </div>
                <button id="calcular" type="submit" class="btn btn-default btn-lg">
                    Calcular
                </button>

                <a id="excel" href="lib/functions.php?action=logExcel" target="_blank" class="btn btn-primary btn-lg pull-right">
                    Log en Excel
                </a>
            </fieldset>
        </form>

<?php

// Fecha origen por defecto -12 meses o lo que se entre en el formulario
$meses = $_GET['meses'];
if(isset($meses)) {
    $fecha_menos_meses = strtotime("-".$meses." months", time());
    $fecha_origen = date('Y-m-d', $fecha_menos_meses);

    echo '<h4>Cálculos a '.$meses.' meses</h4><br>';
}
else {
    $fecha_menos_1a = strtotime("-1 year", time());
    $fecha_origen = date('Y-m-d', $fecha_menos_1a);

    echo '<h4>Cálculos a 12 meses</h4><br>';
}

include 'lib/database.php';
$pdo = Database::connect();

$sql = "SELECT sum(DATEDIFF(fecha_aceptacion,fecha_emision))/count(*), count(*) from presupuesto where fecha_aceptacion is not null and fecha_emision >= '".$fecha_origen."' and fecha_emision is not null";
$q = $pdo->prepare($sql);
$q->execute();
$data = $q->fetch();

?>
        <div class="alert alert-success"><b>Promedio de días de aceptación:</b>
<?php
if($data && $data[1] != 0) {
    echo number_format($data[0], 0, ',', '.').' días';
}
else
{
    echo 'sin datos';
}

?>
        </div>
<?php

$sql_aceptados = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision >= '".$fecha_origen."' and fecha_emision is not null";
$q_aceptados = $pdo->prepare($sql_aceptados);
$q_aceptados->execute();
$data_aceptados = $q_aceptados->fetch();

//presus finales: aceptados o no aceptados sin fecha_negociacion
$sql_totales = "SELECT sum(suma), count(*) from presupuesto where fecha_emision >= '".$fecha_origen."' and fecha_emision is not null and fecha_negociacion is null and estado in ('aceptado','no aceptado')";
$q_totales = $pdo->prepare($sql_totales);
$q_totales->execute();
$data_totales = $q_totales->fetch();
//if($data_totales && $data_aceptados) {
$ratio = $data_aceptados[0]/$data_totales[0];

?>
        <div class="alert alert-info">
            <h5>Presupuestos totales</h5>
<?php

    if($data_totales[1] != 0) {
        echo 'Núm. presupuestos: '. $data_totales[1].'<br>';
        echo 'Total Euros: '. number_format($data_totales[0], 2, ',', '.').' &euro;';
    }
    else {
        echo 'Sin datos';
    }
?>
        </div>
        <div class="alert alert-info">
            <h5>Presupuestos aceptados</h5>
<?php
    if($data_aceptados[1] != 0) {
        echo 'Núm. presupuestos: '. $data_aceptados[1].'<br>';
        echo 'Total Euros: '. number_format($data_aceptados[0], 2, ',', '.').' &euro;';
    }
    else {
        echo 'Sin datos<br><br>';
    }
?>
        </div>
        <div class="alert alert-success">
<?php
    echo '<b>Ratio:</b> '.number_format($ratio, 2, ',', '.');

    // Todos los pendientes
/**
 * (estado='pendiente' and presu_origen is not null) OR
(estado='pendiente' and presu_origen is null and ref not in (select ref from presupuesto where estado != 'pendiente' limit 1))
 */
$sql_pendientes = "SELECT sum(suma), count(*) from presupuesto where estado='pendiente' and fecha_emision >= '".$fecha_origen."' and fecha_emision is not null";
    $q_pendientes = $pdo->prepare($sql_pendientes);
    $q_pendientes->execute();
    $data_pendientes = $q_pendientes->fetch();

/**
 * SELECT sum(suma), count(*) from presupuesto where
(estado='pendiente' and presu_origen is not null) OR
(estado='pendiente' and presu_origen is null and ref not in (select presu_origen from presupuesto where estado = 'aceptado' and presu_origen is not null))
and fecha_emision >= '20140101' and fecha_emision is not null;

select ref from presupuesto where estado='pendiente' and presu_origen is null and ref not in (select presu_origen from presupuesto where estado = 'aceptado' and presu_origen is not null) and fecha_emision >= '20140101' and fecha_emision is not null
 */
?>
        </div>
        <div class="alert alert-info">
            <h5>Presupuestos pendientes</h5>
<?php
    echo '<b>Núm. presupuestos</b>: ' . $data_pendientes[1].'<br>';
    echo '<b>Total Euros</b>: ' . number_format($data_pendientes[0], 2, ',', '.').' &euro;';

?>
        </div>
        <div class="alert alert-success">
<?php
    echo '<b>Forecast:</b> ' . number_format($ratio*$data_pendientes[0], 2, ',', '.').' &euro;';
    //}
    $q_totales = $pdo->prepare($sql_totales);
    $q_totales->execute();
    $data_totales = $q_totales->fetch();

Database::disconnect();
?>

        </div>
    </div>
</div>

