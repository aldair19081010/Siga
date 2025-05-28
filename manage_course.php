<?php
include 'db_connect.php';
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM courses WHERE id = " . $_GET['id']);
    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_array() as $k => $val) {
            $$k = $val;
        }
    } else {
        echo "<script>alert('Curso no encontrado.'); location.replace('courses.php');</script>";
        exit;
    }
}
// Para edición, convertir grados a array
$grados = isset($grades) ? explode(',', $grades) : [];
?>
<div class="container-fluid">
    <form action="" id="manage-course">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <h5><b>Conceptos de pago</b></h5>
                <hr>
                <div id="msg" class="form-group"></div>
                <div class="form-group">
                    <label for="" class="control-label">Concepto</label>
                    <input type="text" class="form-control" name="course" value="<?php echo isset($course) ? $course : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="level" class="control-label">Nivel</label>
                    <select class="form-control" name="level" id="level" required>
                        <option value="" disabled>Seleccione un nivel</option>
                        <option value="Inicial" <?php echo (isset($level) && $level == 'Inicial') ? 'selected' : '' ?>>Inicial</option>
                        <option value="Primaria" <?php echo (isset($level) && $level == 'Primaria') ? 'selected' : '' ?>>Primaria</option>
                        <option value="Secundaria" <?php echo (isset($level) && $level == 'Secundaria') ? 'selected' : '' ?>>Secundaria</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="grades" class="control-label">Grados que aplican</label>
                    <select class="form-control select2" name="grades[]" id="grades" multiple required>
                        <?php
                        $all_grades = ['1°','2°','3°','4°','5°','6°'];
                        foreach ($all_grades as $g):
                        ?>
                        <option value="<?php echo $g; ?>" <?php echo (in_array($g, $grados)) ? 'selected' : '' ?>><?php echo $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Puede seleccionar uno o varios grados.</small>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Descripción</label>
                    <textarea name="description" id="" cols="30" rows="4" class="form-control" required><?php echo isset($description) ? $description : '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Monto</label>
                    <input type="number" step="any" min="0" class="form-control text-right" name="total_amount" value="<?php echo isset($total_amount) ? $total_amount : '' ?>" required>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    $('.select2').select2({ width: '100%' });

    $('#manage-course').on('reset', function() {
        $('#msg').html('');
        $('input:hidden').val('');
    });

    $('#manage-course').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        // Serializar los grados seleccionados como string separado por coma
        var formData = new FormData(this);
        var grades = $('#grades').val();
        if (grades && grades.length > 0) {
            formData.set('grades', grades.join(','));
        }
        $.ajax({
            url: 'ajax.php?action=save_course',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp) {
                try {
                    if (typeof resp === 'string') {
                        resp = JSON.parse(resp); // Asegurarse de que la respuesta sea JSON válida
                    }
                    if (resp.status == 1) {
                        alert_toast(resp.message, 'success'); // Mostrar mensaje de éxito
                        setTimeout(function() {
                            location.reload(); // Recargar la página después de guardar
                        }, 500);
                    } else if (resp.status == 2) {
                        $('#msg').html('<div class="alert alert-danger mx-2">' + resp.message + '</div>');
                        end_load();
                    } else {
                        alert_toast(resp.message, 'danger');
                        end_load();
                    }
                } catch (err) {
                    console.error("Error al procesar la respuesta del servidor:", err);
                    alert_toast("Error inesperado. Intente nuevamente más tarde.", 'danger');
                    end_load();
                }
            },
            error: function(err) {
                console.error("Error en la solicitud AJAX:", err);
                alert_toast("Error en el servidor. Intente nuevamente más tarde.", 'danger');
                end_load();
            }
        });
    });
</script>