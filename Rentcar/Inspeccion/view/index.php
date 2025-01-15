
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
    <title>Gestión Proceso de Inspección - Royal Cars</title>

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
                <h1>Proceso de Inspección </h1>
                <p>Administra los Proceso de Inspección  de forma rápida y sencilla</p>
            </div>

            <!-- Contenedor del título y botón -->
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Lista de Proceso de Inspección </h5>
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
                <th style="width: 5%;">ID</th>
                <th style="width: 10%;">N° Transacciones</th>
                <th style="width: 10%;">Vehículo</th>
                <th style="width: 10%;">Cliente</th>
                <th style="width: 10%;">¿Ralladuras?</th>
                <th style="width: 10%;">Combustible</th>
                <th style="width: 10%;">¿Goma Respaldo?</th>
                <th style="width: 10%;">¿Gato?</th>
                <th style="width: 10%;">¿Rotura Cristal?</th>
                <th style="width: 10%;">Estado Gomas</th>
                <th style="width: 15%;">Observaciones</th>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 10%;">Empleado</th>
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

    <!-- Modal -->
<div class="modal fade" id="modalInspeccion" tabindex="-1" aria-labelledby="modalInspeccionLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalInspeccionLabel">Formulario de Inspección</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formInspeccion">
                    <!-- Campo oculto para ID Transacción -->
                    <input type="hidden" id="idTransaccion" name="idTransaccion">

                    <!-- Vehículos -->
                    <div class="mb-3">
                        <label for="idVehiculoSelect" class="form-label">Vehículo</label>
                        <select id="idVehiculoSelect" name="idVehiculo" class="form-select" >
                            <option value="" disabled selected>Seleccione un vehículo</option>
                        </select>
                    </div>

                    <!-- Clientes -->
                    <div class="mb-3">
                        <label for="idClienteSelect" class="form-label">Cliente</label>
                        <select id="idClienteSelect" name="idCliente" class="form-select">
                            <option value="" disabled selected>Seleccione un cliente</option>
                        </select>
                    </div>

                    <!-- Ralladuras -->
                    <div class="mb-3">
                        <label for="tieneRalladuras" class="form-label">¿Tiene ralladuras?</label>
                        <select class="form-select" id="tieneRalladuras" name="tieneRalladuras" >
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- Combustible -->
                    <div class="mb-3">
                        <label for="cantidadCombustible" class="form-label">Cantidad de Combustible</label>
                        <select class="form-select" id="cantidadCombustible" name="cantidadCombustible">
                            <option value="1/4">1/4</option>
                            <option value="1/2">1/2</option>
                            <option value="3/4">3/4</option>
                            <option value="lleno">Lleno</option>
                        </select>
                    </div>

                    <!-- Goma de Respaldo -->
                    <div class="mb-3">
                        <label for="tieneGomaRespaldo" class="form-label">¿Tiene goma de repuesto?</label>
                        <select class="form-select" id="tieneGomaRespaldo" name="tieneGomaRespaldo">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- Gato -->
                    <div class="mb-3">
                        <label for="tieneGato" class="form-label">¿Tiene gato hidráulico?</label>
                        <select class="form-select" id="tieneGato" name="tieneGato">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- Roturas en Cristales -->
                    <div class="mb-3">
                        <label for="tieneRoturasCristal" class="form-label">¿Tiene roturas en los cristales?</label>
                        <select class="form-select" id="tieneRoturasCristal" name="tieneRoturasCristal">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- Estado de las Gomas -->
                    <div class="mb-3">
                        <label class="form-label">Estado de las Gomas</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="goma1" name="goma1">
                            <label class="form-check-label" for="goma1">Goma delantera izquierda</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="goma2" name="goma2">
                            <label class="form-check-label" for="goma2">Goma delantera derecha</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="goma3" name="goma3">
                            <label class="form-check-label" for="goma3">Goma trasera izquierda</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="goma4" name="goma4">
                            <label class="form-check-label" for="goma4">Goma trasera derecha</label>
                        </div>
                    </div>

                    <!-- Fecha -->
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha">
                    </div>

                    <!-- Empleados -->
                    <div class="mb-3">
                        <label for="idEmpleadoSelect" class="form-label">Empleado</label>
                        <select id="idEmpleadoSelect" name="idEmpleado" class="form-select">
                            <option value="" disabled selected>Seleccione un empleado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <input  type="text" class="form-control" id="observaciones" name="observaciones"  >
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="formInspeccion" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </div>
</div>








    <?php include '../../footerPrincipal.php'; ?>





    <!-- Script Principal -->
    <script src="../js/inspeccion.js"></script>
</body>

</html>
