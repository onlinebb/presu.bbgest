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
require_once('config.php');
$pdo = Database::connect();

date_default_timezone_set('Europe/Madrid');

$anyo_actual = date('Y', time());
$fecha_actual = date('Y-m-d', time());
$fecha_origen = date('Y-m-d', strtotime($anyo_actual.'-01-01'));


//Presupuestado
$sql_totales = "SELECT sum(suma), count(*) from presupuesto where fecha_emision >= '".$fecha_origen."' and fecha_emision is not null and presu_origen is null";
$q_totales = $pdo->prepare($sql_totales);
$q_totales->execute();
$data_totales = $q_totales->fetch();
$presupuestado = $data_totales[0];
if(empty($presupuestado))
    $presupuestado = 0;
echo 'pres: '.$presupuestado;
echo '<br>';

//Aceptado
/*$sql_aceptados = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'aceptado'";
$q_aceptados = $pdo->prepare($sql_aceptados);
$q_aceptados->execute();
$data_aceptados = $q_aceptados->fetch();
$aceptado = $data_aceptados['total_presus'];*/

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
echo 'acep: '.$aceptado;
echo '<br>';

//Pendiente
$sql_pendientes = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'pendiente'";
$q_pendientes = $pdo->prepare($sql_pendientes);
$q_pendientes->execute();
$data_pendientes = $q_pendientes->fetch();
$pendiente = $data_pendientes['total_presus'];
if(empty($pendiente))
    $pendiente = 0;
echo 'pend: '.$pendiente;
echo '<br>';

//Facturado pendiente
$sql = "SELECT SUM(subtotal) AS total_fact from factura WHERE estado IN ('emitida')";
$q = $pdo->prepare($sql);
$q->execute();
$data = $q->fetch();
$facturas_pendientes = $data['total_fact'];
if(empty($facturas_pendientes))
    $facturas_pendientes = 0;
echo 'f pend: '.$facturas_pendientes;
echo '<br>';

//Facturado total
$sql = "SELECT SUM(subtotal) AS total_fact from factura WHERE fecha_emision >= '".$fecha_origen."' and fecha_emision is not null and estado <>'abonada'";
$q = $pdo->prepare($sql);
$q->execute();
$data = $q->fetch();
$facturado_total = $data['total_fact'];
if(empty($facturado_total))
    $facturado_total = 0;
echo 'f total: '.$facturado_total;

