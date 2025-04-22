<?php include 'db_connect.php'; ?>
<div class="container-fluid">
	<form id="upload-excel-form" enctype="multipart/form-data">
		<div class="form-group">
			<label for="excel_file" class="control-label">Seleccionar Archivo Excel</label>
			<input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xls, .xlsx" required>
		</div>
		<div class="form-group">
			<button class="btn btn-primary" type="submit">Subir</button>
		</div>
	</form>
</div>

<script>
	$('#upload-excel-form').submit(function(e) {
		e.preventDefault();
		start_load();
		var formData = new FormData($(this)[0]);

		// Validar que se haya seleccionado un archivo
		if (!$('#excel_file').val()) {
			alert_toast("Por favor, seleccione un archivo Excel.", 'danger');
			end_load();
			return;
		}

		$.ajax({
			url: 'ajax.php?action=upload_excel',
			method: 'POST',
			data: formData,
			contentType: false,
			processData: false,
			success: function(resp) {
				console.log("Respuesta del servidor:", resp); // Registrar la respuesta en la consola
				try {
					resp = JSON.parse(resp); // Asegurarse de que la respuesta sea un JSON válido
					if (resp.status == 1) {
						alert_toast(resp.message, 'success'); // Mostrar mensaje de éxito
						setTimeout(function() {
							location.reload(); // Recargar la página después de un tiempo
						}, 1500);
					} else {
						alert_toast(resp.message, 'danger'); // Mostrar mensaje de error
						end_load();
					}
				} catch (err) {
					console.error("Error al procesar la respuesta del servidor:", err);
					alert_toast("Error inesperado. Verifique el archivo y vuelva a intentarlo.", 'danger');
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
