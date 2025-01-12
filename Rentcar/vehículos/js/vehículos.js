$(document).ready(function () {
    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'vehiculos.php', // Ruta al archivo PHP
            type: 'POST',
            data: { action: 'read' }, // Acción para leer datos
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdVehiculo", "title": "ID" },
            { "data": "Vehiculo", "title": "Descripción" },
            { "data": "NumeroChasis", "title": "Chasis" },
            { "data": "NumeroMotor", "title": "Motor" },
            { "data": "NumeroPlaca", "title": "Placa" },
            { "data": "TipoVehiculo", "title": "Tipo de Vehículo" },
            { "data": "Marca", "title": "Marca" },
            { "data": "Modelo", "title": "Modelo" },
            { "data": "TipoCombustible", "title": "Tipo de Combustible" },
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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editVehiculo(${row.IdVehiculo})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteVehiculo(${row.IdVehiculo})">
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

    // Función para eliminar un vehículo
    window.deleteVehiculo = function (IdVehiculo) {
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
                    url: 'vehiculos.php',
                    type: 'POST',
                    data: { action: 'delete', idVehiculo: IdVehiculo },
                    success: function (response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false); // Recargar tabla
                            Swal.fire('Eliminado!', response.message || 'Vehículo eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el vehículo.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar el vehículo.', 'error');
                    }
                });
            }
        });
    };

    // Función para editar un vehículo
    window.editVehiculo = function (IdVehiculo) {
        $.ajax({
            url: 'vehiculos.php', // Ruta al backend
            type: 'POST',
            data: { action: 'get', idVehiculo: IdVehiculo }, // Enviar acción y el ID del vehículo
            success: function (response) {
                if (response.status === 'success') {
                    const vehiculo = response.data;
    
                    // Rellenar los campos del modal con los datos del vehículo
                    $('#idVehiculo').val(vehiculo.IdVehiculo);
                    $('#descripcion').val(vehiculo.Descripcion);
                    $('#numeroChasis').val(vehiculo.NumeroChasis);
                    $('#numeroMotor').val(vehiculo.NumeroMotor);
                    $('#numeroPlaca').val(vehiculo.NumeroPlaca);
                    $('#activo').val(vehiculo.Estado);
    
                    // Cargar los selectores con los valores preseleccionados
                    cargarMarcasEnSelect(vehiculo.IdMarca);
                    cargartiposvehiculosEnSelect(vehiculo.IdTipoVehiculo);
                    cargartiposmodeloEnSelect(vehiculo.IdModelo);
                    cargartiposcombustibleEnSelect(vehiculo.IdCombustible);
    
                    // Cambiar el título del modal
                    $('#modalVehiculoLabel').text('Editar Vehículo');
    
                    // Mostrar el modal
                    $('#modalVehiculos').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudo obtener la información del vehículo.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error AJAX:', xhr, status, error);
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
        $('#formVehiculos')[0].reset();
        $('#marcaSelect').val('');
        $('#idModeloSelect').val('');
        $('#modalVehiculosLabel').text('Crear Vehiculo');
        $('#modalVehiculos').modal('show');


    }
}



// Interceptar el evento submit del formulario
$('#formVehiculos').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    // Validar campos obligatorios
    const descripcion = $('#descripcion').val().trim();
    const numeroChasis = $('#numeroChasis').val().trim();
    const numeroMotor = $('#numeroMotor').val().trim();
    const numeroPlaca = $('#numeroPlaca').val().trim();
    const tipoVehiculoSeleccionado = $('#idTipoVehiculoSelect').val();
    const marcaSeleccionada = $('#marcaSelect').val();
    const modeloSeleccionado = $('#idModeloSelect').val();
    const tipoCombustibleSeleccionado = $('#idCombustibleSelect').val();

    // Validaciones
    if (!descripcion) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Descripción" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!numeroChasis || !numeroMotor || !numeroPlaca) {
        Swal.fire({
            title: 'Error',
            text: 'Los campos "Chasis", "Motor" y "Placa" no pueden estar vacíos.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!tipoVehiculoSeleccionado || !marcaSeleccionada || !modeloSeleccionado || !tipoCombustibleSeleccionado) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar "Tipo de Vehículo", "Marca", "Modelo" y "Tipo de Combustible".',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    // Preparar los datos del vehículo
    const datosVehiculo = {
        action: $('#idVehiculo').val() ? 'update' : 'create', // Si hay ID, es una edición
        idVehiculo: $('#idVehiculo').val(),
        descripcion: descripcion,
        numeroChasis: numeroChasis,
        numeroMotor: numeroMotor,
        numeroPlaca: numeroPlaca,
        idTipoVehiculo: tipoVehiculoSeleccionado,
        idMarca: marcaSeleccionada,
        idModelo: modeloSeleccionado,
        idCombustible: tipoCombustibleSeleccionado,
        estado: $('#activo').val(),
    };

    // Enviar datos al servidor
    $.ajax({
        url: 'vehiculos.php',
        type: 'POST',
        data: datosVehiculo,
        success: function (response) {
            if (response.status === 'success') {
                $('#modalVehiculos').modal('hide'); // Cerrar el modal
                $('#tabla').DataTable().ajax.reload(); // Recargar la tabla

                // Mensaje de éxito
                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Vehículo agregado correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            } else {
                // Mensaje de error
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar el vehículo.',
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
        url: 'vehiculos.php', // Ruta al archivo PHP
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





function cargartiposvehiculosEnSelect(IdTipoVehiculo) {
    $.ajax({
        url: 'vehiculos.php', // Ruta al archivo PHP
        type: 'POST',
        data: { action: 'detalleTipoVehiculo' }, // Cambiar según tu backend
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione Tipo Vehiculo </option>'; // Opción por defecto

            if (response.status === 'success' && response.data.length > 0) {
                const tipoVehiculo = response.data;

                tipoVehiculo.forEach(tipoVehiculo => {
                    // Si el IdTipoVehiculo coincide, agrega la opción con el atributo selected
                    if (IdTipoVehiculo && IdTipoVehiculo === tipoVehiculo.IdTipoVehiculo) {
                        opciones += `<option value="${tipoVehiculo.IdTipoVehiculo}" selected>${tipoVehiculo.Descripcion}</option>`;
                    } else {
                        opciones += `<option value="${tipoVehiculo.IdTipoVehiculo}">${tipoVehiculo.Descripcion}</option>`;
                    }
                });
            } else {
                console.warn('No se encontraron Tipo Vehiculo  o la respuesta está vacía');
            }

            // Agregar las opciones al select
            $('#idTipoVehiculoSelect').html(opciones);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar Tipo Vehiculo :', xhr.responseText || error);
            // En caso de error, dejar el select vacío con la opción por defecto
            $('#idTipoVehiculoSelect').html('<option value="" selected>Seleccione un Tipo Vehiculo </option>');
        }
    });
}




function cargartiposmodeloEnSelect(IdModelo) {
    $.ajax({
        url: 'vehiculos.php', // Ruta al archivo PHP
        type: 'POST',
        data: { action: 'detalleModelo' }, // Cambiar según tu backend
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione Modelo </option>'; // Opción por defecto

            if (response.status === 'success' && response.data.length > 0) {
                const modelo = response.data;

                modelo.forEach(modelo => {
                    // Si el IdModelo coincide, agrega la opción con el atributo selected
                    if (IdModelo && IdModelo === modelo.IdModelo) {
                        opciones += `<option value="${modelo.IdModelo}" selected>${modelo.Descripcion}</option>`;
                    } else {
                        opciones += `<option value="${modelo.IdModelo}">${modelo.Descripcion}</option>`;
                    }
                });
            } else {
                console.warn('No se encontraron Modelo  o la respuesta está vacía');
            }

            // Agregar las opciones al select
            $('#idModeloSelect').html(opciones);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar Tipo Vehiculo :', xhr.responseText || error);
            // En caso de error, dejar el select vacío con la opción por defecto
            $('#idModeloSelect').html('<option value="" selected>Seleccione un Modelo </option>');
        }
    });
}



function cargartiposcombustibleEnSelect(IdCombustible) {
    $.ajax({
        url: 'vehiculos.php', // Ruta al archivo PHP
        type: 'POST',
        data: { action: 'detalletiposcombustible' }, // Cambiar según tu backend
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione tipos de combustible </option>'; // Opción por defecto

            if (response.status === 'success' && response.data.length > 0) {
                const tiposcombustible = response.data;

                tiposcombustible.forEach(tiposcombustible => {
                    // Si el IdCombustible coincide, agrega la opción con el atributo selected
                    if (IdCombustible && IdCombustible === tiposcombustible.IdCombustible) {
                        opciones += `<option value="${tiposcombustible.IdCombustible}" selected>${tiposcombustible.Descripcion}</option>`;
                    } else {
                        opciones += `<option value="${tiposcombustible.IdCombustible}">${tiposcombustible.Descripcion}</option>`;
                    }
                });
            } else {
                console.warn('No se encontraron tipos de combustible   o la respuesta está vacía');
            }

            // Agregar las opciones al select
            $('#idCombustibleSelect').html(opciones);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar tipos de combustible  :', xhr.responseText || error);
            // En caso de error, dejar el select vacío con la opción por defecto
            $('#idCombustibleSelect').html('<option value="" selected>Seleccione un tipos de combustible  </option>');
        }
    });
}






// Llama a esta función al cargar la página o al abrir el modal
$(document).ready(function () {
    const idSeleccionado = 0; // Cambia este valor según el ID de la marca a seleccionar
    cargarMarcasEnSelect(idSeleccionado);
    cargartiposvehiculosEnSelect(idSeleccionado);
    cargartiposmodeloEnSelect(idSeleccionado);
    cargartiposcombustibleEnSelect(idSeleccionado);
});