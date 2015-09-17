<?php
/**
 * Created by PhpStorm.
 * User: Judit
 * Date: 17/10/14
 * Time: 12:53
 */
?>
<?php
include 'lib/database.php';
$pdo = Database::connect();

error_reporting(E_ALL);
ini_set("display_errors", 1);

$anyo_actual = date('Y', time());
$fecha_actual = date('Y-m-d', time());
$fecha_origen = date('Y-m-d', strtotime($anyo_actual . '-01-01'));

//Presupuestado
$sql_totales = "SELECT sum(suma), count(*) from presupuesto where fecha_emision >= '" . $fecha_origen . "' and fecha_emision is not null and presu_origen is null";
$q_totales = $pdo->prepare($sql_totales);
$q_totales->execute();
$data_totales = $q_totales->fetch();
$presupuestado = $data_totales[0];
if (empty($presupuestado))
    $presupuestado = 0;

$sql_aceptados = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'aceptado'";
$sql_pendientes = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'pendiente'";
$sql_pendiente_parcial = "SELECT SUM(suma-(SELECT sum(factura.subtotal) FROM factura where presupuesto_asoc = presupuesto.ref and estado <>'abonada')) AS total_presus from presupuesto WHERE estado ='facturado parcialmente'";

$q_aceptados = $pdo->prepare($sql_aceptados);
$q_pendientes = $pdo->prepare($sql_pendientes);
$q_pendiente_parcial = $pdo->prepare($sql_pendiente_parcial);

$q_aceptados->execute();
$q_pendientes->execute();
$q_pendiente_parcial->execute();

$data_aceptados = $q_aceptados->fetch();
$data_pendientes = $q_pendientes->fetch();
$data_pendiente_parcial = $q_pendiente_parcial->fetch();

$aceptado = $data_aceptados['total_presus']+$data_pendiente_parcial['total_presus'];

if(empty($aceptado))
    $aceptado = 0;

//Pendiente
$sql_pendientes = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'pendiente'";
$q_pendientes = $pdo->prepare($sql_pendientes);
$q_pendientes->execute();
$data_pendientes = $q_pendientes->fetch();
$pendiente = $data_pendientes['total_presus'];
if(empty($pendiente))
    $pendiente = 0;

//Facturado pendiente
$sql = "SELECT SUM(subtotal) AS total_fact from factura WHERE estado IN ('emitida')";
$q = $pdo->prepare($sql);
$q->execute();
$data = $q->fetch();
$facturas_pendientes = $data['total_fact'];
if (empty($facturas_pendientes))
    $facturas_pendientes = 0;

//Facturado total
$sql = "SELECT SUM(subtotal) AS total_fact from factura WHERE fecha_emision >= '" . $fecha_origen . "' and fecha_emision is not null and estado <>'abonada'";
$q = $pdo->prepare($sql);
$q->execute();
$data = $q->fetch();
$facturado_total = $data['total_fact'];
if (empty($facturado_total))
    $facturado_total = 0;

echo 'fecha act: ' . $fecha_actual . '<br>';
echo 'aceptado: ' . $aceptado . '<br>';
echo 'pendiente: ' . $pendiente . '<br>';
echo 'presupuestado: ' . $presupuestado . '<br>';
echo 'fact pendientes: ' . $facturas_pendientes . '<br>';
echo 'fact total: ' . $facturado_total . '<br>';
echo '------------------------------------';
echo '<br>';

//Guardar
$sql = "INSERT INTO log (
                        fecha,
                        aceptado,
                        pendiente,
                        presupuestado,
                        facturas_pendientes,
                        facturado_total
                      )
          values(?, ?, ?, ?, ?, ?)";
$q = $pdo->prepare($sql);
try {
    $q->execute(
        array(
            $fecha_actual,
            $aceptado,
            $pendiente,
            $presupuestado,
            $facturas_pendientes,
            $facturado_total
        )
    );
} catch (Exception $e) {
    echo 'error';
}

Database::disconnect();

calcLog(3, '2015-05-11');
calcLog(6);
calcLog(12);
calcLog(24);

