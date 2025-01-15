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
        case 'read':
            // Parámetros de DataTables
            $draw = intval($_POST['draw'] ?? 0);
            $start = intval($_POST['start'] ?? 0);
            $length = intval($_POST['length'] ?? 10);

            // Filtros
            $fechaInicio = $_POST['fechaInicio'] ?? null;
            $fechaFin = $_POST['fechaFin'] ?? null;
            $tipoVehiculo = $_POST['tipoVehiculo'] ?? null;

            // Construcción dinámica de la consulta
            $where = 'WHERE 1=1';
            $params = [];

            if ($fechaInicio && $fechaFin) {
                $where .= " AND r.FechaRenta BETWEEN :fechaInicio AND :fechaFin";
                $params[':fechaInicio'] = $fechaInicio;
                $params[':fechaFin'] = $fechaFin;
            }

            if ($tipoVehiculo) {
                $where .= " AND v.IdTipoVehiculo = :tipoVehiculo";
                $params[':tipoVehiculo'] = $tipoVehiculo;
            }

            // Total de registros sin filtros
            $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM RentaDevolucion r");
            $stmtTotal->execute();
            $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de registros con filtros
            $stmtFiltered = $db->prepare("
                SELECT COUNT(*) AS total
                FROM RentaDevolucion r
                INNER JOIN clientes c ON r.IdCliente = c.IdCliente
                INNER JOIN vehiculos v ON r.IdVehiculo = v.IdVehiculo
                $where
            ");
            foreach ($params as $key => $value) {
                $stmtFiltered->bindValue($key, $value);
            }
            $stmtFiltered->execute();
            $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

            // Consulta principal con paginación
            $query = "
                SELECT 
                    r.IdRenta,
                    c.Nombre AS ClienteNombre,
                    v.Descripcion AS Vehiculo,
                    v.IdTipoVehiculo AS TipoVehiculo,
                    r.FechaRenta,
                    r.FechaDevolucion,
                    (r.MontoPorDia * r.CantidadDias) AS MontoTotal
                FROM RentaDevolucion r
                INNER JOIN clientes c ON r.IdCliente = c.IdCliente
                INNER JOIN vehiculos v ON r.IdVehiculo = v.IdVehiculo
                $where
                LIMIT :start, :length
            ";

            $stmt = $db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Respuesta JSON para DataTables
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
