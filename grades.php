<?php
include('db_connect.php');
$login_type = $_SESSION['login_type'] ?? null;
$is_teacher = ($login_type == 2);
$teacher_id = $_SESSION['login_teacher_id'] ?? null;

// Solo los docentes pueden acceder a esta página
if (!$is_teacher || !$teacher_id) {
	echo "<div style=\"max-width: 600px; margin: 80px auto 0 auto;\">
		<div class='alert alert-danger text-center' style=\"font-size:1.2rem;\">
			Solo los docentes pueden gestionar notas.
		</div>
	</div>";
	exit;
}

// Obtener estadísticas para el dashboard
$stats = [
    'total_evaluations' => 0,
    'recent_evaluations' => 0,
    'pending_grades' => 0,
    'courses_count' => 0
];

// Total de evaluaciones
$total_q = $conn->query("SELECT COUNT(*) as total FROM evaluations WHERE teacher_id = $teacher_id");
if($total_q && $total_q->num_rows > 0) {
    $stats['total_evaluations'] = $total_q->fetch_assoc()['total'];
}

// Evaluaciones recientes (últimos 7 días)
$recent_q = $conn->query("SELECT COUNT(*) as total FROM evaluations 
                          WHERE teacher_id = $teacher_id 
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if($recent_q && $recent_q->num_rows > 0) {
    $stats['recent_evaluations'] = $recent_q->fetch_assoc()['total'];
}

// Total de cursos que imparte (corregido para contar asignaciones únicas de cursos)
$courses_q = $conn->query("SELECT COUNT(*) as total FROM (
                           SELECT DISTINCT ac.name, ac.level, tc.grado, tc.seccion 
                           FROM teacher_courses tc
                           INNER JOIN academic_courses ac ON tc.course_id = ac.id
                           WHERE tc.teacher_id = $teacher_id
                          ) as unique_courses");
if($courses_q && $courses_q->num_rows > 0) {
    $stats['courses_count'] = $courses_q->fetch_assoc()['total'];
}
?>
<style>
.dashboard-card {
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    margin-bottom: 20px;
}
.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.dashboard-card .icon {
    font-size: 28px;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    color: white;
}
.dashboard-card .card-title {
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 5px;
    color: #6c757d;
}
.dashboard-card .card-value {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 0;
    color: #2c4964;
}
.btn-action {
    border-radius: 4px;
    padding: 6px 10px;
    min-width: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 5px rgba(0,0,0,0.1);
}
.evaluation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.evaluation-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c4964;
}
.grades-table {
    box-shadow: 0 2px 15px rgba(0,0,0,0.03);
}
.grades-table th {
    background-color: #f8f9fa;
    font-weight: 500;
    border-bottom-width: 2px;
    color: #495057;
}
.grades-table tbody tr {
    transition: background-color 0.2s;
}
.grades-table tbody tr:hover {
    background-color: rgba(252, 125, 28, 0.05);
}
.evaluation-type-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    background-color: rgba(252, 125, 28, 0.1);
    color: #fc7d1c;
    display: inline-block;
}
.btn-floating {
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    bottom: 30px;
    right: 30px;
    box-shadow: 0 5px 15px rgba(252, 125, 28, 0.3);
    z-index: 1000;
    transition: all 0.3s;
}
.btn-floating:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(252, 125, 28, 0.4);
}
.btn-floating i {
    font-size: 18px;
}

