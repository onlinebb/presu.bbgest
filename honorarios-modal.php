<?php
require_once('lib/database.php');
$pdo2 = Database::connect('stack_bbgest');
$q_honorarios = $pdo2->prepare("select * from honorarios order by id ASC");
if(empty($_GET['id_cliente'])){
    $id_cliente = ($load)? $data['id_empresa']:$_POST['empresa'];
}
else {
    $id_cliente = $_GET['id_cliente'];
}
$q_honorarios->execute(array($id_cliente));
$data_honorarios = $q_honorarios->fetchAll(PDO::FETCH_ASSOC);
//echo '<pre>';
//print_r($data_honorarios);
//echo '</pre>';
Database::disconnect();
?>
<!-- Modal -->
<div class="modal fade" id="honorarios-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Calcular honorarios</h4><br>
            <!--<button id="save-honorarios" data-idcliente="<?=$id_cliente?>" type="button" class="btn btn-sm btn-primary" data-dismiss="modal"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar precios</button>-->
            <div id="alerta-no-cliente" class="alert alert-warning hidden" role="alert">Es necesario asignar algún cliente al presu antes de guardar honorarios</div>
        </div>
        <div class="modal-body">

            <!-- Modal body-->
            <table class="table table-striped table-curved">
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Cost per Hr</th>
                        <th>Hr to client</th>
                        <th>Horas</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data_honorarios as $honorario):
                    ?>
                    <tr class="datos">
                        <td class="cargo"><?=$honorario['perfil']?></td>
                        <td><input data-idperfil="<?=$honorario['id']?>" class="form-control cost" type="text" value="<?=$honorario['coste']?>"></td>
                        <td><input data-idperfil="<?=$honorario['id']?>" class="form-control rate" type="text" value="<?=$honorario['precio']?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                    <tr>
                        <td><strong>TOTAL (cost)</strong></td>
                        <td colspan="4"><strong><input class="form-control total-honorarios-coste" type="text" value="0"></strong></td>
                    </tr>
                    <tr>
                        <td><strong>TOTAL (client)</strong></td>
                        <td colspan="4"><strong><input class="form-control total-honorarios" type="text" value="0"></strong></td>
                    </tr>
                    <tr>
                        <td><strong>Coef</strong></td>
                        <td colspan="4"><strong><input class="form-control total-honorarios-coef" type="text" value="0"></strong></td>
                    </tr>
                </tbody>
            </table>
            <!-- /modal body -->

        </div>
        <div class="modal-footer">
            <button id="export-honorarios" type="submit" class="btn btn-primary" data-id="" data-dismiss="modal">Insertar</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
