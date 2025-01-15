
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
    <title>Gestión de Empleados - Royal Cars</title>

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
                <h1>Gestión de Empleados</h1>
                <p>Administra los empleados de forma rápida y sencilla</p>
            </div>

            <!-- Contenedor del título y botón -->
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Lista de Empleados</h5>
                    <button class="btn" style="background-color: #20c997; border: none; color: white; padding: 10px 20px; font-size: 16px; border-radius: 5px;" type="button" onclick="abrirModal(null)">
                        <i class="fas fa-plus"></i> Crear Nuevo
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
                        <table id="tabla" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Cedula</th>
                                    <th>Tanda labor</th>
                                    <th>Porciento Comision</th>
                                    <th>Fecha Ingreso</th>
                                    <th>Puesto Laboral</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../footerPrincipal.php'; ?>

<!-- Modal -->
<div class="modal fade" id="modalEmpleado" tabindex="-1" aria-labelledby="modalEmpleadoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEmpleadoLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEmpleado">
                    <input type="hidden" id="idEmpleado" name="idEmpleado">
                    <input type="hidden" id="idUsuario" name="idUsuario">


                    <div id="idSelect" class="mb-3">
                     <label for="idUsuarioSelect" class="form-label">Usuarios</label>
                     <select id="idUsuarioSelect" name="idUsuarioSelect" class="form-select">
                     <option value="">Seleccione un usuario</option>
                    </select>
                    </div>



                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input disabled type="text" class="form-control" id="nombre" name="nombre" readonly >
                    </div>
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" >
                    </div>

                    <div class="mb-3">
                        <label for="tandaLabor" class="form-label">Tanda labor</label>
                        <select class="form-select" id="tandaLabor" name="tandaLabor" >
                            <option value="Matutina">Matutina</option>
                            <option value="Vespertina">Vespertina</option>
                            <option value="Nocturna">Nocturna</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="porcientoComision" class="form-label">Porciento Comisión</label>
                        <input type="number" class="form-control" id="porcientoComision" name="porcientoComision" min="0" max="100" step="0.01" placeholder="Ejemplo: 10.5">
                    </div>

                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo</label>
                        <input type="text" class="form-control" id="cargo" name="cargo" maxlength="50" placeholder="Ejemplo: Gerente de Ventas">
                    </div>


                    <div class="mb-3">
                        <label for="fechaIngreso" class="form-label">Fecha Ingreso</label>
                        <input type="date" class="form-control" id="fechaIngreso" name="fechaIngreso">
                    </div>
                 
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
                <button type="submit" form="formEmpleado" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>



    <!-- Script Principal -->
    <script src="../js/empleados.js"></script>
</body>

</html>
