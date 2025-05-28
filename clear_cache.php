<?php
// Limpiar caché de PHP
clearstatcache();

// Limpiar cualquier salida previa
ob_clean();
ob_end_flush();

// Desactivar caché del navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Forzar recarga de CSS y JS
$version = time();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Limpiar Caché</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #28a745;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Caché Limpiada Correctamente</h1>
        <p>Se ha limpiado la caché del sistema. Esto debería resolver cualquier problema con cambios que no se visualizan.</p>
        
        <h2>Información del Sistema</h2>
        <ul>
            <li>Versión de PHP: <?php echo phpversion(); ?></li>
            <li>Servidor: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
            <li>Marca de tiempo: <?php echo date('Y-m-d H:i:s'); ?></li>
        </ul>
        
        <h2>Pasos adicionales recomendados:</h2>
        <ol>
            <li>Limpia la caché de tu navegador (Ctrl+F5 en la mayoría de navegadores)</li>
            <li>Reinicia el servidor web si es posible</li>
            <li>Verifica que los permisos de los archivos sean correctos</li>
        </ol>
        
        <a href="index.php?page=courses&v=<?php echo $version; ?>" class="btn">Volver a Conceptos de Pago</a>
    </div>
</body>
</html>
