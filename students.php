<?php include('db_connect.php'); ?>
<style>
	input[type=checkbox] {
		/* Double-sized Checkboxes */
		-ms-transform: scale(1.3);
		/* IE */
		-moz-transform: scale(1.3);
		/* FF */
		-webkit-transform: scale(1.3);
		/* Safari and Chrome */
		-o-transform: scale(1.3);
		/* Opera */
		transform: scale(1.3);
		padding: 10px;
		cursor: pointer;
	}
</style>

<div class="container-fluid">
	<div class="col-lg-12"></div>
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				<!-- Botón para subir Excel -->
				<button class="btn btn-success float-right ml-2" id="upload_excel">
					<i class="fa fa-upload"></i> Subir Excel
				</button>
				<!-- Botón para descargar formato -->
				<a href="download_format.php" class="btn btn-info float-right">
					<i class="fa fa-download"></i> Descargar Formato
				</a>
			</div>
		</div>
		<div class="row">
				<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Lista de Estudiantes</b>
						<span class="float-right">
							<a class="btn btn-primary btn-lg d-flex align-items-center justify-content-center" href="javascript:void(0)" id="new_student">
								<i class="fa fa-plus mr-2"></i> Estudiante
							</a>
						</span>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="">Dni</th>
									<th class="">Nombre</th>
									<th class="">Información</th>
									<th class="">Nivel</th> 
									<th class="">Grado</th>
									<th class="text-center">Acción</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1; // Inicializar contador para numeración
								$student = $conn->query("SELECT * FROM student ORDER BY name ASC");
								while ($row = $student->fetch_assoc()) :
								?>
									<tr>
										<td class="text-center"><?php echo $i++ ?></td> <!-- Mostrar numeración -->
										<td><?php echo $row['id_no'] ?></td>
										<td><?php echo ucwords($row['name']) ?></td>
										<td>
											<p>Correo: <?php echo $row['email'] ?></p>
											<p># Móvil: <?php echo $row['contact'] ?></p>
											<p>Dirección: <?php echo $row['address'] ?></p>
										</td>
										<td><?php echo $row['nivel'] ?></td>
										<td><?php echo $row['grado'] ?></td>
										<td class="text-center">
											<button class="btn btn-primary edit_student" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
											<button class="btn btn-danger delete_student" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
										</td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- Table Panel -->
		</div>
	</div>
</div>

<style>
	td {
		vertical-align: middle !important;
	}

	td p {
		margin: unset;
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

	$('#new_student').click(function() {
		uni_modal("Nuevo Estudiante", "manage_student.php", "mid-large");
	});

	$('.edit_student').click(function() {
		uni_modal("Gestionar Información de Estudiante", "manage_student.php?id=" + $(this).attr('data-id'), "mid-large");
	});

	$('.delete_student').click(function() {
		_conf("¿Deseas eliminar este estudiante?", "delete_student", [$(this).attr('data-id')]);
	});

	function delete_student($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_student',
			method: 'POST',
			data: { id: $id },
			success: function(resp) {
				try {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp); // Asegurarse de que la respuesta sea JSON válida
					}
					if (resp.status == 1) {
						alert_toast(resp.message, 'success');
						setTimeout(function() {
							reload_table(); // Recargar la tabla después de eliminar
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

	$('#upload_excel').click(function() {
		uni_modal("Subir Archivo Excel", "upload_excel.php", "mid-large");
	});

	// Recargar la tabla de estudiantes después de guardar
	window.reload_table = function() {
		location.reload(); // Recargar la página
	};

	$('#manage-student').submit(function(e) {
		e.preventDefault();
		start_load();
		$('#msg').html('');
		$.ajax({
			url: 'ajax.php?action=save_student',
			data: new FormData($(this)[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp) {
				try {
					resp = JSON.parse(resp); // Asegurarse de que la respuesta sea JSON válida
					if (resp.status == 1) {
						alert_toast(resp.message, 'success');
						setTimeout(function() {
							$('#uni_modal').modal('hide');
							reload_table();
						}, 500);
					} else if (resp.status == 2) {
						$('#msg').html('<div class="alert alert-danger mx-2">' + resp.message + '</div>');
						end_load();
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