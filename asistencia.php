<?php include('db_connect.php'); ?>
<style>
	.table-asistencia td, .table-asistencia th {
		vertical-align: middle !important;
		text-align: center;
	}
</style>
<div class="main-content-area">
	<div class="row mb-4 mt-4">
		<div class="col-md-12">
			<button class="btn btn-primary float-right" id="nueva_asistencia">
				<i class="fa fa-plus"></i> Nueva Asistencia Manual
			</button>
		</div>
	</div>
	<div class="row mb-2">
		<div class="col-md-3">
			<label>Filtrar por fecha:</label>
			<input type="date" id="filtro_fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
		</div>
		<div class="col-md-3">
			<label>Filtrar por tipo:</label>
			<select id="filtro_tipo" class="form-control">
				<option value="">Todos</option>
				<option value="Entrada">Entrada</option>
				<option value="Salida">Salida</option>
			</select>
		</div>
		<div class="col-md-3">
			<label>Tipo de marcaje:</label>
			<select id="barcode_tipo" class="form-control">
				<option value="Entrada">Entrada</option>
				<option value="Salida">Salida</option>
			</select>
		</div>
		<div class="col-md-3">
			<label>Escanear Código de Barras (DNI):</label>
			<input type="text" id="barcode_input" class="form-control" placeholder="Escanee el código de barras del estudiante" autofocus autocomplete="off">
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<b>Registro de Asistencia</b>
				</div>
				<div class="card-body">
					<table class="table table-bordered table-hover table-asistencia" id="tabla_asistencia">
						<thead>
							<tr>
								<th>#</th>
								<th>Fecha</th>
								<th>DNI</th>
								<th>Nombre</th>
								<th>Tipo</th>
								<th>Hora</th>
								<th>Estado</th>
							</tr>
						</thead>
						<tbody id="asistencia_body">
							<!-- Se llenará por AJAX -->
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal para nueva asistencia manual -->
<div class="modal fade" id="modal_asistencia" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<form id="form_asistencia">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Registrar Asistencia Manual</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Fecha</label>
						<input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
					</div>
					<div class="form-group">
						<label>Estudiante</label>
						<select name="student_id" class="form-control select2" id="student_id_asistencia" required>
							<option value="">Seleccione un estudiante</option>
							<!-- Opciones se cargarán por AJAX -->
						</select>
					</div>
					<div class="form-group">
						<label>Tipo</label>
						<select name="tipo" class="form-control" required>
							<option value="Entrada">Entrada</option>
							<option value="Salida">Salida</option>
						</select>
					</div>
					<div class="form-group">
						<label>Hora Actual</label>
						<input type="text" id="hora_actual" name="hora" class="form-control" readonly>
					</div>
					<div class="form-group">
						<label>Estado</label>
						<select name="estado" class="form-control" required>
							<option value="Presente">Presente</option>
							<option value="Ausente">Ausente</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary" type="submit">Guardar</button>
					<button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	function cargarAsistencia(fecha, tipo) {
		$.ajax({
			url: 'ajax.php?action=get_asistencia',
			method: 'POST',
			data: { fecha: fecha, tipo: tipo },
			dataType: 'json',
			success: function(resp) {
				var html = '';
				if (resp.length > 0) {
					let i = 1;
					resp.forEach(function(row) {
						html += '<tr>';
						html += '<td>' + (i++) + '</td>';
						html += '<td>' + row.fecha + '</td>';
						html += '<td>' + row.id_no + '</td>';
						html += '<td>' + row.name + '</td>';
						html += '<td>' + row.tipo + '</td>';
						html += '<td>' + row.hora + '</td>';
						html += '<td><span class="badge badge-' + (row.estado == 'Presente' ? 'success' : 'danger') + '">' + row.estado + '</span></td>';
						html += '</tr>';
					});
				} else {
					html = '<tr><td colspan="7">Sin registros para esta fecha.</td></tr>';
				}
				$('#asistencia_body').html(html);
			},
			error: function() {
				$('#asistencia_body').html('<tr><td colspan="7">Error al cargar la asistencia.</td></tr>');
			}
		});
	}

	function cargarAlumnosAsistencia() {
		$.ajax({
			url: 'ajax.php?action=get_students_for_asistencia',
			method: 'GET',
			dataType: 'json',
			success: function(resp) {
				var html = '<option value="">Seleccione un estudiante</option>';
				resp.forEach(function(row) {
					html += '<option value="' + row.id + '">' + row.name + ' (' + row.id_no + ')</option>';
				});
				$('#student_id_asistencia').html(html).trigger('change');
			},
			error: function() {
				$('#student_id_asistencia').html('<option value="">Error al cargar estudiantes</option>');
			}
		});
	}

	// Función para actualizar hora en tiempo real
	function actualizarHora() {
		var now = new Date();
		var hours = String(now.getHours()).padStart(2, '0');
		var minutes = String(now.getMinutes()).padStart(2, '0');
		var seconds = String(now.getSeconds()).padStart(2, '0');
		var horaActual = hours + ':' + minutes + ':' + seconds;
		$('#hora_actual').val(horaActual);
	}

	$(document).ready(function() {
		$('.select2').select2({ width: '100%' });
		let fecha = $('#filtro_fecha').val();
		let tipo = $('#filtro_tipo').val();
		cargarAsistencia(fecha, tipo);

		// Iniciar reloj y actualizarlo cada segundo
		actualizarHora();
		setInterval(actualizarHora, 1000);

		$('#filtro_fecha, #filtro_tipo').change(function() {
			cargarAsistencia($('#filtro_fecha').val(), $('#filtro_tipo').val());
		});

		$('#nueva_asistencia').click(function() {
			cargarAlumnosAsistencia();
			actualizarHora(); // Actualizar la hora al abrir el modal
			$('#modal_asistencia').modal('show');
		});
		$('#form_asistencia').submit(function(e) {
			e.preventDefault();
			start_load();
			$.ajax({
				url: 'ajax.php?action=save_asistencia',
				method: 'POST',
				data: $(this).serialize(),
				dataType: 'json',
				success: function(resp) {
					if(resp.status == 1) {
						alert_toast(resp.message, 'success');
						setTimeout(function(){ 
							$('#modal_asistencia').modal('hide');
							cargarAsistencia($('#filtro_fecha').val(), $('#filtro_tipo').val());
							end_load();
						}, 800);
					} else {
						alert_toast(resp.message, 'danger');
						end_load();
					}
				},
				error: function() {
					alert_toast('Error al guardar asistencia.', 'danger');
					end_load();
				}
			});
		});

		// Escaneo de código de barras (DNI) con tipo seleccionable
		$('#barcode_input').on('keypress', function(e) {
			if (e.which == 13) { // Enter
				let dni = $(this).val().trim();
				let fecha = $('#filtro_fecha').val();
				let tipo = $('#barcode_tipo').val();
				if (dni.length === 0) return;
				start_load();
				
				// Obtener la hora actual del cliente para mayor precisión
				var now = new Date();
				var horaActual = String(now.getHours()).padStart(2, '0') + ':' + 
								String(now.getMinutes()).padStart(2, '0') + ':' + 
								String(now.getSeconds()).padStart(2, '0');
				
				$.ajax({
					url: 'ajax.php?action=save_asistencia_barcode',
					method: 'POST',
					data: { 
						dni: dni, 
						fecha: fecha, 
						tipo: tipo,
						hora_actual: horaActual // Enviar la hora actual
					},
					dataType: 'json',
					success: function(resp) {
						if(resp.status == 1) {
							alert_toast(resp.message, 'success');
							cargarAsistencia(fecha, $('#filtro_tipo').val());
						} else {
							alert_toast(resp.message, 'danger');
						}
						$('#barcode_input').val('').focus();
						end_load();
					},
					error: function() {
						alert_toast('Error al registrar asistencia.', 'danger');
						$('#barcode_input').val('').focus();
						end_load();
					}
				});
			}
		});
	});
</script>
