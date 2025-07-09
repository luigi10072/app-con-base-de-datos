<?php
header('Content-Type: application/json');
require_once('../database.php'); // Ajusta esta ruta si es necesario
require_once('auth_middleware.php'); // Asegúrate de que este archivo exista para la autenticación

try {
    $pdo = conectarDB();
    $authResult = authenticate($pdo);
    if ($authResult['status'] === 'error') {
        http_response_code(401);
        echo json_encode(['message' => $authResult['message']]);
        exit();
    }

    $search = $_GET['search'] ?? '';
    $limit = $_GET['limit'] ?? 10;
    $offset = $_GET['offset'] ?? 0;
    $sort = $_GET['sort'] ?? 'IDpoke';
    $order = $_GET['order'] ?? 'asc';

    // Saneamiento y validación de entradas
    $limit = max(1, (int)$limit);
    $offset = max(0, (int)$offset);
    // Eliminar 'is_dual_type' de los campos de ordenación válidos
    $sort = in_array($sort, ['IDpoke', 'pokename', 'HP', 'attack', 'defense', 'spattack', 'spdefense', 'speed']) ? $sort : 'IDpoke';
    $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';

    $conditions = [];
    $params = [];

    // Lógica de búsqueda: por ID exacto o por nombre (pokename)
    if (is_numeric($search) && $search == (int)$search) {
        $conditions[] = "IDpoke = :id_search";
        $params[':id_search'] = (int)$search;
    } elseif (!empty($search)) {
        $searchParam = '%' . $search . '%';
        $conditions[] = "pokename LIKE :search";
        $params[':search'] = $searchParam;
    }

    $whereClause = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Contar el total de registros filtrados
    $countSql = "SELECT COUNT(IDpoke) FROM pokemon $whereClause";
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $totalRecords = $stmtCount->fetchColumn();

    // Obtener los registros con los campos simplificados (sin is_dual_type)
    $sql = "SELECT IDpoke AS numero, pokename AS especie, HP, attack, defense, spattack, spdefense, speed
            FROM pokemon
            $whereClause
            ORDER BY $sort $order
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['records' => $records, 'totalRecords' => $totalRecords]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server error: ' . $e->getMessage()]);
}
?>