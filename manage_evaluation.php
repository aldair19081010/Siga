<?php
include 'db_connect.php';
session_start();
$is_teacher = (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 2);
$teacher_id = $_SESSION['login_teacher_id'] ?? null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$title = $description = $type = '';
$selected_course_id = '';
$selected_grado = '';
$selected_seccion = '';
$selected_teacher_course_id = '';
$bimestre = '';

if ($id) {
	$q = $conn->query("SELECT * FROM evaluations WHERE id = $id");
	if ($q && $q->num_rows) {
		$row = $q->fetch_assoc();
		$title = $row['title'];
		$description = $row['description'];
		$type = $row['type'];
		$selected_teacher_course_id = $row['teacher_course_id'];
		$bimestre = $row['bimestre'] ?? '';
		
		// Obtener detalles del curso relacionado
		if ($selected_teacher_course_id) {
			$tc_query = $conn->query("SELECT tc.*, ac.id as course_id, ac.name as course_name, ac.level 
                                   FROM teacher_courses tc 
                                   INNER JOIN academic_courses ac ON tc.course_id = ac.id 
                                   WHERE tc.id = $selected_teacher_course_id");
			if ($tc_query && $tc_query->num_rows) {
				$tc_row = $tc_query->fetch_assoc();
				$selected_course_id = $tc_row['course_id'];
				$selected_course_name = $tc_row['course_name'];
				$selected_level = $tc_row['level'];
				$selected_grado = $tc_row['grado'];
				$selected_seccion = $tc_row['seccion'];
			}
		}
	}
}
// Cargar cursos, grados y secciones asignados al docente
$cursos = [];
$curso_grados = [];
$curso_grado_secciones = []; // Array para almacenar las secciones por curso, nivel y grado
$niveles_por_curso = [];

// Agrupar cursos por nombre
$cursos_por_nombre = [];
$niveles_por_nombre = [];
$grados_por_nombre_nivel = [];
$secciones_por_nombre_nivel_grado = [];

if ($is_teacher && $teacher_id) {
	$q = $conn->query("SELECT ac.id, ac.name, ac.level, tc.grado, tc.seccion
		FROM teacher_courses tc
		INNER JOIN academic_courses ac ON ac.id = tc.course_id
		WHERE tc.teacher_id = $teacher_id
		ORDER BY ac.name, ac.level, tc.grado, tc.seccion");
	while ($row = $q->fetch_assoc()) {
		$curso_id = $row['id'];
		$curso_nombre = $row['name'];
		$nivel = $row['level'];
		$grado = $row['grado'];
		$seccion = $row['seccion'] ?? 'U';
		// Agrupar IDs por nombre
		if (!isset($cursos_por_nombre[$curso_nombre])) $cursos_por_nombre[$curso_nombre] = [];
		if (!in_array($curso_id, $cursos_por_nombre[$curso_nombre])) $cursos_por_nombre[$curso_nombre][] = $curso_id;
		// Niveles por nombre
		if (!isset($niveles_por_nombre[$curso_nombre])) $niveles_por_nombre[$curso_nombre] = [];
		if (!in_array($nivel, $niveles_por_nombre[$curso_nombre])) $niveles_por_nombre[$curso_nombre][] = $nivel;
		// Grados por nombre y nivel
		if (!isset($grados_por_nombre_nivel[$curso_nombre])) $grados_por_nombre_nivel[$curso_nombre] = [];
		if (!isset($grados_por_nombre_nivel[$curso_nombre][$nivel])) $grados_por_nombre_nivel[$curso_nombre][$nivel] = [];
		if (!in_array($grado, $grados_por_nombre_nivel[$curso_nombre][$nivel])) $grados_por_nombre_nivel[$curso_nombre][$nivel][] = $grado;
		// Secciones por nombre, nivel y grado
		if (!isset($secciones_por_nombre_nivel_grado[$curso_nombre])) $secciones_por_nombre_nivel_grado[$curso_nombre] = [];
		if (!isset($secciones_por_nombre_nivel_grado[$curso_nombre][$nivel])) $secciones_por_nombre_nivel_grado[$curso_nombre][$nivel] = [];
		if (!isset($secciones_por_nombre_nivel_grado[$curso_nombre][$nivel][$grado])) $secciones_por_nombre_nivel_grado[$curso_nombre][$nivel][$grado] = [];
		if (!in_array($seccion, $secciones_por_nombre_nivel_grado[$curso_nombre][$nivel][$grado])) $secciones_por_nombre_nivel_grado[$curso_nombre][$nivel][$grado][] = $seccion;
	}
}

// Consultar competencias globales del docente
$competencias = [];
if ($is_teacher && $teacher_id) {
	$q_comp = $conn->query("SELECT * FROM competencias WHERE teacher_id = $teacher_id ORDER BY name ASC");
	while ($row = $q_comp->fetch_assoc()) {
		$competencias[] = $row;
	}
}
// Si se está editando, cargar competencias ya asociadas
$selected_competencias = [];
if ($id) {
	$q_sel = $conn->query("SELECT competencia_id FROM evaluation_competencias WHERE evaluation_id = $id");
	while ($row = $q_sel->fetch_assoc()) {
		$selected_competencias[] = $row['competencia_id'];
	}
}
?>
<style>
	.evaluation-form-card {
		max-width: 600px;
		margin: 20px auto 0 auto;
		background: #fff;
		border-radius: 10px;
		box-shadow: 0 3px 15px rgba(0,0,0,0.08);
		padding: 25px 30px 20px 30px;
		transition: all 0.3s ease;
	}
	.evaluation-form-card:hover {
		box-shadow: 0 5px 20px rgba(0,0,0,0.12);
	}
	.evaluation-form-card label {
		font-weight: 500;
		color: #2c4964;
		margin-bottom: 5px;
		font-size: 0.9rem;
	}
	.evaluation-form-card .form-group {
		margin-bottom: 20px;
	}
	.evaluation-form-card .form-control {
		border-radius: 5px;
		border: 1px solid #e0e0e0;
		transition: all 0.3s ease;
		padding: 8px 12px;
		height: auto;
	}
	.evaluation-form-card .form-control:focus {
		border-color: #fc7d1c;
		box-shadow: 0 0 0 0.2rem rgba(252, 125, 28, 0.25);
	}
	.evaluation-form-card .select2-container--default .select2-selection--single {
		height: 38px;
		padding: 6px 12px;
		border-radius: 5px;
		border: 1px solid #e0e0e0;
		transition: all 0.3s ease;
	}
	.evaluation-form-card .select2-container--default.select2-container--focus .select2-selection--single,
	.evaluation-form-card .select2-container--default.select2-container--open .select2-selection--single {
		border-color: #fc7d1c;
		box-shadow: 0 0 0 0.2rem rgba(252, 125, 28, 0.25);
	}
	.evaluation-form-card .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 24px;
		color: #495057;
	}
	.evaluation-form-card .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 38px;
	}
	.evaluation-form-card .select2-container--default .select2-results__option--highlighted[aria-selected] {
		background-color: #fc7d1c;
	}
	.evaluation-form-card button[type="submit"] {
		min-width: 160px;
		padding: 10px 20px;
		border-radius: 5px;
		font-weight: 500;
		transition: all 0.3s ease;
		background-color: #fc7d1c;
		border-color: #fc7d1c;
	}
	.evaluation-form-card button[type="submit"]:hover {
		background-color: #e56c10;
		border-color: #e56c10;
		transform: translateY(-2px);
		box-shadow: 0 4px 8px rgba(252, 125, 28, 0.3);
	}
	.evaluation-form-card .form-header {
		margin-bottom: 20px;
		padding-bottom: 15px;
		border-bottom: 1px solid #f0f0f0;
	}
	.evaluation-form-card .form-header h4 {
		margin: 0;
		font-weight: 600;
		color: #2c4964;
		font-size: 1.2rem;
	}
	.evaluation-form-card .form-footer {
		margin-top: 25px;
		padding-top: 15px;
		border-top: 1px solid #f0f0f0;
		display: flex;
		justify-content: flex-end;
	}
	.evaluation-form-card .form-check-label {
		font-weight: normal;
	}
	@media (max-width: 700px) {
		.evaluation-form-card {
			padding: 18px 15px 15px 15px;
			margin-top: 10px;
		}
		.evaluation-form-card button[type="submit"] {
			width: 100%;
		}
	}
