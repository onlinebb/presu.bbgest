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
    <form id="select_activo" method="get">
        <div class="col-md-2">
            <div class="form-group">
                <label for="y_">Seleccionar año</label>
                <select id="y_" class="form-control" name="y_" required>
                    <?php
                    for ($i = date('Y'); $i >=date('Y')-5 ; $i--):
                        ?>
                        <option value="<?=$i?>" <?=($i == $currentYear) ? "selected" : ""?>><?=$i?></option>
                    <?php
                    endfor;
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary form-control">Mostrar</button>
            </div>
        </div>
    </form>
</div>
<!--QUITAR TABLA EN DESUSO
<div class="row">-->
<!--    <div class="col-md-2 col-md-offset-10 text-right">-->
<!--        <a id="excel" href="lib/functions.php?action=logExcelPerformance" target="_blank" class="btn btn-primary btn-md text-right">-->
<!--            Log en Excel-->
<!--        </a>-->
<!--    </div>-->
<!--</div>-->
<div class="row">
    <div class="col-md-12">
        <?php
        include 'lib/database.php';
        $pdo = Database::connect();
        ?>
        <h5>Facturación asignada (histórico - acumulado)&nbsp;<a href="lib/functions.php?action=logExcelByOwner" id="export-owner" class="btn btn-primary btn-sm">Exportar excel</a></h5>
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
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$currentYear." group by u.id order by u.nombre");
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
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)>=".$prevYear." group by u.id order by acumulado desc");
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
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$prevYear." group by u.id order by u.nombre");
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
        <h5>Facturado por proyecto&nbsp;<a href="lib/functions.php?action=logExcelByProyecto" id="export-proyecto" class="btn btn-primary btn-sm">Exportar excel</a></h5>
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
<div class="row">
    <div class="col-md-12">
        <?php
        $pdo = Database::connect();

        $order2 ="";
        if(!empty($_GET['order2'])) {
            if($_GET['order2'] == 'facturado') {
                $order2 = 'order by acumulado desc';
            }

        }
        ?>
        <h5>Facturado por cliente&nbsp;<a href="lib/functions.php?action=logExcelByClient" id="export-cliente" class="btn btn-primary btn-sm">Exportar excel</a></h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>Cliente</th>
                <th><a href="?order2=facturado">
                        Facturado <?=$prevYear?> <span class="glyphicon glyphicon-sort"></span>
                    </a></th>
                <th>Costes <?=$prevYear?></th>
                <th><a href="?order2=facturado">
                        Facturado <?=$currentYear?> <span class="glyphicon glyphicon-sort"></span>
                    </a></th>
                <th>Costes <?=$currentYear?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $result = $pdo->prepare("select e.id_empresa as id_cliente, e.nombre as cliente, sum(f.subtotal) as acumulado from (
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
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$currentYear." group by e.id_empresa ".$order2);
            $result->execute();
            $dataClientes = $result->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            $result2 = $pdo->prepare("SELECT e.id_empresa as id_cliente, e.nombre as cliente, co.id_proyecto, p.nombre, 
                        ifnull((select sum(base) as extras from presu14.pos_proveedores where id_proyecto = co.id_proyecto),0) + sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste 
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                left join presu14.empresa e on e.id_empresa=p.id_cliente 
                                                WHERE co.year = ".$currentYear." group by e.id_empresa");
            $result2->execute();
            $costesClientes = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            $resultClientes = $pdo->prepare("select e.nombre as cliente, e.id_empresa as id_cliente, sum(f.subtotal) as acumulado from (
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
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)>=".$prevYear." group by e.id_empresa ".$order2);
            $resultClientes->execute();
            $clientList = $resultClientes->fetchAll(PDO::FETCH_ASSOC);

            //Datos año previo
            $resultClientesPrev = $pdo->prepare("select e.id_empresa as id_cliente, e.nombre as cliente, sum(f.subtotal) as acumulado from (
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
                                                left join stack_bbgest.usuarios u on u.id=pr.project_owner  
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=".$prevYear." group by e.id_empresa ".$order2);
            $resultClientesPrev->execute();
            $dataClientesPrev = $resultClientesPrev->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            $result2Prev = $pdo->prepare("SELECT e.id_empresa as id_cliente, e.nombre as cliente, co.id_proyecto, p.nombre, 
            ifnull((select sum(base) as extras from presu14.pos_proveedores where id_proyecto = co.id_proyecto),0) + sum(co.horas*(select salario from stack_bbgest.salarios s where s.id_usuario=co.id_usuario and s.fecha <= co.fecha order by s.fecha desc limit 1)/1400) as coste
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                left join presu14.empresa e on e.id_empresa=p.id_cliente 
                                                WHERE co.year = ".$prevYear." group by e.id_empresa");
            $result2Prev->execute();
            $costesClientesPrev = $result2Prev->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            Database::disconnect();
            foreach ($clientList as $row):
//            for ($i = 0; $row = $result->fetch(); $i++) {
                if($row['cliente'] != null):
                ?>
                <tr>
                    <td><?php echo $row['cliente'] ?></td>
<!--                    <td class="text-right nowrap">--><?php //echo number_format($row['acumulado'], 2, ',', '.').' €' ?><!--</td>-->
<!--                    <td class="text-right">--><?php //echo number_format(isnull($costes[$row['id_cliente']]['coste']), 2, ',', '.').' €' ?><!--</td>-->

                    <td class="text-right nowrap"><?= isset($dataClientesPrev[$row['id_cliente']])?number_format($dataClientesPrev[$row['id_cliente']]['acumulado'], 2, ',', '.').' €':"-" ?></td>
                    <td class="text-right nowrap"><?= isset($costesClientesPrev[$row['id_cliente']])?number_format(empty($costesClientesPrev[$row['id_cliente']]['coste'])?0:$costesClientesPrev[$row['id_cliente']]['coste'], 2, ',', '.').' €':"-" ?></td>
                    <td class="text-right nowrap"><?= isset($dataClientes[$row['id_cliente']])?number_format($dataClientes[$row['id_cliente']]['acumulado'], 2, ',', '.').' €':"-" ?></td>
                    <td class="text-right nowrap"><?= isset($costesClientes[$row['id_cliente']])?number_format(empty($costesClientes[$row['id_cliente']]['coste'])?0:$costesClientes[$row['id_cliente']]['coste'], 2, ',', '.').' €':"-" ?></td>
                </tr>
                <?php
                endif;
            endforeach;
            ?>
            </tbody>
        </table>
    </div>
</div>

<br><br>
<?php
require_once('footer.php');
?>
