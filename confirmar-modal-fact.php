<!-- Modal -->
<div class="modal fade" id="confirmar-modal-fact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Confirmar</h4>
        </div>
        <div class="modal-body">

            <!-- Modal body-->
            <strong>¿Quieres abonar la factura <span id="ref-fact"></span>?</strong>
            <br><br>
            <label>Indica por qué se va a realizar el abono</label>
            <textarea class="form-control input-sm" id="razon-abono" name="razon-abono"></textarea><br>
            <div id="error-razon" class="alert alert-danger small">Es obligatorio indicar la razón del abono.</div>
            <!-- /modal body -->

        </div>
        <div class="modal-footer">
            <button id="confirmar-fact-si" type="submit" class="btn btn-primary" data-id="">Sí</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
        </div>
    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
