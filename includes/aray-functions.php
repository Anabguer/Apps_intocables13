<?php
require_once 'config.php';

/**
 * Obtener todos los años de Aray activos ordenados
 */
function getArayYears() {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM aray_years WHERE activo = 1 ORDER BY year DESC, orden ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error en getArayYears: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener trimestres de un año específico
 */
function getArayTrimestres($yearId) {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM aray_trimestres WHERE year_id = ? AND activo = 1 ORDER BY orden ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$yearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error en getArayTrimestres: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener un año específico por ID
 */
function getArayYear($id) {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM aray_years WHERE id = ? AND activo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error en getArayYear: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener un trimestre específico por ID
 */
function getArayTrimestre($id) {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT t.*, y.year FROM aray_trimestres t 
                JOIN aray_years y ON t.year_id = y.id 
                WHERE t.id = ? AND t.activo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error en getArayTrimestre: " . $e->getMessage());
        return false;
    }
}

/**
 * Crear un nuevo año de Aray
 */
function createArayYear($data) {
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO aray_years (year, image, es_pagina_intermedia, orden, activo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['year'],
            $data['image'],
            $data['es_pagina_intermedia'] ?? true,
            $data['orden'] ?? 1,
            $data['activo'] ?? true
        ]);
        return $result ? $pdo->lastInsertId() : false;
    } catch (Exception $e) {
        error_log("Error en createArayYear: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar un año de Aray
 */
function updateArayYear($id, $data) {
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE aray_years SET year = ?, image = ?, es_pagina_intermedia = ?, orden = ?, activo = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['year'],
            $data['image'],
            $data['es_pagina_intermedia'] ?? true,
            $data['orden'] ?? 1,
            $data['activo'] ?? true,
            $id
        ]);
    } catch (Exception $e) {
        error_log("Error en updateArayYear: " . $e->getMessage());
        return false;
    }
}

/**
 * Eliminar un año de Aray (soft delete)
 */
function deleteArayYear($id) {
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE aray_years SET activo = 0 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("Error en deleteArayYear: " . $e->getMessage());
        return false;
    }
}

/**
 * Crear un nuevo trimestre de Aray
 */
function createArayTrimestre($data) {
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO aray_trimestres (year_id, trimestre, titulo, url_fotos, url_video, tipo_url_fotos, orden, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['year_id'],
            $data['trimestre'],
            $data['titulo'],
            $data['url_fotos'],
            $data['url_video'] ?? null,
            $data['tipo_url_fotos'],
            $data['orden'] ?? 1,
            $data['activo'] ?? true
        ]);
        return $result ? $pdo->lastInsertId() : false;
    } catch (Exception $e) {
        error_log("Error en createArayTrimestre: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar un trimestre de Aray
 */
function updateArayTrimestre($id, $data) {
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE aray_trimestres SET year_id = ?, trimestre = ?, titulo = ?, url_fotos = ?, url_video = ?, tipo_url_fotos = ?, orden = ?, activo = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['year_id'],
            $data['trimestre'],
            $data['titulo'],
            $data['url_fotos'],
            $data['url_video'] ?? null,
            $data['tipo_url_fotos'],
            $data['orden'] ?? 1,
            $data['activo'] ?? true,
            $id
        ]);
    } catch (Exception $e) {
        error_log("Error en updateArayTrimestre: " . $e->getMessage());
        return false;
    }
}

/**
 * Eliminar un trimestre de Aray (soft delete)
 */
function deleteArayTrimestre($id) {
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE aray_trimestres SET activo = 0 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        error_log("Error en deleteArayTrimestre: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener estadísticas de Aray
 */
function getArayStats() {
    try {
        $pdo = getDBConnection();
        
        // Contar años activos
        $sql1 = "SELECT COUNT(*) as total_years FROM aray_years WHERE activo = 1";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute();
        $totalYears = $stmt1->fetch(PDO::FETCH_ASSOC)['total_years'];
        
        // Contar trimestres activos
        $sql2 = "SELECT COUNT(*) as total_trimestres FROM aray_trimestres WHERE activo = 1";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute();
        $totalTrimestres = $stmt2->fetch(PDO::FETCH_ASSOC)['total_trimestres'];
        
        // Contar trimestres con video
        $sql3 = "SELECT COUNT(*) as trimestres_con_video FROM aray_trimestres WHERE activo = 1 AND url_video IS NOT NULL AND url_video != ''";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute();
        $trimestresConVideo = $stmt3->fetch(PDO::FETCH_ASSOC)['trimestres_con_video'];
        
        return [
            'total_years' => $totalYears,
            'total_trimestres' => $totalTrimestres,
            'trimestres_con_video' => $trimestresConVideo
        ];
    } catch (Exception $e) {
        error_log("Error en getArayStats: " . $e->getMessage());
        return [
            'total_years' => 0,
            'total_trimestres' => 0,
            'trimestres_con_video' => 0
        ];
    }
}
?>
