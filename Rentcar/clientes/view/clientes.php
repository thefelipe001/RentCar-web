<?php
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
                if (!isset($_POST['idCliente']) || !is_numeric($_POST['idCliente'])) {
                    echo json_encode(['status' => 'error', 'message' => 'ID de cliente inválido']);
                    exit;
                }
        
                $stmt = $db->prepare("SELECT IdCliente, Nombre, Cedula, NumeroTarjetaCR, LimiteCredito, TipoPersona, Estado FROM CLIENTES WHERE IdCliente = :idCliente");
                $stmt->execute([':idCliente' => $_POST['idCliente']]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($cliente) {
                    echo json_encode(['status' => 'success', 'data' => $cliente]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Cliente no encontrado']);
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
                    $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM Clientes");
                    $stmtTotal->execute();
                    $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Total de registros filtrados
                    $queryFiltered = "SELECT COUNT(*) AS total FROM Clientes";
                    if (!empty($search)) {
                        $queryFiltered .= "
                            WHERE 
                                Nombre LIKE :search OR 
                                Cedula LIKE :search OR 
                                NumeroTarjetaCR LIKE :search OR 
                                TipoPersona LIKE :search OR 
                                IdCliente LIKE :search
                        ";
                    }
                    $stmtFiltered = $db->prepare($queryFiltered);
                    if (!empty($search)) {
                        $stmtFiltered->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmtFiltered->execute();
                    $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Consulta principal para obtener los datos
                    $query = "
                        SELECT 
                            IdCliente, 
                            Nombre, 
                            Cedula, 
                            NumeroTarjetaCR, 
                            LimiteCredito, 
                            TipoPersona, 
                            Estado 
                        FROM 
                            Clientes
                    ";
                    if (!empty($search)) {
                        $query .= "
                            WHERE 
                                Nombre LIKE :search OR 
                                Cedula LIKE :search OR 
                                NumeroTarjetaCR LIKE :search OR 
                                TipoPersona LIKE :search OR 
                                IdCliente LIKE :search
                        ";
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
            
                    // Respuesta JSON para DataTables
                    echo json_encode([
                        "draw" => $draw,
                        "recordsTotal" => $recordsTotal,
                        "recordsFiltered" => $recordsFiltered,
                        "data" => $data
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
                }
                break;
            
        
        
       
       
       
           


                case 'create':
                    try {
                        // Validar datos obligatorios
                        if (empty($_POST['nombre']) || empty($_POST['cedula']) || empty($_POST['limiteCredito']) || empty($_POST['tipoPersona']) || !isset($_POST['estado'])) {
                            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                            exit;
                        }
                    
                        // Valor por defecto para NumeroTarjetaCR si está vacío
                        $numeroTarjetaCR = $_POST['numeroTarjetaCR'] ?? null;
                    
                        // Preparar la consulta
                        $stmt = $db->prepare("INSERT INTO CLIENTES 
                            (Nombre, Cedula, NumeroTarjetaCR, LimiteCredito, TipoPersona, Estado) 
                            VALUES (:nombre, :cedula, :tarjetaCR, :limiteCredito, :tipoPersona, :estado)");
                        $stmt->execute([
                            ':nombre' => $_POST['nombre'],
                            ':cedula' => $_POST['cedula'],
                            ':tarjetaCR' => $numeroTarjetaCR, // Manejo opcional
                            ':limiteCredito' => $_POST['limiteCredito'],
                            ':tipoPersona' => $_POST['tipoPersona'],
                            ':estado' => $_POST['estado']
                        ]);
                    
                        echo json_encode(['status' => 'success', 'message' => 'Cliente creado con éxito']);
                    } catch (PDOException $e) {
                        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                    }
                    
                    break;

                    case 'update':
                        try {
                            $stmt = $db->prepare("UPDATE CLIENTES SET 
                                Nombre = :nombre,
                                Cedula = :cedula,
                                NumeroTarjetaCR = :numeroTarjetaCR,
                                LimiteCredito = :limiteCredito,
                                TipoPersona = :tipoPersona,
                                Estado = :estado
                                WHERE IdCliente = :idCliente");
                    
                            $stmt->execute([
                                ':nombre' => $_POST['nombre'],
                                ':cedula' => $_POST['cedula'],
                                ':numeroTarjetaCR' => $_POST['numeroTarjetaCR'] ?? null,
                                ':limiteCredito' => $_POST['limiteCredito'],
                                ':tipoPersona' => $_POST['tipoPersona'],
                                ':estado' => $_POST['estado'],
                                ':idCliente' => $_POST['idCliente']
                            ]);
                    
                            echo json_encode(['status' => 'success', 'message' => 'Cliente actualizado con éxito']);
                        } catch (PDOException $e) {
                            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                        }
                        break;
                    
                  
                    
                    
                    
                        case 'delete':
                        if (!isset($_POST['idCliente']) || !is_numeric($_POST['idCliente'])) {
                            echo json_encode(['status' => 'error', 'message' => 'ID de cliente inválido']);
                            exit;
                        }
                    
                        try {
                            $stmt = $db->prepare("DELETE FROM CLIENTES WHERE IdCliente = :idCliente");
                            $stmt->execute([':idCliente' => $_POST['idCliente']]);
                            echo json_encode([
                                'status' => $stmt->rowCount() > 0 ? 'success' : 'error',
                                'message' => $stmt->rowCount() > 0 ? 'Cliente eliminado con éxito' : 'Cliente no encontrado'
                            ]);
                        } catch (PDOException $e) {
                            echo json_encode([
                                'status' => 'error',
                                'message' => 'Error en la base de datos: ' . $e->getMessage()
                            ]);
                        }
                        break;
                    

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
