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
                // Verificar que se proporcione un ID de Vehículo válido
                if (!isset($_POST['idVehiculo']) || !is_numeric($_POST['idVehiculo'])) {
                    echo json_encode(['status' => 'error', 'message' => 'ID Vehículo inválido']);
                    exit;
                }
        
                // Preparar la consulta para obtener los datos del vehículo
                $stmt = $db->prepare("
                    SELECT 
                        IdVehiculo, 
                        IdTipoVehiculo, 
                        IdMarca, 
                        IdModelo, 
                        IdCombustible, 
                        Descripcion, 
                        NumeroChasis, 
                        NumeroMotor, 
                        NumeroPlaca, 
                        Estado 
                    FROM 
                        Vehiculos 
                    WHERE 
                        IdVehiculo = :idVehiculo
                ");
                $stmt->execute([':idVehiculo' => $_POST['idVehiculo']]);
                $Vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
        
                // Verificar si se encontraron datos del vehículo
                if ($Vehiculo) {
                    echo json_encode(['status' => 'success', 'data' => $Vehiculo]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Vehículo no encontrado']);
                }
            } catch (PDOException $e) {
                // Manejar errores de la base de datos
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
                    $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM Vehiculos");
                    $stmtTotal->execute();
                    $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Query principal con INNER JOIN para obtener datos relacionados
                    $query = "
                        SELECT 
                            v.IdVehiculo,
                            v.Descripcion AS Vehiculo,
                            v.NumeroChasis,
                            v.NumeroMotor,
                            v.NumeroPlaca,
                            tv.Descripcion AS TipoVehiculo,
                            ma.Descripcion AS Marca,
                            mo.Descripcion AS Modelo,
                            tc.Descripcion AS TipoCombustible,
                            v.Estado
                        FROM 
                            Vehiculos v
                        INNER JOIN 
                            TiposVehiculos tv ON v.IdTipoVehiculo = tv.IdTipoVehiculo
                        INNER JOIN 
                            Marcas ma ON v.IdMarca = ma.IdMarca
                        INNER JOIN 
                            Modelos mo ON v.IdModelo = mo.IdModelo
                        INNER JOIN 
                            TiposCombustible tc ON v.IdCombustible = tc.IdCombustible
                    ";
            
                    // Filtrar registros si hay una búsqueda
                    if (!empty($search)) {
                        $query .= "
                            WHERE 
                                v.IdVehiculo LIKE :search OR 
                                v.Descripcion LIKE :search OR 
                                v.NumeroChasis LIKE :search OR 
                                v.NumeroMotor LIKE :search OR 
                                v.NumeroPlaca LIKE :search OR 
                                tv.Descripcion LIKE :search OR 
                                ma.Descripcion LIKE :search OR 
                                mo.Descripcion LIKE :search OR 
                                tc.Descripcion LIKE :search
                        ";
                    }
            
                    // Cláusula para limitar los resultados por paginación
                    $query .= " LIMIT :start, :length";
            
                    $stmt = $db->prepare($query);
            
                    if (!empty($search)) {
                        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
                    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
                    $stmt->execute();
            
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    // Obtener el total de registros filtrados
                    $queryFiltered = "
                        SELECT COUNT(*) AS total
                        FROM Vehiculos v
                        INNER JOIN TiposVehiculos tv ON v.IdTipoVehiculo = tv.IdTipoVehiculo
                        INNER JOIN Marcas ma ON v.IdMarca = ma.IdMarca
                        INNER JOIN Modelos mo ON v.IdModelo = mo.IdModelo
                        INNER JOIN TiposCombustible tc ON v.IdCombustible = tc.IdCombustible
                    ";
            
                    if (!empty($search)) {
                        $queryFiltered .= "
                            WHERE 
                                v.IdVehiculo LIKE :search OR 
                                v.Descripcion LIKE :search OR 
                                v.NumeroChasis LIKE :search OR 
                                v.NumeroMotor LIKE :search OR 
                                v.NumeroPlaca LIKE :search OR 
                                tv.Descripcion LIKE :search OR 
                                ma.Descripcion LIKE :search OR 
                                mo.Descripcion LIKE :search OR 
                                tc.Descripcion LIKE :search
                        ";
                    }
            
                    $stmtFiltered = $db->prepare($queryFiltered);
            
                    if (!empty($search)) {
                        $stmtFiltered->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmtFiltered->execute();
                    $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Respuesta en formato JSON para DataTables
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
                    // Crear un nuevo registro en `Vehiculos`
                    try {
                        // Validar que los datos requeridos estén presentes
                        if (
                            empty($_POST['descripcion']) ||
                            empty($_POST['numeroChasis']) ||
                            empty($_POST['numeroMotor']) ||
                            empty($_POST['numeroPlaca']) ||
                            empty($_POST['idTipoVehiculo']) ||
                            empty($_POST['idMarca']) ||
                            empty($_POST['idModelo']) ||
                            empty($_POST['idCombustible']) ||
                            !isset($_POST['estado'])
                        ) {
                            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                            exit;
                        }
                
                        // Preparar la consulta
                        $stmt = $db->prepare("
                            INSERT INTO Vehiculos 
                            (Descripcion, NumeroChasis, NumeroMotor, NumeroPlaca, IdTipoVehiculo, IdMarca, IdModelo, IdCombustible, Estado) 
                            VALUES 
                            (:descripcion, :numeroChasis, :numeroMotor, :numeroPlaca, :idTipoVehiculo, :idMarca, :idModelo, :idCombustible, :estado)
                        ");
                        // Ejecutar la consulta con los datos proporcionados
                        $stmt->execute([
                            ':descripcion' => $_POST['descripcion'],
                            ':numeroChasis' => $_POST['numeroChasis'],
                            ':numeroMotor' => $_POST['numeroMotor'],
                            ':numeroPlaca' => $_POST['numeroPlaca'],
                            ':idTipoVehiculo' => $_POST['idTipoVehiculo'],
                            ':idMarca' => $_POST['idMarca'],
                            ':idModelo' => $_POST['idModelo'],
                            ':idCombustible' => $_POST['idCombustible'],
                            ':estado' => $_POST['estado']
                        ]);
                
                        // Respuesta en caso de éxito
                        echo json_encode(['status' => 'success', 'message' => 'Vehículo creado con éxito']);
                    } catch (PDOException $e) {
                        // Manejar errores en la base de datos
                        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                    }
                    break;
                

                    case 'update': // Actualizar un registro en `Vehiculos`
                        if (
                            empty($_POST['idVehiculo']) || 
                            empty($_POST['descripcion']) || 
                            empty($_POST['numeroChasis']) || 
                            empty($_POST['numeroMotor']) || 
                            empty($_POST['numeroPlaca']) || 
                            empty($_POST['idTipoVehiculo']) || 
                            empty($_POST['idMarca']) || 
                            empty($_POST['idModelo']) || 
                            empty($_POST['idCombustible']) || 
                            !isset($_POST['estado'])
                        ) {
                            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                            exit;
                        }
                    
                        try {
                            $stmt = $db->prepare("
                                UPDATE Vehiculos 
                                SET 
                                    Descripcion = :descripcion, 
                                    NumeroChasis = :numeroChasis, 
                                    NumeroMotor = :numeroMotor, 
                                    NumeroPlaca = :numeroPlaca, 
                                    IdTipoVehiculo = :idTipoVehiculo, 
                                    IdMarca = :idMarca, 
                                    IdModelo = :idModelo, 
                                    IdCombustible = :idCombustible, 
                                    Estado = :estado 
                                WHERE 
                                    IdVehiculo = :idVehiculo
                            ");
                            $stmt->execute([
                                ':idVehiculo' => $_POST['idVehiculo'],
                                ':descripcion' => $_POST['descripcion'],
                                ':numeroChasis' => $_POST['numeroChasis'],
                                ':numeroMotor' => $_POST['numeroMotor'],
                                ':numeroPlaca' => $_POST['numeroPlaca'],
                                ':idTipoVehiculo' => $_POST['idTipoVehiculo'],
                                ':idMarca' => $_POST['idMarca'],
                                ':idModelo' => $_POST['idModelo'],
                                ':idCombustible' => $_POST['idCombustible'],
                                ':estado' => $_POST['estado']
                            ]);
                    
                            echo json_encode(['status' => 'success', 'message' => 'Vehículo actualizado con éxito']);
                        } catch (PDOException $e) {
                            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                        }
                        break;
                    
            

                case 'delete': // Eliminar un registro de `Vehiculos`
                    if (empty($_POST['idVehiculo'])) {
                        echo json_encode(['status' => 'error', 'message' => 'ID Vehículo no proporcionado']);
                        exit;
                    }
                
                    try {
                        // Preparar la consulta para eliminar el vehículo
                        $stmt = $db->prepare("DELETE FROM Vehiculos WHERE IdVehiculo = :idVehiculo");
                        $stmt->execute([':idVehiculo' => $_POST['idVehiculo']]);
                
                        // Verificar si se eliminó el registro
                        if ($stmt->rowCount() > 0) {
                            echo json_encode(['status' => 'success', 'message' => 'Vehículo eliminado con éxito']);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'No se encontró el vehículo a eliminar']);
                        }
                    } catch (PDOException $e) {
                        // Manejar errores de la base de datos
                        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                    }
                    break;
                



            case 'detalleMarca':
                try {
                    // Seleccionar todas las marcas
                    $stmt = $db->prepare("SELECT IdMarca, Descripcion, Estado FROM Marcas WHERE Estado != 0");
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

                case 'detalleTipoVehiculo':
                    try {
                        // Seleccionar todas las tiposvehiculos
                        $stmt = $db->prepare("SELECT IdTipoVehiculo, Descripcion, Estado FROM tiposvehiculos");
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

                    case 'detalleModelo':
                        try {
                            // Seleccionar todas las Modelo
                            $stmt = $db->prepare("SELECT IdModelo, Descripcion, Estado FROM Modelos WHERE Estado != 0");
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

                        case 'detalletiposcombustible':
                            try {
                                // Seleccionar todas las tiposcombustible
                                $stmt = $db->prepare("SELECT IdCombustible, Descripcion, Estado FROM tiposcombustible WHERE Estado != 0");
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
