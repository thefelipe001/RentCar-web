$(document).ready(function () {


    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'empleados.php', // Archivo que manejará las solicitudes
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdEmpleado", "title": "ID" },
            { "data": "Nombre", "title": "Nombre Completo" },
            { "data": "Cedula", "title": "Cédula" },
            { "data": "TandaLabor", "title": "Tanda Labor" },
            { "data": "PorcientoComision", "title": "Porciento Comision" },
            { "data": "FechaIngreso", "title": "Fecha Ingreso" },
            { "data": "Cargo", "title": "Puesto Laboral" },

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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editEmpleado(${row.IdEmpleado})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteEmpleado(${row.IdEmpleado})">
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
    


    window.deleteEmpleado = function (idEmpleado) {
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
                    url: 'empleados.php', // Ruta al archivo PHP que gestiona clientes
                    type: 'POST',
                    data: { action: 'delete', idEmpleado: idEmpleado }, // Cambia el nombre del parámetro según tu backend
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#tabla').DataTable().ajax.reload(null, false); // Recargar la tabla sin reiniciar la paginación
                            Swal.fire('Eliminado!', response.message || 'Empleado eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el Empleado.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar el Empleado.', 'error');
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
                    $('#idUsuarioSelect').val(response.data.IdUsuario);

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
        // Crear Empleado: limpiar el formulario
        $('#formEmpleado')[0].reset();
        cargarUsuarioEnSelect(0,0);
        $('#idSelect').css('display', ''); // Restaura el estilo original
        $('#idEmpleado').val('');
        $('#modalEmpleadoLabel').text('Crear Empleado');
        $('#modalEmpleado').modal('show');
    }
}


$('#formEmpleado').on('submit', function (event) {
    event.preventDefault(); // Evita que se recargue la página
});




// Función para validar la cédula dominicana (11 dígitos)
function validarCedulaDominicana(cedula) {
    // Eliminar cualquier espacio o carácter no numérico
    cedula = cedula.replace(/[^0-9]/g, '');

    // Verificar si tiene exactamente 11 dígitos
    if (!/^[0-9]{11}$/.test(cedula)) {
        return false;
    }

    // Coeficientes para la validación
    const multiplicadores = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
    let suma = 0;

    // Cálculo de la suma ponderada
    for (let i = 0; i < 10; i++) {
        let producto = parseInt(cedula.charAt(i)) * multiplicadores[i];
        suma += producto > 9 ? Math.floor(producto / 10) + (producto % 10) : producto;
    }

    // Validar el dígito verificador
    const digitoVerificador = (10 - (suma % 10)) % 10;
    return digitoVerificador === parseInt(cedula.charAt(10));
}

// Función para validar el número de tarjeta de crédito
function validarTarjetaCredito(numero) {
    // Eliminar cualquier carácter no numérico
    numero = numero.replace(/\D/g, '');

    // Verificar longitud mínima y máxima
    if (numero.length < 13 || numero.length > 19) {
        return false;
    }

    // Algoritmo de Luhn
    let suma = 0;
    let alternar = false;

    for (let i = numero.length - 1; i >= 0; i--) {
        let digito = parseInt(numero.charAt(i));

        if (alternar) {
            digito *= 2;
            if (digito > 9) {
                digito -= 9;
            }
        }

        suma += digito;
        alternar = !alternar;
    }

    // Es válido si la suma es divisible por 10
    return suma % 10 === 0;
}


$('#formEmpleado').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    const idUsuario = $('#idUsuario').val() || $('#idUsuarioSelect').val();
    const cedula = $('#cedula').val().trim();
    const tandaLabor = $('#tandaLabor').val();
    const porcientoComision = $('#porcientoComision').val().trim();
    const fechaIngreso = $('#fechaIngreso').val();
    const estado = $('#estado').val();
    const nombre = $('#nombre').val();
    const cargo = $('#cargo').val().trim();

    // Validar campos
    if (!idUsuario) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar un usuario.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!cedula) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Cédula" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!nombre) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Nombre" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!validarCedulaDominicana(cedula)) {
        Swal.fire({
            icon: 'warning',
            title: 'Cédula Incorrecta',
            text: 'La cédula proporcionada no es válida. Asegúrese de que contenga 11 dígitos numéricos y cumpla con el formato dominicano.',
            confirmButtonText: 'Reintentar'
        });
        return;
    }

    if (!tandaLabor) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar una "Tanda Laboral".',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!cargo) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Cargo" no puede estar vacío.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (cargo.length > 50) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Cargo" no puede tener más de 50 caracteres.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (isNaN(porcientoComision) || porcientoComision < 0 || porcientoComision > 100) {
        Swal.fire({
            title: 'Error',
            text: 'El porcentaje de comisión debe estar entre 0 y 100.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }


// Validar que la fecha de ingreso no esté vacía
if (!fechaIngreso) {
    Swal.fire({
        title: 'Error',
        text: 'Debe seleccionar una "Fecha de Ingreso".',
        icon: 'warning',
        confirmButtonText: 'OK'
    });
    return;
}

// Obtener la fecha de hoy
const fechaHoy = new Date();
fechaHoy.setHours(0, 0, 0, 0); // Asegurarse de comparar solo las fechas sin tiempo

// Convertir la fecha de ingreso a un objeto Date
const fechaIngresoObj = new Date(fechaIngreso);

// Validar que la fecha de ingreso sea igual o mayor a hoy
if (fechaIngresoObj < fechaHoy) {
    Swal.fire({
        title: 'Error',
        text: 'La "Fecha de Ingreso" debe ser igual o mayor al día de hoy.',
        icon: 'warning',
        confirmButtonText: 'OK'
    });
    return;
}


   

    // Preparar datos del empleado
    const datosEmpleado = {
        action: $('#idEmpleado').val() ? 'update' : 'create',
        idEmpleado: $('#idEmpleado').val(),
        idUsuario: idUsuario,
        cedula: cedula,
        tandaLabor: tandaLabor,
        porcientoComision: parseFloat(porcientoComision),
        fechaIngreso: fechaIngreso,
        estado: parseInt(estado),
        nombre: nombre,
        cargo: cargo
    };

    $.ajax({
        url: 'empleados.php',
        type: 'POST',
        data: datosEmpleado,
        success: function (response) {
            console.log('Respuesta del servidor:', response); // Depuración
            if (response.status === 'success') {
                $('#modalEmpleado').modal('hide');
                $('#formEmpleado')[0].reset(); // Limpiar el formulario
                $('#tabla').DataTable().ajax.reload(null, false); // Recargar la tabla
                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Empleado guardado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar el empleado.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error AJAX:', xhr, status, error);
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al conectar con el servidor.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});

// Limpiar el formulario al abrir el modal
$('#modalEmpleado').on('hidden.bs.modal', function () {
    $('#formEmpleado')[0].reset();
    $('#idUsuarioSelect').html('<option value="" selected>Seleccione un usuario</option>');
    $('#nombre').val('');
});


async function editEmpleado(idEmpleado) {
    console.log('editEmpleado llamado con idEmpleado:', idEmpleado);

    try {
        const response = await $.ajax({
            url: 'empleados.php',
            type: 'POST',
            data: { action: 'get', idEmpleado: idEmpleado },
        });

        console.log('Respuesta de empleados.php (get):', response);

        if (response.status === 'success') {
            const empleado = response.data;

            // Rellenar el resto de los campos del modal
            $('#idEmpleado').val(empleado.IdEmpleado);
            $('#cedula').val(empleado.Cedula);
            $('#nombre').val(empleado.Nombre);
            $('#tandaLabor').val(empleado.TandaLabor);
            $('#porcientoComision').val(empleado.PorcientoComision);
            $('#fechaIngreso').val(empleado.FechaIngreso);
            $('#cargo').val(empleado.Cargo);
            $('#estado').val(empleado.Estado);
            $('#idUsuario').val(response.data.IdUsuario);
            $('#idSelect').css('display', 'none'); // Oculta solo el campo select


            

            // Cambiar el título del modal
            $('#modalEmpleadoLabel').text('Editar Empleado');

            // Mostrar el modal
            $('#modalEmpleado').modal('show');
        } else {
            Swal.fire('Error', response.message || 'No se pudo obtener la información del empleado.', 'error');
        }
    } catch (error) {
        console.error('Error en editEmpleado:', error);
        Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
    }
}



let usuarios = []; // Variable global para almacenar los usuarios

function cargarUsuarioEnSelect(IdUsuario) {
    $.ajax({
        url: 'empleados.php',
        type: 'POST',
        data: { action: 'detalleusuarios' },
        success: function (response) {
            let opciones = '<option value="" selected>Seleccione un usuario</option>';

            if (response.status === 'success' && response.data.length > 0) {
                usuarios = response.data; // Guardar los usuarios en una variable global

                usuarios.forEach(usuario => {
                    opciones += `
                        <option value="${usuario.IdUsuario}" ${IdUsuario == usuario.IdUsuario ? 'selected' : ''}>
                            ${usuario.Nombres} ${usuario.Apellidos}
                        </option>
                    `;
                });

                // Actualizar el select
                $('#idUsuarioSelect').html(opciones);

                // Si hay un IdUsuario seleccionado, llenar el campo "Nombre Completo"
                if (IdUsuario) {
                    const usuarioSeleccionado = usuarios.find(u => u.IdUsuario == IdUsuario);
                    if (usuarioSeleccionado) {
                        $('#nombre').val(`${usuarioSeleccionado.Nombres} ${usuarioSeleccionado.Apellidos}`);
                    }
                }
            } else {
                console.warn('No se encontraron usuarios o la respuesta está vacía');
                $('#idUsuarioSelect').html('<option value="" selected>No hay usuarios disponibles</option>');
                $('#nombre').val(''); // Limpia el campo "Nombre Completo"
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar usuarios:', xhr.responseText || error);
            $('#idUsuarioSelect').html('<option value="" selected>Error al cargar usuarios</option>');
            $('#nombre').val(''); // Limpia el campo "Nombre Completo"
        }
    });
}





$('#idUsuarioSelect').on('change', function () {
    const idSeleccionado = $(this).val();

    if (idSeleccionado) {
        const usuarioSeleccionado = usuarios.find(u => u.IdUsuario == idSeleccionado);
        if (usuarioSeleccionado) {
            $('#nombre').val(`${usuarioSeleccionado.Nombres} ${usuarioSeleccionado.Apellidos}`);
        }
    } else {
        // Si no hay usuario seleccionado, limpia el campo "Nombre Completo"
        $('#nombre').val('');
    }
});



// Llama a esta función al cargar la página o al abrir el modal
$(document).ready(function () {
   cargarUsuarioEnSelect(0,0);
});