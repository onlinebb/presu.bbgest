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

if (!empty($_POST['id'])) {
    $id = $_POST['id'];
}
else if (!empty($_GET['id'])) {
    $id = $_GET['id'];
}

if (null == $id) {
    header("Location: index.php");
} else {
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM factura left join presupuesto on factura.presupuesto_asoc = presupuesto.ref where factura.id = ? ";
    $q = $pdo->prepare($sql);
    $q->execute(array($id));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    Database::disconnect();
}
?>
    <div class="page-header">
        <a class="logo" href="index.php">
            <h3>Editar factura</h3>
        </a>
    </div>

    <div class="row">
        <div role="complementary" class="col-md-2">
            <nav class="hidden-print hidden-xs hidden-sm affix">
                <button type="button" class="btn btn-primary btn-sm" id="preview-fact-link">
                    <span class="glyphicon glyphicon-eye-open"></span> Previsualizar
                </button>
                <br><br>
                <button type="button" class="btn btn-primary btn-sm save-presu" id="save-fact-link">
                    <span class="glyphicon glyphicon-floppy-disk"></span> Guardar
                </button>
            </nav>
        </div>
        <div class="col-md-10">
            <form id="update-factura-form" class="form-horizontal fact" method="post" action="lib/pdf-fact.php?preview=1" target="_blank">
                <input type="hidden" name="id_factura" id="id_factura" value="<?php echo $id; ?>">
                <fieldset>

                    <!-- Ref. factura -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="ref">Ref. factura</label>

                        <div class="col-md-6">
                            <input id="ref_fact" name="ref_fact" class="form-control input-sm" readonly type="text" value="<?= $data['ref_factura'] ?>">
                        </div>
                    </div>

                    <!-- Ref. presupuesto asociado -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="ref">Ref. presupuesto</label>

                        <div class="col-md-6">
                            <input id="ref_presu" name="ref_presu" class="form-control input-sm" readonly type="text" value="<?= $data['presupuesto_asoc'] ?>">
                        </div>
                    </div>

                    <!-- Ref. compras -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="ref">Ref. compras</label>

                        <div class="col-md-6">
                            <input id="ref_compras" name="ref_compras" class="form-control input-sm" readonly type="text" value="<?= empty($data['po_ref'])?'': $data['po_ref']?>">
                        </div>
                    </div>

                    <!-- Fecha emision -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="fecha_emision">Fecha emisión</label>

                        <div class="col-md-6">
                            <input id="fecha_emision" name="fecha_emision" placeholder="dd-mm-yyyy" class="form-control input-sm date" type="text" value="<?php echo date('d-m-Y', strtotime($data['fecha_emision'])); ?>">
                        </div>
                    </div>

                    <!-- Fecha vencimiento -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="fecha_vencimiento">Fecha vencimiento</label>

                        <div class="col-md-6">
                            <input id="fecha_vencimiento" name="fecha_vencimiento" placeholder="dd-mm-yyyy" class="form-control input-sm date" type="text" value="<?php echo date('d-m-Y', strtotime($data['fecha_vencimiento'])); ?>">
                        </div>
                    </div>

                    <!-- Cliente -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="cliente">Cliente</label>

                        <div class="col-md-6 inputs-cliente">
                            <input id="id-empresa" name="id-empresa" class="form-control input-sm" type="hidden" value="<?= $data['id_empresa'] ?>">
                            <input id="ref-empresa" name="ref-empresa" class="form-control input-sm" type="hidden" value="<?= $data['ref_cliente'] ?>">
                            <input id="nombre-cliente" name="nombre-cliente" class="form-control input-sm" type="text" placeholder="Nombre" autocomplete="off" value="<?=(!empty($data['presupuesto_asoc']))?$data['nombre_cliente']:$data['cliente'] ?>" <?=(!empty($data['presupuesto_asoc']))?'readonly':'' ?>>
                            <textarea class="form-control input-sm" id="direccion-cliente" name="direccion-cliente" <?=(!empty($data['presupuesto_asoc']))?'readonly':'' ?>><?=(!empty($data['presupuesto_asoc']))?$data['direccion_cliente']:$data['direccion'] ?></textarea>
                            <input id="cp-cliente" name="cp-cliente" class="form-control input-sm" type="text" placeholder="CP" value="<?=(!empty($data['presupuesto_asoc']))?$data['cp_cliente']:$data['cp'] ?>" <?=(!empty($data['presupuesto_asoc']))?'readonly':'' ?>>
                            <input id="cif-cliente" name="cif-cliente" class="form-control input-sm" type="text" placeholder="CIF" value="<?=(!empty($data['presupuesto_asoc']))?$data['cif_cliente']:$data['cif'] ?>" <?=(!empty($data['presupuesto_asoc']))?'readonly':'' ?>>
                        </div>
                    </div>

                    <!-- Condiciones pago -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="condiciones-pago">Condiciones de pago</label>

                        <div class="col-md-6">
                            <input id="condiciones-pago" name="condiciones-pago" placeholder="Transferencia bancaria" class="form-control input-sm" type="text" value="<?= $data['condiciones_pago'] ?>">
                        </div>
                    </div>

                    <!-- Datos bancarios -->
                    <div class="form-group">
                        <label class="col-md-6 control-label" for="entidad">Entidad bancaria</label>

                        <div class="col-md-6">
                            <?php
                            $pdo = Database::connect();
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            $q = $pdo->prepare("SELECT * from cuentas_bancarias order by nombre");
                            $q->execute();
                            $data_ent = $q->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <select id="entidad" name="entidad" class="form-control input-sm">
                                <?php
                                foreach ($data_ent as $row) {
                                    ?>
                                    <option value="<?php echo $row['id']; ?>" <?= ($row['id'] == $data['datos_bancarios'])? 'selected' : ''; ?>>
                                        <?= $row['iban']; ?>
                                    </option>
                                <?php
                                }

                                Database::disconnect();
                                ?>
                            </select>
                        </div>
                    </div>

                </fieldset>

                <div id="suma-fact" class="alert alert-info">
                    <p><b>Subtotal: </b><span class="valor subtotal"><?= number_format(str_replace('.',',',$data['subtotal']), 2, ',', '.') ?></span> &euro;</p>
                    <p><b>I.V.A. (21%): </b><span class="valor iva"><?= number_format(str_replace('.',',',$data['iva']), 2, ',', '.') ?></span> &euro;</p>
                    <p><b>TOTAL: </b><span class="valor total"><?= number_format(str_replace('.',',',$data['total']), 2, ',', '.') ?></span> &euro;</p>
                </div>

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
                                    <input id="texto_0_precio" name="texto_0_precio" placeholder="Precio" class="form-control input-sm precio" type="text" data-sumar="1">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-sort"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-default btn-sm del-concepto" title="Eliminar">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>

                    <a class="btn btn-default btn-sm move-concepto" title="Mover">
                        <span class="glyphicon glyphicon-move"></span>
                    </a>

                </fieldset>

                <?php

                $sql = "SELECT * FROM concepto_factura WHERE id_factura = ? ORDER BY id_concepto";
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
											   placeholder="Concepto" class="form-control input-sm" type="text"
											   value="<?php echo $row['concepto'] ?>">
									</div>
								</div>
								<div class="col-md-3">
									<div class="input-group">
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
										<input id="concepto_sub_<?php echo $count ?>" name="concepto_sub_<?php echo $count ?>"
											   placeholder="Concepto subtítulo" class="form-control input-sm" type="text"
											   value="<?php echo $row['concepto_subtitulo'] ?>">
									</div>
								</div>
								<div class="col-md-3">
									<div class="input-group">
										<input id="concepto_sub_<?php echo $count ?>_precio"
											   name="concepto_sub_<?php echo $count ?>_precio" placeholder="Precio" data-sumar="1" class="form-control input-sm precio" type="text"
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
											   value="<?php echo $row['titulo1'] ?>">
									</div>
								</div>
								<div class="col-md-3">
									<div class="input-group">
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
											   value="<?php echo $row['titulo2'] ?>">
									</div>
								</div>
								<div class="col-md-3">
									<div class="input-group">
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
											   value="<?php echo $row['titulo3'] ?>">
									</div>
								</div>
								<div class="col-md-3">
									<div class="input-group">
										<input id="tit3_<?php echo $count ?>_precio" name="tit3_<?php echo $count ?>_precio" data-sumar="1" placeholder="Precio" class="form-control input-sm precio" type="text"
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
			Database::disconnect();
                ?>

                <button type="button" class="btn btn-default center-block add-concepto" title="Nuevo">
                    <span class="glyphicon glyphicon-plus-sign"></span>
                </button>

                <div class="text-center buttons">

                    <button type="submit" class="btn btn-primary btn-lg" id="preview-fact">
                        <span class="glyphicon glyphicon-eye-open"></span> Previsualizar
                    </button>

                    <button type="submit" class="btn btn-primary btn-lg save-fact" id="save-fact">
                        <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Factura
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div><!-- /container -->
    </div><!-- /main-wrapper -->
<?php

require_once('footer.php');