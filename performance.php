<?php
/**
 * Created by PhpStorm.
 * User: Judit
 * Date: 17/10/14
 * Time: 12:53
 */
require_once('header.php');
require_once('config.php');

function isnull($var, $default=0) {
    return is_null($var) ? $default : $var;
}
?>
<div class="page-header">
    <a class="logo" href="index.php">
        <h3>Performance</h3>
    </a>
</div>
<div class="row">
    <a id="excel" href="lib/functions.php?action=logExcelPerformance" target="_blank" class="btn btn-primary btn-lg pull-right">
        Log en Excel
    </a>
    <div class="col-md-12">
        <?php
        include 'lib/database.php';
        $pdo = Database::connect();
        ?>
        <h5>Facturado por project owner 2019</h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>Project Owner</th>
                <th>Facturado</th>
                <th>Costes</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $result = $pdo->prepare("select u.nombre as project_owner, u.id as id_project_owner, sum(f.subtotal) acumulado from (
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
                                                left join stack_bbgest.usuarios u on u.id=f.id_owner 
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=2019 group by u.id order by u.nombre");
            $result->execute();

            $result2 = $pdo->prepare("SELECT c.id_usuario as id_project_owner, co.id_proyecto, p.nombre, sum(co.horas*us.salario/1400) as coste
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                left join stack_bbgest.campaigns c on c.id=p.id_campanya 
                                                WHERE co.year=2019 group by c.id_usuario");
            $result2->execute();
            $costes = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            Database::disconnect();
            for ($i = 0; $row = $result->fetch(); $i++) {
                ?>
                <tr>
                    <td><?php echo $row['project_owner'] ?></td>
                    <td class="text-right nowrap"><?php echo number_format($row['acumulado'], 2, ',', '.').' €' ?></td>
                    <td class="text-right"><?php echo number_format(isnull($costes[$row['id_project_owner']]['coste']), 2, ',', '.').' €' ?></td>
                </tr>
                <?php
            }
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
        <h5>Facturado por proyecto 2019</h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>Proyecto</th>
                <th>Cliente</th>
                <th>Project Owner</th>
                <th>
                    <a href="?order=facturado">
                        Facturado <span class="glyphicon glyphicon-sort"></span>
                    </a>
                </th>
                <th>Costes</th>
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
            $result = $pdo->prepare("SELECT pr.nombre as proyecto, e.nombre as cliente, sum(fact.subtotal) acumulado, u.nombre as project_owner, pr.id as id_proyecto FROM (
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
                                                left join stack_bbgest.campaigns ca on ca.id=pr.id_campanya 
                                                left join stack_bbgest.usuarios u on u.id=ca.id_usuario 
                                                left join presu14.empresa e on e.id_empresa=pr.id_cliente 
                                                WHERE fact.estado <> 'abonada' and YEAR(fact.fecha_emision)=2019 group by pr.nombre,u.nombre ".$order);
            $result->execute();

            $result2 = $pdo->prepare("SELECT co.id_proyecto, p.nombre, sum(co.horas*us.salario/1400) as coste 
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto
                                                WHERE co.year = 2019 group by co.id_proyecto");
            $result2->execute();
            $costes = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);
//            var_dump($costes);

            Database::disconnect();
            for ($i = 0; $row = $result->fetch(); $i++) {
                if(empty( $costes[$row['id_proyecto']]['coste']))
                    $costeaux=0;
                else
                    $costeaux = $costes[$row['id_proyecto']]['coste'];
                ?>
                <tr>
                    <td><?php echo $row['proyecto'] ?></td>
                    <td><?php echo $row['cliente'] ?></td>
                    <td><?php echo $row['project_owner'] ?></td>
                    <td class="text-right nowrap"><?php echo number_format($row['acumulado'], 2, ',', '.').' €' ?></td>
                    <td class="text-right nowrap"><?php echo number_format($costeaux, 2, ',', '.').' €' ?></td>
                </tr>
                <?php
            }
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
        <h5>Facturado por cliente 2019</h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>Cliente</th>
                <th><a href="?order2=facturado">
                        Facturado <span class="glyphicon glyphicon-sort"></span>
                    </a></th>
                <th>Costes</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $result = $pdo->prepare("select e.nombre as cliente, e.id_empresa as id_cliente, sum(f.subtotal) as acumulado from (
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
                                                where f.estado <> 'abonada' and YEAR(f.fecha_emision)=2019 group by e.id_empresa ".$order2);
            $result->execute();

            $result2 = $pdo->prepare("SELECT e.id_empresa as id_cliente, e.nombre as cliente, co.id_proyecto, p.nombre, sum(co.horas*us.salario/1400) as coste 
                                                FROM stack_bbgest.coeficiente co 
                                                left join stack_bbgest.usuarios us on us.id=co.id_usuario 
                                                left join stack_bbgest.proyectos p on p.id=co.id_proyecto 
                                                left join presu14.empresa e on e.id_empresa=p.id_cliente 
                                                WHERE co.year = 2019 group by e.id_empresa");
            $result2->execute();
            $costes = $result2->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

            Database::disconnect();
            for ($i = 0; $row = $result->fetch(); $i++) {
                ?>
                <tr>
                    <td><?php echo $row['cliente'] ?></td>
                    <td class="text-right nowrap"><?php echo number_format($row['acumulado'], 2, ',', '.').' €' ?></td>
                    <td class="text-right"><?php echo number_format(isnull($costes[$row['id_cliente']]['coste']), 2, ',', '.').' €' ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<br><br>
<?php
require_once('footer.php');
?>