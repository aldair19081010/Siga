<?php
include 'db_connect.php';
session_start();

// Obtener el school_id del administrador automáticamente
$school_id = $_SESSION['login_school_id'] ?? 0;

if (isset($_GET['id'])) {
    // Asegurarse de que solo se puedan editar docentes del mismo colegio
    $qry = $conn->query("SELECT * FROM teacher WHERE id = " . $_GET['id'] . " AND school_id = " . $school_id);
    foreach ($qry->fetch_array() as $k => $val) {
        $$k = $val;
    }
}
?>
<div class="container-fluid">
    <form action="" id="manage-teacher">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <!-- Establecer el school_id como valor oculto que no se puede modificar -->
        <input type="hidden" name="school_id" value="<?php echo $school_id ?>">
        <div id="msg" class="form-group"></div>
        <div class="form-group">
            <label for="" class="control-label">Dni</label>
            <input type="text" class="form-control" name="id_no" value="<?php echo isset($id_no) ? $id_no : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Nombre</label>
            <input type="text" class="form-control" name="name" value="<?php echo isset($name) ? $name : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Especialidad</label>
            <input type="text" class="form-control" name="specialty" value="<?php echo isset($specialty) ? $specialty : '' ?>" required>
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
    $('#manage-teacher').on('reset', function() {
        $('#msg').html('');
        $('input:hidden').val('');
    });

    $('#manage-teacher').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        $.ajax({
            url: 'ajax.php?action=save_teacher',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            dataType: 'json',
            success: function(resp) {
                if (resp.status == 1) {
                    alert_toast(resp.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else if (resp.status == 2) {
                    $('#msg').html('<div class="alert alert-danger mx-2">' + resp.message + '</div>');
                    end_load();
                } else {
                    alert_toast(resp.message, 'danger');
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
