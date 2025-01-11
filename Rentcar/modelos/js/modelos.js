$(document).ready(function () {
    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'modelos.php',
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdModelo", "title": "ID" },
            { "data": "Marca", "title": "Marca" },
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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editModelos(${row.IdModelo})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteModelos(${row.IdModelo})">
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




    window.deleteModelos = function (IdModelo) {
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
                    url: 'modelos.php',
                    type: 'POST',
                    data: { action: 'delete', idModelo: IdModelo },
                    success: function (response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false); // Recargar tabla
                            Swal.fire('Eliminado!', response.message || 'Marca eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el Modelo.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar Modelo.', 'error');
                    }
                });
            }
        });
    };
});


function abrirModal(IdModelo = null) {
    if (IdModelo) {
        // Editar usuario: cargar datos del usuario desde el servidor
        $.ajax({
            url: 'modelo.php', // Asegúrate de usar la ruta correcta
            type: 'POST',
            data: { action: 'get', idModelo: IdModelo },
            success: function (response) {
                if (response.status === 'success') {
                    // Rellenar el formulario del modal con los datos del usuario
                    $('#idModelo').val(response.data.IdModelo);
                    $('#descripcion').val(response.data.Descripcion);
                    $('#activo').val(response.data.Activo);

                    // Cambiar el título del modal
                    $('#modalModelosLabel').text('Editar Modelo');

                    // Mostrar el modal
                    $('#modalModelos').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos de la Modelos.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error en la solicitud AJAX:', xhr, status, error);
            }
        });
    } else {
        // Crear usuario: limpiar el formulario
        $('#formModelos')[0].reset();
        $('#idModelo').val('');
        $('#marcaSelect').val(''); // Asegurarse de que la marca esté en blanco
        $('#modalModelosLabel').text('Crear Modelo');
        $('#modalModelos').modal('show');

    }
}



// Interceptar el evento submit del formulario
$('#formModelos').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    const descripcion = $('#descripcion').val().trim();
    const marcaSeleccionada = $('#marcaSelect').val(); 

    if (!marcaSeleccionada) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar una marca antes de guardar.',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return; // Detener la ejecución si la validación falla
    }


    if (!descripcion) {
         Swal.fire({
             title: 'Error',
             text: 'El campo "Descripción" no puede estar vacío.',
             icon: 'warning',
             confirmButtonText: 'OK'
            });

         return; 
    }

    

    const datosModelos = {
        action: $('#idModelo').val() ? 'update' : 'create', // Si hay ID, es una edición
        idModelo: $('#idModelo').val(),
        idMarca: $('#marcaSelect').val(),
        descripcion: $('#descripcion').val(),
        estado: $('#activo').val(),
     
    };

    $.ajax({
        url: 'modelos.php',
        type: 'POST',
        data: datosModelos,
        success: function (response) {
            if (response.status === 'success') {
                $('#modalModelos').modal('hide'); // Cerrar el modal
                $('#tabla').DataTable().ajax.reload(); // Recargar la tabla

                // Mensaje de éxito
                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Modelo agregado correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            } else {
                // Mensaje de error
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar Modelo.',
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






function editModelos(IdModelo) {
    // Obtener datos del modelo específico mediante AJAX
    $.ajax({
        url: 'modelos.php', // Ruta al backend
        type: 'POST',
        data: { action: 'get', idModelo: IdModelo }, // Acción 'get' para obtener los datos del modelo
        success: function (response) {
            if (response.status === 'success') {
                const modelo = response.data;

                // Rellenar los campos del modal con los datos del modelo
                $('#idModelo').val(modelo.idModelo);
                $('#descripcion').val(modelo.Descripcion);
                $('#activo').val(modelo.Estado);

                // Cargar marcas en el select y seleccionar la correspondiente
                cargarMarcasEnSelect(modelo.IdMarca);

                // Cambiar el título del modal
                $('#modalModelosLabel').text('Editar Modelo');

                // Abrir el modal
                $('#modalModelos').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información del modelo.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
}




function cargarMarcasEnSelect(IdMarca) {
    $.ajax({
        url: 'modelos.php', // Ruta al archivo PHP
        type: 'POST',
        data: { action: 'detalleMarca' }, // Cambiar según tu backend
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione una marca</option>'; // Opción por defecto

            if (response.status === 'success' && response.data.length > 0) {
                const marcas = response.data;

                marcas.forEach(marca => {
                    // Si el IdMarca coincide, agrega la opción con el atributo selected
                    if (IdMarca && IdMarca === marca.IdMarca) {
                        opciones += `<option value="${marca.IdMarca}" selected>${marca.Descripcion}</option>`;
                    } else {
                        opciones += `<option value="${marca.IdMarca}">${marca.Descripcion}</option>`;
                    }
                });
            } else {
                console.warn('No se encontraron marcas o la respuesta está vacía');
            }

            // Agregar las opciones al select
            $('#marcaSelect').html(opciones);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar marcas:', xhr.responseText || error);
            // En caso de error, dejar el select vacío con la opción por defecto
            $('#marcaSelect').html('<option value="" selected>Seleccione una marca</option>');
        }
    });
}


// Llama a esta función al cargar la página o al abrir el modal
$(document).ready(function () {
    const idSeleccionado = 2; // Cambia este valor según el ID de la marca a seleccionar
    cargarMarcasEnSelect(idSeleccionado);
});
