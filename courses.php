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

	/* Estilo mejorado para el código de pago */
	.codigo-pago {
		font-weight: bold;
		background-color: #e9f7ef;
		color: #28a745;
		border: 1px solid #28a745;
		padding: 5px 10px;
		border-radius: 5px;
		display: inline-block;
		min-width: 60px;
	}
</style>

<!-- Limpiar cualquier caché o buffer previo -->
<?php 
ob_clean(); 
clearstatcache();
?>

<div class="main-content-area">
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
					<span class="float:right"><a class="btn btn-primary col-sm-6 col-md-2 float-right" href="javascript:void(0)" id="new_course">
						<i class="fa fa-plus"></i> Nuevo Concepto
					</a></span>
				</div>
				<div class="card-body">
					<!-- Forzar recarga de la tabla -->
					<div id="table-container">
						<table class="table table-condensed table-bordered table-hover" id="curso-tabla">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="text-center">Código de Pago</th>
									<th>Concepto</th>
									<th>Descripción</th>
									<th>Monto</th>
									<th class="text-center">Acción</th>
								</tr>
							</thead>
							<tbody>
								<?php
								// Asegurarse de que la consulta sea correcta y actualizada
								$conceptos = $conn->query("SELECT * FROM courses ORDER BY id ASC");
								$i = 1;
								while ($row = $conceptos->fetch_assoc()) :
								?>
									<tr>
										<td class="text-center"><?php echo $i++ ?></td>
										<td class="text-center">
											<span class="codigo-pago"><?php echo $row['id'] ?></span>
										</td>
										<td><?php echo $row['course'] . " - " . $row['level'] ?></td>
										<td><?php echo $row['description'] ?></td>
										<td class="text-right"><?php echo number_format($row['total_amount'], 2) ?></td>
										<td class="text-center">
											<button class="btn btn-primary edit_course" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
											<button class="btn btn-danger delete_course" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
										</td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- Fin Panel Tabla -->
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
	
	/* Asegurar que la columna de ID no sea reemplazada por DataTables */
	.id-column {
		font-weight: bold !important;
	}
	
	/* Añadir regla CSS para prevenir que DataTables modifique estos elementos */
	.sorting_1.id-column:before,
	.sorting_1.id-column:after {
		display: none !important;
	}

	/* Estilo más distintivo para el código de pago */
	.codigo-pago {
		font-weight: bold !important;
		background-color: #e9f7ef !important;
		color: #28a745 !important;
		border: 1px solid #28a745 !important;
		padding: 5px 10px !important;
		border-radius: 5px !important;
	}
</style>

<script>
	$(document).ready(function() {
		// Reinicializar la tabla para asegurar que se apliquen los cambios
		if ($.fn.DataTable.isDataTable('#curso-tabla')) {
			$('#curso-tabla').DataTable().destroy();
		}
		
		// Inicializar con un nuevo ID
		$('#curso-tabla').DataTable({
			"ordering": true,
			"columnDefs": [
				{ "orderable": false, "targets": [5] },
				{ "searchable": false, "targets": [0, 5] }
			],
			"order": [[1, 'asc']], // Ordenar por código de pago por defecto
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
			}
		});
		
		// Agregar un estilo inline para forzar la visualización
		$('.codigo-pago').attr('style', 'font-weight: bold !important; background-color: #e9f7ef !important; color: #28a745 !important; border: 1px solid #28a745 !important; padding: 5px 10px !important; border-radius: 5px !important;');
	});

	$('#new_course').click(function() {
		uni_modal("Nuevo Concepto de Pago", "manage_course.php", 'large');
	});

	$('.edit_course').click(function() {
		uni_modal("Editar Concepto de Pago", "manage_course.php?id=" + $(this).attr('data-id'), 'large');
	});

	$('.delete_course').click(function() {
		_conf("¿Deseas eliminar este concepto de pago?", "delete_course", [$(this).attr('data-id')]);
	});

	function delete_course($id) {
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