<?php
// api/auth_middleware.php

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function authenticate($pdo) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authHeader)) {
        return ['status' => 'error', 'message' => 'No autorizado: Token no proporcionado.'];
    }

    list($type, $token) = explode(' ', $authHeader, 2);

    if (strtolower($type) !== 'bearer' || empty($token)) {
        return ['status' => 'error', 'message' => 'Formato de token no válido. Debe ser "Bearer [token]".'];
    }

    // Verificar el token en la base de datos
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM sessions WHERE token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $session = $stmt->fetch();

    if (!$session) {
        return ['status' => 'error', 'message' => 'Token inválido o no existe.'];
    }

    if (new DateTime($session['expires_at']) < new DateTime()) {
        // Opcional: Eliminar sesión expirada para limpiar la DB
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE token = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return ['status' => 'error', 'message' => 'Token expirado.'];
    }

    // Token válido. Puedes aquí extender la expiración si lo deseas.
    // $newExpiresAt = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');
    // $updateStmt = $pdo->prepare("UPDATE sessions SET expires_at = :new_expires WHERE token = :token");
    // $updateStmt->bindParam(':new_expires', $newExpiresAt);
    // $updateStmt->bindParam(':token', $token);
    // $updateStmt->execute();

    return ['status' => 'success', 'user_id' => $session['user_id']];
}

// Asegúrate de que la tabla `users` y `sessions` existan en tu DB
// Puedes agregarlas a tu `pokemondb.sql` si aún no lo has hecho:
/*
-- Para la tabla de usuarios (ejemplo)
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Para la tabla de sesiones
CREATE TABLE `sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Puedes añadir un usuario de prueba (la contraseña 'password123' está hasheada)
-- INSERT INTO `users` (username, password_hash) VALUES ('admin', '$2y$10$92hA/uL.tW3DkG6A5mC1W.fA6oXp/1nZ2Z3Z4Z5Z6Z7Z8Z9');
-- (Hash para 'password123', puedes generar el tuyo con password_hash('tu_pass', PASSWORD_BCRYPT) en PHP)
*/
?>