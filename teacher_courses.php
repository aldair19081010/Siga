<?php include('db_connect.php'); ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				<button class="btn btn-primary float-right" id="new_teacher_course">
					<i class="fa fa-plus"></i> Asignar Docente a Curso
				</button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header bg-primary text-white">
						<b>Asignaciones de Docentes a Cursos</b>
					</div>
					<div class="card-body">
						<div id="msg"></div>
						<div class="table-responsive">
						<table id="teacher_courses_table" class="table table-bordered table-striped table-hover">
							<thead class="thead-dark">
								<tr>
									<th class="text-center">#</th>
									<th>Docente</th>
									<th>Curso</th>
									<th>Nivel</th>
									<th>Grado</th>
									<th>Sección</th>
									<th class="text-center">Acción</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$i = 1;
							$school_id = $_SESSION['login_school_id'] ?? 0;
							$qry = $conn->query("SELECT tc.*, t.name as tname, ac.name as cname, ac.level as clevel 
								FROM teacher_courses tc 
								LEFT JOIN teacher t ON t.id = tc.teacher_id 
								LEFT JOIN academic_courses ac ON ac.id = tc.course_id 
								WHERE t.school_id = $school_id 
								ORDER BY t.name ASC, ac.name ASC");
							while ($row = $qry->fetch_assoc()):
							?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td><?php echo ucwords($row['tname']) ?></td>
									<td><?php echo ucwords($row['cname']) ?></td>
									<td><?php echo $row['clevel'] ?></td>
									<td><?php echo $row['grado'] ?></td>
									<td><?php echo $row['seccion'] ?? 'U' ?></td>
									<td class="text-center">
										<button class="btn btn-primary btn-sm edit_tc" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
										<button class="btn btn-danger btn-sm delete_tc" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
									</td>
								</tr>
							<?php endwhile; ?>
							</tbody>
						</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- DataTables y jQuery desde CDN para evitar errores de ruta -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#teacher_courses_table').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
            zeroRecords: 'No se encontraron resultados',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros totales)'
        },
        responsive: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[1, 'asc']]
    });
});
$('#new_teacher_course').click(function() {
	uni_modal("Asignar Docente a Curso", "manage_teacher_course.php", "mid-large");
});
$(document).on('click', '.edit_tc', function() {
    uni_modal("Editar Asignación", "manage_teacher_course.php?id=" + $(this).attr('data-id'), "mid-large");
});
$(document).on('submit', '#manage-teacher-course', function(e) {
    e.preventDefault();
    start_load();
    $('#msg').html('');
    $.ajax({
        url: 'ajax.php?action=assign_teacher_course',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(resp) {
            if (resp.status == 1) {
                alert_toast("Asignación guardada exitosamente", 'success');
                setTimeout(function() { location.reload(); }, 1500);
            } else if (resp.status == 2) {
                $('#msg').html('<div class="alert alert-danger">El docente ya está asignado a este curso y grado con esta sección.</div>');
                end_load();
            } else {
                $('#msg').html('<div class="alert alert-danger">' + (resp.message || "Ocurrió un error") + '</div>');
                end_load();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la solicitud AJAX:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
            $('#msg').html('<div class="alert alert-danger">Ocurrió un error en el servidor. Verifique la consola para más detalles.</div>');
            end_load();
        }
    });
});
$('.delete_tc').click(function() {
	_conf("¿Deseas eliminar esta asignación?", "delete_teacher_course", [$(this).attr('data-id')]);
});
function delete_teacher_course(id) {
	start_load();
	$.ajax({
		url: 'ajax.php?action=delete_teacher_course',
		method: 'POST',
		data: { id: id },
		dataType: 'json',
		success: function(resp) {
			if (resp.status == 1) {
				alert_toast("Asignación eliminada exitosamente.", 'success');
				setTimeout(function() { location.reload(); }, 500);
			} else {
				alert_toast(resp.message || "Error al eliminar la asignación.", 'danger');
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