function calcLog($meses, $fecha="")
{
    $pdo = Database::connect();
    $fecha_actual = date('Y-m-d', time());
    $fecha_limite = date('U', time());
    if(!empty($fecha)){
        $fecha_actual = date('Y-m-d', strtotime($fecha));
        $fecha_limite = date('U', strtotime($fecha));
    }

    $fecha_menos_3meses = strtotime("-" . $meses . " months", $fecha_limite);
    $fecha_origen3 = date('Y-m-d', $fecha_menos_3meses);
    echo "fecha origen: " . $fecha_origen3 . "<br>";
    echo "fecha limite: " . $fecha_actual . "<br>";

    //Aceptado
    //$sql_aceptados = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'aceptado' and fecha_emision is not null and fecha_emision <= '" . $fecha_origen3 . "'";
    $sql_aceptados = "SELECT aceptado from log WHERE fecha = '" . $fecha_origen3 . "'";
    //$sql_pendientes = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'pendiente' and fecha_emision is not null and fecha_emision <= '" . $fecha_origen3 . "'";
    $sql_pendientes = "SELECT pendiente from log WHERE fecha = '" . $fecha_origen3 . "'";
    $sql_pendiente_parcial = "SELECT SUM(suma-(SELECT sum(factura.subtotal) FROM factura where presupuesto_asoc = presupuesto.ref and estado <>'abonada')) AS total_presus from presupuesto WHERE estado ='facturado parcialmente' and fecha_emision is not null and fecha_emision <= '" . $fecha_origen3 . "'";

    $q_aceptados = $pdo->prepare($sql_aceptados);
    $q_pendientes = $pdo->prepare($sql_pendientes);
    $q_pendiente_parcial = $pdo->prepare($sql_pendiente_parcial);

    $q_aceptados->execute();
    $q_pendientes->execute();
    $q_pendiente_parcial->execute();

    $data_aceptados = $q_aceptados->fetch();
    $data_pendiente_parcial = $q_pendiente_parcial->fetch();

    //$aceptado = $data_aceptados['total_presus'] + $data_pendiente_parcial['total_presus'];
    $aceptado = $data_aceptados['aceptado'];

    if (empty($aceptado))
        $aceptado = 0;
    echo 'acep (alfa '.$meses.' min): ' . $aceptado;
    echo '<br>';

    //Pendiente
    $q_pendientes = $pdo->prepare($sql_pendientes);
    $q_pendientes->execute();
    $data_pendientes = $q_pendientes->fetch();
    //$pendiente = $data_pendientes['total_presus'];
    $pendiente = $data_pendientes['pendiente'];
    if (empty($pendiente))
        $pendiente = 0;

    $alfa3min = $pendiente;
    echo 'pend: ' . $pendiente;
    echo '<br>';

    $alfa3max = ($pendiente + $aceptado);
    echo 'pend+aceptado (alfa '.$meses.' max): ' . $alfa3max;
    echo '<br>';

    //Presus aceptados, facturados total, facturados parcial hasta fecha limite
    $sql_beta3 = "SELECT sum(suma), count(*) from presupuesto
                  where
                    fecha_emision is not null
                    and fecha_emision >= '" . $fecha_origen3 . "'
                    and fecha_emision <= '" . $fecha_actual . "'
                    and fecha_negociacion is null
                    and estado in ('aceptado', 'facturado totalmente', 'facturado parcialmente')";

    $q_beta3 = $pdo->prepare($sql_beta3);
    $q_beta3->execute();
    $data_beta3 = $q_beta3->fetch();
    $beta3 = number_format($data_beta3[0], 2, '.', '');
    echo 'beta3: ' . $beta3;
    echo '<br>';

    echo 'alfa '.$meses.' min + beta'.$meses.': ' . ($aceptado + $beta3);
    echo '<br>';
    $alfa3_min = ($aceptado + $beta3);

    echo 'alfa '.$meses.' max + beta'.$meses.': ' . ($alfa3max + $beta3);
    echo '<br>';
    echo '------------------------------------';
    echo '<br>';
    $alfa3_max = ($alfa3max + $beta3);

    /////////////////////////////

    $fd = time();
    $fecha_limite = date('Y-m-d', $fd);

    //Ratio3
    $fecha_menos_3meses = strtotime("-".$meses." months", $fd);
    $fecha_origen3 = date('Y-m-d', $fecha_menos_3meses);

    //Aceptados + emitidos en el periodo
    $sql_aceptados3 = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision is not null and fecha_emision >= '" . $fecha_origen3 . "' and fecha_emision <= '" . $fecha_limite . "' and fecha_aceptacion is not null and fecha_aceptacion >= '" . $fecha_origen3 . "' and fecha_aceptacion <= '" . $fecha_limite . "'";
    $q_aceptados3 = $pdo->prepare($sql_aceptados3);
    $q_aceptados3->execute();
    $data_aceptados3 = $q_aceptados3->fetch();

    //Aceptados en el periodo + emitidos cuando sea
    $sql_aceptados3b = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_aceptacion is not null and fecha_aceptacion >= '" . $fecha_origen3 . "' and fecha_aceptacion <= '" . $fecha_limite . "'";
    $q_aceptados3b = $pdo->prepare($sql_aceptados3b);
    $q_aceptados3b->execute();
    $data_aceptados3b = $q_aceptados3b->fetch();

    //presus finales: aceptados, no aceptados, fact. parcial, fact. total, emitidos en el periodo y sin fecha_negociacion
    $sql_totales3 = "SELECT sum(suma), count(*) from presupuesto where fecha_emision is not null and fecha_emision >= '" . $fecha_origen3 . "' and fecha_emision <= '" . $fecha_limite . "' and fecha_negociacion is null and estado in ('pendiente', 'aceptado', 'facturado totalmente', 'facturado parcialmente', 'no aceptado')";
    $q_totales3 = $pdo->prepare($sql_totales3);
    $q_totales3->execute();
    $data_totales3 = $q_totales3->fetch();

    $ratio3 = $data_aceptados3[0] / $data_totales3[0];
    $ratio3 = number_format($ratio3, 2, '.', '');

    $ratio3b = $data_aceptados3b[0] / $data_totales3[0];
    $ratio3b = number_format($ratio3b, 2, '.', '');

    $sql = "update log set
                        ratio".$meses." = ?,
                        ratio".$meses."b = ?,
                        beta".$meses." = ?,
                        alfa".$meses."_min = ?,
                        alfa".$meses."_max = ? where fecha = ?
                      ";
    $q = $pdo->prepare($sql);
    try {
        $q->execute(
            array(
                $ratio3,
                $ratio3b,
                $beta3,
                $alfa3_min,
                $alfa3_max,
                $fecha_actual
            )
        );
    } catch (Exception $e) {
        echo "error";
    }

    Database::disconnect();
}

?>