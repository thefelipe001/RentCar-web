<?php
require '../../libs/fpdf/fpdf.php';
require_once '../../RentCarDB.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$fechaInicio = $_GET['fechaInicio'] ?? null;
$fechaFin = $_GET['fechaFin'] ?? null;
$tipoVehiculo = $_GET['tipoVehiculo'] ?? null;

try {
    $db = RentCarDB::getInstance()->getConnection();

    $where = 'WHERE 1=1';
    $params = [];

    if ($fechaInicio && $fechaFin) {
        $where .= " AND FechaRenta BETWEEN :fechaInicio AND :fechaFin";
        $params[':fechaInicio'] = $fechaInicio;
        $params[':fechaFin'] = $fechaFin;
    }

    if ($tipoVehiculo) {
        $where .= " AND v.IdTipoVehiculo = :tipoVehiculo";
        $params[':tipoVehiculo'] = $tipoVehiculo;
    }

    $query = "
        SELECT 
            r.IdRenta,
            c.Nombre AS Cliente,
            v.Descripcion AS Vehiculo,
            t.Descripcion AS TipoVehiculo, -- Incluye el tipo desde la tabla correspondiente
            r.FechaRenta,
            r.FechaDevolucion,
            (r.MontoPorDia * r.CantidadDias) AS MontoTotal
        FROM RentaDevolucion r
        INNER JOIN clientes c ON r.IdCliente = c.IdCliente
        INNER JOIN vehiculos v ON r.IdVehiculo = v.IdVehiculo
        LEFT JOIN tiposvehiculos t ON v.IdTipoVehiculo = t.IdTipoVehiculo
        $where
    ";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(190, 10, 'Reporte de Rentas', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'ID Renta', 1);
    $pdf->Cell(40, 10, 'Cliente', 1);
    $pdf->Cell(40, 10, 'Vehiculo', 1);
    $pdf->Cell(30, 10, 'Tipo', 1);
    $pdf->Cell(30, 10, 'Fecha Renta', 1);
    $pdf->Cell(30, 10, 'Monto Total', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    foreach ($data as $row) {
        $pdf->Cell(20, 10, $row['IdRenta'], 1);
        $pdf->Cell(40, 10, utf8_decode($row['Cliente']), 1);
        $pdf->Cell(40, 10, utf8_decode($row['Vehiculo']), 1);
        $pdf->Cell(30, 10, utf8_decode($row['TipoVehiculo'] ?? 'N/A'), 1);
        $pdf->Cell(30, 10, $row['FechaRenta'], 1);
        $pdf->Cell(30, 10, number_format($row['MontoTotal'], 2), 1);
        $pdf->Ln();
    }

    $pdf->Output();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}