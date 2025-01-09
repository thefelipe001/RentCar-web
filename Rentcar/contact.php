<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>ROYAL CARS - Plantilla PHP para Alquiler de Autos</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Plantilla para Alquiler de Autos" name="keywords">
    <meta content="Plantilla para Alquiler de Autos" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Rubik&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Inicio de Contacto Superior -->
    <div class="container-fluid bg-dark py-3 px-lg-5 d-none d-lg-block">
        <div class="row">
            <div class="col-md-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body pr-3" href=""><i class="fa fa-phone-alt mr-2"></i>+012 345 6789</a>
                    <span class="text-body">|</span>
                    <a class="text-body px-3" href=""><i class="fa fa-envelope mr-2"></i>info@example.com</a>
                </div>
            </div>
            <div class="col-md-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body pl-3" href="">
                        <i class="fab fa-user"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin de Contacto Superior -->

    <!-- Inicio del Menú de Navegación -->
    <div class="container-fluid position-relative nav-bar p-0">
        <div class="position-relative px-lg-5" style="z-index: 9;">
            <nav class="navbar navbar-expand-lg bg-secondary navbar-dark py-3 py-lg-0 pl-3 pl-lg-5">
                <a href="" class="navbar-brand">
                    <h1 class="text-uppercase text-primary mb-1">Royal Cars</h1>
                </a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between px-3" id="navbarCollapse">
                    <div class="navbar-nav ml-auto py-0">
                        <a href="index.php" class="nav-item nav-link">Inicio</a>
                        <a href="about.php" class="nav-item nav-link">Nosotros</a>
                        <a href="service.php" class="nav-item nav-link">Servicios</a>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Autos</a>
                            <div class="dropdown-menu rounded-0 m-0">
                                <a href="car.php" class="dropdown-item">Listado de Autos</a>
                                <a href="detail.php" class="dropdown-item">Detalle del Auto</a>
                                <a href="booking.php" class="dropdown-item">Reserva de Autos</a>
                            </div>
                        </div>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Páginas</a>
                            <div class="dropdown-menu rounded-0 m-0">
                                <a href="team.php" class="dropdown-item">Nuestro Equipo</a>
                                <a href="testimonial.php" class="dropdown-item">Testimonios</a>
                            </div>
                        </div>
                        <a href="contact.php" class="nav-item nav-link active">Contacto</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Fin del Menú de Navegación -->

    <!-- Inicio del Formulario de Contacto -->
    <div class="container-fluid py-5">
        <div class="container pt-5 pb-3">
            <h1 class="display-4 text-uppercase text-center mb-5">Contáctanos</h1>
            <div class="row">
                <div class="col-lg-7 mb-2">
                    <div class="contact-form bg-light mb-4" style="padding: 30px;">
                        <form action="contact_process.php" method="POST">
                            <div class="row">
                                <div class="col-6 form-group">
                                    <input type="text" class="form-control p-4" name="name" placeholder="Tu Nombre" required>
                                </div>
                                <div class="col-6 form-group">
                                    <input type="email" class="form-control p-4" name="email" placeholder="Tu Correo Electrónico" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control p-4" name="subject" placeholder="Asunto" required>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control py-3 px-4" name="message" rows="5" placeholder="Mensaje" required></textarea>
                            </div>
                            <div>
                                <button class="btn btn-primary py-3 px-5" type="submit">Enviar Mensaje</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 mb-2">
                    <div class="bg-secondary d-flex flex-column justify-content-center px-5 mb-4" style="height: 435px;">
                        <div class="d-flex mb-3">
                            <i class="fa fa-2x fa-map-marker-alt text-primary flex-shrink-0 mr-3"></i>
                            <div class="mt-n1">
                                <h5 class="text-light">Oficina Principal</h5>
                                <p>123 Calle, SANTO DOMINGO, REP.DOM</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <i class="fa fa-2x fa-envelope-open text-primary flex-shrink-0 mr-3"></i>
                            <div class="mt-n1">
                                <h5 class="text-light">Atención al Cliente</h5>
                                <p>f.mejia25@unapec.edu.do</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin del Formulario de Contacto -->

    <!-- Footer -->
    <div class="container-fluid bg-secondary py-5 px-sm-3 px-md-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="text-uppercase text-light mb-4">Contáctanos</h4>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-white mr-3"></i>123 Calle, SANTO DOMINGO, REP.DOM</p>
                <p class="mb-2"><i class="fa fa-phone-alt text-white mr-3"></i>+1829 901 2050</p>
                <p><i class="fa fa-envelope text-white mr-3"></i>f.mejia25@unapec.edu.do</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
