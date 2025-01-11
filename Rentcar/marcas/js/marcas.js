$(document).ready(function () {
    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'marcas.php',
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdMarca", "title": "ID" },
            { "data": "Descripcion", "title": "Descripcion" },
            {
                "data": "Estado",
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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editMarcas(${row.IdMarca})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteMarcas(${row.IdMarca})">
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


    window.deleteMarcas = function (IdMarca) {
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
                    url: 'marcas.php',
                    type: 'POST',
                    data: { action: 'delete', idMarca: IdMarca },
                    success: function (response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false); // Recargar tabla
                            Swal.fire('Eliminado!', response.message || 'Marca eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el Marca.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar Marca.', 'error');
                    }
                });
            }
        });
    };
});


function abrirModal(IdMarca = null) {
    if (IdMarca) {
        // Editar usuario: cargar datos del usuario desde el servidor
        $.ajax({
            url: 'marcas.php', // Asegúrate de usar la ruta correcta
            type: 'POST',
            data: { action: 'get', idMarca: IdMarca },
            success: function (response) {
                if (response.status === 'success') {
                    // Rellenar el formulario del modal con los datos del usuario
                    $('#idMarca').val(response.data.IdMarca);
                    $('#descripcion').val(response.data.Descripcion);
                    $('#activo').val(response.data.Activo);

                    // Cambiar el título del modal
                    $('#modalMarcasLabel').text('Editar Marca');

                    // Mostrar el modal
                    $('#modalMarcas').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos de la Marca.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error en la solicitud AJAX:', xhr, status, error);
            }
        });
    } else {
        // Crear usuario: limpiar el formulario
        $('#formMarcas')[0].reset();
        $('#idMarca').val('');
        $('#modalMarcasLabel').text('Crear Marca');
        $('#modalMarcas').modal('show');
    }
}



// Interceptar el evento submit del formulario
$('#formMarcas').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    // Obtener el valor del campo descripción
    const descripcion = $('#descripcion').val().trim();

    if (!descripcion) {
         Swal.fire({
             title: 'Error',
             text: 'El campo "Descripción" no puede estar vacío.',
             icon: 'warning',
             confirmButtonText: 'OK'
            });

         return; 
    }

    const datosMarcas = {
        action: $('#idMarca').val() ? 'update' : 'create', // Si hay ID, es una edición
        idMarca: $('#idMarca').val(),
        descripcion: $('#descripcion').val(),
        estado: $('#activo').val(),
     
    };

    $.ajax({
        url: 'marcas.php',
        type: 'POST',
        data: datosMarcas,
        success: function (response) {
            if (response.status === 'success') {
                $('#modalMarcas').modal('hide'); // Cerrar el modal
                $('#tabla').DataTable().ajax.reload(); // Recargar la tabla

                // Mensaje de éxito
                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Marca agregado correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            } else {
                // Mensaje de error
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar Marca.',
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


$('#formMarcas').on('submit', function (event) {
    event.preventDefault(); // Evita que se recargue la página
});






function editMarcas(IdMarca) {
    // Obtener datos del marca específico mediante AJAX
    $.ajax({
        url: 'marcas.php', // Ruta al backend
        type: 'POST',
        data: { action: 'get', idMarca: IdMarca }, // Acción 'get' para obtener los datos del usuario
        success: function (response) {
            if (response.status === 'success') {
                const marca = response.data;

                // Rellenar los campos del modal con los datos del usuario
                $('#idMarca').val(marca.IdMarca);
                $('#descripcion').val(marca.Descripcion);
                $('#activo').val(marca.Estado);

                // Cambiar el título del modal
                $('#modalMarcasLabel').text('Editar Marca');

                // Abrir el modal
                $('#modalMarcas').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información del Marca.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
}



