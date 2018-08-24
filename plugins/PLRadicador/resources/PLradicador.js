$(document).ready(function(){
        //muestra div si estan checkeados
        $("input:checkbox").each(function(){
                if(this.checked){
                    $("#"+this.name+"_fields").show();
                }else{
                    $("#"+this.name+"_fields").hide();
                }
        });
        prev();

        //muestra/esconde si check cambia
        $('input[type=checkbox]').click(function(){
            if(this.checked){
                $("#"+this.name+"_fields").show(250);
            }else{
                $("#"+this.name+"_fields").hide(250);
            }
        });

        //configura los colorpickers
        $(".div_colorpicker").minicolors({
            position:'bottom right'
        });

        //acciones del formulario
        $("#salva").click(function(){
            if($("#rad_id").val() == ""){
                alert("No se ha seleccionado ningun tipo de radicación.");
                return;
            }

            if($("#largo").val() == "" || $("#alto").val() == "" || $("#labels_form input:checked").length == 0){
                alert("Se necesita al menos ancho, alto y algun contenido para la etiqueta.");
            }else{
            //llama a preview para generar el thmbnail
                $("#savethumbnail").val('true');
                prev();
                alert('Configuración de código guardada.');
                $("#labels_form").submit();
            }
        });

        function prev(){
            var params;
            params = 'rad_id='+$('#rad_id').val();
            params += '&orientacion='+$('#orientacion').val();
            params += '&alto='+$('#alto').val();
            params += '&largo='+$('#largo').val();
            params += '&cb='+$('#cb').prop('checked');
            params += '&cb_h='+$('#cb_h').val();
            params += '&cb_v='+$('#cb_v').val();
            params += '&qr='+$('#qr').prop('checked');
            params += '&qr_h='+$('#qr_h').val();
            params += '&qr_v='+$('#qr_v').val();
            params += '&fecha='+$('#fecha_radicacion').prop('checked');
            params += '&fecha_h='+$('#fecha_radicacion_h').val();
            params += '&fecha_v='+$('#fecha_radicacion_v').val();
            params += '&fecha_color='+$('#fecha_radicacion_color').val().replace('#','');
            params += '&texto1='+$('#texto1').prop('checked');
            params += '&texto1_h='+$('#texto1_h').val();
            params += '&texto1_v='+$('#texto1_v').val();
            params += '&texto1_color='+$('#texto1_color').val().replace('#','');
            params += '&texto1_txt='+$('#texto1_txt').val();
            params += '&texto2='+$('#texto2').prop('checked');
            params += '&texto2_h='+$('#texto2_h').val();
            params += '&texto2_v='+$('#texto2_v').val();
            params += '&texto2_color='+$('#texto2_color').val().replace('#','');
            params += '&texto2_txt='+$('#texto2_txt').val();
            params += '&savethumbnail='+$('#savethumbnail').val();
            $('#pdf').attr('data','plugins/PLRadicador/lib/LabelPreview.php?'+params);
        };

        $("#prev").click(prev);
});