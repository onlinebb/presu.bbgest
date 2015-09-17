<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 7/04/14
 * Time: 12:09
 */
require_once('header.php');
?>
    <div class="page-header">
        <a class="logo" href="index.php">
            <h3>Control Board</h3>
        </a>
    </div>

    <div class="wizard">
        <div class="row center-block text-center">
            <a href="wizard.php" class="btn btn-primary btn-lg new-presu">
                <span class="glyphicon glyphicon-plus"></span> Nuevo Presupuesto
            </a>
        </div>
    </div>

    <div class="listado">
        <div class="row">
            <div class="col-md-10">
                <h4>Listado de presupuestos</h4>
            </div>
            <div class="col-md-2 text-right">
                <?php
                if (isset($_GET["allpresus"])) {
                    $string = str_replace("&allpresus=1","",$_SERVER['QUERY_STRING']);
                    $boton = "Restablecer";
                }
                else {
                    $string = $_SERVER['QUERY_STRING']."&allpresus=1";
                    $boton = "Mostrar Todos";
                }
                ?>
                <a href="index.php?<?= $string ?>" class="btn btn-default btn-sm view-all-presus">
                    <?= $boton ?>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-curved">
                    <thead>
                    <tr>
                        <th>
                            <a href="index.php?order=ref">
                                Ref. presupuesto <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order=nombre_proyecto">
                                Proyecto <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order=estado">
                                Estado <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order=fecha">
                                Fecha <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order=nombre_cliente">
                                Cliente <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <?php
                            include 'lib/database.php';
                            $pdo = Database::connect();

                            if (isset($_GET["allpresus"])) {
                                $where = "";
                                $pagAllPresus = "&allpresus=1";
                            } else {
                                $pagAllPresus = "";
                            };

                            $sql = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'aceptado'";//.$where;
                            $q = $pdo->prepare($sql);

                            $q->execute(array($id));
                            $data = $q->fetch();
                            ?>
                            Total (<?= number_format($data['total_presus'], 2, ',', '.') ?>)
                        </th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    $rows_per_page = 10;

                    if (isset($_GET["page"])) {
                        $page = $_GET["page"];
                    } else {
                        $page = 1;
                    };

                    if (isset($_GET["order"])) {
                        $order = $_GET["order"];
                    } else {
                        $order = 'ref';
                    };

                    $start_from = ($page - 1) * $rows_per_page;
                    $result = $pdo->prepare("SELECT * FROM presupuesto ". $where ." ORDER BY $order DESC, ref DESC LIMIT $start_from, $rows_per_page");
                    $result->execute();
                    for ($i = 0; $row = $result->fetch(); $i++) {
                        ?>
                        <tr id="presu-<?php echo $row['id'] ?>">
                            <td><?php echo $row['ref'] ?></td>
                            <td><?php echo $row['nombre_proyecto'] ?></td>
                            <td><?php echo $row['estado'] ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['fecha'])); ?></td>
                            <td><?php echo $row['nombre_cliente'] ?></td>
                            <td><?= number_format($row['suma'], 2, ',', '.') ?></td>
                            <td class="acciones">
                                <a href="edit.php?id=<?php echo $row['id'] ?>" title="Editar">
                                    <span class="glyphicon icons-fontawesome-webfont-1"></span>
                                </a>&nbsp;
                                <a href="lib/pdf.php?id=<?php echo $row['id'] ?>" title="Ver PDF" target="_blank">
                                    <span class="glyphicon icons-fontawesome-webfont-2"></span>
                                </a>&nbsp;
                                <a class="noaceptar-presupuesto" href="" title="No aceptar" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                    <span class="glyphicon glyphicon-remove-circle"></span>
                                </a>&nbsp;
                                <a class="delete-presupuesto" href="" title="Eliminar" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                    <span class="glyphicon icons-fontawesome-webfont-3"></span>
                                </a>&nbsp;
                                <a class="po-presupuesto" href="" title="Orden de compra" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                    <b>PO</b>
                                </a>&nbsp;
                                <a class="copy-presupuesto" href="new.php?id=<?php echo $row['id'] ?>" title="Duplicar" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                    <span class="glyphicon icons-fontawesome-webfont"></span>
                                </a>&nbsp;
                                <a class="new-fact" href="new-fact.php?pre=<?php echo $row['id'] ?>" title="Nueva factura" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                    <span class="glyphicon icons-fontawesome-webfont-11"></span>
                                </a>&nbsp;</td>
                        </tr>
                    <?php
                    }

                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-12">
                <ul class="pagination">
                    <?php
                    $result = $pdo->prepare("SELECT COUNT(id) FROM presupuesto ".$where);
                    $result->execute();
                    $row = $result->fetch();
                    $total_records = $row[0];
                    $total_pages = ceil($total_records / $rows_per_page);

                    for ($i = 1; $i <= $total_pages; $i++) {
                        ?>
                        <li <?php if ($page == $i): ?>class="disabled"<?php endif; ?>>
                            <a href="index.php?page=<?= $i.$pagAllPresus ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php
                    }
                    Database::disconnect();
                    ?>
                </ul>
            </div>
        </div>
    </div> <!-- /listado -->

    <div class="row center-block text-center">
        <a href="new-fact.php" class="btn btn-primary btn-lg dnew-fact">
            <span class="glyphicon glyphicon-plus"></span> Nueva factura
        </a>
    </div>

    <div class="listado">
        <div class="row">
            <div class="col-md-10">
                <h4>Facturas por cobrar</h4>
            </div>
            <div class="col-md-2 text-right">
                <?php
                if (isset($_GET["allfact"])) {
                    $string = str_replace("&allfact=1","",$_SERVER['QUERY_STRING']);
                    $boton = "Restablecer";
                }
                else {
                    $string = $_SERVER['QUERY_STRING']."&allfact=1";
                    $boton = "Mostrar Todas";
                }
                ?>
                <a href="index.php?<?= $string ?>" class="btn btn-default btn-sm view-all">
                    <?= $boton ?>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-curved">
                    <thead>
                    <tr>
                        <th>
                            <a href="index.php?order_fact=ref_factura">
                                Ref. factura <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=presupuesto_asoc">
                                Presupuesto asoc. <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=estado">
                                Estado <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=fecha_emision">
                                Fecha emisión <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=fecha_vencimiento">
                                Fecha vencimiento <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <?php
                            $pdo = Database::connect();

                            if (isset($_GET["allfact"])) {
                                $whereFact = "";
                                $pagAllFact = "allfact=1";
                            } else {
                                $whereFact = "WHERE estado IN ('emitida')";
                                $pagAllFact = "";
                            };

                            $sql = "SELECT SUM(subtotal) AS total_fact from factura " . $whereFact;
                            $q = $pdo->prepare($sql);

                            $q->execute(array($id));
                            $data = $q->fetch();
                            ?>
                            Total (<?= number_format($data['total_fact'], 2, ',', '.') ?>)
                        </th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rows_per_page = 10;

                    if (isset($_GET["page_fact"])) {
                        $page_fact = $_GET["page_fact"];
                    } else {
                        $page_fact = 1;
                    };

                    if (isset($_GET["order_fact"])) {
                        $order_fact = $_GET["order_fact"];
                    } else {
                        $order_fact = 'ref_factura';
                    };

                    $start_from = ($page_fact - 1) * $rows_per_page;
                    $result = $pdo->prepare("SELECT * FROM factura ". $whereFact ." ORDER BY $order_fact DESC, ref_factura DESC LIMIT $start_from, $rows_per_page");
                    $result->execute();
                    for ($i = 0; $row = $result->fetch(); $i++) {
                        ?>
                        <tr id="fact-<?php echo $row['id'] ?>">
                            <td><?= $row['ref_factura'] ?></td>
                            <td><?= $row['presupuesto_asoc'] ?></td>
                            <td><?= $row['estado'] ?></td>
                            <td><?= date('d-m-Y', strtotime($row['fecha_emision'])); ?></td>
                            <td><?= date('d-m-Y', strtotime($row['fecha_vencimiento'])); ?></td>
                            <td><?= number_format($row['subtotal'], 2, ',', '.') ?></td>
                            <td class="acciones">
                                <a href="edit-fact.php?id=<?= $row['id'] ?>" title="Editar">
                                    <span class="glyphicon icons-fontawesome-webfont-1"></span>
                                </a>&nbsp;
                                <a href="lib/pdf-fact.php?id=<?= $row['id'] ?>" title="Ver PDF" target="_blank">
                                    <span class="glyphicon icons-fontawesome-webfont-2"></span>
                                </a>&nbsp;
                                <a class="delete-factura" href="" title="Eliminar" data-id="<?= $row['id'] ?>" data-ref="<?= $row['ref_factura'] ?>" data-presu="<?= $row['presupuesto_asoc'] ?>">
                                    <span class="glyphicon icons-fontawesome-webfont-3"></span>
                                </a>&nbsp;
                                <a class="factura-cobrada" href="" title="Dar por cobrada" data-id="<?= $row['id'] ?>" data-ref="<?= $row['ref_factura'] ?>" data-presu="<?= $row['presupuesto_asoc'] ?>">
                                    <span class="glyphicon icons-fontawesome-webfont-12"></span>
                                </a>&nbsp;
                            </td>
                        </tr>
                    <?php
                    }

                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-12">
                <ul class="pagination">
                    <?php
                    $result = $pdo->prepare("SELECT COUNT(id) FROM factura ". $whereFact);
                    $result->execute();
                    $row = $result->fetch();
                    $total_records = $row[0];
                    $total_pages = ceil($total_records / $rows_per_page);

                    for ($i = 1; $i <= $total_pages; $i++) {
                        ?>
                        <li <?php if ($page_fact == $i): ?>class="disabled"<?php endif; ?>>
                            <a href="index.php?page_fact=<?= $i.'&'.$pagAllFact ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php
                    }
                    Database::disconnect();
                    ?>
                </ul>
            </div>
        </div>
    </div> <!-- /listado -->
<?php

require_once('confirmar-modal.php');
require_once('confirmar-modal-fact.php');
require_once('po-modal.php');

require_once('footer.php');