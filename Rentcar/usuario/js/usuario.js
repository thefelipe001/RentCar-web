$(document).ready(function () {
    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'usuario.php',
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdUsuario", "title": "ID" },
            { "data": "Nombres", "title": "Nombre" },
            { "data": "Apellidos", "title": "Apellido" },
            { "data": "Correo", "title": "Correo" },
            {
                "data": "Activo",
                "title": "Estado",
                "render": function (data) {
                    return data === 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                }
            },
            {
                "data": null,
                "title": "Acciones",
                "orderable": false,
                "render": function (data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editUsuario(${row.IdUsuario})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteUser(${row.IdUsuario})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    `;
                }
            }
        ],
        "responsive": true,
        "language": {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });


    window.deleteUser = function (idUsuario) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminarlo!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'usuario.php',
                    type: 'POST',
                    data: { action: 'delete', id_usuario: idUsuario },
                    success: function (response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false); // Recargar tabla
                            Swal.fire('Eliminado!', response.message || 'Usuario eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el usuario.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar el usuario.', 'error');
                    }
                });
            }
        });
    };
});


function abrirModal(idUsuario = null) {
    if (idUsuario) {
        // Editar usuario: cargar datos del usuario desde el servidor
        $.ajax({
            url: 'usuario.php', // Asegúrate de usar la ruta correcta
            type: 'POST',
            data: { action: 'get', id_usuario: idUsuario },
            success: function (response) {
                if (response.status === 'success') {
                    // Rellenar el formulario del modal con los datos del usuario
                    $('#idUsuario').val(response.data.IdUsuario);
                    $('#nombres').val(response.data.Nombres);
                    $('#apellidos').val(response.data.Apellidos);
                    $('#correo').val(response.data.Correo);
                    $('#activo').val(response.data.Activo);

                    // Cambiar el título del modal
                    $('#modalUsuarioLabel').text('Editar Usuario');

                    // Mostrar el modal
                    $('#modalUsuario').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos del usuario.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error en la solicitud AJAX:', xhr, status, error);
            }
        });
    } else {
        // Crear usuario: limpiar el formulario
        $('#formUsuario')[0].reset();
        $('#idUsuario').val('');
        $('#modalUsuarioLabel').text('Crear Usuario');
        $('#modalUsuario').modal('show');
    }
}



// Interceptar el evento submit del formulario
$('#formUsuario').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    const nombres = $('#nombres').val().trim();
    const apellidos = $('#apellidos').val().trim();
    const correo = $('#correo').val().trim();
    const contrasena = $('#contrasena').val().trim();

    if (!nombres) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Nombre" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Salir de la función si la validación falla
    }
    if (!apellidos) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Apellido" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Salir de la función si la validación falla
    }

    if (!correo) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Correo" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Salir de la función si la validación falla
    }

    // Validar formato de correo electrónico
    const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!correoRegex.test(correo)) {
        Swal.fire({
            title: 'Error',
            text: 'Por favor, ingresa un correo electrónico válido.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!contrasena) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Contraseña" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Salir de la función si la validación falla
    }

    // Validar longitud mínima de contraseña
    if (contrasena.length < 6) {
        Swal.fire({
            title: 'Error',
            text: 'La contraseña debe tener al menos 6 caracteres.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }


    const datosUsuario = {
        action: $('#idUsuario').val() ? 'update' : 'create', // Si hay ID, es una edición
        idUsuario: $('#idUsuario').val(),
        nombres: $('#nombres').val(),
        apellidos: $('#apellidos').val(),
        correo: $('#correo').val(),
        contrasena: $('#contrasena').val(),
        esAdministrador: $('#esAdministrador').val(),
        activo: $('#activo').val()
    };

    $.ajax({
        url: 'usuario.php',
        type: 'POST',
        data: datosUsuario,
        success: function (response) {
            if (response.status === 'success') {
                $('#modalUsuario').modal('hide'); // Cerrar el modal
                $('#tabla').DataTable().ajax.reload(); // Recargar la tabla

                // Mensaje de éxito
                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Usuario agregado correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            } else {
                // Mensaje de error
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar el usuario.',
                    icon: 'error',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function (xhr, status, error) {
            // Mensaje de error de conexión
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al conectar con el servidor.',
                icon: 'error',
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'OK'
            });
            console.error('Error AJAX:', xhr, status, error);
        }
    });
});


$('#formUsuario').on('submit', function (event) {
    event.preventDefault(); // Evita que se recargue la página
});






function editUsuario(idUsuario) {
    // Obtener datos del usuario específico mediante AJAX
    $.ajax({
        url: 'usuario.php', // Ruta al backend
        type: 'POST',
        data: { action: 'get', idUsuario: idUsuario }, // Acción 'get' para obtener los datos del usuario
        success: function (response) {
            if (response.status === 'success') {
                const usuario = response.data;

                // Rellenar los campos del modal con los datos del usuario
                $('#idUsuario').val(usuario.IdUsuario);
                $('#nombres').val(usuario.Nombres);
                $('#apellidos').val(usuario.Apellidos);
                $('#correo').val(usuario.Correo);
                $('#contrasena').val(usuario.Contrasena,); 
                $('#esAdministrador').val(usuario.EsAdministrador);
                $('#activo').val(usuario.Activo);

                 // Cambiar el título del modal
                 $('#modalUsuarioLabel').text('Editar Usuario');

                // Abrir el modal
                $('#modalUsuario').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información del usuario.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
}

