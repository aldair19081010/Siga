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
	<div class="col-lg-12"></div>
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				<!-- Botón para subir Excel -->
				<button class="btn btn-success float-right ml-2" id="upload_teacher_excel">
					<i class="fa fa-upload"></i> Subir Excel
				</button>
				<!-- Botón para descargar formato -->
				<a href="download_teacher_format.php" class="btn btn-info float-right">
					<i class="fa fa-download"></i> Descargar Formato
				</a>
			</div>
		</div>
		<div class="row">
			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Lista de Docentes</b>
						<span class="float-right">
							<a class="btn btn-primary btn-lg d-flex align-items-center justify-content-center" href="javascript:void(0)" id="new_teacher">
								<i class="fa fa-plus mr-2"></i> Docente
							</a>
						</span>
					</div>
					<div class="card-body">
						<table class="table table-condensed table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th>Dni</th>
									<th>Nombre</th>
									<th>Información</th>
									<th>Especialidad</th>
									<th class="text-center">Acción</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								// Filtrar por school_id del usuario logueado
								$school_id = $_SESSION['login_school_id'] ?? 0;
								$teacher = $conn->query("SELECT * FROM teacher WHERE school_id = $school_id ORDER BY name ASC");
								while ($row = $teacher->fetch_assoc()) :
								?>
									<tr>
										<td class="text-center"><?php echo $i++ ?></td>
										<td><?php echo $row['id_no'] ?></td>
										<td><?php echo ucwords($row['name']) ?></td>
										<td>
											<p>Correo: <?php echo $row['email'] ?></p>
											<p># Móvil: <?php echo $row['contact'] ?></p>
											<p>Dirección: <?php echo $row['address'] ?></p>
										</td>
										<td><?php echo $row['specialty'] ?></td>
										<td class="text-center">
											<button class="btn btn-primary edit_teacher" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
											<button class="btn btn-danger delete_teacher" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
											<!-- Añadiendo el botón para crear usuario al docente -->
											<button class="btn btn-success create_user_teacher" type="button" data-id="<?php echo $row['id'] ?>" data-name="<?php echo $row['name'] ?>" data-email="<?php echo $row['email'] ?>"><i class="fa fa-user-plus"></i></button>
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

<!-- Modal para subir Excel -->
<div class="modal fade" id="uploadTeacherExcelModal" tabindex="-1" role="dialog" aria-labelledby="uploadTeacherExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadTeacherExcelModalLabel">Subir Archivo Excel de Docentes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="upload-teacher-excel-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="excel_file">Seleccionar archivo Excel</label>
                        <input type="file" class="form-control-file" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-upload"></i> Subir Excel
                        </button>
                    </div>
                </form>
            </div>
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

	$('#new_teacher').click(function() {
		uni_modal("Nuevo Docente", "manage_teacher.php", "mid-large");
	});

	$('.edit_teacher').click(function() {
		uni_modal("Gestionar Información de Docente", "manage_teacher.php?id=" + $(this).attr('data-id'), "mid-large");
	});

	$('.delete_teacher').click(function() {
		_conf("¿Deseas eliminar este docente?", "delete_teacher", [$(this).attr('data-id')]);
	});

	function delete_teacher($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_teacher',
			method: 'POST',
			data: { id: $id },
			dataType: 'json',
			success: function(resp) {
				if (resp.status == 1) {
					alert_toast("Docente eliminado exitosamente.", 'success');
					setTimeout(function() {
						location.reload();
					}, 500);
				} else {
					alert_toast(resp.message || "Error al eliminar el docente.", 'danger');
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

	// Abrir modal para subir Excel
	$('#upload_teacher_excel').click(function() {
		$('#uploadTeacherExcelModal').modal('show');
	});

	// Manejar el envío del formulario de Excel
	$(document).on('submit', '#upload-teacher-excel-form', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		start_load();
		
		if (!$('#excel_file').val()) {
			alert_toast('Por favor, seleccione un archivo Excel.', 'warning');
			end_load();
			return false;
		}

		var formData = new FormData(this);
		
		$.ajax({
			url: 'ajax.php?action=upload_teacher_excel',
			method: 'POST',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function(resp) {
				end_load();
				try {
					if (typeof resp === 'string') {
						resp = JSON.parse(resp);
					}
					
					if (resp.status === 'success') {
						alert_toast(resp.message, 'success');
						$('#uploadTeacherExcelModal').modal('hide');
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
			},
			error: function(xhr, status, error) {
				end_load();
				console.error('Error en la solicitud AJAX:', status, error);
				console.log('Respuesta del servidor:', xhr.responseText);
				alert_toast('Error en el servidor. Intente nuevamente.', 'danger');
			}
		});
		
		return false;
	});

	// Restaurar la funcionalidad para crear usuarios a los docentes
	$('.create_user_teacher').click(function() {
		uni_modal("Crear Usuario para Docente", "manage_teacher_user.php?id=" + $(this).attr('data-id') + "&name=" + $(this).attr('data-name') + "&email=" + $(this).attr('data-email'), "mid-large");
	});
</script>
