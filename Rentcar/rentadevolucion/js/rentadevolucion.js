$(document).ready(function () {
    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'rentadevolucion.php', // Ruta al archivo PHP que gestiona RentaDevolucion
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdRenta", "title": "No. Renta" },
            { "data": "EmpleadoNombre", "title": "Empleado" },
            { "data": "Vehiculo", "title": "Vehículo" },
            { "data": "ClienteNombre", "title": "Cliente" },
            { "data": "FechaRenta", "title": "Fecha Renta" },
            { "data": "FechaDevolucion", "title": "Fecha Devolución" },
            { "data": "MontoPorDia", "title": "Monto x Día", "render": $.fn.dataTable.render.number(',', '.', 2, '$') },
            { "data": "CantidadDias", "title": "Cantidad de Días" },
            { "data": "Comentario", "title": "Comentario" },
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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editRentaDevolucion(${row.IdRenta})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteRenta(${row.IdRenta})">
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

    window.deleteRenta = function (idRenta) {
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
                    url: 'rentadevolucion.php', // Ruta al archivo PHP que gestiona RentaDevolucion
                    type: 'POST',
                    data: { action: 'delete', idRenta: idRenta },
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#tabla').DataTable().ajax.reload(null, false); // Recargar la tabla sin reiniciar la paginación
                            Swal.fire('Eliminado!', response.message || 'Renta eliminada.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar la renta.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar la renta.', 'error');
                        console.error('Error AJAX:', xhr, status, error);
                    }
                });
            }
        });
    };
});




