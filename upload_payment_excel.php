<?php
// Este archivo debe mostrar solo el formulario, no procesar datos
include 'db_connect.php';
?>
<div class="container-fluid">
    <form id="upload-payment-excel-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="excel_file">Seleccionar archivo Excel</label>
            <input type="file" class="form-control-file" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
        </div>
        <div class="form-group">
            <p class="text-muted">
                <small>Formato requerido: Columna 1 = DNI del estudiante, Columna 2 = Código de concepto de pago</small>
            </p>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa fa-upload"></i> Subir Excel
            </button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#upload-payment-excel-form').on('submit', function(e) {
            e.preventDefault();
            
            start_load();
            
            if (!$('#excel_file').val()) {
                alert_toast('Por favor, seleccione un archivo Excel.', 'warning');
                end_load();
                return false;
            }

            var formData = new FormData(this);
            
            $.ajax({
                url: 'ajax.php?action=upload_payment_excel',
                method: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(resp) {
                    try {
                        if (typeof resp === 'string') {
                            resp = JSON.parse(resp);
                        }
                        
                        if (resp.status === 1) {
                            alert_toast(resp.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            alert_toast(resp.message || 'Error desconocido.', 'danger');
                        }
                    } catch (err) {
                        console.error('Error al procesar la respuesta:', err);
                        console.log('Respuesta del servidor:', resp);
                        alert_toast('Error inesperado. Verifique la consola.', 'danger');
                    }
                    end_load();
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', status, error);
                    console.log('Respuesta del servidor:', xhr.responseText);
                    alert_toast('Error en el servidor. Intente nuevamente.', 'danger');
                    end_load();
                }
            });
            
            return false;
        });
    });
</script>
