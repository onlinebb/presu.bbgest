<!-- Modal -->
<div class="modal fade" id="po-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Orden de compra</h4>
            </div>
            <div class="modal-body">

                <!-- Modal body-->
                <div class="row">
                    <div class="col-md-8">

                        <form id="po-form" name="po-form" class="form" role="form" enctype="multipart/form-data" action="lib/functions.php?action=savePO" method="post">
                            <div class="form-group">
                                <label for="po-ref">Referencia</label>
                                <input type="text" class="form-control" id="po-ref" name="po-ref" placeholder="Referencia">
                                <input type="hidden" class="form-control" id="id" name="id">
                                <input type="hidden" class="form-control" id="presu-ref" name="presu-ref">
                            </div>

                            <div class="form-group">
                                <label for="po-file">Archivo</label>
                                <a target="_blank" href="" id="po-file-current"></a>
                                <input type="file" id="po-file" name="po-file">
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /modal body -->

            </div>
            <div class="modal-footer">
                <button id="save-po" type="button" class="btn btn-primary" data-id="" data-dismiss="modal">Guardar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->