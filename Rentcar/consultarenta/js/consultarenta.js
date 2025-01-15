$(document).ready(function () {
    $('#tabla').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'consultarenta.php',
            type: 'POST',
            data: { action: 'read' },
            error: function (xhr) {
                console.error('Error en el AJAX:', xhr.responseText);
            }
        },
        columns: [
            { data: "IdRenta", title: "No. Renta" },
            { data: "EmpleadoNombre", title: "Empleado" },
            { data: "Vehiculo", title: "Vehículo" },
            { data: "ClienteNombre", title: "Cliente" },
            { data: "FechaRenta", title: "Fecha Renta" },
            { data: "FechaDevolucion", title: "Fecha Devolución" },
            { data: "MontoPorDia", title: "Monto x Día", render: $.fn.dataTable.render.number(',', '.', 2, '$') },
            { data: "CantidadDias", title: "Cantidad de Días" },
            { data: "Comentario", title: "Comentario" },
            {
                data: "Estado",
                title: "Estado",
                render: function (data) {
                    return data === 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                }
            },
            {
                data: null,
                title: "Acciones",
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm" onclick="editRenta(${row.IdRenta})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteRenta(${row.IdRenta})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    `;
                }
            }
        ]
    });
});
