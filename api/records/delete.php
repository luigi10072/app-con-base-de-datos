<?php
header('Content-Type: application/json');
// Ajusta esta ruta si es necesario. Desde 'api/records/' sube dos niveles para 'database.php' en la raíz.
require_once('../../database.php');
// Ajusta esta ruta si es necesario. Desde 'api/records/' sube un nivel para 'auth_middleware.php' en 'api/'.
require_once('../auth_middleware.php');

// Manejar la solicitud OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Validar el token de autenticación
$authResult = authenticate(conectarDB()); // Pasa la conexión PDO a la función authenticate
if ($authResult['status'] === 'error') {
    http_response_code(401);
    echo json_encode(['message' => $authResult['message']]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // El frontend envía DELETE como POST con JSON
    global $pdo; // Acceder a la conexión PDO global

    // Leer el cuerpo de la solicitud cruda (los datos JSON)
    $input = file_get_contents('php://input');
    $data = json_decode($input, true); // Decodificar JSON como array asociativo

    // Validar que el IDpoke esté presente
    if (!isset($data['IDpoke'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'ID de Pokémon es requerido para la eliminación.']);
        exit();
    }

    $IDpoke = (int) $data['IDpoke'];

    try {
        $pdo = conectarDB(); // Asegurarse de que $pdo esté definido aquí
        $pdo->beginTransaction();

        // Eliminar el Pokémon de la tabla principal
        $sql = "DELETE FROM pokemon WHERE IDpoke = :idpoke";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idpoke', $IDpoke, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $pdo->commit(); // Confirmar la transacción si se eliminó al menos una fila
            http_response_code(200);
            echo json_encode(['message' => 'Registro eliminado exitosamente.']);
        } else {
            $pdo->rollBack(); // Revertir si no se encontró el registro
            http_response_code(404); // Not Found
            echo json_encode(['message' => 'Registro no encontrado.']);
        }

    } catch (PDOException $e) {
        $pdo->rollBack(); // Revertir la transacción en caso de error de DB
        http_response_code(500);
        echo json_encode(['message' => 'Error de base de datos al eliminar: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error del servidor al eliminar: ' . $e->getMessage()]);
    }
} else {
    // Si el método no es POST, devolver un error
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Método no permitido.']);
}
?>
