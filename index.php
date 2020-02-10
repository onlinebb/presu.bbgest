<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 7/04/14
 * Time: 12:09
 */
require_once('header.php');
require_once('config.php');
?>
    <div class="page-header">
        <div class="row">
            <div class="col-md-6">
                <a class="logo" href="index.php">
                    <h3>Home<?= (isset($_GET['search']))?": filtrando resultados para <b>".$_GET['search']."</b>":"" ?></h3>
                </a>
            </div>
            <div class="col-md-6 text-right">
                <form id="form-filtro" class="form-inline" role="form">
                    <div class="form-group">
                        <input type="text" class="form-control input-lg" id="texto-busqueda" placeholder="Texto a buscar">
                    </div>
                    <button type="submit" id="filtrar" class="btn btn-default btn-lg">
                        <span class="glyphicon glyphicon-search"></span>Filtrar resultados
                    </button>
                </form>

            </div>
        </div>
    </div>

    <div class="wizard">
<!--        <div class="row center-block text-center">-->
            <a href="wizard.php" class="btn btn-primary btn-sm new-presu pull-left">
                <span class="glyphicon glyphicon-plus"></span> Nuevo Presupuesto
            </a>
            <a class="btn btn-primary btn-sm pull-left cliente" href="alta_empresa.php"> <span class="glyphicon glyphicon-plus"></span> Alta Cliente</a>
            <a class="btn btn-primary btn-sm pull-left cliente" href="edit_empresa.php">Modificar Cliente</a>
<!--        </div>-->
    </div>

    <!--<div class="div-anyo clearfix"><input type="text" class="form-control input-sm" id="anyo-export" name="anyo-export" placeholder="Año exportación"></div>-->
<?php
if(isset($_SESSION['priv']) && $_SESSION['priv'] == 1):
    ?>
    <a href="lib/functions.php?action=exportExcel&tipo=presupuestos" class="btn btn-primary btn-sm pull-right exportar-presus">
        <span class="glyphicon glyphicon-export"></span> Exportar Presupuestos
    </a>
    <a href="lib/functions.php?action=exportExcel&tipo=facturas" target="_blank" class="btn btn-primary btn-sm pull-right exportar-fact">
        <span class="glyphicon glyphicon-export"></span> Exportar Facturas
    </a>
