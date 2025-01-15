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
                // Validar y obtener el ID de la transacción
                $idTransaccion = intval($_POST['idTransaccion'] ?? 0);
        
                // Preparar la consulta para obtener los detalles de la inspección
                $stmt = $db->prepare("
                    SELECT 
                        i.IdTransaccion, i.IdVehiculo, i.IdCliente, i.EmpleadoInspeccion, 
                        i.TieneRalladuras, i.CantidadCombustible, i.TieneGomaRespaldo, 
                        i.TieneGato, i.TieneRoturasCristal, i.EstadoGomas, i.Fecha, 
                        i.Estado, i.Observaciones
                    FROM inspeccion i
                    WHERE i.IdTransaccion = :idTransaccion
                ");
                $stmt->execute([':idTransaccion' => $idTransaccion]);
        
                // Obtener los datos de la inspección
                $inspeccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
                // Verificar si se encontraron datos
                if ($inspeccion) {
                    echo json_encode(['status' => 'success', 'data' => $inspeccion]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Inspección no encontrada']);
                }
            } catch (PDOException $e) {
                // Manejar errores de la base de datos
                error_log('Error en la base de datos (get): ' . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos. Intente más tarde.']);
            }
            break;
        


            case 'read':
                try {
                    $draw = intval($_POST['draw'] ?? 1);
                    $start = intval($_POST['start'] ?? 0);
                    $length = intval($_POST['length'] ?? 10);
                    $search = $_POST['search']['value'] ?? '';
            
                    // Filtro para búsqueda
                    $where = '';
                    if (!empty($search)) {
                        $where = " WHERE 
                            i.IdTransaccion LIKE :search OR 
                            v.Descripcion LIKE :search OR 
                            c.Nombre LIKE :search OR 
                            i.Observaciones LIKE :search OR 
                            i.Fecha LIKE :search OR 
                            e.Nombre LIKE :search";
                    }
            
                    // Total de registros sin filtro
                    $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM inspeccion i");
                    $stmtTotal->execute();
                    $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Total de registros filtrados
                    $queryFiltered = "
                        SELECT COUNT(*) AS total 
                        FROM inspeccion i
                        INNER JOIN clientes c ON i.IdCliente = c.IdCliente
                        INNER JOIN vehiculos v ON i.IdVehiculo = v.IdVehiculo
                        INNER JOIN empleados e ON i.EmpleadoInspeccion = e.IdEmpleado
                        " . $where;
                    $stmtFiltered = $db->prepare($queryFiltered);
            
                    // Si hay un filtro, lo vinculamos
                    if (!empty($search)) {
                        $stmtFiltered->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmtFiltered->execute();
                    $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Consulta principal con los datos
                    $query = "
                       SELECT 
                            i.IdTransaccion,
                            v.Descripcion AS Vehiculo,
                            c.Nombre AS ClienteNombre,
                            i.TieneRalladuras,
                            i.CantidadCombustible,
                            i.TieneGomaRespaldo,
                            i.TieneGato,
                            i.TieneRoturasCristal,
                            i.EstadoGomas,
                            i.Observaciones,
                            i.Fecha,
                            e.Nombre AS EmpleadoNombre,
                            i.Estado
                        FROM inspeccion i
                        INNER JOIN clientes c ON i.IdCliente = c.IdCliente
                        INNER JOIN vehiculos v ON i.IdVehiculo = v.IdVehiculo
                        INNER JOIN empleados e ON i.EmpleadoInspeccion = e.IdEmpleado
                        " . $where . " LIMIT :start, :length";
                    $stmt = $db->prepare($query);
            
                    // Vinculamos los valores si hay un filtro
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
                    // Capturamos errores para depurar
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
                }
                break;


                case 'create':
                    try {
                        // Validación de datos obligatorios
                        $requiredFields = [
                            'idVehiculo',
                            'idCliente',
                            'idEmpleado',
                            'tieneRalladuras',
                            'cantidadCombustible',
                            'tieneGomaRespaldo',
                            'tieneGato',
                            'tieneRoturasCristal',
                            'estadoGomas',
                            'fecha',
                            'estado'
                        ];
                
                        // Validar que todos los campos requeridos estén presentes y no vacíos
                        foreach ($requiredFields as $field) {
                            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                                echo json_encode([
                                    'status' => 'error',
                                    'message' => 'El campo "' . $field . '" es obligatorio.'
                                ]);
                                exit;
                            }
                        }
                
                        // Preparar la consulta de inserción
                        $stmt = $db->prepare("INSERT INTO inspeccion 
                            (IdVehiculo, IdCliente, EmpleadoInspeccion, TieneRalladuras, CantidadCombustible, 
                            TieneGomaRespaldo, TieneGato, TieneRoturasCristal, EstadoGomas, Fecha, Estado, Observaciones) 
                            VALUES (:idVehiculo, :idCliente, :idEmpleado, :tieneRalladuras, :cantidadCombustible, 
                            :tieneGomaRespaldo, :tieneGato, :tieneRoturasCristal, :estadoGomas, :fecha, :estado, :observaciones)");
                
                        // Ejecutar la consulta con los datos enviados
                        $stmt->execute([
                            ':idVehiculo' => $_POST['idVehiculo'],
                            ':idCliente' => $_POST['idCliente'],
                            ':idEmpleado' => $_POST['idEmpleado'],
                            ':tieneRalladuras' => $_POST['tieneRalladuras'],
                            ':cantidadCombustible' => $_POST['cantidadCombustible'],
                            ':tieneGomaRespaldo' => $_POST['tieneGomaRespaldo'],
                            ':tieneGato' => $_POST['tieneGato'],
                            ':tieneRoturasCristal' => $_POST['tieneRoturasCristal'],
                            ':estadoGomas' => $_POST['estadoGomas'],
                            ':fecha' => $_POST['fecha'],
                            ':estado' => $_POST['estado'],
                            ':observaciones' => $_POST['observaciones'] ?? null // Campo opcional
                        ]);
                
                        // Respuesta exitosa
                        echo json_encode(['status' => 'success', 'message' => 'Inspección creada con éxito.']);
                    } catch (PDOException $e) {
                        // Manejo de errores en la base de datos
                        error_log('Error en la base de datos: ' . $e->getMessage()); // Registrar el error en el log
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Error en la base de datos. Por favor, intente más tarde.'
                        ]);
                    } catch (Exception $e) {
                        // Manejo de cualquier otro tipo de error
                        error_log('Error general: ' . $e->getMessage()); // Registrar el error en el log
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Ocurrió un error inesperado. Por favor, intente más tarde.'
                        ]);
                    }
                    break;


                    case 'delete':
                        // Validar que el ID de inspección sea proporcionado y sea numérico
                        if (!isset($_POST['idTransaccion']) || !is_numeric($_POST['idTransaccion'])) {
                            echo json_encode(['status' => 'error', 'message' => 'ID de transacción inválido']);
                            exit;
                        }
                    
                        try {
                            // Preparar y ejecutar la consulta para eliminar la inspección
                            $stmt = $db->prepare("DELETE FROM inspeccion WHERE IdTransaccion = :idTransaccion");
                            $stmt->execute([':idTransaccion' => $_POST['idTransaccion']]);
                    
                            // Verificar si se eliminó algún registro
                            if ($stmt->rowCount() > 0) {
                                echo json_encode([
                                    'status' => 'success',
                                    'message' => 'Inspección eliminada con éxito'
                                ]);
                            } else {
                                echo json_encode([
                                    'status' => 'error',
                                    'message' => 'Inspección no encontrada'
                                ]);
                            }
                        } catch (PDOException $e) {
                            // Manejar errores de la base de datos
                            error_log('Error en la base de datos (delete): ' . $e->getMessage());
                            echo json_encode([
                                'status' => 'error',
                                'message' => 'Error en la base de datos. Intente más tarde.'
                            ]);
                        }
                        break;

                        case 'update':
                            try {
                                // Validar datos obligatorios
                                if (
                                    empty($_POST['idTransaccion']) ||
                                    empty($_POST['idVehiculo']) ||
                                    empty($_POST['idCliente']) ||
                                    empty($_POST['idEmpleado']) ||
                                    !isset($_POST['tieneRalladuras']) ||
                                    empty($_POST['cantidadCombustible']) ||
                                    !isset($_POST['tieneGomaRespaldo']) ||
                                    !isset($_POST['tieneGato']) ||
                                    !isset($_POST['tieneRoturasCristal']) ||
                                    empty($_POST['estadoGomas']) ||
                                    empty($_POST['fecha']) ||
                                    !isset($_POST['estado'])
                                ) {
                                    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar la inspección']);
                                    exit;
                                }
                        
                                // Obtener los datos actuales de la inspección
                                $stmtSelect = $db->prepare("SELECT * FROM inspeccion WHERE IdTransaccion = :idTransaccion");
                                $stmtSelect->execute([':idTransaccion' => $_POST['idTransaccion']]);
                                $inspeccionActual = $stmtSelect->fetch(PDO::FETCH_ASSOC);
                        
                                if (!$inspeccionActual) {
                                    echo json_encode(['status' => 'error', 'message' => 'Inspección no encontrada']);
                                    exit;
                                }
                        
                                // Comparar datos actuales con los nuevos
                                $datosNuevos = [
                                    'IdVehiculo' => $_POST['idVehiculo'],
                                    'IdCliente' => $_POST['idCliente'],
                                    'EmpleadoInspeccion' => $_POST['idEmpleado'],
                                    'TieneRalladuras' => $_POST['tieneRalladuras'],
                                    'CantidadCombustible' => $_POST['cantidadCombustible'],
                                    'TieneGomaRespaldo' => $_POST['tieneGomaRespaldo'],
                                    'TieneGato' => $_POST['tieneGato'],
                                    'TieneRoturasCristal' => $_POST['tieneRoturasCristal'],
                                    'EstadoGomas' => $_POST['estadoGomas'],
                                    'Fecha' => $_POST['fecha'],
                                    'Estado' => $_POST['estado'],
                                    'Observaciones' => $_POST['observaciones'] ?? null // Campo opcional
                                ];
                        
                                $cambiosRealizados = false;
                                foreach ($datosNuevos as $campo => $valor) {
                                    if ($inspeccionActual[$campo] != $valor) {
                                        $cambiosRealizados = true;
                                        break;
                                    }
                                }
                        
                                if (!$cambiosRealizados) {
                                    echo json_encode(['status' => 'warning', 'message' => 'No se realizaron cambios. Verifique que los nuevos datos son diferentes a los actuales.']);
                                    exit;
                                }
                        
                                // Preparar la consulta para actualizar la inspección
                                $stmt = $db->prepare("UPDATE inspeccion SET 
                                    IdVehiculo = :idVehiculo,
                                    IdCliente = :idCliente,
                                    EmpleadoInspeccion = :idEmpleado,
                                    TieneRalladuras = :tieneRalladuras,
                                    CantidadCombustible = :cantidadCombustible,
                                    TieneGomaRespaldo = :tieneGomaRespaldo,
                                    TieneGato = :tieneGato,
                                    TieneRoturasCristal = :tieneRoturasCristal,
                                    EstadoGomas = :estadoGomas,
                                    Fecha = :fecha,
                                    Estado = :estado,
                                    Observaciones = :observaciones
                                    WHERE IdTransaccion = :idTransaccion");
                        
                                // Ejecutar la consulta
                                $stmt->execute([
                                    ':idVehiculo' => $_POST['idVehiculo'],
                                    ':idCliente' => $_POST['idCliente'],
                                    ':idEmpleado' => $_POST['idEmpleado'],
                                    ':tieneRalladuras' => $_POST['tieneRalladuras'],
                                    ':cantidadCombustible' => $_POST['cantidadCombustible'],
                                    ':tieneGomaRespaldo' => $_POST['tieneGomaRespaldo'],
                                    ':tieneGato' => $_POST['tieneGato'],
                                    ':tieneRoturasCristal' => $_POST['tieneRoturasCristal'],
                                    ':estadoGomas' => $_POST['estadoGomas'],
                                    ':fecha' => $_POST['fecha'],
                                    ':estado' => $_POST['estado'],
                                    ':observaciones' => $_POST['observaciones'] ?? null, // Valor opcional
                                    ':idTransaccion' => $_POST['idTransaccion']
                                ]);
                        
                                echo json_encode(['status' => 'success', 'message' => 'Inspección actualizada con éxito']);
                            } catch (PDOException $e) {
                                echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                            }
                            break;
                        

                        

                       
                        
                    
                

            


            
        
        


    


        


            
            
            
                        
                            

                            

                                case 'detalleVehiculo':
                                    try {
                                        // Seleccionar todas las vehiculos
                                        $stmt = $db->prepare("SELECT IdVehiculo, Descripcion, Estado FROM vehiculos WHERE Estado != 0");
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


                                case 'detalleCliente':
                                    try {
                                        // Seleccionar todas las vehiculos
                                        $stmt = $db->prepare("SELECT IdCliente, Nombre, Estado FROM clientes WHERE Estado != 0");
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


                                case 'detalleEmpleado':
                                    try {
                                        // Seleccionar todas las vehiculos
                                        $stmt = $db->prepare("SELECT IdEmpleado, Nombre, Estado FROM empleados WHERE Estado != 0");
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
