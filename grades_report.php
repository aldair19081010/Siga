<?php
include 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$school_id = $_SESSION['login_school_id'] ?? null;
$login_type = $_SESSION['login_type'] ?? null;
$is_admin = ($login_type == 1);
$is_teacher = ($login_type == 2);
$teacher_id = $_SESSION['login_teacher_id'] ?? null;

if (!$school_id || !$login_type) {
    die("Acceso no autorizado o configuración de sesión incompleta. Por favor, inicie sesión nuevamente.");
}

function normalize_level_for_key($level_name) {
    if (empty($level_name)) return '';
    return strtolower(str_replace(' ', '', trim($level_name)));
}

// --- Cargar opciones de filtros ---
$courses = [];
$levels = [];
$grades = [];
$sections = [];
$students = [];

if ($is_admin) {
    $q = $conn->prepare("SELECT id, name, level FROM academic_courses WHERE school_id = ? ORDER BY name");
    $q->bind_param("i", $school_id);
    $q->execute();
    $res = $q->get_result();
    $levels_set = [];
    while ($row = $res->fetch_assoc()) {
        $courses[$row['id']] = ['name' => $row['name'], 'level' => $row['level']];
        if (!in_array($row['level'], $levels_set) && $row['level']) $levels_set[] = $row['level'];
    }
    $levels = $levels_set;
    $q->close();
    $grades = [];
    $sections = [];
    $qg = $conn->prepare("SELECT DISTINCT grado FROM student WHERE school_id = ? AND grado != '' ORDER BY grado");
    $qg->bind_param("i", $school_id);
    $qg->execute();
    $rg = $qg->get_result();
    while($row = $rg->fetch_assoc()) $grades[] = $row['grado'];
    $qg->close();
    $qs = $conn->prepare("SELECT DISTINCT seccion FROM student WHERE school_id = ? AND seccion != '' ORDER BY seccion");
    $qs->bind_param("i", $school_id);
    $qs->execute();
    $rs = $qs->get_result();
    while($row = $rs->fetch_assoc()) $sections[] = $row['seccion'];
    $qs->close();
} elseif ($is_teacher && $teacher_id) {
    $q = $conn->prepare("SELECT ac.id as course_id, ac.name, ac.level, tc.grado, tc.seccion FROM teacher_courses tc INNER JOIN academic_courses ac ON ac.id = tc.course_id WHERE tc.teacher_id = ? AND tc.school_id = ? ORDER BY ac.name, tc.grado, tc.seccion");
    $q->bind_param("ii", $teacher_id, $school_id);
    $q->execute();
    $res = $q->get_result();
    $levels_set = [];
    $grades_set = [];
    $sections_set = [];
    while ($row = $res->fetch_assoc()) {
        $courses[$row['course_id']] = ['name' => $row['name'], 'level' => $row['level']];
        if (!in_array($row['level'], $levels_set) && $row['level']) $levels_set[] = $row['level'];
        if (!in_array($row['grado'], $grades_set) && $row['grado']) $grades_set[] = $row['grado'];
        if (!in_array($row['seccion'], $sections_set) && $row['seccion']) $sections_set[] = $row['seccion'];
    }
    $levels = $levels_set;
    $grades = $grades_set;
    $sections = $sections_set;
    $q->close();
}

// --- Obtener valores seleccionados ---
$selected_course = $_POST['course_id'] ?? '';
$selected_level = $_POST['level'] ?? '';
$selected_grado = $_POST['grado'] ?? '';
$selected_seccion = $_POST['seccion'] ?? '';
$selected_bimestre = $_POST['bimestre'] ?? '';
$selected_evaluation = $_POST['evaluation_id'] ?? '';
$selected_student = $_POST['student_id'] ?? '';

