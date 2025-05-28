<?php
include 'db_connect.php';
session_start();

// Obtener el school_id del administrador
$school_id = $_SESSION['login_school_id'] ?? 0;

if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM student WHERE id = ".$_GET['id']." AND school_id = ".$school_id);
    foreach($qry->fetch_array() as $k => $val){
        $$k = $val;
    }
}
?>
<style>
    .student-form {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 3px 15px rgba(66, 133, 244, 0.1);
        transition: all 0.3s ease;
    }
    
    .student-form .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .student-form .form-group label {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        margin-bottom: 8px;
        transition: all 0.3s;
        display: block;
    }
    
    .student-form .form-control {
        border-radius: 6px;
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0;
        padding: 10px 15px;
        background-color: #f9f9fc;
        font-size: 0.95rem;
    }
    
    .student-form .form-control:hover {
        border-color: #4285f4;
        background-color: #f5f7ff;
    }
    
    .student-form .form-control:focus {
        border-color: #4285f4;
        box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.15);
        background-color: #fff;
        transform: translateY(-1px);
    }
    
    .student-form .input-group-text {
        background: linear-gradient(135deg, #4285f4, #2a75f3);
        color: white;
        border: none;
        border-radius: 6px 0 0 6px;
        width: 40px;
        display: flex;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .student-form .input-group-text i {
        transition: all 0.3s;
    }
    
    .student-form .input-group:hover .input-group-text i {
        transform: scale(1.1);
    }
    
    .student-form .input-group:focus-within .input-group-text {
        background: linear-gradient(135deg, #2a75f3, #1a65e3);
    }
    
    /* Estilos para los niveles con colores mejorados */
    .nivel-inicial { 
        background-color: #fff8e1; 
        color: #664d00; 
        border-left: 3px solid #ffb300;
    }
    
    .nivel-primaria { 
        background-color: #e8f5e9; 
        color: #1b5e20; 
        border-left: 3px solid #43a047;
    }
    
    .nivel-secundaria { 
        background-color: #e3f2fd; 
        color: #0d47a1; 
        border-left: 3px solid #1976d2;
    }
    
    .form-section {
        margin-bottom: 25px;
        border-bottom: 1px solid #f1f1f1;
        padding-bottom: 15px;
    }
    
    .select-styled {
        border-left: 3px solid #4285f4;
        font-weight: 500;
        background-image: linear-gradient(to right, rgba(66, 133, 244, 0.05), transparent);
    }
    
    /* Estilo para campos requeridos */
    .form-control:required {
        background-image: linear-gradient(to right, rgba(66, 133, 244, 0.03), transparent);
    }
    
    /* Efectos para los selects */
    select.form-control {
        cursor: pointer;
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg fill="%234285f4" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }
    
    /* Estilo para el mensaje de alerta */
    .alert-danger {
        border: none;
        border-left: 4px solid #4285f4;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    /* Ajustes para textarea */
    textarea.form-control {
        resize: none;
        min-height: 100px;
    }
    
    /* Estilo para etiquetas activas */
    .active-label {
        color: #4285f4 !important;
        transform: translateY(-2px);
    }
    
    /* Animación al enviar el formulario */
    .form-control.submitting {
        transition: all 0.5s;
    }
    
    .success-pulse {
        animation: successPulse 1.5s;
    }
    
    @keyframes successPulse {
        0% { background: linear-gradient(135deg, #4285f4, #2a75f3); }
        50% { background: linear-gradient(135deg, #42b983, #2f9968); }
        100% { background: linear-gradient(135deg, #4285f4, #2a75f3); }
    }
</style>

<div class="container-fluid">
    <h4 class="form-title" style="margin-bottom: 20px; color: #4285f4; border-left: 4px solid #4285f4; padding-left: 15px; font-weight: 600;">
        <?php echo isset($id) ? "Editar Estudiante" : "Nuevo Estudiante"; ?>
    </h4>
    <form action="" id="manage-student" class="student-form">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <input type="hidden" name="school_id" value="<?php echo $school_id ?>">
        <div id="msg" class="form-group"></div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="id_no" class="control-label">DNI</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                        </div>
                        <input type="text" class="form-control" id="id_no" name="id_no" value="<?php echo isset($id_no) ? $id_no : '' ?>" required>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="name" class="control-label">Nombre</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? $name : '' ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email" class="control-label">Correo</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : '' ?>" required>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="contact" class="control-label">Contacto</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-phone"></i></span>
                        </div>
                        <input type="text" class="form-control" id="contact" name="contact" value="<?php echo isset($contact) ? $contact : '' ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="control-label">Dirección</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-map-marker"></i></span>
                </div>
                <textarea name="address" id="address" cols="30" rows="3" class="form-control" required><?php echo isset($address) ? $address : '' ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="nivel_select" class="control-label">Nivel</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-graduation-cap"></i></span>
                        </div>
                        <select id="nivel_select" class="form-control select-styled" required>
                            <option value="">Seleccione un nivel</option>
                            <option value="Inicial" <?php echo (isset($nivel) && $nivel == 'Inicial') ? 'selected' : '' ?>>Inicial</option>
                            <option value="Primaria" <?php echo (isset($nivel) && $nivel == 'Primaria') ? 'selected' : '' ?>>Primaria</option>
                            <option value="Secundaria" <?php echo (isset($nivel) && $nivel == 'Secundaria') ? 'selected' : '' ?>>Secundaria</option>
                        </select>
                        <!-- Mantenemos el campo original oculto para compatibilidad con el backend -->
                        <input type="hidden" name="nivel" id="nivel_hidden" value="<?php echo isset($nivel) ? $nivel : '' ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="grado_select" class="control-label">Grado</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-list-ol"></i></span>
                        </div>
                        <select id="grado_select" class="form-control select-styled" required>
                            <option value="">Seleccione un grado</option>
                            <option value="1°" <?php echo (isset($grado) && $grado == '1°') ? 'selected' : '' ?>>1°</option>
                            <option value="2°" <?php echo (isset($grado) && $grado == '2°') ? 'selected' : '' ?>>2°</option>
                            <option value="3°" <?php echo (isset($grado) && $grado == '3°') ? 'selected' : '' ?>>3°</option>
                            <option value="4°" <?php echo (isset($grado) && $grado == '4°') ? 'selected' : '' ?>>4°</option>
                            <option value="5°" <?php echo (isset($grado) && $grado == '5°') ? 'selected' : '' ?>>5°</option>
                            <option value="6°" <?php echo (isset($grado) && $grado == '6°') ? 'selected' : '' ?>>6°</option>
                        </select>
                        <!-- Mantenemos el campo original oculto para compatibilidad con el backend -->
                        <input type="hidden" name="grado" id="grado_hidden" value="<?php echo isset($grado) ? $grado : '' ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="seccion" class="control-label">Sección</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-tag"></i></span>
                        </div>
                        <select class="form-control select-styled" name="seccion" id="seccion" required>
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
                </div>
            </div>
        </div>
        <div class="form-group text-right mt-4">
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #4285f4, #2a75f3); border-color: #2a75f3; padding: 8px 20px; border-radius: 30px; box-shadow: 0 4px 15px rgba(66, 133, 244, 0.3);">
                <i class="fa fa-save mr-2"></i> Guardar Estudiante
            </button>
        </div>
    </form>
</div>

<script>
    // Funciones para los selectores de nivel y grado
    $(document).ready(function() {
        // Inicializar valores si ya existen
        if ($('#nivel_hidden').val()) {
            $('#nivel_select').val($('#nivel_hidden').val());
            aplicarEstiloNivel($('#nivel_select').val());
        }
        
        if ($('#grado_hidden').val()) {
            $('#grado_select').val($('#grado_hidden').val());
        }
        
        // Evento para el cambio de nivel
        $('#nivel_select').change(function() {
            let nivelSeleccionado = $(this).val();
            $('#nivel_hidden').val(nivelSeleccionado);
            aplicarEstiloNivel(nivelSeleccionado);
            
            // Filtrar grados disponibles según nivel seleccionado
            filtrarGradosPorNivel(nivelSeleccionado);
        });
        
        // Evento para el cambio de grado
        $('#grado_select').change(function() {
            $('#grado_hidden').val($(this).val());
        });
        
        // Inicializar filtrado de grados
        filtrarGradosPorNivel($('#nivel_select').val());
        
        // Añadir efecto visual al hacer focus en los campos
        $('.form-control').focus(function() {
            $(this).closest('.form-group').find('label').addClass('active-label');
        }).blur(function() {
            $(this).closest('.form-group').find('label').removeClass('active-label');
        });
    });
    
    // Función para aplicar estilos según el nivel seleccionado
    function aplicarEstiloNivel(nivel) {
        $('#nivel_select').removeClass('nivel-inicial nivel-primaria nivel-secundaria');
        
        if (nivel === 'Inicial') {
            $('#nivel_select').addClass('nivel-inicial');
        } else if (nivel === 'Primaria') {
            $('#nivel_select').addClass('nivel-primaria');
        } else if (nivel === 'Secundaria') {
            $('#nivel_select').addClass('nivel-secundaria');
        }
    }
    
    // Función para filtrar grados según el nivel educativo
    function filtrarGradosPorNivel(nivel) {
        $('#grado_select option').show();
        
        if (nivel === 'Inicial') {
            // En Inicial solo hay hasta 3er grado
            $('#grado_select option[value="4°"]').hide();
            $('#grado_select option[value="5°"]').hide();
            $('#grado_select option[value="6°"]').hide();
        } else if (nivel === 'Secundaria') {
            // Verificar si el valor actual ya no es válido con el nuevo nivel
            if ($('#grado_select').val() === '6°') {
                $('#grado_select').val('5°');
                $('#grado_hidden').val('5°');
            }
        }
        
        // Si el grado seleccionado no está visible, seleccionar el primero disponible
        if ($('#grado_select option:selected').css('display') === 'none') {
            $('#grado_select').val($('#grado_select option:visible:first').val());
            $('#grado_hidden').val($('#grado_select').val());
        }
    }

    $('#manage-student').on('reset', function() {
        $('#msg').html('');
        $('input:hidden').val('');
    });

    $('#manage-student').submit(function(e) {
        e.preventDefault();
        start_load();
        $('#msg').html('');
        
        // Animación de los inputs al enviar
        $('.form-control').addClass('submitting');
        
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
                        // Mostrar animación de éxito
                        $('.input-group-text').addClass('success-pulse');
                        
                        alert_toast(resp.message, 'success'); // Mostrar mensaje de éxito
                        setTimeout(function() {
                            location.reload(); // Recargar la página después de guardar
                        }, 800);
                    } else if (resp.status == 2) {
                        $('#msg').html('<div class="alert alert-danger mx-2"><i class="fa fa-exclamation-circle mr-2"></i>' + resp.message + '</div>');
                        end_load();
                        $('.form-control').removeClass('submitting');
                    } else {
                        alert_toast(resp.message, 'danger');
                        end_load();
                        $('.form-control').removeClass('submitting');
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