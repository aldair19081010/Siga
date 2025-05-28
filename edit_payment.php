<?php include 'db_connect.php'; ?>
<?php
// Obtener el ID del pago a editar
$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$payment_data = [];
if ($payment_id) {
    $qry = $conn->query("SELECT * FROM payments WHERE id = $payment_id");
    if ($qry && $qry->num_rows > 0) {
        $payment_data = $qry->fetch_assoc();
    }
}
?>
<div class="container-fluid">
    <form id="edit-payment-form">
        <input type="hidden" name="id" value="<?php echo $payment_id; ?>">
        <div class="form-group">
            <label for="amount" class="control-label">Monto</label>
            <input type="number" step="0.01" min="0" class="form-control text-right" name="amount" id="amount" value="<?php echo isset($payment_data['amount']) ? $payment_data['amount'] : '0.00'; ?>" required>
        </div>
        <div class="form-group">
            <label for="receipt_no" class="control-label">N° Boleta</label>
            <input type="text" class="form-control" name="receipt_no" id="receipt_no" value="<?php echo isset($payment_data['receipt_no']) ? $payment_data['receipt_no'] : ''; ?>" required readonly>
        </div>
        <div class="form-group mb-2">
            <label for="payment_method_id" class="control-label">Medio de Pago</label>
            <select id="payment_method_id" name="payment_method_id" class="form-control" required>
                <option value="" disabled>Seleccione un tipo de pago</option>
                <?php
                $methods_query = $conn->query("SELECT id, name FROM payment_methods ORDER BY name ASC");
                while($method = $methods_query->fetch_assoc()):
                    $selected = isset($payment_data['payment_method_id']) && $payment_data['payment_method_id'] == $method['id'] ? 'selected' : '';
                ?>
                <option value="<?php echo $method['id']; ?>" <?php echo $selected; ?>>
                    <?php echo $method['name']; ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="remarks" class="control-label">Observaciones</label>
            <textarea name="remarks" id="remarks" cols="30" rows="3" class="form-control" required><?php echo isset($payment_data['remarks']) ? $payment_data['remarks'] : ''; ?></textarea>
        
</div>
<script>
$(document).ready(function() {
    $('#edit-payment-form').submit(function(e) {
        e.preventDefault();
        start_load();
        
        // Asegurarse de que ef_id también se envía, ya que es necesario en ajax.php
        var formData = $(this).serialize();
        if (!formData.includes('ef_id')) {
            // Obtener ef_id del pago actual y agregarlo al formulario
            formData += '&ef_id=<?php echo isset($payment_data["ef_id"]) ? $payment_data["ef_id"] : ""; ?>';
        }
        
        $.ajax({
            url: 'ajax.php?action=save_payment',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(resp) {
                if (resp.status == 1) {
                    alert_toast("Pago actualizado con éxito.", 'success');
                    setTimeout(function() {
                        location.href = 'index.php?page=payments'; // Redirigir al listado de pagos
                    }, 1500);
                } else {
                    alert_toast(resp.message || "Error al actualizar el pago.", 'danger');
                    end_load();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud:", xhr.responseText);
                alert_toast("Error en el servidor. Revise la consola para más detalles.", 'danger');
                end_load();
            }
        });
    });
});
</script>
