/**
 * Created by judit on 1/04/14.
 */

$(function () {
    /**
     * eliminar outline en links si no es fa servir el teclat
     */
    $("body").on("mousedown", "a, button", function (e) {
        //if (($(this).is(":focus") || $(this).is(e.target)) && $(this).css("outline-style") == "none") {
        $(this).css("outline", "none").on("blur", function () {
            $(this).off("blur").css("outline", "");
        });
        //}
    });

    //Filtrar resultados home
    $('#form-filtro').submit(function (e) {
        var texto = $('#texto-busqueda').val();
        var url = location.pathname + location.search;

        if(location.search == "") {
            window.location = url + "?search=" + texto;
        }
        else {
            window.location = url + "&search=" + texto;
        }

        e.preventDefault();
    });

    /**
     * Selección de cliente
     */
    $('#nombre-cliente').typeahead({
        source: function (query, response) {
            return $.getJSON("lib/functions.php?action=searchClient&text=" + query,
                function (result, status) {
                    //console.log(result);
                    response($.map(result, function (item) {
                        return {
                            id: item.id,
                            label: item.nombre,
                            cif: item.cif,
                            direccion: item.direccion,
                            cp: item.cp,
                            ref_cliente: item.ref_cliente
                        }
                    }));
                });
        },
        onselect: function (element, obj) {
            element.val(obj.label);
            $('#cif-cliente').val(obj.cif);
            $('#direccion-cliente').val(obj.direccion);
            $('#cp-cliente').val(obj.cp);
            $('#id-empresa').val(obj.id);
            $('#ref-empresa').val(obj.ref_cliente);

            //$('#proyecto').removeAttr("disabled");

            if($('.honorarios-wrapper').length > 0) {
                $.get("honorarios-modal.php?id_cliente="+$('#id-empresa').val(), function(data) {
                    $(".honorarios-wrapper").html(data);
                    loadEventHonorarios();
                });
            }

        },
        property: "label",
        minLength: 2
    });

    /**
     * Selección de proyecto
     */
    $('#proyecto').typeahead({
        source: function (query, response) {
            return $.getJSON("lib/functions.php?action=searchProyecto&text=" + query + "&cliente=" + $('#id-empresa').val(),
                function (result, status) {
                    //console.log(result);
                    response($.map(result, function (item) {
                        return {
                            id: item.id,
                            label: item.nombre,
                            ref_proyecto: item.ref_proyecto
                        }
                    }));
                });
        },
        onselect: function (element, obj) {
            element.val(obj.label);
            $('#id_proyecto').val(obj.id);
        },
        property: "label",
        minLength: 2
    });


    /**
     * Eventos de la lista de conceptos
     */
    var loadEvents = function () {
        $('#presupuesto-form, #update-presupuesto-form, #factura-form, #update-factura-form').sortable({
            items: "> fieldset.concepto",
            axis: "y",
            placeholder: "sortable-highlight",
            forcePlaceholderSize: true
        });

        $('.concepto').sortable({
            items: "> div",
            axis: "y"/*,
             update: function(event, ui) {
             var newOrder = $(this).sortable('toArray', {attribute: "data-tipo"});
             //console.log(newOrder);
             //$.get('saveSortable.php', {order:newOrder});
             }*/
        });

        $('.remove').on('click', function () {
            var input = $(this).parent().parent().parent().parent();
            input.animate({
                "opacity": "0",
                "duration": 1000
            }, function () {
                input.remove();
                sumaPrecios();
            });
        });

        copyConcepto();

        $('.del-concepto').on('click', function () {
            var item = $(this).parent();
            item.animate({
                "opacity": "0",
                "duration": 1000
            }, function () {
                item.remove();
                sumaPrecios();
            });
        });

        $('.toggle-suma').on('click', function () {
            var item = $(this).find('span');
            var precio = $(this).parent().find('.precio');

            if(item.hasClass('glyphicon-ok-circle')) {
                //no sumar
                item.removeClass('glyphicon-ok-circle');
                item.addClass('glyphicon-remove-circle');

                precio.data('sumar', '0');
            }
            else if(item.hasClass('glyphicon-remove-circle')) {
                //sí sumar
                item.removeClass('glyphicon-remove-circle');
                item.addClass('glyphicon-ok-circle');

                precio.data('sumar', '1');
            }

            sumaPrecios();
        });

        $('.precio').on('change', function () {
            sumaPrecios();
        });

        sumaPrecios();
    }

    var sumaPrecios = function () {

        var total = 0;

        $('.precio').each(function (i) {

			if($(this).val() != 0 && $(this).data('sumar') == 1) {

				if ($(this).val().length > 0)
					total += parseFloat($(this).val());
			}
        });

        $('#suma .valor').text(total.formatMoney(2));

        if($('#suma-fact').length > 0) {

            var iva = total * 0.21;
            var totalFact = total + iva;

            $('#suma-fact .valor.subtotal').text(total.formatMoney(2));
            $('#suma-fact .valor.iva').text(iva.formatMoney(2));
            $('#suma-fact .valor.total').text(totalFact.formatMoney(2));
        }

    }

    var getHoy = function () {
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!

        var yyyy = today.getFullYear();
        if (dd < 10) {
            dd = '0' + dd
        }
        if (mm < 10) {
            mm = '0' + mm
        }
        var today = dd + '-' + mm + '-' + yyyy;

        return today;
    }

    var copyConcepto = function () {

        $('.copy-concepto').off('click');

        $('.copy-concepto').on('click', function () {

            var concepto = $(this).parent().clone().hide();

            var id_copy = concepto.attr('id');
            var index_copy = id_copy.substring("concepto_group_".length);

            var textarea = $('#texto_'+index_copy).val();

            concepto.removeClass('hide');

            concepto.attr('id', 'concepto_group_' + counter);

            concepto.attr('data-index', counter);

            concepto.find('legend').text('Concepto ' + counter);

            concepto.find('#concepto_'+index_copy).attr('id', 'concepto_' + counter).attr('name', 'concepto_' + counter);
            concepto.find('#concepto_'+index_copy+'_precio').attr('id', 'concepto_' + counter + '_precio').attr('name', 'concepto_' + counter + '_precio');

            concepto.find('#concepto_sub_'+index_copy).attr('id', 'concepto_sub_' + counter).attr('name', 'concepto_sub_' + counter);
            concepto.find('#concepto_sub_'+index_copy+'_precio').attr('id', 'concepto_sub_' + counter + '_precio').attr('name', 'concepto_sub_' + counter + '_precio');

            concepto.find('#tit1_'+index_copy).attr('id', 'tit1_' + counter).attr('name', 'tit1_' + counter);
            concepto.find('#tit1_'+index_copy+'_precio').attr('id', 'tit1_' + counter + '_precio').attr('name', 'tit1_' + counter + '_precio');

            concepto.find('#tit2_'+index_copy).attr('id', 'tit2_' + counter).attr('name', 'tit2_' + counter);
            concepto.find('#tit2_'+index_copy+'_precio').attr('id', 'tit2_' + counter + '_precio').attr('name', 'tit2_' + counter + '_precio');

            concepto.find('#tit3_'+index_copy).attr('id', 'tit3_' + counter).attr('name', 'tit3_' + counter);
            concepto.find('#tit3_'+index_copy+'_precio').attr('id', 'tit3_' + counter + '_precio').attr('name', 'tit3_' + counter + '_precio');

            concepto.find('#texto_'+index_copy).val(textarea);
            concepto.find('#texto_'+index_copy).attr('id', 'texto_' + counter).attr('name', 'texto_' + counter);
            concepto.find('#texto_'+index_copy+'_precio').attr('id', 'texto_' + counter + '_precio').attr('name', 'texto_' + counter + '_precio');

            concepto.insertAfter($(this).parent()).fadeIn();

            counter++;
            loadEvents();
        });
    }

    var wrapHonorarios = function () {

        var honorarios = [];

        $('#honorarios-modal .rate').each(function (i) {
            //guardamos el objeto con los campos llenos de cada item
            var content = {};
            var item = $(this);
            var index = item.data('idperfil');

            content["id_perfil"] = index;
            content["precio"] = item.val();

            honorarios.push(content);
        });

        return honorarios;
    }

    var wrapHonorariosNovartis = function () {

        var honorarios = [];

        $('#honorarios-modal .rate').each(function (i) {
            //guardamos el objeto con los campos llenos de cada item
            var content = {};
            var item = $(this);
            var index = item.data('idperfil');

            content["id_perfil"] = index;
            content["precio"] = item.val();

            honorarios.push(content);
        });

        return honorarios;
    }

    var wrapConceptos = function () {

        var conceptos = [];

        $('.concepto').each(function (i) {
            if (i > 0) {
                //guardamos el objeto con los campos llenos de cada item
                var content = {};
                var item = $(this);
                var orden = [];
                var index = item.data('index');

                item.find('.wrap-concepto').each(function () {
                    orden.push($(this).data('tipo'));
                });

                content["concepto"] = item.find('#concepto_' + index).val();
                content["concepto_subtitulo"] = item.find('#concepto_sub_' + index).val();
                content["titulo1"] = item.find('#tit1_' + index).val();
                content["titulo2"] = item.find('#tit2_' + index).val();
                content["titulo3"] = item.find('#tit3_' + index).val();
                content["texto"] = item.find('#texto_' + index).val();

                content["precio_concepto"] = item.find('#concepto_' + index + '_precio').val();
                content["precio_concepto_subtitulo"] = item.find('#concepto_sub_' + index + '_precio').val();
                content["precio_titulo1"] = item.find('#tit1_' + index + '_precio').val();
                content["precio_titulo2"] = item.find('#tit2_' + index + '_precio').val();
                content["precio_titulo3"] = item.find('#tit3_' + index + '_precio').val();
                content["precio_texto"] = item.find('#texto_' + index + '_precio').val();

                content["precio_concepto_sumar"] = item.find('#concepto_' + index + '_precio').data('sumar');
                content["precio_concepto_subtitulo_sumar"] = item.find('#concepto_sub_' + index + '_precio').data('sumar');
                content["precio_titulo1_sumar"] = item.find('#tit1_' + index + '_precio').data('sumar');
                content["precio_titulo2_sumar"] = item.find('#tit2_' + index + '_precio').data('sumar');
                content["precio_titulo3_sumar"] = item.find('#tit3_' + index + '_precio').data('sumar');
                content["precio_texto_sumar"] = item.find('#texto_' + index + '_precio').data('sumar');

                content["orden"] = orden.toString();

                conceptos.push(content);

            }

        });

        return conceptos;
    }

    var wrapConceptosFact = function () {

        var conceptos = [];

        $('.concepto').each(function (i) {
            if (i > 0) {
                //guardamos el objeto con los campos llenos de cada item
                var content = {};
                var item = $(this);
                var index = item.data('index');

                content["concepto"] = item.find('#concepto_' + index).val();
                content["precio_concepto"] = item.find('#concepto_' + index + '_precio').val();
                conceptos.push(content);
            }

        });

        return conceptos;
    }

    Number.prototype.formatMoney = function (c, d, t) {
        var n = this,
            c = isNaN(c = Math.abs(c)) ? 2 : c,
            d = d == undefined ? "," : d,
            t = t == undefined ? "." : t,
            s = n < 0 ? "-" : "",
            i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    };

    var counter = $('.concepto').length;

    loadEvents();

    if($('#update-factura-form').length == 0)
        sumaPrecios();

    if ($('#fecha').length > 0 && $('#fecha').val().length == 0) {
        //poner fecha dia de hoy
        $('#fecha').val(getHoy());
    }

    if ($('#fecha_emision').length > 0 && $('#fecha_emision').val().length == 0) {
        //poner fecha dia de hoy
        $('#fecha_emision').val(getHoy());
    }

    //Date picker
    $.fn.bootstrapDP = $.fn.datepicker.noConflict();
    $('input.date').bootstrapDP({
        autoclose: true,
        language: 'es',
        weekStart: 1,
        format: 'dd-mm-yyyy'
    });

    $('#honorarios-modal').on('show.bs.modal', function () {
        // if($('#id-empresa').val() == "") {
        //     $('#alerta-no-cliente').removeClass('hidden');
        // }
        // else {
        //     $('#alerta-no-cliente').addClass('hidden');
        // }
    });

    loadEventHonorarios();

    function loadEventHonorarios() {
        $('#honorarios-modal').on('hide.bs.modal', function () {
            if($('#id-empresa').val() != "") {
                var honorarios = [];

                honorarios = wrapHonorarios();

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "lib/functions.php?action=saveHonorarios",
                    data: {
                        id_cliente: $('#id-empresa').val(),
                        honorarios: honorarios
                    },
                    success: function (data) {

                    },
                    error: function (e) {
                        console.log("Error: " + e.message);
                    }
                });
            }
            /*else {
             alert("Es necesario asignar algún cliente al presu antes de guardar honorarios");
             }*/

        });

        $('#honorarios-modal .form-control').unbind("change paste keyup");
        $('#honorarios-modal .form-control').bind("change paste keyup", function(){
            var totalHonorarios = 0;
            var totalHonorariosCoste = 0;
            $('#honorarios-modal tr.datos').each(function(index, elem){
                var total = parseFloat($(this).find('.rate').val()) * parseFloat($(this).find('.horas').val());
                var cost = parseFloat($(this).find('.cost').val()) * parseFloat($(this).find('.horas').val());
                $(this).find('.total').val(total.toFixed(2));
                totalHonorarios += total;
                totalHonorariosCoste += cost;
            });
            $('#honorarios-modal .total-honorarios').val(totalHonorarios.toFixed(2));
            $('#honorarios-modal .total-honorarios-coste').val(totalHonorariosCoste.toFixed(2));
            $('#honorarios-modal .total-honorarios-coef').val((totalHonorarios/totalHonorariosCoste).toFixed(2));
        });

        $('#export-honorarios').off('click');
        $('#export-honorarios').click(function(){
            exportHonorarios();
        });


        $('#honorarios-novartis-modal .form-control').unbind("change paste keyup");
        $('#honorarios-novartis-modal .form-control').bind("change paste keyup", function(){
            var totalHonorarios;
            var totalHonorariosTier = 0;
            var totalHonorariosFixed = 0;

            $('#honorarios-novartis-modal table.datos-tier tr.datos').each(function(index, elem){
                var total = parseFloat($(this).find('.rate').val()) * parseFloat($(this).find('.horas').val());
                $(this).find('.total').val(total.toFixed(2));
                totalHonorariosTier += total;
            });
            $('#honorarios-novartis-modal table.datos-fixed tr.datos').each(function(index, elem){
                var total = parseFloat($(this).find('.rate').val()) * parseFloat($(this).find('.horas').val());
                $(this).find('.total').val(total.toFixed(2));
                totalHonorariosFixed += total;
            });
            totalHonorarios = totalHonorariosTier + totalHonorariosFixed;
            $('#honorarios-novartis-modal #total-novartis').text(totalHonorarios.toFixed(2));
            $('#honorarios-novartis-modal #total-tier').text(totalHonorariosTier.toFixed(2));
            $('#honorarios-novartis-modal #total-fixed').text(totalHonorariosFixed.toFixed(2));
        });

        $('#export-honorarios-novartis').off('click');
        $('#export-honorarios-novartis').click(function(){
            exportHonorariosNovartis();
        });

        $('#reset-honorarios-novartis').off('click');
        $('#reset-honorarios-novartis').click(function(){
            $('#honorarios-novartis-modal table .horas').each(function(index, elem){
               $(this).val(0);
               $(this).trigger('change');
            });
        });
    }


    $('.add-concepto').on('click', function () {

        var concepto = $('#concepto_group_0').clone().hide();

        concepto.removeClass('hide');

        concepto.attr('id', 'concepto_group_' + counter);

        concepto.attr('data-index', counter);

        concepto.find('legend').text('Concepto ' + counter);

        concepto.find('#concepto_0').attr('id', 'concepto_' + counter).attr('name', 'concepto_' + counter);
        concepto.find('#concepto_0_precio').attr('id', 'concepto_' + counter + '_precio').attr('name', 'concepto_' + counter + '_precio');

        concepto.find('#concepto_sub_0').attr('id', 'concepto_sub_' + counter).attr('name', 'concepto_sub_' + counter);
        concepto.find('#concepto_sub_0_precio').attr('id', 'concepto_sub_' + counter + '_precio').attr('name', 'concepto_sub_' + counter + '_precio');

        concepto.find('#tit1_0').attr('id', 'tit1_' + counter).attr('name', 'tit1_' + counter);
        concepto.find('#tit1_0_precio').attr('id', 'tit1_' + counter + '_precio').attr('name', 'tit1_' + counter + '_precio');

        concepto.find('#tit2_0').attr('id', 'tit2_' + counter).attr('name', 'tit2_' + counter);
        concepto.find('#tit2_0_precio').attr('id', 'tit2_' + counter + '_precio').attr('name', 'tit2_' + counter + '_precio');

        concepto.find('#tit3_0').attr('id', 'tit3_' + counter).attr('name', 'tit3_' + counter);
        concepto.find('#tit3_0_precio').attr('id', 'tit3_' + counter + '_precio').attr('name', 'tit3_' + counter + '_precio');

        concepto.find('#texto_0').attr('id', 'texto_' + counter).attr('name', 'texto_' + counter);
        concepto.find('#texto_0_precio').attr('id', 'texto_' + counter + '_precio').attr('name', 'texto_' + counter + '_precio');

        concepto.insertBefore($(this)).fadeIn();

        counter++;
        loadEvents();
    });

    $('.add-total').on('click', function () {

        if($('.item-total').length == 0) {
            var concepto = $('#concepto_group_0').clone().hide();

            concepto.removeClass('hide');

            concepto.attr('id', 'concepto_group_' + counter);

            concepto.attr('data-index', counter);

            concepto.find('legend').text('Concepto ' + counter);

            concepto.find('#concepto_0').attr('id', 'concepto_' + counter).attr('name', 'concepto_' + counter).attr('disabled', 'disabled').val('TOTAL').addClass('item-total');
            concepto.find('#concepto_0_precio').attr('id', 'concepto_' + counter + '_precio').attr('name', 'concepto_' + counter + '_precio').attr('data-sumar', 0);

            concepto.find('#concepto_sub_0').parent().parent().parent().parent().remove();
            concepto.find('#concepto_sub_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#tit1_0').parent().parent().parent().parent().remove();
            concepto.find('#tit1_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#tit2_0').parent().parent().parent().parent().remove();
            concepto.find('#tit2_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#tit3_0').parent().parent().parent().parent().remove();
            concepto.find('#tit3_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#texto_0').parent().parent().parent().parent().remove();
            concepto.find('#texto_0_precio').parent().parent().parent().parent().remove();

            concepto.insertBefore($('#insert-concepto')).fadeIn();

            $('#concepto_' + counter + '_precio').parent().find('.toggle-suma').remove();

            counter++;
            loadEvents();
        }
    });

    $('.add-pgbreak').on('click', function () {

        // if($('.item-total').length == 0) {
            var concepto = $('#concepto_group_0').clone().hide();

            concepto.removeClass('hide');

            concepto.attr('id', 'concepto_group_' + counter);

            concepto.attr('data-index', counter);

            concepto.find('legend').text('Concepto ' + counter);

            concepto.find('#concepto_0').attr('id', 'concepto_' + counter).attr('name', 'concepto_' + counter).attr('disabled', 'disabled').val('pgbreak');
            //concepto.find('#concepto_0_precio').attr('id', 'concepto_' + counter + '_precio').attr('name', 'concepto_' + counter + '_precio').attr('data-sumar', 0);

            concepto.find('#concepto_sub_0').parent().parent().parent().parent().remove();
            concepto.find('#concepto_sub_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#tit1_0').parent().parent().parent().parent().remove();
            concepto.find('#tit1_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#tit2_0').parent().parent().parent().parent().remove();
            concepto.find('#tit2_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#tit3_0').parent().parent().parent().parent().remove();
            concepto.find('#tit3_0_precio').parent().parent().parent().parent().remove();

            concepto.find('#texto_0').parent().parent().parent().parent().remove();
            concepto.find('#texto_0_precio').parent().parent().parent().parent().remove();

            concepto.insertBefore($('#insert-concepto')).fadeIn();

            $('#concepto_' + counter + '_precio').parent().find('.toggle-suma').remove();

            counter++;
            loadEvents();
        // }
    });

    /**
     * Generar presupuesto
     */

    var preview = false;

    $('#preview-presu, #preview-fact').on('click', function (e) {
        preview = true;
    });

    $('#preview-presu-link').on('click', function (e) {
        $('#preview-presu').click();
    });

    $('#save-presu-link').on('click', function (e) {
        $('#save-presu').click();
    });

    $('#preview-fact-link').on('click', function (e) {
        $('#preview-fact').click();
    });

    $('#save-fact-link').on('click', function (e) {
        $('#save-fact').click();
    });

    //submit formulario y comprobar campos
    $('#presupuesto-form').submit(function (e) {

        var conceptos = [];

        conceptos = wrapConceptos();

        if (preview) {
            preview = false;

            var $hidden = $("<input type='hidden' name='conceptos'/>");
            $hidden.val(JSON.stringify(conceptos));
            $(this).append($hidden); //e.preventDefault();console.log(conceptos);return;
        }
        else {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "lib/functions.php?action=savePresu",
                data: {
                    ref_presu: $('#ref').val(),
                    fecha: $('#fecha').val(),
                    cliente: $('#nombre-cliente').val(),
                    ref_cliente: $('#ref-empresa').val(),
                    direccion: $('#direccion-cliente').val(),
                    cif: $('#cif-cliente').val(),
                    cp: $('#cp-cliente').val(),
                    contacto: $('#contacto').val(),
                    propuesta: $('#propuesta').val(),
                    id_proyecto: $('#id_proyecto').val(),
                    empresa: $('#id-empresa').val(),
                    empresa_orig: $('#id-empresa-orig').val(),
                    suma: $('#suma .valor').text(),
                    conceptos: conceptos,
                    english: $('#export-en').is(':checked')?1:0,
                },
                success: function (data) {
                    if($('#export-en').is(':checked')) {
                        window.open('lib/pdf_en.php?id=' + data.id_presu);
                    }
                    else
                        window.open('lib/pdf.php?id=' + data.id_presu);

                    window.location = 'index.php';
                },
                error: function (e) {
                    console.log("Error: " + e.message);
                }
            });
            e.preventDefault();
        }
    });

    /**
     * Generar Factura
     */

    //submit formulario y comprobar campos
    $('#factura-form').submit(function (e) {

        var conceptos = [];

        conceptos = wrapConceptos();

        if (preview) {
            preview = false;

            var $hidden = $("<input type='hidden' name='conceptos'/>");
            $hidden.val(JSON.stringify(conceptos));
            $(this).append($hidden); //e.preventDefault();console.log(conceptos);return;

            var $subtotal = $("<input type='hidden' name='subtotal'/>");
            $subtotal.val($('#suma-fact .valor.subtotal').text());
            $(this).append($subtotal);

            var $iva = $("<input type='hidden' name='iva'/>");
            $iva.val($('#suma-fact .valor.iva').text());
            $(this).append($iva);

            var $total = $("<input type='hidden' name='total'/>");
            $total.val($('#suma-fact .valor.total').text());
            $(this).append($total);
        }
        else {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "lib/functions.php?action=saveFact",
                data: {
                    fecha_emision: $('#fecha_emision').val(),
                    fecha_vencimiento: $('#fecha_vencimiento').val(),
                    ref_cliente: $('#ref-empresa').val(),
                    ref_compras: $('#ref_compras').val(),
                    nombre_cliente: $('#nombre-cliente').val(),
                    direccion_cliente: $('#direccion-cliente').val(),
                    cif_cliente: $('#cif-cliente').val(),
                    cp_cliente: $('#cp-cliente').val(),
                    condiciones_pago: $('#condiciones-pago').val(),
                    datos_bancarios: $('#entidad').val(),
                    presupuesto_asoc: $('#ref_presu').val(),
                    subtotal: $('#suma-fact .valor.subtotal').text(),
                    iva: $('#suma-fact .valor.iva').text(),
                    total: $('#suma-fact .valor.total').text(),
                    conceptos: conceptos,
                    english: $('#export-en').is(':checked')?1:0,
                    noiva: $('#export-no-iva').is(':checked')?1:0,
                    owner: $('#owner').val()
                },
                complete: function(data) {
                    console.log(data);
                },
                success: function (data) {
                    if($('#export-en').is(':checked')) {
                        if($('#export-no-iva').is(':checked')) {
                            window.open('lib/pdf-fact_en.php?noiva=1&id=' + data);
                        }
                        else {
                            window.open('lib/pdf-fact_en.php?id=' + data);
                        }
                    }
                    else {
                        if ($('#export-no-iva').is(':checked')) {
                            window.open('lib/pdf-fact.php?noiva=1&id=' + data);
                        }
                        else {
                            window.open('lib/pdf-fact.php?id=' + data);
                        }
                    }

                    window.location = 'index.php';
                },
                error: function (e) {
                    console.log("Error: " + e.message);
                }
            });
            e.preventDefault();
        }
    });

    /**
     * Modificar presupuesto
     */

    //submit formulario y comprobar campos
    $('#update-presupuesto-form').submit(function (e) {

        var conceptos = [];

        conceptos = wrapConceptos();

        if (preview) {
            preview = false;

            if($('#export-en').is(':checked')) {
                $(this).attr('action', "lib/pdf_en.php?preview=1");
            }
            else{
                $(this).attr('action', "lib/pdf.php?preview=1");
            }

            var $hidden = $("<input type='hidden' name='conceptos'/>");
            $hidden.val(JSON.stringify(conceptos));
            $(this).append($hidden); //e.preventDefault();console.log(conceptos);return;
        }
        else {

            $.ajax({
                type: "POST",
                dataType: "json",
                url: "lib/functions.php?action=updatePresu",
                data: {
                    ref_presu: $('#ref').val(),
                    fecha: $('#fecha').val(),
                    cliente: $('#nombre-cliente').val(),
                    ref_cliente: $('#ref-empresa').val(),
                    direccion: $('#direccion-cliente').val(),
                    cif: $('#cif-cliente').val(),
                    cp: $('#cp-cliente').val(),
                    contacto: $('#contacto').val(),
                    propuesta: $('#propuesta').val(),
                    id_proyecto: $('#id_proyecto').val(),
                    empresa: $('#id-empresa').val(),
                    empresa_orig: $('#id-empresa-orig').val(),
                    id: $('#id_presupuesto').val(),
                    suma: $('#suma .valor').text(),
                    conceptos: conceptos,
                    english: $('#export-en').is(':checked')?1:0,
                },
                success: function (output) {
                    console.log(output);
                    $('#ref').val(output.ref_presu);
                    var ref_orig = output.ref_presu_orig;
                    var ref_final = output.ref_presu;

                    if(output.facturas !== undefined) {
                        alert("¡Atención! Ya existen facturas emitidas para este presupuesto: " + output.facturas + '. Por favor verifica los totales facturados y presupuestados');

                        if($('#id-empresa').val() != $('#id-empresa-orig').val()) {
                            if (confirm("Se ha modificado el cliente asociado al presupuesto. Actualizar en las facturas existentes?")) {

                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    url: "lib/functions.php?action=updateClienteFact",
                                    data: {
                                        id_presu: $('#id_presupuesto').val(),
                                        ref_presu_orig: ref_orig,
                                        ref_presu: ref_final,
                                        cliente: $('#nombre-cliente').val(),
                                        ref_cliente: $('#ref-empresa').val(),
                                        direccion: $('#direccion-cliente').val(),
                                        cif: $('#cif-cliente').val(),
                                        cp: $('#cp-cliente').val(),
                                    },
                                    success: function (data) {
                                        //ok
                                    },
                                    error: function (e) {
                                        console.log("Error: " + e.message);
                                    }
                                });

                            }
                        }
                    }
                    if ($('#export-en').is(':checked')) {
                        window.open('lib/pdf_en.php?id=' + output.id_presu);
                    }
                    else
                        window.open('lib/pdf.php?id=' + output.id_presu);
                },
                error: function (e) {
                    console.log("Error: " + e.message);
                }
            });
            e.preventDefault();
        }
    });

    /**
     * Modificar factura
     */

    //submit formulario y comprobar campos
    $('#update-factura-form').submit(function (e) {

        var conceptos = [];

        conceptos = wrapConceptos();

        if (preview) {
            preview = false;

            if($('#export-en').is(':checked')) {
                if($('#export-no-iva').is(':checked')) {
                    $(this).attr('action', "lib/pdf-fact_en.php?preview=1&noiva=1");
                }
                else {
                    $(this).attr('action', "lib/pdf-fact_en.php?preview=1");
                }
            }
            else{
                if($('#export-no-iva').is(':checked')) {
                    $(this).attr('action', "lib/pdf-fact.php?preview=1&noiva=1");
                }
                else {
                    $(this).attr('action', "lib/pdf-fact.php?preview=1");
                }
            }

            var $hidden = $("<input type='hidden' name='conceptos'/>");
            $hidden.val(JSON.stringify(conceptos));
            $(this).append($hidden); //e.preventDefault();console.log(conceptos);return;

            var $subtotal = $("<input type='hidden' name='subtotal'/>");
            $subtotal.val($('#suma-fact .valor.subtotal').text());
            $(this).append($subtotal);

            var $iva = $("<input type='hidden' name='iva'/>");
            $iva.val($('#suma-fact .valor.iva').text());
            $(this).append($iva);

            var $total = $("<input type='hidden' name='total'/>");
            $total.val($('#suma-fact .valor.total').text());
            $(this).append($total);
        }
        else {

            $.ajax({
                type: "POST",
                dataType: "json",
                url: "lib/functions.php?action=updateFact",
                data: {
                    id: $('#id_factura').val(),
                    fecha_emision: $('#fecha_emision').val(),
                    fecha_vencimiento: $('#fecha_vencimiento').val(),
                    ref_cliente: $('#ref-empresa').val(),
                    nombre_cliente: $('#nombre-cliente').val(),
                    direccion_cliente: $('#direccion-cliente').val(),
                    cif_cliente: $('#cif-cliente').val(),
                    cp_cliente: $('#cp-cliente').val(),
                    condiciones_pago: $('#condiciones-pago').val(),
                    datos_bancarios: $('#entidad').val(),
                    presupuesto_asoc: $('#ref_presu').val(),
                    subtotal: $('#suma-fact .valor.subtotal').text(),
                    iva: $('#suma-fact .valor.iva').text(),
                    total: $('#suma-fact .valor.total').text(),
                    conceptos: conceptos,
                    english: $('#export-en').is(':checked')?1:0,
                    noiva: $('#export-no-iva').is(':checked')?1:0,
                    owner: $('#owner').val()
                },
                success: function (data) {
                    if($('#export-en').is(':checked')) {
                        if ($('#export-no-iva').is(':checked')) {
                            window.open('lib/pdf-fact_en.php?noiva=1&id=' + data);
                        }
                        else {
                            window.open('lib/pdf-fact_en.php?id=' + data);
                        }
                    }
                    else {
                        if ($('#export-no-iva').is(':checked')) {
                            window.open('lib/pdf-fact.php?noiva=1&id=' + data);
                        }
                        else {
                            window.open('lib/pdf-fact.php?id=' + data);
                        }
                    }

                    window.location = 'index.php';
                },
                error: function (e) {
                    console.log("Error: " + e.message);
                }
            });
            e.preventDefault();
        }
    });

    /**
     * Eliminar presupuesto
     */
    $('.delete-presupuesto').on('click', function (e) {

        $('#ref-presu').text($(this).data('ref'));
        $('#confirmar-si').data('id', $(this).data('id'));

        $('#confirmar-modal').modal('show');

        $('#confirmar-si').on('click', function (e) {

            $.ajax({
                type: "POST",
                url: "lib/functions.php?action=deletePresu",
                data: {
                    id: $(this).data('id')
                },
                success: function (data) {
                    window.location.reload();
                },
                error: function (e) {
                    console.log("Error: " + e.message);
                }
            });

        });
        e.preventDefault();
    });

    /**
     * No aceptar presupuesto
     */
    $('.noaceptar-presupuesto').on('click', function (e) {

        $.ajax({
            type: "POST",
            url: "lib/functions.php?action=denyPresu",
            data: {
                id: $(this).data('id')
            },
            success: function (data) {
                window.location.reload();
            },
            error: function (e) {
                console.log("Error: " + e.message);
            }
        });

        e.preventDefault();
    });

    /**
     * Eliminar factura
     */
    $('.delete-factura').on('click', function (e) {
        $('#ref-fact').text($(this).data('ref'));
        $('#confirmar-fact-si').data('id', $(this).data('id'));
        $('#confirmar-fact-si').data('presu', $(this).data('presu'));
        $('#confirmar-fact-si').data('ref', $(this).data('ref'));
        $('#razon-abono').val('');
        $('#error-razon').hide();

        $('#confirmar-modal-fact').modal('show');

        $('#confirmar-fact-si').on('click', function (e) {

          if($('#razon-abono').val() !== '') {
              $('#confirmar-modal-fact').modal('hide');
              $('#error-razon').hide();
              $.ajax({
                type: "POST",
                url: "lib/functions.php?action=deleteFact",
                data: {
                  id: $(this).data('id'),
                  presu: $(this).data('presu'),
                  ref: $(this).data('ref'),
                  razon: $('#razon-abono').val()
                },
                success: function (data) {
                  window.location.reload();
                },
                error: function (e) {
                  console.log("Error: " + e.message);
                }
              });
            }
          else {
              $('#error-razon').show();
          }

        });
        e.preventDefault();
    });

    /**
     * Factura cobrada
     */
    $('.factura-cobrada').on('click', function (e) {

        $.ajax({
            type: "POST",
            url: "lib/functions.php?action=cobrarFact",
            data: {
                id: $(this).data('id'),
                presu: $(this).data('presu'),
                subtotal: $(this).data('subtotal')
            },
            /*complete: function(data) {
                console.log(data);
            }*/
            success: function (data) {
                window.location.reload();
            },
            error: function (e) {
                console.log("Error: " + e.message);
            }
        });

        e.preventDefault();
    });

    $('.obs-factura').on('click', function(e) {

        $('#obs-form #id').val($(this).data('id'));
        $('#obs-form #fact-ref').val($(this).data('ref'));
        $('#obs-ref').text($(this).data('ref'));
        $('#obs-text').text($(this).data('obs'));
        $('#obs-modal').modal('show');

        e.preventDefault();
    });

    $('#obs-form').ajaxForm(function() {window.location.reload();});
    $('#save-obs').on('click', function(e) {
        $('#obs-form').submit();
    });

    /**
     * Duplicar presupuesto
     */
    $('.copy-presupuesto').on('click', function (e) {

        $.ajax({
            type: "POST",
            url: "lib/functions.php?action=copyPresu",
            data: {
                id: $(this).data('id'),
                origen : $(this).data('origen')
            },
            success: function (data) {
                window.location.reload();
            },
            error: function (e) {
                console.log("Error: " + e.message);
            }
        });

        e.preventDefault();
    });

    /**
     * Orden de compra
     */
    $('#po-form').ajaxForm(function() {window.location.reload();});

    $('#save-po').on('click', function (e) {
        $('#po-form').submit();
    });

    $('.po-presupuesto').on('click', function(e) {

        $('#po-form #id').val($(this).data('id'));
        $('#po-form #presu-ref').val($(this).data('ref'));

        $('#po-modal').modal('show');

        $.ajax({
            type: "POST",
            url: "lib/functions.php?action=getPO",
            data: {
                id: $(this).data('id')
            },
            success: function (data) {
                var result = JSON.parse(data);
                $('#po-ref').val(result.po_ref);

                if(result.po_file) {
                    $('#po-file-current').attr('href', 'po/'+result.po_file);
                    $('#po-file-current').text(result.po_file);
                    $('#po-file-current').css('display', 'block')
                }
                else {
                    $('#po-file-current').css('display', 'none')
                    $('#po-file-current').attr('href', '');
                    $('#po-file-current').text('');
                }
            }
        });

        e.preventDefault();
    });

    /**
     * Buscar en catálogo
     */

    var conceptos_wiz = [];

    $('#search-catalog').on('click', function (e) {

        $.ajax({
            type: "POST",
            url: "lib/functions.php?action=searchCatalog",
            data: {
                text: $('#texto-busqueda').val()
            },
            success: function (data) {
                $('#catalog-results tbody').html(data);

                var numPags = 0;

                if ($("#catalog-results tr").length > 0) {
                    numPags = $("#catalog-results tr.cat-concepto").first().data('paginas');
                }

                $('#catalog-pagination').bootpag({
                    total: numPags,
                    next: null,
                    prev: null
                }).on("page", function (event, /* page number here */ num) {
                    $("#catalog-results tr.cat-concepto").each(function () {
                        if ($(this).data('pagina') == num && $(this).hasClass('hide'))
                            $(this).removeClass('hide');
                        else if (!$(this).hasClass('hide'))
                            $(this).addClass('hide');
                    });
                });

                $('#catalog-results .add').on('click', function (e) {

                    var el = $(this);

                    conceptos_wiz.push(
                        {
                            concepto: el.data('concepto'),
                            concepto_subtitulo: el.data('concepto_subtitulo'),
                            titulo1: el.data('titulo1'),
                            titulo2: el.data('titulo2'),
                            titulo3: el.data('titulo3'),
                            texto: el.data('texto'),
                            precio_concepto: el.data('precio_concepto'),
                            precio_concepto_subtitulo: el.data('precio_concepto_subtitulo'),
                            precio_titulo1: el.data('precio_titulo1'),
                            precio_titulo2: el.data('precio_titulo2'),
                            precio_titulo3: el.data('precio_titulo3'),
                            precio_texto: el.data('precio_texto')
                        }
                    );

                    var row = $(this).parent().parent();
                    row.animate({
                        "opacity": "0",
                        "duration": 1000
                    }, function () {
                        row.remove();
                    });
                });
            },
            error: function (e) {
                console.log("Error: " + e.message);
            }
        });

        $.ajax({
            type: "POST",
            url: "lib/functions.php?action=searchArchive",
            data: {
                text: $('#texto-busqueda').val()
            },
            success: function (data) {
                $('#archive-results tbody').html(data);

                var numPags = 0;

                if ($("#archive-results tr").length > 0) {
                    numPags = $("#archive-results tr.cat-concepto").first().data('paginas');
                }

                /*$('#archive-pagination').bootpag({
                    total: numPags,
                    next: null,
                    prev: null
                }).on("page", function (event, /* page number here *//* num) {
                    $("#archive-results tr.cat-concepto").each(function () {
                        if ($(this).data('pagina') == num && $(this).hasClass('hide'))
                            $(this).removeClass('hide');
                        else if (!$(this).hasClass('hide'))
                            $(this).addClass('hide');
                    });
                });*/

                $('#archive-results .add').on('click', function (e) {

                    var el = $(this);

                    conceptos_wiz.push(
                        {
                            concepto: el.data('concepto'),
                            concepto_subtitulo: el.data('concepto_subtitulo'),
                            titulo1: el.data('titulo1'),
                            titulo2: el.data('titulo2'),
                            titulo3: el.data('titulo3'),
                            texto: el.data('texto'),
                            precio_concepto: el.data('precio_concepto'),
                            precio_concepto_subtitulo: el.data('precio_concepto_subtitulo'),
                            precio_titulo1: el.data('precio_titulo1'),
                            precio_titulo2: el.data('precio_titulo2'),
                            precio_titulo3: el.data('precio_titulo3'),
                            precio_texto: el.data('precio_texto')
                        }
                    );

                    var row = $(this).parent().parent();
                    row.animate({
                        "opacity": "0",
                        "duration": 1000
                    }, function () {
                        row.remove();
                    });
                });

            },
            error: function (e) {
                console.log("Error: " + e.message);
            }
        });

        e.preventDefault();
    });


    $('#wizard-next').on('submit', function (e) {

        if($('#conceptos').val().length > 0) {
            var conceptos = JSON.parse($('#conceptos').val());
            $('#conceptos').val(JSON.stringify(conceptos.concat(conceptos_wiz)));
        }
        else {
            $('#conceptos').val(JSON.stringify(conceptos_wiz));
        }
    });

    $('#wizard-prev').on('submit', function (e) {

        var $hidden = $("<input type='hidden' name='conceptos'/>");
        var conceptos = wrapConceptos();

        $hidden.val(JSON.stringify(conceptos));
        $(this).append($hidden);

        $('#referencia').val($('#ref').val());
        $('#fechapresu').val($('#fecha').val());
        $('#empresa').val($('#id-empresa').val());
        $('#ref_empresa').val($('#ref-empresa').val());
        $('#cliente').val($('#nombre-cliente').val());
        $('#direccion').val($('#direccion-cliente').val());
        $('#cif').val($('#cif-cliente').val());
        $('#cp').val($('#cp-cliente').val());
        $('#contactocl').val($('#contacto').val());
        $('#tpropuesta').val($('#propuesta').val());
        $('#idproyecto').val($('#id_proyecto').val());
        $('#tpropuesta').val($('#propuesta').val());
        $('#total').val($('#sumag').val());
        //$('#suma-vis').text($('#total').val());
    });

    /*** Calc honorarios ***/
    /*$('#honorarios-modal .form-control').bind("change paste keyup", function(){
        var totalHonorarios = 0;
        $('#honorarios-modal tr.datos').each(function(index, elem){
            var total = parseFloat($(this).find('.rate').val()) * parseFloat($(this).find('.horas').val());
            $(this).find('.total').val(total.toFixed(2));
            totalHonorarios += total;
        });
        $('#honorarios-modal .total-honorarios').val(totalHonorarios.toFixed(2));
    });

    $('#export-honorarios').click(function(){
        exportHonorarios();
    });*/

    function exportHonorarios() {
        var textoHonorarios = '';
        $('#honorarios-modal tr.datos').each(function(index, elem){
            var valor = parseFloat($(this).find('.total').val());
            if(valor != 0) {
                var linea = $(this).find('.cargo').text() + " (" + $(this).find('.horas').val() + " horas): " + valor + " €\n";
                textoHonorarios += linea;
            }
        });

        var concepto = $('#concepto_group_0').clone().hide();

        concepto.removeClass('hide');

        concepto.attr('id', 'concepto_group_' + counter);

        concepto.attr('data-index', counter);

        concepto.find('legend').text('Concepto ' + counter);

        concepto.find('#concepto_0').val('HONORARIOS');
        concepto.find('#concepto_0_precio').val($('#honorarios-modal .total-honorarios').val());

        concepto.find('#concepto_0').attr('id', 'concepto_' + counter).attr('name', 'concepto_' + counter);
        concepto.find('#concepto_0_precio').attr('id', 'concepto_' + counter + '_precio').attr('name', 'concepto_' + counter + '_precio');

        concepto.find('.wrap-concepto[data-tipo="concepto_subtitulo"]').remove();
        concepto.find('.wrap-concepto[data-tipo="titulo1"]').remove();
        concepto.find('.wrap-concepto[data-tipo="titulo2"]').remove();
        concepto.find('.wrap-concepto[data-tipo="titulo3"]').remove();

        concepto.find('#texto_0').text(textoHonorarios);
        concepto.find('#texto_0').attr('id', 'texto_' + counter).attr('name', 'texto_' + counter);
        concepto.find('#texto_0_precio').attr('id', 'texto_' + counter + '_precio').attr('name', 'texto_' + counter + '_precio');

        concepto.insertBefore($('button.add-concepto')).fadeIn();

        counter++;
        loadEvents();
    }

    function exportHonorariosNovartis() {
        var textoHonorarios = '';
        $('#honorarios-novartis-modal tr.datos').each(function(index, elem){
            var valor = parseFloat($(this).find('.total').val());
            var rate = parseFloat($(this).find('.rate').val());
            if(valor != 0) {
                var horas_element = $(this).find('.horas');
                if(horas_element.data('descripcion') == 'hours') {
                    var linea = $(this).find('.cargo').text() + " (" + horas_element.val() + " hours): " + valor + " €\n";

                }
                else {
                    var linea = $(this).find('.cargo').text() + " (" + horas_element.val() + " x "+ rate + " € " + horas_element.data('descripcion')+"): " + valor + " €\n";
                }
                textoHonorarios += linea;
            }
        });

        var concepto = $('#concepto_group_0').clone().hide();

        concepto.removeClass('hide');

        concepto.attr('id', 'concepto_group_' + counter);

        concepto.attr('data-index', counter);

        concepto.find('legend').text('Concepto ' + counter);

        concepto.find('#tit1_0').val('HONORARIOS NOVARTIS');
        concepto.find('#tit1_0_precio').val($('#honorarios-novartis-modal #total-novartis').text());

        concepto.find('#tit1_0').attr('id', 'tit1_' + counter).attr('name', 'tit1_' + counter);
        concepto.find('#tit1_0_precio').attr('id', 'tit1_' + counter + '_precio').attr('name', 'tit1_' + counter + '_precio');


        concepto.find('.wrap-concepto[data-tipo="concepto"]').remove();
        concepto.find('.wrap-concepto[data-tipo="concepto_subtitulo"]').remove();
        concepto.find('.wrap-concepto[data-tipo="titulo2"]').remove();
        concepto.find('.wrap-concepto[data-tipo="titulo3"]').remove();

        concepto.find('#texto_0').text(textoHonorarios);
        concepto.find('#texto_0').attr('id', 'texto_' + counter).attr('name', 'texto_' + counter);
        concepto.find('#texto_0_precio').attr('id', 'texto_' + counter + '_precio').attr('name', 'texto_' + counter + '_precio');

        concepto.insertBefore($('button.add-concepto')).fadeIn();

        counter++;
        loadEvents();
    }

    /*FILE UPLOAD*/
    //Crea una carpeta con el ID el Debbrief si ya existe o el siguiente ID que toca en BBDD
    var url = 'lib/functions.php?action=uploadFiles&otherDir='+$('#ref').val();

    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url).text(file.name);
                $('<p/>').html(link).appendTo('#files');
            });
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');


    $.ajax({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: $('#fileupload').fileupload('option', 'url'),
        dataType: 'json',
        context: $('#fileupload')[0]
    }).always(function () {
        $(this).removeClass('fileupload-processing');
    }).done(function (result) {
        $(this).fileupload('option', 'done')
            .call(this, $.Event('done'), {result: result});
    });
    /*END FILE UPLOAD*/

    Date.prototype.getWeekNumber = function(){
        var d = new Date(+this);
        d.setHours(0,0,0,0);
        d.setDate(d.getDate()+4-(d.getDay()||7));
        return Math.ceil((((d-new Date(d.getFullYear(),0,1))/8.64e7)+1)/7);
    };

    //Edit contenteditable table
    //var message_status = $("#status");
    if($('.coeficientes').length > 0) {
        var hoy = new Date();
        var semanaActual = hoy.getWeekNumber();
        //console.log(hoy.getWeekNumber());
    }

    $(".horas-proyecto td[contenteditable=true]").on('blur', function(){
        var fields = $(this).attr("id") ;
        var value = $(this).text();

        //console.log('enviar '+fields + "=" + value);

        $.post('lib/functions.php?action=updateHoras' , fields + "=" + value, function(data){
            if(data != '')
            {
                console.log('output: '+data);
                //message_status.show();
                //message_status.text(data);
                //hide the message
                //setTimeout(function(){message_status.hide()},3000);
            }
        });
    });

    $(".salarios td[contenteditable=true]").on('blur', function(){
        var userid = $(this).data("userid") ;
        var salario = $(this).text();

        // console.log('user '+userid + "=" + salario);

        $.post('lib/functions.php?action=updateSalario' , {"userid":userid,"salario":salario}, function(data){
            if(data != '')
            {
                console.log('output: '+data);
            }
        });
    });

    $(".coef-bruto td.costes[contenteditable=true]").on('focus', function(){
        var valor = (parseFloat($(this).data('value'))).toFixed(2);
        $(this).text(valor);
    });
    $(".coef-bruto td.costes[contenteditable=true]").on('blur', function(){
        //console.log(parseFloat($(this).text()));
        var valor = (parseFloat($(this).text())).formatMoney(2);
        $(this).data('value', $(this).text());
        $(this).text(valor);
        updateCoeficiente($(this).data('semana'));
    });

    $(".coef-bruto td.costes-extra[contenteditable=true]").on('focus', function(){
        var valor = (parseFloat($(this).data('value'))).toFixed(2);
        $(this).text(valor);
    });
    $(".coef-bruto td.costes-extra[contenteditable=true]").on('blur', function(){
        //console.log($(this).text());
        var valor = (parseFloat($(this).text())).formatMoney(2);
        $(this).data('value', $(this).text());
        $(this).text(valor);
        updateCoeficiente($(this).data('semana'));
    });

    function updateCoeficiente(semana) {
        var ganados = parseFloat($('.semana-'+semana+' .ganados').data('value'));
        var costes = parseFloat($('.semana-'+semana+' .costes').data('value'));
        //console.log(costes);
        var costesExtra = parseFloat($('.semana-'+semana+' .costes-extra').data('value'));

        var coef = 1-((costes+costesExtra)/ganados);
        if(ganados == 0.00) {
            coef = -1.00;
        }

        $('.semana-'+semana+' .coef').data('value',coef.toFixed(2));
        $('.semana-'+semana+' .coef').text(coef.formatMoney(2));

        //console.log(coef.formatMoney(2));
        //guardar en db
        var fields = costes.toFixed(2)+':'+costesExtra.toFixed(2)+':'+coef.toFixed(2);

        // console.log('enviar '+fields + "=" + semana);

        $.post('lib/functions.php?action=updateCostes' , fields + "=" + semana, function(data){
            if(data != '')
            {
                console.log('output: '+data);
            }
        });
    }

    /** Recogida horas **/

    if($('.recogida-horas').length > 0) {
        $('#dfecha').bootstrapDP('update', new Date());

        $('#dproyecto').typeahead({
            source: function (query, response) {
                return $.getJSON("lib/functions.php?action=searchProyecto2&text=" + query,
                    function (result, status) {
                        //console.log(result);
                        response($.map(result, function (item) {
                            return {
                                id: item.id,
                                label: item.nombre,
                                ref_proyecto: item.ref_proyecto
                            }
                        }));
                    });
            },
            onselect: function (element, obj) {
                element.val(obj.label);
                $('#did_proyecto').val(obj.id);
            },
            property: "label",
            minLength: 2
        });

        $('#input_horas').submit(function (e) {
            e.preventDefault();
            if($('#dusuario').val() != '' && $('#did_proyecto').val() != '' && $('#ndeliverable').val() != '' && $('#dfecha').val() != '' && $('#nhoras').val() != '') {
                var horas = $('#nhoras').val();
                horas = horas.replace(',','.');
                horas = parseFloat(horas);
                $.ajax({
                    type: "POST",
                    //dataType: "json",
                    url: "lib/functions.php?action=saveHoras",
                    data: {
                        dusuario: $('#dusuario').val(),
                        did_proyecto: $('#did_proyecto').val(),
                        ndeliverable: $('#ndeliverable').val(),
                        dfecha: $('#dfecha').val(),
                        nhoras: horas,
                    },
                    success: function (data) {
                        //ok
                        $('#horas_guardadas').append(data);
                        $('#ndeliverable').val('');
                        $('#nhoras').val('');
                    },
                    error: function (e) {
                        console.log("Error: " + e.message);
                    }
                });
            }
        });
    }

    /* Alta cliente empresa */

    if($('.alta-empresa').length > 0) {
        $('#formentidad').submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "lib/functions.php?action=altaCliente",
                data: {
                    nombre: $('#nombre').val(),
                    direccion: $('#direccion').val(),
                    cp: $('#cp').val(),
                    cif: $('#cif').val()
                },
                success: function (data) {
                    //ok
                    $('.clienteok').text($('#nombre').val());
                    $('.alert-success').show();
                    // window.location.reload();
                    $('#formentidad').trigger("reset");
                },
                error: function (e) {
                    $('.clienteok').text($('#nombre').val());
                    $('.alert-danger').show();
                    console.log("Error: " + e.message);
                }
            });
        });
    }

    /* Edit cliente empresa */

    if($('.edit-empresa').length > 0) {
        $('#nombre').typeahead({
            source: function (query, response) {
                return $.getJSON("lib/functions.php?action=searchClient&text=" + query,
                    function (result, status) {
                        //console.log(result);
                        response($.map(result, function (item) {
                            return {
                                id: item.id,
                                label: item.nombre,
                                ref_cliente: item.ref_cliente,
                                direccion: item.direccion,
                                cp: item.cp,
                                cif: item.cif
                            }
                        }));
                    });
            },
            onselect: function (element, obj) {
                element.val(obj.label);
                $('#did_empresa').val(obj.id);
                $('#direccion').val(obj.direccion);
                $('#cp').val(obj.cp);
                $('#cif').val(obj.cif);
            },
            property: "label",
            minLength: 2
        });

        $('#formentidad').submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "lib/functions.php?action=updateCliente",
                data: {
                    nombre: $('#nombre').val(),
                    id: $('#did_empresa').val(),
                    direccion: $('#direccion').val(),
                    cp: $('#cp').val(),
                    cif: $('#cif').val()
                },
                success: function (data) {
                    //ok
                    $('.clienteok').text($('#nombre').val());
                    $('.alert-success').show();
                    // window.location.reload();
                    $('#formentidad').trigger("reset");
                },
                error: function (e) {
                    $('.clienteok').text($('#nombre').val());
                    $('.alert-danger').show();
                    console.log("Error: " + e.message);
                }
            });
        });
    }

});
