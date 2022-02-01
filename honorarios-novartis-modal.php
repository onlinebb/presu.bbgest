<?php
require_once('lib/database.php');
$pdo2 = Database::connect('stack_bbgest');
$q_honorarios = $pdo2->prepare("select * from honorarios_novartis where tier is not null order by id ASC");
$q_honorarios_materials = $pdo2->prepare("select * from honorarios_novartis where tier is null order by id ASC");

if(empty($_GET['id_cliente'])){
    $id_cliente = ($load)? $data['id_empresa']:$_POST['empresa'];
}
else {
    $id_cliente = $_GET['id_cliente'];
}
$q_honorarios->execute(array($id_cliente));
$data_honorarios = $q_honorarios->fetchAll(PDO::FETCH_ASSOC);

$q_honorarios_materials->execute(array($id_cliente));
$data_honorarios_materials = $q_honorarios_materials->fetchAll(PDO::FETCH_ASSOC);
//echo '<pre>';
//print_r($data_honorarios);
//echo '</pre>';
Database::disconnect();
?>
<!-- Modal -->
<div class="modal fade" id="honorarios-novartis-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Calcular honorarios Novartis</h4><br>
            <!--<button id="save-honorarios" data-idcliente="<?=$id_cliente?>" type="button" class="btn btn-sm btn-primary" data-dismiss="modal"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar precios</button>-->
            <div id="alerta-no-cliente" class="alert alert-warning hidden" role="alert">Es necesario asignar algún cliente al presu antes de guardar honorarios</div>
        </div>
        <div class="modal-body">
            <h5>TOTAL: <span id="total-novartis">0</span>&euro;</h5>
            <div class="row">
                <div class="col-md-6 text-left">
                    <h5>Tier hourly rates</h5>
                </div>
                <div class="col-md-6 text-right">
                    <h5>Total: <span id="total-tier">0</span>&euro;</h5>
                </div>
            </div>
            <table class="table table-striped table-curved datos-tier">
            <!-- Modal body-->
                    <?php
                    $current_category = "";
                    foreach ($data_honorarios as $honorario):

                        if($current_category == "" || $current_category != $honorario['categoria']):
                            $current_category = $honorario['categoria'];
                    ?>
                            <tr><th colspan="5"><?=$current_category?></th></tr>
                            <tr>
                                <th>Tier</th>
                                <th>Title</th>
                                <th>Rate</th>
                                <th>Horas</th>
                                <th>Total</th>
                            </tr>
                    <?php
                        endif;
                    ?>
                    <tr class="datos">
                        <td><?=$honorario['tier']?></td>
                        <td class="cargo"><?=$honorario['perfil']?></td>
                        <td><input data-idperfil="<?=$honorario['id']?>" class="form-control rate" type="text" value="<?=$honorario['precio']?>"></td>
                        <td><input data-descripcion="hours" class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
            </table>

            <div class="row">
                <div class="col-md-6 text-left">
                    <h5>Fixed deliverable rates</h5>
                </div>
                <div class="col-md-6 text-right">
                    <h5>Total: <span id="total-fixed">0</span>&euro;</h5>
                </div>
            </div>
            <table class="table table-striped table-curved datos-fixed">
                <!-- Modal body-->
                <?php
                $current_category = "";
                foreach ($data_honorarios_materials as $honorario):

                    if($current_category == "" || $current_category != $honorario['categoria']):
                        $current_category = $honorario['categoria'];
                        ?>
                        <tr><th colspan="5"><?=$current_category?></th></tr>
                        <tr>
                            <th>Material</th>
                            <th>Desc.</th>
                            <th>Rate</th>
                            <th>Nº de</th>
                            <th>Total</th>
                        </tr>
                    <?php
                    endif;
                    ?>
                    <tr class="datos">
                        <td class="cargo"><?=$honorario['perfil']?></td>
                        <td><?=$honorario['descripcion']?></td>
                        <td><input style="min-width: 69px;" data-idperfil="<?=$honorario['id']?>" class="form-control rate" type="text" value="<?=$honorario['precio']?>"></td>
                        <td><input data-descripcion="<?=$honorario['descripcion']?>" class="form-control horas" type="text" value="0"></td>
                        <td><input style="min-width: 69px;" class="form-control total" type="text" value="0"></td>
                    </tr>
                <?php
                endforeach;
                ?>
            </table>
            <!-- /modal body -->

        </div>
        <div class="modal-footer">
            <button id="export-honorarios-novartis" type="submit" class="btn btn-primary" data-id="" data-dismiss="modal">Insertar</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
