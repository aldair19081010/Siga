<?php
include 'db_connect.php';

$date_from = $_POST['date_from'] ?? date('Y-m-01');
$date_to = $_POST['date_to'] ?? date('Y-m-d');
$student_id = $_POST['student_id'] ?? '';
$early_time = $_POST['early_time'] ?? '07:30';
$late_time = $_POST['late_time'] ?? '08:10';

$where = "a.fecha BETWEEN '$date_from' AND '$date_to'";
if ($student_id) {
    $where .= " AND a.student_id = " . intval($student_id);
}

// Mostrar todos los alumnos si no se selecciona uno específico
$q = $conn->query("SELECT a.*, s.name, s.id_no, s.grado FROM asistencia a INNER JOIN student s ON s.id = a.student_id WHERE $where ORDER BY a.fecha, s.grado, s.name, a.hora ASC");

if (!$q || $q->num_rows == 0) {
    echo "<div class='alert alert-info'>No hay registros de asistencia para los filtros seleccionados.</div>";
    exit;
}
?>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Alumno</th>
            <th>DNI</th>
            <th>Grado</th>
            <th>Tipo</th>
            <th>Hora</th>
            <th>Estado</th>
            <th>Regla</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $q->fetch_assoc()): 
            $hora = $row['hora'];
            $regla = '';
            if ($row['tipo'] == 'Entrada') {
                if ($hora <= $early_time) $regla = '<span class="badge badge-success">Temprano</span>';
                elseif ($hora > $late_time) $regla = '<span class="badge badge-danger">Tarde</span>';
                else $regla = '<span class="badge badge-warning">Normal</span>';
            } else {
                $regla = '-';
            }
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['fecha']) ?></td>
            <td><?php echo ucwords($row['name']) ?></td>
            <td><?php echo htmlspecialchars($row['id_no']) ?></td>
            <td><?php echo htmlspecialchars($row['grado']) ?></td>
            <td><?php echo htmlspecialchars($row['tipo']) ?></td>
            <td><?php echo htmlspecialchars($hora) ?></td>
            <td><?php echo htmlspecialchars($row['estado']) ?></td>
            <td><?php echo $regla ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<script>
$('table').dataTable();
</script>