/*
//Ratio3
$fecha_menos_3meses = strtotime("-3 months", time());
$fecha_origen3 = date('Y-m-d', $fecha_menos_3meses);
$sql_aceptados3 = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision >= '".$fecha_origen3."' and fecha_emision is not null";
$q_aceptados3 = $pdo->prepare($sql_aceptados3);
$q_aceptados3->execute();
$data_aceptados3 = $q_aceptados3->fetch();

//presus finales: aceptados o no aceptados sin fecha_negociacion
$sql_totales3 = "SELECT sum(suma), count(*) from presupuesto where fecha_emision >= '".$fecha_origen3."' and fecha_emision is not null and fecha_negociacion is null and estado in ('aceptado','no aceptado')";
$q_totales3 = $pdo->prepare($sql_totales3);
$q_totales3->execute();
$data_totales3 = $q_totales3->fetch();
$ratio3 = $data_aceptados3[0]/$data_totales3[0];
$ratio3 = number_format($ratio3, 2, '.', '');
echo '<br>ratio3: '.$ratio3.'<br>';

//Ratio6
$fecha_menos_meses = strtotime("-6 months", time());
$fecha_origen = date('Y-m-d', $fecha_menos_meses);
$sql_aceptados = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision >= '".$fecha_origen."' and fecha_emision is not null";
$q_aceptados = $pdo->prepare($sql_aceptados);
$q_aceptados->execute();
$data_aceptados = $q_aceptados->fetch();

//presus finales: aceptados o no aceptados sin fecha_negociacion
$sql_totales = "SELECT sum(suma), count(*) from presupuesto where fecha_emision >= '".$fecha_origen."' and fecha_emision is not null and fecha_negociacion is null and estado in ('aceptado','no aceptado')";
$q_totales = $pdo->prepare($sql_totales);
$q_totales->execute();
$data_totales = $q_totales->fetch();
$ratio6 = $data_aceptados[0]/$data_totales[0];
$ratio6 = number_format($ratio6, 2, '.', '');
echo 'ratio6: '.$ratio6.'<br>';

//Ratio12
$fecha_menos_meses = strtotime("-12 months", time());
$fecha_origen = date('Y-m-d', $fecha_menos_meses);
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
$ratio12 = $data_aceptados[0]/$data_totales[0];
$ratio12 = number_format($ratio12, 2, '.', '');
echo 'ratio12: '.$ratio12.'<br>';

//Ratio24
$fecha_menos_meses = strtotime("-24 months", time());
$fecha_origen = date('Y-m-d', $fecha_menos_meses);
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
$ratio24 = $data_aceptados[0]/$data_totales[0];
$ratio24 = number_format($ratio24, 2, '.', '');
echo 'ratio24: '.$ratio24.'<br>';

$fechas = Array("03-12-2014",
"04-12-2014",
"05-12-2014",
"06-12-2014",
"07-12-2014",
"08-12-2014",
"09-12-2014",
"10-12-2014",
"11-12-2014",
"12-12-2014",
"13-12-2014",
"14-12-2014",
"15-12-2014",
"16-12-2014",
"17-12-2014",
"18-12-2014",
"19-12-2014",
"20-12-2014",
"21-12-2014",
"22-12-2014",
"23-12-2014",
"24-12-2014",
"25-12-2014",
"26-12-2014",
"27-12-2014",
"28-12-2014",
"29-12-2014",
"30-12-2014",
"31-12-2014",
"08-01-2015",
"09-01-2015",
"10-01-2015",
"11-01-2015",
"12-01-2015",
"13-01-2015",
"14-01-2015",
"15-01-2015",
"16-01-2015",
"17-01-2015",
"18-01-2015",
"19-01-2015",
"20-01-2015",
"21-01-2015",
"22-01-2015",
"23-01-2015",
"24-01-2015",
"25-01-2015",
"26-01-2015",
"27-01-2015",
"28-01-2015",
"29-01-2015",
"30-01-2015",
"31-01-2015",
"01-02-2015",
"02-02-2015",
"03-02-2015",
"04-02-2015",
"05-02-2015",
"06-02-2015",
"07-02-2015",
"08-02-2015",
"09-02-2015",
"10-02-2015",
"11-02-2015",
"12-02-2015",
"13-02-2015",
"14-02-2015",
"15-02-2015",
"16-02-2015",
"17-02-2015",
"18-02-2015",
"19-02-2015",
"20-02-2015",
"21-02-2015",
"22-02-2015",
"23-02-2015",
"24-02-2015",
"25-02-2015",
"26-02-2015",
"27-02-2015",
"28-02-2015",
"01-03-2015",
"02-03-2015",
"03-03-2015",
"04-03-2015",
"05-03-2015");

foreach ($fechas as $f) {

    $fd = strtotime($f);*/
//echo date('Y-m-d', $fd);
$fd = time();
$fecha_limite = date('Y-m-d', $fd);

//Ratio3
$fecha_menos_3meses = strtotime("-3 months", $fd);
$fecha_origen3 = date('Y-m-d', $fecha_menos_3meses);
//echo $fecha_origen3.'<br>';

//Aceptados + emitidos en el periodo
$sql_aceptados3 = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision is not null and fecha_emision >= '".$fecha_origen3."' and fecha_emision <= '".$fecha_limite."' and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen3."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados3 = $pdo->prepare($sql_aceptados3);
$q_aceptados3->execute();
$data_aceptados3 = $q_aceptados3->fetch();

//Aceptados en el periodo + emitidos cuando sea
$sql_aceptados3b = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen3."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados3b = $pdo->prepare($sql_aceptados3b);
$q_aceptados3b->execute();
$data_aceptados3b = $q_aceptados3b->fetch();

//presus finales: aceptados, no aceptados, fact. parcial, fact. total, emitidos en el periodo y sin fecha_negociacion
$sql_totales3 = "SELECT sum(suma), count(*) from presupuesto where fecha_emision is not null and fecha_emision >= '".$fecha_origen3."' and fecha_emision <= '".$fecha_limite."' and fecha_negociacion is null and estado in ('pendiente', 'aceptado', 'facturado totalmente', 'facturado parcialmente', 'no aceptado')";
//echo $sql_totales3.'<br>';
$q_totales3 = $pdo->prepare($sql_totales3);
$q_totales3->execute();
$data_totales3 = $q_totales3->fetch();

$ratio3 = $data_aceptados3[0]/$data_totales3[0];
$ratio3 = number_format($ratio3, 2, '.', '');

$ratio3b = $data_aceptados3b[0]/$data_totales3[0];
$ratio3b = number_format($ratio3b, 2, '.', '');

//Ratio6
$fecha_menos_6meses = strtotime("-6 months", $fd);
$fecha_origen6 = date('Y-m-d', $fecha_menos_6meses);
//echo $fecha_origen3.'<br>';

