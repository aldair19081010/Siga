<?php
include 'db_connect.php';
session_start();
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM users WHERE id = {$_GET['id']}");
    foreach ($qry->fetch_array() as $k => $v) {
        $$k = $v;
    }
}
?>
<div class="container-fluid">
    <div id="msg"></div>

    <form id="manage-user" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="form-group">
            <label for="" class="control-label">Nombre</label>
            <input type="text" class="form-control" name="name" value="<?php echo isset($name) ? $name : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Usuario</label>
            <input type="text" class="form-control" name="username" value="<?php echo isset($username) ? $username : '' ?>" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="" class="control-label">Contraseña</label>
            <input type="password" class="form-control" name="password" placeholder="(Dejar en blanco para no cambiar)" autocomplete="off">
            <?php if (isset($password)) : ?>
                <small><i>Dejar en blanco si no desea cambiar la contraseña.</i></small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Foto de Perfil</label>
            <input type="file" class="form-control" name="avatar" accept="image/*">
            <?php if (isset($avatar) && !empty($avatar)) : ?>
                <img src="assets/uploads/<?php echo $avatar ?>" alt="Avatar" class="img-thumbnail mt-2" width="100">
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    $('#manage-user').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html(''); // Limpiar mensajes previos
        $.ajax({
            url: 'ajax.php?action=save_user',
            method: 'POST',
            data: new FormData($(this)[0]),
            contentType: false,
            processData: false,
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Perfil actualizado con éxito.", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else if (resp == 2) {
                    $('#msg').html('<div class="alert alert-danger">El nombre de usuario ya existe.</div>');
                    end_load();
                } else {
                    $('#msg').html('<div class="alert alert-danger">Ocurrió un error al guardar los datos.</div>');
                    end_load();
                }
            },
            error: function(err) {
                console.log(err);
                $('#msg').html('<div class="alert alert-danger">Error en la solicitud. Verifique la consola para más detalles.</div>');
                end_load();
            }
        });
    });
</script>