<?php
include 'db_connect.php';
session_start();
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM users WHERE id = {$_GET['id']}");
    foreach ($qry->fetch_array() as $k => $v) {
        $$k = $v;
    }
    // Si hay avatar, actualizar la sesión para que se muestre en el topbar tras recargar
    if (isset($avatar) && !empty($avatar)) {
        $_SESSION['login_avatar'] = $avatar;
    }
}
?>
<div class="container-fluid" id="manage-user-container">
    <h4 class="page-header">
        <?php echo isset($id) ? "Editar Usuario" : "Nuevo Usuario"; ?>
    </h4>
    <style>
        /* Reset para evitar colisiones con estilos globales */
        #manage-user-container * {
            box-sizing: border-box;
        }
        
        /* Estilos personalizados con el color principal #4285f4 */
        #manage-user-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(66, 133, 244, 0.1);
            padding: 25px;
            max-width: 800px;
            margin: 20px auto;
            position: relative;
            z-index: 1;
            clear: both;
        }
        
        /* Estilos para las etiquetas */
        .control-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 8px;
            transition: all 0.3s;
            position: relative;
            display: inline-block;
        }
        
        .control-label:after {
            content: '';
            display: block;
            width: 0;
            height: 2px;
            background: #4285f4;
            transition: width 0.3s;
            position: absolute;
            bottom: -3px;
            left: 0;
        }
        
        .form-control:focus + .control-label:after,
        .form-group:hover .control-label:after {
            width: 30px;
        }
        
        /* Estilos para los inputs */
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 10px 15px;
            transition: all 0.3s ease;
            background-color: #f9f9fc;
        }
        
        .form-control:focus {
            border-color: #4285f4;
            box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.15);
            background-color: #fff;
            transform: translateY(-2px);
        }
        
        .form-control:hover {
            border-color: #4285f4;
            background-color: #f5f7ff;
        }
        
        /* Estilos para los grupos de formulario */
        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }
        
        /* Estilos para la imagen de perfil */
        .avatar-preview-container {
            text-align: center;
            margin-top: 15px !important;
            padding: 10px;
            border-radius: 8px;
            background: linear-gradient(145deg, #f5f7ff, #fff);
            display: inline-block;
        }
        
        .img-thumbnail {
            border: 2px solid #4285f4;
            border-radius: 50% !important;
            padding: 3px;
            transition: all 0.4s ease;
            width: 100px;
            height: 100px;
            object-fit: cover;
            max-width: 100%; /* Prevenir que la imagen exceda su contenedor */
        }
        
        .img-thumbnail:hover {
            border-color: #2a75f3;
            box-shadow: 0 5px 15px rgba(66, 133, 244, 0.3);
            transform: scale(1.05);
        }
        
        /* Estilos para las alertas */
        .alert-danger {
            border: none;
            border-left: 4px solid #4285f4;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease-out;
            display: flex;
            align-items: center;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        /* Nuevas animaciones */
        .animated {
            animation-duration: 0.5s;
        }
        
        .fadeIn {
            animation-name: fadeIn;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Asegurar compatibilidad con topbar */
        #manage-user-container form {
            width: 100%;
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            #manage-user-container {
                margin: 10px;
                padding: 15px;
            }
        }
        
        /* Prevenir overflow */
        body {
            overflow-x: hidden;
        }
        
        #manage-user-container {
            overflow: hidden;
        }
        }
        
        /* Estilo para el título de la página */
        .page-header {
            border-left: 4px solid #4285f4;
            padding-left: 15px;
            margin-bottom: 25px;
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
            background: -webkit-linear-gradient(45deg, #4285f4, #2a75f3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Efecto para campos con focus */
        .input-focus-effect {
            position: relative;
        }
        
        .input-focus-effect:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: #4285f4;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            transition: width 0.3s ease;
        }
        
        .form-control:focus + .input-focus-effect:after {
            width: 100%;
        }
        
        /* Estilos para el botón */
        .btn-primary {
            background: linear-gradient(135deg, #4285f4, #2a75f3);
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2a75f3, #1a65e3);
            box-shadow: 0 6px 20px rgba(66, 133, 244, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.4);
        }
        
        /* Estilos para el selector de archivo */
        /* Estilos para el selector de archivo - específicos para esta página */
        #manage-user-container .custom-file-container {
            position: relative;
            border: 1px dashed #4285f4;
            border-radius: 8px;
            padding: 10px;
            background-color: #f8fbff;
            transition: all 0.3s;
        }
        
        #manage-user-container .custom-file-container:hover {
            background-color: #f0f7ff;
            border-color: #2a75f3;
        }
    </style>
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
            <input type="password" class="form-control" name="password" id="password" value="" autocomplete="off" <?php echo isset($id) ? '' : 'required'; ?>>
            <?php if (isset($password)) : ?>
                <small><i>Dejar en blanco si no desea cambiar la contraseña.</i></small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Repetir Contraseña</label>
            <input type="password" class="form-control" name="repeat_password" id="repeat_password" value="" autocomplete="off" <?php echo isset($id) ? '' : 'required'; ?>>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Foto de Perfil</label>
            <div class="custom-file-container">
                <input type="file" class="form-control" name="avatar" accept="image/*" onchange="displayImg(this)" style="padding: 10px;">
                <div class="file-upload-info d-flex align-items-center mt-2">
                    <i class="fa fa-cloud-upload" style="color: #4285f4; margin-right: 8px;"></i>
                    <small class="text-muted">Haga clic para seleccionar una imagen de perfil (opcional)</small>
                </div>
            </div>
            <?php
            $avatar_path = '';
            if (isset($avatar) && !empty($avatar)) {
                $avatar_path = "assets/uploads/" . $avatar;
            }
            ?>
            <div class="avatar-preview-container mt-3">
                <img id="user_avatar_preview" src="<?php echo $avatar_path ?>" alt="Avatar" class="img-thumbnail" <?php echo empty($avatar_path) ? 'style="display:none;"' : '' ?>>
                <?php if (!empty($avatar_path)) : ?>
                <div class="avatar-info mt-2">
                    <small class="text-success"><i class="fa fa-check-circle"></i> Imagen cargada</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
       
    </form>
    <div class="clearfix"></div>
