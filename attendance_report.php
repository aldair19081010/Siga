<?php
include 'db_connect.php';

// Obtener reglas actuales (puedes guardar esto en una tabla, aquí es solo ejemplo)
$default_early = "07:30";
$default_late = "08:10";
$early_time = $_POST['early_time'] ?? $default_early;
$late_time = $_POST['late_time'] ?? $default_late;

// Filtros
$student_id = $_POST['student_id'] ?? '';
$date_from = $_POST['date_from'] ?? date('Y-m-01');
$date_to = $_POST['date_to'] ?? date('Y-m-d');

// Consulta de estudiantes (con grado)
$students = $conn->query("SELECT id, name, id_no, grado FROM student ORDER BY grado ASC, name ASC");
?>
<div class="container-fluid">
    <div class="card col-lg-12 mt-4">
        <div class="card-header"><b>Reporte de Asistencia</b></div>
        <div class="card-body">
            <form id="attendance-filter" class="form-row align-items-end mb-3">
                <div class="form-group col-md-2">
                    <label>Desde:</label>
                    <input type="date" name="date_from" value="<?php echo $date_from ?>" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Hasta:</label>
                    <input type="date" name="date_to" value="<?php echo $date_to ?>" class="form-control" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Alumno:</label>
                    <select name="student_id" class="form-control select2" style="min-width:180px;">
                        <option value="">Todos</option>
                        <?php while($stu = $students->fetch_assoc()): ?>
                            <option value="<?php echo $stu['id'] ?>" <?php echo $student_id == $stu['id'] ? 'selected' : '' ?>>
                                <?php echo ucwords($stu['name']) . " ({$stu['id_no']}) - " . $stu['grado'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Hora Temprano:</label>
                    <input type="time" name="early_time" value="<?php echo $early_time ?>" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Hora Tarde:</label>
                    <input type="time" name="late_time" value="<?php echo $late_time ?>" class="form-control" required>
                </div>
                <div class="form-group col-md-1">
                    <button type="submit" class="btn btn-primary btn-block">Buscar</button>
                </div>
            </form>
            <div id="attendance-report-table"></div>
        </div>
    </div>
</div>
<script>
$('.select2').select2({ width: '100%' });

$('#attendance-filter').submit(function(e) {
    e.preventDefault();
    start_load();
    $.ajax({
        url: 'attendance_report_table.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(resp) {
            $('#attendance-report-table').html(resp);
            end_load();
        },
        error: function() {
            alert_toast("Error al cargar el reporte.", 'danger');
            end_load();
        }
    });
});
</script>
