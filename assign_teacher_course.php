<?php
include 'db_connect.php';
session_start();

// Obtener el school_id del administrador
$school_id = $_SESSION['login_school_id'] ?? 0;

// Cargar docente y curso si se especifica un ID
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM teacher_courses WHERE id = " . $_GET['id']);
    foreach ($qry->fetch_array() as $k => $v) {
        $$k = $v;
    }
}
?>
<style>
.main-content-area.assign-teacher-center {
    min-height: calc(100vh - 120px); /* Ajuste para topbar y footer */
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafe;
    padding: 32px 0 32px 0;
}
.assign-teacher-form-box {
    width: 100%;
    max-width: 440px;
    background: #fff;
    padding: 32px 28px 18px 28px;
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.10);
    margin-bottom: 32px;
}
@media (max-width: 600px) {
    .assign-teacher-form-box {
        padding: 18px 6px 12px 6px;
        max-width: 98vw;
    }
}
</style>
<div class="main-content-area assign-teacher-center">
    <form action="" id="assign-teacher-course" class="assign-teacher-form-box">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <input type="hidden" name="school_id" value="<?php echo $school_id ?>">
        <div class="form-group">
            <label for="teacher_id" class="control-label">Docente</label>
            <select class="form-control" name="teacher_id" required>
                <option value="">Seleccionar Docente</option>
                <?php
                $teachers = $conn->query("SELECT * FROM teacher WHERE school_id = $school_id ORDER BY name ASC");
                while ($row = $teachers->fetch_assoc()):
                ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($teacher_id) && $teacher_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="course_id" class="control-label">Curso</label>
            <select class="form-control" name="course_id" required>
                <option value="">Seleccionar Curso</option>
                <?php
                $courses = $conn->query("SELECT * FROM academic_courses WHERE school_id = $school_id ORDER BY name ASC");
                while ($row = $courses->fetch_assoc()):
                ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($course_id) && $course_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="grado" class="control-label">Grado</label>
            <input type="text" class="form-control" name="grado" value="<?php echo isset($grado) ? $grado : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="seccion" class="control-label">Sección</label>
            <select class="form-control" name="seccion" required>
                <option value="">Seleccione una sección</option>
                <option value="U" <?php echo (isset($seccion) && $seccion == 'U') ? 'selected' : '' ?>>U (Única)</option>
                <option value="A" <?php echo (isset($seccion) && $seccion == 'A') ? 'selected' : '' ?>>A</option>
                <option value="B" <?php echo (isset($seccion) && $seccion == 'B') ? 'selected' : '' ?>>B</option>
                <option value="C" <?php echo (isset($seccion) && $seccion == 'C') ? 'selected' : '' ?>>C</option>
                <option value="D" <?php echo (isset($seccion) && $seccion == 'D') ? 'selected' : '' ?>>D</option>
                <option value="E" <?php echo (isset($seccion) && $seccion == 'E') ? 'selected' : '' ?>>E</option>
                <option value="F" <?php echo (isset($seccion) && $seccion == 'F') ? 'selected' : '' ?>>F</option>
            </select>
        </div>
        <div class="form-group">
            <label for="level" class="control-label">Nivel</label>
            <select class="form-control" name="level" required>
                <option value="">Seleccionar Nivel</option>
                <option value="Inicial" <?php echo (isset($level) && $level == 'Inicial') ? 'selected' : '' ?>>Inicial</option>
                <option value="Primaria" <?php echo (isset($level) && $level == 'Primaria') ? 'selected' : '' ?>>Primaria</option>
                <option value="Secundaria" <?php echo (isset($level) && $level == 'Secundaria') ? 'selected' : '' ?>>Secundaria</option>
            </select>
        </div>
    </form>
</div>

<script>
    $('#assign-teacher-course').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        $.ajax({
            url: 'ajax.php?action=assign_teacher_course',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Asignación guardada exitosamente", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else if (resp == 2) {
                    $('#msg').html('<div class="alert alert-danger">El docente ya está asignado a este curso y grado.</div>');
                    end_load();
                } else {
                    alert_toast("Ocurrió un error", 'danger');
                    end_load();
                }
            }
        });
    });
</script>