//Aceptados + emitidos en el periodo
$sql_aceptados6 = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision is not null and fecha_emision >= '".$fecha_origen6."' and fecha_emision <= '".$fecha_limite."' and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen6."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados6 = $pdo->prepare($sql_aceptados6);
$q_aceptados6->execute();
$data_aceptados6 = $q_aceptados6->fetch();

//Aceptados en el periodo + emitidos cuando sea
$sql_aceptados6b = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen6."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados6b = $pdo->prepare($sql_aceptados6b);
$q_aceptados6b->execute();
$data_aceptados6b = $q_aceptados6b->fetch();

//presus finales: aceptados o no aceptados sin fecha_negociacion
$sql_totales6 = "SELECT sum(suma), count(*) from presupuesto where fecha_emision is not null and fecha_emision >= '".$fecha_origen6."' and fecha_emision <= '".$fecha_limite."' and fecha_negociacion is null and estado in ('pendiente', 'aceptado', 'facturado totalmente', 'facturado parcialmente', 'no aceptado')";
//echo $sql_totales3.'<br>';
$q_totales6 = $pdo->prepare($sql_totales6);
$q_totales6->execute();
$data_totales6 = $q_totales6->fetch();

$ratio6 = $data_aceptados6[0]/$data_totales6[0];
$ratio6 = number_format($ratio6, 2, '.', '');

$ratio6b = $data_aceptados6b[0]/$data_totales6[0];
$ratio6b = number_format($ratio6b, 2, '.', '');


//Ratio12
$fecha_menos_12meses = strtotime("-12 months", $fd);
$fecha_origen12 = date('Y-m-d', $fecha_menos_12meses);

//Aceptados + emitidos en el periodo
$sql_aceptados12 = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision is not null and fecha_emision >= '".$fecha_origen12."' and fecha_emision <= '".$fecha_limite."' and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen12."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados12 = $pdo->prepare($sql_aceptados12);
$q_aceptados12->execute();
$data_aceptados12 = $q_aceptados12->fetch();

//Aceptados en el periodo + emitidos cuando sea
$sql_aceptados12b = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen12."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados12b = $pdo->prepare($sql_aceptados12b);
$q_aceptados12b->execute();
$data_aceptados12b = $q_aceptados12b->fetch();

//presus finales: aceptados o no aceptados sin fecha_negociacion
$sql_totales12 = "SELECT sum(suma), count(*) from presupuesto where fecha_emision is not null and fecha_emision >= '".$fecha_origen12."' and fecha_emision <= '".$fecha_limite."' and fecha_negociacion is null and estado in ('pendiente', 'aceptado', 'facturado totalmente', 'facturado parcialmente', 'no aceptado')";
//echo $sql_totales3.'<br>';
$q_totales12 = $pdo->prepare($sql_totales12);
$q_totales12->execute();
$data_totales12 = $q_totales12->fetch();

$ratio12 = $data_aceptados12[0]/$data_totales12[0];
$ratio12 = number_format($ratio12, 2, '.', '');

$ratio12b = $data_aceptados12b[0]/$data_totales12[0];
$ratio12b = number_format($ratio12b, 2, '.', '');

//Ratio24
$fecha_menos_24meses = strtotime("-24 months", $fd);
$fecha_origen24 = date('Y-m-d', $fecha_menos_24meses);

//Aceptados + emitidos en el periodo
$sql_aceptados24 = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_emision is not null and fecha_emision >= '".$fecha_origen24."' and fecha_emision <= '".$fecha_limite."' and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen24."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados24 = $pdo->prepare($sql_aceptados24);
$q_aceptados24->execute();
$data_aceptados24 = $q_aceptados24->fetch();

//Aceptados en el periodo + emitidos cuando sea
$sql_aceptados24b = "SELECT sum(suma), count(*) from presupuesto where estado in ('aceptado','facturado totalmente', 'facturado parcialmente') and fecha_aceptacion is not null and fecha_aceptacion >= '".$fecha_origen12."' and fecha_aceptacion <= '".$fecha_limite."'";
//echo $sql_aceptados3.'<br>';
$q_aceptados24b = $pdo->prepare($sql_aceptados24b);
$q_aceptados24b->execute();
$data_aceptados24b = $q_aceptados24b->fetch();

