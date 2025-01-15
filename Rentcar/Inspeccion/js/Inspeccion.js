$(document).ready(function () {


    $(document).ready(function () {
        var table = $('#tabla').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: 'inspeccion.php',
                type: 'POST',
                data: { action: 'read' },
                error: function (xhr, error, thrown) {
                    console.error('Error AJAX:', xhr.responseText);
                    Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
                }
            },
            "columns": [
                { "data": "IdTransaccion", "title": "ID Transacción" },
                { "data": "Vehiculo", "title": "Vehículo" },
                { "data": "ClienteNombre", "title": "Cliente" },
                {
                    "data": "TieneRalladuras",
                    "title": "Ralladuras",
                    "render": function (data) {
                        return data === 1 ? 'Sí' : 'No';
                    }
                },
                { "data": "CantidadCombustible", "title": "Combustible" },
                {
                    "data": "TieneGomaRespaldo",
                    "title": "Goma Respaldo",
                    "render": function (data) {
                        return data === 1 ? 'Sí' : 'No';
                    }
                },
                {
                    "data": "TieneGato",
                    "title": "Gato",
                    "render": function (data) {
                        return data === 1 ? 'Sí' : 'No';
                    }
                },
                {
                    "data": "TieneRoturasCristal",
                    "title": "Roturas Cristal",
                    "render": function (data) {
                        return data === 1 ? 'Sí' : 'No';
                    }
                },
                { "data": "EstadoGomas", "title": "Estado Gomas" },
                { "data": "Observaciones", "title": "Observaciones" },
                { "data": "Fecha", "title": "Fecha" },
                { "data": "EmpleadoNombre", "title": "Empleado Inspección" },
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
                            <button class="btn btn-warning btn-sm" title="Editar" onclick="editInspeccion(${row.IdTransaccion})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteInspeccion(${row.IdTransaccion})">
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
    });
    



    window.deleteInspeccion = function (idTransaccion) {
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
                    url: 'inspeccion.php', // Ruta al archivo PHP que gestiona inspecciones
                    type: 'POST',
                    data: { action: 'delete', idTransaccion: idTransaccion }, // Cambiar el parámetro según tu backend
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#tabla').DataTable().ajax.reload(null, false); // Recargar la tabla sin reiniciar la paginación
                            Swal.fire('Eliminado!', response.message || 'Inspección eliminada.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar la inspección.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar la inspección.', 'error');
                        console.error('Error AJAX:', xhr, status, error);
                    }
                });
            }
        });
    };
    
    
});


function abrirModal(idCliente = null) {
    if (idCliente) {
        // Editar cliente: cargar datos del cliente desde el servidor
        $.ajax({
            url: 'clientes.php', // Ruta al archivo PHP que manejará la solicitud
            type: 'POST',
            data: { action: 'get', id_cliente: idCliente },
            success: function (response) {
                if (response.status === 'success') {
                    // Rellenar el formulario del modal con los datos del cliente
                    $('#idCliente').val(response.data.IdCliente);
                    $('#nombre').val(response.data.Nombre);
                    $('#cedula').val(response.data.Cedula);
                    $('#numeroTarjetaCR').val(response.data.NumeroTarjetaCR);
                    $('#limiteCredito').val(response.data.LimiteCredito);
                    $('#tipoPersona').val(response.data.TipoPersona);
                    $('#estado').val(response.data.Estado);

                    // Cambiar el título del modal
                    $('#modalClienteLabel').text('Editar Cliente');

                    // Mostrar el modal
                    $('#modalCliente').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'No se pudieron cargar los datos del cliente.', 'error');
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                console.error('Error en la solicitud AJAX:', xhr, status, error);
            }
        });
    } else {
        // Crear Inspeccion: limpiar el formulario
      //  $('#formInspeccion')[0].reset();
        $('#modalInspeccionLabel').text('Crear Inspeccion');
        $('#modalInspeccion').modal('show');
    }
}


$('#formCliente').on('submit', function (event) {
    event.preventDefault(); // Evita que se recargue la página
});