// --- Cargar alumnos si hay filtros ---
if ($selected_level && $selected_grado && $selected_seccion) {
    $students = [];
    $sql = "SELECT id, name, id_no FROM student WHERE LOWER(REPLACE(TRIM(nivel), ' ', '')) = ? AND grado = ? AND seccion = ? AND school_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $level_key = normalize_level_for_key($selected_level);
    $stmt->bind_param("sssi", $level_key, $selected_grado, $selected_seccion, $school_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $students[] = $row;
    $stmt->close();
}
?>
<div class="container-fluid">
    <div class="report-card">
        <h4 class="mb-3">Reporte de Notas</h4>        <form id="filter-form" class="form-row align-items-end mb-4">
            <div class="form-group col-md-2">
                <label>Nivel:</label>
                <select name="level" id="level" class="form-control select2">
                    <option value="">Seleccionar</option>
                    <?php foreach($levels as $l): ?>
                        <option value="<?php echo $l; ?>" <?php echo ($selected_level == $l) ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Grado:</label>
                <select name="grado" id="grado" class="form-control select2" <?php echo empty($selected_level) ? 'disabled' : ''; ?>>
                    <option value="">Seleccionar</option>
                    <?php if(!empty($selected_level)): ?>
                        <?php foreach($grades as $g): ?>
                            <option value="<?php echo $g; ?>" <?php echo ($selected_grado == $g) ? 'selected' : ''; ?>><?php echo $g; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Sección:</label>
                <select name="seccion" id="seccion" class="form-control select2" <?php echo empty($selected_grado) ? 'disabled' : ''; ?>>
                    <option value="">Seleccionar</option>
                    <?php if(!empty($selected_grado)): ?>
                        <?php foreach($sections as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo ($selected_seccion == $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Curso:</label>
                <select name="course_id" id="course_id" class="form-control select2" <?php echo empty($selected_seccion) ? 'disabled' : ''; ?>>
                    <option value="">Todos</option>
                    <?php if(!empty($selected_level) && !empty($selected_grado) && !empty($selected_seccion)): ?>
                        <?php foreach($courses as $cid => $c): ?>
                            <?php if($c['level'] == $selected_level): ?>
                                <option value="<?php echo $cid; ?>" <?php echo ($selected_course == $cid) ? 'selected' : ''; ?>><?php echo $c['name']; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>            <div class="form-group col-md-2">
                <label for="bimestre">
                    Bimestre: 
                    <span class="text-danger">*</span> 
                    <span class="badge badge-warning" style="font-size: 0.7rem; vertical-align: middle;">REQUERIDO</span>
                </label>
                <select name="bimestre" id="bimestre" class="form-control select2" data-placeholder="Seleccionar bimestre">
                    <option value="">Seleccionar bimestre</option>
                    <option value="1" <?php echo ($selected_bimestre == '1') ? 'selected' : ''; ?>>1° Bimestre</option>
                    <option value="2" <?php echo ($selected_bimestre == '2') ? 'selected' : ''; ?>>2° Bimestre</option>
                    <option value="3" <?php echo ($selected_bimestre == '3') ? 'selected' : ''; ?>>3° Bimestre</option>
                    <option value="4" <?php echo ($selected_bimestre == '4') ? 'selected' : ''; ?>>4° Bimestre</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Evaluación:</label>
                <select name="evaluation_id" id="evaluation_id" class="form-control select2">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>Alumno:</label>
                <select name="student_id" id="student_id" class="form-control select2">
                    <option value="">Todos</option>
                    <?php foreach($students as $stu): ?>
                        <option value="<?php echo $stu['id']; ?>" <?php echo ($selected_student == $stu['id']) ? 'selected' : ''; ?>><?php echo ucwords($stu['name']) . " ({$stu['id_no']})"; ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="no-students-msg"></div>
            </div>            <div class="form-group col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary btn-block btn-search">
                    <i class="fa fa-search"></i> Buscar
                </button>
            </div>            <div class="form-group col-md-2 align-self-end">
                <button type="button" class="btn btn-secondary btn-block btn-print" id="print-report">
                    <i class="fa fa-print"></i> Imprimir
                </button>
            </div>            <div class="form-group col-md-2 align-self-end">
                <button type="button" class="btn btn-success btn-block btn-gradient" id="show-avg-report" data-toggle="tooltip" data-html="true" title="<span style='font-size:12px'><b>IMPORTANTE</b>:<br>Debe seleccionar un <b>alumno</b> y un <b>bimestre específico</b></span>">
                    <i class="fa fa-chart-bar"></i> <span>Ver Promedio</span> <i class="fa fa-info-circle bimestre-required"></i>
                </button>
            </div>
        </form>        <div id="bimestre-notification" style="display: none; margin-top: 20px; margin-bottom: 20px;" class="alert alert-warning animate__animated animate__fadeIn">
            <div class="d-flex align-items-center" style="background: linear-gradient(45deg, rgba(255,193,7,0.1) 0%, rgba(255,242,213,0.3) 100%); padding: 15px; border-radius: 8px; border-left: 5px solid #ffc107;">
                <i class="fa fa-exclamation-triangle" style="font-size: 28px; margin-right: 18px; color: #ff9800;"></i>
                <div>
                    <h5 class="alert-heading mb-1" style="color: #e65100; font-weight: 600;">¡Bimestre requerido!</h5>
                    <p class="mb-0" style="color: #33333; font-weight: 500;">Para ver el promedio, primero debe seleccionar un bimestre específico.</p>
                    <p class="mt-2 mb-0"><button id="goto-bimestre" class="btn btn-sm" style="background: #ff9800; color: white; font-weight: 600; border-radius: 6px; padding: 4px 12px;">Seleccionar bimestre</button></p>
                </div>
            </div>
        </div>
        <div id="grades-report-table"></div>
    </div>
</div>
<style>    /* Mejoras adicionales al diseño general */
    .report-card { 
        max-width: 1100px; 
        margin: 30px auto 0 auto; 
        background: linear-gradient(to bottom right, #ffffff, #f7f9fc); 
        border-radius: 16px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.15), 0 1px 5px rgba(0,0,0,0.1); 
        padding: 35px 35px 30px 35px;
        border-top: 5px solid #4285f4;
        border-bottom: 1px solid rgba(66, 133, 244, 0.2);
    }
    
    .report-card h4 {
        color: #4285f4;
        font-weight: 700;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(66, 133, 244, 0.2);
        font-size: 1.5rem;
        letter-spacing: -0.5px;
    }
    
    .report-card label { 
        font-weight: 600; 
        color: #5f6368;
        font-size: 0.95rem;
        margin-bottom: 8px;
        letter-spacing: -0.2px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    /* Estilos para botones mejorados */
    .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
    }
    
    .btn-search {
        background: linear-gradient(135deg, #4285f4, #1a73e8);
        border: none;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.1);
        box-shadow: 0 4px 12px rgba(26, 115, 232, 0.3);
    }
    
    .btn-search:hover {
        background: linear-gradient(135deg, #1a73e8, #0d65d9);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(26, 115, 232, 0.4);
    }
    
    .btn-print {
        background: linear-gradient(135deg, #78909c, #546e7a);
        border: none;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.1);
        box-shadow: 0 4px 12px rgba(84, 110, 122, 0.3);
    }
    
    .btn-print:hover {
        background: linear-gradient(135deg, #546e7a, #455a64);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(84, 110, 122, 0.4);
    }
    
    .btn-gradient {
        background: linear-gradient(135deg, #0f9d58, #0c8043);
        border: none;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.1);
        box-shadow: 0 4px 12px rgba(15, 157, 88, 0.3);
    }
    
    .btn-gradient:hover {
        background: linear-gradient(135deg, #0c8043, #0a6b38);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(15, 157, 88, 0.4);
    }
    
    .btn:active {
        transform: translateY(1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }
    
    .btn i {
        margin-right: 5px;
    }
    
    /* Ripple effect para botones */
    .btn::after {
        content: '';
        display: block;
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        pointer-events: none;
        background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
        background-repeat: no-repeat;
        background-position: 50%;
        transform: scale(10, 10);
        opacity: 0;
        transition: transform .5s, opacity 1s;
    }
    
    .btn:active::after {
        transform: scale(0, 0);
        opacity: .3;
        transition: 0s;
    }
    #grades-report-table { 
        margin-top: 30px; 
    }
      /* Estilos mejorados para botón Ver Promedio */
    .btn-gradient {
        background: linear-gradient(135deg, #28a745, #218838);
        border: none;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .btn-gradient:hover {
        background: linear-gradient(135deg, #218838, #1e7e34);
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.15);
    }
    .btn-gradient:active {
        transform: translateY(0);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }    /* Animación de pulsación para el botón */
    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 3px 6px rgba(0,0,0,0.1); }
        50% { transform: scale(0.97); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        100% { transform: scale(1); box-shadow: 0 3px 6px rgba(0,0,0,0.1); }
    }
      .pulse-animation {
        animation: pulse 0.3s;
    }
    
    /* Estilos para resaltar el bimestre requerido */
    .bimestre-required {
        color: #ffc107;
        margin-left: 5px;
        animation: pulsate 1.5s infinite;
    }
    
    @keyframes pulsate {
        0% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.2); }
        100% { opacity: 0.5; transform: scale(1); }
    }    .bimestre-highlight {
        border: 3px solid #ffc107 !important;
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.7) !important;
        animation: highlight-pulse 1.5s infinite !important;
        background: rgba(255, 248, 225, 0.7) !important;
        position: relative;
        z-index: 10;
        transform: scale(1.02);
    }
    
    @keyframes highlight-pulse {
        0% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.4) !important; }
        50% { box-shadow: 0 0 20px rgba(255, 193, 7, 0.9) !important; border-color: #ffb300 !important; }
        100% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.4) !important; }
    }
    
    /* Añadir un pseudo-elemento para un efecto de cinta en el bimestre requerido */
    .select2-container--default .select2-selection--single.bimestre-highlight::before {
        content: 'Requerido';
        position: absolute;
        top: -15px;
        right: 10px;
        background: #ffc107;
        color: #000;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px 4px 0 0;
        font-weight: bold;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        z-index: 20;
        animation: fadeInOut 2s infinite;
    }
    
    @keyframes fadeInOut {
        0% { opacity: 0.7; }
        50% { opacity: 1; }
        100% { opacity: 0.7; }
    }
      /* Estilos para mejorar la apariencia de los Select2 - NUEVO DISEÑO */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4285f4;
    }
    
    /* Estos estilos son reemplazados por la sección de estilos reforzados */
    /* Se mantienen aquí para referencia pero no tienen efecto debido a la mayor especificidad
     * de los selectores en la sección "REFORZADO: Estilos modernos y mejorados para Select2" 
     *//* Estilos para el botón Buscar */
    .btn-search {
        background: linear-gradient(135deg, #007bff, #0062cc);
        border: none;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .btn-search:hover {
        background: linear-gradient(135deg, #0069d9, #0056b3);
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.15);
    }    .btn-search:active {
        transform: translateY(0);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    /* Estilos para el botón Imprimir */
    .btn-print {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        border: none;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .btn-print:hover {
        background: linear-gradient(135deg, #5a6268, #4e555b);
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.15);
    }
    .btn-print:active {
        transform: translateY(0);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    /* Para dispositivos móviles */
    @media (max-width: 1200px) { .report-card { padding: 18px 8px 10px 8px; } }
    @media (max-width: 700px) { .report-card { padding: 8px 2px 5px 2px; } }
    
    /* REFORZADO: Estilos modernos y mejorados para Select2 */
.select2-container--default .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default .select2-selection--single:focus,
.select2-container--default .select2-selection--single:hover,
.select2-container--default.select2-container--open .select2-selection--single {
    border: 3px solid #4285f4 !important;
    background: linear-gradient(to bottom, #ffffff, #f9f9f9) !important;
    height: 42px !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 10px rgba(66, 133, 244, 0.15) !important;
    outline: none !important;
    transition: all 0.3s ease !important;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 15px !important;
    color: #495057 !important;
    font-weight: 500 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    width: 30px !important;
    right: 5px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #4285f4 transparent transparent transparent !important;
    border-width: 6px 4px 0 4px !important;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #4285f4 transparent !important;
    border-width: 0 4px 6px 4px !important;
}

.select2-container--default.select2-container--disabled .select2-selection--single {
    border: 3px solid #e0e0e0 !important;
    background: #f5f5f5 !important;
    color: #b0b3b8 !important;
    opacity: 0.8 !important;
}

.select2-dropdown {
    border: 3px solid #4285f4 !important;
    border-top: none !important;
    border-radius: 0 0 12px 12px !important;
    box-shadow: 0 8px 16px rgba(66, 133, 244, 0.2) !important;
    overflow: hidden !important;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 2px solid #4285f4 !important;
    border-radius: 6px !important;
    padding: 8px !important;
}

/* Efectos de animación para Select2 */
    .select2-container--open .select2-dropdown {
        animation: fadeInSelect 0.3s ease-out;
    }
    
    @keyframes fadeInSelect {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Indicador de selección con check */
    .select2-results__option[aria-selected=true] {
        position: relative;
        padding-right: 25px !important;
        font-weight: 600 !important;
        color: #4285f4 !important;
    }
    
    .select2-results__option[aria-selected=true]::after {
        content: '✓';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #4285f4;
        font-weight: bold;
    }
    
    /* Placeholder con estilo */
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9aa0a6 !important;
        font-style: italic;
    }
    
    /* Efecto hover en los items de la lista */
    .select2-results__option:hover {
        background-color: #f1f5ff !important;
    }
    
    /* Fin refuerzo bordes Select2 */
</style>

<!-- Cargar animate.css para animaciones adicionales -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<script>
$('.select2').select2({ width: '100%' });

// Función para actualizar grados según el nivel seleccionado
function updateGrados() {
    var nivel = $('#level').val();
    if (!nivel) {
        $('#grado').html('<option value="">Seleccionar</option>').prop('disabled', true).trigger('change');
        $('#seccion').html('<option value="">Seleccionar</option>').prop('disabled', true).trigger('change');
        $('#course_id').html('<option value="">Todos</option>').prop('disabled', true).trigger('change');
        $('#student_id').html('<option value="">Todos</option>').trigger('change');
        return;
    }
    
    $.ajax({
        url: 'ajax.php?action=get_grados_by_nivel',
        method: 'POST',
        data: { level: nivel },
        dataType: 'json',
        success: function(resp) {
            var html = '<option value="">Seleccionar</option>';
            if (resp && Array.isArray(resp.grados) && resp.grados.length > 0) {
                resp.grados.forEach(function(grado) {
                    html += '<option value="'+grado+'">'+grado+'</option>';
                });
                $('#grado').html(html).prop('disabled', false).trigger('change');
            } else {
                $('#grado').html(html).prop('disabled', true).trigger('change');
                $('#seccion').html('<option value="">Seleccionar</option>').prop('disabled', true).trigger('change');
                $('#course_id').html('<option value="">Todos</option>').prop('disabled', true).trigger('change');
            }
        },
        error: function() {
            $('#grado').html('<option value="">Error al cargar</option>').prop('disabled', true).trigger('change');
        }
    });
}

// Función para actualizar secciones según el grado y nivel seleccionados
function updateSecciones() {
    var nivel = $('#level').val();
    var grado = $('#grado').val();
    
    if (!nivel || !grado) {
        $('#seccion').html('<option value="">Seleccionar</option>').prop('disabled', true).trigger('change');
        $('#course_id').html('<option value="">Todos</option>').prop('disabled', true).trigger('change');
        return;
    }
    
    $.ajax({
        url: 'ajax.php?action=get_secciones_by_grado_nivel',
        method: 'POST',
        data: { level: nivel, grado: grado },
        dataType: 'json',
        success: function(resp) {
            var html = '<option value="">Seleccionar</option>';
            if (resp && Array.isArray(resp.secciones) && resp.secciones.length > 0) {
                resp.secciones.forEach(function(seccion) {
                    html += '<option value="'+seccion+'">'+seccion+'</option>';
                });
                $('#seccion').html(html).prop('disabled', false).trigger('change');
            } else {
                $('#seccion').html(html).prop('disabled', true).trigger('change');
                $('#course_id').html('<option value="">Todos</option>').prop('disabled', true).trigger('change');
            }
        },
        error: function() {
            $('#seccion').html('<option value="">Error al cargar</option>').prop('disabled', true).trigger('change');
        }
    });
}

// Función para actualizar cursos según nivel, grado y sección
function updateCursos() {
    var nivel = $('#level').val();
    var grado = $('#grado').val();
    var seccion = $('#seccion').val();
    
    if (!nivel || !grado || !seccion) {
        $('#course_id').html('<option value="">Todos</option>').prop('disabled', true).trigger('change');
        return;
    }
    
    $.ajax({
        url: 'ajax.php?action=get_courses_by_aula',
        method: 'POST',
        data: { level: nivel, grado: grado, seccion: seccion },
        dataType: 'json',
        success: function(resp) {
            var html = '<option value="">Todos</option>';
            if (resp && Array.isArray(resp.courses) && resp.courses.length > 0) {
                resp.courses.forEach(function(course) {
                    html += '<option value="'+course.id+'">'+course.name+'</option>';
                });
                $('#course_id').html(html).prop('disabled', false).trigger('change');
            } else {
                $('#course_id').html(html).prop('disabled', false).trigger('change');
            }
            updateStudents();
            updateEvaluations();
        },
        error: function() {
            $('#course_id').html('<option value="">Error al cargar</option>').prop('disabled', true).trigger('change');
        }
    });
}

// Función para actualizar estudiantes
function updateStudents() {
    var grado = $('#grado').val();
    var seccion = $('#seccion').val();
    var nivel = $('#level').val();
    var msgDiv = $('#no-students-msg');
    
    if (!grado || !seccion || !nivel) {
        $('#student_id').html('<option value="">Todos</option>');
        if (msgDiv.length) msgDiv.remove();
        return;
    }
    
    $.ajax({
        url: 'ajax.php?action=get_students_by_grado_seccion',
        method: 'POST',
        data: { grado: grado, seccion: seccion, level: nivel },
        dataType: 'json',
        success: function(resp) {
            var html = '<option value="">Todos</option>';
            if (resp && Array.isArray(resp.students) && resp.students.length > 0) {
                resp.students.forEach(function(stu) {
                    html += '<option value="'+stu.id+'">'+stu.name+' ('+stu.id_no+')</option>';
                });
                $('#student_id').html(html).trigger('change');
                if (msgDiv.length) msgDiv.remove();
                actualizarEstadoBotonPromedio(); // Actualizar estado del botón promedio
            } else {
                $('#student_id').html(html).trigger('change');
                if (msgDiv.length) msgDiv.remove();
                $('#student_id').after('<div id="no-students-msg" style="color:#b00;font-size:0.95em;margin-top:4px;">'+(resp.message || 'No hay alumnos para el grado, sección y nivel seleccionados.')+'</div>');
                actualizarEstadoBotonPromedio(); // Actualizar estado del botón promedio
            }
        }
    });
}

// Función para actualizar evaluaciones
function updateEvaluations() {
    var course_id = $('#course_id').val();
    var grado = $('#grado').val();
    var seccion = $('#seccion').val();
    var bimestre = $('#bimestre').val();
    var nivel = $('#level').val();
    
    if (!nivel || !grado || !seccion) {
        $('#evaluation_id').html('<option value="">Todas</option>');
        return;
    }
    
    $.ajax({
        url: 'ajax.php?action=get_evaluations_by_filters',
        method: 'POST',
        data: { course_id: course_id, grado: grado, seccion: seccion, bimestre: bimestre, level: nivel },
        dataType: 'json',
        success: function(resp) {
            var html = '<option value="">Todas</option>';
            if (resp && Array.isArray(resp.evaluations) && resp.evaluations.length > 0) {
                resp.evaluations.forEach(function(ev) {
                    html += '<option value="'+ev.id+'">'+ev.title+'</option>';
                });
            }
            $('#evaluation_id').html(html).trigger('change');
        }
    });
}

// Eventos de cambio para cada filtro
$('#level').on('change', function() {
    updateGrados();
    // Reiniciar los demás filtros
    $('#seccion').html('<option value="">Seleccionar</option>').prop('disabled', true);
    $('#course_id').html('<option value="">Todos</option>').prop('disabled', true);
    $('#student_id').html('<option value="">Todos</option>');
    $('#evaluation_id').html('<option value="">Todas</option>');
});

$('#grado').on('change', function() {
    updateSecciones();
    // Reiniciar los demás filtros
    $('#course_id').html('<option value="">Todos</option>').prop('disabled', true);
    $('#student_id').html('<option value="">Todos</option>');
    $('#evaluation_id').html('<option value="">Todas</option>');
});

$('#seccion').on('change', function() {
    updateCursos();
    // Las funciones updateStudents y updateEvaluations se llaman dentro de updateCursos
});

$('#course_id, #bimestre').on('change', function() {
    updateEvaluations();
    if ($(this).attr('id') === 'bimestre') {
        actualizarEstadoBotonPromedio(); // Actualizar estado del botón cuando cambia el bimestre
    }
});

// Evento específico para cuando cambia el bimestre
$('#bimestre').on('change', function() {
    // Si se ha seleccionado un bimestre, ocultar la notificación con animación
    if ($(this).val()) {
        $('#bimestre-notification')
            .removeClass('animate__fadeIn')
            .addClass('animate__fadeOut');
        setTimeout(function() {
            $('#bimestre-notification').slideUp(300);
        }, 300);
        
        // También quitar el resaltado del bimestre
        $('#bimestre').removeClass('bimestre-highlight');
        $('#bimestre').next('.select2-container').find('.select2-selection').removeClass('bimestre-highlight');
    }
});

// Evento cuando cambia el estudiante seleccionado
$('#student_id').on('change', function() {
    actualizarEstadoBotonPromedio(); // Actualizar estado del botón cuando cambia el estudiante
});

$('#filter-form').submit(function(e) {
    e.preventDefault();
    
    // Efecto de pulsación en el botón Buscar
    $(this).find('button[type="submit"]').addClass('pulse-animation');
    setTimeout(() => {
        $(this).find('button[type="submit"]').removeClass('pulse-animation');
    }, 300);
    
    start_load();
    
    // Destroy any existing DataTable instance before making the AJAX request
    if ($.fn.dataTable.isDataTable('#gradesReportData')) {
        $('#gradesReportData').DataTable().destroy();
        console.log("DataTable destruido antes de la nueva búsqueda");
    }
    
    $.ajax({
        url: 'grades_report_table.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(resp) {
            try {
                // Clear previous content first
                $('#grades-report-table').empty().html(resp);
                
                // DataTables should initialize automatically via the script in grades_report_table.php
                // This is just a fallback in case that fails
                setTimeout(function() {
                    if ($('#grades-report-table .dataTables_wrapper').length === 0 && 
                        $('#gradesReportData tbody tr').length > 0 && 
                        $('#gradesReportData tbody tr:first td').length > 1 && 
                        !$('#gradesReportData tbody tr.no-data-row').length) {                        console.log("DataTables no se inicializó automáticamente, intentándolo manualmente...");
                        try {
                            // Double-check that it's still not initialized
                            if (!$.fn.dataTable.isDataTable('#gradesReportData')) {                            $('#gradesReportData').DataTable({
                                retrieve: true, // Evita errores de reinicialización
                                responsive: true,
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                                },
                                pageLength: 10,
                                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
                                });
                                console.log("DataTable inicializado manualmente con éxito");
                            } else {
                                console.log("DataTable ya estaba inicializado");
                            }
                        } catch(e) {
                            console.error("Error en la inicialización manual de DataTable:", e);
                        }
                    }
                }, 100);
            } catch(e) {
                console.error("Error procesando respuesta:", e);
            }
            end_load();
        },
        error: function() {
            alert_toast("Error al cargar el reporte.", 'danger');
            end_load();
        }
    });
});
$('#print-report').on('click', function() {
    // Efecto de pulsación en el botón Imprimir
    $(this).addClass('pulse-animation');
    setTimeout(() => {
        $(this).removeClass('pulse-animation');
    }, 300);
    
    var tables = $('#grades-report-table table');
    if (!tables.length) {
        alert_toast('No hay reporte para imprimir.', 'warning');
        return;
    }
    var allTablesHtml = '';
    tables.each(function() {
        var dataTable = $.fn.dataTable.isDataTable(this) ? $(this).DataTable() : null;
        var fullTable;
        if (dataTable) {
            var originalPageLength = dataTable.page.len();
            dataTable.page.len(-1).draw();
            fullTable = $(dataTable.table().node()).clone();
            fullTable.removeClass('dataTable no-footer').removeAttr('style');
            fullTable.find('thead th, tbody td').removeAttr('style');
            fullTable.find('tfoot').remove();
            dataTable.page.len(originalPageLength).draw();
        } else {
            fullTable = $(this).clone();
        }
        allTablesHtml += '<div style="margin-bottom:30px;">' + fullTable.prop('outerHTML') + '</div>';
    });
    var win = window.open('', '', 'width=900,height=700');
    win.document.write('<html><head><title>Reporte de Notas</title>');
    win.document.write('<style>body{font-family:sans-serif;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:6px;}th{background:#f5f5f5;}</style>');
    win.document.write('</head><body>');
    win.document.write('<h3 style="text-align:center;">Reporte de Notas</h3>');
    win.document.write(allTablesHtml);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function(){ win.print(); win.close(); }, 500);
});
// Inicializar tooltips para todos los elementos con data-toggle="tooltip"
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    
    // Añadir estilos adicionales para el botón deshabilitado
    $('<style>.btn-disabled { opacity: 0.85; }</style>').appendTo('head');
    
    // Inicializar estado del botón de promedio
    actualizarEstadoBotonPromedio();
});

// Función para actualizar el estado visual del botón "Ver Promedio" según las selecciones
function actualizarEstadoBotonPromedio() {
    var studentId = $('#student_id').val();
    var bimestre = $('#bimestre').val();
    var botonPromedio = $('#show-avg-report');
    var iconoInfo = botonPromedio.find('.bimestre-required');
    
    if (studentId && bimestre) {
        // Ambos valores están seleccionados, el botón está listo
        botonPromedio.removeClass('btn-disabled');
        iconoInfo.removeClass('fa-info-circle').addClass('fa-check-circle').css('color', '#ffffff');
        botonPromedio.attr('data-original-title', 'Listo para ver el promedio');
    } else {
        // Falta algún valor, el botón no está listo
        botonPromedio.addClass('btn-disabled');
        iconoInfo.removeClass('fa-check-circle').addClass('fa-info-circle').css('color', '#ffc107');
        
        if (!studentId && !bimestre) {
            botonPromedio.attr('data-original-title', 'Debe seleccionar un alumno y un bimestre específico');
        } else if (!studentId) {
            botonPromedio.attr('data-original-title', 'Debe seleccionar un alumno');
        } else {
            botonPromedio.attr('data-original-title', 'Debe seleccionar un bimestre específico');
        }
    }
}

// Aplicar efectos de hover para el botón de promedio
$('#show-avg-report').hover(
    function() {
        // Al hacer hover sobre el botón, resaltar el select de bimestre si no está seleccionado
        if (!$('#bimestre').val()) {
            $('#bimestre').addClass('bimestre-highlight');
            
            // También resaltar el contenedor Select2 si está activo
            $('#bimestre').next('.select2-container').find('.select2-selection').addClass('bimestre-highlight');
        }
    },
    function() {
        // Al quitar el hover, quitar el resaltado
        $('#bimestre').removeClass('bimestre-highlight');
        $('#bimestre').next('.select2-container').find('.select2-selection').removeClass('bimestre-highlight');
    }
);

$('#show-avg-report').on('click', function() {
    // Efecto de pulsación en el botón
    $(this).addClass('pulse-animation');
    setTimeout(() => {
        $(this).removeClass('pulse-animation');
    }, 300);

    var studentId = $('#student_id').val();
    var bimestre = $('#bimestre').val();
    
    if (!studentId) {
        alert_toast('Seleccione un alumno para ver el promedio.', 'warning');
        return;
    }
    
    if (!bimestre) {
        // Mostrar un mensaje más visible cuando no hay bimestre seleccionado
        Swal.fire({
            title: 'Bimestre requerido',
            html: '<div style="text-align:left; padding:15px 10px;">' +
                  '<p><i class="fa fa-exclamation-triangle text-warning" style="font-size: 18px; margin-right: 8px;"></i> Para calcular el promedio de un alumno, <b>es necesario seleccionar un bimestre específico</b>.</p>' +
                  '<p style="margin-top: 10px;">Los promedios se calculan por bimestre considerando los porcentajes de cada competencia.</p>' +
                  '</div>',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#4e73df',
            background: '#fff',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
          // Resaltar visualmente el select de bimestre
        $('#bimestre').addClass('bimestre-highlight');
        $('#bimestre').next('.select2-container').find('.select2-selection').addClass('bimestre-highlight');
        
        // Mostrar la notificación inline con animación
        $('#bimestre-notification')
            .slideDown(300)
            .removeClass('animate__fadeOut')
            .addClass('animate__fadeIn');
        
        // Hacer scroll hasta el select de bimestre
        $('html, body').animate({
            scrollTop: $('#bimestre').offset().top - 100
        }, 500);
        
        // Enfocar el select de bimestre
        setTimeout(function() {
            // Abrir el dropdown de Select2 con animación
            $('#bimestre').select2('open');
        }, 600);
        
        // Eliminar el resaltado después de unos segundos, pero mantenerlo si el dropdown sigue abierto
        setTimeout(function() {
            if (!$('.select2-container--open').length) {
                $('#bimestre').removeClass('bimestre-highlight');
                $('#bimestre').next('.select2-container').find('.select2-selection').removeClass('bimestre-highlight');
            }
        }, 5000);
        
        return;
    }
    
    // Si hay bimestre seleccionado, ocultar la notificación si estaba visible
    if ($('#bimestre-notification').is(':visible')) {
        $('#bimestre-notification')
            .removeClass('animate__fadeIn')
            .addClass('animate__fadeOut');
        setTimeout(function() {
            $('#bimestre-notification').slideUp(300);
        }, 300);
    }
    
    // Mostrar indicador de carga con animación
    start_load(); // Show loading indicator
    
    var course_id = $('#course_id').val();
    var grado = $('#grado').val();
    var seccion = $('#seccion').val();
    var bimestre = $('#bimestre').val();
    var evaluation_id = $('#evaluation_id').val();
    var nivel = $('#level').val();
    
    $.ajax({
        url: 'grades_report_table.php',
        method: 'POST',
        data: {
            course_id: course_id,
            grado: grado,
            seccion: seccion,
            bimestre: bimestre,
            student_id: studentId,
            evaluation_id: evaluation_id,
            level: nivel,
            show_avg: 1
        },
        success: function(resp) {
            try {
                if (resp.indexOf('alert-danger') > -1) {
                    // There was an error in the response
                    alert_toast('Error al calcular el promedio: ' + $(resp).text(), 'danger');
                    end_load();
                    return;
                }
                
                var win = window.open('', '', 'width=700,height=600');
                if (!win) {
                    alert_toast('Por favor, permita las ventanas emergentes para ver el promedio.', 'warning');
                    end_load();
                    return;
                }
                
                win.document.write('<html><head><title>Promedio Final</title>');
                win.document.write('<style>body{font-family:sans-serif;padding:20px;}table{width:100%;border-collapse:collapse;margin-top:20px;}th,td{border:1px solid #ccc;padding:6px;}th{background:#f5f5f5;}</style>');
                win.document.write('</head><body>');
                win.document.write('<h3 style="text-align:center;">Promedio Final del Alumno</h3>');
                win.document.write(resp);
                win.document.write('</body></html>');
                win.document.close();
                win.focus();
            } catch(e) {
                console.error("Error al procesar promedio:", e);
                alert_toast('Error al mostrar el promedio.', 'danger');
            }
            end_load(); // Hide loading indicator
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            alert_toast('Error al calcular el promedio: ' + error, 'danger');
            end_load(); // Hide loading indicator
        }
    });
});

// Evento para el botón de la notificación de bimestre
$('#goto-bimestre').on('click', function(e) {
    e.preventDefault();
    
    // Enfocar el select de bimestre
    $('html, body').animate({
        scrollTop: $('#bimestre').offset().top - 100
    }, 500);
    
    // Resaltar el select y abrirlo
    $('#bimestre').addClass('bimestre-highlight');
    $('#bimestre').next('.select2-container').find('.select2-selection').addClass('bimestre-highlight');
    
    setTimeout(function() {
        $('#bimestre').select2('open');
    }, 600);
});
</script>
