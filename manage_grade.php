<?php
include 'db_connect.php';
$is_teacher = (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 2);
$teacher_id = $_SESSION['login_teacher_id'] ?? null;
$id = isset($_GET['id']) ? $_GET['id'] : '';
if ($id) {
    $qry = $conn->query("SELECT * FROM grades WHERE id = $id");
    if ($qry->num_rows) {
        foreach ($qry->fetch_assoc() as $k => $v) {
            $$k = $v;
        }
    }
}
?>
<div class="container-fluid">
    <form id="manage-grade">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div id="msg" class="form-group"></div>
        <div class="form-group">
            <label>Estudiante</label>
            <select name="student_id" class="form-control select2" required>
                <option value="">Seleccione un estudiante</option>
                <?php
                $students = $conn->query("SELECT id, name, id_no FROM student ORDER BY name ASC");
                while ($row = $students->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo (isset($student_id) && $student_id == $row['id']) ? 'selected' : '' ?>>
                    <?php echo ucwords($row['name']) . " ({$row['id_no']})" ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Docente</label>
            <select name="teacher_id" class="form-control select2" required <?php echo $is_teacher ? 'readonly disabled' : '' ?>>
                <option value="">Seleccione un docente</option>
                <?php
                $teachers = $conn->query("SELECT id, name, specialty FROM teacher ORDER BY name ASC");
                while ($row = $teachers->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>"
                    <?php
                    if ($is_teacher && $teacher_id == $row['id']) echo 'selected';
                    else if (isset($teacher_id) && $teacher_id == $row['id']) echo 'selected';
                    ?>
                ><?php echo ucwords($row['name']) . " ({$row['specialty']})" ?></option>
                <?php endwhile; ?>
            </select>
            <?php if ($is_teacher): ?>
                <input type="hidden" name="teacher_id" value="<?php echo $teacher_id ?>">
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Curso</label>
            <select name="course_id" class="form-control select2" required>
                <option value="">Seleccione un curso</option>
                <?php
                $courses = $conn->query("SELECT id, course, level FROM courses ORDER BY course ASC");
                while ($row = $courses->fetch_assoc()):
                ?>
                <option value="<?php echo $row['id'] ?>" <?php echo (isset($course_id) && $course_id == $row['id']) ? 'selected' : '' ?>>
                    <?php echo $row['course'] . " (" . $row['level'] . ")" ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nota</label>
            <input type="number" step="any" min="0" max="20" class="form-control" name="grade" value="<?php echo isset($grade) ? $grade : '' ?>" required>
        </div>
        <div class="form-group">
            <label>Observación</label>
            <textarea name="remark" class="form-control" rows="2"><?php echo isset($remark) ? $remark : '' ?></textarea>
        </div>
    </form>
</div>
<script>
    $('.select2').select2({ width: '100%' });

    $('#manage-grade').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        $.ajax({
            url: 'ajax.php?action=save_grade',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp) {
                if (resp.status == 1) {
                    alert_toast(resp.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    $('#msg').html('<div class="alert alert-danger">' + resp.message + '</div>');
                    end_load();
                }
            },
            error: function(err) {
                alert_toast("Error en el servidor. Intente nuevamente más tarde.", 'danger');
                end_load();
            }
        });
    });
</script>
