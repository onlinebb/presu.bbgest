<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 1/04/14
 * Time: 9:50
 */

require_once('header.php');

require_once('lib/database.php');
$id = null;
$load = true;

if (!empty($_POST['id'])) {
    $id = $_POST['id'];
}
else if (!empty($_GET['id'])) {
    $id = $_GET['id'];
}

if(!empty($_GET['wiz'])) {
    $load = false;
}

if (null == $id) {
    header("Location: index.php");
} else {

    if($load) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM presupuesto where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        Database::disconnect();
    }
}
?>
    <div class="page-header">
        <a class="logo" href="index.php">
            <h3>Editar presupuesto</h3>
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 text-left">
            <form id="wizard-prev" class="form-inline" method="post" action="wizard.php">
                <button type="submit" class="btn btn-primary btn-lg">
                    <span class="glyphicon glyphicon-arrow-left"></span> Anterior
                </button>

                <input type="hidden" id="id" name="id" value='<?= $id ?>'>
                <input type="hidden" id="referencia" name="referencia" value='<?= $_POST['referencia'] ?>'>
                <input type="hidden" id="fechapresu" name="fechapresu" value='<?= $_POST['fechapresu'] ?>'>
                <input type="hidden" id="empresa" name="empresa" value='<?= $_POST['empresa'] ?>'>
                <input type="hidden" id="ref_empresa" name="ref_empresa" value='<?= $_POST['ref_empresa'] ?>'>
                <input type="hidden" id="cliente" name="cliente" value='<?= $_POST['cliente'] ?>'>
                <input type="hidden" id="direccion" name="direccion" value='<?= $_POST['direccion'] ?>'>
                <input type="hidden" id="cif" name="cif" value='<?= $_POST['cif'] ?>'>
                <input type="hidden" id="cp" name="cp" value='<?= $_POST['cp'] ?>'>
                <input type="hidden" id="contactocl" name="contactocl" value='<?= $_POST['contactocl'] ?>'>
                <input type="hidden" id="nproyecto" name="nproyecto" value='<?= $_POST['nproyecto'] ?>'>
                <input type="hidden" id="total" name="total" value='<?= $_POST['total'] ?>'>
            </form>
        </div>
    </div>

    <br>

    <div class="row">
        <div role="complementary" class="col-md-2">
            <nav class="hidden-print hidden-xs hidden-sm affix">
                <button type="button" class="btn btn-primary btn-sm" id="preview-presu-link">
                    <span class="glyphicon glyphicon-eye-open"></span> Previsualizar
                </button>
                <br><br>
                <button type="button" class="btn btn-primary btn-sm save-presu" id="save-presu-link">
                    <span class="glyphicon glyphicon-floppy-disk"></span> Guardar
                </button>
                <br><br>
                <button type="button" class="btn btn-primary btn-sm add-honorarios" title="Honorarios" data-toggle="modal" data-target="#honorarios-modal">
                    <span class="glyphicon glyphicon-euro"></span> Cálculo Honorarios
                </button>
            </nav>
        </div>
        <div class="col-md-10">
            <form id="update-presupuesto-form" class="form-horizontal" method="post" action="lib/pdf.php?preview=1" target="_blank">
                <input type="hidden" name="id_presupuesto" id="id_presupuesto" value="<?php echo $id; ?>">
                <fieldset>
                    <!-- Ref. presupuesto -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="ref">Presupuesto</label>

                        <div class="col-md-6">
                            <input disabled id="ref" name="ref" class="form-control input-sm" required="" type="text" value="<?= ($load)? $data['ref']:$_POST['referencia'] ?>">
                        </div>
                    </div>

                    <!-- Fecha -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="fecha">Fecha</label>

                        <div class="col-md-6">
                            <input id="fecha" name="fecha" placeholder="dd-mm-yyyy" class="form-control input-sm date" required="" type="text" value="<?php echo date('d-m-Y', strtotime($data['fecha'])); ?>">
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="cliente">Cliente</label>

                        <div class="col-md-6 inputs-cliente">
                            <input id="id-empresa-orig" name="id-empresa-orig" class="form-control input-sm" type="hidden" value="<?= ($load)? $data['id_empresa']:$_POST['empresa'] ?>">
                            <input id="id-empresa" name="id-empresa" class="form-control input-sm" type="hidden" value="<?= ($load)? $data['id_empresa']:$_POST['empresa'] ?>">
                            <input id="ref-empresa" name="ref-empresa" class="form-control input-sm" type="hidden" value="<?= ($load)? $data['ref_cliente']:$_POST['ref_empresa'] ?>">
                            <input id="nombre-cliente" name="nombre-cliente" class="form-control input-sm" type="text" placeholder="Nombre" value="<?= ($load)? $data['nombre_cliente']:$_POST['cliente'] ?>">
                            <textarea class="form-control input-sm" id="direccion-cliente" name="direccion-cliente" placeholder="Dirección"><?= ($load)? $data['direccion_cliente']:$_POST['direccion'] ?></textarea>
                            <input id="cp-cliente" name="cp-cliente" class="form-control input-sm" type="text" placeholder="CP" value="<?= ($load)? $data['cp_cliente']:$_POST['cp'] ?>">
                            <input id="cif-cliente" name="cif-cliente" class="form-control input-sm" type="text" placeholder="CIF" value="<?= ($load)? $data['cif_cliente']:$_POST['cif'] ?>">
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="contacto">Contacto</label>

                        <div class="col-md-6">
                            <input id="contacto" name="contacto" placeholder="Nombre contacto" class="form-control input-sm"
                                   type="text" value="<?= ($load)? $data['contacto_cliente']:$_POST['contactocl'] ?>">
                        </div>
                    </div>

                    <!-- Proyecto -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="proyecto">Proyecto</label>

                        <div class="col-md-6">
                            <input type="text" id="proyecto" name="proyecto" placeholder="Nombre proyecto" class="form-control input-sm" value="<?= ($load)? $data['nombre_proyecto']:$_POST['nproyecto'] ?>">
                            <input type="hidden" id="id_proyecto" name="id_proyecto" class="form-control input-sm"><?= $_POST['idproyecto'] ?>
                        </div>
                    </div>

                    <!-- Checkbox idioma -->
                    <div class="form-group">
                        <div class="form-group">
                            <div class=" col-md-offset-6 col-md-6">
                                <div class="checkbox">
                                    <label>
                                        <input name="export-en" id="export-en" type="checkbox" <?=$data['english']?'checked':'' ?>><b>English</b>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                </fieldset>

                <div id="suma" class="alert alert-info">
                    <b>Total guardado: </b><span id="suma-vis"><?= ($load)? number_format(str_replace('.',',',$data['suma']), 2, ',', '.'):number_format(str_replace('.',',',$_POST['total']), 2, ',', '.') ?></span> &euro; <br>
                    <input type="hidden" id="sumag" name="sumag" value='<?= ($load)? $data['suma']:$_POST['total'] ?>'>
                    <b>Suma precios: </b><span class="valor">0</span> &euro;
                </div>

                <button type="button" class="btn btn-default add-honorarios" title="Honorarios" data-toggle="modal" data-target="#honorarios-modal">
                    <span class="glyphicon glyphicon-euro"></span> Cálculo Honorarios
                </button>

                <span class="honorarios-wrapper"><?php require_once('honorarios-modal.php'); ?></span>
                <br><br>

                <fieldset id="concepto_group_0" class="concepto hide" data-index="0">

                    <legend>Concepto 0</legend>

                    <!-- Concepto -->
                    <div class="wrap-concepto" data-tipo="concepto">
                        <div class="form-group nombre-concepto">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                    <input id="concepto_0" name="concepto_0" placeholder="Concepto" class="form-control input-sm" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                    <input id="concepto_0_precio" name="concepto_0_precio" placeholder="Precio" class="form-control input-sm precio" type="text" data-sumar="1">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Concepto subtitulo -->
                    <div class="wrap-concepto" data-tipo="concepto_subtitulo">
                        <div class="form-group nombre-concepto-subtitulo">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                    <input id="concepto_sub_0" name="concepto_sub_0" placeholder="Concepto subtítulo" class="form-control input-sm" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                    <input id="concepto_sub_0_precio" name="concepto_sub_0_precio" placeholder="Precio" class="form-control input-sm precio" type="text" data-sumar="1">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Título 1 -->
                    <div class="wrap-concepto" data-tipo="titulo1">
                        <div class="form-group titulo-1">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                    <input id="tit1_0" name="tit1_0" class="form-control input-sm" placeholder="Título 1" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                    <input id="tit1_0_precio" name="tit1_0_precio" placeholder="Precio" class="form-control input-sm precio" type="text" data-sumar="1">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Título 2 -->
                    <div class="wrap-concepto" data-tipo="titulo2">
                        <div class="form-group titulo-2">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                    <input id="tit2_0" name="tit2_0" placeholder="Título 2" class="form-control input-sm" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                    <input id="tit2_0_precio" name="tit2_0_precio" placeholder="Precio" class="form-control input-sm precio" type="text" data-sumar="1">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Título 3 -->
                    <div class="wrap-concepto" data-tipo="titulo3">
                        <div class="form-group titulo-3">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                    <input id="tit3_0" name="tit3_0" placeholder="Título 3" class="form-control input-sm" type="text">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                    <input id="tit3_0_precio" name="tit3_0_precio" placeholder="Precio" class="form-control input-sm precio" type="text" data-sumar="1">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Texto -->
                    <div class="wrap-concepto" data-tipo="texto">
                        <div class="form-group texto-concepto">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                    <textarea class="form-control input-sm" id="texto_0" name="texto_0" placeholder="Texto"></textarea>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                    <input id="texto_0_precio" name="texto_0_precio" placeholder="Precio" class="form-control input-sm precio" type="text" data-sumar="1">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-default btn-sm copy-concepto" title="Duplicar bloque">
                        <span class="glyphicon icons-fontawesome-webfont"></span>
                    </button>

                    <button type="button" class="btn btn-default btn-sm del-concepto" title="Eliminar">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>

                    <a class="btn btn-default btn-sm move-concepto" title="Mover">
                        <span class="glyphicon glyphicon-move"></span>
                    </a>

                </fieldset>

                <?php

                if($load):

                $sql = "SELECT * FROM concepto WHERE id_presupuesto = ? ORDER BY id_concepto";
                $q = $pdo->prepare($sql);
                $q->execute(array($id));
                $count = 1;
                for ($i = 0; $row = $q->fetch(); $i++) {
                    ?>


                    <fieldset id="concepto_group_<?php echo $count ?>" class="concepto" data-index="<?php echo $count ?>">

                        <legend>Concepto <?php echo $count ?></legend>

                        <!-- Concepto -->
                        <?php if (!empty($row['concepto']) || (!empty($row['precio_concepto']) && $row['precio_concepto'] != 0)): ?>

                            <div class="wrap-concepto" data-tipo="concepto">
                                <div class="form-group nombre-concepto">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="concepto_<?php echo $count ?>" name="concepto_<?php echo $count ?>"
                                                   placeholder="Concepto" class="form-control input-sm" type="text" value='<?php echo $row['concepto'] ?>'>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="concepto_<?php echo $count ?>_precio" name="concepto_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($row['precio_concepto'])) {
                                                       echo $row['precio_concepto'];
                                                   } ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                        <!-- Concepto subtitulo -->
                        <?php if (!empty($row['concepto_subtitulo']) || (!empty($row['precio_concepto_subtitulo']) && $row['precio_concepto_subtitulo'] != 0)): ?>

                            <div class="wrap-concepto" data-tipo="concepto_subtitulo">
                                <div class="form-group nombre-concepto-subtitulo">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="concepto_sub_<?php echo $count ?>" name="concepto_sub_<?php echo $count ?>" placeholder="Concepto subtítulo" class="form-control input-sm" type="text"
                                                   value='<?php echo $row['concepto_subtitulo'] ?>'>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="concepto_sub_<?php echo $count ?>_precio" name="concepto_sub_<?php echo $count ?>_precio" placeholder="Precio" data-sumar="1"
                                                   class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($row['precio_concepto_subtitulo'])) {
                                                       echo $row['precio_concepto_subtitulo'];
                                                   } ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                        <!-- Título 1 -->
                        <?php if (!empty($row['titulo1']) || (!empty($row['precio_titulo1']) && $row['precio_titulo1'] != 0)): ?>

                            <div class="wrap-concepto" data-tipo="titulo1">
                                <div class="form-group titulo-1">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="tit1_<?php echo $count ?>" name="tit1_<?php echo $count ?>"
                                                   class="form-control input-sm" placeholder="Título 1" type="text"
                                                   value='<?php echo $row['titulo1'] ?>'>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="tit1_<?php echo $count ?>_precio" name="tit1_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($row['precio_titulo1'])) {
                                                       echo $row['precio_titulo1'];
                                                   } ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                        <!-- Título 2 -->
                        <?php if (!empty($row['titulo2']) || (!empty($row['precio_titulo2']) && $row['precio_titulo2'] != 0)): ?>

                            <div class="wrap-concepto" data-tipo="titulo2">
                                <div class="form-group titulo-2">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="tit2_<?php echo $count ?>" name="tit2_<?php echo $count ?>"
                                                   placeholder="Título 2" class="form-control input-sm" type="text"
                                                   value='<?php echo $row['titulo2'] ?>'>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="tit2_<?php echo $count ?>_precio" name="tit2_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($row['precio_titulo2'])) {
                                                       echo $row['precio_titulo2'];
                                                   } ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                        <!-- Título 3 -->
                        <?php if (!empty($row['titulo3']) || (!empty($row['precio_titulo3']) && $row['precio_titulo3'] != 0)): ?>

                            <div class="wrap-concepto" data-tipo="titulo3">
                                <div class="form-group titulo-3">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="tit3_<?php echo $count ?>" name="tit3_<?php echo $count ?>"
                                                   placeholder="Título 3" class="form-control input-sm" type="text"
                                                   value='<?php echo $row['titulo3'] ?>'>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="tit3_<?php echo $count ?>_precio" name="tit3_<?php echo $count ?>_precio" placeholder="Precio" data-sumar="1" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($row['precio_titulo3'])) {
                                                       echo $row['precio_titulo3'];
                                                   } ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                        <!-- Texto -->
                        <?php if (!empty($row['texto']) || (!empty($row['precio_texto']) && $row['precio_texto'] != 0)): ?>

                            <div class="wrap-concepto" data-tipo="texto">
                                <div class="form-group texto-concepto">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <textarea class="form-control input-sm" id="texto_<?php echo $count ?>"
                                                      name="texto_<?php echo $count ?>"
                                                      placeholder="Texto"><?php echo $row['texto'] ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="texto_<?php echo $count ?>_precio" name="texto_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($row['precio_texto'])) {
                                                       echo $row['precio_texto'];
                                                   } ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                        <button type="button" class="btn btn-default btn-sm copy-concepto" title="Duplicar bloque">
                            <span class="glyphicon icons-fontawesome-webfont"></span>
                        </button>

                        <button type="button" class="btn btn-default btn-sm del-concepto" title="Eliminar">
                            <span class="glyphicon glyphicon-trash"></span>
                        </button>

                        <a class="btn btn-default btn-sm move-concepto" title="Mover">
                            <span class="glyphicon glyphicon-move"></span>
                        </a>

                    </fieldset>

                    <?php
                    $count++;
                }
                else:
                ?>

                <?php
                $count = 1;

                if (isset($_POST['conceptos'])) {
                    $conceptos = json_decode($_POST['conceptos'], true);

                    foreach ($conceptos as $item) {

                        ?>
                        <fieldset id="concepto_group_<?php echo $count ?>" class="concepto" data-index="<?php echo $count ?>">

                            <legend>Concepto <?php echo $count ?></legend>

                            <!-- Concepto -->
                            <?php if (!empty($item['concepto']) || (!empty($item['precio_concepto']) && $item['precio_concepto'] != 0)): ?>
                            <div class="wrap-concepto" data-tipo="concepto">
                                <div class="form-group nombre-concepto">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span
                                                    class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="concepto_<?php echo $count ?>" name="concepto_<?php echo $count ?>"
                                                   placeholder="Concepto" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['concepto'])): echo $item['concepto']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="concepto_<?php echo $count ?>_precio"
                                                   name="concepto_<?php echo $count ?>_precio" placeholder="Precio" data-sumar="1" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_concepto'])): echo $item['precio_concepto']; endif; ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Concepto subtitulo -->
                            <?php if (!empty($item['concepto_subtitulo']) || (!empty($item['precio_concepto_subtitulo']) && $item['precio_concepto_subtitulo'] != 0)): ?>
                            <div class="wrap-concepto" data-tipo="concepto_subtitulo">
                                <div class="form-group nombre-concepto-subtitulo">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="concepto_sub_<?php echo $count ?>" name="concepto_sub_<?php echo $count ?>"
                                                   placeholder="Concepto subtítulo" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['concepto_subtitulo'])): echo $item['concepto_subtitulo']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="concepto_sub_<?php echo $count ?>_precio" data-sumar="1" name="concepto_sub_<?php echo $count ?>_precio" placeholder="Precio"
                                                   class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_concepto_subtitulo'])): echo $item['precio_concepto_subtitulo']; endif; ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Título 1 -->
                            <?php if (!empty($item['titulo1']) || (!empty($item['precio_titulo1']) && $item['precio_titulo1'] != 0)): ?>
                            <div class="wrap-concepto" data-tipo="titulo1">
                                <div class="form-group titulo-1">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="tit1_<?php echo $count ?>" name="tit1_<?php echo $count ?>"
                                                   class="form-control input-sm" placeholder="Título 1" type="text"
                                                   value="<?php if (isset($item['titulo1'])): echo $item['titulo1']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="tit1_<?php echo $count ?>_precio" name="tit1_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_titulo1'])): echo $item['precio_titulo1']; endif; ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Título 2 -->
                            <?php if (!empty($item['titulo2']) || (!empty($item['precio_titulo2']) && $item['precio_titulo2'] != 0)): ?>
                            <div class="wrap-concepto" data-tipo="titulo2">
                                <div class="form-group titulo-2">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="tit2_<?php echo $count ?>" name="tit2_<?php echo $count ?>"
                                                   placeholder="Título 2" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['titulo2'])): echo $item['titulo2']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="tit2_<?php echo $count ?>_precio" name="tit2_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_titulo2'])): echo $item['precio_titulo2']; endif; ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Título 3 -->
                            <?php if (!empty($item['titulo3']) || (!empty($item['precio_titulo3']) && $item['precio_titulo3'] != 0)): ?>
                            <div class="wrap-concepto" data-tipo="titulo3">
                                <div class="form-group titulo-3">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="tit3_<?php echo $count ?>" name="tit3_<?php echo $count ?>"
                                                   placeholder="Título 3" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['titulo3'])): echo $item['titulo3']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="tit3_<?php echo $count ?>_precio" name="tit3_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_titulo3'])): echo $item['precio_titulo3']; endif; ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Texto -->
                            <?php if (!empty($item['texto']) || (!empty($item['precio_texto']) && $item['precio_texto'] != 0)): ?>
                            <div class="wrap-concepto" data-tipo="texto">
                                <div class="form-group texto-concepto">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <textarea class="form-control input-sm" id="texto_<?php echo $count ?>"
                                                      name="texto_<?php echo $count ?>" placeholder="Texto"><?php if (isset($item['texto'])): echo $item['texto']; endif; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="glyphicon glyphicon-ok-circle"></span></a>
                                            <input id="texto_<?php echo $count ?>_precio" name="texto_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_texto'])): echo $item['precio_texto']; endif; ?>">
                                            <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <button type="button" class="btn btn-default btn-sm copy-concepto" title="Duplicar bloque">
                                <span class="glyphicon icons-fontawesome-webfont"></span>
                            </button>

                            <button type="button" class="btn btn-default btn-sm del-concepto" title="Eliminar">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                            <a class="btn btn-default btn-sm move-concepto" title="Mover">
                                <span class="glyphicon glyphicon-move"></span>
                            </a>

                        </fieldset>
                        <?php
                        $count++;
                    }
                }
                endif;
                ?>

                <button type="button" class="btn btn-default center-block add-concepto" title="Nuevo">
                    <span class="glyphicon glyphicon-plus-sign"></span>
                </button>

                <div class="buttons text-center">
                    <button type="submit" class="btn btn-primary btn-lg" id="preview-presu">
                        <span class="glyphicon glyphicon-eye-open"></span> Previsualizar
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg save-presu" id="save-presu">
                        <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Presupuesto
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div><!-- /container -->
    </div><!-- /main-wrapper -->
<?php

require_once('footer.php');