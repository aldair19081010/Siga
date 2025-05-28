<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db_connect.php');
$teacher_id = $_SESSION['login_teacher_id'] ?? null;
$login_type = $_SESSION['login_type'] ?? null;
if ($login_type != 2 || !$teacher_id) {
	echo "<div style=\"max-width: 600px; margin: 80px auto 0 auto;\">
		<div class='alert alert-danger text-center' style=\"font-size:1.2rem;\">
			Solo los docentes pueden gestionar competencias.
		</div>
	</div>";
	exit;
}

// CRUD de competencias globales por docente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	$name = trim($_POST['name'] ?? '');
	$percentage = floatval($_POST['percentage'] ?? 0);
	$id = intval($_POST['id'] ?? 0);
	if ($action === 'add' && $name && $percentage > 0) {
		$stmt = $conn->prepare("INSERT INTO competencias (teacher_id, name, percentage) VALUES (?, ?, ?)");
		$stmt->bind_param('isd', $teacher_id, $name, $percentage);
		$stmt->execute();
		if (!headers_sent()) {
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit;
		} else {
			echo '<script>window.location.href="' . $_SERVER['REQUEST_URI'] . '";</script>';
			exit;
		}
	}
	if ($action === 'edit' && $id && $name && $percentage > 0) {
		$stmt = $conn->prepare("UPDATE competencias SET name=?, percentage=? WHERE id=? AND teacher_id=?");
		$stmt->bind_param('sdii', $name, $percentage, $id, $teacher_id);
		$stmt->execute();
		if (!headers_sent()) {
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit;
		} else {
			echo '<script>window.location.href="' . $_SERVER['REQUEST_URI'] . '";</script>';
			exit;
		}
	}
	if ($action === 'delete' && $id) {
		$stmt = $conn->prepare("DELETE FROM competencias WHERE id=? AND teacher_id=?");
		$stmt->bind_param('ii', $id, $teacher_id);
		$stmt->execute();
		if (!headers_sent()) {
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit;
		} else {
			echo '<script>window.location.href="' . $_SERVER['REQUEST_URI'] . '";</script>';
			exit;
		}
	}
}

