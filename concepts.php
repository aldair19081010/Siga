<?php include('db_connect.php'); ?>
<style>
	input[type=checkbox] {
		-ms-transform: scale(1.3);
		-moz-transform: scale(1.3);
		-webkit-transform: scale(1.3);
		-o-transform: scale(1.3);
		transform: scale(1.3);
		padding: 10px;
		cursor: pointer;
	}
</style>
<div class="container-fluid">

	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				<!-- Aquí podrías agregar botones relacionados a conceptos de pago si lo deseas -->
			</div>
		</div>
		<div class="row">
			<!-- Panel de Tabla de Conceptos de Pago -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Conceptos de Pagos</b>
						<span class="float:right"><a class="btn btn-primary col-sm-6 col-md-2 float-right" href="javascript:void(0)" id="new_concept">
								<i class="fa fa-plus"></i> Nuevo Concepto
							</a></span>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">Código</th>
									<th>Concepto</th>
									<th>Descripción</th>
									<th>Monto</th>
									<th class="text-center">Acción</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$conceptos = $conn->query("SELECT * FROM courses ORDER BY course ASC");
								while ($row = $conceptos->fetch_assoc()) :
								?>
									<tr>
										<td class="text-center font-weight-bold"><?php echo $row['id'] ?></td>
										<td>
											<?php echo $row['course'] . " - " . $row['level'] ?>
										</td>
										<td>
											<?php echo $row['description'] ?>
										</td>
										<td class="text-right">
											<?php echo number_format($row['total_amount'], 2) ?>
										</td>
										<td class="text-center">
											<button class="btn btn-primary edit_concept" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
											<button class="btn btn-danger delete_concept" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
										</td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- Fin Panel Tabla -->
		</div>
	</div>
</div>
<style>
	td {
		vertical-align: middle !important;
	}
	td p {
		margin: unset
	}
	img {
		max-width: 100px;
		max-height: 150px;
	}
</style>

<script>
	$(document).ready(function() {
		$('table').dataTable();
	});

	$('#new_concept').click(function() {
		uni_modal("Nuevo Concepto de Pago", "manage_concept.php", 'large');
	});

	$('.edit_concept').click(function() {
		uni_modal("Editar Concepto de Pago", "manage_concept.php?id=" + $(this).attr('data-id'), 'large');
	});

	$('.delete_concept').click(function() {
		_conf("¿Deseas eliminar este concepto de pago?", "delete_concept", [$(this).attr('data-id')]);
	});

	function delete_concept($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_course',
			method: 'POST',
			data: { id: $id },
			success: function(resp) {
				try {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp);
					}
					if (resp.status == 1) {
						alert_toast("Concepto eliminado exitosamente.", 'success');
						setTimeout(function() {
							location.reload();
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
	}
</script>