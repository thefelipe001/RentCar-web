

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
                <h1>Proceso de Renta y Devolución </h1>
                <p>Administra los Proceso de renta y devolución  de forma rápida y sencilla</p>
            </div>

            <!-- Contenedor del título y botón -->
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Lista de Proceso de renta y evolución </h5>
                    <button class="btn" style="background-color: #20c997; border: none; color: white; padding: 10px 20px; font-size: 16px; border-radius: 5px;" type="button" onclick="abrirModal(null)">
                        <i class="fas fa-plus"></i> Crear Proceso
                    </button>
                </div>

                <!-- Tabla -->
                <div class="card shadow">
                    <div class="card-body">
                        <!-- Spinner -->
                        <div id="loading-spinner" class="d-none text-center my-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>

                        <!-- Tabla -->
        <table id="tabla" class="table table-striped table-hover align-middle">
             <thead>
            <tr>
                <th style="width: 5%;">No. Renta</th>
                <th style="width: 10%;">Empleado</th>
                <th style="width: 10%;">Vehículo</th>
                <th style="width: 10%;">Cliente</th>
                <th style="width: 10%;">Fecha Renta</th>
                <th style="width: 10%;">Fecha Devolución</th>
                <th style="width: 10%;">Monto x Día</th>
                <th style="width: 10%;">Cantidad de días</th>
                <th style="width: 10%;">Comentario</th>
                <th style="width: 8%;">Estado</th>
                <th style="width: 8%;">Acciones</th>
             </tr>
        </thead>
        <tbody id="userTableBody"></tbody>
        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Renta y Devolución -->
<div class="modal fade" id="modalRentaDevolucion" tabindex="-1" aria-labelledby="modalRentaDevolucionLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalRentaDevolucionLabel">Formulario de Renta y Devolución</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formRentaDevolucion">
                    <!-- ID de Renta (Oculto) -->
                    <input type="hidden" id="idRenta" name="idRenta">

                    <!-- Empleado -->
                    <div class="mb-3">
                        <label for="idEmpleadoSelect" class="form-label">Empleado</label>
                        <select id="idEmpleadoSelect" name="idEmpleado" class="form-select" aria-label="Seleccionar empleado">
                            <option value="" disabled selected>Seleccione un empleado</option>
                        </select>
                    </div>

                    <!-- Vehículo -->
                    <div class="mb-3">
                        <label for="idVehiculoSelect" class="form-label">Vehículo</label>
                        <select id="idVehiculoSelect" name="idVehiculo" class="form-select" aria-label="Seleccionar vehículo">
                            <option value="" disabled selected>Seleccione un vehículo</option>
                        </select>
                    </div>

                    <!-- Cliente -->
                    <div class="mb-3">
                        <label for="idClienteSelect" class="form-label">Cliente</label>
                        <select id="idClienteSelect" name="idCliente" class="form-select" aria-label="Seleccionar cliente">
                            <option value="" disabled selected>Seleccione un cliente</option>
                        </select>
                    </div>

                    <!-- Fecha de Renta -->
                    <div class="mb-3">
                        <label for="fechaRenta" class="form-label">Fecha de Renta</label>
                        <input type="date" class="form-control" id="fechaRenta" name="fechaRenta">
                    </div>

                    <!-- Fecha de Devolución -->
                    <div class="mb-3">
                        <label for="fechaDevolucion" class="form-label">Fecha de Devolución</label>
                        <input type="date" class="form-control" id="fechaDevolucion" name="fechaDevolucion">
                    </div>

                    <!-- Monto por Día -->
                    <div class="mb-3">
                        <label for="montoDia" class="form-label">Monto por Día</label>
                        <input type="number" class="form-control" id="montoDia" name="montoDia" step="0.01" min="0">
                    </div>

                    <!-- Cantidad de Días -->
                    <div class="mb-3">
                        <label for="cantidadDias" class="form-label">Cantidad de Días</label>
                        <input type="number" class="form-control" id="cantidadDias" name="cantidadDias" min="1" >
                    </div>

                    <!-- Comentario -->
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="formRentaDevolucion" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </div>
</div>










    <?php include '../../footerPrincipal.php'; ?>





    <!-- Script Principal -->
    <script src="../js/rentadevolucion.js"></script>
</body>

</html>
