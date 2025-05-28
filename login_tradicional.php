<?php
// Inicio de sesión tradicional (no-AJAX) para casos de problemas de compatibilidad
session_start();
include('./db_connect.php');

// Verificar si es un POST de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;
    
    // Validación básica
    if (empty($school_id)) {
        $error = "Debe seleccionar un colegio.";
    } else {
        // Verificar credenciales usando prepared statements
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = MD5(?) AND school_id = ?");
        $stmt->bind_param("ssi", $username, $password, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            // Login exitoso
            $row = $result->fetch_assoc();
            
            // Guardar datos en sesión
            $_SESSION['login_id'] = $row['id'];
            $_SESSION['login_type'] = $row['type'];
            $_SESSION['login_school_id'] = $row['school_id'];
            $_SESSION['login_name'] = $row['name'] ?? 'Usuario';
            
            if(isset($row['avatar']) && !empty($row['avatar'])) {
                $_SESSION['login_avatar'] = $row['avatar'];
            }
            
            if ($row['type'] == 2) { // Docente
                if (!empty($row['teacher_id'])) {
                    $_SESSION['login_teacher_id'] = $row['teacher_id'];
                } else {
                    // Buscar teacher_id
                    $tq_stmt = $conn->prepare("SELECT id FROM teacher WHERE name = ? LIMIT 1");
                    $tq_stmt->bind_param("s", $row['name']);
                    $tq_stmt->execute();
                    $tq_result = $tq_stmt->get_result();
                    if ($tq_result && $tq_result->num_rows > 0) {
                        $_SESSION['login_teacher_id'] = $tq_result->fetch_assoc()['id'];
                    }
                    $tq_stmt->close();
                }
            }
            
            // Forzar guardado de sesión
            session_write_close();
            
            // Redirigir al index
            header("Location: index.php?page=home");
            exit;
        } else {
            $error = "Usuario, contraseña o colegio incorrectos.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-body">
                <h3 class="text-center mb-4">Iniciar Sesión</h3>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="school_id">Colegio</label>
                        <select name="school_id" id="school_id" class="form-control" required>
                            <option value="">Seleccione un colegio</option>
                            <?php
                            $schools = $conn->query("SELECT id, name FROM schools ORDER BY name ASC");
                            while ($row = $schools->fetch_assoc()):
                            ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    </div>
                    
                    <div class="text-center">
                        <a href="login.php">Volver al login normal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
