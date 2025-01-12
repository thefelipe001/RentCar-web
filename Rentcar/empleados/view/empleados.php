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
                $idEmpleado = intval($_POST['idEmpleado'] ?? 0);
                $stmt = $db->prepare("
                    SELECT 
                        e.IdEmpleado, e.Cedula, e.TandaLabor, e.PorcientoComision, 
                        e.FechaIngreso, e.Cargo, e.Estado, e.IdUsuario,e.Nombre
                    FROM empleados e
                    WHERE e.IdEmpleado = :idEmpleado
                ");
                $stmt->execute([':idEmpleado' => $idEmpleado]);
                $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($empleado) {
                    echo json_encode(['status' => 'success', 'data' => $empleado]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Empleado no encontrado']);
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
    
            // Lógica de filtrado
            $where = '';
            if (!empty($search)) {
                $where = " WHERE 
                    Nombre LIKE :search OR 
                    Cedula LIKE :search OR 
                    TandaLabor LIKE :search OR 
                    Cargo LIKE :search OR 
                    IdEmpleado LIKE :search";
            }
    
            // Total de registros sin filtro
            $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM Empleados");
            $stmtTotal->execute();
            $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    
            // Total de registros filtrados
            $queryFiltered = "SELECT COUNT(*) AS total FROM Empleados" . $where;
            $stmtFiltered = $db->prepare($queryFiltered);
            if (!empty($search)) {
                $stmtFiltered->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            }
            $stmtFiltered->execute();
            $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
    
            // Consulta principal con paginación
            $query = "
                SELECT 
                    IdEmpleado, 
                    Nombre, 
                    Cedula, 
                    TandaLabor, 
                    PorcientoComision, 
                    FechaIngreso, 
                    Cargo, 
                    Estado 
                FROM 
                    Empleados" . $where . " LIMIT :start, :length";
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
                if (empty($_POST['idUsuario']) || empty($_POST['nombre']) || empty($_POST['cedula']) || 
                    empty($_POST['tandaLabor']) || empty($_POST['porcientoComision']) || 
                    empty($_POST['fechaIngreso']) || empty($_POST['cargo'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
                    exit;
                }
        
                $stmt = $db->prepare("INSERT INTO Empleados 
                    (IdUsuario, Nombre, Cedula, TandaLabor, PorcientoComision, FechaIngreso, Estado, Cargo) 
                    VALUES (:idUsuario, :nombre, :cedula, :tandaLabor, :porcientoComision, :fechaIngreso, :estado, :cargo)");
        
                $stmt->execute([
                    ':idUsuario' => $_POST['idUsuario'],
                    ':nombre' => $_POST['nombre'],
                    ':cedula' => $_POST['cedula'],
                    ':tandaLabor' => $_POST['tandaLabor'],
                    ':porcientoComision' => $_POST['porcientoComision'],
                    ':fechaIngreso' => $_POST['fechaIngreso'],
                    ':estado' => $_POST['estado'],
                    ':cargo' => $_POST['cargo']
                ]);
        
                echo json_encode(['status' => 'success', 'message' => 'Empleado creado con éxito']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
            }
            break;


            case 'update':
                try {
                    // Validar datos obligatorios
                    if (
                        empty($_POST['idEmpleado']) || 
                        empty($_POST['cedula']) || 
                        empty($_POST['tandaLabor']) || 
                        empty($_POST['porcientoComision']) || 
                        empty($_POST['fechaIngreso']) || 
                        empty($_POST['cargo']) || 
                        !isset($_POST['estado']) || 
                        empty($_POST['idUsuario'])
                    ) {
                        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar el empleado']);
                        exit;
                    }
            
                    // Validar que el IdUsuario exista en la tabla Usuario
                    $stmtUsuario = $db->prepare("SELECT COUNT(*) AS total FROM usuario WHERE IdUsuario = :idUsuario AND Activo = 1");
                    $stmtUsuario->execute([':idUsuario' => $_POST['idUsuario']]);
                    $usuarioValido = $stmtUsuario->fetch(PDO::FETCH_ASSOC)['total'] > 0;
            
                    if (!$usuarioValido) {
                        echo json_encode(['status' => 'error', 'message' => 'El usuario especificado no existe o está inactivo']);
                        exit;
                    }
            
                    // Obtener los datos actuales del empleado
                    $stmtSelect = $db->prepare("SELECT * FROM empleados WHERE IdEmpleado = :idEmpleado");
                    $stmtSelect->execute([':idEmpleado' => $_POST['idEmpleado']]);
                    $empleadoActual = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            
                    if (!$empleadoActual) {
                        echo json_encode(['status' => 'error', 'message' => 'Empleado no encontrado']);
                        exit;
                    }
            
                    // Comparar datos actuales con los nuevos
                    $datosNuevos = [
                        'Cedula' => $_POST['cedula'],
                        'TandaLabor' => $_POST['tandaLabor'],
                        'PorcientoComision' => $_POST['porcientoComision'],
                        'FechaIngreso' => $_POST['fechaIngreso'],
                        'Cargo' => $_POST['cargo'],
                        'Estado' => $_POST['estado'],
                        'IdUsuario' => $_POST['idUsuario']
                    ];
            
                    $cambiosRealizados = false;
                    foreach ($datosNuevos as $campo => $valor) {
                        if ($empleadoActual[$campo] != $valor) {
                            $cambiosRealizados = true;
                            break;
                        }
                    }
            
                    if (!$cambiosRealizados) {
                        echo json_encode(['status' => 'warning', 'message' => 'No se realizaron cambios. Verifique que los nuevos datos son diferentes a los actuales.']);
                        exit;
                    }
            
                    // Preparar la consulta para actualizar el empleado
                    $stmt = $db->prepare("UPDATE empleados SET 
                        Cedula = :cedula,
                        TandaLabor = :tandaLabor,
                        PorcientoComision = :porcientoComision,
                        FechaIngreso = :fechaIngreso,
                        Cargo = :cargo,
                        Estado = :estado,
                        IdUsuario = :idUsuario
                        WHERE IdEmpleado = :idEmpleado");
            
                    // Ejecutar la consulta
                    $stmt->execute([
                        ':cedula' => $_POST['cedula'],
                        ':tandaLabor' => $_POST['tandaLabor'],
                        ':porcientoComision' => $_POST['porcientoComision'],
                        ':fechaIngreso' => $_POST['fechaIngreso'],
                        ':cargo' => $_POST['cargo'],
                        ':estado' => $_POST['estado'],
                        ':idUsuario' => $_POST['idUsuario'],
                        ':idEmpleado' => $_POST['idEmpleado']
                    ]);
            
                    echo json_encode(['status' => 'success', 'message' => 'Empleado actualizado con éxito']);
                } catch (PDOException $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                }
                break;
            
            
                        case 'delete':
                            // Validar que el ID de empleado sea proporcionado y sea numérico
                            if (!isset($_POST['idEmpleado']) || !is_numeric($_POST['idEmpleado'])) {
                                echo json_encode(['status' => 'error', 'message' => 'ID de empleado inválido']);
                                exit;
                            }
                        
                            try {
                                // Preparar y ejecutar la consulta para eliminar el empleado
                                $stmt = $db->prepare("DELETE FROM Empleados WHERE IdEmpleado = :idEmpleado");
                                $stmt->execute([':idEmpleado' => $_POST['idEmpleado']]);
                        
                                // Verificar si se eliminó algún registro
                                echo json_encode([
                                    'status' => $stmt->rowCount() > 0 ? 'success' : 'error',
                                    'message' => $stmt->rowCount() > 0 ? 'Empleado eliminado con éxito' : 'Empleado no encontrado'
                                ]);
                            } catch (PDOException $e) {
                                // Manejar errores de la base de datos
                                echo json_encode([
                                    'status' => 'error',
                                    'message' => 'Error en la base de datos: ' . $e->getMessage()
                                ]);
                            }
                            break;

                            case 'detalleusuarios':
                                try {
                                    // Seleccionar usuarios que no estén asociados a empleados
                                    $stmt = $db->prepare("
                                        SELECT u.IdUsuario, u.Nombres, u.Apellidos, u.Activo
                                        FROM usuario u
                                        WHERE u.Activo != 0
                                        AND NOT EXISTS (
                                            SELECT 1
                                            FROM empleados e
                                            WHERE e.IdUsuario = u.IdUsuario
                                        )
                                    ");
                                    $stmt->execute();
                            
                                    // Obtener todos los registros
                                    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                                    // Devolver los usuarios
                                    echo json_encode(['status' => 'success', 'data' => $usuarios]);
                                } catch (PDOException $e) {
                                    // Manejar errores en la base de datos
                                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                                }
                                break;
                            

                            case 'editarusuarios':
                                try {
                                    $idEmpleado = isset($_POST['idEmpleado']) ? intval($_POST['idEmpleado']) : 0;
                            
                                    $stmt = $db->prepare("
                                        SELECT u.IdUsuario, u.Nombres, u.Apellidos, u.Activo
                                        FROM usuario u
                                        WHERE u.Activo != 0
                                        AND (NOT EXISTS (
                                            SELECT 1 
                                            FROM empleados e 
                                            WHERE e.IdUsuario = u.IdUsuario
                                        ) 
                                        OR u.IdUsuario = (
                                            SELECT IdUsuario 
                                            FROM empleados 
                                            WHERE IdEmpleado = :idEmpleado
                                        ));
                                    ");
                            
                                    $stmt->execute([':idEmpleado' => $idEmpleado]);
                                    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                                    echo json_encode(['status' => 'success', 'data' => $usuarios]);
                                } catch (PDOException $e) {
                                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
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
