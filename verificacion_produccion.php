<?php
/**
 * Verificación pre-producción
 * Ejecuta este archivo para comprobar posibles problemas antes de subir al host
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Verificación Pre-Producción</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    .success { color: green; }
    .warning { color: orange; }
    .error { color: red; }
    .section { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; }
</style>";

// 1. Verificar conexión a base de datos
echo "<div class='section'>";
echo "<h2>1. Conexión a base de datos</h2>";
include 'db_connect.php';

if ($conn && !$conn->connect_error) {
    echo "<p class='success'>✅ Conexión a la base de datos exitosa</p>";
    
    // Verificar tablas importantes
    $tablas_requeridas = ['payments', 'schools', 'system_settings', 'users'];
    $tablas_faltantes = [];
    
    foreach ($tablas_requeridas as $tabla) {
        $result = $conn->query("SHOW TABLES LIKE '$tabla'");
        if ($result->num_rows == 0) {
            $tablas_faltantes[] = $tabla;
        }
    }
    
    if (empty($tablas_faltantes)) {
        echo "<p class='success'>✅ Todas las tablas principales están presentes</p>";
    } else {
        echo "<p class='error'>❌ Faltan las siguientes tablas: " . implode(", ", $tablas_faltantes) . "</p>";
    }
} else {
    echo "<p class='error'>❌ Error de conexión a la base de datos: " . ($conn ? $conn->connect_error : "No se pudo crear la conexión") . "</p>";
}
echo "</div>";

// 2. Verificar directorios y permisos
echo "<div class='section'>";
echo "<h2>2. Directorios y permisos</h2>";
$directorios = [
    'assets/uploads' => 0755,
    'assets/img' => 0755,
    'temp' => 0755,
];

foreach ($directorios as $dir => $perm) {
    if (!file_exists($dir)) {
        echo "<p class='warning'>⚠️ El directorio '$dir' no existe. Creando...</p>";
        if (!mkdir($dir, $perm, true)) {
            echo "<p class='error'>❌ No se pudo crear el directorio '$dir'</p>";
        } else {
            echo "<p class='success'>✅ Directorio '$dir' creado con éxito</p>";
        }
    } else {
        echo "<p class='success'>✅ El directorio '$dir' existe</p>";
        
        // Verificar permisos en Unix/Linux
        if (function_exists('posix_getpwuid')) {
            $permisos = substr(sprintf('%o', fileperms($dir)), -4);
            echo "<p>Permisos actuales: $permisos</p>";
        }
        
        // Verificar si es escribible
        if (is_writable($dir)) {
            echo "<p class='success'>✅ El directorio '$dir' tiene permisos de escritura</p>";
        } else {
            echo "<p class='error'>❌ El directorio '$dir' no tiene permisos de escritura</p>";
        }
    }
}
echo "</div>";

// 3. Verificar configuración PHP
echo "<div class='section'>";
echo "<h2>3. Configuración PHP</h2>";
$php_version = phpversion();
echo "<p>Versión de PHP: $php_version</p>";

if (version_compare($php_version, '7.0.0', '<')) {
    echo "<p class='error'>❌ La versión de PHP es antigua. Se recomienda PHP 7.0 o superior.</p>";
} else {
    echo "<p class='success'>✅ La versión de PHP es compatible</p>";
}

$extensiones_requeridas = ['mysqli', 'json', 'session', 'gd', 'curl'];
foreach ($extensiones_requeridas as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ Extensión '$ext' cargada</p>";
    } else {
        echo "<p class='error'>❌ Extensión '$ext' no está cargada</p>";
    }
}

$php_configs = [
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_execution_time' => ini_get('max_execution_time'),
];

echo "<p>Configuraciones PHP importantes:</p>";
echo "<ul>";
foreach ($php_configs as $config => $value) {
    echo "<li>$config: $value</li>";
}
echo "</ul>";
echo "</div>";

// 4. Verificar archivos críticos
echo "<div class='section'>";
echo "<h2>4. Archivos críticos</h2>";
$archivos_criticos = [
    'index.php',
    'login.php',
    'ajax.php',
    'header.php',
    'footer.php',
    'navbar.php',
    'topbar.php',
    '.htaccess'
];

foreach ($archivos_criticos as $archivo) {
    if (file_exists($archivo)) {
        echo "<p class='success'>✅ Archivo '$archivo' presente</p>";
    } else {
        echo "<p class='error'>❌ Archivo '$archivo' faltante</p>";
    }
}
echo "</div>";

// 5. Recomendaciones para producción
echo "<div class='section'>";
echo "<h2>5. Recomendaciones para producción</h2>";
echo "<ul>";
echo "<li>Asegúrate de que las credenciales de base de datos en db_connect.php son correctas para el host.</li>";
echo "<li>Configura correctamente el archivo .htaccess para proteger archivos sensibles.</li>";
echo "<li>Desactiva el modo de depuración en producción.</li>";
echo "<li>Asegúrate de que todas las URLs absolutas apunten al dominio correcto.</li>";
echo "<li>Verifica que todas las rutas de archivos sean relativas o correctamente absolutas.</li>";
echo "<li>No subas directorios innecesarios como .git, node_modules, etc.</li>";
echo "</ul>";
echo "</div>";

// 6. Verificación de seguridad básica
echo "<div class='section'>";
echo "<h2>6. Verificación de seguridad básica</h2>";

if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'php_flag display_errors off') !== false) {
        echo "<p class='success'>✅ .htaccess configurado para ocultar errores en producción</p>";
    } else {
        echo "<p class='warning'>⚠️ Considera ocultar errores en producción mediante .htaccess</p>";
    }
} else {
    echo "<p class='warning'>⚠️ No se encontró archivo .htaccess</p>";
}

if (file_exists('db_connect.php')) {
    $db_content = file_get_contents('db_connect.php');
    if (preg_match('/mysqli.*?\((.*?)\)/s', $db_content, $matches)) {
        echo "<p class='warning'>⚠️ Las credenciales de base de datos están expuestas en db_connect.php</p>";
        echo "<p>Considera usar un archivo .env para mayor seguridad</p>";
    }
}
echo "</div>";

echo "<hr>";
echo "<p><strong>Nota:</strong> Esta verificación no garantiza que todo esté correcto pero ayuda a identificar problemas comunes.</p>";
echo "<p><strong>Importante:</strong> Elimina este archivo antes de subir tu proyecto a producción.</p>";
?>
