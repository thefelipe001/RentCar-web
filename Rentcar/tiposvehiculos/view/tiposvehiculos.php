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
                if (!isset($_POST['idTipoVehiculo']) || !is_numeric($_POST['idTipoVehiculo'])) {
                    echo json_encode(['status' => 'error', 'message' => 'ID de Tipo de Vehiculo inválido']);
                    exit;
                }
        
                $stmt = $db->prepare("SELECT IdTipoVehiculo,Descripcion, Estado FROM tiposvehiculos WHERE IdTipoVehiculo = :idTipoVehiculo");
                $stmt->execute([':idTipoVehiculo' => $_POST['idTipoVehiculo']]);
                $TipoVehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($TipoVehiculo) {
                    echo json_encode(['status' => 'success', 'data' => $TipoVehiculo]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Tipo de Vehiculo no encontrado']);
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

                $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM tiposvehiculos");
                $stmtTotal->execute();
                $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

                $query = "SELECT IdTipoVehiculo,Descripcion, Estado FROM tiposvehiculos";
                if (!empty($search)) {
                    $query .= " WHERE Descripcion LIKE :search OR IdTipoVehiculo LIKE :search";
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
        case 'create': // Crear un nuevo registro en `tiposvehiculos`
            if (empty($_POST['descripcion']) || !isset($_POST['estado'])) {
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO tiposvehiculos (Descripcion, Estado) VALUES (:descripcion, :estado)");
            $stmt->execute([
                ':descripcion' => $_POST['descripcion'],
                ':estado' => $_POST['estado']
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Tipo de vehículo creado con éxito']);
            break;

        case 'update': // Actualizar un registro en `tiposvehiculos`
            if (empty($_POST['idTipoVehiculo']) || empty($_POST['descripcion']) || !isset($_POST['estado'])) {
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                exit;
            }

            $stmt = $db->prepare("UPDATE tiposvehiculos SET Descripcion = :descripcion, Estado = :estado WHERE IdTipoVehiculo = :idTipoVehiculo");
            $stmt->execute([
                ':idTipoVehiculo' => $_POST['idTipoVehiculo'],
                ':descripcion' => $_POST['descripcion'],
                ':estado' => $_POST['estado']
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Tipo de vehículo actualizado con éxito']);
            break;

        case 'delete': // Eliminar un registro de `tiposvehiculos`
            if (empty($_POST['idTipoVehiculo'])) {
                echo json_encode(['status' => 'error', 'message' => 'ID de tipo de vehículo no proporcionado']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM tiposvehiculos WHERE IdTipoVehiculo = :idTipoVehiculo");
            $stmt->execute([':idTipoVehiculo' => $_POST['idTipoVehiculo']]);

            echo json_encode(['status' => 'success', 'message' => 'Tipo de vehículo eliminado con éxito']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
