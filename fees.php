<?php include('db_connect.php'); ?>
<style>
/* === ESTILO UNIFICADO COMO grades.php === */
.card {
	border-radius: 10px;
	box-shadow: 0 4px 15px rgba(0,0,0,0.08);
	border: none;
	margin-bottom: 24px;
	overflow: hidden;
}
.card-header {
	padding: 15px 20px;
	background: linear-gradient(90deg, #2a75f3 0%, #4285f4 100%);
	color: #fff;
	border-bottom: 1px solid #e3e6f0;
	font-weight: 600;
	font-size: 1.1rem;
}
.card-header .btn {
	border-radius: 6px;
	font-weight: 500;
	transition: all 0.2s;
}
.card-header .btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(66,133,244,0.15);
}
.table {
	border-radius: 8px;
	overflow: hidden;
	background: #fff;
}
.table th, .table td {
	vertical-align: middle !important;
	padding: 12px 15px;
}
.table thead th {
	background: #f8f9fc;
	color: #2c4964;
	font-weight: 600;
	border-bottom: 2px solid #e3e6f0;
}
.table-hover tbody tr:hover {
	background-color: #f1f3f6;
	transform: translateY(-1px);
	box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.badge-info {
	background: #17a2b8;
}
.badge-success {
	background: #28a745;
}
.badge-danger {
	background: #dc3545;
}
.badge-secondary {
	background: #6c757d;
}
.btn-sm {
	padding: 0.25rem 0.5rem;
	font-size: 0.875rem;
	border-radius: 6px;
}
.btn-action {
	border-radius: 6px;
	padding: 6px 10px;
	min-width: 36px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	transition: all 0.2s;
}
.btn-action:hover {
	transform: translateY(-2px);
	box-shadow: 0 3px 5px rgba(0,0,0,0.1);
}
.dataTables_wrapper .dataTables_filter input {
	border: 1px solid #e3e6f0;
	border-radius: 6px;
	padding: 6px 12px;
	margin-left: 8px;
}
.dataTables_wrapper .dataTables_length select {
	border: 1px solid #e3e6f0;
	border-radius: 6px;
	padding: 4px 8px;
	margin: 0 4px;
}
.dataTables_info, .dataTables_paginate {
	margin-top: 15px;
}
.dataTables_paginate .paginate_button {
	border-radius: 4px !important;
	margin: 0 2px;
}
.dataTables_paginate .paginate_button.current {
	background: #4285f4 !important;
	border-color: #2a75f3 !important;
	color: white !important;
}
@media (max-width: 768px) {
	.btn-action {
		padding: 5px 8px;
		min-width: 32px;
	}
	.table-responsive {
		overflow-x: auto;
	}
}
@media (max-width: 576px) {
	.btn-action {
		padding: 4px 6px;
		min-width: 28px;
	}
	.btn-action .badge {
		font-size: 0.65rem;
	}
}
td { vertical-align: middle !important; }
td p { margin: unset }
img { max-width: 100px; max-height: 150px; }
</style>

<div class="main-content-area">
	<div class="row mb-4 mt-4">
		<div class="col-md-12">
			<!-- Botón para subir Excel -->
			<button class="btn btn-success float-right ml-2" id="upload_payment_excel">
				<i class="fa fa-upload"></i> Subir Excel
			</button>
			<!-- Botón para descargar formato -->
			<a href="download_payment_format.php" class="btn btn-info float-right">
				<i class="fa fa-download"></i> Descargar Formato
			</a>
		</div>
	</div>
	<div class="row">
		<!-- Table Panel -->
		<div class="col-md-12">
			<div class="card shadow-sm border-0">
				<div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
					<b>Pagos de Estudiantes</b>
					<a class="btn btn-light btn-sm text-primary font-weight-bold" href="javascript:void(0)" id="new_fees">
						<i class="fa fa-plus"></i> Pagos
					</a>
				</div>
				<div class="card-body">
					<div id="msg"></div>
					<div class="table-responsive">
						<table id="feesTable" class="table table-bordered table-hover table-striped mb-0">
							<thead class="thead-light">
								<tr>
									<th class="text-center">#</th>
									<th>ID No.</th>
									<th>Nombre</th>
									<th>Concepto de Pago</th>
									<th>Tarifa</th>
									<th>Pago</th>
									<th>Balance</th>
									<th class="text-center">Acción</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$i = 1;
							$fees = $conn->query("SELECT ef.*, s.name as sname, s.id_no, c.course as concepto_pago FROM student_ef_list ef INNER JOIN student s ON s.id = ef.student_id INNER JOIN courses c ON c.id = ef.course_id ORDER BY s.name ASC ");
							while ($row = $fees->fetch_assoc()) :
								$paid = $conn->query("SELECT sum(amount) as paid FROM payments where ef_id=" . $row['id']);
                                $paid = $paid->num_rows > 0 ? $paid->fetch_array()['paid'] : 0;
                                $balance = $row['total_fee'] - $paid;
							?>
								<tr>
									<td class="text-center align-middle"><?php echo $i++ ?></td>
									<td class="align-middle"><span class="badge badge-secondary p-2"><?php echo $row['id_no'] ?></span></td>
									<td class="align-middle"><?php echo ucwords($row['sname']) ?></td>
									<td class="align-middle"><?php echo $row['concepto_pago'] ?></td>
									<td class="text-right align-middle"><span class="badge badge-info p-2">S/ <?php echo number_format($row['total_fee'], 2) ?></span></td>
									<td class="text-right align-middle"><span class="badge badge-success p-2">S/ <?php echo number_format($paid, 2) ?></span></td>
									<td class="text-right align-middle">
										<span class="badge badge-<?php echo $balance > 0 ? 'danger' : 'success' ?> p-2">S/ <?php echo number_format($balance, 2) ?></span>
									</td>
									<td class="text-center align-middle">
										<button class="btn btn-sm btn-primary view_payment" type="button" data-id="<?php echo $row['id'] ?>" title="Ver Detalle"><i class="fa fa-eye"></i></button>
										<button class="btn btn-sm btn-info edit_fees" type="button" data-id="<?php echo $row['id'] ?>" title="Editar"><i class="fa fa-edit"></i></button>
										<button class="btn btn-sm btn-danger delete_fees" type="button" data-id="<?php echo $row['id'] ?>" title="Eliminar"><i class="fa fa-trash-alt"></i></button>
									</td>
								</tr>
							<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- Table Panel -->
	</div>
</div>

<!-- DataTables & jQuery via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css"/>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" type="text/javascript"></script>

<script>
$(document).ready(function() {
	$('#feesTable').DataTable({
		language: {
			url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
		},
		responsive: true,
		order: [[2, 'asc']],
		columnDefs: [
			{ targets: [0,7], orderable: false }
		]
	});

	$('.view_payment').click(function() {
		uni_modal("Información de Pagos", "view_payment.php?ef_id=" + $(this).attr('data-id') + "&pid=0", "mid-large");
	});

	$('#new_fees').click(function() {
		uni_modal("Inscribir estudiante", "manage_fee.php", "mid-large");
	});

	$('#upload_payment_excel').click(function() {
		uni_modal("Subir Pagos por Excel", "show_payment_upload_form.php");
	});

	$('.edit_fees').click(function() {
		uni_modal("Editar detalles de inscripción", "manage_fee.php?id=" + $(this).attr('data-id'), "mid-large");
	});

	$('.delete_fees').click(function() {
		_conf("¿Deseas eliminar estas tarifas?", "delete_fees", [$(this).attr('data-id')]);
	});
});

function delete_fees($id) {
	start_load();
	$.ajax({
		url: 'ajax.php?action=delete_fees',
		method: 'POST',
		data: { id: $id },
		success: function(resp) {
			try {
				if (typeof resp === 'string') {
					resp = JSON.parse(resp);
				}
				if (resp.status == 1) {
					$('#msg').html('<div class="alert alert-success">Datos eliminados exitosamente</div>');
					setTimeout(function() { location.reload(); }, 800);
				} else {
					$('#msg').html('<div class="alert alert-danger">' + resp.message + '</div>');
					end_load();
				}
			} catch (err) {
				console.error("Error al procesar la respuesta del servidor:", err);
				$('#msg').html('<div class="alert alert-danger">Error inesperado. Intente nuevamente más tarde.</div>');
				end_load();
			}
		},
		error: function(err) {
			console.error("Error en la solicitud AJAX:", err);
			$('#msg').html('<div class="alert alert-danger">Error en el servidor. Intente nuevamente más tarde.</div>');
			end_load();
		}
	});
}

$('#uni_modal').on('hidden.bs.modal', function () {
	$(this).find('.modal-title').html('');
	$(this).find('.modal-body').html('');
});

window.reload_table = function() {
	location.reload();
};
</script>