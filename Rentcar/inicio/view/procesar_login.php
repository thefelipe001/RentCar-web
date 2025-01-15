<?php
// Iniciar la sesión
session_start();

$path = __DIR__ . '/../../RentCarDB.php';
if (!file_exists($path)) {
    die("Archivo RentCarDB.php no encontrado en: $path");
}
require_once($path);

// Encabezados de seguridad
header('Access-Control-Allow-Origin: *');
header('Content-Security-Policy: script-src \'self\';');
header('Content-Type: application/json');

// Recuperar los datos enviados por el cliente
$correo = $_POST['correo'] ?? null;
$contrasena = $_POST['contrasena'] ?? null;

if (!$correo || !$contrasena) {
    echo json_encode([
        "success" => false,
        "message" => "Por favor, completa todos los campos."
    ]);
    exit;
}

try {
    // Obtener conexión a la base de datos
    $db = RentCarDB::getInstance()->getConnection();

    // Consulta para verificar si el usuario existe y validar la contraseña directamente
    $query = "
        SELECT IdUsuario, Nombres, Apellidos, Contrasena, EsAdministrador, Activo
        FROM usuario 
        WHERE Correo = :correo AND Contrasena = :contrasena
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':contrasena', $contrasena);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el usuario existe
    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "El usuario o la contraseña son incorrectos. Por favor, verifique."
        ]);
        exit;
    }

    // Verificar si el usuario está activo
    if ($user['Activo'] != 1) {
        echo json_encode([
            "success" => false,
            "message" => "El usuario está inactivo. Contacta al administrador."
        ]);
        exit;
    }

    // Guardar datos del usuario en la sesión
    $_SESSION['usuario'] = [
        'idUsuario' => $user['IdUsuario'],
        'nombres' => $user['Nombres'],
        'apellidos' => $user['Apellidos'],
        'esAdministrador' => $user['EsAdministrador']
    ];

    // Respuesta exitosa
    echo json_encode([
        "success" => true,
        "message" => "Inicio de sesión exitoso."
    ]);
    exit;
} catch (Exception $e) {
    // Manejo de errores
    echo json_encode([
        "success" => false,
        "message" => "Error del servidor: " . $e->getMessage()
    ]);
    exit;
}
