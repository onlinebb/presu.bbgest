<?php
require_once('lib/database.php');
$pdo2 = Database::connect('stack_bbgest');
$q_honorarios = $pdo2->prepare("select * from precios_honorarios where id_cliente = ?");
if(empty($_GET['id_cliente'])){
    $id_cliente = ($load)? $data_honorarios['id_empresa']:$_POST['empresa'];
}
else {
    $id_cliente = $_GET['id_cliente'];
}
$q_honorarios->execute(array($id_cliente));
$data_honorarios = $q_honorarios->fetchAll(PDO::FETCH_ASSOC);
/*echo '<pre>';
print_r($data);
echo '</pre>';*/
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
        </div>
        <div class="modal-body">

            <!-- Modal body-->
            <table class="table table-striped table-curved">
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Hr Rate</th>
                        <th>Horas</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="datos">
                        <td class="cargo">Client Services Director</td>
                        <td><input data-idperfil="1" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[0])?$data_honorarios[0]["precio"]:190.48?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Account Executive</td>
                        <td><input data-idperfil="2" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[1])?$data_honorarios[1]["precio"]:95.24?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Jr. Account Executive</td>
                        <td><input data-idperfil="3" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[2])?$data_honorarios[2]["precio"]:59.52?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Medical Advisor</td>
                        <td><input data-idperfil="4" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[3])?$data_honorarios[3]["precio"]:190.48?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Medical writer</td>
                        <td><input data-idperfil="5" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[4])?$data_honorarios[4]["precio"]:119.05?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Executive Creative Director</td>
                        <td><input data-idperfil="6" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[5])?$data_honorarios[5]["precio"]:190.48?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Creative Director</td>
                        <td><input data-idperfil="7" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[6])?$data_honorarios[6]["precio"]:119.05?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Senior Copywriter</td>
                        <td><input data-idperfil="8" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[7])?$data_honorarios[7]["precio"]:95.24?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Junior Copywriter</td>
                        <td><input data-idperfil="9" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[8])?$data_honorarios[8]["precio"]:59.52?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Senior art director</td>
                        <td><input data-idperfil="10" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[9])?$data_honorarios[9]["precio"]:119.05?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Junior art director</td>
                        <td><input data-idperfil="11" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[10])?$data_honorarios[10]["precio"]:64.29?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Designer</td>
                        <td><input data-idperfil="12" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[11])?$data_honorarios[11]["precio"]:59.52?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Head of audiovisuals</td>
                        <td><input data-idperfil="13" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[12])?$data_honorarios[12]["precio"]:119.05?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Senior audiovisual specialist</td>
                        <td><input data-idperfil="14" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[13])?$data_honorarios[13]["precio"]:119.05?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Junior audiovisual specialist</td>
                        <td><input data-idperfil="15" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[14])?$data_honorarios[14]["precio"]:61.90?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Head of engineering/digital</td>
                        <td><input data-idperfil="16" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[15])?$data_honorarios[15]["precio"]:95.24?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Senior engineer</td>
                        <td><input data-idperfil="17" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[16])?$data_honorarios[16]["precio"]:71.43?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">Junior engineer</td>
                        <td><input data-idperfil="18" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[17])?$data_honorarios[17]["precio"]:42.86?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">PR Article writer</td>
                        <td><input data-idperfil="19" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[18])?$data_honorarios[18]["precio"]:71.43?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">PR Senior Community manager</td>
                        <td><input data-idperfil="20" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[19])?$data_honorarios[19]["precio"]:61.90?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr class="datos">
                        <td class="cargo">PR Junior Community manager</td>
                        <td><input data-idperfil="21" class="form-control rate" type="text" value="<?= ($data_honorarios && $data_honorarios[20])?$data_honorarios[20]["precio"]:52.38?>"></td>
                        <td><input class="form-control horas" type="text" value="0"></td>
                        <td><input class="form-control total" type="text" value="0"></td>
                    </tr>
                    <tr>
                        <td><strong>TOTAL</strong></td>
                        <td colspan="3"><strong><input class="form-control total-honorarios" type="text" value="0"></strong></td>
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