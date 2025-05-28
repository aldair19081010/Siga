<?php
include 'db_connect.php';
$teacher_id = $_SESSION['login_teacher_id'] ?? 0;
?>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card courses-card">
            <div class="card-header bg-gradient-info text-white">
                <div class="d-flex align-items-center">
                    <i class="fa fa-book mr-2" style="font-size: 1.2rem;"></i>
                    <b>Mis Cursos Asignados</b>
                </div>
            </div>            <div class="card-body">
                <div class="courses-info mb-4">                <div class="d-flex align-items-center mb-2">
                        <i class="fa fa-info-circle mr-2" style="color: #4285f4;"></i>
                        <h6 class="mb-0 font-weight-bold">Información de cursos</h6>
                    </div>
                    <p class="text-muted mb-0">Aquí puede ver todos los cursos que tiene asignados actualmente. Utilice estos datos para gestionar evaluaciones y calificaciones.</p>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover courses-table" id="my_courses_table">
                        <thead class="bg-light">
                            <tr>
                                <th width="50px">#</th>
                                <th>Curso Académico</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Nivel</th>
                                <th>Colegio</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        // Usar el valor numérico, no string, en la consulta SQL
                        $sql = "SELECT 
                                tc.id,
                                ac.name as course_name,
                                ac.level,
                                tc.grado,
                                tc.seccion,
                                sc.name as school_name
                            FROM teacher_courses tc
                            INNER JOIN academic_courses ac ON ac.id = tc.course_id
                            LEFT JOIN schools sc ON ac.school_id = sc.id
                            WHERE tc.teacher_id = $teacher_id
                            ORDER BY sc.name, ac.name, tc.grado, tc.seccion";
                        $q = $conn->query($sql);
                        if ($q && $q->num_rows > 0):
                            while ($row = $q->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $i++ ?></td>
                            <td><?php echo htmlspecialchars($row['course_name']) ?></td>
                            <td><?php echo htmlspecialchars($row['grado']) ?></td>
                            <td><?php echo htmlspecialchars($row['seccion'] ?? 'U') ?></td>
                            <td><?php echo htmlspecialchars($row['level']) ?></td>
                            <td><?php echo htmlspecialchars($row['school_name']) ?></td>
                        </tr>                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fa fa-graduation-cap text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <p class="text-muted">No tienes cursos asignados actualmente.</p>
                                    <small class="d-block text-muted">Póngase en contacto con administración para asignación de cursos.</small>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
                
                <div class="card-footer bg-light mt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small><i class="fa fa-info-circle mr-1"></i> Para ver las evaluaciones de un curso, acceda desde el menú de Evaluaciones.</small>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .courses-card { 
        border-radius: 10px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: none;
        overflow: hidden;
        margin-top: 20px;
    }    .bg-gradient-info {
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
    }    .courses-info {
        background-color: #f8f9fc;
        border-left: 4px solid #4285f4;
        padding: 15px;
        border-radius: 4px;
    }
    .courses-table {
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }    .courses-table thead th {
        border-bottom: 2px solid #e3e6f0;
        font-weight: 600;
        color: #4285f4;
    }
    .courses-table tbody tr:hover {
        background-color: rgba(66, 133, 244, 0.05);
    }
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }    .btn-outline-info {
        border-color: #4285f4;
        color: #4285f4;
        transition: all 0.3s;
    }
    .btn-outline-info:hover {
        background: linear-gradient(135deg, #4285f4, #2a75f3);
        border-color: #2a75f3;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(66, 133, 244, 0.3);
    }
      /* Mejoras para DataTables */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        padding: 6px 10px;
        border: 1px solid #dce4ec;
    }
    .dataTables_wrapper .dataTables_filter input {
        min-width: 250px;
        padding-left: 30px;
    }    .dataTables_wrapper .dataTables_length select:focus,
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #4285f4;
        box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
    }
    /* Estilos específicos para el campo de búsqueda */
    .dataTables_filter {
        margin-bottom: 15px;
    }
    .dataTables_filter label {
        font-weight: normal;
        white-space: nowrap;
    }
    .search-label {
        color: #555;
        font-weight: normal;
    }    .search-icon {
        color: #4285f4 !important;
        font-size: 14px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        transition: all 0.3s;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, #4285f4, #2a75f3);
        border: 1px solid #2a75f3;
        color: white !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(66, 133, 244, 0.1);
        border: 1px solid rgba(66, 133, 244, 0.2);
    }
    
    /* Mejoras para responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        .courses-info {
            padding: 10px;
        }
        .card-footer {
            flex-direction: column;
        }
        .card-footer a {
            margin-top: 10px;
        }
    }
    /* Unificación de colores con el resto del sistema */
    .text-info {
        color: #4285f4 !important;
    }
    .alert-info {
        background-color: rgba(66, 133, 244, 0.1);
        border-color: rgba(66, 133, 244, 0.2);
        color: #4285f4;
    }
    
    /* Mejoras adicionales con el color #4285f4 */
    .courses-card {
        border-top: 4px solid #4285f4;
    }
    
    .dataTables_wrapper .dataTables_filter input:hover {
        border-color: rgba(66, 133, 244, 0.5);
    }
    
    /* Efecto de animación para la tarjeta */
    .courses-card {
        transition: all 0.3s ease;
    }
    
    .courses-card:hover {
        box-shadow: 0 6px 18px rgba(66, 133, 244, 0.15);
    }
