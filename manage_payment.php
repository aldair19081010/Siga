<?php include 'db_connect.php' ?>
<?php
if (isset($_GET['id'])) {
	$qry = $conn->query("SELECT * FROM payments where id = {$_GET['id']} ");
	foreach ($qry->fetch_array() as $k => $v) {
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<form id="manage-payment">
		<div id="msg"></div>
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group">
			<label for="" class="control-label">Concepto Pago/Estudiante</label>
			<select name="ef_id" id="ef_id" class="custom-select input-sm select2">
				<option value=""></option>
				<?php
				$fees = $conn->query("
				SELECT 
					ef.id, ef.student_id, ef.course_id, ef.total_fee, 
					s.name as sname, s.id_no, 
					c.course as course_name 
				FROM student_ef_list ef 
				INNER JOIN student s ON s.id = ef.student_id 
				INNER JOIN courses c ON c.id = ef.course_id 
				ORDER BY s.name ASC
			");
				while ($row = $fees->fetch_assoc()) :
					$paid = $conn->query("SELECT sum(amount) as paid FROM payments where ef_id=" . $row['id'] . (isset($id) ? " and id!=$id " : ''));
					$paid = $paid->num_rows > 0 ? $paid->fetch_array()['paid'] : '';
					$balance = $row['total_fee'] - $paid;
				?>
				<option value="<?php echo $row['id'] ?>" data-balance="<?php echo $balance ?>" <?php echo isset($ef_id) && $ef_id == $row['id'] ? 'selected' : '' ?>>
					<?php echo $row['course_name'] . ' | ' . ucwords($row['sname']) ?>
				</option>	
			<?php endwhile; ?>
			</select>
		</div>
		<div class="form-group">
			<label for="" class="control-label">Saldo Pendiente</label>
			<input type="text" class="form-control text-right" id="balance" value="<?php echo isset($balance) ? $balance : '' ?>" required readonly>
		</div>
		<div class="form-group">
			<label for="" class="control-label">Monto</label>
			<input type="text" class="form-control text-right" name="amount" value="<?php echo isset($amount) ? number_format($amount) : 0 ?>" required>
		</div>
		<div class="form-group">
			<label for="" class="control-label">N° Boleta</label>
			<input type="text" class="form-control" name="receipt_no" value="<?php echo isset($receipt_no) ? $receipt_no : '' ?>" required>
		</div>
		<div class="form-group mb-2">
    <label for="" class="control-label">Medio de Pago</label> 
    <select id="ft" name="payment_method_id" class="form-control" required>
        <option value="" disabled selected>Seleccione un tipo de pago</option>
        <?php
            $methods = $conn->query("SELECT id, name FROM payment_methods ORDER BY name ASC");
            while($row = $methods->fetch_assoc()):
        ?>
            <option value="<?php echo $row['id'] ?>" 
                <?php echo isset($payment_method_id) && $payment_method_id == $row['id'] ? 'selected' : '' ?>>
                <?php echo $row['name'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

		<div class="form-group">
			<label for="" class="control-label">Observaciones</label>
			<textarea name="remarks" id="" cols="30" rows="3" class="form-control" required=""><?php echo isset($remarks) ? $remarks : '' ?></textarea>
		</div>
	</form>
</div>

<script>
	$('.select2').select2({
		placeholder: 'Por favor selecciona aquí',
		width: '100%'
	});

	$('#ef_id').change(function() {
		var amount = $('#ef_id option[value="' + $(this).val() + '"]').attr('data-balance');
		$('#balance').val(parseFloat(amount).toLocaleString('en-US', {
			style: 'decimal',
			maximumFractionDigits: 2,
			minimumFractionDigits: 2
		}));
	});

	$('#manage-payment').submit(function(e) {
		e.preventDefault();
		start_load();
		$('#msg').html('');
		$.ajax({
			url: 'ajax.php?action=save_payment',
			method: 'POST',
			data: $(this).serialize(),
			success: function(resp) {
				try {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp); // Asegurarse de que la respuesta sea JSON válida
					}
					if (resp.status == 1) {
						alert_toast("Datos guardados con éxito.", 'success');
						setTimeout(function() {
							var nw = window.open('receipt.php?ef_id=' + resp.ef_id + '&pid=' + resp.pid, "_blank", "width=900,height=600");
							setTimeout(function() {
								nw.print();
								setTimeout(function() {
									nw.close();
									location.reload(); // Recargar la página después de guardar
								}, 500);
							}, 500);
						}, 500);
					} else {
						alert_toast(resp.message, 'danger');
						end_load();
					}
				} catch (err) {
					console.error("Error al procesar la respuesta del servidor:", err);
					alert_toast("Error inesperado. Intente nuevamente más tarde.", 'danger');
					end_load();
				}
			},
			error: function(err) {
				console.error("Error en la solicitud AJAX:", err);
				alert_toast("Error en el servidor. Intente nuevamente más tarde.", 'danger');
				end_load();
			}
		});
	});
</script>