<?php
require_once('header.php');
?>

    <div class="page-header coeficientes">
        <a class="logo" href="index.php">
            <h3>Editar datos cliente</h3>
        </a>
    </div>

<?php

include 'lib/database.php';
?>

    <div class="row edit-empresa">
        <div class="alert alert-success" style="display: none;">Cliente <span class="clienteok text-bold" style="font-weight: bold"></span> editado con éxito.</div>
        <div class="alert alert-danger" style="display: none;">Se ha producido algun error al actualizar los datos del cliente <span class="clienteok text-bold" style="font-weight: bold"></span>.</div>
        <form class="" id="formentidad" name="formentidad" method="post">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nombre">Nombre cliente (escribir para autocompletar)</label>
                    <input type="text" autocomplete="off" id="nombre" name="nombre" placeholder="Nombre" class="form-control input-md">
                    <input type="hidden" id="did_empresa" name="did_empresa" class="form-control input-md"">
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección Fiscal</label>
                    <textarea class="form-control input-md" id="direccion" name="direccion" placeholder="Dirección"></textarea>
                </div>
                <div class="form-group">
                    <label for="cp">CP</label>
                    <input type="text" class="form-control input-md" id="cp" name="cp" placeholder="00000" value="">
                </div>
                <div class="form-group">
                    <label for="cif">CIF</label>
                    <input type="text" class="form-control input-md" id="cif" name="cif" placeholder="CIF" value="">
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
require_once('footer.php');
?>