</style>
<script>
$(document).ready(function() {
    $('#my_courses_table').DataTable({
        "language": {
            "emptyTable": "No tienes cursos asignados actualmente.",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ cursos",
            "infoEmpty": "Mostrando 0 a 0 de 0 cursos",
            "infoFiltered": "(filtrado de _MAX_ cursos en total)",
            "lengthMenu": "Mostrar _MENU_ cursos",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "No se encontraron cursos coincidentes",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
        "order": [[1, "asc"]], // Ordenar por curso académico (segunda columna)
        "responsive": true,
        "drawCallback": function() {
            // Añadir clases personalizadas después de que DataTables cree su estructura
            $('.dataTables_wrapper .dataTables_filter').addClass('custom-filter');
            $('.dataTables_wrapper .dataTables_length').addClass('custom-length');
              // Añadir iconos a los campos de DataTables
            if ($('.search-icon').length === 0) {
                // Modificar el HTML del filtro para reacomodar los elementos
                var filterText = $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; // Nodo de texto
                }).first();
                
                // Reemplazar el texto "Buscar:" con un span para poder posicionarlo mejor
                if (filterText.length) {
                    filterText.replaceWith('<span class="search-label">Buscar:</span>');
                }
                
                // Añadir el icono después del label y antes del input
                $('.dataTables_filter input');
                // Aplicar estilos para evitar la sobreposición
                $('.dataTables_filter').css('position', 'relative');
                $('.dataTables_filter').css('display', 'flex');
                $('.dataTables_filter label').css({
                    'display': 'flex',
                    'align-items': 'center',
                    'position': 'relative',
                    'width': '100%'
                });
                $('.dataTables_filter .search-label').css({
                    'margin-right': '8px'
                });
                $('.search-icon').css({
                    'position': 'absolute',
                    'left': '92px', // Posicionado después de "Buscar:"
                    'top': '10px',
                    'z-index': '1'
                });
                $('.dataTables_filter input').css({
                    'padding-left': '25px',
                    'margin-left': '0'
                });
            }
        }
    });
    
    // Mostrar un mensaje tras cargar la tabla
    if ($('#my_courses_table tbody tr').length > 1) {
        var courseCount = $('#my_courses_table tbody tr').length;
        var message = 'Se encontraron ' + courseCount + ' cursos asignados.';
          $('<div class="alert alert-info mt-3 animate__animated animate__fadeIn" style="background-color: rgba(66, 133, 244, 0.1); border-color: rgba(66, 133, 244, 0.2); color: #4285f4;">' +
          '<i class="fa fa-info-circle mr-2"></i>' + message +
          '</div>').insertBefore('#my_courses_table_wrapper');
        
        setTimeout(function() {
            $('.alert-info').fadeOut(500);
        }, 5000);
    }
});
</script>