</div>
<script>
    function displayImg(input) {
        if (input.files && input.files[0]) {
            // Cambiar estilo del contenedor para mostrar que hay un archivo seleccionado
            $(input).closest('.custom-file-container').css({
                'border-color': '#4285f4',
                'background': 'rgba(66, 133, 244, 0.05)'
            });
            
            // Cambiar el texto después de seleccionar archivo
            $(input).siblings('.file-upload-info').html('<small class="text-primary"><i class="fa fa-file-image-o mr-1"></i> Imagen seleccionada: ' + input.files[0].name + '</small>');
            
            var reader = new FileReader();
            reader.onload = function(e) {
                // Mostrar previsualización con animación
                $('#user_avatar_preview')
                    .attr('src', e.target.result)
                    .hide()
                    .fadeIn(500);
                    
                // Agregar información adicional de la imagen
                var html = '<div class="avatar-info mt-2">' +
                           '<small class="text-success"><i class="fa fa-check-circle"></i> Imagen lista para subir</small>' +
                           '</div>';
                           
                $('.avatar-preview-container').find('.avatar-info').remove();
                $('.avatar-preview-container').append(html);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Validación en tiempo real de contraseñas
    $('#password, #repeat_password').on('keyup', function() {
        var pass = $('#password').val();
        var repeat = $('#repeat_password').val();
        
        if (pass.trim() !== '' && repeat.trim() !== '') {
            if (pass === repeat) {
                $('#repeat_password').css('border-color', '#4CAF50').next('.password-match-indicator').remove();
                $('<div class="password-match-indicator text-success mt-1"><i class="fa fa-check-circle"></i> Las contraseñas coinciden</div>').insertAfter('#repeat_password');
            } else {
                $('#repeat_password').css('border-color', '#f44336').next('.password-match-indicator').remove();
                $('<div class="password-match-indicator text-danger mt-1"><i class="fa fa-times-circle"></i> Las contraseñas no coinciden</div>').insertAfter('#repeat_password');
            }
        } else {
            $('#repeat_password').css('border-color', '').next('.password-match-indicator').remove();
        }
    });

    $('#manage-user').submit(function(e) {
        e.preventDefault();
        $('#msg').html(''); // Limpiar mensajes previos

        // Validar que las contraseñas coincidan
        var pass = $('#password').val();
        var repeat = $('#repeat_password').val();
        if(pass !== repeat && pass.trim() !== '') {
            $('#msg').html('<div class="alert alert-danger animated fadeIn"><i class="fa fa-exclamation-circle mr-2"></i> Las contraseñas no coinciden.</div>');
            $('#repeat_password').focus();
            return false;
        }

        start_load();
        $.ajax({
            url: 'ajax.php?action=save_user',
            method: 'POST',
            data: new FormData($(this)[0]),
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(resp) {
                if (resp == 1 || (resp.status && resp.status == 1)) {
                    // Mostrar animación de éxito en el botón
                    $('.btn-primary').prepend('<i class="fa fa-check-circle mr-1"></i>').find('.fa-save').remove();
                    $('.btn-primary').addClass('btn-success').removeClass('btn-primary')
                        .css('background', 'linear-gradient(135deg, #42b983, #2f9968)');
                    
                    // Mostrar mensaje de éxito
                    alert_toast("Perfil actualizado con éxito.", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else if (resp == 2 || (resp.status && resp.status == 2)) {
                    $('#msg').html('<div class="alert alert-danger animated fadeIn"><i class="fa fa-exclamation-triangle mr-2"></i> El nombre de usuario ya existe.</div>');
                    end_load();
                } else {
                    $('#msg').html('<div class="alert alert-danger animated fadeIn"><i class="fa fa-exclamation-circle mr-2"></i> Ocurrió un error al guardar los datos.</div>');
                    end_load();
                }
            },
            error: function(err) {
                console.log(err);
                $('#msg').html('<div class="alert alert-danger animated fadeIn"><i class="fa fa-exclamation-circle mr-2"></i> Error en la solicitud. Verifique la consola para más detalles.</div>');
                end_load();
            }
        });
    });
</script>