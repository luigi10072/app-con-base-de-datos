<?php
// api/login.php
header('Content-Type: application/json');
require_once('../database.php'); // Ajusta esta ruta si es necesario
require_once('auth_middleware.php'); // Necesario para generateToken()

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Usuario y contraseña son requeridos.']);
    exit();
}

$username = $data['username'];
$password = $data['password'];

try {
    $pdo = conectarDB();

    // Buscar al usuario
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Credenciales inválidas.']);
        exit();
    }

    // Si las credenciales son correctas, generar un token de sesión
    $token = generateToken();
    $expiresAt = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s'); // Token válido por 1 hora

    // Guardar la sesión en la base de datos
    $stmt = $pdo->prepare("INSERT INTO sessions (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
    $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':expires_at', $expiresAt);
    $stmt->execute();

    echo json_encode(['message' => 'Inicio de sesión exitoso.', 'token' => $token]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>