//presus finales: aceptados o no aceptados sin fecha_negociacion
$sql_totales24 = "SELECT sum(suma), count(*) from presupuesto where fecha_emision is not null and fecha_emision >= '".$fecha_origen24."' and fecha_emision <= '".$fecha_limite."' and fecha_negociacion is null and estado in ('pendiente', 'aceptado', 'facturado totalmente', 'facturado parcialmente', 'no aceptado')";
//echo $sql_totales3.'<br>';
$q_totales24 = $pdo->prepare($sql_totales24);
$q_totales24->execute();
$data_totales24 = $q_totales24->fetch();

$ratio24 = $data_aceptados24[0]/$data_totales24[0];
$ratio24 = number_format($ratio24, 2, '.', '');

$ratio24b = $data_aceptados24b[0]/$data_totales24[0];
$ratio24b = number_format($ratio24b, 2, '.', '');


/*echo $data_aceptados3[0].','.$data_totales3[0].','.$ratio3.','.
     $data_aceptados6[0].','.$data_totales6[0].','.$ratio6.','.
     $data_aceptados12[0].','.$data_totales12[0].','.$ratio12.','.
     $data_aceptados24[0].','.$data_totales24[0].','.$ratio24.'<br>';

echo $data_aceptados3b[0].','.$data_totales3[0].','.$ratio3b.','.
    $data_aceptados6b[0].','.$data_totales6[0].','.$ratio6b.','.
    $data_aceptados12b[0].','.$data_totales12[0].','.$ratio12b.','.
    $data_aceptados24b[0].','.$data_totales24[0].','.$ratio24b.'<br>';*/
//echo '<br><br>';

/*   $sql = "update log set
                       ratio3 = ?,
                       ratio6 = ?,
                       ratio12 = ?,
                       ratio24 = ?,
                       ratio3b = ?,
                       ratio6b = ?,
                       ratio12b = ?,
                       ratio24b = ? where fecha = ?
                     ";
   $q = $pdo->prepare($sql);
   try {
       $q->execute(
           array(
               $ratio3,
               $ratio6,
               $ratio12,
               $ratio24,
               $ratio3b,
               $ratio6b,
               $ratio12b,
               $ratio24b,
               $fecha_limite
           )
       );
   } catch (Exception $e) {
       //
   }
}*/

//Guardar
$sql = "INSERT INTO log (
                        fecha,
                        aceptado,
                        pendiente,
                        presupuestado,
                        facturas_pendientes,
                        facturado_total,
                        ratio3,
                        ratio6,
                        ratio12,
                        ratio24,
                        ratio3b,
                        ratio6b,
                        ratio12b,
                        ratio24b
                      )
          values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$q = $pdo->prepare($sql);
try {
    $q->execute(
        array(
            $fecha_actual,
            $aceptado,
            $pendiente,
            $presupuestado,
            $facturas_pendientes,
            $facturado_total,
            $ratio3,
            $ratio6,
            $ratio12,
            $ratio24,
            $ratio3b,
            $ratio6b,
            $ratio12b,
            $ratio24b
        )
    );
} catch (Exception $e) {
    echo 'fail';
}

function isnull($var, $default=0) {
    return is_null($var) ? $default : $var;
}

//guardar datos performance
$result = $pdo->prepare("select u.nombre as project_owner, u.id as id_project_owner, sum(f.total) acumulado from (
                                                    SELECT  * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.factura
                                                ) as f 
                                                left join (
                                                    SELECT  * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT  * FROM presuetal.presupuesto
                                                ) as p on p.ref=f.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto 
                                                left join stack_bbgest.campaigns ca on ca.id=pr.id_campanya 
                                                left join stack_bbgest.usuarios u on u.id=ca.id_usuario 
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=2019 group by u.id order by u.nombre");
$result->execute();

$result2 = $pdo->prepare("SELECT c.id_usuario as id_project_owner, co.id_proyecto, p.nombre, sum(co.horas*us.salario/1400) as coste
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                left join stack_bbgest.campaigns c on c.id=p.id_campanya 
                                                WHERE co.year = 2019 group by c.id_usuario");
$result2->execute();
$costes = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

//guardar en log_performance
$sql = "INSERT INTO log_performance (
                        fecha,
                        id_owner,
                        facturado,
                        costes
                      )
          values(?, ?, ?, ? )";
$q = $pdo->prepare($sql);

for ($i = 0; $row = $result->fetch(); $i++) {
    try {
        $q->execute(
            array(
                $fecha_actual,
                $row['id_project_owner'],
                number_format($row['acumulado'], 2, '.', ''),
                number_format(isnull($costes[$row['id_project_owner']]['coste']), 2, '.', '')
            )
        );
    } catch (Exception $e) {
        echo 'fail';
    }
}


Database::disconnect();

?>