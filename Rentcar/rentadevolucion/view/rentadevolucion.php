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
                // Validar y obtener el ID de la renta
                $idRenta = intval($_POST['idRenta'] ?? 0);

                if ($idRenta <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'ID de renta inválido.']);
                    exit;
                }

                // Preparar la consulta para obtener los detalles de la renta
                $stmt = $db->prepare("
                    SELECT 
                        IdRenta, IdVehiculo, IdCliente, IdEmpleado, 
                        FechaRenta, FechaDevolucion, MontoPorDia, 
                        CantidadDias, Comentario, Estado
                    FROM rentadevolucion
                    WHERE IdRenta = :idRenta
                ");
                $stmt->execute([':idRenta' => $idRenta]);

                // Obtener los datos de la renta
                $renta = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verificar si se encontraron datos
                if ($renta) {
                    echo json_encode(['status' => 'success', 'data' => $renta]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Renta no encontrada.']);
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
                            r.IdRenta LIKE :search OR 
                            v.Descripcion LIKE :search OR 
                            c.Nombre LIKE :search OR 
                            e.Nombre LIKE :search OR 
                            r.FechaRenta LIKE :search OR 
                            r.FechaDevolucion LIKE :search OR 
                            r.Comentario LIKE :search";
                }

                // Total de registros sin filtro
                $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM RentaDevolucion r");
                $stmtTotal->execute();
                $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

                // Total de registros filtrados
                $queryFiltered = "
                        SELECT COUNT(*) AS total 
                        FROM RentaDevolucion r
                        INNER JOIN clientes c ON r.IdCliente = c.IdCliente
                        INNER JOIN vehiculos v ON r.IdVehiculo = v.IdVehiculo
                        INNER JOIN empleados e ON r.IdEmpleado = e.IdEmpleado
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
                            r.IdRenta,
                            v.Descripcion AS Vehiculo,
                            c.Nombre AS ClienteNombre,
                            e.Nombre AS EmpleadoNombre,
                            r.FechaRenta,
                            r.FechaDevolucion,
                            r.MontoPorDia,
                            r.CantidadDias,
                            r.Comentario,
                            r.Estado
                        FROM RentaDevolucion r
                        INNER JOIN clientes c ON r.IdCliente = c.IdCliente
                        INNER JOIN vehiculos v ON r.IdVehiculo = v.IdVehiculo
                        INNER JOIN empleados e ON r.IdEmpleado = e.IdEmpleado
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
                    'fechaRenta',
                    'fechaDevolucion',
                    'montoDia',
                    'cantidadDias',
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

                // Validar que las fechas sean válidas
                if (strtotime($_POST['fechaRenta']) >= strtotime($_POST['fechaDevolucion'])) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'La fecha de devolución debe ser posterior a la fecha de renta.'
                    ]);
                    exit;
                }

                // Validar que montoDia y cantidadDias sean valores numéricos válidos
                if (!is_numeric($_POST['montoDia']) || floatval($_POST['montoDia']) <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'El campo "Monto por Día" debe ser un número positivo.'
                    ]);
                    exit;
                }

                if (!is_numeric($_POST['cantidadDias']) || intval($_POST['cantidadDias']) <= 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'El campo "Cantidad de Días" debe ser un número entero positivo.'
                    ]);
                    exit;
                }

                // Iniciar una transacción
                $db->beginTransaction();

                // Preparar la consulta de inserción
                $stmt = $db->prepare("INSERT INTO rentadevolucion 
                            (IdVehiculo, IdCliente, IdEmpleado, FechaRenta, FechaDevolucion, MontoPorDia, CantidadDias, Comentario, Estado) 
                            VALUES (:idVehiculo, :idCliente, :idEmpleado, :fechaRenta, :fechaDevolucion, :montoDia, :cantidadDias, :comentario, :estado)");

                // Ejecutar la consulta con los datos enviados
                $stmt->execute([
                    ':idVehiculo' => $_POST['idVehiculo'],
                    ':idCliente' => $_POST['idCliente'],
                    ':idEmpleado' => $_POST['idEmpleado'],
                    ':fechaRenta' => $_POST['fechaRenta'],
                    ':fechaDevolucion' => $_POST['fechaDevolucion'],
                    ':montoDia' => $_POST['montoDia'],
                    ':cantidadDias' => $_POST['cantidadDias'],
                    ':comentario' => $_POST['comentario'] ?? null, // Campo opcional
                    ':estado' => $_POST['estado']
                ]);

                // Preparar y ejecutar la consulta de actualización del estado del vehículo
                $updateStmt = $db->prepare("UPDATE vehiculos SET Estado = 0 WHERE IdVehiculo = :idVehiculo");
                $updateStmt->execute([
                    ':idVehiculo' => $_POST['idVehiculo']
                ]);

                // Confirmar la transacción
                $db->commit();

                // Respuesta exitosa
                echo json_encode(['status' => 'success', 'message' => 'Renta/Devolución creada con éxito y estado del vehículo actualizado.']);
            } catch (PDOException $e) {
                // Revertir la transacción en caso de error
                $db->rollBack();
                error_log('Error en la base de datos: ' . $e->getMessage()); // Registrar el error en el log
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error en la base de datos. Por favor, intente más tarde.'
                ]);
            } catch (Exception $e) {
                // Manejo de cualquier otro tipo de error
                $db->rollBack();
                error_log('Error general: ' . $e->getMessage()); // Registrar el error en el log
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ocurrió un error inesperado. Por favor, intente más tarde.'
                ]);
            }
            break;




        case 'delete':
            // Validar que el ID de renta sea proporcionado y sea numérico
            if (!isset($_POST['idRenta']) || !is_numeric($_POST['idRenta'])) {
                echo json_encode(['status' => 'error', 'message' => 'ID de renta inválido.']);
                exit;
            }

            try {
                // Preparar y ejecutar la consulta para eliminar la renta
                $stmt = $db->prepare("DELETE FROM rentadevolucion WHERE IdRenta = :idRenta");
                $stmt->execute([':idRenta' => $_POST['idRenta']]);

                // Verificar si se eliminó algún registro
                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Renta eliminada con éxito.'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Renta no encontrada.'
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
                    empty($_POST['idRenta']) ||
                    empty($_POST['idVehiculo']) ||
                    empty($_POST['idCliente']) ||
                    empty($_POST['idEmpleado']) ||
                    empty($_POST['fechaRenta']) ||
                    empty($_POST['fechaDevolucion']) ||
                    empty($_POST['montoDia']) ||
                    empty($_POST['cantidadDias']) ||
                    !isset($_POST['estado'])
                ) {
                    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar la renta.']);
                    exit;
                }

                // Obtener los datos actuales de la renta
                $stmtSelect = $db->prepare("SELECT * FROM rentadevolucion WHERE IdRenta = :idRenta");
                $stmtSelect->execute([':idRenta' => $_POST['idRenta']]);
                $rentaActual = $stmtSelect->fetch(PDO::FETCH_ASSOC);

                if (!$rentaActual) {
                    echo json_encode(['status' => 'error', 'message' => 'Renta no encontrada.']);
                    exit;
                }

                // Comparar datos actuales con los nuevos
                $datosNuevos = [
                    'IdVehiculo' => $_POST['idVehiculo'],
                    'IdCliente' => $_POST['idCliente'],
                    'IdEmpleado' => $_POST['idEmpleado'],
                    'FechaRenta' => $_POST['fechaRenta'],
                    'FechaDevolucion' => $_POST['fechaDevolucion'],
                    'MontoPorDia' => $_POST['montoDia'],
                    'CantidadDias' => $_POST['cantidadDias'],
                    'Comentario' => $_POST['comentario'] ?? null,
                    'Estado' => $_POST['estado']
                ];

                $cambiosRealizados = false;
                foreach ($datosNuevos as $campo => $valor) {
                    if ($rentaActual[$campo] != $valor) {
                        $cambiosRealizados = true;
                        break;
                    }
                }

                if (!$cambiosRealizados) {
                    echo json_encode(['status' => 'warning', 'message' => 'No se realizaron cambios. Verifique que los nuevos datos son diferentes a los actuales.']);
                    exit;
                }

                // Preparar la consulta para actualizar la renta
                $stmt = $db->prepare("UPDATE rentadevolucion SET 
                                    IdVehiculo = :idVehiculo,
                                    IdCliente = :idCliente,
                                    IdEmpleado = :idEmpleado,
                                    FechaRenta = :fechaRenta,
                                    FechaDevolucion = :fechaDevolucion,
                                    MontoPorDia = :montoDia,
                                    CantidadDias = :cantidadDias,
                                    Comentario = :comentario,
                                    Estado = :estado
                                    WHERE IdRenta = :idRenta");

                // Ejecutar la consulta
                $stmt->execute([
                    ':idVehiculo' => $_POST['idVehiculo'],
                    ':idCliente' => $_POST['idCliente'],
                    ':idEmpleado' => $_POST['idEmpleado'],
                    ':fechaRenta' => $_POST['fechaRenta'],
                    ':fechaDevolucion' => $_POST['fechaDevolucion'],
                    ':montoDia' => $_POST['montoDia'],
                    ':cantidadDias' => $_POST['cantidadDias'],
                    ':comentario' => $_POST['comentario'] ?? null, // Valor opcional
                    ':estado' => $_POST['estado'],
                    ':idRenta' => $_POST['idRenta']
                ]);

                echo json_encode(['status' => 'success', 'message' => 'Renta actualizada con éxito.']);
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
