$(document).ready(function () {
    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'tiposcombustible.php',
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdCombustible", "title": "ID" },
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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="edittiposcombustible(${row.IdCombustible})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deletetiposcombustible(${row.IdCombustible})">
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


    window.deletetiposcombustible  = function (IdCombustible) {
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
                    url: 'tiposcombustible.php',
                    type: 'POST',
                    data: { action: 'delete', idCombustible: IdCombustible },
                    success: function (response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false); // Recargar tabla
                            Swal.fire('Eliminado!', response.message || 'tiposcombustible eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el tiposcombustible.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar tiposcombustible.', 'error');
                    }
                });
            }
        });
    };
});


function abrirModal(IdCombustible = null) {
    if (IdCombustible) {
        // Editar tiposcombustible: cargar datos del tiposcombustible desde el servidor
        $.ajax({
            url: 'tiposcombustible.php', // Asegúrate de usar la ruta correcta
            type: 'POST',
            data: { action: 'get', idCombustible: IdCombustible },
            success: function (response) {
                if (response.status === 'success') {
                    // Rellenar el formulario del modal con los datos del tiposcombustible
                    $('#idCombustible').val(response.data.IdCombustible);
                    $('#descripcion').val(response.data.Descripcion);
                    $('#activo').val(response.data.Activo);

                    // Cambiar el título del modal
                    $('#modaltiposcombustibleLabel').text('Editar Tipos de Combustible');

                    // Mostrar el modal
                    $('#modaltiposcombustible').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos de la tiposcombustible.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error en la solicitud AJAX:', xhr, status, error);
            }
        });
    } else {
        // Crear usuario: limpiar el formulario
        $('#formtiposcombustible')[0].reset();
        $('#IdCombustible').val('');
        $('#modaltiposcombustibleLabel').text('Tipos de Combustible');
        $('#modaltiposcombustible').modal('show');
    }
}



// Interceptar el evento submit del formulario
$('#formtiposcombustible').on('submit', function (event) {
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

    const tiposcombustible = {
        action: $('#IdCombustible').val() ? 'update' : 'create', // Si hay ID, es una edición
        idCombustible: $('#IdCombustible').val(),
        descripcion: $('#descripcion').val(),
        estado: $('#activo').val(),
     
    };

    $.ajax({
        url: 'tiposcombustible.php',
        type: 'POST',
        data: tiposcombustible,
        success: function (response) {
            if (response.status === 'success') {
                $('#modaltiposcombustible').modal('hide'); // Cerrar el modal
                $('#tabla').DataTable().ajax.reload(); // Recargar la tabla

                // Mensaje de éxito
                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Tipos de Combustible agregado correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            } else {
                // Mensaje de error
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar Tipos de Combustible.',
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


$('#formtiposcombustible').on('submit', function (event) {
    event.preventDefault(); // Evita que se recargue la página
});






function edittiposcombustible(IdCombustible) {
    // Obtener datos del tiposcombustible específico mediante AJAX
    $.ajax({
        url: 'tiposcombustible.php', // Ruta al backend
        type: 'POST',
        data: { action: 'get', idCombustible: IdCombustible }, // Acción 'get' para obtener los datos del tiposcombustible
        success: function (response) {
            if (response.status === 'success') {
                const tiposcombustible = response.data;

                // Rellenar los campos del modal con los datos del tiposcombustible
                $('#IdCombustible').val(tiposcombustible.idCombustible);
                $('#descripcion').val(tiposcombustible.Descripcion);
                $('#activo').val(tiposcombustible.Estado);

                // Cambiar el título del modal
                $('#modaltiposcombustibleLabel').text('Editar Tipos de Combustible');

                // Abrir el modal
                $('#modaltiposcombustible').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información del tiposcombustible.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
}



