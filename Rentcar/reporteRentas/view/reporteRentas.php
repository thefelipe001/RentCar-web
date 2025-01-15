
<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']['nombres']) || empty($_SESSION['usuario']['apellidos'])) {
    header('Location: login.php');
    exit;
}


$usuario = $_SESSION['usuario'];


$rentasActivas = 10; 
$usuariosRegistrados = 25; 
$reportesGenerados = 15; 
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Proceso de renta y devolución - Royal Cars</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="/css/style1.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.15.10/dist/sweetalert2.all.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>

</head>

<body>
    <div class="wrapper">
        <?php include '../../Sidebar.php'; ?>

        <!-- Content -->
        <div class="content">
        <div class="header d-flex justify-content-between align-items-center">
                <button class="btn btn-light btn-toggle-sidebar d-md-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5>Bienvenido, <?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?>!</h5>
                <a href="/inicio/view/logout.php" class="btn btn-danger btn-sm">Cerrar sesión</a>
            </div>

        
            <!-- Hero Section -->
            <div class="hero text-center py-4">
                <h1>Consulta de Rentas</h1>
                <p>Administra las rentas y devoluciones fácilmente.</p>
            </div>

            <div class="container my-5">
                <h1 class="text-center">Reporte de Rentas</h1>
                <div class="row g-3 mb-4">
                    <div class="col-md-3 d-flex align-items-end">
                        <a id="downloadPDF" class="btn btn-success w-100" target="_blank">Descargar PDF</a>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="card shadow">
                    <div class="card-body">
                        <table id="tabla" class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID Renta</th>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Tipo Vehículo</th>
                                    <th>Fecha Renta</th>
                                    <th>Fecha Devolución</th>
                                    <th>Monto Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
       $(document).ready(function () {
    const table = $('#tabla').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'reporte.php',
            type: 'POST',
            data: function (d) {
                d.action = 'read';
            }
        },
        columns: [
            { data: 'IdRenta' },
            { data: 'ClienteNombre' },
            { data: 'Vehiculo' },
            { data: 'TipoVehiculo' },
            { data: 'FechaRenta' },
            { data: 'FechaDevolucion' },
            { data: 'MontoTotal', render: $.fn.dataTable.render.number(',', '.', 2, '$') }
        ],
        responsive: true,
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "No hay datos disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                previous: "Anterior",
                next: "Siguiente"
            }
        }
    });
});

$(document).ready(function () {
    $('#downloadPDF').attr('href', 'generarReportePDF.php'); // Enlace estático al archivo PHP
});


    </script>
</body>

</html>
