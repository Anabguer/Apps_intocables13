<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/aray-functions.php';

header('Content-Type: application/json');

// Verificar permisos de administrador o editor
if (!isLoggedIn() || (!isAdmin() && !isEdit())) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener un año específico
                $year = getArayYear($_GET['id']);
                if ($year) {
                    echo json_encode(['success' => true, 'data' => $year]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Año no encontrado']);
                }
            } else {
                // Obtener todos los años con paginación y filtros
                $pdo = getDBConnection();
                
                // Parámetros de paginación
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = 10;
                $offset = ($page - 1) * $limit;
                
                // Parámetros de ordenación
                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'year';
                $order = isset($_GET['order']) ? $_GET['order'] : 'desc';
                
                // Construir WHERE clause
                $whereConditions = ['activo = 1'];
                $params = [];
                
                if (isset($_GET['year']) && !empty($_GET['year'])) {
                    $whereConditions[] = 'year = :year';
                    $params['year'] = $_GET['year'];
                }
                
                if (isset($_GET['activo']) && $_GET['activo'] !== '') {
                    $whereConditions[] = 'activo = :activo';
                    $params['activo'] = (int)$_GET['activo'];
                }
                
                $whereClause = implode(' AND ', $whereConditions);
                
                // Consulta principal
                $sql = "SELECT * FROM aray_years 
                        WHERE {$whereClause}
                        ORDER BY {$sort} {$order}
                        LIMIT :limit OFFSET :offset";
                
                $stmt = $pdo->prepare($sql);
                foreach ($params as $key => $value) {
                    $stmt->bindValue(":{$key}", $value);
                }
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Contar total para paginación
                $countSql = "SELECT COUNT(*) as total FROM aray_years WHERE {$whereClause}";
                $countStmt = $pdo->prepare($countSql);
                foreach ($params as $key => $value) {
                    $countStmt->bindValue(":{$key}", $value);
                }
                $countStmt->execute();
                $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                $pagination = [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => $total,
                    'items_per_page' => $limit
                ];
                
                echo json_encode([
                    'success' => true, 
                    'data' => $years,
                    'pagination' => $pagination
                ]);
            }
            break;
            
        case 'POST':
            // Crear nuevo año
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['year']) || !isset($data['image'])) {
                echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos']);
                break;
            }
            
            $yearId = createArayYear($data);
            if ($yearId) {
                echo json_encode(['success' => true, 'data' => ['id' => $yearId]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el año']);
            }
            break;
            
        case 'PUT':
            // Actualizar año
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                break;
            }
            
            $result = updateArayYear($data['id'], $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Año actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el año']);
            }
            break;
            
        case 'DELETE':
            // Eliminar año
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                break;
            }
            
            $result = deleteArayYear($data['id']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Año eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el año']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    error_log("Error en aray-years API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
