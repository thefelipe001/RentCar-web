$(document).ready(function () {


    var table = $('#tabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: 'clientes.php', // Archivo que manejará las solicitudes
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr, error, thrown) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire('Error', 'No se pudo cargar la tabla.', 'error');
            }
        },
        "columns": [
            { "data": "IdCliente", "title": "ID" },
            { "data": "Nombre", "title": "Nombre" },
            { "data": "Cedula", "title": "Cédula" },
            { "data": "NumeroTarjetaCR", "title": "Tarjeta CR" },
            { "data": "LimiteCredito", "title": "Límite de Crédito" },
            { "data": "TipoPersona", "title": "Tipo de Persona" },
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
                        <button class="btn btn-warning btn-sm" title="Editar" onclick="editCliente(${row.IdCliente})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" title="Eliminar" onclick="deleteCliente(${row.IdCliente})">
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
    


    window.deleteCliente = function (idCliente) {
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
                    url: 'clientes.php', // Ruta al archivo PHP que gestiona clientes
                    type: 'POST',
                    data: { action: 'delete', idCliente: idCliente }, // Cambia el nombre del parámetro según tu backend
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#tabla').DataTable().ajax.reload(null, false); // Recargar la tabla sin reiniciar la paginación
                            Swal.fire('Eliminado!', response.message || 'Cliente eliminado.', 'success');
                        } else {
                            Swal.fire('Error!', response.message || 'No se pudo eliminar el cliente.', 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire('Error!', 'Hubo un problema al eliminar el cliente.', 'error');
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
        // Crear cliente: limpiar el formulario
        $('#formCliente')[0].reset();
        $('#idCliente').val('');
        $('#modalClienteLabel').text('Crear Cliente');
        $('#modalCliente').modal('show');
    }
}


$('#formCliente').on('submit', function (event) {
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













$('#formCliente').on('submit', function (event) {
    event.preventDefault(); // Evita el comportamiento predeterminado del formulario

    const nombre = $('#nombre').val().trim();
    const cedula = $('#cedula').val().trim();
    const tarjetaCR = $('#numeroTarjetaCR').val().trim();
    const limiteCredito = $('#limiteCredito').val().trim();
    const tipoPersona = $('#tipoPersona').val();

    // Validar campos
    if (!nombre) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Nombre" no puede estar vacío.',
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


      // Validar Identificación (Cédula Dominicana de 11 dígitos)
    if (cedula === "" || !validarCedulaDominicana(cedula)) 
    {
    Swal.fire({
        icon: 'warning',
        title: 'Cédula Incorrecta',
        text: 'La cédula proporcionada no es válida. Asegúrese de que contenga 11 dígitos numéricos y cumpla con el formato dominicano.',
        confirmButtonText: 'Reintentar'
    });

    return;
   }


     // Validar número de tarjeta
    if (tarjetaCR === "" || !validarTarjetaCredito(tarjetaCR)) 
    {
         Swal.fire({
              icon: 'warning',
              title: 'Número de Tarjeta Inválido',
              text: 'El número de tarjeta proporcionado no es válido. Asegúrese de que contenga entre 13 y 19 dígitos numéricos y cumpla con el formato correcto.',
              confirmButtonText: 'Reintentar'
          });

       return false;
    }



    if (!limiteCredito || isNaN(limiteCredito) || parseFloat(limiteCredito) <= 0) {
        Swal.fire({
            title: 'Error',
            text: 'El campo "Límite de Crédito" debe ser un número positivo.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!tipoPersona) {
        Swal.fire({
            title: 'Error',
            text: 'Debe seleccionar un "Tipo de Persona".',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Preparar datos del cliente
    const datosCliente = {
        action: $('#idCliente').val() ? 'update' : 'create',
        idCliente: $('#idCliente').val(),
        nombre: nombre,
        cedula: cedula,
        numeroTarjetaCR: tarjetaCR || null,
        limiteCredito: parseFloat(limiteCredito),
        tipoPersona: tipoPersona,
        estado: parseInt($('#estado').val()) || 0
    };

    console.log(datosCliente); // Depuración

    $.ajax({
        url: 'clientes.php',
        type: 'POST',
        data: datosCliente,
        success: function (response) {
            if (response.status === 'success') {
                $('#modalCliente').modal('hide');
                $('#tabla').DataTable().ajax.reload();

                Swal.fire({
                    title: 'Éxito!',
                    text: response.message || 'Cliente agregado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo guardar el cliente.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al conectar con el servidor.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            console.error('Error AJAX:', xhr, status, error);
        }
    });
});






function editCliente(idCliente) {
    // Obtener datos del cliente específico mediante AJAX
    $.ajax({
        url: 'clientes.php', // Ruta al backend
        type: 'POST',
        data: { action: 'get', idCliente: idCliente }, // Acción 'get' para obtener los datos del cliente
        success: function (response) {
            if (response.status === 'success') {
                const cliente = response.data;

                // Rellenar los campos del modal con los datos del cliente
                $('#idCliente').val(cliente.IdCliente);
                $('#nombre').val(cliente.Nombre);
                $('#cedula').val(cliente.Cedula);
                $('#numeroTarjetaCR').val(cliente.NumeroTarjetaCR || ''); // Manejar valores nulos
                $('#limiteCredito').val(cliente.LimiteCredito);
                $('#tipoPersona').val(cliente.TipoPersona);
                $('#estado').val(cliente.Estado);

                // Cambiar el título del modal
                $('#modalClienteLabel').text('Editar Cliente');

                // Abrir el modal
                $('#modalCliente').modal('show');
            } else {
                Swal.fire('Error', response.message || 'No se pudo obtener la información del cliente.', 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
            console.error('Error AJAX:', xhr, status, error);
        }
    });
}


