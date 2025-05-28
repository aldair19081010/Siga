<?php
// notifications_sender.php
include('header.php');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Enviar Notificaciones de Deuda</h4>
                </div>
                <div class="card-body">
                    <form id="send_notifications_form">
                        <div class="form-group">
                            <label>Título de la notificación</label>
                            <input type="text" class="form-control" name="title" value="Recordatorio de Pago" required>
                        </div>
                        <div class="form-group">
                            <label>Mensaje personalizado (opcional)</label>
                            <textarea class="form-control" name="message">Recuerda pagar tu deuda pendiente para evitar recargos.</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar a Estudiantes con Deudas</button>
                    </form>
                    <div id="result_area" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#send_notifications_form').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'scripts/send_debt_notifications.php',
            type: 'POST',
            data: $(this).serialize(),
            beforeSend: function() {
                $('#result_area').html('<div class="alert alert-info">Enviando notificaciones...</div>');
            },
            success: function(response) {
                $('#result_area').html('<div class="alert alert-success">Notificaciones enviadas exitosamente</div>');
            },
            error: function() {
                $('#result_area').html('<div class="alert alert-danger">Error al enviar notificaciones</div>');
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>