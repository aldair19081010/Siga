<?php
include 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$school_id = $_SESSION['login_school_id'] ?? null;
$login_type = $_SESSION['login_type'] ?? null; // 1: Admin, 2: Teacher
$teacher_id = $_SESSION['login_teacher_id'] ?? null;

if (!$school_id) {
    die("<div class=\"alert alert-danger\">Error: ID de colegio no configurado.</div>");
}
if (!$login_type) {
    die("<div class=\"alert alert-danger\">Error: Tipo de usuario no definido.</div>");
}

function normalize_level_for_key($level_name) {
    if (empty($level_name)) return null;
    return strtolower(str_replace(' ', '', trim($level_name)));
}

// Get filter parameters
$course_id_filter = $_POST['course_id'] ?? null;
$level_filter = $_POST['level'] ?? null;
$grado_filter = $_POST['grado'] ?? null;
$seccion_filter = $_POST['seccion'] ?? null;
$student_id_filter = $_POST['student_id'] ?? null;
$evaluation_id_filter = $_POST['evaluation_id'] ?? null;
$bimestre_filter = $_POST['bimestre'] ?? null;
$show_avg = isset($_POST['show_avg']) && $_POST['show_avg'] == '1';

// Normalizar el nivel para asegurar consistencia en las comparaciones
$normalized_level_key = normalize_level_for_key($level_filter);

