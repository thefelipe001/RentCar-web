
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

            <!-- Filtros -->
            <div class="container mb-4">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-4">
                        <label for="filterCliente" class="form-label">Cliente</label>
                        <input type="text" class="form-control" id="filterCliente" placeholder="Buscar por cliente">
                    </div>
                    <div class="col-md-4">
                        <label for="filterVehiculo" class="form-label">Vehículo</label>
                        <input type="text" class="form-control" id="filterVehiculo" placeholder="Buscar por vehículo">
                    </div>
                    <div class="col-md-4">
                        <label for="filterFechaInicio" class="form-label">Fecha de Renta (Inicio)</label>
                        <input type="date" class="form-control" id="filterFechaInicio">
                    </div>
                    <div class="col-md-4">
                        <label for="filterFechaFin" class="form-label">Fecha de Renta (Fin)</label>
                        <input type="date" class="form-control" id="filterFechaFin">
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="button" class="btn btn-primary w-100" id="filterButton">Filtrar</button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="container">
                <div class="card shadow">
                    <div class="card-body">
                        <table id="tabla" class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No. Renta</th>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Fecha Renta</th>
                                    <th>Fecha Devolución</th>
                                    <th>Monto x Día</th>
                                    <th>Cantidad de Días</th>
                                    <th>Comentario</th>
                                    <th>Estado</th>
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
                    url: 'consultarenta.php',
                    type: 'POST',
                    data: function (d) {
                        d.action = 'read';
                        d.idCliente = $('#filterCliente').val();
                        d.idVehiculo = $('#filterVehiculo').val();
                        d.fechaInicio = $('#filterFechaInicio').val();
                        d.fechaFin = $('#filterFechaFin').val();
                    },
                },
                columns: [
                    { data: 'IdRenta' },
                    { data: 'ClienteNombre' },
                    { data: 'Vehiculo' },
                    { data: 'FechaRenta' },
                    { data: 'FechaDevolucion' },
                    { data: 'MontoPorDia', render: $.fn.dataTable.render.number(',', '.', 2, '$') },
                    { data: 'CantidadDias' },
                    { data: 'Comentario' },
                    {
                        data: 'Estado',
                        render: function (data) {
                            return data === 1
                                ? '<span class="badge bg-success">Activo</span>'
                                : '<span class="badge bg-danger">Inactivo</span>';
                        }
                    }
                ],
                responsive: true,
                language: {
                    processing: "Procesando...",
                    lengthMenu: "Mostrar _MENU_ registros",
                    zeroRecords: "No se encontraron resultados",
                    emptyTable: "Ningún dato disponible en esta tabla",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });

            $('#filterButton').on('click', function () {
                table.ajax.reload();
            });
        });
    </script>
</body>

</html>