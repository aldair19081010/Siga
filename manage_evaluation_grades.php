<?php
include 'db_connect.php';
session_start();
$evaluation_id = intval($_GET['evaluation_id'] ?? 0);
if (!$evaluation_id) {
	echo "<div class='alert alert-danger'>Evaluación no encontrada.</div>";
	exit;
}
$eval = $conn->query("SELECT * FROM evaluations WHERE id = $evaluation_id")->fetch_assoc();
if (!$eval) {
	echo "<div class='alert alert-danger'>Evaluación no encontrada.</div>";
	exit;
}
$teacher_course_id = $eval['teacher_course_id'] ?? 0;
$course_name = $level = $grado = $seccion = '';
if ($teacher_course_id) {
	$q = $conn->query("SELECT ac.name as course_name, ac.level, tc.grado, tc.seccion
		FROM teacher_courses tc
		INNER JOIN academic_courses ac ON ac.id = tc.course_id
		WHERE tc.id = $teacher_course_id LIMIT 1");
	if ($q && $row = $q->fetch_assoc()) {
		$course_name = $row['course_name'];
		$level = $row['level'];
		$grado = $row['grado'];
		$seccion = $row['seccion'];
	}
}
$school_id = $_SESSION['login_school_id'] ?? 0;
// Obtener estudiantes del grado, sección y nivel
$students = $conn->query("SELECT * FROM student 
    WHERE grado = '".$conn->real_escape_string($grado)."' 
    AND seccion = '".$conn->real_escape_string($seccion)."'
    AND LOWER(nivel) = '".strtolower($level)."'
    AND school_id = $school_id
    ORDER BY name ASC");

if ($students->num_rows === 0) {
    echo "<div class='alert alert-warning'>No hay estudiantes registrados para el grado $grado, sección $seccion.</div>";
}

// Obtener competencias asociadas a la evaluación
$competencias = [];
$q_comp = $conn->query("SELECT c.* FROM evaluation_competencias ec INNER JOIN competencias c ON c.id = ec.competencia_id WHERE ec.evaluation_id = $evaluation_id ORDER BY c.name ASC");
while ($row = $q_comp->fetch_assoc()) {
	$competencias[] = $row;
}
?>
<style>
	.eval-header {
		background-color: #f8f9fa;
		border-radius: 8px;
		padding: 20px 25px;
		box-shadow: 0 2px 4px rgba(0,0,0,0.05);
		margin-bottom: 25px;
	}
	.eval-title {
		color: #3f51b5;
		font-size: 1.5rem;
		margin-bottom: 15px;
		border-bottom: 2px solid #e9ecef;
		padding-bottom: 10px;
	}
	.eval-desc {
		font-style: italic;
		color: #6c757d;
		margin-bottom: 15px;
	}
	.eval-details {
		background-color: #e9f5fe;
		border-left: 4px solid #3f51b5;
		padding: 12px 15px;
		border-radius: 4px;
		font-size: 0.95rem;
	}
	.eval-details span {
		display: inline-block;
		margin-right: 15px;
	}
	.eval-details strong {
		color: #495057;
	}
	.competencia-heading {
		background-color: #f1f8ff;
		padding: 10px 15px;
		border-radius: 5px;
		border-left: 4px solid #007bff;
		margin-top: 20px;
		margin-bottom: 10px;
		font-weight: 600;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.competencia-heading .percentage {
		background-color: #007bff;
		color: white;
		padding: 3px 10px;
		border-radius: 20px;
		font-size: 0.85rem;
	}
	.table thead th {
		background-color: #f5f7fb;
		border-bottom: 2px solid #dee2e6;
		vertical-align: middle;
	}
	.submit-btn {
		background-color: #3f51b5;
		border-color: #3f51b5;
	}
	.submit-btn:hover {
		background-color: #324191;
		border-color: #324191;
	}
</style>

<div class="container-fluid">
	<div class="eval-header">
		<h4 class="eval-title"><?php echo htmlspecialchars($eval['title']) ?></h4>
		<p class="eval-desc"><?php echo htmlspecialchars($eval['description']) ?: 'Sin descripción disponible'; ?></p>
		<div class="eval-details">
			<span><strong>Curso:</strong> <?php echo htmlspecialchars($course_name) ?></span>
			<span><strong>Nivel:</strong> <?php echo htmlspecialchars($level) ?></span>
			<span><strong>Grado:</strong> <?php echo htmlspecialchars($grado) ?></span>
			<span><strong>Sección:</strong> <?php echo htmlspecialchars($seccion) ?></span>
			<span><strong>Bimestre:</strong> <?php echo isset($eval['bimestre']) && $eval['bimestre'] ? $eval['bimestre'].'° Bimestre' : 'No asignado'; ?></span>
		</div>
	</div>
	
	<form id="manage-evaluation-grades">
		<input type="hidden" name="evaluation_id" value="<?php echo $evaluation_id ?>">		<?php if (count($competencias) > 0): ?>
			<?php foreach ($competencias as $comp): ?>
				<div class="competencia-heading">
					<div>Competencia: <?php echo htmlspecialchars($comp['name']) ?></div>
					<div class="percentage"><?php echo $comp['percentage'] ?>%</div>
				</div>
				<table class="table table-bordered table-hover mb-4">
					<thead>
						<tr>
							<th width="5%">#</th>
							<th width="45%">Estudiante</th>
							<th width="20%">DNI</th>
							<th width="30%">Nota</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$i = 1;
					$students->data_seek(0); // Reiniciar puntero
					while ($stu = $students->fetch_assoc()):
						// Buscar nota existente por competencia
						$grade = '';
						$qg = $conn->query("SELECT grade FROM evaluation_grades WHERE evaluation_id = $evaluation_id AND student_id = {$stu['id']} AND competencia_id = {$comp['id']}");
						if ($qg && $qg->num_rows) {
							$grade = $qg->fetch_assoc()['grade'];
						}
					?>
					<tr>
						<td><?php echo $i++ ?></td>
						<td><?php echo ucwords($stu['name']) ?></td>
						<td><?php echo $stu['id_no'] ?></td>						<td>
							<input type="number" step="any" min="0" max="20" name="grades[<?php echo $comp['id'] ?>][<?php echo $stu['id'] ?>]" 
								class="form-control" value="<?php echo htmlspecialchars($grade) ?>" 
								style="text-align: center; font-weight: bold; width: 80px; margin: 0 auto;">
						</td>
					</tr>
					<?php endwhile; ?>					<?php if ($students->num_rows === 0): ?>
					<tr>
						<td colspan="4" class="text-center" style="padding: 20px; color: #dc3545;">
							<i class="fa fa-users" style="margin-right: 5px;"></i>
							No hay estudiantes registrados en esta sección y grado
						</td>
					</tr>
					<?php endif; ?>
					</tbody>
				</table>
			<?php endforeach; ?>		<?php else: ?>
			<div class="alert alert-warning text-center" style="margin-top: 20px;">
				<i class="fa fa-exclamation-triangle" style="margin-right: 5px;"></i> 
				No hay competencias asociadas a esta evaluación.
			</div>
		<?php endif; ?>
		<div class="form-group text-right mt-4">
			
		</div>
	</form>
</div>
<script>
$('#manage-evaluation-grades').submit(function(e) {
	e.preventDefault();
	start_load();
	$.ajax({
		url: 'ajax.php?action=save_evaluation_grades',
		method: 'POST',
		data: $(this).serialize(),
		dataType: 'json',
		success: function(resp) {
			if (resp.status == 1) {
				alert_toast(resp.message, 'success');
				setTimeout(function() { location.reload(); }, 500);
			} else {
				alert_toast(resp.message, 'danger');
				end_load();
			}
		},
		error: function() {
			alert_toast("Error en el servidor.", 'danger');
			end_load();
		}
	});
});
</script>