function editRentaDevolucion(idRenta) {
    // Mostrar indicador de carga mientras se obtienen los datos
    Swal.fire({
        title: 'Cargando datos...',
        text: 'Por favor, espere.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Obtener datos de la renta específica mediante AJAX
    $.ajax({
        url: 'rentadevolucion.php', // Ruta al backend que gestiona las rentas
        type: 'POST',
        dataType: 'json',
        data: { action: 'get', idRenta: idRenta }, // Acción 'get' para obtener los datos de la renta
        success: function (response) {
            Swal.close(); // Cerrar indicador de carga

            if (response.status === 'success') {
                const renta = response.data;

                // Rellenar los campos del modal con los datos de la renta
                $('#idRenta').val(renta.IdRenta || '');
                $('#idVehiculoSelect').val(renta.IdVehiculo || '');
                $('#idClienteSelect').val(renta.IdCliente || '');
                $('#idEmpleadoSelect').val(renta.IdEmpleado || '');
                $('#fechaRenta').val(renta.FechaRenta || '');
                $('#fechaDevolucion').val(renta.FechaDevolucion || '');
                $('#montoDia').val(renta.MontoPorDia || '');
                $('#cantidadDias').val(renta.CantidadDias || '');
                $('#comentario').val(renta.Comentario || '');
                $('#estado').val(renta.Estado || '');

                // Cambiar el título del modal
                $('#modalRentaDevolucionLabel').text('Editar Renta o Devolución');

                // Abrir el modal
                $('#modalRentaDevolucion').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información de la renta.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.close(); // Cerrar indicador de carga
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
}
















function abrirModal(idRenta = null) {
    if (idRenta) {
        // Editar Renta/Devolución: cargar datos desde el servidor
        Swal.fire({
            title: 'Cargando datos...',
            text: 'Por favor, espere.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: 'rentadevolucion.php', // Ruta al archivo PHP que manejará la solicitud
            type: 'POST',
            dataType: 'json',
            data: { action: 'get', idRenta: idRenta },
            success: function (response) {
                Swal.close(); // Cerrar el indicador de carga

                if (response.status === 'success') {
                    const renta = response.data;

                    // Validar que los datos existan antes de asignarlos al formulario
                    $('#idRenta').val(renta.IdRenta || '');
                    $('#idEmpleadoSelect').val(renta.IdEmpleado || '');
                    $('#idVehiculoSelect').val(renta.IdVehiculo || '');
                    $('#idClienteSelect').val(renta.IdCliente || '');
                    $('#fechaRenta').val(renta.FechaRenta || '');
                    $('#fechaDevolucion').val(renta.FechaDevolucion || '');
                    $('#montoDia').val(renta.MontoPorDia || '');
                    $('#cantidadDias').val(renta.CantidadDias || '');
                    $('#comentario').val(renta.Comentario || '');
                    $('#estado').val(renta.Estado || '');

                    // Cambiar el título del modal
                    $('#modalRentaDevolucionLabel').text('Editar Renta/Devolución');

                    // Mostrar el modal
                    $('#modalRentaDevolucion').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos de la renta.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.close(); // Cerrar el indicador de carga
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error en la solicitud AJAX:', {
                    responseText: xhr.responseText,
                    status,
                    error
                });
            }
        });
    } else {
        // Crear Renta/Devolución: limpiar el formulario
        $('#formRentaDevolucion')[0].reset(); // Restablecer el formulario
        $('#idRenta').val(''); // Limpiar el campo oculto de ID
        $('#modalRentaDevolucionLabel').text('Crear Renta/Devolución');
        $('#modalRentaDevolucion').modal('show');
    }
}



// Llama a esta función al cargar la página o al abrir el modal
$(document).ready(function () {
    const idSeleccionado = 0; // Cambia este valor según el ID de la vehiculo a seleccionar
    cargarVehiculosEnSelect(idSeleccionado);
    cargarClientesEnSelect(idSeleccionado);
    cargarEmpleadosEnSelect(idSeleccionado);
 
});

function cargarVehiculosEnSelect(IdVehiculo) {
    $.ajax({
        url: 'rentadevolucion.php', // Ruta al archivo PHP
        type: 'POST',
        data: { action: 'detalleVehiculo' }, // Cambiar según tu backend
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione un vehiculo</option>'; // Opción por defecto

            if (response.status === 'success' && response.data.length > 0) {
                const vehiculos = response.data;

                vehiculos.forEach(vehiculo => {
                    // Si el IdVehiculo coincide, agrega la opción con el atributo selected
                    if (IdVehiculo && IdVehiculo === vehiculo.IdVehiculo) {
                        opciones += `<option value="${vehiculo.IdVehiculo}" selected>${vehiculo.Descripcion}</option>`;
                    } else {
                        opciones += `<option value="${vehiculo.IdVehiculo}">${vehiculo.Descripcion}</option>`;
                    }
                });
            } else {
                console.warn('No se encontraron vehiculos o la respuesta está vacía');
            }

            // Agregar las opciones al select
            $('#idVehiculoSelect').html(opciones);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar vehiculos:', xhr.responseText || error);
            // En caso de error, dejar el select vacío con la opción por defecto
            $('#idVehiculoSelect').html('<option value="" selected>Seleccione un vehiculo</option>');
        }
    });
}


function cargarClientesEnSelect(IdCliente) {
    $.ajax({
        url: 'rentadevolucion.php', // Ruta al archivo PHP
        type: 'POST',
        data: { action: 'detalleCliente' }, // Cambiar según tu backend
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione un cliente</option>'; // Opción por defecto

            if (response.status === 'success' && response.data.length > 0) {
                const clientes = response.data;

                clientes.forEach(cliente => {
                    // Si el IdVehiculo coincide, agrega la opción con el atributo selected
                    if (IdCliente && IdCliente === cliente.IdVehiculo) {
                        opciones += `<option value="${cliente.IdCliente}" selected>${cliente.Nombre}</option>`;
                    } else {
                        opciones += `<option value="${cliente.IdCliente}">${cliente.Nombre}</option>`;
                    }
                });
            } else {
                console.warn('No se encontraron clientes o la respuesta está vacía');
            }

            // Agregar las opciones al select
            $('#idClienteSelect').html(opciones);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar clientes:', xhr.responseText || error);
            // En caso de error, dejar el select vacío con la opción por defecto
            $('#idClienteSelect').html('<option value="" selected>Seleccione una clientes</option>');
        }
    });
}


function cargarEmpleadosEnSelect(IdEmpleado) {
    $.ajax({
        url: 'rentadevolucion.php', // Ruta al archivo PHP
        type: 'POST',
        data: { action: 'detalleEmpleado' }, // Cambiar según tu backend
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione un empleado</option>'; // Opción por defecto

            if (response.status === 'success' && response.data.length > 0) {
                const empleados = response.data;

                empleados.forEach(empleado => {
                    // Si el IdEmpleado coincide, agrega la opción con el atributo selected
                    if (IdEmpleado && IdEmpleado === empleado.IdEmpleado) {
                        opciones += `<option value="${empleado.IdEmpleado}" selected>${empleado.Nombre}</option>`;
                    } else {
                        opciones += `<option value="${empleado.IdEmpleado}">${empleado.Nombre}</option>`;
                    }
                });
            } else {
                console.warn('No se encontraron empleados o la respuesta está vacía');
            }

            // Agregar las opciones al select
            $('#idEmpleadoSelect').html(opciones);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar empleados:', xhr.responseText || error);
            // En caso de error, dejar el select vacío con la opción por defecto
            $('#idEmpleadoSelect').html('<option value="" selected>Seleccione una empleados</option>');
        }
    });
}

$('#formRentaDevolucion').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    // Obtener valores del formulario
    const idRenta = $('#idRenta').val();
    const idEmpleado = $('#idEmpleadoSelect').val();
    const idVehiculo = $('#idVehiculoSelect').val();
    const idCliente = $('#idClienteSelect').val();
    const fechaRenta = $('#fechaRenta').val();
    const fechaDevolucion = $('#fechaDevolucion').val();
    const montoDia = $('#montoDia').val();
    const cantidadDias = $('#cantidadDias').val();
    const comentario = $('#comentario').val().trim();
    const estado = $('#estado').val();

    // Validar campos obligatorios
    if (!validarCampos({
        idEmpleado,
        idVehiculo,
        idCliente,
        fechaRenta,
        fechaDevolucion,
        montoDia,
        cantidadDias
    })) {
        return;
    }

    // Validar que las fechas sean correctas
    if (new Date(fechaRenta) >= new Date(fechaDevolucion)) {
        mostrarAlerta('Error', 'La Fecha de Devolución debe ser posterior a la Fecha de Renta.', 'warning');
        return;
    }

    // Validar que montoDia y cantidadDias sean valores numéricos válidos
    if (isNaN(montoDia) || parseFloat(montoDia) <= 0) {
        mostrarAlerta('Error', 'El campo "Monto por Día" debe ser un número positivo.', 'warning');
        return;
    }
    if (isNaN(cantidadDias) || parseInt(cantidadDias) <= 0) {
        mostrarAlerta('Error', 'El campo "Cantidad de Días" debe ser un número entero positivo.', 'warning');
        return;
    }

    // Preparar datos para envío
    const datosRentaDevolucion = {
        action: idRenta ? 'update' : 'create',
        idRenta: idRenta || null,
        idEmpleado: idEmpleado,
        idVehiculo: idVehiculo,
        idCliente: idCliente,
        fechaRenta: fechaRenta,
        fechaDevolucion: fechaDevolucion,
        montoDia: parseFloat(montoDia),
        cantidadDias: parseInt(cantidadDias),
        comentario: comentario,
        estado: parseInt(estado)
    };

    console.log("Datos enviados:", datosRentaDevolucion); // Depuración

    // Mostrar alerta de carga mientras se procesa
    mostrarAlerta('Procesando...', 'Por favor, espere mientras se procesa la información.', 'info');

    // Enviar datos mediante AJAX
    $.ajax({
        url: 'rentadevolucion.php',
        type: 'POST',
        dataType: 'json',
        data: datosRentaDevolucion,
        success: function (response) {
            Swal.close(); // Cerrar alerta de carga
            if (response.status === 'success') {
                $('#formRentaDevolucion')[0].reset(); // Limpiar el formulario
                $('#modalRentaDevolucion').modal('hide');
                $('#tabla').DataTable().ajax.reload(); // Recargar tabla

                mostrarAlerta('Éxito', response.message || 'Registro guardado correctamente.', 'success');
            } else {
                mostrarAlerta('Error', response.message || 'No se pudo registrar la renta.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.close(); // Cerrar alerta de carga
            if (xhr.status === 0) {
                mostrarAlerta('Error', 'No se pudo conectar con el servidor.', 'error');
            } else {
                mostrarAlerta('Error', 'Hubo un error en el servidor. Inténtelo más tarde.', 'error');
            }
            console.error('Error AJAX:', { xhr, status, error });
        }
    });
});

// Función para validar campos obligatorios
function validarCampos(campos) {
    const nombresCampos = {
        idEmpleado: 'Empleado',
        idVehiculo: 'Vehículo',
        idCliente: 'Cliente',
        fechaRenta: 'Fecha de Renta',
        fechaDevolucion: 'Fecha de Devolución',
        montoDia: 'Monto por Día',
        cantidadDias: 'Cantidad de Días'
    };

    for (const [key, value] of Object.entries(campos)) {
        if (!value || value.toString().trim() === '') {
            mostrarAlerta('Error', `El campo "${nombresCampos[key] || key}" es obligatorio.`, 'warning');
            return false;
        }
    }
    return true;
}

// Función para mostrar alertas con SweetAlert
function mostrarAlerta(titulo, texto, icono) {
    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        confirmButtonText: 'OK'
    });
}
