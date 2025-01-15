
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
    <title>Gestión de Modelos - Royal Cars</title>

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
                <h1>Gestión Modelos</h1>
                <p>Administra los modelos de forma rápida y sencilla</p>
            </div>

            <!-- Contenedor del título y botón -->
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Lista Modelos</h5>
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
                                    <th>Marca</th>
                                    <th>Descripción</th>
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
 <div class="modal fade" id="modalModelos" tabindex="-1" aria-labelledby="modalModelosLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
 <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalModelosLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formModelos">
                    <input type="hidden" id="idModelo" name="idModelo">

                    <div class="mb-3">
                       <label for="marcaSelect" class="form-label">Marca</label>
                       <select id="marcaSelect" name="marcaSelect" class="form-select">
                          <option value="">Cargando...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripcion</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion">
                    </div>
                    <div class="mb-3">
                        <label for="activo" class="form-label">Estado</label>
                        <select class="form-select" id="activo" name="activo" >
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="formModelos" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>



 <!-- Script Principal -->
 <script src="../js/modelos.js"></script>
</body>

</html>
