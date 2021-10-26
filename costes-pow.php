<?php
/**
 * Created by PhpStorm.
 * User: Judit
 * Date: 17/10/14
 * Time: 12:53
 * NOTA RB: 5/10/21. El cálculo del coste en PERFORMANCE (Facturación asignada (histórico - acumulado)) = nºde horas IMPUTADAS A UN PROJECT OWNER * coste horario (= bruto / 1400h) + extras DESDE EL PRINCIPIO (sea o no del POW)
 * No es un dato ajustado!!! (en esta tabla) * Ahora los extras están imputadas a un proyecto
 * (hacemos ahora tabla agregado "saco" -> hora metida * coste h en ese momento + costes POs metidas)
 *  LA FACTURACION SI ES CORRECTA, porque las facturas SI se guardan con POW
 */
require_once('header.php');
require_once('config.php');

$currentYear = date('Y');

if (isset($_GET["ini_"]) && !empty($_GET['ini_'])) {
    $fecha_ini = date('Y-m-d', strtotime($_GET["ini_"]));
}
else {
    if($currentYear > 2021) {
        $fecha_ini = date('Y-m-d', strtotime('01-01-'.$currentYear));
    }
    else {
        //11-10-2021 fecha en la que se empiezan a guardar datos en la tabla costes_pow
        $fecha_ini = date('Y-m-d', strtotime('11-10-2021'));
    }
}

if (isset($_GET["fin_"]) && !empty($_GET['fin_'])) {
    $fecha_fin = date('Y-m-d', strtotime($_GET["fin_"]));
}
else {
    $fecha_fin = '';
}

function isnull($var, $default=0) {
    return is_null($var) ? $default : $var;
}
?>
<div class="page-header">
    <a class="logo" href="index.php">
        <h3>Costes Project Owner</h3>
    </a>
</div>
<!-- Números semana -->
<div class="row">
    <form id="select_rango" class="form-horizontal" method="get">
        <div class="col-md-3">
            <b>Seleccionar rango de fechas:</b><br><br>
            <div class="form-group">
                <label class="col-md-2 control-label" for="fecha">Inicio</label>
                <div class="col-md-6">
                    <input id="ini_" name="ini_" placeholder="dd-mm-yyyy" class="form-control input-sm date" type="text" value="11-10-2021">
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <br><br>
            <div class="form-group">
                <label class="col-md-2 control-label" for="fecha">Fin</label>
                <div class="col-md-6">
                    <input id="fin_" name="fin_" placeholder="dd-mm-yyyy" class="form-control input-sm date" type="text">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <br><br>
            <div class="form-group">
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
        <h5>Horas imputadas a proyecto x coste horario (SOLO COSTE INTERNO SIN EXTRAS)</h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>POW</th>
                <th>Costes</th>
            </tr>
            </thead>
            <tbody>
			<?php

            //Resultados
            $where_fecha_fin = "";
            if(!empty($fecha_fin)) {
                $where_fecha_fin = " and fecha <='".$fecha_fin."'";
            }
            $result_pow = $pdo->prepare("select us.nombre, sum(coste) as coste from costes_pow 
                                                left join stack_bbgest.usuarios us on us.id=id_usuario 
                                                where fecha>='".$fecha_ini."'".$where_fecha_fin." and tipo='interno' 
                                                group by id_usuario");
            $result_pow->execute();

            $data_pow = $result_pow->fetchAll(PDO::FETCH_ASSOC);
			Database::disconnect();

            foreach ($data_pow as $row):
                ?>
                <tr>
                    <td><?php echo $row['nombre'] ?></td>
                    <td class="text-right"><?php echo number_format(isnull($row['coste']), 2, ',', '.').' €' ?></td>
                </tr>
                <?php
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
        <h5>Costes extra (costes externos de POs proveedores imputadas a proyectos)</h5>
        <table class="table table-striped table-bordered table-curved table-hover">
            <thead>
            <tr>
                <th>POW</th>
                <th>Costes</th>
            </tr>
            </thead>
            <tbody>
            <?php

            //Resultados
            $where_fecha_fin = "";
            if(!empty($fecha_fin)) {
                $where_fecha_fin = " and fecha <='".$fecha_fin."'";
            }
            $result_pow = $pdo->prepare("select us.nombre, sum(coste) as coste from costes_pow 
                                                left join stack_bbgest.usuarios us on us.id=id_usuario 
                                                where fecha>='".$fecha_ini."'".$where_fecha_fin." and tipo='externo' 
                                                group by id_usuario");
            $result_pow->execute();

            $data_pow = $result_pow->fetchAll(PDO::FETCH_ASSOC);
            Database::disconnect();

            foreach ($data_pow as $row):
                ?>
                <tr>
                    <td><?php echo $row['nombre'] ?></td>
                    <td class="text-right"><?php echo number_format(isnull($row['coste']), 2, ',', '.').' €' ?></td>
                </tr>
            <?php
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
