<?php
// index.php
// Este es un archivo de prueba para verificar si PHP está funcionando.
echo "<h1>¡Hola desde PHP en Railway!</h1>";
echo "<p>Si ves esto, PHP está funcionando correctamente.</p>";

// Intenta incluir tu database.php para una prueba más avanzada
try {
    require_once('database.php'); // Asume que database.php está en la misma raíz
    $pdo = conectarDB();
    echo "<p>Conexión a la base de datos exitosa.</p>";
    // Puedes intentar una consulta simple si quieres
    // $stmt = $pdo->query("SELECT 1");
    // $result = $stmt->fetchColumn();
    // echo "<p>Resultado de consulta simple: " . $result . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al conectar a la base de datos: " . $e->getMessage() . "</p>";
}

echo "<p>Ahora intenta acceder a <a href='api/data.php'>api/data.php</a> (si tienes un token válido).</p>";
?>
