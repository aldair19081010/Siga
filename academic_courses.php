<?php include('db_connect.php'); ?>

<!-- DataTables y jQuery desde CDN para evitar errores de ruta -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12">
                <button class="btn btn-primary float-right" id="new_course">
                    <i class="fa fa-plus"></i> Nuevo Curso
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Lista de Cursos Académicos</b>
                    </div>
                    <div class="card-body">
                        <table class="table table-condensed table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Nombre</th>
                                    <th>Nivel</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                // Filtrar cursos por el colegio del administrador
                                $school_id = $_SESSION['login_school_id'] ?? 0;
                                $courses = $conn->query("SELECT * FROM academic_courses WHERE school_id = $school_id ORDER BY name ASC");
                                while($row = $courses->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td><?php echo ucwords($row['name']) ?></td>
                                    <td><?php echo $row['level'] ?></td>
                                    <td><?php echo $row['description'] ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-primary edit_course" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-danger delete_course" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash-alt"></i></button>
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

<script>
$(document).ready(function() {
    $('table').DataTable({
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

$('#new_course').click(function() {
	uni_modal("Nuevo Curso Académico", "manage_academic_course.php", "mid-large");
});
$('.edit_course').click(function() {
	uni_modal("Editar Curso Académico", "manage_academic_course.php?id=" + $(this).attr('data-id'), "mid-large");
});
$('.delete_course').click(function() {
	_conf("¿Deseas eliminar este curso académico?", "delete_academic_course", [$(this).attr('data-id')]);
});
function delete_academic_course(id) {
	start_load();
	$.ajax({
		url: 'ajax.php?action=delete_academic_course',
		method: 'POST',
		data: { id: id },
		dataType: 'json',
		success: function(resp) {
			if (resp.status == 1) {
				alert_toast("Curso eliminado exitosamente.", 'success');
				setTimeout(function() { location.reload(); }, 500);
			} else {
				alert_toast(resp.message || "Error al eliminar el curso.", 'danger');
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
