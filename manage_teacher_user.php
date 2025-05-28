<?php
include 'db_connect.php';
session_start();

// Obtener el school_id del administrador
$school_id = $_SESSION['login_school_id'] ?? 0;

// Obtener información del docente
$teacher_id = $_GET['id'] ?? '';
$teacher_name = $_GET['name'] ?? '';

// Verificar si ya existe un usuario para este docente
$check = $conn->query("SELECT * FROM users WHERE teacher_id = '$teacher_id'");
$user = $check->num_rows > 0 ? $check->fetch_assoc() : null;
?>

<div class="container-fluid">
    <form id="manage-teacher-user">
        <input type="hidden" name="id" value="<?php echo isset($user['id']) ? $user['id'] : '' ?>">
        <input type="hidden" name="teacher_id" value="<?php echo $teacher_id ?>">
        <input type="hidden" name="school_id" value="<?php echo $school_id ?>">
        <input type="hidden" name="name" value="<?php echo $teacher_name ?>">
        
        <div class="form-group">
            <label for="teacher_name">Nombre del Docente</label>
            <input type="text" class="form-control" id="teacher_name" value="<?php echo $teacher_name ?>" readonly>
        </div>
        
        <div class="form-group">
            <label for="username">Nombre de Usuario</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($user['username']) ? $user['username'] : '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña<?php echo isset($user['id']) ? ' (dejar en blanco para mantener actual)' : '' ?></label>
            <input type="password" class="form-control" id="password" name="password" <?php echo !isset($user['id']) ? 'required' : '' ?>>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#manage-teacher-user').submit(function(e) {
        e.preventDefault();
        start_load();
        
        $.ajax({
            url: 'ajax.php?action=save_teacher_user',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp == 1) {
                    alert_toast('Usuario creado/actualizado exitosamente', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else if (resp == 2) {
                    $('#msg').html('<div class="alert alert-danger">El nombre de usuario ya existe</div>');
                    end_load();
                } else {
                    $('#msg').html('<div class="alert alert-danger">Error al guardar los datos</div>');
                    end_load();
                }
            }
        });
    });
});
</script>
