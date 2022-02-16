<!-- Modal -->
<div class="modal fade" id="obs-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Factura <b id="obs-ref"></b></h4>
            </div>
            <div class="modal-body">

                <!-- Modal body-->
                <div class="row">
                    <div class="col-md-12">

                        <form id="obs-form" name="obs-form" class="form" role="form" enctype="multipart/form-data" action="lib/functions.php?action=saveObservacionesFactura" method="post">
                            <div class="form-group">
                                <label for="obs-text">Observaciones</label>
                                <textarea rows="5" class="form-control" id="obs-text" name="obs-text" placeholder="Observaciones"></textarea>
                                <input type="hidden" class="form-control" id="id" name="id">
                                <input type="hidden" class="form-control" id="fact-ref" name="fact-ref">
                            </div>
                        </form>

                    </div>
                </div>
                <!-- /modal body -->

            </div>
            <div class="modal-footer">
                <button id="save-obs" type="button" class="btn btn-primary" data-id="" data-dismiss="modal">Guardar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