<?php
endif;
?>

    <div class="listado">
        <div class="row">
            <div class="col-md-10">
                <a name="presus"></a>
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
                            <a href="index.php?order=proyecto">
                                Proyecto <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order=nombre_propuesta">
                                Título Propuesta <span class="glyphicon glyphicon-sort"></span>
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

                            if(isset($_SESSION['priv']) && $_SESSION['priv'] == 1):

                                //Busqueda
                                if (isset($_GET["search"])) {
                                    $search = "concat(ref, nombre_cliente, ifnull(ref_cliente,''), ifnull(nombre_propuesta,''), ifnull(proyecto,'')) like '%".$_GET["search"]."%'";
                                }
                                else {
                                    $search = "1=1";
                                }

                                if (isset($_GET["allpresus"])) {
                                    $where = " WHERE ".$search;
                                    $pagAllPresus = "&allpresus=1";
                                } else {
                                    $where = " WHERE estado IN ('pendiente', 'aceptado', 'facturado parcialmente') AND ".$search;
                                    $pagAllPresus = "";
                                }

                                $sql_aceptados = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'aceptado'";
                                $sql_pendientes = "SELECT SUM(suma) AS total_presus from presupuesto WHERE estado = 'pendiente'";
                                $sql_pendiente_parcial = "SELECT SUM(suma-(SELECT sum(factura.subtotal) FROM factura where presupuesto_asoc = presupuesto.ref and estado <>'abonada')) AS total_presus from presupuesto WHERE estado ='facturado parcialmente'";

                                $q_aceptados = $pdo->prepare($sql_aceptados);
                                $q_pendientes = $pdo->prepare($sql_pendientes);
                                $q_pendiente_parcial = $pdo->prepare($sql_pendiente_parcial);

                                $q_aceptados->execute();
                                $q_pendientes->execute();
                                $q_pendiente_parcial->execute();

                                $data_aceptados = $q_aceptados->fetch();
                                $data_pendientes = $q_pendientes->fetch();
                                $data_pendiente_parcial = $q_pendiente_parcial->fetch();
                                ?>
                                A: <?= number_format($data_aceptados['total_presus']+$data_pendiente_parcial['total_presus'], 2, ',', '.') ?> <br>P: <?= number_format($data_pendientes['total_presus'], 2, ',', '.') ?>
                            <?php
                            else:
                                ?>
                                Total
                            <?php
                            endif;
                            ?>
                        </th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    $rows_per_page = 50;

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

                    if(!isset($_SESSION['priv']) || isset($_SESSION['priv']) && $_SESSION['priv'] == 0) {
                        if(empty($where))
                            $privs = " WHERE autor LIKE '%".$_SESSION['valid']."%' ";
                        else
                            $privs = " AND autor LIKE '%".$_SESSION['valid']."%' ";
                    }
                    else {
                        $privs = "";
                    }

                    $start_from = ($page - 1) * $rows_per_page;
                    $result = $pdo->prepare("SELECT * FROM listado_presus ". $where .$privs." ORDER BY $order DESC, ref DESC LIMIT $start_from, $rows_per_page");
                    $result->execute();
                    for ($i = 0; $row = $result->fetch(); $i++) {

                        $facturado_presu = $pdo->prepare("SELECT sum(subtotal) as fact_presu FROM factura where presupuesto_asoc = ? and estado not in ('abonada')");
                        $facturado_presu->execute(array($row['ref']));
                        $data_facturado = $facturado_presu->fetch();
                        $restar = $data_facturado['fact_presu'];
                        ?>
                        <tr id="presu-<?php echo $row['id'] ?>">
                            <td><?php echo $row['ref'] ?></td>
                            <td><?php echo $row['proyecto'] ?></td>
                            <td><?php echo $row['nombre_propuesta'] ?></td>
                            <td><?php echo $row['estado'] ?></td>
                            <td class="fecha"><?php echo date('d-m-Y', strtotime($row['fecha'])); ?></td>
                            <td><?php echo $row['nombre_cliente'] ?></td>
                            <td><?= number_format($row['suma']-$restar, 2, ',', '.') ?></td>
                            <td class="acciones">
                                <a href="edit.php?id=<?php echo $row['id'] ?>" title="Editar">
                                    <span class="glyphicon icons-fontawesome-webfont-1"></span>
                                </a>&nbsp;
                                <?php
                                if($row['english']):
                                ?>
                                <a href="lib/pdf_en.php?id=<?= $row['id'] ?>" title="Ver PDF" target="_blank">
                                    <?php
                                    else:
                                    ?>
                                    <a href="lib/pdf.php?id=<?= $row['id'] ?>" title="Ver PDF" target="_blank">
                                        <?php
                                        endif;
                                        ?>
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
                                    <a class="copy-presupuesto" data-origen="0" href="new.php?id=<?php echo $row['id'] ?>" title="Duplicar sin origen" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                        <span class="glyphicon icons-fontawesome-webfont"></span>
                                    </a>&nbsp;
                                    <a class="copy-presupuesto" data-origen="1" href="new.php?id=<?php echo $row['id'] ?>" title="Negociar (con origen)" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                        <span class="glyphicon icons-fontawesome-webfont"></span>+
                                    </a>&nbsp;
                                    <?php
                                    if(isset($_SESSION['priv']) && $_SESSION['priv'] == 1):
                                        ?>
                                        <a class="new-fact" href="new-fact.php?pre=<?php echo $row['id'] ?>" title="Nueva factura" data-id="<?php echo $row['id'] ?>" data-ref="<?php echo $row['ref'] ?>">
                                            <span class="glyphicon icons-fontawesome-webfont-11"></span>
                                        </a>&nbsp;
                                    <?php
                                    endif;
                                    ?>
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
                    $result = $pdo->prepare("SELECT COUNT(id) FROM presupuesto ".$where.$privs);
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

<?php
if(isset($_SESSION['priv']) && $_SESSION['priv'] == 1):
    ?>
    <div class="row center-block text-center">
        <a href="new-fact.php" class="btn btn-primary btn-lg dnew-fact">
            <span class="glyphicon glyphicon-plus"></span> Nueva factura
        </a>
    </div>

    <div class="listado">
        <div class="row">
            <div class="col-md-10">
                <a name="fact"></a>
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
                <a href="index.php?<?= $string.'#fact' ?>" class="btn btn-default btn-sm view-all">
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
                            <a href="index.php?order_fact=ref_factura#fact">
                                Ref. factura <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=presupuesto_asoc#fact">
                                Presupuesto asoc. <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=estado#fact">
                                Estado <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=fecha_emision#fact">
                                Fecha emisión <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_fact=fecha_vencimiento#fact">
                                Fecha vencimiento <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <?php
                            $pdo = Database::connect();

                            //Busqueda
                            if (isset($_GET["search"])) {
                                $searchFact = "concat(ref_factura, ifnull(presupuesto_asoc,''), ifnull(cliente,'')) like '%".$_GET["search"]."%'";
                            }
                            else {
                                $searchFact = "1=1";
                            }

                            if (isset($_GET["allfact"])) {
                                $whereFact = " WHERE ".$searchFact;
                                $pagAllFact = "allfact=1";
                                $sql = "SELECT SUM(subtotal) AS total_fact from factura where estado not IN('abonada') ";
                                $sql_total = "SELECT SUM(total) AS total_fact_i from factura where estado not IN('abonada') ";
                            } else {
                                $whereFact = " WHERE estado IN ('emitida') AND ".$searchFact;
                                $pagAllFact = "";
                                $sql = "SELECT SUM(subtotal) AS total_fact from factura ".$whereFact." and estado not IN('abonada') ";
                                $sql_total = "SELECT SUM(total) AS total_fact_i from factura ".$whereFact." and estado not IN('abonada') ";
                            };

                            $q = $pdo->prepare($sql);
                            $q->execute(/*array($id)*/);
                            $data = $q->fetch();

                            $q_i = $pdo->prepare($sql_total);
                            $q_i->execute(/*array($id)*/);
                            $data_i = $q_i->fetch();
                            ?>
                            Total (<?= number_format($data['total_fact'], 2, ',', '.') ?>)<br>
                            Total I (<?= number_format($data_i['total_fact_i'], 2, ',', '.') ?>)
                        </th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rows_per_page = 50;

                    if (isset($_GET["page_fact"])) {
                        $page_fact = $_GET["page_fact"];
                    } else {
                        $page_fact = 1;
                    }

                    if (isset($_GET["order_fact"])) {
                        $order_fact = $_GET["order_fact"];
                    } else {
                        $order_fact = 'ref_factura';
                    }

                    $start_from = ($page_fact - 1) * $rows_per_page;
                    $result = $pdo->prepare("SELECT * FROM factura ". $whereFact ." ORDER BY $order_fact DESC, ref_factura DESC LIMIT $start_from, $rows_per_page");
                    $result->execute();
                    for ($i = 0; $row = $result->fetch(); $i++) {
                        ?>
                        <tr id="fact-<?php echo $row['id'] ?>">
                            <td><?= $row['ref_factura'] ?></td>
                            <td><?= $row['presupuesto_asoc'] ?></td>
                            <td><?= $row['estado'] ?></td>
                            <td class="fecha"><?= date('d-m-Y', strtotime($row['fecha_emision'])); ?></td>
                            <td class="fecha"><?= date('d-m-Y', strtotime($row['fecha_vencimiento'])); ?></td>
                            <td><?= number_format($row['total'], 2, ',', '.') ?></td>
                            <td class="acciones">
                                <a href="edit-fact.php?id=<?= $row['id'] ?>" title="Editar">
                                    <span class="glyphicon icons-fontawesome-webfont-1"></span>
                                </a>&nbsp;
                                <?php
                                if($row['english']):
                                ?>
                                <a href="lib/pdf-fact_en.php?id=<?= $row['id'] ?>&noiva=<?=$row['noiva']?>" title="Ver PDF" target="_blank">
                                    <?php
                                    else:
                                    ?>
                                    <a href="lib/pdf-fact.php?id=<?= $row['id'] ?>&noiva=<?=$row['noiva']?>" title="Ver PDF" target="_blank">
                                        <?php
                                        endif;
                                        ?>
                                        <span class="glyphicon icons-fontawesome-webfont-2"></span>
                                    </a>&nbsp;
                                    <a class="delete-factura" href="" title="Abonar" data-id="<?= $row['id'] ?>" data-ref="<?= $row['ref_factura'] ?>" data-presu="<?= $row['presupuesto_asoc'] ?>">
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
                            <a href="index.php?page_fact=<?= $i.'&'.$pagAllFact ?>#fact">
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


    <div class="listado">
        <div class="row">
            <div class="col-md-10">
                <a name="abon"></a>
                <h4>Facturas abonadas</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-curved">
                    <thead>
                    <tr>
                        <th>
                            <a href="index.php?order_abonos=ref_abono#abon">
                                Ref. Abono <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_abonos=ref_factura#abon">
                                Ref. factura <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_abonos=presupuesto_asoc#abon">
                                Presupuesto asoc. <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_abonos=fecha_emision#abon">
                                Fecha emisión <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <a href="index.php?order_abonos=fecha_abono#abon">
                                Fecha abono <span class="glyphicon glyphicon-sort"></span>
                            </a>
                        </th>
                        <th>
                            <?php
                            $pdo = Database::connect();

                            $sql = "SELECT SUM(subtotal) AS total_fact from factura WHERE estado IN ('abonada')";
                            $q = $pdo->prepare($sql);

                            $q->execute(/*array($id)*/);
                            $data = $q->fetch();
                            ?>
                            Total (<?= number_format($data['total_fact'], 2, ',', '.') ?>)
                        </th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rows_per_page = 50;

                    //Busqueda
                    if (isset($_GET["search"])) {
                        $searchAbo = "concat(ref_factura, ifnull(presupuesto_asoc,''), ifnull(cliente,'')) like '%".$_GET["search"]."%'";
                    }
                    else {
                        $searchAbo = "1=1";
                    }

                    if (isset($_GET["page_fact"])) {
                        $page_fact = $_GET["page_abonos"];
                    } else {
                        $page_fact = 1;
                    }

                    if (isset($_GET["order_abonos"])) {
                        $order_fact = $_GET["order_abonos"];
                    } else {
                        $order_fact = 'ref_abono';
                    }

                    $start_from = ($page_fact - 1) * $rows_per_page;
                    $result = $pdo->prepare("SELECT * FROM factura WHERE estado IN ('abonada') AND ".$searchAbo." ORDER BY $order_fact DESC, ref_abono DESC LIMIT $start_from, $rows_per_page");
                    $result->execute();
                    for ($i = 0; $row = $result->fetch(); $i++) {
                        ?>
                        <tr id="fact-<?php echo $row['id'] ?>">
                            <td><?= $row['ref_abono'] ?></td>
                            <td><?= $row['ref_factura'] ?></td>
                            <td><?= $row['presupuesto_asoc'] ?></td>
                            <td class="fecha"><?= date('d-m-Y', strtotime($row['fecha_emision'])); ?></td>
                            <td class="fecha"><?= date('d-m-Y', strtotime($row['fecha_abono'])); ?></td>
                            <td><?= number_format($row['subtotal'], 2, ',', '.') ?></td>
                            <td>
                                <?php
                                if($row['english']):
                                ?>
                                <a href="lib/pdf-fact.php?id=<?= $row['id'] ?>&noiva=<?=$row['noiva']?>" title="Ver PDF" target="_blank">
                                    <?php
                                    else:
                                    ?>
                                    <a href="lib/pdf-fact.php?id=<?= $row['id'] ?>&noiva=<?=$row['noiva']?>" title="Ver PDF" target="_blank">
                                        <?php
                                        endif;
                                        ?>
                                        <span class="glyphicon icons-fontawesome-webfont-2"></span>
                                    </a>
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
                    $result = $pdo->prepare("SELECT COUNT(id) FROM factura WHERE estado IN ('abonada')");
                    $result->execute();
                    $row = $result->fetch();
                    $total_records = $row[0];
                    $total_pages = ceil($total_records / $rows_per_page);

                    for ($i = 1; $i <= $total_pages; $i++) {
                        ?>
                        <li <?php if ($page_fact == $i): ?>class="disabled"<?php endif; ?>>
                            <a href="index.php?page_abonos=<?= $i.'&'.$pagAllFact ?>#abon">
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
endif;

require_once('confirmar-modal.php');
require_once('confirmar-modal-fact.php');
require_once('po-modal.php');

require_once('footer.php');