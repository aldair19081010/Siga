<?php
// Verificador de versiones de PHP y compatibilidad
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Verificador de Compatibilidad PHP</h1>";

// Información básica de PHP
echo "<h2>Información de PHP</h2>";
echo "<ul>";
echo "<li><b>Versión de PHP:</b> " . PHP_VERSION . "</li>";
echo "<li><b>Servidor:</b> " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li><b>Sistema operativo:</b> " . PHP_OS . "</li>";
echo "</ul>";

// Verificar versión para Composer
echo "<h2>Compatibilidad con Composer</h2>";
if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
    echo "<p style='color:green;'>✅ Tu versión de PHP (" . PHP_VERSION . ") es compatible con las dependencias que requieren PHP 8.2.0+</p>";
} else {
    echo "<p style='color:red;'>❌ Tu versión de PHP (" . PHP_VERSION . ") es menor que la requerida (8.2.0)</p>";
    echo "<p>Opciones para solucionar:</p>";
    echo "<ol>";
    echo "<li>Actualiza PHP a la versión 8.2.0 o superior en tu hosting</li>";
    echo "<li>Modifica el archivo composer.json para utilizar dependencias compatibles con tu versión de PHP</li>";
    echo "<li>Contacta a tu proveedor de hosting para actualizar la versión de PHP</li>";
    echo "</ol>";
}

// Verificar extensiones importantes
$extensiones_necesarias = ['mysqli', 'json', 'session', 'pdo', 'mbstring'];
echo "<h2>Extensiones requeridas</h2>";
echo "<ul>";
foreach ($extensiones_necesarias as $ext) {
    if (extension_loaded($ext)) {
        echo "<li style='color:green;'>✅ $ext: Disponible</li>";
    } else {
        echo "<li style='color:red;'>❌ $ext: No disponible</li>";
    }
}
echo "</ul>";

// Sugerencias
echo "<h2>Recomendaciones</h2>";
echo "<p>Si no puedes actualizar PHP, puedes:</p>";
echo "<ol>";
echo "<li>Ejecutar <code>composer update --ignore-platform-reqs</code> para ignorar los requisitos de plataforma</li>";
echo "<li>O modificar tu <code>composer.json</code> como se muestra a continuación:</li>";
echo "</ol>";

echo "<pre style='background-color:#f8f8f8; padding:10px; border:1px solid #ddd;'>";
echo '{
    "require": {
        "php": ">='. PHP_VERSION .'"
    },
    "config": {
        "platform": {
            "php": "'. PHP_VERSION .'"
        }
    }
}';
echo "</pre>";
echo "<p>Luego ejecuta <code>composer update</code></p>";

echo "<p><i>Nota: El archivo debe estar protegido. Elimínalo después de usar.</i></p>";
?>