if ($show_avg && !empty($student_id_filter)) {    // Verificar si hay un bimestre seleccionado
    if (empty($bimestre_filter)) {        // Si no hay bimestre seleccionado, mostrar mensaje de error estilizado con animación
        echo '<div style="max-width: 700px; margin: 30px auto; padding: 25px 30px; background-color: #fff8e1; border-left: 5px solid #ff9800; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); animation: fadeInUp 0.4s ease-out;">
            <div style="display: flex; align-items: center; margin-bottom: 18px;">
                <div style="background: linear-gradient(135deg, #ff9800, #ffb74d); border-radius: 50%; width: 45px; height: 45px; display: flex; justify-content: center; align-items: center; margin-right: 15px; box-shadow: 0 3px 8px rgba(255,152,0,0.3);">
                    <i class="fa fa-exclamation-triangle" style="color: white; font-size: 22px; text-shadow: 0 1px 1px rgba(0,0,0,0.2);"></i>
                </div>
                <h4 style="margin: 0; color: #e65100; font-size: 20px; font-weight: 600;">Es necesario seleccionar un bimestre</h4>
            </div>
              <div style="background-color: #fffaf0; border: 2px dashed #ffb74d; padding: 15px; border-radius: 8px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fa fa-info-circle" style="color: #ff9800; font-size: 20px; margin-right: 10px;"></i>
                    <span style="font-weight: 600; color: #e65100;">¿Por qué es necesario un bimestre?</span>
                </div>
                <p style="color: #555; font-size: 14.5px; line-height: 1.7;">
                    Los promedios se calculan por bimestre considerando los porcentajes de cada competencia.
                    Sin un bimestre específico, no se pueden calcular correctamente los promedios de las competencias.
                </p>
                <div style="margin-top: 15px; background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <p style="font-weight: 500; margin: 0; color: #856404; font-size: 15px;">
                        <i class="fa fa-lightbulb-o" style="margin-right: 8px;"></i> Recuerde: Los porcentajes de competencias pueden variar entre bimestres.
                    </p>
                </div>
            </div>
              <div style="background-color: #f0f8ff; padding: 15px; border-radius: 8px; margin-bottom: 18px; border-left: 4px solid #007bff; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <div style="background: linear-gradient(135deg, #007bff, #0056b3); width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-right: 12px;">
                        <i class="fa fa-check" style="color: white; font-size: 18px;"></i>
                    </div>
                    <h5 style="margin: 0; color: #0056b3; font-size: 16px;">Solución:</h5>
                </div>
                <ol style="color: #333; font-size: 14.5px; line-height: 1.7; margin-bottom: 0; padding-left: 20px;">
                    <li style="margin-bottom: 8px;"><strong>Regrese</strong> a la pantalla anterior haciendo clic en el botón de abajo</li>
                    <li style="margin-bottom: 8px;"><strong>Seleccione</strong> un bimestre específico (1°, 2°, 3° o 4°) en la sección de filtros</li>
                    <li><strong>Haga clic</strong> nuevamente en "Ver Promedio"</li>
                </ol>
            </div>
              <div style="margin-top: 25px; text-align: center;">
                <a href="javascript:history.back()" class="btn" style="text-decoration: none; padding: 14px 28px; background: linear-gradient(135deg, #28a745, #218838); color: white; border-radius: 30px; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 12px rgba(40,167,69,0.4); font-size: 16px; display: inline-block; animation: bounce 2s infinite;">
                    <i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Volver y seleccionar un bimestre
                </a>
            </div>
            <style>
                @keyframes bounce {
                    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
                    40% {transform: translateY(-8px);}
                    60% {transform: translateY(-4px);}
                }
            </style>
        </div>
        <style>
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>';
        exit; // Finalizar la ejecución para no procesar más el cálculo del promedio
    }

    // --- "VER PROMEDIO" LOGIC ---
    $student_name_avg = "Alumno Desconocido";
    $stmt_student_name = $conn->prepare("SELECT name FROM student WHERE id = ? AND school_id = ?");
    if ($stmt_student_name) {
        $stmt_student_name->bind_param("ii", $student_id_filter, $school_id);
        $stmt_student_name->execute();
        $result_student_name = $stmt_student_name->get_result();
        if ($row_student_name = $result_student_name->fetch_assoc()) {
            $student_name_avg = htmlspecialchars($row_student_name['name']);
        }
        $stmt_student_name->close();
    }

    $course_name_for_avg_display = "Todos los aplicables";
    if (!empty($course_id_filter)) {
        $stmt_course_name = $conn->prepare("SELECT name FROM academic_courses WHERE id = ? AND school_id = ?");
        if ($stmt_course_name) {
            $stmt_course_name->bind_param("ii", $course_id_filter, $school_id);
            $stmt_course_name->execute();
            $result_course_name = $stmt_course_name->get_result();
            if ($row_course_name = $result_course_name->fetch_assoc()) {
                $course_name_for_avg_display = htmlspecialchars($row_course_name['name']);
            }
            $stmt_course_name->close();
        }
    }
      // Obtener datos de competencias y evaluaciones si hay un curso seleccionado
    $competencias_data = [];
    $final_average = 0;
    $has_competencias = false;
    $total_percentage = 0; // Para normalizar los porcentajes
    
    if (!empty($course_id_filter)) {        // Primero, obtener todas las competencias del bimestre seleccionado
        $sql_get_competencias = "SELECT DISTINCT c.id, c.name, c.percentage  
                               FROM competencias c
                               INNER JOIN evaluation_competencias ec ON c.id = ec.competencia_id
                               INNER JOIN evaluations e ON ec.evaluation_id = e.id 
                               INNER JOIN teacher_courses tc ON e.teacher_course_id = tc.id
                               WHERE tc.course_id = ?";
                               
        $params_get_comp = [$course_id_filter];
        $types_get_comp = "i";
        
        // Añadir filtro de bimestre si está presente
        if (!empty($bimestre_filter)) {
            $sql_get_competencias .= " AND e.bimestre = ?";
            $params_get_comp[] = $bimestre_filter;
            $types_get_comp .= "s";
        }
        
        // Añadir otros filtros relevantes
        if (!empty($grado_filter)) {
            $sql_get_competencias .= " AND tc.grado = ?";
            $params_get_comp[] = $grado_filter;
            $types_get_comp .= "s";
        }
        
        if (!empty($seccion_filter)) {
            $sql_get_competencias .= " AND tc.seccion = ?";
            $params_get_comp[] = $seccion_filter;
            $types_get_comp .= "s";
        }
        
        $stmt_get_comp = $conn->prepare($sql_get_competencias);
        $all_competencias = [];
        
        if ($stmt_get_comp) {
            $stmt_get_comp->bind_param($types_get_comp, ...$params_get_comp);
            $stmt_get_comp->execute();
            $result_get_comp = $stmt_get_comp->get_result();
            
            while ($row_get_comp = $result_get_comp->fetch_assoc()) {
                $all_competencias[$row_get_comp['id']] = [
                    'id' => $row_get_comp['id'],
                    'name' => $row_get_comp['name'],
                    'percentage' => $row_get_comp['percentage']
                ];
                $total_percentage += $row_get_comp['percentage'];
            }
            $stmt_get_comp->close();
        }
          // Ahora obtener las notas de cada competencia para el estudiante
        $sql_competencias = "SELECT c.id, c.name, c.percentage, 
                           e.id as evaluation_id, e.title as evaluation_title, e.bimestre,
                           eg.grade
                    FROM competencias c
                    INNER JOIN evaluation_competencias ec ON c.id = ec.competencia_id
                    INNER JOIN evaluations e ON ec.evaluation_id = e.id
                    INNER JOIN teacher_courses tc ON e.teacher_course_id = tc.id
                    INNER JOIN evaluation_grades eg ON e.id = eg.evaluation_id
                    INNER JOIN student s ON eg.student_id = s.id
                    WHERE tc.course_id = ? 
                    AND eg.student_id = ?
                    AND s.school_id = ?";                            
        
        // Añadir filtros adicionales
        $params_comp = [$course_id_filter, $student_id_filter, $school_id];
        $types_comp = "iii";
        
        if ($normalized_level_key !== null) {
            $sql_competencias .= " AND LOWER(REPLACE(TRIM(s.nivel), ' ', '')) = ?";
            $params_comp[] = $normalized_level_key;
            $types_comp .= "s";
        }
        
        if (!empty($grado_filter)) {
            $sql_competencias .= " AND tc.grado = ?";
            $params_comp[] = $grado_filter;
            $types_comp .= "s";
        }
        
        if (!empty($seccion_filter)) {
            $sql_competencias .= " AND tc.seccion = ?";
            $params_comp[] = $seccion_filter;
            $types_comp .= "s";
        }
        
        if (!empty($bimestre_filter)) {
            $sql_competencias .= " AND e.bimestre = ?";
            $params_comp[] = $bimestre_filter;
            $types_comp .= "s";
        }
        
        $sql_competencias .= " ORDER BY c.id, e.title";
        
        $stmt_comp = $conn->prepare($sql_competencias);
        
        if ($stmt_comp) {
            $stmt_comp->bind_param($types_comp, ...$params_comp);
            $stmt_comp->execute();
            $result_comp = $stmt_comp->get_result();
            
            // Inicializar la estructura para todas las competencias
            foreach ($all_competencias as $comp_id => $comp_info) {
                $competencias_data[$comp_id] = [
                    'name' => $comp_info['name'],
                    'percentage' => $comp_info['percentage'],
                    'evaluations' => [],
                    'avg_grade' => 0
                ];
            }
              // Llenar con datos de evaluaciones
            while ($row_comp = $result_comp->fetch_assoc()) {
                $comp_id = $row_comp['id'];
                
                // Si la competencia existe en nuestro mapeo, añadir evaluaciones
                // Solo se añaden evaluaciones del bimestre seleccionado si se filtró por bimestre
                if (isset($competencias_data[$comp_id])) {
                    // Si hay filtro de bimestre, verificar que coincida
                    if (empty($bimestre_filter) || $row_comp['bimestre'] == $bimestre_filter) {
                        $competencias_data[$comp_id]['evaluations'][] = [
                            'id' => $row_comp['evaluation_id'],
                            'title' => $row_comp['evaluation_title'],
                            'grade' => $row_comp['grade'],
                            'bimestre' => $row_comp['bimestre']
                        ];
                    }
                }
            }
            
            $stmt_comp->close();
            $has_competencias = !empty($competencias_data);
        }$evaluation_title_for_avg_display = "Todas las aplicables";    if (!empty($evaluation_id_filter)) {
        $stmt_eval_name = $conn->prepare("SELECT title FROM evaluations WHERE id = ?");
        if ($stmt_eval_name) {
            $stmt_eval_name->bind_param("i", $evaluation_id_filter);
            $stmt_eval_name->execute();
            $result_eval_name = $stmt_eval_name->get_result();
            if ($row_eval_name = $result_eval_name->fetch_assoc()) {
                $evaluation_title_for_avg_display = htmlspecialchars($row_eval_name['title']);
            }
            $stmt_eval_name->close();
        }
    }    // Calcular promedio por competencia y promedio final
    if ($has_competencias) {
        $weighted_sum = 0;            // Suma ponderada de (promedio * porcentaje) para todas las competencias
        $total_applied_percentage = 0; // Suma total de porcentajes de competencias aplicadas
        $bimestres_data = [];         // Almacenar datos por bimestre
        
        // Primero agrupar las evaluaciones por bimestre
        foreach ($competencias_data as $comp_id => &$comp_data) {
            // Solo procesar si hay evaluaciones
            if (!empty($comp_data['evaluations'])) {
                // Agrupar por bimestre
                foreach ($comp_data['evaluations'] as $eval) {
                    $bim = $eval['bimestre'];
                    if (!isset($bimestres_data[$bim])) {
                        $bimestres_data[$bim] = [];
                    }
                    if (!isset($bimestres_data[$bim][$comp_id])) {
                        $bimestres_data[$bim][$comp_id] = [
                            'name' => $comp_data['name'],
                            'percentage' => $comp_data['percentage'],
                            'evaluations' => [],
                            'avg_grade' => 0
                        ];
                    }
                    // Añadir evaluación al bimestre correspondiente
                    $bimestres_data[$bim][$comp_id]['evaluations'][] = $eval;
                }
            }
        }
          // Calcular promedios para el bimestre seleccionado (ya validamos que bimestre_filter no esté vacío)
        if (isset($bimestres_data[$bimestre_filter])) {
            $bim_weighted_sum = 0;
            $bim_total_percentage = 0;
            
            foreach ($bimestres_data[$bimestre_filter] as $comp_id => &$comp_data) {
                $total_grades = 0;
                $valid_evaluations = count($comp_data['evaluations']);
                
                // Calcular promedio de notas para esta competencia en este bimestre
                foreach ($comp_data['evaluations'] as $eval) {
                    $total_grades += $eval['grade'];
                }
                
                // Calcular el promedio de la competencia
                if ($valid_evaluations > 0) {
                    $comp_data['avg_grade'] = $total_grades / $valid_evaluations;
                      // Acumular valores para el cálculo del promedio final
                    // Multiplicamos por el porcentaje (dividido por 100 para obtener la fracción)
                    $bim_weighted_sum += ($comp_data['avg_grade'] * $comp_data['percentage'] / 100);
                    $bim_total_percentage += $comp_data['percentage'];
                    
                    // Actualizar también en competencias_data para mostrar en la UI
                    if (isset($competencias_data[$comp_id])) {
                        $competencias_data[$comp_id]['avg_grade'] = $comp_data['avg_grade'];
                    }
                }
            }
              // Calcular el promedio final del bimestre
            if ($bim_total_percentage > 0) {
                // La suma ponderada ya tiene los porcentajes aplicados, solo necesitamos obtener el resultado final
                $final_average = $bim_weighted_sum;
            } else {
                $final_average = 0;
            }
        } else {
            // Si no hay datos para el bimestre seleccionado, mostrar mensaje informativo
            echo '<div class="alert alert-info">No se encontraron evaluaciones para el bimestre seleccionado.</div>';
            $final_average = 0;
        }
    }    // Si no hay competencias configuradas, usar el promedio simple de todas las evaluaciones que cumplan los filtros
    $sql_avg = "SELECT AVG(eg.grade) as promedio_final
                FROM evaluation_grades eg
                INNER JOIN evaluations e ON eg.evaluation_id = e.id
                INNER JOIN teacher_courses tc ON e.teacher_course_id = tc.id
                INNER JOIN academic_courses ac ON tc.course_id = ac.id AND ac.school_id = ?
                INNER JOIN student s ON eg.student_id = s.id AND s.school_id = ?
                WHERE eg.student_id = ?";
    
    $params_avg = [$school_id, $school_id, $student_id_filter];
    $types_avg = "iii";

    if (!empty($course_id_filter)) { $sql_avg .= " AND tc.course_id = ?"; $params_avg[] = $course_id_filter; $types_avg .= "i"; }
    if ($normalized_level_key !== null) { 
        $sql_avg .= " AND LOWER(REPLACE(TRIM(ac.level), ' ', '')) = ?"; 
        $params_avg[] = $normalized_level_key; 
        $types_avg .= "s"; 
        
        // Asegurar que nivel del alumno coincide con el nivel del curso
        $sql_avg .= " AND LOWER(REPLACE(TRIM(s.nivel), ' ', '')) = ?"; 
        $params_avg[] = $normalized_level_key; 
        $types_avg .= "s"; 
    }
    if (!empty($grado_filter)) { 
        $sql_avg .= " AND tc.grado = ?"; 
        $params_avg[] = $grado_filter; 
        $types_avg .= "s"; 
        
        // Asegurar que grado del alumno coincide con el grado del curso
        $sql_avg .= " AND s.grado = ?"; 
        $params_avg[] = $grado_filter; 
        $types_avg .= "s"; 
    }
    if (!empty($seccion_filter)) { 
        $sql_avg .= " AND tc.seccion = ?"; 
        $params_avg[] = $seccion_filter; 
        $types_avg .= "s"; 
        
        // Asegurar que sección del alumno coincide con la sección del curso
        $sql_avg .= " AND s.seccion = ?"; 
        $params_avg[] = $seccion_filter; 
        $types_avg .= "s"; 
    }
    if (!empty($bimestre_filter)) { $sql_avg .= " AND e.bimestre = ?"; $params_avg[] = $bimestre_filter; $types_avg .= "s"; }
    if (!empty($evaluation_id_filter)) { $sql_avg .= " AND eg.evaluation_id = ?"; $params_avg[] = $evaluation_id_filter; $types_avg .= "i"; }if ($login_type == 2 && $teacher_id) {
        // Ensure teacher can only see averages for their assigned courses/sections
        $sql_avg .= " AND EXISTS (SELECT 1 FROM teacher_courses tc2 
                                  WHERE tc2.id = e.teacher_course_id 
                                  AND tc2.teacher_id = ?)";
        $params_avg[] = $teacher_id;
        $types_avg .= "i";
    }
    
    $promedio_final = null;
    $stmt_avg = $conn->prepare($sql_avg);
    if ($stmt_avg) {
        $stmt_avg->bind_param($types_avg, ...$params_avg);
        $stmt_avg->execute();
        $result_avg = $stmt_avg->get_result();
        if ($row_avg = $result_avg->fetch_assoc()) {
            $promedio_final = $row_avg['promedio_final'];
        }
        $stmt_avg->close();
    } else {
        echo "<div class='alert alert-danger'>Error al preparar la consulta de promedio: " . htmlspecialchars($conn->error) . "</div>";
    }
    ?>    <div style="padding: 25px; border-radius: 10px; margin-top:20px; background-color: #ffffff; box-shadow: 0 5px 20px rgba(0,0,0,0.1); animation: fadeIn 0.5s ease-out;">
        <h3 style="text-align:center; margin-bottom:25px; color:#4e73df; text-shadow: 0 1px 1px rgba(0,0,0,0.05);">
            Promedio Final para: <span style="font-weight: 600;"><?php echo $student_name_avg; ?></span>
            <?php if (!empty($bimestre_filter)): ?>
                <span style="display:inline-block; margin-left:12px; font-size:0.8em; padding:5px 12px; background: linear-gradient(135deg, #4e73df, #3257b3); color:white; border-radius:20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <?php echo $bimestre_filter; ?>° Bimestre
                </span>
            <?php endif; ?>
        </h3>
          <div style="margin-bottom:25px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.07);">
            <table class="table" style="background-color: #fff; margin-bottom: 0;">
                <tr style="background-color: #f8f9fc;">
                    <td style="width:30%; padding: 12px 15px; border-left: 4px solid #4e73df;"><strong>Curso:</strong></td>
                    <td style="padding: 12px 15px;"><?php echo $course_name_for_avg_display; ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 15px; border-left: 4px solid #4e73df;"><strong>Nivel:</strong></td>
                    <td style="padding: 12px 15px;"><?php echo !empty($level_filter) ? htmlspecialchars($level_filter) : 'Todos los aplicables'; ?></td>
                </tr>
                <tr style="background-color: #f8f9fc;">
                    <td style="padding: 12px 15px; border-left: 4px solid #4e73df;"><strong>Grado:</strong></td>
                    <td style="padding: 12px 15px;"><?php echo !empty($grado_filter) ? htmlspecialchars($grado_filter) : 'Todos los aplicables'; ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 15px; border-left: 4px solid #4e73df;"><strong>Sección:</strong></td>
                    <td style="padding: 12px 15px;"><?php echo !empty($seccion_filter) ? htmlspecialchars($seccion_filter) : 'Todas las aplicables'; ?></td>
                </tr>
                <tr style="background-color: #f8f9fc;">
                    <td style="padding: 12px 15px; border-left: 4px solid #4e73df;"><strong>Bimestre:</strong></td>
                    <td style="padding: 12px 15px;"><?php echo !empty($bimestre_filter) ? htmlspecialchars($bimestre_filter) . '° Bimestre' : 'Todos los aplicables'; ?></td>
                </tr>
                <?php if (!empty($evaluation_id_filter)): ?>
                <tr>
                    <td style="padding: 12px 15px; border-left: 4px solid #4e73df;"><strong>Evaluación:</strong></td>
                    <td style="padding: 12px 15px;"><?php echo $evaluation_title_for_avg_display; ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
        </style>
          <?php if ($has_competencias): ?>
            <div class="competencias-container" style="margin-top:30px;">
                <?php foreach ($competencias_data as $comp_id => $comp_data): ?>
                    <?php 
                    // Color por rendimiento de la competencia
                    $nota = (float)$comp_data['avg_grade'];
                    $color_class = '';
                    $color_bar = '';
                    
                    if ($nota >= 16) {
                        $color_class = 'success';
                        $color_bar = '#28a745';
                    } elseif ($nota >= 11) {
                        $color_class = 'primary';  
                        $color_bar = '#4e73df';
                    } elseif ($nota >= 6) {
                        $color_class = 'warning';
                        $color_bar = '#ffc107';
                    } else {
                        $color_class = 'danger';
                        $color_bar = '#dc3545';
                    }
                    ?>
                    <div class="competencia-block" style="margin-bottom:25px; border-radius:8px; padding:15px; background-color:#fff; box-shadow: 0 3px 10px rgba(0,0,0,0.08); border-top: 3px solid <?php echo $color_bar; ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom:15px;">
                            <h4 style="color:#333; margin:0; font-weight: 600;">
                                <?php echo htmlspecialchars($comp_data['name']); ?> 
                            </h4>
                            <div style="display: flex; align-items: center;">
                                <span style="font-size:0.9em; color:#555; margin-right: 10px; background-color: #f8f9fc; padding: 4px 10px; border-radius: 4px;"><?php echo $comp_data['percentage']; ?>% del total</span>
                                <span class="badge badge-<?php echo $color_class; ?>" style="font-size: 14px; padding: 5px 10px;"><?php echo number_format((float)$comp_data['avg_grade'], 2); ?></span>
                            </div>
                        </div>
                        
                        <div style="overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                            <table class="table table-hover" style="margin-bottom: 0; font-size:0.9em;">                            
                                <thead style="background: linear-gradient(90deg, <?php echo $color_bar; ?>20, <?php echo $color_bar; ?>10); border-bottom: 2px solid <?php echo $color_bar; ?>;">                                
                                    <tr>
                                        <th style="width:60%; padding: 12px 15px;">Evaluación</th>
                                        <th style="width:40%; padding: 12px 15px;">Nota</th>
                                    </tr>
                                </thead>
                                <tbody>                                
                                    <?php foreach ($comp_data['evaluations'] as $eval): ?>
                                    <tr>
                                        <td style="padding: 10px 15px;"><?php echo htmlspecialchars($eval['title']); ?></td>
                                        <td style="padding: 10px 15px;">
                                            <span style="display: inline-block; min-width: 40px; text-align: center; 
                                                  padding: 3px 8px; border-radius: 4px; 
                                                  background-color: <?php echo $color_bar; ?>15; 
                                                  color: <?php echo $color_bar; ?>;">
                                                <?php echo number_format((float)$eval['grade'], 2); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr style="background-color: <?php echo $color_bar; ?>10;">
                                        <td style="padding: 12px 15px;"><strong>Promedio de Competencia</strong></td>
                                        <td style="padding: 12px 15px;">
                                            <strong style="display: inline-block; min-width: 40px; text-align: center; 
                                                  padding: 5px 10px; border-radius: 4px; 
                                                  background-color: <?php echo $color_bar; ?>; 
                                                  color: #fff;">
                                                <?php echo number_format((float)$comp_data['avg_grade'], 2); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>                <?php 
                // Color por rendimiento del promedio final
                $final_note = (float)$final_average;
                $final_color = '';
                $final_bg = '';
                $final_text = '';
                $final_icon = '';
                
                if ($final_note >= 18) {
                    $final_color = '#1e7e34';
                    $final_bg = '#d4edda';
                    $final_text = 'Excelente';
                    $final_icon = 'trophy';
                } elseif ($final_note >= 14) {
                    $final_color = '#117a8b';
                    $final_bg = '#d1ecf1';
                    $final_text = 'Muy Bueno';
                    $final_icon = 'thumbs-up';
                } elseif ($final_note >= 11) {
                    $final_color = '#856404';
                    $final_bg = '#fff3cd';
                    $final_text = 'Aprobado';
                    $final_icon = 'check-circle';
                } else {
                    $final_color = '#721c24';
                    $final_bg = '#f8d7da';
                    $final_text = 'Necesita Mejorar';
                    $final_icon = 'exclamation-circle';
                }
                ?>
                <div class="final-average" style="margin-top:35px; background: linear-gradient(135deg, <?php echo $final_bg; ?>, white); padding:20px; border-radius:10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid <?php echo $final_color; ?>40;">
                    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 15px;">
                        <div style="background-color: <?php echo $final_color; ?>; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-right: 15px; box-shadow: 0 4px 10px <?php echo $final_color; ?>50;">
                            <i class="fa fa-<?php echo $final_icon; ?>" style="color: white; font-size: 24px;"></i>
                        </div>
                        <h3 style="text-align:center; color:<?php echo $final_color; ?>; margin: 0; font-weight: 600;">
                            Promedio <?php echo $bimestre_filter; ?>° Bimestre: 
                            <strong style="font-size: 1.1em;"><?php echo number_format((float)$final_average, 2); ?></strong>
                            <span style="font-size: 0.8em; display: block; margin-top: 5px; text-transform: uppercase;"><?php echo $final_text; ?></span>
                        </h3>
                    </div>
                    
                    <div style="width: 100%; height: 8px; background-color: #eee; border-radius: 4px; margin: 15px 0; overflow: hidden;">
                        <div style="width: <?php echo min($final_note * 5, 100); ?>%; height: 100%; background: linear-gradient(90deg, <?php echo $final_color; ?>80, <?php echo $final_color; ?>);"></div>
                    </div>
                    
                    <p style="text-align:center; font-size:0.9em; color:#555; margin-top:10px; font-style: italic;">
                        Calculado según el porcentaje de cada competencia del bimestre
                    </p>
                    
                    <?php 
                    // Mostrar detalle de cálculo para bimestre filtrado
                    if (!empty($bimestre_filter) && isset($bimestres_data[$bimestre_filter])): 
                        $bim_data = $bimestres_data[$bimestre_filter];
                        $bim_percentage_sum = 0;
                        foreach ($bim_data as $comp) {
                            $bim_percentage_sum += $comp['percentage'];
                        }
                    ?>                    <div style="margin-top:20px;">
                        <h5 style="color:#495057; margin-bottom:12px; text-align:center; font-size:1em;">
                            Desglose del Cálculo de Promedio
                        </h5>
                        <table class="table table-sm table-bordered" style="background-color:#f8f9fa; font-size:0.9em; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius:4px; overflow:hidden;">
                            <thead style="background-color:#e9ecef;">
                                <tr>
                                    <th style="width:50%; padding:10px; border-bottom:2px solid #dee2e6;">Competencia</th>
                                    <th style="width:15%; padding:10px; border-bottom:2px solid #dee2e6; text-align:center;">Promedio</th>
                                    <th style="width:15%; padding:10px; border-bottom:2px solid #dee2e6; text-align:center;">Peso</th>
                                    <th style="width:20%; padding:10px; border-bottom:2px solid #dee2e6; text-align:center;">Contribución</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bim_data as $comp): ?>
                                <tr>
                                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($comp['name']); ?></td>
                                    <td class="text-center" style="padding:8px 10px;"><?php echo number_format((float)$comp['avg_grade'], 2); ?></td>
                                    <td class="text-center" style="padding:8px 10px;"><span class="badge badge-info" style="font-size:0.85em; padding:3px 8px; background-color:#17a2b8;"><?php echo $comp['percentage']; ?>%</span></td>
                                    <td class="text-center" style="padding:8px 10px;"><?php echo number_format((float)($comp['avg_grade'] * $comp['percentage'] / 100), 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="font-weight:bold; background-color:#e8f4f8;">
                                    <td style="padding:8px 10px;">Total</td>
                                    <td></td>
                                    <td class="text-center" style="padding:8px 10px;"><?php echo $bim_percentage_sum; ?>%</td>
                                    <td class="text-center" style="padding:8px 10px; font-size:1.1em; color:#155724;"><?php echo number_format((float)$final_average, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div><?php endif; ?>
                    
                    <?php if ($bim_total_percentage > 0 && $bim_total_percentage != 100): ?>
                    <div style="margin-top:10px; padding:10px; background-color:#fff3cd; border-radius:5px;">
                        <p style="text-align:center; font-size:0.85em; color:#856404; margin:0;">
                            <i class="fa fa-info-circle"></i> 
                            Nota: Los porcentajes aplicados suman <?php echo $bim_total_percentage; ?>%. 
                            Para el cálculo del promedio se considera 100% como base.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($promedio_final !== null): ?>
            <table class="table table-bordered table-hover" style="margin-top: 15px; font-size: 0.95em;">
                <thead style="background-color: #f0f0f0;">
                    <tr>
                        <th style="width:40%;">Concepto</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Promedio General (según filtros)</td>
                        <td><strong><?php echo number_format((float)$promedio_final, 2); ?></strong></td>
                    </tr>
                    <tr><td colspan="2" class="text-center" style="color:#666;">No se encontraron competencias configuradas. Se muestra el promedio simple de todas las evaluaciones.</td></tr>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center" style="margin-top:15px; color: #d9534f;">No se pudo calcular el promedio con los filtros seleccionados o no hay notas registradas para este alumno bajo esos criterios.</p>        <?php endif; ?>
    </div>
    <?php
}
} else {    // --- MAIN REPORT LOGIC ---
    $sql_main = "SELECT s.name as student_name, s.id_no as student_dni, 
                        ac.name as course_name, ac.level as academic_level,
                        tc.grado as evaluation_grado, tc.seccion as evaluation_seccion,
                        e.title as evaluation_title, 
                        e.bimestre, eg.grade as nota
                 FROM evaluation_grades eg
                 INNER JOIN student s ON eg.student_id = s.id AND s.school_id = ?
                 INNER JOIN evaluations e ON eg.evaluation_id = e.id
                 INNER JOIN teacher_courses tc ON e.teacher_course_id = tc.id
                 INNER JOIN academic_courses ac ON tc.course_id = ac.id AND ac.school_id = ? ";
    $where_clauses = [];
    $params = [$school_id, $school_id];
    $types = "ii";

    if ($login_type == 2 && $teacher_id) {
        $where_clauses[] = "tc.teacher_id = ?";
        $params[] = $teacher_id;
        $types .= "i";
    }    if (!empty($course_id_filter)) { 
        $where_clauses[] = "tc.course_id = ?"; 
        $params[] = $course_id_filter; 
        $types .= "i"; 
    }
    
    // Filtro de nivel académico - Aplicamos a curso y estudiante
    if ($normalized_level_key !== null) { 
        $where_clauses[] = "LOWER(REPLACE(TRIM(ac.level), ' ', '')) = ?"; 
        $params[] = $normalized_level_key; 
        $types .= "s"; 
        
        $where_clauses[] = "LOWER(REPLACE(TRIM(s.nivel), ' ', '')) = ?"; 
        $params[] = $normalized_level_key; 
        $types .= "s";
    }
    
    // Filtro de grado - Aplicamos a curso y estudiante
    if (!empty($grado_filter)) { 
        $where_clauses[] = "tc.grado = ?"; 
        $params[] = $grado_filter; 
        $types .= "s"; 
        
        $where_clauses[] = "s.grado = ?"; 
        $params[] = $grado_filter; 
        $types .= "s";
    }
    
    // Filtro de sección - Aplicamos a curso y estudiante
    if (!empty($seccion_filter)) { 
        $where_clauses[] = "tc.seccion = ?"; 
        $params[] = $seccion_filter; 
        $types .= "s"; 
        
        $where_clauses[] = "s.seccion = ?"; 
        $params[] = $seccion_filter; 
        $types .= "s";
    }
    if (!empty($student_id_filter)) { 
        $where_clauses[] = "eg.student_id = ?"; 
        $params[] = $student_id_filter; 
        $types .= "i"; 
    }
    if (!empty($evaluation_id_filter)) { 
        $where_clauses[] = "eg.evaluation_id = ?"; 
        $params[] = $evaluation_id_filter; 
        $types .= "i"; 
    }
    if (!empty($bimestre_filter)) { 
        $where_clauses[] = "e.bimestre = ?"; 
        $params[] = $bimestre_filter; 
        $types .= "s"; 
    }

    if (count($where_clauses) > 0) {
        $sql_main .= " WHERE " . implode(" AND ", $where_clauses);
    }
    $sql_main .= " ORDER BY s.name, ac.name, e.title ";

    $report_data = [];
    $stmt_main = $conn->prepare($sql_main);

    if ($stmt_main) {
        if (!empty($params)) {
             $stmt_main->bind_param($types, ...$params);
        }
        $stmt_main->execute();
        $result_main = $stmt_main->get_result();
        while ($row = $result_main->fetch_assoc()) {
            $report_data[] = $row;
        }
        $stmt_main->close();
    } else {
        echo "<div class='alert alert-danger'>Error al preparar la consulta principal: " . htmlspecialchars($conn->error) . ". SQL: " . htmlspecialchars($sql_main) ."</div>";
    }
    ?>
    <hr>    <?php 
    // Add a data attribute to indicate if there's any data
    $has_data = (count($report_data) > 0) ? 'true' : 'false';
    ?>
    <table class="table table-bordered table-striped table-hover" id="gradesReportData" 
           style="width:100%; font-size:0.9em;" data-has-records="<?php echo $has_data; ?>">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Alumno</th>
                <th>DNI</th>
                <th>Curso</th>
                <th>Nivel</th>
                <th>Grado</th>
                <th>Sección</th>
                <th>Evaluación</th>
                <th>Bimestre</th>
                <th>Nota</th>
            </tr>
        </thead><tbody>
            <?php if (count($report_data) > 0): ?>
                <?php foreach ($report_data as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_dni']); ?></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['academic_level']); ?></td>
                        <td><?php echo htmlspecialchars($row['evaluation_grado']); ?></td>
                        <td><?php echo htmlspecialchars($row['evaluation_seccion']); ?></td>
                        <td><?php echo htmlspecialchars($row['evaluation_title']); ?></td>
                        <td><?php echo !empty($row['bimestre']) ? htmlspecialchars($row['bimestre']) . '° Bimestre' : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($row['nota']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="no-data-row">
                    <td colspan="10" class="text-center" style="padding:15px;">No se encontraron registros con los filtros seleccionados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>    <script>
        $(document).ready(function() {
            // Check if the table has data rows using the data attribute
            var hasData = $('#gradesReportData').attr('data-has-records') === 'true';
            
            // Ensure any existing DataTable instance is properly destroyed first
            if ($.fn.dataTable.isDataTable('#gradesReportData')) {
                $('#gradesReportData').DataTable().destroy();
                console.log("DataTable instance was destroyed before reinitializing");
            }
            
            // Clear any existing DataTables classes and properties
            $('#gradesReportData').removeClass('dataTable no-footer display')
                                 .find('thead th').removeClass('sorting_asc sorting_desc sorting');
            
            // Only initialize DataTables if there is actual data
            if (hasData) {
                try {
                    // Use a unique ID to help avoid reinitializing the same table
                    window.gradesTableInit = (window.gradesTableInit || 0) + 1;
                    
                    // Add a short delay to ensure DOM is fully processed
                    setTimeout(function() {
                        // Double-check that the table hasn't been initialized again during the delay
                        if (!$.fn.dataTable.isDataTable('#gradesReportData')) {                            $('#gradesReportData').DataTable({
                                retrieve: true, // This helps prevent "Cannot reinitialize DataTable" errors
                                responsive: true,
                                // dom: 'Bfrtip', // Uncomment if you need export buttons
                                // buttons: [
                                //     'copy', 'csv', 'excel', 'pdf', 'print'
                                // ],
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                                },
                                pageLength: 10,
                                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
                            });
                            console.log("DataTable initialized successfully");
                        } else {
                            console.log("DataTable already initialized, skipping initialization");
                        }
                    }, 100);
                } catch (e) {
                    console.error("Error inicializando DataTable:", e);
                }
            } else {
                console.log("La tabla no tiene datos, se omite la inicialización de DataTables");
            }
        });
    </script>
    <?php
}
$conn->close();
?>
