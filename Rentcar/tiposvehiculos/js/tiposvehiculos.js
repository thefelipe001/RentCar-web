$(document).ready(function () {
    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'tiposvehiculos.php',
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdTipoVehiculo", "title": "ID" },
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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editTiposvehiculos(${row.IdTipoVehiculo})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteTiposvehiculos(${row.IdTipoVehiculo})">
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


    window.deleteTiposvehiculos = function (IdTipoVehiculo) {
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
                    url: 'tiposvehiculos.php',
                    type: 'POST',
                    data: { action: 'delete', idTipoVehiculo: IdTipoVehiculo },
                    success: function (response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false); // Recargar tabla
                            Swal.fire('Eliminado!', response.message || 'Tipos de Vehiculos eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el Tipos de Vehiculos.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar el Tipos de Vehiculos.', 'error');
                    }
                });
            }
        });
    };
});


function abrirModal(idTipoVehiculo = null) {
    if (idTipoVehiculo) {
        // Editar Tipos de Vehiculos: cargar datos del Tipos de Vehiculos desde el servidor
        $.ajax({
            url: 'tiposvehiculos.php', // Asegúrate de usar la ruta correcta
            type: 'POST',
            data: { action: 'get', idTipoVehiculo: idTipoVehiculo },
            success: function (response) {
                if (response.status === 'success') {
                    // Rellenar el formulario del modal con los datos del Tipos de Vehiculos
                    $('#idTipoVehiculo').val(response.data.IdTipoVehiculo);
                    $('#descripcion').val(response.data.Descripcion);
                    $('#activo').val(response.data.Activo);

                    // Cambiar el título del modal
                    $('#modalTiposVehiculosLabel').text('Editar Tipos de Vehiculos');

                    // Mostrar el modal
                    $('#modalTiposvehiculos').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos del Tipos de Vehiculos.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error en la solicitud AJAX:', xhr, status, error);
            }
        });
    } else {
        // Crear Tipos de Vehiculos: limpiar el formulario
        $('#formTiposvehiculos')[0].reset();
        $('#idTipoVehiculo').val('');
        $('#modalTiposvehiculosLabel').text('Crear Tipos de Vehiculos');
        $('#modalTiposvehiculos').modal('show');
    }
}



// Interceptar el evento submit del formulario
$('#formTiposvehiculos').on('submit', function (event) {
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

    const datosTiposvehiculos = {
        action: $('#idTipoVehiculo').val() ? 'update' : 'create', // Si hay ID, es una edición
        idTipoVehiculo: $('#idTipoVehiculo').val(),
        descripcion: $('#descripcion').val(),
        estado: $('#activo').val(),
     
    };

    $.ajax({
        url: 'tiposvehiculos.php',
        type: 'POST',
        data: datosTiposvehiculos,
        success: function (response) {
            if (response.status === 'success') {
                $('#modalTiposvehiculos').modal('hide'); // Cerrar el modal
                $('#tabla').DataTable().ajax.reload(); // Recargar la tabla

                // Mensaje de éxito
                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Tipos vehiculos agregado correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            } else {
                // Mensaje de error
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar el Tipos de Vehiculos.',
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


$('#formTiposvehiculos').on('submit', function (event) {
    event.preventDefault(); // Evita que se recargue la página
});






function editTiposvehiculos(idTipoVehiculo) {
    // Obtener datos del tipos de vehiculos específico mediante AJAX
    $.ajax({
        url: 'tiposvehiculos.php', // Ruta al backend
        type: 'POST',
        data: { action: 'get', idTipoVehiculo: idTipoVehiculo }, // Acción 'get' para obtener los datos del Tipos de Vehiculos
        success: function (response) {
            if (response.status === 'success') {
                const tiposvehiculos = response.data;

                // Rellenar los campos del modal con los datos del Tipos de Vehiculos
                $('#idTipoVehiculo').val(tiposvehiculos.IdTipoVehiculo);
                $('#descripcion').val(tiposvehiculos.Descripcion);
                $('#activo').val(tiposvehiculos.Estado);

                // Cambiar el título del modal
                $('#modalTiposvehiculosLabel').text('Editar Tipos de Vehiculos');

                // Abrir el modal
                $('#modalTiposvehiculos').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información del Tipos de Vehiculos.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
}

