<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Royal Cars</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            background: #1f2833;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #0b132b;
            background-size: cover;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
            color: #fff;
        }

        .login-container h3 {
            color: #f8b739;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }

        .login-container .btn-primary {
            background: #f8b739;
            border: none;
            color: #fff;
            font-weight: bold;
            border-radius: 8px;
            padding: 0.75rem;
        }

        .login-container .btn-primary:hover {
            background: #e4a129;
        }

        .login-container .form-control {
            border-radius: 8px;
            border: 1px solid #f8b739;
        }

        .login-container .form-control:focus {
            box-shadow: none;
            border-color: #f8b739;
        }

        .login-container a {
            color: #f8b739;
            text-decoration: none;
        }

        .login-container a:hover {
            text-decoration: underline;
        }

        .icon {
            font-size: 50px;
            color: #f8b739;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <i class="fas fa-car-side icon"></i>
        <h3>Iniciar Sesión</h3>
        <form id="loginForm" action="procesar_login.php" method="POST">
    <div class="mb-3">
        <input type="mail" name="correo" class="form-control" placeholder="Correo" >
    </div>
    <div class="mb-3">
        <input type="password" name="contrasena" class="form-control" placeholder="Contraseña" >
    </div>
    <button type="submit" class="btn btn-primary w-100">Acceder</button>
</form>

        <div class="mt-3">
            <a href="#">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
    <!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script Principal -->
    <script src="../js/login.js"></script>
</body>

</html>
