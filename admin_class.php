<?php
session_start();
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
		$password = md5($this->db->real_escape_string($password)); // Hashear la contraseña con MD5

		$qry = $this->db->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");
		if ($qry && $qry->num_rows > 0) {
			$row = $qry->fetch_array();
			foreach ($row as $key => $value) {
				if ($key != 'password' && !is_numeric($key)) {
					$_SESSION['login_' . $key] = $value;
				}
			}
			$_SESSION['login_id'] = $row['id']; // Asegúrate de establecer `login_id`
			return json_encode(['status' => 1, 'message' => 'Inicio de sesión exitoso.']);
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
	        $hashed_password = md5($password); // Generar el hash de la contraseña
	        $data .= ", password = '$password' "; // Actualizar solo si se proporciona una nueva contraseña
	    }
	    if (!empty($_FILES['avatar']['tmp_name'])) {
	        $avatar = strtotime(date('Y-m-d H:i')) . '_' . $_FILES['avatar']['name'];
	        move_uploaded_file($_FILES['avatar']['tmp_name'], 'assets/uploads/' . $avatar);
	        $data .= ", avatar = '$avatar' ";
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
	    return $save ? 1 : 0; // Devolver 1 si se guarda correctamente, 0 en caso contrario
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
		if(!empty($password))
		$data .= ", password = '".$password."' ";
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
	        if (!in_array($k, array('id', 'fid', 'type', 'amount')) && !is_numeric($k)) {
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
	            $id = $this->db->insert_id;
	            foreach ($fid as $k => $v) {
	                $fee_data = " course_id = '$id' ";
	                $fee_data .= ", description = '{$type[$k]}' ";
	                $fee_data .= ", amount = '{$amount[$k]}' ";
	                $this->db->query("INSERT INTO fees SET $fee_data");
	            }
	            return 1; // Curso guardado exitosamente
	        }
	    } else {
	        // Actualizar curso existente
	        $save = $this->db->query("UPDATE courses SET $data WHERE id = $id");
	        if ($save) {
	            $this->db->query("DELETE FROM fees WHERE course_id = $id AND id NOT IN (" . implode(',', $fid) . ")");
	            foreach ($fid as $k => $v) {
	                $fee_data = " course_id = '$id' ";
	                $fee_data .= ", description = '{$type[$k]}' ";
	                $fee_data .= ", amount = '{$amount[$k]}' ";
	                if (empty($v)) {
	                    $this->db->query("INSERT INTO fees SET $fee_data");
	                } else {
	                    $this->db->query("UPDATE fees SET $fee_data WHERE id = $v");
	                }
	            }
	            return 1; // Curso actualizado exitosamente
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
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM student where id_no ='$id_no' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO student set $data");
		}else{
			$save = $this->db->query("UPDATE student set $data where id = $id");
		}
		if($save)
			return 1;
	}
	function delete_student(){
		extract($_POST);
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
		$check = $this->db->query("SELECT * FROM student_ef_list where ef_no ='$ef_no' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
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
			if(!in_array($k, array('id')) && !is_numeric($k)){
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
}