/* Estilos responsivos para los botones de acción */
@media (max-width: 768px) {
    .btn-action {
        padding: 5px 8px;
        min-width: 32px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    th[width="18%"] {
        min-width: 140px;
    }
}

@media (max-width: 576px) {
    .d-flex.justify-content-center {
        flex-wrap: nowrap;
    }
    
    .btn-action {
        padding: 4px 6px;
        min-width: 28px;
    }
    
    .btn-action .badge {
        font-size: 0.65rem;
    }
}
</style>

<div class="container-fluid py-4">    <!-- Dashboard Cards -->
    <div class="row mb-4">
        <div class="col-md-4 col-12 mb-md-0 mb-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="icon bg-primary"><i class="fa fa-book"></i></div>
                    <div class="card-title">Total Evaluaciones</div>
                    <div class="card-value"><?php echo $stats['total_evaluations']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12 mb-md-0 mb-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="icon" style="background-color: #fc7d1c;"><i class="fa fa-calendar-alt"></i></div>
                    <div class="card-title">Evaluaciones Recientes (7 días)</div>
                    <div class="card-value"><?php echo $stats['recent_evaluations']; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="icon bg-success"><i class="fa fa-graduation-cap"></i></div>
                    <div class="card-title">Cursos Asignados</div>
                    <div class="card-value"><?php echo $stats['courses_count']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card">
        <div class="card-body">
            <div class="evaluation-header">
                <div class="evaluation-title">
                    <i class="fa fa-clipboard-list mr-2"></i> Mis Evaluaciones
                </div>
                <button class="btn btn-primary" id="new_evaluation">
                    <i class="fa fa-plus"></i> Nueva Evaluación
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover grades-table" id="grades_table">
                    <thead>                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Evaluación</th>
                            <th width="10%">Tipo</th>
                            <th width="10%">Curso</th>
                            <th width="10%">Nivel</th>
                            <th width="5%">Grado</th>
                            <th width="5%">Sección</th>
                            <th width="10%">Bimestre</th>
                            <th width="18%">Acción</th>
                        </tr>
                    </thead>							<tbody>
								<?php
								$i = 1;
								$q = $conn->query("SELECT e.*, ac.name as course_name, ac.level, tc.grado, tc.seccion,
                                     (SELECT COUNT(*) FROM evaluation_grades WHERE evaluation_id = e.id) as grades_count
                                     FROM evaluations e
                                     INNER JOIN teacher_courses tc ON tc.id = e.teacher_course_id
                                     INNER JOIN academic_courses ac ON ac.id = tc.course_id
                                     WHERE e.teacher_id = $teacher_id
                                     ORDER BY e.created_at DESC");
								while ($row = $q->fetch_assoc()):
                                    // Determinar color para el tipo de evaluación
                                    $type_color = '';
                                    $type_bg = 'rgba(252, 125, 28, 0.1)';
                                    $type_text = '#fc7d1c';
                                    
                                    switch(strtolower($row['type'])) {
                                        case 'examen parcial':
                                            $type_bg = 'rgba(0, 123, 255, 0.1)';
                                            $type_text = '#007bff';
                                            break;
                                        case 'examen final':
                                            $type_bg = 'rgba(220, 53, 69, 0.1)';
                                            $type_text = '#dc3545';
                                            break;
                                        case 'exposición':
                                            $type_bg = 'rgba(23, 162, 184, 0.1)';
                                            $type_text = '#17a2b8';
                                            break;
                                        case 'trabajo en clase':
                                            $type_bg = 'rgba(40, 167, 69, 0.1)';
                                            $type_text = '#28a745';
                                            break;
                                        case 'quiz':
                                            $type_bg = 'rgba(108, 117, 125, 0.1)';
                                            $type_text = '#6c757d';
                                            break;
                                    }
                                    
                                    // Estado de notas
                                    $has_grades = $row['grades_count'] > 0;
								?>
								<tr>
									<td class="align-middle"><?php echo $i++ ?></td>
									<td class="align-middle">
                                        <div class="d-flex flex-column">
                                            <strong><?php echo htmlspecialchars($row['title']) ?></strong>
                                            <small class="text-muted"><?php echo substr(htmlspecialchars($row['description']), 0, 30) . (strlen($row['description']) > 30 ? '...' : ''); ?></small>
                                        </div>
                                    </td>
									<td class="align-middle">
                                        <span class="evaluation-type-badge" style="background-color: <?php echo $type_bg; ?>; color: <?php echo $type_text; ?>">
                                            <?php echo htmlspecialchars($row['type']) ?>
                                        </span>
                                    </td>
									<td class="align-middle"><?php echo htmlspecialchars($row['course_name']) ?></td>
									<td class="align-middle"><?php echo htmlspecialchars($row['level']) ?></td>
									<td class="align-middle text-center"><?php echo htmlspecialchars($row['grado']) ?></td>
									<td class="align-middle text-center"><?php echo htmlspecialchars($row['seccion'] ?? 'U') ?></td>
									<td class="align-middle">
                                        <?php if($row['bimestre']): ?>
                                        <span class="badge badge-pill badge-light">
                                            <?php echo $row['bimestre'] ?>° Bimestre
                                        </span>
                                        <?php else: ?>
                                        <span class="badge badge-pill badge-secondary">No asignado</span>
                                        <?php endif; ?>
                                    </td>									<td class="align-middle text-center">
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-action btn-outline-info mx-1 enter_grades" data-id="<?php echo $row['id'] ?>" data-toggle="tooltip" title="Gestionar notas">
                                                <i class="fa fa-pen"></i><?php echo $has_grades ? ' <span class="badge badge-info">'.$row['grades_count'].'</span>' : '' ?>
                                            </button>
                                            <button class="btn btn-action btn-outline-primary mx-1 edit_evaluation" data-id="<?php echo $row['id'] ?>" data-toggle="tooltip" title="Editar evaluación">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-action btn-outline-danger mx-1 delete_evaluation" data-id="<?php echo $row['id'] ?>" data-toggle="tooltip" title="Eliminar evaluación">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </div>
									</td>
								</tr>
								<?php endwhile; ?>
							</tbody>						</table>
					</div>
                </div>
            </div>
            
            <!-- Botón flotante para nueva evaluación -->
            <button class="btn btn-primary btn-floating d-md-none" id="new_evaluation_floating">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable con opciones mejoradas
	$('#grades_table').dataTable({
        "language": {
            "search": "Buscar:",
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron evaluaciones",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ evaluaciones",
            "infoEmpty": "No hay evaluaciones disponibles",
            "infoFiltered": "(filtrado de _MAX_ evaluaciones totales)",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "pageLength": 10,
        "responsive": true,
        "order": [[0, 'desc']],
        "columnDefs": [
            { "targets": [8], "orderable": false }
        ]
    });
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Efectos de tarjetas al cargar la página
    $('.dashboard-card').each(function(index) {
        $(this).css('opacity', 0);
        $(this).animate(
            { opacity: 1 },
            { duration: 300, delay: index * 100 }
        );
    });

    // Evento para nueva evaluación desde el botón principal o flotante
    $('#new_evaluation, #new_evaluation_floating').click(function() {
        uni_modal("Nueva Evaluación", "manage_evaluation.php", "mid-large");
    });

    // Delegación de eventos para botones de la tabla
    $(document).on('click', '.edit_evaluation', function() {
        uni_modal("Editar Evaluación", "manage_evaluation.php?id=" + $(this).attr('data-id'), "mid-large");
    });

    $(document).on('click', '.delete_evaluation', function() {
        _conf("¿Deseas eliminar esta evaluación?", "delete_evaluation", [$(this).attr('data-id')]);
    });

    $(document).on('click', '.enter_grades', function() {
        uni_modal("Ingresar Notas", "manage_evaluation_grades.php?evaluation_id=" + $(this).attr('data-id'), "large");
    });
});

function delete_evaluation(id) {
	start_load();
	$.ajax({
		url: 'ajax.php?action=delete_evaluation',
		method: 'POST',
		data: { id: id },
		dataType: 'json',
		success: function(resp) {
			if (resp.status == 1) {
				alert_toast("Evaluación eliminada exitosamente.", 'success');
				setTimeout(function() { location.reload(); }, 500);
			} else {
				alert_toast(resp.message || "Error al eliminar la evaluación.", 'danger');
				end_load();
			}
		},
		error: function() {
			alert_toast("Error en el servidor.", 'danger');
			end_load();
		}
	});
}
</script>