$('#formInspeccion').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    // Obtener valores del formulario
    const idVehiculo = $('#idVehiculoSelect').val();
    const idCliente = $('#idClienteSelect').val();
    const idEmpleado = $('#idEmpleadoSelect').val();
    const tieneRalladuras = $('#tieneRalladuras').val();
    const cantidadCombustible = $('#cantidadCombustible').val();
    const tieneGomaRespaldo = $('#tieneGomaRespaldo').val();
    const tieneGato = $('#tieneGato').val();
    const tieneRoturasCristal = $('#tieneRoturasCristal').val();
    const estadoGomasArray = [
        $('#goma1').is(':checked') ? 1 : 0,
        $('#goma2').is(':checked') ? 1 : 0,
        $('#goma3').is(':checked') ? 1 : 0,
        $('#goma4').is(':checked') ? 1 : 0
    ];
    
    // Sumar la cantidad de gomas marcadas
    const cantidadBuenas = estadoGomasArray.reduce((acc, val) => acc + val, 0);
    
    // Evaluar el estado general
    let estadoGomas;
    switch (cantidadBuenas) {
        case 0:
            estadoGomas = 'Malo';
            break;
        case 1:
            estadoGomas = 'Más o menos';
            break;
        case 2:
            estadoGomas = 'Regular';
            break;
        case 3:
            estadoGomas = 'Bueno';
            break;
        case 4:
            estadoGomas = 'Excelente';
            break;
        default:
            estadoGomas = 'Indefinido'; // Esto no debería suceder, pero es un caso de seguridad
    }
        
  
    const fecha = $('#fecha').val();
    const estado = $('#estado').val();
    const observaciones = $('#observaciones').val().trim();

    // Validar campos obligatorios
    if (!validarCampos({ idVehiculo, idCliente, idEmpleado, fecha })) {
        return;
    }

    // Preparar datos para envío
    const datosInspeccion = {
        action: $('#idTransaccion').val() ? 'update' : 'create',
        idTransaccion: $('#idTransaccion').val() || null,
        idVehiculo: idVehiculo,
        idCliente: idCliente,
        idEmpleado: idEmpleado,
        tieneRalladuras: tieneRalladuras,
        cantidadCombustible: cantidadCombustible,
        tieneGomaRespaldo: tieneGomaRespaldo,
        tieneGato: tieneGato,
        tieneRoturasCristal: tieneRoturasCristal,
        estadoGomas: estadoGomas,
        fecha: fecha,
        estado: estado,
        observaciones: observaciones
    };

    console.log("Datos enviados:", datosInspeccion); // Depuración

    // Enviar datos mediante AJAX
    $.ajax({
        url: 'inspeccion.php',
        type: 'POST',
        dataType: 'json',
        data: datosInspeccion,
        success: function (response) {
            if (response.status === 'success') {
                $('#modalInspeccion').modal('hide');
                $('#tabla').DataTable().ajax.reload(); // Recargar tabla

                mostrarAlerta('Éxito', response.message || 'Inspección registrada correctamente.', 'success');
            } else {
                mostrarAlerta('Error', response.message || 'No se pudo registrar la inspección.', 'error');
            }
        },
        error: function (xhr, status, error) {
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
    for (const [key, value] of Object.entries(campos)) {
        if (!value) {
            mostrarAlerta('Error', `El campo "${key}" es obligatorio.`, 'warning');
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




function editInspeccion(idTransaccion) {
    // Obtener datos de la inspección específica mediante AJAX
    $.ajax({
        url: 'inspeccion.php', // Ruta al backend que gestiona inspecciones
        type: 'POST',
        dataType: 'json',
        data: { action: 'get', idTransaccion: idTransaccion }, // Acción 'get' para obtener los datos de la inspección
        success: function (response) {
            if (response.status === 'success') {
                const inspeccion = response.data;

                // Rellenar los campos del modal con los datos de la inspección
                $('#idTransaccion').val(inspeccion.IdTransaccion);
                $('#idVehiculoSelect').val(inspeccion.IdVehiculo);
                $('#idClienteSelect').val(inspeccion.IdCliente);
                $('#idEmpleadoSelect').val(inspeccion.EmpleadoInspeccion);
                $('#tieneRalladuras').val(inspeccion.TieneRalladuras);
                $('#cantidadCombustible').val(inspeccion.CantidadCombustible);
                $('#tieneGomaRespaldo').val(inspeccion.TieneGomaRespaldo);
                $('#tieneGato').val(inspeccion.TieneGato);
                $('#tieneRoturasCristal').val(inspeccion.TieneRoturasCristal);
                $('#fecha').val(inspeccion.Fecha);
                $('#estado').val(inspeccion.Estado);
                $('#observaciones').val(inspeccion.Observaciones || ''); // Manejar valores nulos

                // Evaluar el estado general y actualizar el estado de las gomas
                let estadoGomasArray;
                switch (inspeccion.EstadoGomas) {
                    case 'Malo':
                        estadoGomasArray = ['0', '0', '0', '0'];
                        break;
                    case 'Más o menos':
                        estadoGomasArray = ['1', '0', '0', '0'];
                        break;
                    case 'Regular':
                        estadoGomasArray = ['1', '1', '0', '0'];
                        break;
                    case 'Bueno':
                        estadoGomasArray = ['1', '1', '1', '0'];
                        break;
                    case 'Excelente':
                        estadoGomasArray = ['1', '1', '1', '1'];
                        break;
                    default:
                        estadoGomasArray = ['0', '0', '0', '0']; // Seguridad
                }

                // Actualizar checkboxes de estado de gomas
                $('#goma1').prop('checked', estadoGomasArray[0] === '1');
                $('#goma2').prop('checked', estadoGomasArray[1] === '1');
                $('#goma3').prop('checked', estadoGomasArray[2] === '1');
                $('#goma4').prop('checked', estadoGomasArray[3] === '1');


              
                // Cambiar el título del modal
                $('#modalInspeccionLabel').text('Editar Inspección');

                // Abrir el modal
                $('#modalInspeccion').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información de la inspección.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
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
        url: 'inspeccion.php', // Ruta al archivo PHP
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
        url: 'inspeccion.php', // Ruta al archivo PHP
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
        url: 'inspeccion.php', // Ruta al archivo PHP
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
