<?php
ob_start();
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

include 'db_connect.php';
$action = $_GET['action'] ?? null;
include 'admin_class.php';
$crud = new Action();

try {
	if ($action == "login") {
		header('Content-Type: application/json');
		$username = $_POST['username'] ?? '';
		$password = $_POST['password'] ?? '';
		$school_id = intval($_POST['school_id'] ?? 0);

		if (!$school_id) {
			echo json_encode(['status' => 0, 'message' => 'Debe seleccionar un colegio.']);
			exit;
		}

		$user = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = md5('$password') AND school_id = $school_id");
		if ($user && $user->num_rows > 0) {
			$row = $user->fetch_assoc();
			$_SESSION['login_id'] = $row['id'];
			$_SESSION['login_type'] = $row['type'];
			$_SESSION['login_school_id'] = $row['school_id'];
			
			// Ensure the avatar is loaded into the session
			if(isset($row['avatar']) && !empty($row['avatar'])) {
				$_SESSION['login_avatar'] = $row['avatar'];
			}
			
			// Asignar login_teacher_id si es docente
			if ($row['type'] == 2) {
				if (!empty($row['teacher_id'])) {
					$_SESSION['login_teacher_id'] = $row['teacher_id'];
				} else {
					// Buscar teacher_id por nombre si no está en users
					$tq = $conn->query("SELECT id FROM teacher WHERE name = '" . $conn->real_escape_string($row['name']) . "' LIMIT 1");
					if ($tq && $tq->num_rows > 0) {
						$_SESSION['login_teacher_id'] = $tq->fetch_assoc()['id'];
					}
				}
			}
			// ...otros datos de sesión...
			echo json_encode(['status' => 1]);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Usuario, contraseña o colegio incorrectos.']);
		}
		exit;
	}
	if ($action == 'login2') {
		$login = $crud->login2();
		if ($login)
			echo $login;
	}
	if ($action == 'logout') {
		$logout = $crud->logout();
		if ($logout)
			echo $logout;
	}
	if ($action == 'logout2') {
		$logout = $crud->logout2();
		if ($logout)
			echo $logout;
	}
	if ($action == 'save_user') {
		header('Content-Type: application/json');
		$save = $crud->save_user();
		if ($save == 1) {
			echo json_encode(['status' => 1, 'message' => 'Perfil actualizado con éxito.']);
		} else if ($save == 2) {
			echo json_encode(['status' => 2, 'message' => 'El nombre de usuario ya existe.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Ocurrió un error al guardar los datos.']);
		}
		exit;
	}
	if ($action == 'delete_user') {
		$save = $crud->delete_user();
		if ($save)
			echo $save;
	}
	if ($action == 'signup') {
		$save = $crud->signup();
		if ($save)
			echo $save;
	}
	if ($action == 'update_account') {
		$save = $crud->update_account();
		if ($save)
			echo $save;
	}
	if ($action == "save_settings") {
		$save = $crud->save_settings();
		if ($save)
			echo $save;
	}
	if ($action == "save_course") {
		header('Content-Type: application/json');
		$save = $crud->save_course();
		echo $save;
		exit;
	}
	if ($action == "delete_course") {
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		$delete = $crud->delete_course();
		if ($delete == 1) {
			echo json_encode(['status' => 1, 'message' => 'Curso eliminado exitosamente.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al eliminar el curso.']);
		}
		exit;
	}
	if ($action == "save_student") {
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		$save = $crud->save_student();
		if ($save == 1) {
			echo json_encode(['status' => 1, 'message' => 'Estudiante guardado exitosamente.']);
		} elseif ($save == 2) {
			echo json_encode(['status' => 2, 'message' => 'El ID del estudiante ya existe.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al guardar el estudiante.']);
		}
		exit;
	}
	if ($action == "delete_student") {
		header('Content-Type: application/json');
		$delete = $crud->delete_student();
		if ($delete == 1) {
			echo json_encode(['status' => 1, 'message' => 'Estudiante eliminado exitosamente.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al eliminar el estudiante.']);
		}
		exit;
	}
	if ($action == "save_fees") {
		$result = $crud->save_fees();
		if ($result == 1) {
			echo json_encode(['status' => 1, 'message' => 'Datos guardados exitósamente']);
		} else if ($result == 2) {
			echo json_encode(['status' => 2, 'message' => 'Número de Curso Existe Actualmente']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al guardar los datos.']);
		}
		exit;
	}
	if ($action == "delete_fees") {
		$delete = $crud->delete_fees();
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		if ($delete == 1) {
			echo json_encode(['status' => 1, 'message' => 'Tarifas eliminadas exitosamente.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al eliminar las tarifas.']);
		}
		exit;
	}
	if ($action == "save_payment") {
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		$save = $crud->save_payment();
		if ($save) {
			echo $save; // La función `save_payment` ya devuelve un JSON válido
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al guardar el pago.']);
		}
		exit;
	}
	if ($action == "delete_payment") {
		$delete = $crud->delete_payment();
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		if ($delete == 1) {
			echo json_encode(['status' => 1, 'message' => 'Pago eliminado exitosamente.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al eliminar el pago.']);
		}
		exit;
	}
	if ($action == 'upload_excel') {
		include 'upload_excel.php'; // Incluir el archivo que maneja la subida de Excel
		exit; // Asegurarse de que no se ejecute más código después
	}

	if ($action == 'upload_teacher_excel') {
		include 'upload_teacher_excel.php'; // Incluir el archivo que maneja la subida de Excel para docentes
		exit; // Asegurarse de que no se ejecute más código después
	}

	if ($action == 'process_payment_excel') {
		include 'process_payment_excel.php';
		exit;
	}

	if ($action == 'save_asistencia') {
		header('Content-Type: application/json');
		$student_id = $_POST['student_id'];
		$fecha = $_POST['fecha'];
		$tipo = $_POST['tipo'];
		$hora = date('H:i:s');
		$estado = $_POST['estado'];
		$check = $conn->query("SELECT id FROM asistencia WHERE student_id='$student_id' AND fecha='$fecha' AND tipo='$tipo'");
		if ($check && $check->num_rows > 0) {
			echo json_encode(['status' => 0, 'message' => 'Ya existe un registro de ' . $tipo . ' para este estudiante en esta fecha.']);
		} else {
			$save = $conn->query("INSERT INTO asistencia (student_id, fecha, tipo, hora, estado) VALUES ('$student_id', '$fecha', '$tipo', '$hora', '$estado')");
			if ($save) {
				echo json_encode(['status' => 1, 'message' => 'Asistencia guardada correctamente.']);
			} else {
				echo json_encode(['status' => 0, 'message' => 'Error al guardar la asistencia.']);
			}
		}
		exit;
	}
	if ($action == 'get_asistencia') {
		header('Content-Type: application/json');
		$fecha = $_POST['fecha'];
			$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
		$where_tipo = $tipo ? " AND a.tipo = '$tipo'" : "";
		$q = $conn->query("SELECT a.*, s.name, s.id_no FROM asistencia a INNER JOIN student s ON s.id = a.student_id WHERE a.fecha = '$fecha' $where_tipo ORDER BY a.tipo ASC, a.hora ASC");
		$data = [];
		while($row = $q->fetch_assoc()) {
			$data[] = $row;
		}
		echo json_encode($data);
		exit;
	}
	if ($action == 'save_asistencia_barcode') {
		header('Content-Type: application/json');
		$dni = $_POST['dni'];
		$fecha = $_POST['fecha'];
		$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'Entrada';
		
		// Usar la hora enviada desde el cliente o generar una en el servidor
		$hora = isset($_POST['hora_actual']) ? $_POST['hora_actual'] : date('H:i:s');
		
		$q = $conn->query("SELECT id, name FROM student WHERE id_no = '$dni' LIMIT 1");
		if ($q && $q->num_rows > 0) {
			$student = $q->fetch_assoc();
			$student_id = $student['id'];
			$check = $conn->query("SELECT id FROM asistencia WHERE student_id='$student_id' AND fecha='$fecha' AND tipo='$tipo'");
			if ($check && $check->num_rows > 0) {
				echo json_encode(['status' => 0, 'message' => "Ya se registró $tipo para este estudiante hoy."]);
				exit;
			}
			$estado = "Presente";
			$save = $conn->query("INSERT INTO asistencia (student_id, fecha, tipo, hora, estado) VALUES ('$student_id', '$fecha', '$tipo', '$hora', '$estado')");
			if ($save) {
				echo json_encode(['status' => 1, 'message' => "Asistencia registrada ($tipo) para " . $student['name']]);
			} else {
				echo json_encode(['status' => 0, 'message' => 'Error al guardar la asistencia.']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'DNI no encontrado.']);
		}
		exit;
	}
	if ($action == 'get_students_for_asistencia') {
		header('Content-Type: application/json');
		$q = $conn->query("SELECT id, name, id_no FROM student ORDER BY name ASC");
		$data = [];
		while($row = $q->fetch_assoc()) {
			$data[] = $row;
		}
		echo json_encode($data);
		exit;
	}
	if ($action == "save_teacher") {
		header('Content-Type: application/json');
		$save = $crud->save_teacher();
		echo $save;
		exit;
	}
	if ($action == "delete_teacher") {
		header('Content-Type: application/json');
		$delete = $crud->delete_teacher();
		if ($delete == 1) {
			echo json_encode(['status' => 1, 'message' => 'Docente eliminado exitosamente.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al eliminar el docente.']);
		}
		exit;
	}
	if ($action == "save_grade") {
		header('Content-Type: application/json');
		$save = $crud->save_grade();
		echo $save;
		exit;
	}
	if ($action == "delete_grade") {
		header('Content-Type: application/json');
		$delete = $crud->delete_grade();
		echo $delete;
		exit;
	}
	if ($action == "save_academic_course") {
		header('Content-Type: application/json');
		$id = $_POST['id'] ?? '';
		$name = $conn->real_escape_string($_POST['name']);
		$level = $conn->real_escape_string($_POST['level']);
		$description = $conn->real_escape_string($_POST['description']);
		
		// Obtener el school_id del administrador actual
		$school_id = $_SESSION['login_school_id'] ?? 0;
		
		if (empty($id)) {
			// Al crear nuevo curso, asignar el school_id del administrador
			$save = $conn->query("INSERT INTO academic_courses (name, level, description, school_id) 
							 VALUES ('$name', '$level', '$description', $school_id)");
		} else {
			// Al actualizar, verificar que el curso pertenezca al mismo colegio
			$check = $conn->query("SELECT * FROM academic_courses WHERE id = $id AND school_id = $school_id");
			if ($check && $check->num_rows > 0) {
				$save = $conn->query("UPDATE academic_courses SET name='$name', level='$level', description='$description' WHERE id=$id");
			} else {
				echo json_encode(['status' => 0, 'message' => 'No tiene permiso para editar este curso.']);
				exit;
			}
		}
		
		if ($save) {
			echo json_encode(['status' => 1, 'message' => 'Curso guardado exitosamente.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al guardar el curso: ' . $conn->error]);
		}
		exit;
	}
	if ($action == "delete_academic_course") {
		header('Content-Type: application/json');
		$id = $_POST['id'];
		$delete = $conn->query("DELETE FROM academic_courses WHERE id = $id");
		if ($delete) {
			echo json_encode(['status' => 1]);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al eliminar el curso.']);
		}
		exit;
	}
	if ($action == "assign_teacher_course") {
		header('Content-Type: application/json');
		echo $crud->assign_teacher_course();
		exit;
	}
	if ($action == "delete_teacher_course") {
		header('Content-Type: application/json');
		echo $crud->delete_teacher_course();
		exit;
	}
	if ($action == "save_teacher_user") {
		header('Content-Type: application/json');
		echo $crud->save_teacher_user();
		exit;
	}
	if ($action == "save_evaluation") {
		header('Content-Type: application/json');
		echo $crud->save_evaluation();
		exit;
	}
	if ($action == "delete_evaluation") {
		header('Content-Type: application/json');
		echo $crud->delete_evaluation();
		exit;
	}
	if ($action == "save_evaluation_grades") {
		header('Content-Type: application/json');
		echo $crud->save_evaluation_grades();
		exit;
	}
	if ($action == "get_teacher_course_grades") {
		header('Content-Type: application/json');
		$course_id = intval($_POST['course_id']);
		$teacher_id = $_SESSION['login_teacher_id'] ?? 0;
		$grados = [];
		if ($teacher_id && $course_id) {
			$q = $conn->query("SELECT DISTINCT grado FROM teacher_courses WHERE teacher_id = $teacher_id AND course_id = $course_id");
			while ($row = $q->fetch_assoc()) {
				$grados[] = $row['grado'];
			}
		}
		echo json_encode($grados);
		exit;
	}
	if ($action == 'get_pending_concepts') {
		header('Content-Type: application/json');
		$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
		$data = [];
		if($student_id) {
			$q = $conn->query("SELECT ef.id as ef_id, ef.total_fee, s.name as sname, s.id_no, c.course as course_name
				FROM student_ef_list ef
				INNER JOIN student s ON s.id = ef.student_id
				INNER JOIN courses c ON c.id = ef.course_id
				WHERE ef.student_id = $student_id");
			while($row = $q->fetch_assoc()) {
				$paid_q = $conn->query("SELECT SUM(amount) as paid FROM payments WHERE ef_id = " . $row['ef_id']);
				$paid = $paid_q && $paid_q->num_rows > 0 ? floatval($paid_q->fetch_assoc()['paid']) : 0;
				$balance = floatval($row['total_fee']) - $paid;
				if($balance > 0.01) { // Solo mostrar si hay deuda
					$row['balance'] = $balance;
					$data[] = $row;
				}
			}
		}
		echo json_encode(['status' => 'ok', 'data' => $data]);
		exit;
	}
	if ($action == 'get_students_by_grado_seccion') {
		header('Content-Type: application/json');
		$grado = $_POST['grado'] ?? '';
		$seccion = $_POST['seccion'] ?? '';
        $level = $_POST['level'] ?? '';
        $school_id = $_SESSION['login_school_id'] ?? 0;
		
		// Siempre filtramos por school_id para seguridad
		$where = ["school_id = $school_id"];
		
		if ($grado) $where[] = "grado = '".$conn->real_escape_string($grado)."'";
		if ($seccion) $where[] = "seccion = '".$conn->real_escape_string($seccion)."'";
        if ($level) {
            // Normalizamos el nivel para evitar problemas con espacios y mayúsculas
            $normalized_level = strtolower(str_replace(' ', '', trim($level)));
            $where[] = "LOWER(REPLACE(TRIM(nivel), ' ', '')) = '".$conn->real_escape_string($normalized_level)."'";
        }
        
		$where_sql = implode(' AND ', $where);
		$students = [];
		
		// Mejoramos la consulta para ser consistentes
		$q = $conn->query("SELECT id, name, id_no FROM student WHERE $where_sql ORDER BY name ASC");
		if ($q && $q->num_rows > 0) {
			while($stu = $q->fetch_assoc()) {
				$students[] = [
					'id' => $stu['id'],
					'name' => ucwords($stu['name']),
					'id_no' => $stu['id_no']
				];
			}
			echo json_encode(['status' => 1, 'students' => $students]);
		} else {
			echo json_encode(['status' => 1, 'students' => [], 'message' => 'No hay alumnos para el grado y sección seleccionados.']);
		}
		exit;
	}
	if ($action == 'get_evaluations_by_filters') {
		header('Content-Type: application/json');
		// Permitir course_id o course_name
		$course_id = $_POST['course_id'] ?? '';
		$course_name = $_POST['course_name'] ?? '';
		$level = $_POST['level'] ?? '';
		$grado = $_POST['grado'] ?? '';
		$seccion = $_POST['seccion'] ?? '';
		$bimestre = $_POST['bimestre'] ?? '';
		$evaluations = [];
		// Si solo viene course_id, buscar el nombre
		if ($course_id && !$course_name) {
			$qcn = $conn->query("SELECT name FROM academic_courses WHERE id = '".$conn->real_escape_string($course_id)."'");
			if ($qcn && $qcn->num_rows > 0) {
				$course_name = $qcn->fetch_assoc()['name'];
			}
		}
		// Buscar el teacher_course_id correspondiente
		$where_tc = [];
		if ($course_name) $where_tc[] = "ac.name = '".$conn->real_escape_string($course_name)."'";
		if ($level) $where_tc[] = "ac.level = '".$conn->real_escape_string($level)."'";
		if ($grado) $where_tc[] = "tc.grado = '".$conn->real_escape_string($grado)."'";
		if ($seccion) $where_tc[] = "tc.seccion = '".$conn->real_escape_string($seccion)."'";
		$where_tc_sql = $where_tc ? 'WHERE ' . implode(' AND ', $where_tc) : '';
		$teacher_course_ids = [];
		$qtc = $conn->query("SELECT tc.id FROM teacher_courses tc INNER JOIN academic_courses ac ON ac.id = tc.course_id $where_tc_sql");
		if ($qtc && $qtc->num_rows > 0) {
			while($row = $qtc->fetch_assoc()) {
				$teacher_course_ids[] = $row['id'];
			}
		}
		if (!empty($teacher_course_ids)) {
			$where_eval = ["teacher_course_id IN (".implode(",", array_map('intval', $teacher_course_ids)).")"];
			if ($bimestre) $where_eval[] = "bimestre = '".$conn->real_escape_string($bimestre)."'";
			$where_eval_sql = $where_eval ? 'WHERE ' . implode(' AND ', $where_eval) : '';
			$q = $conn->query("SELECT id, title FROM evaluations $where_eval_sql ORDER BY title ASC");
			if ($q && $q->num_rows > 0) {
				while($ev = $q->fetch_assoc()) {
					$evaluations[] = [
						'id' => $ev['id'],
						'title' => $ev['title']
					];
				}
			}
		}
		echo json_encode(['status' => 1, 'evaluations' => $evaluations]);
		exit;
	}
	if ($action == 'get_grados_by_nivel') {
		header('Content-Type: application/json');
		$level = $_POST['level'] ?? '';
		$school_id = $_SESSION['login_school_id'] ?? 0;
		
		// Normalizar nivel para evitar problemas con espacios y mayúsculas
		$normalized_level = strtolower(str_replace(' ', '', trim($level)));
		
		$grados = [];
		if ($level) {
			if ($_SESSION['login_type'] == 2 && isset($_SESSION['login_teacher_id'])) {
				// Para profesores, solo mostrar sus grados asignados
				$teacher_id = $_SESSION['login_teacher_id'];
				$q = $conn->query("SELECT DISTINCT tc.grado 
								   FROM teacher_courses tc 
								   INNER JOIN academic_courses ac ON tc.course_id = ac.id 
								   WHERE tc.teacher_id = $teacher_id 
								   AND tc.school_id = $school_id 
								   AND LOWER(REPLACE(TRIM(ac.level), ' ', '')) = '".$conn->real_escape_string($normalized_level)."' 
								   ORDER BY tc.grado");
			} else {
				// Para administradores, mostrar todos los grados
				$q = $conn->query("SELECT DISTINCT s.grado 
								   FROM student s 
								   WHERE s.school_id = $school_id 
								   AND LOWER(REPLACE(TRIM(s.nivel), ' ', '')) = '".$conn->real_escape_string($normalized_level)."' 
								   AND s.grado != '' 
								   ORDER BY s.grado");
			}
			
			if ($q && $q->num_rows > 0) {
				while($row = $q->fetch_assoc()) {
					$grados[] = $row['grado'];
				}
			}
		}
		
		echo json_encode(['status' => 1, 'grados' => $grados]);
		exit;
	}
	
	if ($action == 'get_secciones_by_grado_nivel') {
		header('Content-Type: application/json');
		$level = $_POST['level'] ?? '';
		$grado = $_POST['grado'] ?? '';
		$school_id = $_SESSION['login_school_id'] ?? 0;
		
		// Normalizar nivel para evitar problemas con espacios y mayúsculas
		$normalized_level = strtolower(str_replace(' ', '', trim($level)));
		
		$secciones = [];
		if ($level && $grado) {
			if ($_SESSION['login_type'] == 2 && isset($_SESSION['login_teacher_id'])) {
				// Para profesores, solo mostrar sus secciones asignadas
				$teacher_id = $_SESSION['login_teacher_id'];
				$q = $conn->query("SELECT DISTINCT tc.seccion 
								   FROM teacher_courses tc 
								   INNER JOIN academic_courses ac ON tc.course_id = ac.id 
								   WHERE tc.teacher_id = $teacher_id 
								   AND tc.school_id = $school_id 
								   AND LOWER(REPLACE(TRIM(ac.level), ' ', '')) = '".$conn->real_escape_string($normalized_level)."' 
								   AND tc.grado = '".$conn->real_escape_string($grado)."' 
								   ORDER BY tc.seccion");
			} else {
				// Para administradores, mostrar todas las secciones
				$q = $conn->query("SELECT DISTINCT s.seccion 
								   FROM student s 
								   WHERE s.school_id = $school_id 
								   AND LOWER(REPLACE(TRIM(s.nivel), ' ', '')) = '".$conn->real_escape_string($normalized_level)."' 
								   AND s.grado = '".$conn->real_escape_string($grado)."'
								   AND s.seccion != '' 
								   ORDER BY s.seccion");
			}
			
			if ($q && $q->num_rows > 0) {
				while($row = $q->fetch_assoc()) {
					$secciones[] = $row['seccion'];
				}
			}
		}
		
		echo json_encode(['status' => 1, 'secciones' => $secciones]);
		exit;
	}
	
	if ($action == 'get_courses_by_aula') {
		header('Content-Type: application/json');
		$level = $_POST['level'] ?? '';
		$grado = $_POST['grado'] ?? '';
		$seccion = $_POST['seccion'] ?? '';
		$school_id = $_SESSION['login_school_id'] ?? 0;
		
		// Normalizar nivel para evitar problemas con espacios y mayúsculas
		$normalized_level = strtolower(str_replace(' ', '', trim($level)));
		
		$courses = [];
		if ($level && $grado && $seccion) {
			if ($_SESSION['login_type'] == 2 && isset($_SESSION['login_teacher_id'])) {
				// Para profesores, solo mostrar sus cursos asignados
				$teacher_id = $_SESSION['login_teacher_id'];
				$q = $conn->query("SELECT ac.id, ac.name 
								   FROM teacher_courses tc 
								   INNER JOIN academic_courses ac ON tc.course_id = ac.id 
								   WHERE tc.teacher_id = $teacher_id 
								   AND tc.school_id = $school_id 
								   AND LOWER(REPLACE(TRIM(ac.level), ' ', '')) = '".$conn->real_escape_string($normalized_level)."' 
								   AND tc.grado = '".$conn->real_escape_string($grado)."' 
								   AND tc.seccion = '".$conn->real_escape_string($seccion)."' 
								   ORDER BY ac.name");
			} else {
				// Para administradores, mostrar todos los cursos
				$q = $conn->query("SELECT ac.id, ac.name 
								   FROM academic_courses ac 
								   INNER JOIN teacher_courses tc ON ac.id = tc.course_id 
								   WHERE ac.school_id = $school_id 
								   AND LOWER(REPLACE(TRIM(ac.level), ' ', '')) = '".$conn->real_escape_string($normalized_level)."' 
								   AND tc.grado = '".$conn->real_escape_string($grado)."' 
								   AND tc.seccion = '".$conn->real_escape_string($seccion)."' 
								   GROUP BY ac.id 
								   ORDER BY ac.name");
			}
			
			if ($q && $q->num_rows > 0) {
				while($row = $q->fetch_assoc()) {
					$courses[] = [
						'id' => $row['id'],
						'name' => $row['name']
					];
				}
			}
		}
		
		echo json_encode(['status' => 1, 'courses' => $courses]);
		exit;
	}
} catch (Throwable $e) {
	error_log("Error en el servidor: " . $e->getMessage());
	header('Content-Type: application/json');
	echo json_encode(['status' => 0, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
	exit;
}
ob_end_flush();
?>
