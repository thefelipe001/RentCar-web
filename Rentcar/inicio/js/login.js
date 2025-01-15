$('#loginForm').on('submit', function (e) {
    e.preventDefault(); // Evita el comportamiento predeterminado del formulario

    $.ajax({
        url: 'procesar_login.php', // Cambia a la URL correcta del backend
        type: 'POST',
        data: $(this).serialize(), // Serializa los datos del formulario
        success: function (response) {
            // Intenta analizar la respuesta como JSON
            try {
                response = typeof response === 'string' ? JSON.parse(response) : response;

                if (response.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                       
                        window.location.assign('/inicio/view/dashboard.php');
 
                                            });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error'
                    });
                }
            } catch (error) {
                console.error('Error al procesar la respuesta:', error);
                Swal.fire({
                    title: 'Error inesperado',
                    text: 'Ocurrió un problema al procesar la respuesta del servidor.',
                    icon: 'error'
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error AJAX:', status, error);
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al enviar la solicitud. Por favor, intenta nuevamente.',
                icon: 'error'
            });
        }
    });
});
