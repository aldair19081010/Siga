<?php include 'db_connect.php' ?>
<?php
if (isset($_GET['id'])) {
	$qry = $conn->query("SELECT * FROM payments where id = {$_GET['id']} ");
	foreach ($qry->fetch_array() as $k => $v) {
		$$k = $v;
	}
} else {
	// Obtener el último número de boleta y sumarle 1
	$last_receipt = $conn->query("SELECT MAX(CAST(receipt_no AS UNSIGNED)) as last_no FROM payments");
	$next_receipt = 1;
	if ($last_receipt && $last_receipt->num_rows > 0) {
		$row = $last_receipt->fetch_assoc();
		if (!empty($row['last_no'])) {
			$next_receipt = $row['last_no'] + 1;
		}
	}
	$receipt_no = $next_receipt;
}
?>
<div class="container-fluid">
	<form id="manage-payment">
		<div id="msg"></div>
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group">
			<label for="" class="control-label">Alumno</label>
			<select name="student_id" id="student_id" class="custom-select input-sm select2" required>
				<option value="">Seleccione un alumno</option>
				<?php
				$students = $conn->query("SELECT id, name, id_no FROM student ORDER BY name ASC");
				while ($stu = $students->fetch_assoc()): ?>
					<option value="<?php echo $stu['id']; ?>" <?php echo (isset($student_id) && $student_id == $stu['id']) ? 'selected' : '' ?>>
						<?php echo $stu['id_no'] . ' - ' . ucwords($stu['name']); ?>
					</option>
				<?php endwhile; ?>
			</select>
		</div>
		<div class="form-group">
			<label for="" class="control-label">Concepto de Pago Pendiente</label>
			<select name="ef_id" id="ef_id" class="custom-select input-sm select2" required disabled>
				<option value="">Seleccione un concepto</option>
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
			<input type="text" class="form-control" name="receipt_no" value="<?php echo isset($receipt_no) ? $receipt_no : '' ?>" required readonly>
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

	$('#student_id').change(function() {
		var student_id = $(this).val();
		$('#ef_id').prop('disabled', true).html('<option value="">Cargando...</option>');
		if(student_id) {
			$.ajax({
				url: 'ajax.php?action=get_pending_concepts',
				method: 'POST',
				data: {student_id: student_id},
				success: function(resp) {
					try {
						if(typeof resp === 'string') resp = JSON.parse(resp);
						var options = '<option value="">Seleccione un concepto</option>';
						(resp.data || []).forEach(function(item) {
							options += `<option value="${item.ef_id}" data-balance="${item.balance}">${item.course_name} | ${item.sname}</option>`;
						});
						$('#ef_id').html(options).prop('disabled', false);
					} catch(e) {
						$('#ef_id').html('<option value="">Error al cargar conceptos</option>');
					}
				},
				error: function() {
					$('#ef_id').html('<option value="">Error al cargar conceptos</option>');
				}
			});
		} else {
			$('#ef_id').html('<option value="">Seleccione un concepto</option>').prop('disabled', true);
		}
	});

	$('#ef_id').change(function() {
		var amount = $('#ef_id option:selected').attr('data-balance');
		$('#balance').val(amount ? parseFloat(amount).toLocaleString('en-US', {style: 'decimal', maximumFractionDigits: 2, minimumFractionDigits: 2}) : '');
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
<?php
if(isset($_GET['action']) && $_GET['action'] == 'get_pending_concepts') {
	require 'db_connect.php';
	header('Content-Type: application/json');
	$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
	$data = [];
	if($student_id) {
		$q = $conn->query("SELECT ef.id as ef_id, ef.total_fee, s.name as sname, s.id_no, c.course as course_name
			FROM student_ef_list ef
			INNER JOIN student s ON s.id = ef.student_id
			INNER JOIN courses c ON c.id = ef.course_id
			WHERE ef.student_id = $student_id");
		while($row = $q->fetch_assoc()) {
			$paid_q = $conn->query("SELECT SUM(amount) as paid FROM payments WHERE ef_id = " . $row['ef_id']);
			$paid = $paid_q && $paid_q->num_rows > 0 ? floatval($paid_q->fetch_assoc()['paid']) : 0;
			$balance = floatval($row['total_fee']) - $paid;
			if($balance > 0.01) { // Solo mostrar si hay deuda
				$row['balance'] = $balance;
				$data[] = $row;
			}
		}
	}
	echo json_encode(['status' => 'ok', 'data' => $data]);
	exit;
}