<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 22/04/14
 * Time: 12:09
 */
require_once('header.php');
?>
    <script type="text/javascript" src="js/jquery.bootpag.min.js"></script>

    <div class="page-header">
        <a class="logo" href="index.php">
            <h3>Búsqueda conceptos</h3>
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form class="form-inline" role="form">
                <div class="form-group">
                    <input type="text" class="form-control input-lg" id="texto-busqueda" placeholder="Texto a buscar">
                </div>
                <!--<div class="form-group">
                    <div class="checkbox">
                        <label>
                            Buscar en otros presupuestos
                            <input type="checkbox" >
                        </label>
                    </div>
                </div>-->
                <button id="search-catalog" type="submit" class="btn btn-default btn-lg">
                    <span class="glyphicon glyphicon-search"></span> Buscar conceptos
                </button>
            </form>

        </div>
        <div class="col-md-4 text-right">
            <form id="wizard-next" class="form-inline" role="form" method="post" action="<?= empty($_POST['referencia'])?'new.php':'edit.php?wiz=1&id='.$_POST['id'] ?>">
                <button type="submit" class="btn btn-primary btn-lg">
                    Siguiente <span class="glyphicon glyphicon-arrow-right"></span>
                </button>
                <input type="hidden" id="conceptos" name="conceptos" value='<?= $_POST['conceptos'] ?>'>

                <input type="hidden" id="id" name="id" value='<?= $_POST['id'] ?>'>
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
        <div class="col-md-12">
            <h4>Resultados Catálogo</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table id="catalog-results" class="table table-striped table-curved">
                <thead>
                <tr>
                    <th>Concepto</th>
                    <th width="10%">Precio</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
    <div class="row text-center">
        <div id="catalog-pagination" class="col-md-12">
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col-md-12">
            <h4>Resultados Histórico</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table id="archive-results" class="table table-striped table-curved">
                <thead>
                <tr>
                    <th>Concepto</th>
                    <th width="10%">Precio</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
    <div class="row text-center">
        <div id="archive-pagination" class="col-md-12">
        </div>
    </div>
<?php

require_once('footer.php');