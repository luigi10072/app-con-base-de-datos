<?php
// database.php
function conectarDB() {
    $host = getenv('MYSQLHOST') ?: 'mysql.railway.internal'; // O la IP de tu servidor de base de datos
    $port = getenv('MYSQLPORT') ?: '3306'; // <-- ¡AÑADE ESTE PUNTO Y COMA!
    $db = getenv('MYSQL_DATABASE') ?: 'railway';     // El nombre de la base de datos que creaste
    $user = getenv('MYSQLUSER') ?: 'root';      // Tu usuario de MySQL
    $pass = getenv('MYSQLPASSWORD') ?: 'IwSjZUqVxDIceOLuBBaPKzUPvfoCHlQv';          // Tu contraseña de MySQL (vacía si no tienes)

    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage()); // <-- ¡AÑADE ESTE PUNTO Y COMA!
        throw new Exception("Error al conectar con la base de datos. Por favor, inténtelo de nuevo más tarde.");
    }
}
?>
