<?php
include 'db_connect.php';
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM student where id= " . $_GET['id']);
    foreach ($qry->fetch_array() as $k => $val) {
        $$k = $val;
    }
}
?>
<div class="container-fluid">
    <form action="" id="manage-student">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div id="msg" class="form-group"></div>
        <div class="form-group">
            <label for="" class="control-label">Dni</label>
            <input type="text" class="form-control" name="id_no" value="<?php echo isset($id_no) ? $id_no : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Nombre</label>
            <input type="text" class="form-control" name="name" value="<?php echo isset($name) ? $name : '' ?>" required>
        </div>
        <!-- Campo de Nivel -->
        <div class="form-group">
            <label for="nivel" class="control-label">Nivel</label>
            <select class="form-control" name="nivel" id="nivel" required>
                <option value="" disabled selected>Seleccione un nivel</option>
                <option value="Inicial" <?php echo (isset($nivel) && $nivel == 'Inicial') ? 'selected' : '' ?>>Inicial</option>
                <option value="Primaria" <?php echo (isset($nivel) && $nivel == 'Primaria') ? 'selected' : '' ?>>Primaria</option>
                <option value="Secundaria" <?php echo (isset($nivel) && $nivel == 'Secundaria') ? 'selected' : '' ?>>Secundaria</option>
            </select>
        </div>

        <!-- Campo de Grado -->
        <div class="form-group">
            <label for="grado" class="control-label">Grado</label>
            <select class="form-control" name="grado" id="grado" required>
                <option value="" disabled selected>Seleccione un grado</option>
                <option value="1°" <?php echo (isset($grado) && $grado == '1°') ? 'selected' : '' ?>>1°</option>
                <option value="2°" <?php echo (isset($grado) && $grado == '2°') ? 'selected' : '' ?>>2°</option>
                <option value="3°" <?php echo (isset($grado) && $grado == '3°') ? 'selected' : '' ?>>3°</option>
                <option value="4°" <?php echo (isset($grado) && $grado == '4°') ? 'selected' : '' ?>>4°</option>
                <option value="5°" <?php echo (isset($grado) && $grado == '5°') ? 'selected' : '' ?>>5°</option>
                <option value="6°" <?php echo (isset($grado) && $grado == '6°') ? 'selected' : '' ?>>6°</option>
            </select>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Contacto</label>
            <input type="text" class="form-control" name="contact" value="<?php echo isset($contact) ? $contact : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Correo</label>
            <input type="email" class="form-control" name="email" value="<?php echo isset($email) ? $email : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Dirección</label>
            <textarea name="address" id="" cols="30" rows="3" class="form-control" required=""><?php echo isset($address) ? $address : '' ?></textarea>
        </div>
    </form>
</div>

<script>
    $('#manage-student').on('reset', function() {
        $('#msg').html('');
        $('input:hidden').val('');
    });

    $('#manage-student').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        $.ajax({
            url: 'ajax.php?action=save_student',
            data: new FormData($(this)[0]),
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

    $('.select2').select2({
        placeholder: "Por favor selecciona aquí",
        width: '100%'
    });
</script>