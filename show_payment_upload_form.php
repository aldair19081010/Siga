<div class="container-fluid">
    <form id="upload-payment-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="payment_excel_file">Seleccionar archivo Excel</label>
            <input type="file" class="form-control-file" id="payment_excel_file" name="payment_excel_file" accept=".xls,.xlsx" required>
        </div>
        <div class="form-group">
            <p class="text-muted">
                <small>Formato requerido: Primera columna = DNI del estudiante, Segunda columna = Código de concepto de pago</small>
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
        $('#upload-payment-form').submit(function(e) {
            e.preventDefault();
            start_load();
            
            var formData = new FormData(this);
            
            $.ajax({
                url: 'ajax.php?action=process_payment_excel',
                method: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(resp) {
                    if (typeof resp === 'string') {
                        resp = JSON.parse(resp);
                    }
                    
                    if (resp.status === 1) {
                        alert_toast(resp.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast(resp.message || 'Error al procesar el archivo.', 'danger');
                    }
                    end_load();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert_toast('Error en el servidor. Intente nuevamente.', 'danger');
                    end_load();
                }
            });
        });
    });
</script>
