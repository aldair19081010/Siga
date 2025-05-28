<?php
include 'db_connect.php';
session_start();

// Obtener el school_id del administrador
$school_id = $_SESSION['login_school_id'] ?? 0;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$teacher_id = $course_id = $grado = $seccion = '';

if ($id) {
    $q = $conn->query("SELECT * FROM teacher_courses WHERE id = $id");
    if ($q && $q->num_rows) {
        $row = $q->fetch_assoc();
        $teacher_id = $row['teacher_id'];
        $course_id = $row['course_id'];
        $grado = $row['grado'];
        $seccion = $row['seccion'] ?? 'U';
    }
}
?>

<div class="container-fluid">
    <form id="manage-teacher-course">
        <input type="hidden" name="id" value="<?php echo $id ?>">
        <input type="hidden" name="school_id" value="<?php echo $school_id ?>">
        <div id="msg" class="form-group"></div>
        
        <div class="form-group">
            <label for="teacher_id">Docente</label>
            <select name="teacher_id" id="teacher_id" class="form-control select2" required>
                <option value="">Seleccione un docente</option>
                <?php
                // Filtrar los docentes por el colegio del administrador
                $teachers = $conn->query("SELECT id, name FROM teacher WHERE school_id = $school_id ORDER BY name ASC");
                while ($row = $teachers->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo ($teacher_id == $row['id']) ? 'selected' : '' ?>><?php echo ucwords($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="course_id">Curso</label>
            <select name="course_id" id="course_id" class="form-control select2" required>
                <option value="">Seleccione un curso</option>
                <?php
                // Filtrar los cursos por el colegio del administrador
                $courses = $conn->query("SELECT id, name, level FROM academic_courses WHERE school_id = $school_id ORDER BY name ASC");
                while ($row = $courses->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" data-nivel="<?php echo htmlspecialchars($row['level']) ?>" <?php echo ($course_id == $row['id']) ? 'selected' : '' ?>><?php echo ucwords($row['name']) ?> (<?php echo $row['level'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="nivel">Nivel</label>
            <input type="text" name="nivel" id="nivel" class="form-control" value="<?php echo isset($nivel) ? htmlspecialchars($nivel) : '' ?>" readonly>
        </div>
        
        <div class="form-group">
            <label for="grado">Grado</label>
            <select name="grado" id="grado" class="form-control select2" required>
                <option value="">Seleccione un grado</option>
                <?php
                $all_grades = ['1°','2°','3°','4°','5°','6°'];
                foreach ($all_grades as $g):
                ?>
                <option value="<?php echo $g; ?>" <?php echo ($grado == $g) ? 'selected' : '' ?>><?php echo $g; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="seccion">Sección</label>
            <select name="seccion" id="seccion" class="form-control select2" required>
                <option value="">Seleccione una sección</option>
                <option value="U" <?php echo ($seccion == 'U') ? 'selected' : '' ?>>U (Única)</option>
                <option value="A" <?php echo ($seccion == 'A') ? 'selected' : '' ?>>A</option>
                <option value="B" <?php echo ($seccion == 'B') ? 'selected' : '' ?>>B</option>
                <option value="C" <?php echo ($seccion == 'C') ? 'selected' : '' ?>>C</option>
                <option value="D" <?php echo ($seccion == 'D') ? 'selected' : '' ?>>D</option>
                <option value="E" <?php echo ($seccion == 'E') ? 'selected' : '' ?>>E</option>
                <option value="F" <?php echo ($seccion == 'F') ? 'selected' : '' ?>>F</option>
            </select>
        </div>
    </form>
</div>

<script>
    $(document).ready(function(){
        $('.select2').select2({
            placeholder: "Seleccione aquí",
            width: "100%"
        });
        // Actualizar el campo nivel automáticamente al seleccionar un curso
        $('#course_id').on('change', function() {
            var nivel = $('#course_id option:selected').data('nivel') || '';
            $('#nivel').val(nivel);
        });
        // Si ya hay curso seleccionado al cargar, mostrar el nivel
        var nivelInit = $('#course_id option:selected').data('nivel') || '';
        $('#nivel').val(nivelInit);
    });
    
    $('#manage-teacher-course').submit(function(e){
        e.preventDefault();
        start_load();
        $('#msg').html('');
        
        $.ajax({
            url: 'ajax.php?action=assign_teacher_course',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp){
                if(resp.status == 1){
                    alert_toast("Asignación guardada exitosamente", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else if(resp.status == 2){
                    $('#msg').html('<div class="alert alert-danger">El docente ya está asignado a este curso y grado con esta sección.</div>');
                    end_load();
                } else {
                    $('#msg').html('<div class="alert alert-danger">' + (resp.message || "Ocurrió un error") + '</div>');
                    end_load();
                }
            },
            error: function(xhr, status, error){
                console.error("Error en la solicitud AJAX:", error);
                console.log("Respuesta del servidor:", xhr.responseText);
                $('#msg').html('<div class="alert alert-danger">Ocurrió un error en el servidor. Verifique la consola para más detalles.</div>');
                end_load();
            }
        });
    });
</script>
