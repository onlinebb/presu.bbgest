<?php
require_once('header.php');
?>

    <div class="page-header coeficientes">
        <a class="logo" href="index.php">
            <h3>Coeficientes</h3>
        </a>
    </div>

<?php

include 'lib/database.php';

$semanaIni = 1;
if(date('W')-12 > 10) {
    $semanaIni = date('W')-12;
}
$semanaFin = date('W');

$pdo = Database::connect('stack_bbgest');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//SQL lista usuarios
$sql_usuarios = "select id, nombre, salario from usuarios";
$q_usuarios = $pdo->prepare($sql_usuarios);
$q_usuarios->execute(array());
$data_usuarios = $q_usuarios->fetchAll(PDO::FETCH_ASSOC);

//SQL lista proyectos con datos en tabla coeficiente
$sql_proyectos = "select pr.nombre, pr.id, pr.kickoff, de.f_entrega as delivery_date, ifnull(sum(presu.suma),0) as euros, presu.estado from proyectos pr  
                  left join campaigns ca on pr.id_campanya=ca.id 
                  left join deliverables de on ca.id=de.id_campaign 
                  left join presu14.presupuesto presu on presu.id_proyecto=pr.id 
                  where de.nombre='PTC' AND de.f_entrega>CURDATE() AND pr.kickoff>0 AND (presu.estado<>'no aceptado' or presu.estado is null) group by pr.id";//"select pr.nombre, pr.id, pr.kickoff, pr.delivery_date, pr.euros from coeficiente co left join proyectos pr on pr.id=co.id_proyecto group by id_proyecto";
$q_proyectos = $pdo->prepare($sql_proyectos);
$q_proyectos->execute(array());
$data_proyectos = $q_proyectos->fetchAll(PDO::FETCH_ASSOC);

$sql = "select pr.nombre as proyecto, us.nombre as usuario, co.numSemana, co.horas 
        from coeficiente co 
        left join proyectos pr on pr.id=co.id_proyecto 
        left join usuarios us on us.id=co.id_usuario 
        where numSemana between 1 and ".$semanaFin." order by pr.id, us.id, co.numSemana";
$q = $pdo->prepare($sql);
$q->execute(array());
$data = $q->fetchAll(PDO::FETCH_ASSOC);

