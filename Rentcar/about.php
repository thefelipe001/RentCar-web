<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>ROYAL CARS - Plantilla HTML para Alquiler de Autos</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Plantillas HTML Gratis" name="keywords">
    <meta content="Plantillas HTML Gratis" name="description">

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
    <!-- Barra Superior Inicio -->
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
    <!-- Barra Superior Fin -->


    <!-- Barra de Navegación Inicio -->
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
                        <a href="about.php" class="nav-item nav-link active">Nosotros</a>
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
                                <a href="team.html" class="dropdown-item">Nuestro Equipo</a>
                                <a href="testimonial.html" class="dropdown-item">Testimonios</a>
                            </div>
                        </div>
                        <a href="contact.php" class="nav-item nav-link">Contacto</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Barra de Navegación Fin -->


    <!-- Página Header Inicio -->
    <div class="container-fluid page-header">
        <h1 class="display-3 text-uppercase text-white mb-3">Nosotros</h1>
        <div class="d-inline-flex text-white">
            <h6 class="text-uppercase m-0"><a class="text-white" href="">Inicio</a></h6>
            <h6 class="text-body m-0 px-3">/</h6>
            <h6 class="text-uppercase text-body m-0">Nosotros</h6>
        </div>
    </div>
    <!-- Página Header Fin -->

    <!-- Nosotros Inicio -->
    <div class="container-fluid py-5">
        <div class="container pt-5 pb-3">
            <h1 class="display-4 text-uppercase text-center mb-5">Bienvenido a <span class="text-primary">Royal Cars</span></h1>
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <img class="w-75 mb-4" src="img/about.png" alt="">
                    <p>Explora la experiencia premium en alquiler de autos con Royal Cars. Comprometidos con la excelencia, ofrecemos soporte 24/7 y múltiples ubicaciones de recogida para adaptarnos a tus necesidades.</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-4 mb-2">
                    <div class="d-flex align-items-center bg-light p-4 mb-4" style="height: 150px;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary ml-n4 mr-4" style="width: 100px; height: 100px;">
                            <i class="fa fa-2x fa-headset text-secondary"></i>
                        </div>
                        <h4 class="text-uppercase m-0">Soporte 24/7</h4>
                    </div>
                </div>
                <div class="col-lg-4 mb-2">
                    <div class="d-flex align-items-center bg-secondary p-4 mb-4" style="height: 150px;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary ml-n4 mr-4" style="width: 100px; height: 100px;">
                            <i class="fa fa-2x fa-car text-secondary"></i>
                        </div>
                        <h4 class="text-light text-uppercase m-0">Reserva de Autos</h4>
                    </div>
                </div>
                <div class="col-lg-4 mb-2">
                    <div class="d-flex align-items-center bg-light p-4 mb-4" style="height: 150px;">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary ml-n4 mr-4" style="width: 100px; height: 100px;">
                            <i class="fa fa-2x fa-map-marker-alt text-secondary"></i>
                        </div>
                        <h4 class="text-uppercase m-0">Múltiples Ubicaciones</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Nosotros Fin -->