</style>
<div class="container-fluid">
	<div class="evaluation-form-card">
		<form id="manage-evaluation">
			<div class="form-header">
				<h4><i class="fa fa-clipboard-list mr-2"></i><?php echo $id ? 'Editar Evaluación' : 'Nueva Evaluación' ?></h4>
			</div>
			<input type="hidden" name="id" value="<?php echo $id ?>">
			
			<div class="row">
				<div class="col-md-8">
					<div class="form-group">
						<label for="title"><i class="fa fa-pen-fancy mr-1"></i> Título de la Evaluación</label>
						<input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($title) ?>" placeholder="Ej: Examen de Mitad de Curso" required>					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="type"><i class="fa fa-tags mr-1"></i> Tipo</label>
						<select name="type" id="type" class="form-control select2" required>
							<option value="">Seleccione un tipo</option>
							<option value="Examen" <?php echo ($type == 'Examen') ? 'selected' : '' ?>>Examen</option>
							<option value="Examen Parcial" <?php echo ($type == 'Examen Parcial') ? 'selected' : '' ?>>Examen Parcial</option>
							<option value="Examen Final" <?php echo ($type == 'Examen Final') ? 'selected' : '' ?>>Examen Final</option>
							<option value="Quiz" <?php echo ($type == 'Quiz') ? 'selected' : '' ?>>Quiz</option>
							<option value="Tarea" <?php echo ($type == 'Tarea') ? 'selected' : '' ?>>Tarea</option>
							<option value="Proyecto" <?php echo ($type == 'Proyecto') ? 'selected' : '' ?>>Proyecto</option>
							<option value="Exposición" <?php echo ($type == 'Exposición') ? 'selected' : '' ?>>Exposición</option>
							<option value="Trabajo en clase" <?php echo ($type == 'Trabajo en clase') ? 'selected' : '' ?>>Trabajo en clase</option>
							<option value="Participación" <?php echo ($type == 'Participación') ? 'selected' : '' ?>>Participación</option>
							<option value="Práctica de laboratorio" <?php echo ($type == 'Práctica de laboratorio') ? 'selected' : '' ?>>Práctica de laboratorio</option>
							<option value="Informe" <?php echo ($type == 'Informe') ? 'selected' : '' ?>>Informe</option>
							<option value="Ensayo" <?php echo ($type == 'Ensayo') ? 'selected' : '' ?>>Ensayo</option>
							<option value="Debate" <?php echo ($type == 'Debate') ? 'selected' : '' ?>>Debate</option>
							<option value="Otro" <?php echo ($type == 'Otro') ? 'selected' : '' ?>>Otro</option>
						</select>
					</div>
				</div>
			</div>
			
			<!-- Textarea para descripción -->
			<div class="form-group">
				<label for="description"><i class="fa fa-align-left mr-1"></i> Tema o Descripción</label>
				<textarea name="description" id="description" class="form-control" rows="3" placeholder="Describe el contenido de esta evaluación" required><?php echo htmlspecialchars($description) ?></textarea>
			</div>

			<!-- Sección de información del curso -->
			<div class="card mt-3 mb-3">
				<div class="card-header bg-light">
					<i class="fa fa-book mr-1"></i> Información del Curso
				</div>
				<div class="card-body">
					<?php if ($is_teacher && $teacher_id && !empty($cursos_por_nombre)): ?>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="course_name"><i class="fa fa-chalkboard mr-1"></i> Curso a Evaluar</label>
				<select name="course_name" id="course_name" class="form-control select2" required>
									<option value="">Seleccione un curso</option>
									<?php foreach (array_keys($cursos_por_nombre) as $cname): ?>
									<option value="<?php echo htmlspecialchars($cname); ?>" <?php echo ($selected_course_id && isset($cursos[$selected_course_id]) && $cursos[$selected_course_id] == $cname) ? 'selected' : '' ?>>
										<?php echo htmlspecialchars($cname); ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="level"><i class="fa fa-layer-group mr-1"></i> Nivel</label>
								<select name="level" id="level" class="form-control select2" required>
									<option value="">Seleccione un nivel</option>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="grado"><i class="fa fa-sort-numeric-up mr-1"></i> Grado</label>
								<select name="grado" id="grado" class="form-control select2" required>
									<option value="">Seleccione un grado</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="seccion"><i class="fa fa-users mr-1"></i> Sección</label>
								<select name="seccion" id="seccion" class="form-control select2" required>
									<option value="">Seleccione una sección</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Sección de configuración de la evaluación -->
			<div class="card mt-3 mb-3">
				<div class="card-header bg-light">
					<i class="fa fa-cog mr-1"></i> Configuración de Evaluación
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="bimestre"><i class="fa fa-calendar-alt mr-1"></i> Bimestre</label>
								<select name="bimestre" id="bimestre" class="form-control select2" required>
									<option value="">Seleccione un bimestre</option>
									<option value="1" <?php echo ($bimestre == '1') ? 'selected' : '' ?>>1° Bimestre</option>
									<option value="2" <?php echo ($bimestre == '2') ? 'selected' : '' ?>>2° Bimestre</option>
									<option value="3" <?php echo ($bimestre == '3') ? 'selected' : '' ?>>3° Bimestre</option>
									<option value="4" <?php echo ($bimestre == '4') ? 'selected' : '' ?>>4° Bimestre</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="competencias"><i class="fa fa-award mr-1"></i> Competencias a Evaluar</label>
								<select name="competencias[]" id="competencias" class="form-control select2" multiple required>
									<?php foreach ($competencias as $comp): ?>
									<option value="<?php echo $comp['id']; ?>" <?php echo in_array($comp['id'], $selected_competencias) ? 'selected' : '' ?>>
										<?php echo htmlspecialchars($comp['name']) . " (" . $comp['percentage'] . "%)"; ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<input type="hidden" name="teacher_id" value="<?php echo $teacher_id ?>">
			<input type="hidden" name="teacher_course_id" id="teacher_course_id_hidden" value="<?php echo htmlspecialchars($selected_teacher_course_id); ?>">
			<?php endif; ?>
			
			<!-- Botón de guardar con mejor estilo -->
			<div class="form-footer">
				<button type="submit" class="btn btn-primary">
					<i class="fa fa-save mr-1"></i> Guardar Evaluación
				</button>
			</div>
		</form>
	</div>
