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
                if (!isset($_POST['idUsuario']) || !is_numeric($_POST['idUsuario'])) {
                    echo json_encode(['status' => 'error', 'message' => 'ID de usuario inválido']);
                    exit;
                }
        
                $stmt = $db->prepare("SELECT IdUsuario, Nombres, Apellidos, Correo, Contrasena, EsAdministrador, Activo FROM USUARIO WHERE IdUsuario = :idUsuario");
                $stmt->execute([':idUsuario' => $_POST['idUsuario']]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($usuario) {
                    echo json_encode(['status' => 'success', 'data' => $usuario]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
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
                    $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM USUARIO");
                    $stmtTotal->execute();
                    $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Total de registros filtrados (aplicando el término de búsqueda)
                    $queryFiltered = "SELECT COUNT(*) AS total FROM USUARIO";
                    if (!empty($search)) {
                        $queryFiltered .= " WHERE Nombres LIKE :search OR Apellidos LIKE :search OR Correo LIKE :search OR IdUsuario LIKE :search";
                    }
                    $stmtFiltered = $db->prepare($queryFiltered);
                    if (!empty($search)) {
                        $stmtFiltered->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
                    }
                    $stmtFiltered->execute();
                    $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];
            
                    // Consulta principal para obtener los datos con paginación
                    $query = "SELECT IdUsuario, Nombres, Apellidos, Correo, Activo FROM USUARIO";
                    if (!empty($search)) {
                        $query .= " WHERE Nombres LIKE :search OR Apellidos LIKE :search OR Correo LIKE :search OR IdUsuario LIKE :search";
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
                        "recordsTotal" => $recordsTotal,       // Total de registros sin filtrar
                        "recordsFiltered" => $recordsFiltered, // Total de registros filtrados
                        "data" => $data                        // Datos para la tabla
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
                }
                break;
            

            case 'create':
                try {
                    // Verificar si es para crear o editar
                    $isEdit = isset($_POST['idUsuario']) && !empty($_POST['idUsuario']);
            
                    if ($isEdit) {
                        // Actualizar usuario existente
                        $stmt = $db->prepare("UPDATE USUARIO SET 
                            Nombres = :nombres,
                            Apellidos = :apellidos,
                            Correo = :correo,
                            Contrasena = :contrasena,
                            EsAdministrador = :esAdministrador,
                            Activo = :activo
                            WHERE IdUsuario = :idUsuario");
                        $stmt->execute([
                            ':nombres' => $_POST['nombres'],
                            ':apellidos' => $_POST['apellidos'],
                            ':correo' => $_POST['correo'],
                            ':contrasena' => password_hash($_POST['contrasena'], PASSWORD_BCRYPT),
                            ':esAdministrador' => $_POST['esAdministrador'],
                            ':activo' => $_POST['activo'],
                            ':idUsuario' => $_POST['idUsuario']
                        ]);
                        echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado con éxito']);
                    } else {
                        // Crear nuevo usuario
                        $stmt = $db->prepare("INSERT INTO USUARIO 
                            (Nombres, Apellidos, Correo, Contrasena, EsAdministrador, Activo) 
                            VALUES (:nombres, :apellidos, :correo, :contrasena, :esAdministrador, :activo)");
                        $stmt->execute([
                            ':nombres' => $_POST['nombres'],
                            ':apellidos' => $_POST['apellidos'],
                            ':correo' => $_POST['correo'],
                            ':contrasena' => $_POST['contrasena'],
                            ':esAdministrador' => $_POST['esAdministrador'],
                            ':activo' => $_POST['activo']
                        ]);
                        echo json_encode(['status' => 'success', 'message' => 'Usuario creado con éxito']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                }
                break;
            

                case 'update':
                    try {
                        if (!isset($_POST['idUsuario']) || !is_numeric($_POST['idUsuario'])) {
                            echo json_encode(['status' => 'error', 'message' => 'ID de usuario inválido']);
                            exit;
                        }
                
                        $stmt = $db->prepare("UPDATE USUARIO SET 
                            Nombres = :nombres,
                            Apellidos = :apellidos,
                            Correo = :correo,
                            Activo = :activo
                            WHERE IdUsuario = :idUsuario");
                        $stmt->execute([
                            ':nombres' => $_POST['nombres'],
                            ':apellidos' => $_POST['apellidos'],
                            ':correo' => $_POST['correo'],
                            ':activo' => $_POST['activo'],
                            ':idUsuario' => $_POST['idUsuario']
                        ]);
                
                        if ($stmt->rowCount() > 0) {
                            echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado con éxito']);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el usuario']);
                        }
                    } catch (PDOException $e) {
                        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                    }
                    break;
            
            case 'delete':
            if (!isset($_POST['id_usuario']) || !is_numeric($_POST['id_usuario'])) {
                echo json_encode(['status' => 'error', 'message' => 'ID de usuario inválido']);
                exit;
            }
            $stmt = $db->prepare("DELETE FROM USUARIO WHERE IdUsuario = :id_usuario");
            $stmt->execute([':id_usuario' => $_POST['id_usuario']]);
            echo json_encode([
                'status' => $stmt->rowCount() > 0 ? 'success' : 'error',
                'message' => $stmt->rowCount() > 0 ? 'Usuario eliminado con éxito' : 'Usuario no encontrado'
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
