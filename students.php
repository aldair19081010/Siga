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
    
    /* Estilos generales para la página de estudiantes */
    .main-content-area {
        padding: 10px 5px;
    }
    
    .page-title {
        color: #4285f4;
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .page-title i {
        margin-right: 8px;
        font-size: 1.2em;
    }
    
    .action-buttons .btn {
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .action-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    }
    
    .action-buttons .btn i {
        margin-right: 6px;
    }
    
    .students-card {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: none;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .students-card .card-header {
        padding: 15px 20px;
        background-color: #f8f9fc;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .students-card .card-header .btn-add {
        padding: 8px 18px;
        font-weight: 500;
        border-radius: 8px;
        background: linear-gradient(135deg, #4285f4, #2a75f3);
        border-color: #2a75f3;
        box-shadow: 0 2px 5px rgba(66, 133, 244, 0.15);
        transition: all 0.3s ease;
    }
    
    .students-card .card-header .btn-add:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #2a75f3, #1a65e3);
        box-shadow: 0 4px 8px rgba(66, 133, 244, 0.25);
    }
</style>

<div class="main-content-area">
    <h4 class="page-title mb-4">
        <i class="fa fa-graduation-cap"></i> Gestión de Estudiantes
    </h4>
    
	<div class="row mb-4">
		<div class="col-md-12 action-buttons text-right">
			<!-- Botón para subir Excel -->
			<button class="btn btn-success ml-2" id="upload_excel" style="background: linear-gradient(135deg, #28a745, #218838); border-color: #1e7e34; box-shadow: 0 2px 5px rgba(40, 167, 69, 0.2);">
				<i class="fa fa-upload"></i> Subir Excel
			</button>
			<!-- Botón para descargar formato -->
			<a href="download_format.php" class="btn btn-info" style="background: linear-gradient(135deg, #17a2b8, #138496); border-color: #117a8b; box-shadow: 0 2px 5px rgba(23, 162, 184, 0.2);">
				<i class="fa fa-download"></i> Descargar Formato
			</a>
		</div>
	</div>
	
	<div class="row">
		<!-- Table Panel -->
		<div class="col-md-12">
			<div class="card students-card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0"><i class="fa fa-list mr-2"></i>Lista de Estudiantes</h5>
					<a class="btn btn-primary btn-add" href="javascript:void(0)" id="new_student">
						<i class="fa fa-plus mr-2"></i> Nuevo Estudiante
					</a>
				</div>
				<div class="card-body">
					<table class="table table-hover student-table" id="student-table">
						<thead class="bg-light">
							<tr>
								<th class="text-center" width="40px">#</th>
								<th width="120px">DNI</th>
								<th>Nombre</th>
								<th>Información de Contacto</th>
								<th>Nivel</th>
								<th>Grado</th>
								<th>Sección</th>
								<th class="text-center" width="120px">Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 1; // Inicializar contador para numeración
							$school_id = $_SESSION['login_school_id'] ?? 0;
							$student = $conn->query("SELECT * FROM student WHERE school_id = $school_id ORDER BY name ASC");
							
							if ($student->num_rows === 0): 
							?>
								<tr>
									<td colspan="8" class="text-center py-4">
										<div class="empty-state">
											<i class="fa fa-user-graduate text-muted mb-2" style="font-size: 2.5rem;"></i>
											<p class="text-muted">No hay estudiantes registrados. Añada su primer estudiante usando el botón "Nuevo Estudiante".</p>
										</div>
									</td>
								</tr>
							<?php else: ?>
								<?php while ($row = $student->fetch_assoc()): ?>
									<tr>
										<td class="text-center"><?php echo $i++ ?></td> <!-- Mostrar numeración -->
										<td><?php echo $row['id_no'] ?></td>
										<td>
											<span class="student-name"><?php echo ucwords($row['name']) ?></span>
										</td>
										<td>
											<p class="student-info"><i class="fa fa-envelope text-primary"></i> <?php echo $row['email'] ?: 'N/A' ?></p>
											<p class="student-info"><i class="fa fa-phone text-success"></i> <?php echo $row['contact'] ?: 'N/A' ?></p>
											<p class="student-info"><i class="fa fa-map-marker-alt text-danger"></i> <?php echo $row['address'] ?: 'N/A' ?></p>
										</td>
										<td><span class="level-badge"><?php echo $row['nivel'] ?: 'N/A' ?></span></td>
										<td><?php echo $row['grado'] ?: 'N/A' ?></td>
										<td><?php echo $row['seccion'] ?? '-' ?></td>
										<td class="text-center">
											<div class="btn-group btn-group-sm">
												<button class="btn btn-primary edit_student" type="button" data-id="<?php echo $row['id'] ?>" data-toggle="tooltip" title="Editar estudiante">
													<i class="fa fa-edit"></i>
												</button>
												<button class="btn btn-danger delete_student" type="button" data-id="<?php echo $row['id'] ?>" data-toggle="tooltip" title="Eliminar estudiante">
													<i class="fa fa-trash-alt"></i>
												</button>
											</div>
										</td>
									</tr>
								<?php endwhile; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- Table Panel -->
	</div>
</div>

<style>
	/* Estilos para la tabla de estudiantes */
	.student-table {
		border-collapse: separate;
		border-spacing: 0;
		width: 100%;
		border-radius: 8px;
		overflow: hidden;
	}
	
	.student-table thead th {
		font-weight: 600;
		color: #4285f4;
		border-bottom: 2px solid #e3e6f0;
		white-space: nowrap;
		padding: 12px 15px;
	}
	
	.student-table td {
		vertical-align: middle !important;
		padding: 12px 15px;
		border-color: #f1f1f8;
	}
	
	.student-table tbody tr {
		transition: all 0.2s ease;
	}
	
	.student-table tbody tr:hover {
		background-color: #f8f9fc;
		transform: translateY(-1px);
		box-shadow: 0 2px 8px rgba(0,0,0,0.05);
	}
	
	.student-name {
		font-weight: 600;
		color: #333;
		font-size: 1.05rem;
	}
	
	.student-info {
		margin: 5px 0;
		color: #5a5c69;
		font-size: 0.9rem;
	}
	
	.student-info i {
		margin-right: 5px;
		width: 16px;
		text-align: center;
	}
	
	.level-badge {
		background-color: #e8f4fe;
		color: #4285f4;
		padding: 4px 8px;
		border-radius: 4px;
		font-size: 0.85rem;
		font-weight: 500;
	}
	
	.btn-group .btn {
		border-radius: 6px;
		margin: 0 2px;
		box-shadow: 0 2px 4px rgba(0,0,0,0.05);
		transition: all 0.2s;
	}
	
	.btn-group .btn:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 6px rgba(0,0,0,0.1);
	}
	
	.empty-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 30px 20px;
	}
	
	/* Estilos adicionales */
	img {
		max-width: 100px;
		max-height: 150px;
		border-radius: 4px;
	}
	
	/* Mejoras para DataTables */
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
</style>

