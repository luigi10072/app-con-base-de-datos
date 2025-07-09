<?php
header('Content-Type: application/json');
require_once('../../database.php'); // Ajusta esta ruta si es necesario
require_once('../auth_middleware.php'); // Asegúrate de que este archivo exista

$data = json_decode(file_get_contents('php://input'), true);

// Validar que todos los campos necesarios estén presentes (sin isDualType)
if (!isset($data['pokename'], $data['HP'], $data['attack'], $data['defense'],
            $data['spattack'], $data['spdefense'], $data['speed'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Faltan datos para añadir el Pokémon.']);
    exit();
}

try {
    $pdo = conectarDB();
    $authResult = authenticate($pdo);
    if ($authResult['status'] === 'error') {
        http_response_code(401);
        echo json_encode(['message' => $authResult['message']]);
        exit();
    }

    $pdo->beginTransaction();

    // Insertar en la tabla pokemon con los campos simplificados (sin is_dual_type)
    $sql = "INSERT INTO pokemon (pokename, HP, attack, defense, spattack, spdefense, speed)
            VALUES (:pokename, :hp, :attack, :defense, :spattack, :spdefense, :speed)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pokename', $data['pokename']);
    $stmt->bindParam(':hp', $data['HP'], PDO::PARAM_INT);
    $stmt->bindParam(':attack', $data['attack'], PDO::PARAM_INT);
    $stmt->bindParam(':defense', $data['defense'], PDO::PARAM_INT);
    $stmt->bindParam(':spattack', $data['spattack'], PDO::PARAM_INT);
    $stmt->bindParam(':spdefense', $data['spdefense'], PDO::PARAM_INT);
    $stmt->bindParam(':speed', $data['speed'], PDO::PARAM_INT);
    $stmt->execute();
    
    $pdo->commit();
    echo json_encode(['message' => 'Pokémon añadido exitosamente.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Error al añadir el Pokémon: ' . $e->getMessage()]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>