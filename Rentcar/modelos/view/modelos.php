<?php
// Ruta para incluir RentCarDB.php
$path = __DIR__ . '/../../RentCarDB.php';
if (!file_exists($path)) {
    die("Archivo RentCarDB.php no encontrado en: $path");
}
require_once($path);

header('Access-Control-Allow-Origin: *');
header('Content-Security-Policy: script-src \'self\';');
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    $db = RentCarDB::getInstance()->getConnection();

    switch ($action) {

        case 'get':
            try {
                if (!isset($_POST['idModelo']) || !is_numeric($_POST['idModelo'])) {
                    echo json_encode(['status' => 'error', 'message' => 'ID  Modelo inválido']);
                    exit;
                }
        
                $stmt = $db->prepare("SELECT idModelo,IdMarca,Descripcion, Estado FROM Modelos WHERE idModelo = :idModelo");
                $stmt->execute([':idModelo' => $_POST['idModelo']]);
                $Modelo = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($Modelo) {
                    echo json_encode(['status' => 'success', 'data' => $Modelo]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Modelo no encontrado']);
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
            }
            break;

            case 'read':
                try {
                    $draw = intval($_POST['draw'] ?? 1);
                    $start = intval($_POST['start'] ?? 0);
                    $length = intval($_POST['length'] ?? 10);
                    $search = $_POST['search']['value'] ?? '';
            
                    // Total de registros
                    $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM Modelos");
                    $stmtTotal->execute();
                    $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Query principal con INNER JOIN
                    $query = "
                        SELECT 
                            m.IdModelo,
                            ma.Descripcion AS Marca,
                            m.Descripcion AS Descripcion,
                            ma.IdMarca,
                            m.Estado
                        
                        FROM 
                            Modelos m
                        INNER JOIN 
                            Marcas ma
                        ON 
                            m.IdMarca = ma.IdMarca
                    ";
            
                    // Búsqueda
                    if (!empty($search)) {
                        $query .= " WHERE m.Descripcion LIKE :search OR ma.Descripcion LIKE :search OR m.IdModelo LIKE :search";
                    }
            
                    // Limitar resultados para paginación
                    $query .= " LIMIT :start, :length";
            
                    $stmt = $db->prepare($query);
                    if (!empty($search)) {
                        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
                    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
                    $stmt->execute();
            
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    echo json_encode([
                        "draw" => $draw,
                        "recordsTotal" => $recordsTotal,
                        "recordsFiltered" => count($data),
                        "data" => $data
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
                }
                break;
            

       
        case 'create': // Crear un nuevo registro en `Modelo`
            if (empty($_POST['descripcion']) || !isset($_POST['estado'])) {
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO Modelos (IdMarca,Descripcion, Estado) VALUES (:idMarca,:descripcion, :estado)");
            $stmt->execute([
                ':idMarca' => $_POST['idMarca'],
                ':descripcion' => $_POST['descripcion'],
                ':estado' => $_POST['estado']
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Modelo creado con éxito']);
            break;

            case 'update': // Actualizar un registro en `Modelos`
                if (empty($_POST['idModelo']) || empty($_POST['descripcion']) || !isset($_POST['estado']) || empty($_POST['idMarca'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                    exit;
                }
            
                try {
                    $stmt = $db->prepare("UPDATE Modelos SET Descripcion = :descripcion, Estado = :estado, IdMarca = :idMarca WHERE IdModelo = :idModelo");
                    $stmt->execute([
                        ':idModelo' => $_POST['idModelo'],
                        ':descripcion' => $_POST['descripcion'],
                        ':estado' => $_POST['estado'],
                        ':idMarca' => $_POST['idMarca']
                    ]);
            
                    echo json_encode(['status' => 'success', 'message' => 'Modelo actualizado con éxito']);
                } catch (PDOException $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                }
                break;
            

        case 'delete': // Eliminar un registro de `Modelo`
            if (empty($_POST['idModelo'])) {
                echo json_encode(['status' => 'error', 'message' => 'ID Modelo no proporcionado']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM Modelos WHERE idModelo = :idModelo");
            $stmt->execute([':idModelo' => $_POST['idModelo']]);

            echo json_encode(['status' => 'success', 'message' => 'Modelo eliminado con éxito']);
            break;


            case 'detalleMarca':
                try {
                    // Seleccionar todas las marcas
                    $stmt = $db->prepare("SELECT IdMarca, Descripcion, Estado FROM Marcas");
                    $stmt->execute();
            
                    // Obtener todos los registros
                    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    // Devolver todas las marcas
                    echo json_encode(['status' => 'success', 'data' => $marcas]);
                } catch (PDOException $e) {
                    // Manejar errores en la base de datos
                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                }
                break;
            
        
        

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
