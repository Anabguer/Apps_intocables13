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
                // Obtener un trimestre específico
                $trimestre = getArayTrimestre($_GET['id']);
                if ($trimestre) {
                    echo json_encode(['success' => true, 'data' => $trimestre]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Trimestre no encontrado']);
                }
            } elseif (isset($_GET['year_id'])) {
                // Obtener trimestres de un año específico
                $trimestres = getArayTrimestres($_GET['year_id']);
                echo json_encode(['success' => true, 'data' => $trimestres]);
            } else {
                // Obtener todos los trimestres con paginación y filtros
                $pdo = getDBConnection();
                
                // Parámetros de paginación
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = 10;
                $offset = ($page - 1) * $limit;
                
                // Parámetros de ordenación
                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'year';
                $order = isset($_GET['order']) ? $_GET['order'] : 'desc';
                
                // Construir WHERE clause
                $whereConditions = ['t.activo = 1'];
                $params = [];
                
                if (isset($_GET['year_id']) && !empty($_GET['year_id'])) {
                    $whereConditions[] = 't.year_id = :year_id';
                    $params['year_id'] = $_GET['year_id'];
                }
                
                if (isset($_GET['titulo']) && !empty($_GET['titulo'])) {
                    $whereConditions[] = 't.titulo LIKE :titulo';
                    $params['titulo'] = '%' . $_GET['titulo'] . '%';
                }
                
                if (isset($_GET['tipo_url_fotos']) && !empty($_GET['tipo_url_fotos'])) {
                    $whereConditions[] = 't.tipo_url_fotos = :tipo_url_fotos';
                    $params['tipo_url_fotos'] = $_GET['tipo_url_fotos'];
                }
                
                if (isset($_GET['activo']) && $_GET['activo'] !== '') {
                    $whereConditions[] = 't.activo = :activo';
                    $params['activo'] = (int)$_GET['activo'];
                }
                
                $whereClause = implode(' AND ', $whereConditions);
                
                // Consulta principal
                $sql = "SELECT t.*, y.year FROM aray_trimestres t 
                        JOIN aray_years y ON t.year_id = y.id 
                        WHERE {$whereClause}
                        ORDER BY {$sort} {$order}, t.orden ASC
                        LIMIT :limit OFFSET :offset";
                
                $stmt = $pdo->prepare($sql);
                foreach ($params as $key => $value) {
                    $stmt->bindValue(":{$key}", $value);
                }
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $trimestres = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Contar total para paginación
                $countSql = "SELECT COUNT(*) as total FROM aray_trimestres t 
                            JOIN aray_years y ON t.year_id = y.id 
                            WHERE {$whereClause}";
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
                    'data' => $trimestres,
                    'pagination' => $pagination
                ]);
            }
            break;
            
        case 'POST':
            // Crear nuevo trimestre
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['year_id']) || !isset($data['trimestre']) || !isset($data['titulo']) || !isset($data['url_fotos']) || !isset($data['tipo_url_fotos'])) {
                echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos']);
                break;
            }
            
            $trimestreId = createArayTrimestre($data);
            if ($trimestreId) {
                echo json_encode(['success' => true, 'data' => ['id' => $trimestreId]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el trimestre']);
            }
            break;
            
        case 'PUT':
            // Actualizar trimestre
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                break;
            }
            
            $result = updateArayTrimestre($data['id'], $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Trimestre actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el trimestre']);
            }
            break;
            
        case 'DELETE':
            // Eliminar trimestre
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                break;
            }
            
            $result = deleteArayTrimestre($data['id']);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Trimestre eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el trimestre']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    error_log("Error en aray-trimestres API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
