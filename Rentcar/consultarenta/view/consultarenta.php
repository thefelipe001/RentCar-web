<?php
require_once '../../RentCarDB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
    $db = RentCarDB::getInstance()->getConnection();

    if ($action === 'read') {
        $draw = intval($_POST['draw'] ?? 1);
        $start = intval($_POST['start'] ?? 0);
        $length = intval($_POST['length'] ?? 10);
        $idCliente = $_POST['idCliente'] ?? null;
        $idVehiculo = $_POST['idVehiculo'] ?? null;
        $fechaInicio = $_POST['fechaInicio'] ?? null;
        $fechaFin = $_POST['fechaFin'] ?? null;

        $where = ' WHERE 1=1';
        $params = [];

        if (!empty($idCliente)) {
            $where .= " AND c.Nombre LIKE :idCliente";
            $params[':idCliente'] = '%' . $idCliente . '%';
        }
        if (!empty($idVehiculo)) {
            $where .= " AND v.Descripcion LIKE :idVehiculo";
            $params[':idVehiculo'] = '%' . $idVehiculo . '%';
        }
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $where .= " AND r.FechaRenta BETWEEN :fechaInicio AND :fechaFin";
            $params[':fechaInicio'] = $fechaInicio;
            $params[':fechaFin'] = $fechaFin;
        }

        $stmtTotal = $db->prepare("SELECT COUNT(*) AS total FROM RentaDevolucion r");
        $stmtTotal->execute();
        $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        $stmtFiltered = $db->prepare("SELECT COUNT(*) AS total FROM RentaDevolucion r
            INNER JOIN clientes c ON r.IdCliente = c.IdCliente
            INNER JOIN vehiculos v ON r.IdVehiculo = v.IdVehiculo
            $where");
        $stmtFiltered->execute($params);
        $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = $db->prepare("SELECT 
                r.IdRenta, c.Nombre AS ClienteNombre, v.Descripcion AS Vehiculo, 
                r.FechaRenta, r.FechaDevolucion, r.MontoPorDia, r.CantidadDias, 
                r.Comentario, r.Estado
            FROM RentaDevolucion r
            INNER JOIN clientes c ON r.IdCliente = c.IdCliente
            INNER JOIN vehiculos v ON r.IdVehiculo = v.IdVehiculo
            $where
            LIMIT :start, :length");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
