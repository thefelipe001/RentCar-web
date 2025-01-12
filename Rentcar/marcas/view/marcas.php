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
                if (!isset($_POST['idMarca']) || !is_numeric($_POST['idMarca'])) {
                    echo json_encode(['status' => 'error', 'message' => 'ID  Marca inválido']);
                    exit;
                }
        
                $stmt = $db->prepare("SELECT IdMarca,Descripcion, Estado FROM Marcas WHERE IdMarca = :idMarca");
                $stmt->execute([':idMarca' => $_POST['idMarca']]);
                $Marca = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($Marca) {
                    echo json_encode(['status' => 'success', 'data' => $Marca]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Marca no encontrado']);
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
            
                    // Total de registros en la tabla
                    $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM Marcas");
                    $stmtTotal->execute();
                    $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Total de registros filtrados
                    $queryFiltered = "SELECT COUNT(*) AS total FROM Marcas";
                    if (!empty($search)) {
                        $queryFiltered .= " WHERE Descripcion LIKE :search OR IdMarca LIKE :search";
                    }
                    $stmtFiltered = $db->prepare($queryFiltered);
                    if (!empty($search)) {
                        $stmtFiltered->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmtFiltered->execute();
                    $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Consulta principal con paginación
                    $query = "SELECT IdMarca, Descripcion, Estado FROM Marcas";
                    if (!empty($search)) {
                        $query .= " WHERE Descripcion LIKE :search OR IdMarca LIKE :search";
                    }
                    $query .= " LIMIT :start, :length";
            
                    $stmt = $db->prepare($query);
                    if (!empty($search)) {
                        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
                    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
                    $stmt->execute();
            
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    // Respuesta en formato JSON
                    echo json_encode([
                        "draw" => $draw,
                        "recordsTotal" => $recordsTotal,       // Total de registros sin filtro
                        "recordsFiltered" => $recordsFiltered, // Total de registros después del filtro
                        "data" => $data                        // Datos para la tabla
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
                }
                break;
                

       
        case 'create': // Crear un nuevo registro en `Marcas`
            if (empty($_POST['descripcion']) || !isset($_POST['estado'])) {
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO Marcas (Descripcion, Estado) VALUES (:descripcion, :estado)");
            $stmt->execute([
                ':descripcion' => $_POST['descripcion'],
                ':estado' => $_POST['estado']
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Marca creado con éxito']);
            break;

        case 'update': // Actualizar un registro en `Marcas`
            if (empty($_POST['idMarca']) || empty($_POST['descripcion']) || !isset($_POST['estado'])) {
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                exit;
            }

            $stmt = $db->prepare("UPDATE Marcas SET Descripcion = :descripcion, Estado = :estado WHERE idMarca = :idMarca");
            $stmt->execute([
                ':idMarca' => $_POST['idMarca'],
                ':descripcion' => $_POST['descripcion'],
                ':estado' => $_POST['estado']
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Marcas actualizado con éxito']);
            break;

        case 'delete': // Eliminar un registro de `tiposvehiculos`
            if (empty($_POST['idMarca'])) {
                echo json_encode(['status' => 'error', 'message' => 'ID Marcas no proporcionado']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM Marcas WHERE idMarca = :idMarca");
            $stmt->execute([':idMarca' => $_POST['idMarca']]);

            echo json_encode(['status' => 'success', 'message' => 'Marcas eliminado con éxito']);

            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
