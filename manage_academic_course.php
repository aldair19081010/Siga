<?php
include 'db_connect.php';
session_start();

// Obtener el school_id del administrador
$school_id = $_SESSION['login_school_id'] ?? 0;

if(isset($_GET['id'])){
    // Verificar que el curso pertenezca al colegio del administrador
    $qry = $conn->query("SELECT * FROM academic_courses WHERE id = ".$_GET['id']." AND school_id = $school_id");
    foreach($qry->fetch_array() as $k => $val){
        $$k = $val;
    }
}
?>
<div class="container-fluid">
    <form id="manage-academic-course">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <input type="hidden" name="school_id" value="<?php echo $school_id ?>">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" value="<?php echo isset($name) ? $name : '' ?>" required>
        </div>
        <div class="form-group">
            <label>Nivel</label>
            <select name="level" class="form-control" required>
                <option value="">Seleccione nivel</option>
                <option value="Inicial" <?php echo (isset($level) && $level == 'Inicial') ? 'selected' : '' ?>>Inicial</option>
                <option value="Primaria" <?php echo (isset($level) && $level == 'Primaria') ? 'selected' : '' ?>>Primaria</option>
                <option value="Secundaria" <?php echo (isset($level) && $level == 'Secundaria') ? 'selected' : '' ?>>Secundaria</option>
            </select>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" class="form-control"><?php echo isset($description) ? $description : '' ?></textarea>
        </div>
    </form>
</div>

<script>
    $('#manage-academic-course').submit(function(e){
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_academic_course',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp){
                if(resp.status == 1){
                    alert_toast("Curso guardado exitosamente", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast(resp.message || "Ocurrió un error", 'danger');
                    end_load();
                }
            },
            error: function(xhr, status, error){
                console.error("Error en la solicitud AJAX:", error);
                alert_toast("Error en el servidor. Intente nuevamente.", 'danger');
                end_load();
            }
        });
    });
</script>
