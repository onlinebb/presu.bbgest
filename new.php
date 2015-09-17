<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 1/04/14
 * Time: 9:50
 */

require_once('header.php');
?>
    <div class="page-header">
        <a class="logo" href="index.php">
            <h3>Nuevo presupuesto</h3>
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 text-left">
            <form id="wizard-prev" class="form-inline" role="form" method="post" action="wizard.php">
                <button type="submit" class="btn btn-primary btn-lg">
                    <span class="glyphicon glyphicon-arrow-left"></span> Anterior
                </button>

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
            </form>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col-md-10">
            <form id="presupuesto-form" class="form-horizontal" method="post" action="lib/pdf.php?preview=1" target="_blank">
                <fieldset>
                    <!-- Ref. presupuesto -->
                    <div class="form-group hidden">
                      <label class="col-md-6 control-label" for="ref">Presupuesto</label>
                      <div class="col-md-6">
                        <input id="ref" name="ref" placeholder="PR14001MER" class="form-control input-sm" required="" type="hidden">
                      </div>
                    </div>

                    <!-- Fecha -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="fecha">Fecha</label>

                        <div class="col-md-6">
                            <input id="fecha" name="fecha" placeholder="dd-mm-yyyy" class="form-control input-sm date" type="text" value="<?= $_POST['fechapresu'] ?>">
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="cliente">Cliente</label>

                        <div class="col-md-6 inputs-cliente">
                            <input id="id-empresa" name="id-empresa" class="form-control input-sm" type="hidden" value="<?= $_POST['empresa'] ?>">
                            <input id="ref-empresa" name="ref-empresa" class="form-control input-sm" type="hidden" value="<?= $_POST['ref_empresa'] ?>">
                            <input id="nombre-cliente" name="nombre-cliente" class="form-control input-sm" type="text" placeholder="Nombre" autocomplete="off" value="<?= $_POST['cliente'] ?>">
                            <textarea class="form-control input-sm" id="direccion-cliente" name="direccion-cliente" placeholder="Dirección"><?= $_POST['direccion'] ?></textarea>
                            <input id="cp-cliente" name="cp-cliente" class="form-control input-sm" type="text" placeholder="CP" value="<?= $_POST['cp'] ?>">
                            <input id="cif-cliente" name="cif-cliente" class="form-control input-sm" type="text" placeholder="CIF" value="<?= $_POST['cif'] ?>">
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="contacto">Contacto</label>

                        <div class="col-md-6">
                            <input id="contacto" name="contacto" placeholder="Nombre contacto" class="form-control input-sm" type="text" value="<?= $_POST['contactocl'] ?>">
                        </div>
                    </div>

                    <!-- Proyecto -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="proyecto">Proyecto</label>

                        <div class="col-md-6">
                            <textarea id="proyecto" name="proyecto" placeholder="Nombre proyecto" class="form-control input-sm"><?= $_POST['nproyecto'] ?></textarea>
                        </div>
                    </div>

                </fieldset>

                <div id="suma" class="alert alert-info">
                    <b>Suma precios: </b><span class="valor">0</span> &euro;
                </div>

                <button type="button" class="btn btn-default add-honorarios" title="Honorarios" data-toggle="modal" data-target="#honorarios-modal">
                    <span class="glyphicon glyphicon-euro"></span> Cálculo Honorarios
                </button>
                <?php require_once('honorarios-modal.php'); ?>
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
                $count = 1;

                if (isset($_POST['conceptos'])) {
                    $conceptos = json_decode($_POST['conceptos'], true);

                    foreach ($conceptos as $item) {

                        ?>
                        <fieldset id="concepto_group_<?php echo $count ?>" class="concepto" data-index="<?php echo $count ?>">

                            <legend>Concepto <?php echo $count ?></legend>

                            <!-- Concepto -->
                            <div class="wrap-concepto" data-tipo="concepto">
                                <div class="form-group nombre-concepto">
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-addon remove"><span class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="concepto_<?php echo $count ?>" name="concepto_<?php echo $count ?>"
                                                   placeholder="Concepto" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['concepto'])): echo $item['concepto']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="<?= ($item['precio_concepto_sumar'])?'glyphicon glyphicon-ok-circle':'glyphicon glyphicon-remove-circle' ?>"></span></a>
                                            <input id="concepto_<?php echo $count ?>_precio" data-sumar="<?= $item['precio_concepto_sumar'];?>" name="concepto_<?php echo $count ?>_precio" placeholder="Precio"
                                                   class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_concepto'])): echo $item['precio_concepto']; endif; ?>">
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
                                            <span class="input-group-addon remove"><span
                                                    class="glyphicon glyphicon-minus-sign"></span></span>
                                            <input id="concepto_sub_<?php echo $count ?>" name="concepto_sub_<?php echo $count ?>"
                                                   placeholder="Concepto subtítulo" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['concepto_subtitulo'])): echo $item['concepto_subtitulo']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="<?= ($item['precio_concepto_subtitulo_sumar'])?'glyphicon glyphicon-ok-circle':'glyphicon glyphicon-remove-circle' ?>"></span></a>
                                            <input id="concepto_sub_<?php echo $count ?>_precio"
                                                   name="concepto_sub_<?php echo $count ?>_precio" placeholder="Precio" data-sumar="<?= $item['precio_concepto_subtitulo_sumar'];?>" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_concepto_subtitulo'])): echo $item['precio_concepto_subtitulo']; endif; ?>">
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
                                            <input id="tit1_<?php echo $count ?>" name="tit1_<?php echo $count ?>"
                                                   class="form-control input-sm" placeholder="Título 1" type="text"
                                                   value="<?php if (isset($item['titulo1'])): echo $item['titulo1']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="<?= ($item['precio_titulo1_sumar'])?'glyphicon glyphicon-ok-circle':'glyphicon glyphicon-remove-circle' ?>"></span></a>
                                            <input id="tit1_<?php echo $count ?>_precio" name="tit1_<?php echo $count ?>_precio" data-sumar="<?= $item['precio_titulo1_sumar'];?>" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_titulo1'])): echo $item['precio_titulo1']; endif; ?>">
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
                                            <input id="tit2_<?php echo $count ?>" name="tit2_<?php echo $count ?>"
                                                   placeholder="Título 2" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['titulo2'])): echo $item['titulo2']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="<?= ($item['precio_titulo2_sumar'])?'glyphicon glyphicon-ok-circle':'glyphicon glyphicon-remove-circle' ?>"></span></a>
                                            <input id="tit2_<?php echo $count ?>_precio" name="tit2_<?php echo $count ?>_precio" data-sumar="<?= $item['precio_titulo2_sumar'];?>" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_titulo2'])): echo $item['precio_titulo2']; endif; ?>">
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
                                            <input id="tit3_<?php echo $count ?>" name="tit3_<?php echo $count ?>"
                                                   placeholder="Título 3" class="form-control input-sm" type="text"
                                                   value="<?php if (isset($item['titulo3'])): echo $item['titulo3']; endif; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="<?= ($item['precio_titulo3_sumar'])?'glyphicon glyphicon-ok-circle':'glyphicon glyphicon-remove-circle' ?>"></span></a>
                                            <input id="tit3_<?php echo $count ?>_precio" name="tit3_<?php echo $count ?>_precio" data-sumar="<?= $item['precio_titulo3_sumar'];?>" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_titulo3'])): echo $item['precio_titulo3']; endif; ?>">
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
                                            <textarea class="form-control input-sm" id="texto_<?php echo $count ?>"
                                                      name="texto_<?php echo $count ?>" placeholder="Texto"><?php if (isset($item['texto'])): echo $item['texto']; endif; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <a class="input-group-addon toggle-suma"><span class="<?= ($item['precio_texto_sumar'])?'glyphicon glyphicon-ok-circle':'glyphicon glyphicon-remove-circle' ?>"></span></a>
                                            <input id="texto_<?php echo $count ?>_precio" name="texto_<?php echo $count ?>_precio" data-sumar="<?= $item['precio_texto_sumar'];?>" placeholder="Precio" class="form-control input-sm precio" type="text"
                                                   value="<?php if (!empty($item['precio_texto'])): echo $item['precio_texto']; endif; ?>">
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
                        $count++;
                    }
                }
                ?>

                <button type="button" class="btn btn-default center-block add-concepto" title="Nuevo">
                    <span class="glyphicon glyphicon-plus-sign"></span>
                </button>

                <div class="text-center buttons">

                    <button type="submit" class="btn btn-primary btn-lg" id="preview-presu">
                        <span class="glyphicon glyphicon-eye-open"></span> Previsualizar
                    </button>

                    <button type="submit" class="btn btn-primary btn-lg save-presu">
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