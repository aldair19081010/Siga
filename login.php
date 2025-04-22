<?php
session_start();
include('./db_connect.php');
ob_start();

// Validar si la consulta devuelve resultados antes de usar foreach
$system = $conn->query("SELECT * FROM system_settings LIMIT 1");
if ($system && $system->num_rows > 0) {
	$system = $system->fetch_array();
	foreach ($system as $k => $v) {
		$_SESSION['system'][$k] = $v;
	}
}

ob_end_flush();

// Redirigir si el usuario ya está autenticado
if (isset($_SESSION['login_id'])) {
	header("Location: index.php?page=payments");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<link rel="icon" type="image/x-icon" href="assets/uploads/favicon.png">

	<?php include('./header.php'); ?>
	<?php include('./footer.php'); ?>
</head>
<style>
	body {
		width: 100%;
		height: 100%;
		position: fixed;
		top: 0;
		left: 0;
		background: #f8f9fa;
	}

	main#main {
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		background-image: url(assets/uploads/background.jpg);
		background-size: cover;
		background-position: center;
	}

	.card {
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
		border-radius: 10px;
	}
</style>

<body>
	<main id="main">
		<div class="card col-md-4">
			<div class="card-body">
				<h1 class="text-center mb-4"><img src="assets/uploads/logo.jpg" width="200px"></h1>
				<form id="login-form">
					<div class="form-group">
						<label for="username" class="control-label">Correo</label>
						<input type="text" id="username" name="username" class="form-control" required>
					</div>
					<div class="form-group">
						<label for="password" class="control-label">Contraseña</label>
						<input type="password" id="password" name="password" class="form-control" required>
					</div>
					<br>
					<center><button class="btn btn-primary btn-block" type="submit">Ingresar</button></center>
				</form>
			</div>
		</div>
	</main>
</body>

<script>
	$('#login-form').submit(function(e) {
		e.preventDefault();
		$('#login-form button[type="submit"]').attr('disabled', true).html('Ingresando...');
		if ($(this).find('.alert-danger').length > 0)
			$(this).find('.alert-danger').remove();
		$.ajax({
			url: 'ajax.php?action=login',
			method: 'POST',
			data: $(this).serialize(),
			error: err => {
				console.error("Error en la solicitud AJAX:", err);
				$('#login-form button[type="submit"]').removeAttr('disabled').html('Ingresar');
				alert_toast("Error en el servidor. Intente nuevamente más tarde.", 'danger');
			},
			success: function(resp) {
				try {
					resp = JSON.parse(resp); // Asegurarse de que la respuesta sea JSON válida
					if (resp.status == 1) {
						// Redirigir inmediatamente a la página de payments
						window.location.href = 'index.php?page=payments';
					} else {
						$('#login-form').prepend('<div class="alert alert-danger">' + resp.message + '</div>');
						$('#login-form button[type="submit"]').removeAttr('disabled').html('Ingresar');
					}
				} catch (err) {
					console.error("Error al procesar la respuesta del servidor:", err);
					alert_toast("Error inesperado. Intente nuevamente más tarde.", 'danger');
					$('#login-form button[type="submit"]').removeAttr('disabled').html('Ingresar');
				}
			}
		});
	});
</script>

</html>