?>

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
            <th class="text-center">Coef. Proyecto <br><small>(&euro;_presu/cost_acu)</small></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sumaGanadosSemana = 0;
        $SumaSemanal = Array();

        for ($j = 0; $j < count($data_proyectos); $j++):
            $diasSemana = 6;
            //echo $data_proyectos[$j]['nombre'].'<br>';
            $fechaIni = strtotime($data_proyectos[$j]['kickoff']);
            $fechaFin = strtotime($data_proyectos[$j]['delivery_date']);
            /*echo date('d-m-Y', $fechaIni).'<br>';
            echo date('d-m-Y', $fechaFin).'<br>';*/
            $segundos = $fechaFin - $fechaIni;
            $diferencia_dias = intval($segundos / 60 / 60 / 24);
            //echo "dif= ".$diferencia_dias.'<br><br>';
            $semanas_imputables = $diferencia_dias / $diasSemana;
            $euros_semana = $data_proyectos[$j]['euros'] / $semanas_imputables;
            ?>
            <tr class="row-proyecto <?= ($data_proyectos[$j]['estado']=='pendiente')?'warning':''?> <?= ($j < count($data_proyectos)) ? '' : 'border-top' ?>">
                <td><?= ($j < count($data_proyectos)) ? $data_proyectos[$j]['nombre'] : '' ?> (Total: <?=number_format($data_proyectos[$j]['euros'], 0, ',', '.')?>&euro; / Semanas: <?=number_format($semanas_imputables, 0, ',', '.')?> / <?=number_format($euros_semana, 0, ',', '.')?>&euro;/sem.)</td>
                <?php

                /*
                    select us.nombre, ((40-sum(co.horas))*us.salario)/count(co.id_proyecto) as euros_perdidos, 40-sum(co.horas) horas_perdidas, count(co.id_proyecto) proyectos_activos, us.salario from coeficiente co
                    left join usuarios us on us.id=co.id_usuario
                    where co.numSemana = 10 group by co.id_usuario
                 */
                //Sumatorio de Euros por proyecto
                $sql_acumulado = 'SELECT ifnull(sum(co.horas*(us.salario/1400)),0) as suma_acumulado from coeficiente co 
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

                    if($i>=$semanakickoff && $i<=$semanaDelivery && $data_proyectos[$j]['estado']!='pendiente') {
                        $SumaSemanal[$i] = $SumaSemanal[$i] + $euros_semana;
                    }
                    ?>
                    <td class="text-center">

                        <br>
                        <?php

                        //Sumatorio de Euros por semana y proyecto
                        $sql_euros = 'SELECT id_usuario, ifnull(sum(co.horas*(us.salario/1400)),0) as suma_euros from coeficiente co 
                                          left join usuarios us on us.id=co.id_usuario 
                                          where co.numSemana=' . $i . ' and co.id_proyecto=' . $data_proyectos[$j]['id'] . ' group by co.id_usuario;';
                        $q_euros = $pdo->prepare($sql_euros);
                        $q_euros->execute(array());
                        $data_euros = $q_euros->fetchAll(PDO::FETCH_ASSOC);
                        $sumaSemana = 0;

                        foreach ($data_euros as $euros):
                            $sql_euros_perdidos = 'SELECT co.id_usuario, ifnull(0,40-sum(co.horas)) horas_perdidas, count(co.id_proyecto) proyectos_activos, 
                                           (ifnull(0,(40-sum(co.horas)))*(us.salario/1400))/count(co.id_proyecto) as euros_perdidos_proyecto
                                           from coeficiente co 
                                           left join usuarios us on us.id=co.id_usuario 
                                           where co.numSemana=' . $i . ' and id_usuario=' . $euros['id_usuario'] . ';';
                            $q_euros_perdidos = $pdo->prepare($sql_euros_perdidos);
                            $q_euros_perdidos->execute(array());
                            $data_euros_perdidos = $q_euros_perdidos->fetch();


                            $sumaSemana += $euros['suma_euros'] + $data_euros_perdidos['euros_perdidos_proyecto'];
                            //echo $euros['id_usuario'].' = '.$euros['suma_euros'].' + '.$data_euros_perdidos['euros_perdidos_proyecto'].'<br>';
                        endforeach;
                        echo number_format((float)$sumaSemana, 2, ',', '.');
                        /*if (empty($sumaSemana) || $data_proyectos[$j]['estado']=='pendiente') {
                            echo '';
                        } else {
                            if(!empty($euros_semana)){
                                echo number_format((float)$euros_semana / $sumaSemana, 2, ',', '.');
                            }*/

                            $sumaEurosProyecto += $sumaSemana;
                            $sumaCoeficientes += $euros_semana / $sumaSemana;
                        //}
                        ?>
                    </td>
                    <?php
                endfor;
                $numSemanas = $i - $semanaIni;
                ?>
                <td class="text-center">
                    <?= number_format((float)$data_acumulado['suma_acumulado'], 2, ',', '.') ?>
                </td>
                <td class="text-center">
                    <?= ($data_proyectos[$j]['estado']=='pendiente')?'0,00':number_format((float)$data_proyectos[$j]['euros'] / $data_acumulado['suma_acumulado'], 2, ',', '.') ?>
                </td>
            </tr>
            <?php
        endfor;
        ?>
        </tbody>
    </table>

    <!-- Euros/Semana
    <table class="table table-bordered table-striped table-curved">
        <thead>
        <tr>
            <th>EUROS/SEMANA</th>
            <?php
            for ($i = $semanaIni; $i <= $semanaFin; $i++):
                ?>
                <th class="text-center"><?= $i ?></th>
                <?php
            endfor;
            ?>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td></td>
        <?php
        for ($i = $semanaIni; $i <= $semanaFin; $i++):
            //Sumatorio de Euros por semana
            $sql_euros_semana = 'SELECT ifnull(sum(co.horas*(us.salario/1400)),0) as suma_semanal from coeficiente co 
                                              left join usuarios us on us.id=co.id_usuario 
                                              left join proyectos pr on pr.id=co.id_proyecto 
                                              where co.numSemana=' . $i . ' ;';//and pr.euros<>0
            $q_euros_semana = $pdo->prepare($sql_euros_semana);
            $q_euros_semana->execute(array());
            $data_euros_semana = $q_euros_semana->fetch();
            ?>
            <td class="text-center">
                <?=number_format($sumaGanadosSemana/$data_euros_semana['suma_semanal'], 2, ',', '.');?>

                <?php//number_format($data_euros_semana['suma_semanal'], 0, ',', '.');?>
            </td>
            <?php
        endfor;
        ?>
        </tr>
        </tbody>
    </table>-->

    <!-- Coef Bruto -->
    <table class="coef-bruto table table-bordered table-striped table-curved">
        <thead>
        <tr>
            <th>MARGEN REAL</th>
            <?php
            for ($i = $semanaIni; $i <= $semanaFin; $i++):
                ?>
                <th class="text-center"><?= $i ?></th>
                <?php
            endfor;
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        //Datos guardados de coeficiente
        $sql_coeficiente_total = 'SELECT sum(coeficiente)/count(coeficiente) from costes where year=?';
        $q_coeficiente_total = $pdo->prepare($sql_coeficiente_total);
        $q_coeficiente_total->execute(array(date('Y')));
        $data_coeficiente_total = $q_coeficiente_total->fetchAll(PDO::FETCH_COLUMN);
        ?>
        <tr>
            <td>
                <table class="table table-bordered table-striped table-curved">
                    <tr><th>Ganados</th></tr>
                    <tr><th>Coste</th></tr>
                    <tr><th>Costes extra</th></tr>
                    <tr><th>Margen <br> <small>((costes+costes_extra)/suma_semanal)</small></th></tr>
                    <tr><th>YTD = <?=number_format($data_coeficiente_total[0], 2, ',', '.');?></th></tr>
                </table>
            </td>
            <?php
            //Datos guardados de costes
            $sql_costes_semana = 'SELECT numSemana,costes from costes where year=?';
            $q_costes_semana = $pdo->prepare($sql_costes_semana);
            $q_costes_semana->execute(array(date('Y')));
            $data_costes_semana = $q_costes_semana->fetchAll(PDO::FETCH_KEY_PAIR);

            //Datos guardados de costes extra
            $sql_costes_extra_semana = 'SELECT numSemana,costes_extra from costes where year=?';
            $q_costes_extra_semana = $pdo->prepare($sql_costes_extra_semana);
            $q_costes_extra_semana->execute(array(date('Y')));
            $data_costes_extra_semana = $q_costes_extra_semana->fetchAll(PDO::FETCH_KEY_PAIR);

            //Datos guardados de coeficiente
            $sql_coeficiente_semana = 'SELECT numSemana,coeficiente from costes where year=?';
            $q_coeficiente_semana = $pdo->prepare($sql_coeficiente_semana);
            $q_coeficiente_semana->execute(array(date('Y')));
            $data_coeficiente_semana = $q_coeficiente_semana->fetchAll(PDO::FETCH_KEY_PAIR);

            for ($i = $semanaIni; $i <= $semanaFin; $i++):
                //Sumatorio de Euros por semana
                $sql_euros_semana = 'SELECT sum(us.salario/12)/4 as suma_semanal from usuarios us';
                $q_euros_semana = $pdo->prepare($sql_euros_semana);
                $q_euros_semana->execute(array());
                $data_euros_semana = $q_euros_semana->fetch();

                $costes_extra = !empty($data_costes_extra_semana[$i])?$data_costes_extra_semana[$i]:0;
                $costes = !empty($data_costes_semana[$i])?$data_costes_semana[$i]:$data_euros_semana['suma_semanal'];

                ?>
                <td class="text-center">
                    <table class="semana-<?=$i?> table table-bordered table-striped table-curved">
                        <tr>
                            <td class="ganados" data-value="<?=$SumaSemanal[$i]?>">
                                <?=number_format($SumaSemanal[$i], 2, ',', '.');?>
                            </td>
                        </tr>
                        <tr>
                            <td class="costes" contenteditable="true" data-value="<?=$costes?>" data-semana="<?=$i?>">
                                <?=number_format($costes, 2, ',', '.');?>
                            </td>
                        </tr>
                        <tr>
                            <td class="costes-extra" contenteditable="true" data-value="<?=$costes_extra?>" data-semana="<?=$i?>">
                                <?=number_format($costes_extra, 2, ',', '.')?> 
                            </td>
                        </tr>
                        <tr>
                            <?php
                            if(empty($SumaSemanal[$i])){
                                $calculoCoef = 0;
                            }
                            else {
                                $calculoCoef = (($SumaSemanal[$i])/($costes+$costes_extra));
                            }
                            ?>
                            <td class="coef" data-value="<?=$SumaSemanal[$i]/($costes+$costes_extra)?>">
                                <?=!empty($data_coeficiente_semana[$i])?$data_coeficiente_semana[$i]:number_format($calculoCoef, 2, ',', '.')?>
                            </td>
                        </tr>
                    </table>
                </td>
                <?php
            endfor;
            ?>
        </tr>
        </tbody>
    </table>

    <hr>

    <table class=" salarios table table-bordered table-striped table-curved" style="table-layout:auto">
        <thead>
        <tr>
            <th>PERSONA</th>
            <th>BRUTO/AÑO</th>
        </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data_usuarios as $usuario):
                ?>
            <tr class="">
                <td><?= $usuario['nombre'] ?></td>
                <td data-userid="<?= $usuario['id'] ?>" contenteditable="true"><?= $usuario['salario'] ?></td>
            </tr>
                <?php
            endforeach;
            ?>
        </tbody>
    </table>

    <div class="overflow">
        <?php
        if (isset($_GET["s_"])) {
            $semana_activa = $_GET["s_"];
        }
        else {
            $semana_activa = $semanaFin;
        }

        ?>
        <!-- Números semana -->
        <form id="select_activo" method="get">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="s_">Semana</label>
                    <select id="s_" class="form-control" name="s_" required>
                        <?php
                        for ($i = $semanaFin; $i >=$semanaIni ; $i--):
                            ?>
                            <option value="<?=$i?>" <?=($i == $semana_activa) ? "selected" : ""?>><?=$i?></option>
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
            <!--<div class="col-md-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <a target="_blank" href="./recogida-horas.php" class="btn btn-primary form-control">Recogida horas</a>
                </div>
            </div>-->
        </form>

        <?php
        //if($semana_activa < 48 && date('Y') == 2017) :
        ?>
            <table class="horas-proyecto table table-bordered table-hover table-striped-column table-curved">
                <thead>
                <tr>
                    <th class="headcol">HORAS/PROYECTO</th>
                    <th colspan="<?= count($data_usuarios)?>" class="info text-center"><?= $semana_activa ?></th>
                    <?php
                    /*for ($i = $semanaIni; $i <= $semanaFin; $i++):
                        if ($i == $semanaFin):
                            ?>
                            <th colspan="<?= count($data_usuarios)?>" class="info border-right text-center"><?= $i ?></th>
                            <?php
                        else:
                            ?>
                            <th colspan="<?= count($data_usuarios)?>" class="info text-center"><?= $i ?></th>
                            <?php
                        endif;
                    endfor;*/
                    ?>
                </tr>
                </thead>
                <tbody>
                <tr class="small text-center">
                    <td>&nbsp;</td>
                    <?php
                    //for ($i = $semanaIni; $i <= $semanaFin; $i++):
                    foreach ($data_usuarios as $usuario):
                        ?>
                        <th class="text-center border-right"><?= $usuario['nombre'] ?></th>
                        <?php
                    endforeach;
                    //endfor;
                    ?>
                </tr>

                <!-- Horas/proyecto -->
                <?php
                for ($j = 0; $j < count($data_proyectos); $j++):
                    ?>
                    <tr class="row-proyecto <?= ($j < count($data_proyectos)) ? '' : 'border-top' ?>">
                        <td class="headcol"><?= ($j < count($data_proyectos)) ? $data_proyectos[$j]['nombre'] : '' ?></td>
                        <?php

                        //for ($i = $semanaIni; $i <= $semanaFin; $i++):
                        foreach ($data_usuarios as $usuario):
                            //Sumatorio de horas por usuario y proyecto en una semana
                            $sql_horas = 'SELECT id_usuario, ifnull(sum(co.horas),0) as suma_horas from coeficiente co 
                                          left join usuarios us on us.id=co.id_usuario 
                                          where co.id_proyecto=' . $data_proyectos[$j]['id'] . ' and co.year='.date('Y').' and co.numSemana=' . $semana_activa . ' group by co.id_usuario;';
										  
                            $q_horas = $pdo->prepare($sql_horas);
                            $q_horas->execute(array());
                            $data_horas = $q_horas->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

                            if (!empty($data_horas[$usuario['id']])) {
                                $horas_user = $data_horas[$usuario['id']][0];
                            } else {
                                $horas_user = 0;
                            }
                            ?>
                            <td id="<?= $data_proyectos[$j]['id'].':'.$usuario['id'].':'.$semana_activa ?>" class="text-center"><?= $horas_user ?></td>
                            <?php
                        endforeach;
                        //endfor;
                        ?>
                    </tr>
                    <?php
                endfor;
                ?>
                <tr class="row-totales">
                    <td class="headcol">Total</td>
                    <?php

                    //for ($i = $semanaIni; $i <= $semanaFin; $i++):
                    foreach ($data_usuarios as $usuario):
                        //Sumatorio de horas por usuario en una semana
                        $sql_horas = 'SELECT id_usuario, ifnull(sum(co.horas),0) as suma_horas from coeficiente co 
                                          left join usuarios us on us.id=co.id_usuario 
                                          where co.year='.date('Y').' and co.numSemana=' . $semana_activa . ' group by co.id_usuario;';
                        $q_horas = $pdo->prepare($sql_horas);
                        $q_horas->execute(array());
                        $data_horas = $q_horas->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

                        if (!empty($data_horas[$usuario['id']])) {
                            $horas_user = $data_horas[$usuario['id']][0];
                        } else {
                            $horas_user = 0;
                        }
                        ?>
                        <td class="text-center"><?= $horas_user ?></td>
                        <?php
                    endforeach;
                    //endfor;
                    ?>
                </tr>
                </tbody>
            </table>
        <?php
        /*else:  //nuevo cálculo de horas recibido de tabla recogida_horas
        ?>
            <table class="horas-proyecto table table-bordered table-hover table-striped-column table-curved">
                <thead>
                <tr>
                    <th class="headcol">HORAS/PROYECTO</th>
                    <th colspan="<?= count($data_usuarios)?>" class="info text-center"><?= $semana_activa ?></th>

                </tr>
                </thead>
                <tbody>
                <tr class="small text-center">
                    <td>&nbsp;</td>
                    <?php
                    foreach ($data_usuarios as $usuario):
                        ?>
                        <th class="text-center border-right"><?= $usuario['nombre'] ?></th>
                        <?php
                    endforeach;
                    ?>
                </tr>

                <!-- Horas/proyecto -->
                <?php
                for ($j = 0; $j < count($data_proyectos); $j++):
                    ?>
                    <tr class="row-proyecto <?= ($j < count($data_proyectos)) ? '' : 'border-top' ?>">
                        <td class="headcol"><?= ($j < count($data_proyectos)) ? $data_proyectos[$j]['nombre'] : '' ?></td>
                        <?php

                        //for ($i = $semanaIni; $i <= $semanaFin; $i++):
                        foreach ($data_usuarios as $usuario):
                            //Sumatorio de horas por usuario y proyecto en una semana
                            $sql_horas = 'SELECT id_usuario, ifnull(sum(h.horas),0) as suma_horas from recogida_horas h 
                                          left join usuarios us on us.id=h.id_usuario 
                                          where h.id_proyecto=' . $data_proyectos[$j]['id'] . ' and h.year='.date('Y').' and h.numSemana=' . $semana_activa . ' group by h.id_usuario';

                            //echo $sql_horas.'<br>';
                            $q_horas = $pdo->prepare($sql_horas);
                            $q_horas->execute(array());
                            $data_horas = $q_horas->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);


                            if (!empty($data_horas[$usuario['id']])) {
                                $horas_user = $data_horas[$usuario['id']][0];
                            } else {
                                $horas_user = 0;
                            }
                            ?>
                            <td id="<?= $data_proyectos[$j]['id'].':'.$usuario['id'].':'.$semana_activa ?>" class="text-center"><?= $horas_user ?></td>
                            <?php
                        endforeach;
                        //endfor;
                        ?>
                    </tr>
                    <?php
                endfor;
                ?>
                <tr class="row-totales">
                    <td class="headcol">Total</td>
                    <?php

                    //for ($i = $semanaIni; $i <= $semanaFin; $i++):
                    foreach ($data_usuarios as $usuario):
                        //Sumatorio de horas por usuario en una semana
                        $sql_horas = 'SELECT h.id_usuario, ifnull(sum(h.horas),0) as suma_horas from recogida_horas h 
                                          left join usuarios us on us.id=h.id_usuario 
                                          where h.numSemana=' . $semana_activa . ' and h.year='.date('Y').' group by h.id_usuario';
                        $q_horas = $pdo->prepare($sql_horas);
                        $q_horas->execute(array());
                        $data_horas = $q_horas->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

                        if (!empty($data_horas[$usuario['id']])) {
                            $horas_user = $data_horas[$usuario['id']][0];
                        } else {
                            $horas_user = 0;
                        }
                        ?>
                        <td class="text-center"><?= $horas_user ?></td>
                        <?php
                    endforeach;
                    //endfor;
                    ?>
                </tr>
                </tbody>
            </table>
        <?php
        endif;*/
        ?>

    </div>
    <br><br>
<?php
if(isset($_GET['save'])) {
    include 'functions.php';
    updateCostesCron();
}
Database::disconnect();
include 'lib/footer.php';
?>