// Listar competencias del docente
$competencias = $conn->query("SELECT * FROM competencias WHERE teacher_id = $teacher_id ORDER BY name ASC");
?>
<div class="container-fluid">
	<div class="col-lg-8 mx-auto mt-4">
		<div class="card competencias-card">
			<div class="card-header bg-gradient-primary text-white">
				<div class="d-flex align-items-center">
					<i class="fa fa-star-half-o mr-2" style="font-size: 1.2rem;"></i>
					<b>Competencias Globales</b>
				</div>
			</div>
			<div class="card-body">
				<?php if (isset($_GET['success'])): ?>
					<div class="alert alert-success animate__animated animate__fadeIn">
						<i class="fa fa-check-circle mr-2"></i> Acción realizada correctamente.
					</div>
				<?php endif; ?>
				<div class="competencias-info mb-4">
					<div class="d-flex align-items-center mb-2">
						<i class="fa fa-info-circle text-info mr-2"></i>
						<h6 class="mb-0 font-weight-bold">¿Qué son las competencias globales?</h6>
					</div>
					<p class="text-muted mb-0">Las competencias globales definen los aspectos a evaluar en sus cursos y el peso porcentual de cada uno en la calificación final.</p>
				</div>
				<form method="POST" class="form-inline mb-4 add-competencia-form">
					<input type="hidden" name="action" value="add">
					<input type="text" name="name" class="form-control mr-2 competencia-input" placeholder="Nombre de la competencia" required>
					<input type="number" name="percentage" class="form-control mr-2 percentage-input" placeholder="%" min="1" max="100" step="0.01" required>
					<button class="btn btn-success btn-add-competencia" type="submit"><i class="fa fa-plus mr-1"></i> Agregar</button>
				</form>				<div class="table-responsive">
					<table class="table table-bordered table-hover competencias-table">
						<thead class="bg-light">
							<tr>
								<th>Nombre</th>
								<th width="140px">Porcentaje (%)</th>
								<th width="100px" class="text-center">Acción</th>
							</tr>
						</thead>
						<tbody>
						<?php if($competencias->num_rows == 0): ?>
							<tr>
								<td colspan="3" class="text-center py-4">
									<div class="empty-state">
										<i class="fa fa-info-circle text-muted mb-2" style="font-size: 2rem;"></i>
										<p class="text-muted">No hay competencias registradas. Agregue su primera competencia usando el formulario superior.</p>
									</div>
								</td>
							</tr>
						<?php else: ?>
							<?php while($c = $competencias->fetch_assoc()): ?>
								<tr class="competencia-row">
									<form method="POST" class="form-inline competencia-form">
										<input type="hidden" name="id" value="<?php echo $c['id'] ?>">
										<td>
											<input type="text" name="name" value="<?php echo htmlspecialchars($c['name']) ?>" class="form-control competencia-input" required>
										</td>
										<td>
											<div class="input-group percentage-input-container">
												<input type="number" name="percentage" value="<?php echo $c['percentage'] ?>" class="form-control percentage-input" min="1" max="100" step="0.01" required>
												<div class="input-group-append">
													<span class="input-group-text">%</span>
												</div>
											</div>
										</td>
										<td class="text-center">
											<div class="btn-group btn-group-sm">
												<input type="hidden" name="action" value="edit">
												<button class="btn btn-primary btn-save mr-1" type="submit" data-toggle="tooltip" title="Guardar cambios">
													<i class="fa fa-save"></i>
												</button>
									</form>
									<form method="POST" style="display:inline-block">
										<input type="hidden" name="id" value="<?php echo $c['id'] ?>">
										<input type="hidden" name="action" value="delete">
										<button class="btn btn-danger btn-delete" type="submit" data-toggle="tooltip" title="Eliminar competencia" 
											onclick="return confirm('¿Está seguro que desea eliminar esta competencia?\nEsta acción no se puede deshacer.')">
											<i class="fa fa-trash"></i>
										</button>
									</form>
											</div>
										</td>
								</tr>
							<?php endwhile; ?>
						<?php endif; ?>					</tbody>
				</table>
				</div>
				
				<div class="total-percentage-container mt-4" id="totalPercentageContainer">
					<!-- El total de porcentajes se mostrará aquí -->
				</div>
				
				<div class="card-footer bg-light mt-3 border-top">
					<div class="d-flex justify-content-between align-items-center">
						<div class="text-muted">
							<small><i class="fa fa-info-circle mr-1"></i> Las competencias se utilizan para calcular los promedios finales por bimestre.</small>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	/* Unificación de colores con el resto del sistema */
	.text-info {
		color: #4285f4 !important;
	}
	.text-primary {
		color: #4285f4 !important;
	}
	/* Mejoras de estilo para el card con el nuevo color */
	.competencias-card {
		border-top: 4px solid #4285f4;
		transition: all 0.3s ease;
	}
	.competencias-card:hover {
		box-shadow: 0 6px 18px rgba(66, 133, 244, 0.15);
	}
	.competencias-card { 
		border-radius: 10px; 
		box-shadow: 0 4px 12px rgba(0,0,0,0.1);
		border: none;
		overflow: hidden;
	}	.bg-gradient-primary {
		background: linear-gradient(135deg, #4285f4, #2a75f3);
	}
	.card-header {
		padding: 0.85rem 1.5rem;
		border-bottom: 0;
	}
	.card-body {
		padding: 1.5rem;
	}
	.card-footer {
		padding: 0.75rem 1.5rem;
	}	.competencias-info {
		background-color: #f8f9fc;
		border-left: 4px solid #4285f4;
		padding: 15px;
		border-radius: 4px;
	}
	.add-competencia-form {
		background-color: #f9f9f9;
		padding: 15px;
		border-radius: 8px;
		box-shadow: 0 2px 5px rgba(0,0,0,0.05);
	}
	.competencia-input {
		min-width: 280px;
		border-radius: 6px;
	}
	.percentage-input {
		width: 80px;
		text-align: right;
		border-radius: 6px 0 0 6px;
		padding-right: 5px;
	}
	.input-group-append .input-group-text {
		padding: 0.375rem 0.6rem;
		background-color: #f2f4f8;
		border-left: 0;
		border-radius: 0 6px 6px 0;
	}
	.btn-add-competencia {
		box-shadow: 0 2px 6px rgba(40, 167, 69, 0.2);
		border-radius: 6px;
		transition: all 0.3s;
	}
	.btn-add-competencia:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
	}
	.competencias-table {
		border-radius: 6px;
		overflow: hidden;
		box-shadow: 0 2px 8px rgba(0,0,0,0.04);
	}	.competencias-table thead th {
		border-bottom: 2px solid #e3e6f0;
		font-weight: 600;
		color: #4285f4;
	}	.competencia-row:hover {
		background-color: rgba(66, 133, 244, 0.05);
	}
	.competencia-form {
		width: 100%;
	}	.btn-save {
		background: linear-gradient(135deg, #4285f4, #2a75f3);
		border: none;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		transition: all 0.2s;
	}
	.btn-save:hover {
		background: linear-gradient(135deg, #2a75f3, #1a65e3);
		transform: translateY(-1px);
		box-shadow: 0 3px 5px rgba(0,0,0,0.15);
	}
	.btn-delete {
		background: linear-gradient(135deg, #e74a3b, #c44a3b);
		border: none;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		transition: all 0.2s;
	}
	.btn-delete:hover {
		background: linear-gradient(135deg, #c44a3b, #a53128);
		transform: translateY(-1px);
		box-shadow: 0 3px 5px rgba(0,0,0,0.15);
	}
	.empty-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 20px;
	}	.total-percentage-container {
		padding: 15px 20px;
		border-radius: 8px;
		background-color: #f8f9fc;
		border-left: 4px solid #4285f4;
		box-shadow: 0 2px 8px rgba(0,0,0,0.05);
		margin-bottom: 15px;
	}
	.total-percentage-warning {
		color: #e74a3b;
		font-weight: 600;
		font-size: 1.2rem;
		margin-right: 5px;
	}	.total-percentage-ok {
		color: #4285f4;
		font-weight: 600;
		font-size: 1.2rem;
		margin-right: 5px;
	}.percentage-input-container {
		width: 120px;
		max-width: 100%;
		display: inline-flex;
	}
	
	@media (max-width: 768px) {
		.form-inline {
			flex-direction: column;
			align-items: stretch;
		}
		.competencia-input, .btn-add-competencia {
			margin-right: 0 !important;
			margin-bottom: 10px;
			width: 100%;
		}
		.percentage-input-container {
			width: 100%;
			margin-bottom: 10px;
		}
		.percentage-input {
			width: calc(100% - 40px);
		}
		.btn-group-sm {
			display: flex;
			justify-content: center;
		}
	}
</style>

<script>
$(document).ready(function() {
	// Inicializar tooltips
	$('[data-toggle="tooltip"]').tooltip();
	
	// Función para calcular y mostrar el porcentaje total
	function updateTotalPercentage() {
		let total = 0;
		$('input[name="percentage"]').each(function() {
			total += parseFloat($(this).val() || 0);
		});
				let statusClass = 'total-percentage-warning';
		let statusIcon = 'fa-exclamation-triangle';
		let statusMessage = 'El porcentaje total debe ser exactamente 100%.';
				if (total == 100) {
			statusClass = 'total-percentage-ok';
			statusIcon = 'fa-check-circle';
			statusMessage = 'El porcentaje total es correcto. Las competencias suman 100%.';
		}
				$('#totalPercentageContainer').html(`
			<div class="d-flex align-items-center">
				<i class="fa ${statusIcon} ${statusClass} mr-3" style="font-size: 1.5rem;"></i>
				<div>
					<div class="d-flex align-items-baseline mb-1">
						<span class="font-weight-bold" style="font-size: 1.1rem; margin-right: 6px;">Total:</span>
						<span class="${statusClass}" style="letter-spacing: 0.5px;">${total}%</span>
					</div>
					<p class="mb-0 text-muted" style="font-size: 0.9rem;">${statusMessage}</p>
				</div>
			</div>
		`);
	}
	
	// Calcular el porcentaje total al cargar la página
	updateTotalPercentage();
		// Actualizar el porcentaje total cuando cambie cualquier valor
	$(document).on('input', 'input[name="percentage"]', function() {
		// Asegurarnos de que el campo mantenga un formato adecuado
		let value = parseFloat($(this).val() || 0);
		if (value > 100) {
			$(this).val(100);
		}
		updateTotalPercentage();
	});
	
	// Añadir efectos visuales a los botones
	$('.btn').on('mousedown', function() {
		$(this).css('transform', 'scale(0.95)');
	});
	
	$('.btn').on('mouseup mouseleave', function() {
		$(this).css('transform', '');
	});
	
	// Efecto de highlight para filas recién modificadas
	$('.competencia-form').on('submit', function() {
		// Guardar el formulario normalmente
	});
	
	// Si hay mensaje de éxito, ocultarlo después de 3 segundos
	setTimeout(function() {
		$('.alert-success').fadeOut(500);
	}, 3000);
});
</script>
