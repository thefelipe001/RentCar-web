SELECT TOP (1000) [id_cliente]
      ,[nombre]
      ,[cedula]
      ,[numero_tarjeta_cr]
      ,[limite_credito]
      ,[tipo_persona]
      ,[estado]
      ,[CreadoPor]
      ,[FechaCreacion]
  FROM [RentCarDB].[dbo].[Clientes]
  -- Tabla USUARIO
INSERT INTO USUARIO (Nombres, Apellidos, Correo, Contrasena, EsAdministrador, Activo)
VALUES 
('Admin', 'Demo', 'admin@example.com', 'password123', 1, 1),
('Empleado', 'Demo', 'empleado@example.com', 'password123', 0, 1);

-- Tabla Clientes
INSERT INTO Clientes (nombre, cedula, numero_tarjeta_cr, limite_credito, tipo_persona, estado, CreadoPor)
VALUES 
('Carlos Gómez', '001-1234567-8', '1234-5678-9012-3456', 50000.00, 'Física', 1, 1),
('Empresa XYZ', '12345678-9', '5678-9012-3456-7890', 100000.00, 'Jurídica', 1, 1);

-- Tabla Empleados
INSERT INTO Empleados (nombre, cedula, tanda_labor, porcentaje_comision, fecha_ingreso, estado, IdUsuario, CreadoPor)
VALUES 
('Luis Torres', '001-8765432-1', 'Matutina', 10.00, '2023-01-01', 1, 1, 1),
('Ana Martínez', '002-5678901-2', 'Vespertina', 15.00, '2023-02-01', 1, 2, 1);

-- Tabla Vehiculos
INSERT INTO Vehiculos (descripcion, numero_chasis, numero_motor, numero_placa, id_tipo_vehiculo, id_marca, id_modelo, id_tipo_combustible, estado, Imagen, CreadoPor)
VALUES 
('Vehículo 1', 'CH123456', 'MOT123456', 'PL123456', 1, 1, 1, 1, 1, 'imagenes/vehiculos/vehiculo1.jpg', 1),
('Vehículo 2', 'CH654321', 'MOT654321', 'PL654321', 2, 2, 2, 2, 1, 'imagenes/vehiculos/vehiculo2.jpg', 1),
('Vehículo 3', 'CH789123', 'MOT789123', 'PL789123', 3, 3, 3, 3, 1, 'imagenes/vehiculos/vehiculo3.jpg', 1);

-- Tabla Inspecciones
INSERT INTO Inspecciones (id_transaccion, id_vehiculo, id_cliente, ralladuras, cantidad_combustible, goma_respuesta, gato, roturas_cristal, estado_gomas, fecha, id_empleado, estado, CreadoPor)
VALUES 
(1, 14, 1, 1, '1/2', 1, 1, 0, 'Buenas', '2023-07-01', 27, 1, 1),
(2, 15, 2, 0, '3/4', 1, 1, 0, 'Regulares', '2023-07-02', 28, 1, 1),
(3, 16, 1, 1, '1/4', 0, 0, 1, 'Malas', '2023-07-03', 27, 1, 1);

-- Tabla RentasDevoluciones
INSERT INTO RentasDevoluciones (id_empleado, id_vehiculo, id_cliente, fecha_renta, fecha_devolucion, monto_dia, cantidad_dias, comentario, estado, CreadoPor)
VALUES 
(27, 14, 1, '2023-08-01', '2023-08-05', 500.00, 4, 'Sin incidentes', 1, 1),
(28, 15, 2, '2023-08-02', '2023-08-06', 600.00, 4, 'Rayón pequeño en puerta', 1, 1),
(27, 16, 1, '2023-08-03', '2023-08-07', 700.00, 4, 'Devolución tarde', 1, 1);
