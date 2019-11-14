<?php
require_once('header.php');
?>

<div class="page-header coeficientes">
    <a class="logo" href="index.php">
        <h3>Alta cliente</h3>
    </a>
</div>

<?php

include 'lib/database.php';
?>

    <div class="row alta-empresa">
        <div class="alert alert-success" style="display: none;">Nuevo cliente <span class="clienteok text-bold" style="font-weight: bold"></span> dado de alta con éxito.</div>
        <div class="alert alert-danger" style="display: none;">Se ha producido algun error al dar de alta el cliente <span class="clienteok text-bold" style="font-weight: bold"></span>.</div>
        <form class="" id="formentidad" name="formentidad" method="post">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nombre">Nombre cliente</label>
                    <input type="text" class="form-control input-md" id="nombre" name="nombre" placeholder="Nombre" required="">
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