<script>
	$(document).ready(function() {
		// Inicializar DataTables con configuración mejorada
		$('table').dataTable({
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
			},
			"responsive": true,
			"pageLength": 10,
			"ordering": true,
			"columnDefs": [
				{ "orderable": false, "targets": -1 } // Deshabilitar ordenamiento en la última columna (acciones)
			]
		});
		
		// Inicializar tooltips para botones
		$('[data-toggle="tooltip"]').tooltip();
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
			dataType: 'json',
			success: function(resp) {
				if (resp.status == 1) {
					alert_toast("Estudiante eliminado exitosamente.", 'success');
					setTimeout(function() {
						location.reload(); // Recargar la página después de eliminar
					}, 500);
				} else {
					alert_toast(resp.message || "Error al eliminar el estudiante.", 'danger');
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
	$('#upload_excel').click(function() {
		$('#uploadExcelModal').modal('show');
	});

	// Manejar el envío del formulario de Excel
	$(document).on('submit', '#upload-excel-form', function(e) {
		e.preventDefault(); // Prevenir el comportamiento predeterminado del formulario
		e.stopPropagation(); // Detener la propagación del evento
		
		start_load();
		
		// Verificar que se seleccionó un archivo
		if (!$('#excel_file').val()) {
			alert_toast('Por favor, seleccione un archivo Excel.', 'warning');
			end_load();
			return false;
		}

		var formData = new FormData(this);
		
		$.ajax({
			url: 'ajax.php?action=upload_excel',
			method: 'POST',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function(resp) {
				end_load();
				try {
					// Si la respuesta es un string, convertirla a objeto
					if (typeof resp === 'string') {
						resp = JSON.parse(resp);
					}
					
					if (resp.status === 'success') {
						alert_toast(resp.message, 'success');
						$('#uploadExcelModal').modal('hide');
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
		
		return false; // Importante: retornar false para evitar el envío del formulario
	});
	
	// Mejoras de accesibilidad y UX para el input de archivo Excel
	$(document).on('change', '.custom-file-input', function (e) {
		var fileName = e.target.files[0] ? e.target.files[0].name : '';
		$(this).next('.custom-file-label').html(fileName ? fileName : 'Seleccionar archivo...');
	});
</script>

<!-- Modal para subir Excel -->
<div class="modal fade" id="uploadExcelModal" tabindex="-1" role="dialog" aria-labelledby="uploadExcelModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" style="background: linear-gradient(to right, #f8f9fc, #fff);">
				<h5 class="modal-title" id="uploadExcelModalLabel">
					<i class="fa fa-file-excel text-success mr-2"></i>Subir Archivo Excel
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="upload-info-box mb-4">
					<div class="upload-info-icon">
						<i class="fa fa-info-circle"></i>
					</div>
					<div class="upload-info-content">
						<p>Suba un archivo Excel con la información de estudiantes. Asegúrese de usar el formato correcto.</p>
						<p class="mb-0">Puede <a href="download_format.php" style="color: #4285f4; font-weight: 500;">descargar el formato aquí</a>.</p>
					</div>
				</div>
				
				<form id="upload-excel-form" enctype="multipart/form-data">
					<div class="form-group">
						<label for="excel_file">Seleccionar archivo Excel:</label>
						<div class="custom-file">
							<input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
							<label class="custom-file-label" for="excel_file">Seleccionar archivo...</label>
						</div>
						<small class="form-text text-muted">
							Formatos permitidos: .xls, .xlsx
						</small>
					</div>
					<div class="form-group mt-4">
						<button type="submit" class="btn btn-primary btn-block" style="background: linear-gradient(135deg, #4285f4, #2a75f3); border-color: #2a75f3; box-shadow: 0 2px 5px rgba(66, 133, 244, 0.2);">
							<i class="fa fa-upload mr-2"></i> Subir Excel
						</button>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" style="background: linear-gradient(135deg, #6c757d, #5a6268); border-color: #545b62; box-shadow: 0 2px 5px rgba(108, 117, 125, 0.2);">
					<i class="fa fa-times mr-2"></i>Cancelar
				</button>
			</div>
		</div>
	</div>
</div>

<style>
	.upload-info-box {
		display: flex;
		background-color: #e8f4fe;
		border-left: 4px solid #4285f4;
		border-radius: 6px;
		padding: 15px;
	}
	
	.upload-info-icon {
		color: #4285f4;
		font-size: 1.5rem;
		margin-right: 15px;
		padding-top: 3px;
	}
	
	.upload-info-content {
		flex: 1;
	}
	
	.upload-info-content p {
		margin-bottom: 5px;
		font-size: 0.95rem;
	}
	
	.custom-file-label {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		padding-right: 90px;
	}		.custom-file-input:lang(es)~.custom-file-label::after {
		content: "Explorar";
		background-color: #4285f4;
		color: white;
		transition: all 0.3s ease;
	}
	
	.custom-file-input:focus ~ .custom-file-label {
		border-color: #4285f4;
		box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
	}
	
	.btn-primary {
		background: linear-gradient(135deg, #4285f4, #2a75f3);
		border-color: #2a75f3;
	}
	
	.btn-primary:hover {
		background: linear-gradient(135deg, #2a75f3, #1a65e3);
		border-color: #1a65e3;
	}
</style>