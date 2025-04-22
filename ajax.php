<?php
ob_start();
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

include 'db_connect.php';
$action = $_GET['action'] ?? null;
include 'admin_class.php';
$crud = new Action();

try {
	if ($action == 'login') {
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		extract($_POST);

		// Validar que los campos no estén vacíos
		if (empty($username) || empty($password)) {
			echo json_encode(['status' => 0, 'message' => 'Por favor, complete todos los campos.']);
			exit;
		}

		// Escapar los valores para evitar inyección SQL
		$username = $conn->real_escape_string($username);
		$password = md5($conn->real_escape_string($password)); // Hashear la contraseña con MD5

		$qry = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");
		if ($qry && $qry->num_rows > 0) {
			$row = $qry->fetch_array();
			foreach ($row as $k => $v) {
				if (!is_numeric($k) && $k != 'password') {
					$_SESSION['login_' . $k] = $v;
				}
			}
			$_SESSION['login_id'] = $row['id']; // Asegúrate de establecer `login_id`
			echo json_encode(['status' => 1, 'message' => 'Inicio de sesión exitoso.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Credenciales incorrectas.']);
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
		$save = $crud->save_user();
		echo $save ? $save : 0; // Asegurarse de devolver una respuesta clara
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
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		$save = $crud->save_course();
		if ($save == 1) {
			echo json_encode(['status' => 1, 'message' => 'Curso guardado exitosamente.']);
		} elseif ($save == 2) {
			echo json_encode(['status' => 2, 'message' => 'El curso y nivel ya existen.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al guardar el curso.']);
		}
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
		$delete = $crud->delete_student();
		header('Content-Type: application/json'); // Asegurar el encabezado JSON
		if ($delete == 1) {
			echo json_encode(['status' => 1, 'message' => 'Estudiante eliminado exitosamente.']);
		} else {
			echo json_encode(['status' => 0, 'message' => 'Error al eliminar el estudiante.']);
		}
		exit;
	}
	if ($action == "save_fees") {
		$save = $crud->save_fees();
		echo $save ? $save : 0; // Asegurarse de devolver una respuesta clara
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
		if (isset($_FILES['excel_file']['tmp_name']) && !empty($_FILES['excel_file']['tmp_name'])) {
			$file = $_FILES['excel_file']['tmp_name'];
			try {
				// Validar que el archivo sea un Excel
				$fileType = IOFactory::identify($file);
				if (!in_array($fileType, ['Xlsx', 'Xls'])) {
					throw new Exception("El archivo no es un formato Excel válido.");
				}

				$spreadsheet = IOFactory::load($file);
				$sheet = $spreadsheet->getActiveSheet();
				$data = $sheet->toArray();

				// Validar que el archivo tenga datos
				if (count($data) <= 1) {
					throw new Exception("El archivo está vacío o no tiene datos válidos.");
				}

				$conn->begin_transaction();
				foreach ($data as $index => $row) {
					if ($index == 0) continue; // Saltar encabezados

					// Validar que las columnas requeridas no estén vacías
					if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4]) || empty($row[5]) || empty($row[6])) {
						throw new Exception("Fila $index: Hay columnas requeridas vacías.");
					}

					$id_no = $conn->real_escape_string(trim($row[0]));
					$name = $conn->real_escape_string(trim($row[1]));
					$email = $conn->real_escape_string(trim($row[2]));
					$contact = $conn->real_escape_string(trim($row[3]));
					$address = $conn->real_escape_string(trim($row[4]));
					$nivel = $conn->real_escape_string(trim($row[5]));
					$grado = $conn->real_escape_string(trim($row[6]));

					// Validar valores permitidos para `nivel`
					if (!in_array($nivel, ['Inicial', 'Primaria', 'Secundaria'])) {
						throw new Exception("Fila $index: El nivel '$nivel' no es válido.");
					}

					// Validar formato de `grado`
					if (!preg_match('/^[1-6]°$/', $grado)) {
						throw new Exception("Fila $index: El grado '$grado' no es válido.");
					}

					// Validar duplicados en la base de datos
					$check = $conn->query("SELECT id FROM student WHERE id_no = '$id_no'");
					if ($check === false) {
						throw new Exception("Error al verificar duplicados: " . $conn->error);
					}
					if ($check->num_rows > 0) {
						throw new Exception("Fila $index: El ID '$id_no' ya existe en la base de datos.");
					}

					// Insertar datos en la tabla `student`
					$query = "INSERT INTO student (id_no, name, email, contact, address, nivel, grado) 
							  VALUES ('$id_no', '$name', '$email', '$contact', '$address', '$nivel', '$grado')";
					if (!$conn->query($query)) {
						throw new Exception("Error al insertar datos en la fila $index: " . $conn->error);
					}
				}
				$conn->commit();
				echo json_encode(['status' => 1, 'message' => 'Datos subidos exitosamente.']);
			} catch (Exception $e) {
				$conn->rollback();
				error_log("Error al procesar el archivo Excel: " . $e->getMessage());
				echo json_encode(['status' => 0, 'message' => 'Ocurrió un error al procesar el archivo: ' . $e->getMessage()]);
			}
		} else {
			error_log("No se subió ningún archivo o el archivo está vacío.");
			echo json_encode(['status' => 0, 'message' => 'No se subió ningún archivo.']);
		}
	}

	if ($action == 'upload_payment_excel') {
		if (isset($_FILES['excel_file']['tmp_name']) && !empty($_FILES['excel_file']['tmp_name'])) {
			$file = $_FILES['excel_file']['tmp_name'];
			try {
				// Validar que el archivo sea un Excel
				$fileType = IOFactory::identify($file);
				if (!in_array($fileType, ['Xlsx', 'Xls'])) {
					throw new Exception("El archivo no es un formato Excel válido.");
				}

				$spreadsheet = IOFactory::load($file);
				$sheet = $spreadsheet->getActiveSheet();
				$data = $sheet->toArray();

				// Validar que el archivo tenga datos
				if (count($data) <= 1) {
					throw new Exception("El archivo está vacío o no tiene datos válidos.");
				}

				$conn->begin_transaction();
				foreach ($data as $index => $row) {
					if ($index == 0) continue; // Saltar encabezados

					// Validar que las columnas requeridas no estén vacías
					if (empty($row[0]) || empty($row[1])) {
						throw new Exception("Fila $index: Hay columnas requeridas vacías.");
					}

					$id_no = $conn->real_escape_string(trim($row[0])); // DNI del estudiante
					$course_id = $conn->real_escape_string(trim($row[1])); // Código del concepto de pago

					// Validar que el estudiante exista
					$student_check = $conn->query("SELECT id FROM student WHERE id_no = '$id_no'");
					if ($student_check === false) {
						throw new Exception("Error al verificar el estudiante: " . $conn->error);
					}
					if ($student_check->num_rows == 0) {
						throw new Exception("Fila $index: El estudiante con DNI '$id_no' no existe.");
					}
					$student_id = $student_check->fetch_assoc()['id'];

					// Validar que el concepto de pago exista
					$course_check = $conn->query("SELECT id, total_amount FROM courses WHERE id = '$course_id'");
					if ($course_check === false) {
						throw new Exception("Error al verificar el concepto de pago: " . $conn->error);
					}
					if ($course_check->num_rows == 0) {
						throw new Exception("Fila $index: El concepto de pago con ID '$course_id' no existe.");
					}
					$course = $course_check->fetch_assoc();
					$total_fee = $course['total_amount'];

					 // Verificar si el estudiante ya tiene registrado este concepto de pago
					$existing_entry = $conn->query("SELECT id FROM student_ef_list WHERE student_id = '$student_id' AND course_id = '$course_id'");
					if ($existing_entry === false) {
						throw new Exception("Error al verificar entradas existentes: " . $conn->error);
					}
					if ($existing_entry->num_rows > 0) {
						echo json_encode(['status' => 0, 'message' => "Fila $index: El estudiante ya tiene registrado este concepto de pago."]);
						exit;
					}

					// Insertar datos en la tabla `student_ef_list`
					$insert = $conn->query("INSERT INTO student_ef_list (student_id, course_id, total_fee) 
									  VALUES ('$student_id', '$course_id', '$total_fee')");
					if ($insert === false) {
						throw new Exception("Error al insertar datos en la fila $index: " . $conn->error);
					}
				}
				$conn->commit();
				echo json_encode(['status' => 1, 'message' => 'Pagos subidos exitosamente.']);
			} catch (Exception $e) {
				$conn->rollback();
				error_log("Error al procesar el archivo Excel: " . $e->getMessage());
				header('Content-Type: application/json');
				echo json_encode(['status' => 0, 'message' => $e->getMessage()]);
				exit;
			}
		} else {
			error_log("No se subió ningún archivo o el archivo está vacío.");
			echo json_encode(['status' => 0, 'message' => 'No se subió ningún archivo.']);
		}
	} else {
		header('Content-Type: application/json');
		echo json_encode(['status' => 0, 'message' => 'Acción no válida.']);
		exit;
	}
} catch (Throwable $e) {
	error_log("Error en el servidor: " . $e->getMessage());
	header('Content-Type: application/json');
	echo json_encode(['status' => 0, 'message' => 'Error interno del servidor.']);
	exit;
}
ob_end_flush();
?>
