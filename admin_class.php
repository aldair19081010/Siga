<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login() {
        extract($_POST);

        // Validar que los campos no estén vacíos
        if (empty($username) || empty($password)) {
            return json_encode(['status' => 0, 'message' => 'Por favor, complete todos los campos.']);
        }

        // Escapar los valores para evitar inyección SQL
        $username = $this->db->real_escape_string($username);
        $qry = $this->db->query("SELECT * FROM users WHERE username = '$username'");
        if ($qry && $qry->num_rows > 0) {
            $row = $qry->fetch_array();
            if ($row['password'] === md5($password)) {
                foreach ($row as $key => $value) {
                    if ($key != 'password' && !is_numeric($key)) {
                        $_SESSION['login_' . $key] = $value;
                    }
                }
                $_SESSION['login_id'] = $row['id'];
                return json_encode(['status' => 1, 'message' => 'Inicio de sesión exitoso.']);
            } else {
                return json_encode(['status' => 0, 'message' => 'Credenciales incorrectas.']);
            }
        } else {
            return json_encode(['status' => 0, 'message' => 'Credenciales incorrectas.']);
        }
    }

	function login2(){
		
		extract($_POST);		
		$qry = $this->db->query("SELECT * FROM complainants where email = '".$email."' and password = '".$password."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user() {
    extract($_POST);
    $data = " name = '$name', username = '$username' ";
    if (!empty($password)) {
        $hashed_password = md5($password);
        $data .= ", password = '$hashed_password' ";
    }
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $avatar = strtotime(date('Y-m-d H:i')) . '_' . $_FILES['avatar']['name'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], 'assets/uploads/' . $avatar);
        $data .= ", avatar = '$avatar' ";
        
        // Update the session with the new avatar
        $_SESSION['login_avatar'] = $avatar;
    }
    $chk = $this->db->query("SELECT * FROM users WHERE username = '$username' AND id != '$id'")->num_rows;
    if ($chk > 0) {
        return 2; // Usuario ya existe
    }
    if (empty($id)) {
        $save = $this->db->query("INSERT INTO users SET $data");
    } else {
        $save = $this->db->query("UPDATE users SET $data WHERE id = $id");
    }
    return $save ? 1 : 0;
	}

	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function signup(){
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", email = '$email' ";
		$data .= ", address = '$address' ";
		$data .= ", contact = '$contact' ";
		$data .= ", password = '".$password."' ";
		$chk = $this->db->query("SELECT * from complainants where email ='$email' ".(!empty($id) ? " and id != '$id' " : ''))->num_rows;
		if($chk > 0){
			return 3;
			exit;
		}
		if(empty($id))
			$save = $this->db->query("INSERT INTO complainants set $data");
		else
			$save = $this->db->query("UPDATE complainants set $data where id=$id ");
		if($save){
			if(empty($id))
				$id = $this->db->insert_id;
				$qry = $this->db->query("SELECT * FROM complainants where id = $id ");
				if($qry->num_rows > 0){
					foreach ($qry->fetch_array() as $key => $value) {
						if($key != 'password' && !is_numeric($key))
							$_SESSION['login_'.$key] = $value;
					}
						return 1;
				}else{
					return 3;
				}
		}
	}
	function update_account(){
		extract($_POST);
		$data = " name = '".$firstname.' '.$lastname."' ";
		$data .= ", username = '$email' ";
		if(!empty($password)) {
			$hashed_password = md5($password);
			$data .= ", password = '$hashed_password' ";
		}
		$chk = $this->db->query("SELECT * FROM users where username = '$email' and id != '{$_SESSION['login_id']}' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
		$save = $this->db->query("UPDATE users set $data where id = '{$_SESSION['login_id']}' ");
		if($save){
			$data = '';
			foreach($_POST as $k => $v){
				if($k =='password')
					continue;
				if(empty($data) && !is_numeric($k) )
					$data = " $k = '$v' ";
				else
					$data .= ", $k = '$v' ";
			}
			if($_FILES['img']['tmp_name'] != ''){
				$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
				$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
				$data .= ", avatar = '$fname' ";
			}
			$save_alumni = $this->db->query("UPDATE alumnus_bio set $data where id = '{$_SESSION['bio']['id']}' ");
			if($data){
				foreach ($_SESSION as $key => $value) {
					unset($_SESSION[$key]);
				}
				$login = $this->login2();
				if($login)
					return 1;
			}
		}
	}

	function save_settings(){
		extract($_POST);
		$data = " name = '".str_replace("'","&#x2019;",$name)."' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
		if($_FILES['img']['tmp_name'] != ''){
						$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
						$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
					$data .= ", cover_img = '$fname' ";

		}
		
		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
		$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
		foreach ($query as $key => $value) {
			if(!is_numeric($key))
				$_SESSION['system'][$key] = $value;
		}

			return 1;
				}
	}
	function save_course() {
	    extract($_POST);
	    $data = "";
	    foreach ($_POST as $k => $v) {
	        if (!in_array($k, array('id')) && !is_numeric($k)) {
	            // Si es un array (grados), convertir a string separado por coma
	            if ($k == 'grades' && is_array($v)) {
	                $v = implode(',', $v);
	            }
	            if (empty($data)) {
	                $data .= " $k='$v' ";
	            } else {
	                $data .= ", $k='$v' ";
	            }
	        }
	    }

	    // Validar que las variables necesarias estén definidas
	    if (!isset($course) || !isset($level)) {
	        return json_encode(['status' => 0, 'message' => 'Faltan datos requeridos para guardar el curso.']);
	    }

	    // Verificar si el curso y nivel ya existen
	    $check = $this->db->query("SELECT * FROM courses WHERE course ='$course' AND level ='$level' " . (!empty($id) ? " AND id != {$id} " : ''));
	    if ($check->num_rows > 0) {
	        return json_encode(['status' => 2, 'message' => 'El curso y nivel ya existen.']);
	    }

	    if (empty($id)) {
	        // Insertar nuevo curso
	        $save = $this->db->query("INSERT INTO courses SET $data");
	        if ($save) {
	            return json_encode(['status' => 1, 'message' => 'Curso guardado exitosamente.']);
	        }
	    } else {
	        // Actualizar curso existente
	        $save = $this->db->query("UPDATE courses SET $data WHERE id = $id");
	        if ($save) {
	            return json_encode(['status' => 1, 'message' => 'Curso actualizado exitosamente.']);
	        }
	    }

	    return json_encode(['status' => 0, 'message' => 'Error al guardar el curso.']);
	}

	function delete_course(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM courses where id = ".$id);
		$delete2 = $this->db->query("DELETE FROM fees where course_id = ".$id);
		if($delete && $delete2){
			return 1;
		}
	}
	function save_student(){
		extract($_POST);
		
		// Validar que school_id sea el mismo que el del administrador actual
		if ($school_id != $_SESSION['login_school_id']) {
			return json_encode(['status' => 0, 'message' => 'No tiene permiso para crear o editar estudiantes en este colegio.']);
		}
		
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, ['id']) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(empty($id)){
			// Verificar si el ID ya existe en este colegio
			$check = $this->db->query("SELECT * FROM student WHERE id_no = '$id_no' AND school_id = '$school_id'")->num_rows;
			if($check > 0) {
				return 2; // ID ya existe
			}
			
			$save = $this->db->query("INSERT INTO student set $data");
		}else{
			// Asegurarse de que solo edite estudiantes del mismo colegio
			$check = $this->db->query("SELECT * FROM student WHERE id = '$id' AND school_id = '$school_id'")->num_rows;
			if($check <= 0) {
				return 0; // No tiene permiso para editar este estudiante
			}
			
			$save = $this->db->query("UPDATE student set $data where id = $id");
		}
		if($save)
			return 1;
	}
	function delete_student(){
		extract($_POST);
		// Eliminar primero las asistencias relacionadas
		$this->db->query("DELETE FROM asistencia WHERE student_id = ".$id);
		// Eliminar inscripciones y pagos relacionados si aplica
		$this->db->query("DELETE FROM payments WHERE ef_id IN (SELECT id FROM student_ef_list WHERE student_id = ".$id.")");
		$this->db->query("DELETE FROM student_ef_list WHERE student_id = ".$id);
		// Finalmente eliminar el estudiante
		$delete = $this->db->query("DELETE FROM student where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_fees(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'total_fee'){
					$v = str_replace(',', '', $v);
				}
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		// Validar duplicados por combinación de student_id y course_id si ef_no no está definido
		if (isset($ef_no) && $ef_no !== '') {
			$check = $this->db->query("SELECT * FROM student_ef_list WHERE ef_no ='$ef_no' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		} else if (isset($student_id) && isset($course_id)) {
			$check = $this->db->query("SELECT * FROM student_ef_list WHERE student_id ='$student_id' AND course_id ='$course_id' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		} else {
			$check = 0;
		}
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO student_ef_list set $data");
		}else{
			$save = $this->db->query("UPDATE student_ef_list set $data where id = $id");
		}
		if($save)
			return 1;
	}
	function delete_fees(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM student_ef_list where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_payment(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id', 'student_id')) && !is_numeric($k)){
				if($k == 'amount'){
					$v = str_replace(',', '', $v);
				}
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO payments set $data");
			if($save)
				$id= $this->db->insert_id;
		}else{
			$save = $this->db->query("UPDATE payments set $data where id = $id");
		}
		if($save)
			return json_encode(array('ef_id'=>$ef_id, 'pid'=>$id,'status'=>1));
	}
	function delete_payment(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM payments where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_teacher(){
		extract($_POST);
		
		// Validar que school_id sea el mismo que el del administrador actual
		if ($school_id != $_SESSION['login_school_id']) {
			return json_encode(['status'=>0, 'message'=>'No tiene permiso para crear o editar docentes en este colegio.']);
		}
		
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, ['id']) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		
		if(empty($id)){
			// Verificar si el ID ya existe
			$check = $this->db->query("SELECT * FROM teacher WHERE id_no ='$id_no' AND school_id = '$school_id'")->num_rows;
			if($check > 0){
				return json_encode(['status'=>2, 'message'=>'ID ya existe']);
			}
			
			$save = $this->db->query("INSERT INTO teacher set $data");
		}else{
			// Asegurarse de que solo edite docentes del mismo colegio
			$check = $this->db->query("SELECT * FROM teacher WHERE id ='$id' AND school_id = '$school_id'")->num_rows;
			if($check <= 0){
				return json_encode(['status'=>0, 'message'=>'No tiene permiso para editar este docente.']);
			}
			
			$save = $this->db->query("UPDATE teacher set $data where id = $id");
		}
		
		if($save)
			return json_encode(['status'=>1, 'message'=>'Docente guardado exitosamente.']);
		else
			return json_encode(['status'=>0, 'message'=>'Error al guardar los datos.']);
	}
	function delete_teacher(){
		extract($_POST);
		$id = intval($id);
		
		// Obtener el school_id del administrador
		$school_id = $_SESSION['login_school_id'] ?? 0;
		
		// Verificar que el docente pertenezca al colegio del administrador
		$check = $this->db->query("SELECT * FROM teacher WHERE id = $id AND school_id = $school_id");
		if (!$check || $check->num_rows == 0) {
			return json_encode(['status' => 0, 'message' => 'No tiene permiso para eliminar este docente.']);
		}
		
		// Iniciar transacción para asegurar que todas las operaciones sean exitosas
		$this->db->begin_transaction();
		
		try {
			// 1. Eliminar todas las asignaciones de cursos del docente
			$this->db->query("DELETE FROM teacher_courses WHERE teacher_id = $id");
			
			// 2. Eliminar todas las evaluaciones creadas por el docente
			$this->db->query("DELETE FROM evaluations WHERE teacher_id = $id");
			
			// 3. Eliminar el usuario asociado al docente si existe
			$this->db->query("DELETE FROM users WHERE teacher_id = $id");
			
			// 4. Finalmente, eliminar el docente
			$delete = $this->db->query("DELETE FROM teacher WHERE id = $id");
			
			if (!$delete) {
				throw new Exception('Error al eliminar el docente: ' . $this->db->error);
			}
			
			// Si todo va bien, confirmar los cambios
			$this->db->commit();
			return 1;
			
		} catch (Exception $e) {
			// Si hay algún error, revertir los cambios
			$this->db->rollback();
			error_log("Error en delete_teacher: " . $e->getMessage());
			return json_encode(['status' => 0, 'message' => $e->getMessage()]);
		}
	}
	function save_grade() {
		extract($_POST);
		// Si es profesor, forzar el teacher_id de la sesión
		if (isset($_SESSION['login_type']) && $_SESSION['login_type'] == 2 && isset($_SESSION['login_teacher_id'])) {
			$teacher_id = $_SESSION['login_teacher_id'];
		}
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id')) && !is_numeric($k)) {
				if ($k == 'teacher_id' && isset($_SESSION['login_type']) && $_SESSION['login_type'] == 2) {
					$v = $_SESSION['login_teacher_id'];
				}
				if (empty($data)) {
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		if (empty($id)) {
			$save = $this->db->query("INSERT INTO grades SET $data");
		} else {
			$save = $this->db->query("UPDATE grades SET $data WHERE id = $id");
		}
		if ($save)
			return json_encode(['status' => 1, 'message' => 'Nota guardada exitosamente.']);
		return json_encode(['status' => 0, 'message' => 'Error al guardar la nota.']);
	}
	function delete_grade() {
		extract($_POST);
		$delete = $this->db->query("DELETE FROM grades WHERE id = " . $id);
		if ($delete) {
			return json_encode(['status' => 1]);
		}
		return json_encode(['status' => 0, 'message' => 'Error al eliminar la nota.']);
	}

	// Nueva función para asignar cursos a docentes
	function assign_teacher_course() {
		extract($_POST);
		
		// Validar que school_id sea el mismo que el del administrador actual
		if ($school_id != $_SESSION['login_school_id']) {
			return json_encode(['status' => 0, 'message' => 'No tiene permiso para asignar docentes a otro colegio.']);
		}
		
		// Verificar que los campos obligatorios estén presentes
		if (empty($teacher_id) || empty($course_id) || empty($grado) || empty($seccion)) {
			return json_encode(['status' => 0, 'message' => 'Todos los campos son obligatorios']);
		}
		
		// Escapar datos para la base de datos
		$teacher_id = $this->db->real_escape_string($teacher_id);
		$course_id = $this->db->real_escape_string($course_id);
		$grado = $this->db->real_escape_string($grado);
		$seccion = $this->db->real_escape_string($seccion);
		$level = isset($level) ? $this->db->real_escape_string($level) : 'Primaria';
		
		if (empty($id)) {
			// Verificar si ya existe una asignación similar
			$check = $this->db->query("SELECT * FROM teacher_courses 
								  WHERE teacher_id = '$teacher_id' 
								  AND course_id = '$course_id' 
								  AND grado = '$grado' 
								  AND seccion = '$seccion' 
								  AND level = '$level'");
			
			if ($check && $check->num_rows > 0) {
				return json_encode(['status' => 2, 'message' => 'El docente ya está asignado a este curso y grado con esta sección y nivel.']);
			}
			
			// Insertar nueva asignación
			$save = $this->db->query("INSERT INTO teacher_courses (teacher_id, course_id, grado, seccion, school_id, level) 
                                 VALUES ('$teacher_id', '$course_id', '$grado', '$seccion', '$school_id', '$level')");
		} else {
			// Actualizar asignación existente
			$id = $this->db->real_escape_string($id);
			
			// Verificar que esta asignación pertenece al colegio del administrador
			$check_ownership = $this->db->query("SELECT tc.* 
                                           FROM teacher_courses tc 
                                           INNER JOIN teacher t ON tc.teacher_id = t.id
                                           WHERE tc.id = '$id' AND t.school_id = '$school_id'");
			
			if (!$check_ownership || $check_ownership->num_rows === 0) {
				return json_encode(['status' => 0, 'message' => 'No tiene permiso para editar esta asignación.']);
			}
			
			$save = $this->db->query("UPDATE teacher_courses SET 
                                 teacher_id = '$teacher_id', 
                                 course_id = '$course_id', 
                                 grado = '$grado', 
                                 seccion = '$seccion', 
                                 school_id = '$school_id',
                                 level = '$level'
                                 WHERE id = '$id'");
		}
		
		if ($save) {
			return json_encode(['status' => 1]);
		} else {
			return json_encode(['status' => 0, 'message' => 'Error al guardar: ' . $this->db->error]);
		}
	}

	function delete_teacher_course() {
		extract($_POST);
		$delete = $this->db->query("DELETE FROM teacher_courses WHERE id = ".$id);
		if ($delete) {
			return json_encode(['status' => 1]);
		}
		return json_encode(['status' => 0, 'message' => 'Error al eliminar la asignación.']);
	}

	function save_teacher_user() {
		extract($_POST);
		
		// Asegurarnos de que school_id sea un valor numérico válido
		$school_id = intval($school_id);
		
		// Validar que school_id sea el mismo que del administrador actual
		if ($school_id != $_SESSION['login_school_id']) {
			return 3; // Error: Intento de asignar a otro colegio
		}
		
		// Obtener el nombre del docente
		$teacher_info = $this->db->query("SELECT name FROM teacher WHERE id = '$teacher_id' LIMIT 1");
		$teacher_name = "";
		
		if ($teacher_info && $teacher_info->num_rows > 0) {
			$teacher_data = $teacher_info->fetch_assoc();
			$teacher_name = $teacher_data['name'];
		}
		
		if (empty($id)) {
			// Crear nuevo usuario
			$check = $this->db->query("SELECT * FROM users WHERE username = '$username'")->num_rows;
			if ($check > 0) {
				return 2; // Error: Nombre de usuario ya existe
			}
			
			// Hashear la contraseña
			$password = md5($password);
			
			// Insertar usuario asociado al docente y al colegio, incluyendo el nombre
			$save = $this->db->query("INSERT INTO users 
                                 (username, password, name, type, teacher_id, school_id) 
                                 VALUES ('$username', '$password', '$teacher_name', 2, '$teacher_id', '$school_id')");
		} else {
			// Actualizar usuario existente
			$check = $this->db->query("SELECT * FROM users WHERE username = '$username' AND id != '$id'")->num_rows;
			if ($check > 0) {
				return 2; // Error: Nombre de usuario ya existe
			}
			
			// Actualizar sin cambiar la contraseña si está vacía
			if (empty($password)) {
				$save = $this->db->query("UPDATE users 
                                   SET username = '$username', name = '$teacher_name', school_id = '$school_id' 
                                   WHERE id = '$id'");
			} else {
				$password = md5($password);
				$save = $this->db->query("UPDATE users 
                                   SET username = '$username', password = '$password', name = '$teacher_name', school_id = '$school_id' 
                                   WHERE id = '$id'");
			}
		}
		
		if ($save) {
			return 1;
		}
		return 0;
	}

	function save_evaluation() {
    extract($_POST);
    
    $data = [
        "title" => $this->db->real_escape_string($title),
        "description" => $this->db->real_escape_string($description),
        "type" => $this->db->real_escape_string($type),
        // "course_id" => intval($course_id), // Ya no se usa
        // "grado" => $this->db->real_escape_string($grado), // Ya no se usa
        // "seccion" => $this->db->real_escape_string($seccion), // Ya no se usa
        // "level" => isset($level) ? $this->db->real_escape_string($level) : '', // Ya no se usa
        "bimestre" => $this->db->real_escape_string($bimestre),
        "teacher_id" => intval($teacher_id),
        "teacher_course_id" => intval($teacher_course_id)
    ];
    
    $columns = [];
    $values = [];
    $updates = [];
    
    foreach ($data as $key => $value) {
        $columns[] = "`$key`";
        $values[] = "'$value'";
        $updates[] = "`$key` = '$value'";
    }
    
    if (empty($id)) {
        $sql = "INSERT INTO evaluations (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
    } else {
        $id = intval($id);
        $sql = "UPDATE evaluations SET " . implode(", ", $updates) . " WHERE id = $id";
    }
    
    $save = $this->db->query($sql);
    
    if ($save) {
        // Obtener el id de la evaluación (insert o update)
        $eval_id = empty($id) ? $this->db->insert_id : $id;
        // Guardar competencias asociadas
        if (isset($_POST['competencias']) && is_array($_POST['competencias'])) {
            // Eliminar las anteriores
            $this->db->query("DELETE FROM evaluation_competencias WHERE evaluation_id = $eval_id");
            // Insertar las nuevas
            foreach ($_POST['competencias'] as $comp_id) {
                $comp_id = intval($comp_id);
                $this->db->query("INSERT IGNORE INTO evaluation_competencias (evaluation_id, competencia_id) VALUES ($eval_id, $comp_id)");
            }
        }
        return json_encode(['status' => 1, 'message' => 'Evaluación guardada exitosamente.']);
    } else {
        return json_encode(['status' => 0, 'message' => 'Error al guardar la evaluación: ' . $this->db->error]);
    }
}
	function delete_evaluation() {
		extract($_POST);
		$this->db->query("DELETE FROM evaluation_grades WHERE evaluation_id = $id");
		$delete = $this->db->query("DELETE FROM evaluations WHERE id = $id");
		if ($delete) {
			return json_encode(['status' => 1]);
		}
		return json_encode(['status' => 0, 'message' => 'Error al eliminar la evaluación.']);
	}
	function save_evaluation_grades() {
		extract($_POST);
		if (!isset($grades) || !is_array($grades)) {
			return json_encode(['status' => 0, 'message' => 'No hay notas para guardar.']);
		}
		// Nuevo formato: grades[competencia_id][student_id] = grade
		foreach ($grades as $comp_id => $stu_grades) {
            $comp_id = intval($comp_id);
            if (!is_array($stu_grades)) continue;
            foreach ($stu_grades as $student_id => $grade) {
                $student_id = intval($student_id);
                $grade = floatval($grade);
                // Verificar si ya existe
                $q = $this->db->query("SELECT id FROM evaluation_grades WHERE evaluation_id = '$evaluation_id' AND student_id = '$student_id' AND competencia_id = '$comp_id'");
                if ($q && $q->num_rows > 0) {
                    $this->db->query("UPDATE evaluation_grades SET grade = '$grade' WHERE evaluation_id = '$evaluation_id' AND student_id = '$student_id' AND competencia_id = '$comp_id'");
                } else {
                    $this->db->query("INSERT INTO evaluation_grades (evaluation_id, student_id, competencia_id, grade) VALUES ('$evaluation_id', '$student_id', '$comp_id', '$grade')");
                }
            }
        }
        return json_encode(['status' => 1, 'message' => 'Notas guardadas exitosamente.']);
	}
}