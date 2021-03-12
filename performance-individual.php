<?php
/**
 * Created by PhpStorm.
 * User: Judit
 * Date: 17/10/14
 * Time: 12:53
 */
require_once('header.php');
require_once('config.php');


if (isset($_GET["y_"])) {
    $currentYear = $_GET["y_"];
    $prevYear = $currentYear-1;
}
else {
    $currentYear = date('Y');
    $prevYear = date('Y')-1;
}

function isnull($var, $default=0) {
    return is_null($var) ? $default : $var;
}
?>
<div class="page-header">
    <a class="logo" href="index.php">
        <h3>Performance</h3>
    </a>
</div>
<!-- Números semana -->
<div class="row">
    <div class="col-md-12">
        <?php
        include 'lib/database.php';
        $pdo = Database::connect();
        ?>
        <h5>Facturación asignada (histórico - acumulado)&nbsp</h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>Responsable</th>
                <th>Facturado <?=$prevYear?></th>
                <th>Costes <?=$prevYear?></th>
                <th>Facturado <?=$currentYear?></th>
                <th>Costes <?=$currentYear?></th>
            </tr>
            </thead>
            <tbody>
            <?php

            //Resultados current year
            //en la query de la suma usamos el owner de la factura porque el owner de la factura no tiene por qué ser el owner del proyecto de este año
            $result = $pdo->prepare("select u.id as id_project_owner, u.nombre as project_owner, sum(f.subtotal) acumulado from (
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
                                                left join stack_bbgest.usuarios u on u.id=f.id_owner 
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$currentYear." 
                                                and u.id=".$_SESSION['id_stack']." group by u.id order by u.nombre");
            $result->execute();
            $dataCurrentYear = $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
//            var_dump($dataCurrentYear);

            $result2 = $pdo->prepare("SELECT p.project_owner as id_project_owner, co.id_proyecto, p.nombre, 
            ifnull((select sum(base) as extras from presu14.pos_proveedores where id_proyecto = co.id_proyecto),0) + sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                WHERE co.year=".$currentYear." group by p.project_owner");
            $result2->execute();
            $costes = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            //listado owners
            $resultOwners = $pdo->prepare("select u.id as id_project_owner, u.nombre as nombre, sum(f.subtotal) acumulado from (
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
                                                left join stack_bbgest.usuarios u on u.id=f.id_owner 
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)>=".$prevYear."  
                                                and u.id=".$_SESSION['id_stack']." group by u.id order by acumulado desc");
            $resultOwners->execute();
            $projectOwners = $resultOwners->fetchAll(PDO::FETCH_ASSOC);
//            var_dump($projectOwners);

            //Resultados prev year
            $resultprev = $pdo->prepare("select u.id as id_project_owner, u.nombre as project_owner, sum(f.subtotal) acumulado from (
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
                                                left join stack_bbgest.usuarios u on u.id=f.id_owner 
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$prevYear." 
                                                and u.id=".$_SESSION['id_stack']."  group by u.id order by u.nombre");
            $resultprev->execute();
//            $dataPrevYear = $resultprev->fetchAll(PDO::FETCH_ASSOC);
            $dataPrevYear = $resultprev->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
//            var_dump($dataPrevYear);

            $result2prev = $pdo->prepare("SELECT p.project_owner as id_project_owner, co.id_proyecto, p.nombre, 
            ifnull((select sum(base) as extras from presu14.pos_proveedores where id_proyecto = co.id_proyecto),0) + sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                WHERE co.year=".$prevYear." group by p.project_owner");
            $result2prev->execute();
            $costesprev = $result2prev->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            Database::disconnect();

            foreach ($projectOwners as $row):
                if($row['id_project_owner'] != null && $row['id_project_owner'] != 0):
                ?>
                <tr>
                    <td><?php echo $row['nombre'] ?></td>
                    <td class="text-right nowrap"><?php echo number_format($dataPrevYear[$row['id_project_owner']]['acumulado'], 2, ',', '.').' €' ?></td>
                    <td class="text-right"><?php echo number_format(isnull($costesprev[$row['id_project_owner']]['coste']), 2, ',', '.').' €' ?></td>
                    <td class="text-right nowrap"><?php echo number_format($dataCurrentYear[$row['id_project_owner']]['acumulado'], 2, ',', '.').' €' ?></td>
                    <td class="text-right"><?php echo number_format(isnull($costes[$row['id_project_owner']]['coste']), 2, ',', '.').' €' ?></td>
<!--                    <td class="text-right nowrap">--><?php //echo number_format($row['acumulado'], 2, ',', '.').' €' ?><!--</td>-->
<!--                    <td class="text-right">--><?php //echo number_format(isnull($costes[$row['id_project_owner']]['coste']), 2, ',', '.').' €' ?><!--</td>-->
                </tr>
                <?php
                endif;
            endforeach;
            ?>
            </tbody>
        </table>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php
        $pdo = Database::connect();
        ?>
        <h5>Facturado por proyecto</h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>Proyecto</th>
                <th>Cliente</th>
                <th>Project Owner</th>
                <th>
                    <a href="?order=facturado">
                        Facturado <?=$prevYear?> <span class="glyphicon glyphicon-sort"></span>
                    </a>
                </th>
                <th>Costes <?=$prevYear?></th>
                <th>
                    <a href="?order=facturado">
                        Facturado <?=$currentYear?> <span class="glyphicon glyphicon-sort"></span>
                    </a>
                </th>
                <th>Costes <?=$currentYear?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $order = "";
            if(!empty($_GET['order'])) {
                if($_GET['order'] == 'facturado') {
                    $order = 'order by acumulado desc';
                }

            }
            $result = $pdo->prepare("SELECT pr.id as id_proyecto, pr.nombre as proyecto, e.nombre as cliente, 
                                                sum(fact.subtotal) acumulado, u.nombre as project_owner FROM (
                                                    SELECT * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT * FROM presuetal.factura
                                                ) as fact
                                                left join (
                                                    SELECT * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT * FROM presuetal.presupuesto
                                                ) as p on p.ref=fact.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto  
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                WHERE fact.estado <> 'abonada' and YEAR(fact.fecha_emision)=".$currentYear." 
                                                and u.id=".$_SESSION['id_stack']." 
                                                group by pr.id,u.nombre ".$order);
            $result->execute();
            $dataProyectosCurrentYear = $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
            //            var_dump($dataProyectosCurrentYear);

            $result2 = $pdo->prepare("SELECT co.id_proyecto, p.nombre, 
                        ifnull((select sum(base) as extras from presu14.pos_proveedores where id_proyecto = co.id_proyecto),0) + sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste 
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto
                                                WHERE co.year = ".$currentYear." group by co.id_proyecto");

            $result2->execute();
            $costes = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
            //            var_dump($costes);

            $resultProyectos = $pdo->prepare("SELECT pr.id as id_proyecto, pr.nombre as proyecto, e.nombre as cliente, 
                                                    u.nombre as project_owner, sum(fact.subtotal) acumulado FROM (
                                                    SELECT * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT * FROM presuetal.factura
                                                ) as fact
                                                left join (
                                                    SELECT * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT * FROM presuetal.presupuesto
                                                ) as p on p.ref=fact.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto  
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                WHERE fact.estado <> 'abonada' and YEAR(fact.fecha_emision)>=".$prevYear." 
                                                and u.id=".$_SESSION['id_stack']." 
                                                group by pr.id,u.nombre ".$order);
            $resultProyectos->execute();
            $projectList = $resultProyectos->fetchAll(PDO::FETCH_ASSOC);
            //            var_dump($projectList);

            //datos año previo
            $resultPrev = $pdo->prepare("SELECT pr.id as id_proyecto, pr.nombre as proyecto, e.nombre as cliente, 
                                                    sum(fact.subtotal) acumulado, u.nombre as project_owner FROM (
                                                    SELECT * FROM presu14.factura
                                                    UNION ALL
                                                    SELECT * FROM presuetal.factura
                                                ) as fact
                                                left join (
                                                    SELECT * FROM presu14.presupuesto
                                                    UNION ALL
                                                    SELECT * FROM presuetal.presupuesto
                                                ) as p on p.ref=fact.presupuesto_asoc 
                                                left join stack_bbgest.proyectos pr on pr.id=p.id_proyecto  
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                WHERE fact.estado <> 'abonada' and YEAR(fact.fecha_emision)=".$prevYear." 
                                                and u.id=".$_SESSION['id_stack']." 
                                                group by pr.id,u.nombre ".$order);
            $resultPrev->execute();
            $dataProyectosPrevYear = $resultPrev->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
            //            var_dump($dataProyectosCurrentYear);

            $result2Prev = $pdo->prepare("SELECT co.id_proyecto, p.nombre, 
ifnull((select sum(base) as extras from presu14.pos_proveedores where id_proyecto = co.id_proyecto),0) + sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste 
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto
                                                WHERE co.year = ".$prevYear." group by co.id_proyecto");
            $result2Prev->execute();
            $costesPrev = $result2Prev->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
            //            var_dump($costes);

            Database::disconnect();
            foreach ($projectList as $row):
                if($row['id_proyecto'] != null):
                    ?>
                    <tr>
                        <td><?php echo $row['proyecto'] ?></td>
                        <td><?php echo $row['cliente'] ?></td>
                        <td><?php echo $row['project_owner'] ?></td>
                        <td class="text-right nowrap"><?= isset($dataProyectosPrevYear[$row['id_proyecto']])?number_format($dataProyectosPrevYear[$row['id_proyecto']]['acumulado'], 2, ',', '.').' €':"-" ?></td>
                        <td class="text-right nowrap"><?= isset($costesPrev[$row['id_proyecto']])?number_format(empty($costesPrev[$row['id_proyecto']]['coste'])?0:$costesPrev[$row['id_proyecto']]['coste'], 2, ',', '.').' €':"-" ?></td>
                        <td class="text-right nowrap"><?= isset($dataProyectosCurrentYear[$row['id_proyecto']])?number_format($dataProyectosCurrentYear[$row['id_proyecto']]['acumulado'], 2, ',', '.').' €':"-" ?></td>
                        <td class="text-right nowrap"><?= isset($costes[$row['id_proyecto']])?number_format(empty($costes[$row['id_proyecto']]['coste'])?0:$costes[$row['id_proyecto']]['coste'], 2, ',', '.').' €':"-" ?></td>
                    </tr>
                <?php
                endif;
            endforeach;
            ?>
            </tbody>
        </table>
    </div>
</div>

<div class="page-header coeficientes">
    <a class="logo" href="index.php">
        <h3>Coeficientes</h3>
    </a>
</div>

<?php
$semanaIni = 1;
if (date('W') > 12) {
$semanaIni = date('W') - 11;
}
$semanaFin = date('W');

$pdo = Database::connect('stack_bbgest');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//SQL lista usuarios
$sql_usuarios = "select id, nombre,
(select salario from stack_bbgest.salarios s where s.id_usuario=usuarios.id order by s.fecha desc limit 1) as salario
from usuarios where estado=1";
$q_usuarios = $pdo->prepare($sql_usuarios);
$q_usuarios->execute(array());
$data_usuarios = $q_usuarios->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET["allproj"])) {
$cerrada = " year(pr.kickoff)=".date('Y')." AND ";
$estados = "('pendiente','aceptado','facturado totalmente','facturado parcialmente','cobrado')";
}
else {
$cerrada = " pr.cerrado=0 AND ";
$estados = "('aceptado','facturado totalmente','facturado parcialmente','cobrado')";
}

//SQL lista proyectos con datos en tabla coeficiente
$sql_proyectos = "select nombre,id,kickoff, delivery_date, sum(euros) as euros, estado, owner  FROM (
select pr.project_owner as owner, pr.nombre as nombre, pr.id as id, pr.kickoff as kickoff, pr.ptc as delivery_date, sum(presu.suma) as euros, presu.estado as estado
from stack_bbgest.proyectos pr
left join presu14.presupuesto presu on presu.id_proyecto=pr.id
where ".$cerrada."  presu.estado in ".$estados." group by id
UNION ALL

select pr.project_owner as owner, pr.nombre as nombre, pr.id as id, pr.kickoff as kickoff, pr.ptc as delivery_date, sum(presu.suma) as euros, presu.estado as estado
from stack_bbgest.proyectos pr
left join presuetal.presupuesto presu on presu.id_proyecto=pr.id
where ".$cerrada."  presu.estado in ".$estados." group by id) sq group by id";

//echo $sql_proyectos;

$q_proyectos = $pdo->prepare($sql_proyectos);
$q_proyectos->execute(array());
$data_proyectos = $q_proyectos->fetchAll(PDO::FETCH_ASSOC);

$sql = "select pr.nombre as proyecto, us.nombre as usuario, co.numSemana, co.horas
from coeficiente co
left join proyectos pr on pr.id=co.id_proyecto
left join usuarios us on us.id=co.id_usuario
where numSemana between 1 and " . $semanaFin . " order by pr.id, us.id, co.numSemana";
$q = $pdo->prepare($sql);
$q->execute(array());
$data = $q->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="row">
    <div class="col-md-10">
        <?php
        if (isset($_GET["allproj"])) {
            $string = str_replace("&allproj=1", "", $_SERVER['QUERY_STRING']);
            $boton = "Restablecer";
        } else {
            $string = $_SERVER['QUERY_STRING'] . "&allproj=1";
            $boton = "Mostrar Todos (".date('Y').")";
        }

        ?>
        <a href="coeficiente.php?<?= $string ?>" class="btn btn-default btn-sm view-all-proj">
            <?= $boton ?>
        </a>
    </div>
</div>

<br><br>

<table class="table table-bordered table-striped table-curved">
    <thead>
    <tr>
        <th>PROYECTO/NÚM. SEMANA</th>
        <?php
        for ($i = $semanaIni; $i <= $semanaFin; $i++):
            ?>
            <th class="text-center"><?= $i ?></th>
        <?php
        endfor;
        ?>
        <th class="text-center">Coste Proyecto</th>
        <th class="text-center">Coef. Proyecto <br>
            <small>(&euro;_presu/cost_acu)</small>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sumaGanadosSemana = 0;
    $SumaSemanal = Array();
    $euros_semana = 0;

    for ($j = 0; $j < count($data_proyectos); $j++):
        $diasSemana = 6;
        $fechaIni = strtotime($data_proyectos[$j]['kickoff']);
        $fechaFin = strtotime($data_proyectos[$j]['delivery_date']);
        $segundos = $fechaFin - $fechaIni;
        $diferencia_dias = intval($segundos / 60 / 60 / 24);
        $semanas_imputables = $diferencia_dias / $diasSemana;
        $euros_semana = $data_proyectos[$j]['euros'] / $semanas_imputables;
        if($data_proyectos[$j]['owner'] == $_SESSION['id_stack']):
        ?>
        <tr class="row-proyecto <?= ($data_proyectos[$j]['estado'] == 'pendiente') ? 'warning' : '' ?> <?= ($j < count($data_proyectos)) ? '' : 'border-top' ?>">
            <td><?= ($j < count($data_proyectos)) ? $data_proyectos[$j]['nombre'] : '' ?> (Total:
                <?= number_format($data_proyectos[$j]['euros'], 0, ',', '.') ?>&euro; /
                Semanas: <?= number_format($semanas_imputables, 0, ',', '.') ?> /
                <?= number_format($euros_semana, 0, ',', '.') ?>&euro;/sem.)
            </td>
            <?php
            //Sumatorio de Euros por proyecto
            $sql_acumulado = 'SELECT ifnull(sum(co.horas*((select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400)),0) as suma_acumulado from coeficiente co 
                                          left join usuarios us on us.id=co.id_usuario 
                                          where co.id_proyecto=' . $data_proyectos[$j]['id'] . ';';
            $q_acumulado = $pdo->prepare($sql_acumulado);
            $q_acumulado->execute(array());
            $data_acumulado = $q_acumulado->fetch();

            $presuProyecto = 0;
            $sumaEurosProyecto = 0;
            $sumaCoeficientes = 0;

            $sumaGanadosSemana += $euros_semana;

            for ($i = $semanaIni; $i <= $semanaFin; $i++):
                $semanakickoff = date('W', $fechaIni);
                $semanaDelivery = date('W', $fechaFin);

                if ($i >= $semanakickoff && $i <= $semanaDelivery ) {
                    if(isset($SumaSemanal[$i])) {
                        $SumaSemanal[$i] = isnull($SumaSemanal[$i]) + $euros_semana;
                    }
                    else {
                        $SumaSemanal[$i] = $euros_semana;
                    }
                }
                ?>
                <td class="text-center">
                    <br>
                    <?php
                    //Sumatorio de Euros por semana y proyecto


                    //semanas anteriores
                    /*if ($i < $semanaFin) {
                        $sql_euros_guardados = 'SELECT id_usuario, ifnull(sum(co.horas*(us.salario/1400)),0) as suma_euros from coeficiente co
                                                left join usuarios us on us.id=co.id_usuario
                                                where co.numSemana=' . $i . ' and year=' . date('Y') . ' and co.id_proyecto=' . $data_proyectos[$j]['id'] . 'group by co.id_usuario;';
                        $q_euros_guardados = $pdo->prepare($sql_euros_guardados);
                        $q_euros_guardados->execute(array());
                        $data_euros_guardados = $q_euros_guardados->fetchAll(PDO::FETCH_ASSOC);
                        $sumaSemana = 0;

                        foreach ($data_euros_guardados as $euros):
                            $sumaSemana += $euros['suma_euros'];
                        endforeach;

                        echo number_format((float)$sumaSemana, 2, ',', '.');
                    } //semana actual
                    else {*/
                    $sql_euros = 'SELECT id_usuario, ifnull(sum(co.horas*((select salario from stack_bbgest.salarios s 
                                          where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400)),0) as suma_euros from coeficiente co 
                                          left join usuarios us on us.id=co.id_usuario 
                                          where co.numSemana=' . $i . ' and year=' . date('Y') . ' and co.id_proyecto=' . $data_proyectos[$j]['id'] . ' 
                                          group by co.id_usuario;';
                    $q_euros = $pdo->prepare($sql_euros);
                    $q_euros->execute(array());
                    $data_euros = $q_euros->fetchAll(PDO::FETCH_ASSOC);
                    $sumaSemana = 0;

                    foreach ($data_euros as $euros):
                        $sumaSemana += $euros['suma_euros'];
                    endforeach;

                    echo number_format((float)$sumaSemana, 2, ',', '.');
                    //echo '<br>'.$euros_semana;
                    //}

                    //$sumaEurosProyecto += $sumaSemana;
                    //$sumaCoeficientes += $euros_semana / $sumaSemana;
                    ?>
                </td>
            <?php
            endfor;
            $numSemanas = $i - $semanaIni;
            ?>
            <td class="text-center">
                <?= number_format((float)$data_acumulado['suma_acumulado'], 2, ',', '.') ?>
                <br>
                Extras:
                <?php
                $q_extras_po = $pdo->prepare("select sum(base) as extras from presu14.pos_proveedores where id_proyecto = ?");

                $q_extras_po->execute(array($data_proyectos[$j]['id']));
                $data_extras_po = $q_extras_po->fetch();

                $extras_po = !empty($data_extras_po['extras'])?$data_extras_po['extras']:0;

                ?>
                <?= number_format((float)$extras_po, 2, ',', '.') ?>
            </td>
            <td class="text-center">
                <?= number_format((float)$data_proyectos[$j]['euros'] / nozero($data_acumulado['suma_acumulado'] + $extras_po), 2, ',', '.') ?>
            </td>
        </tr>
    <?php
    endif;
    endfor;
    ?>
    </tbody>
</table>
<?php


function nozero($var) {
    if($var == 0) return 1;
    else return $var;
}
require_once('footer.php');
?>