</div>
<script>
// Inicializar Select2 con mejores opciones visuales
$('.select2').select2({ 
    width: '100%',
    theme: 'classic',
    placeholder: 'Seleccione una opción',
    allowClear: true
});

// Añadir animaciones de entrada a las tarjetas
$(document).ready(function() {
    // Animación de las tarjetas del formulario
    $('.card').css('opacity', 0).css('transform', 'translateY(20px)');
    
    setTimeout(function() {
        $('.card').animate({
            opacity: 1,
            transform: 'translateY(0)'
        }, 500);
    }, 100);
    
    // Añadir efecto hover a los select
    $('.select2-container').hover(
        function() { $(this).addClass('select2-hover'); },
        function() { $(this).removeClass('select2-hover'); }
    );
});

<?php if ($is_teacher && $teacher_id && !empty($cursos_por_nombre)): ?>
// Datos para la cascada de selección
var nivelesPorNombre = <?php echo json_encode($niveles_por_nombre); ?>;
var gradosPorNombreNivel = <?php echo json_encode($grados_por_nombre_nivel); ?>;
var seccionesPorNombreNivelGrado = <?php echo json_encode($secciones_por_nombre_nivel_grado); ?>;

// Mapa para buscar el teacher_course_id según selección
var teacherCourseMap = {};
<?php
$teacher_course_map = [];
if ($is_teacher && $teacher_id) {
	$q = $conn->query("SELECT tc.id as tcid, ac.name, ac.level, tc.grado, tc.seccion
		FROM teacher_courses tc
		INNER JOIN academic_courses ac ON ac.id = tc.course_id
		WHERE tc.teacher_id = $teacher_id");
	while ($row = $q->fetch_assoc()) {
		$teacher_course_map[$row['name']][$row['level']][$row['grado']][$row['seccion'] ?? 'U'] = $row['tcid'];
	}
}
?>
teacherCourseMap = <?php echo json_encode($teacher_course_map); ?>;

// Función mejorada para actualizar niveles con animación
function updateNiveles() {
    var cname = $('#course_name').val();
    var $nivel = $('#level');
    
    // Efecto de transición
    $nivel.prop('disabled', true).css('opacity', 0.6);
    
    setTimeout(function() {
        $nivel.html('<option value="">Seleccione un nivel</option>');
        if (cname && nivelesPorNombre[cname]) {
            nivelesPorNombre[cname].forEach(function(n) {
                $nivel.append('<option value="'+n+'">'+n+'</option>');
            });
        }
        $nivel.val('').prop('disabled', false).css('opacity', 1).trigger('change.select2');
    }, 200);
}

// Función mejorada para actualizar grados con animación
function updateGrados() {
    var cname = $('#course_name').val();
    var nivel = $('#level').val();
    var $grado = $('#grado');
    
    // Efecto de transición
    $grado.prop('disabled', true).css('opacity', 0.6);
    
    setTimeout(function() {
        $grado.html('<option value="">Seleccione un grado</option>');
        if (cname && nivel && gradosPorNombreNivel[cname] && gradosPorNombreNivel[cname][nivel]) {
            gradosPorNombreNivel[cname][nivel].forEach(function(g) {
                $grado.append('<option value="'+g+'">'+g+'</option>');
            });
        }
        $grado.val('').prop('disabled', false).css('opacity', 1).trigger('change.select2');
    }, 200);
}

// Función mejorada para actualizar secciones con animación
function updateSecciones() {
    var cname = $('#course_name').val();
    var nivel = $('#level').val();
    var grado = $('#grado').val();
    var $seccion = $('#seccion');
    
    // Efecto de transición
    $seccion.prop('disabled', true).css('opacity', 0.6);
    
    setTimeout(function() {
        $seccion.html('<option value="">Seleccione una sección</option>');
        if (cname && nivel && grado && seccionesPorNombreNivelGrado[cname] && 
            seccionesPorNombreNivelGrado[cname][nivel] && 
            seccionesPorNombreNivelGrado[cname][nivel][grado]) {
            seccionesPorNombreNivelGrado[cname][nivel][grado].forEach(function(s) {
                $seccion.append('<option value="'+s+'">'+s+'</option>');
            });
        }
        $seccion.val('').prop('disabled', false).css('opacity', 1).trigger('change.select2');
    }, 200);
}

// Actualizar el ID oculto del curso del profesor
function updateTeacherCourseIdHidden() {
    var cname = $('#course_name').val();
    var nivel = $('#level').val();
    var grado = $('#grado').val();
    var seccion = $('#seccion').val();
    var tcid = '';
    
    if (cname && nivel && grado && seccion && teacherCourseMap[cname] && 
        teacherCourseMap[cname][nivel] && 
        teacherCourseMap[cname][nivel][grado] && 
        teacherCourseMap[cname][nivel][grado][seccion]) {
        tcid = teacherCourseMap[cname][nivel][grado][seccion];
    }
    
    $('#teacher_course_id_hidden').val(tcid);
}

// Configurar eventos para los selectores en cascada
$('#course_name').on('change', function() {
    updateNiveles();
    $('#grado').html('<option value="">Seleccione un grado</option>').val('').prop('disabled', true);
    $('#seccion').html('<option value="">Seleccione una sección</option>').val('').prop('disabled', true);
});

$('#level').on('change', function() {
    updateGrados();
    $('#seccion').html('<option value="">Seleccione una sección</option>').val('').prop('disabled', true);
});

$('#grado').on('change', function() {
    updateSecciones();
});

// Actualizar ID oculto cuando cambia cualquier selector
$('#course_name, #level, #grado, #seccion').on('change', function() {
    updateTeacherCourseIdHidden();
});

// Inicialización mejorada si hay valores seleccionados previamente
$(document).ready(function() {
    // Valores preseleccionados para edición
    var selectedCourseName = '<?php echo isset($selected_course_name) ? $selected_course_name : ''; ?>';
    var selectedLevel = '<?php echo isset($selected_level) ? $selected_level : ''; ?>';
    var selectedGrado = '<?php echo $selected_grado; ?>';
    var selectedSeccion = '<?php echo $selected_seccion; ?>';
    
    if (selectedCourseName) {
        // Mostrar un indicador de carga mientras se inicializan los selectores
        if ($('#course_name').length) {
            var loadingHtml = '<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.7);display:flex;align-items:center;justify-content:center;z-index:10"><i class="fa fa-spinner fa-spin"></i> Cargando datos...</div>';
            $('.card').eq(0).css('position', 'relative').append(loadingHtml);
            
            // Configurar los valores en cascada con temporizadores para asegurar la secuencia correcta
            $('#course_name').val(selectedCourseName).trigger('change');
            setTimeout(function() {
                if (selectedLevel) {
                    $('#level').val(selectedLevel).trigger('change');
                    setTimeout(function() {
                        if (selectedGrado) {
                            $('#grado').val(selectedGrado).trigger('change');
                            setTimeout(function() {
                                if (selectedSeccion) {
                                    $('#seccion').val(selectedSeccion).trigger('change');
                                }
                                updateTeacherCourseIdHidden();
                                // Eliminar la capa de carga
                                $('.loading-overlay').fadeOut(300, function() { $(this).remove(); });
                            }, 300);
                        } else {
                            $('.loading-overlay').fadeOut(300, function() { $(this).remove(); });
                        }
                    }, 300);
                } else {
                    $('.loading-overlay').fadeOut(300, function() { $(this).remove(); });
                }
            }, 500);
        }
    }
    
    // Animar cards de formulario al cargar
    $('.card').each(function(index) {
        var $card = $(this);
        setTimeout(function() {
            $card.css({
                'transform': 'translateY(0)',
                'opacity': 1,
                'transition': 'all 0.5s ease'
            });
        }, index * 150);
    });
});
<?php endif; ?>

// Envío del formulario con validación mejorada y notificación
$('#manage-evaluation').submit(function(e) {
    e.preventDefault();
    
    // Validación del formulario
    var teacherCourseId = $('#teacher_course_id_hidden').val();
    var title = $('#title').val().trim();
    var type = $('#type').val();
    var description = $('#description').val().trim();
    
    // Validar campos
    if (!title) {
        alert_toast('El título de la evaluación es obligatorio', 'warning');
        $('#title').focus();
        return;
    }
    
    if (!type) {
        alert_toast('Debe seleccionar un tipo de evaluación', 'warning');
        $('#type').focus();
        return;
    }
    
    if (!description) {
        alert_toast('La descripción o tema es obligatorio', 'warning');
        $('#description').focus();
        return;
    }
    
    if (!teacherCourseId) {
        alert_toast('Debe seleccionar curso, nivel, grado y sección válidos', 'warning');
        $('#course_name').focus();
        return;
    }
    
    // Animación de envío
    $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Guardando...');
    
    start_load();
    $.ajax({
        url: 'ajax.php?action=save_evaluation',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(resp) {
            if (resp.status == 1) {
                alert_toast(resp.message, 'success');
                // Mostrar animación de éxito
                $('.evaluation-form-card').addClass('animate__animated animate__bounceOut');
                setTimeout(function() { location.reload(); }, 800);
            } else {
                alert_toast(resp.message || "Error al guardar la evaluación.", 'danger');
                $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Guardar Evaluación');
                end_load();
            }
        },
        error: function() {
            alert_toast("Error en el servidor.", 'danger');
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Guardar Evaluación');
            end_load();
        }
    });
});
</script>
