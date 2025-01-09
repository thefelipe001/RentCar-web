-- Crear la base de datos
CREATE DATABASE RentCarDB;
GO

-- Usar la base de datos recién creada
USE RentCarDB;
GO

-- Crear tabla USUARIO
CREATE TABLE USUARIO (
    IdUsuario INT PRIMARY KEY IDENTITY,
    Nombres NVARCHAR(100) NOT NULL,
    Apellidos NVARCHAR(100) NOT NULL,
    Correo NVARCHAR(100) NOT NULL UNIQUE,
    Contrasena NVARCHAR(100) NOT NULL,
    EsAdministrador BIT NOT NULL,
    Activo BIT DEFAULT 1,
    FechaRegistro DATETIME DEFAULT GETDATE()
);

-- Crear tabla Clientes
CREATE TABLE Clientes (
    id_cliente INT PRIMARY KEY IDENTITY,
    nombre NVARCHAR(100) NOT NULL,
    cedula NVARCHAR(20) NOT NULL UNIQUE,
    numero_tarjeta_cr NVARCHAR(50) NOT NULL,
    limite_credito DECIMAL(18,2) NOT NULL,
    tipo_persona NVARCHAR(10) NOT NULL CHECK (tipo_persona IN ('Física', 'Jurídica')),
    estado BIT NOT NULL,
    CreadoPor INT NOT NULL,
    FechaCreacion DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_Clientes_CreadoPor FOREIGN KEY (CreadoPor) REFERENCES USUARIO(IdUsuario)
);

-- Crear tabla Empleados
CREATE TABLE Empleados (
    id_empleado INT PRIMARY KEY IDENTITY,
    nombre NVARCHAR(100) NOT NULL,
    cedula NVARCHAR(20) NOT NULL UNIQUE,
    tanda_labor NVARCHAR(10) NOT NULL CHECK (tanda_labor IN ('Matutina', 'Vespertina', 'Nocturna')),
    porcentaje_comision DECIMAL(5,2) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    estado BIT NOT NULL,
    IdUsuario INT UNIQUE,
    CreadoPor INT NOT NULL,
    FechaCreacion DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_Empleados_Usuario FOREIGN KEY (IdUsuario) REFERENCES USUARIO(IdUsuario),
    CONSTRAINT FK_Empleados_CreadoPor FOREIGN KEY (CreadoPor) REFERENCES USUARIO(IdUsuario)
);

-- Crear tabla Vehiculos
CREATE TABLE Vehiculos (
    id_vehiculo INT PRIMARY KEY IDENTITY,
    descripcion NVARCHAR(100) NOT NULL,
    numero_chasis NVARCHAR(50) NOT NULL UNIQUE,
    numero_motor NVARCHAR(50) NOT NULL UNIQUE,
    numero_placa NVARCHAR(50) NOT NULL UNIQUE,
    id_tipo_vehiculo INT NOT NULL,
    id_marca INT NOT NULL,
    id_modelo INT NOT NULL,
    id_tipo_combustible INT NOT NULL,
    estado BIT NOT NULL,
    Imagen NVARCHAR(255),
    CreadoPor INT NOT NULL,
    FechaCreacion DATETIME DEFAULT GETDATE()
);

-- Crear tabla Inspecciones
CREATE TABLE Inspecciones (
    id_inspeccion INT PRIMARY KEY IDENTITY,
    id_transaccion INT NOT NULL,
    id_vehiculo INT NOT NULL,
    id_cliente INT NOT NULL,
    ralladuras BIT NOT NULL,
    cantidad_combustible NVARCHAR(10) NOT NULL CHECK (cantidad_combustible IN ('1/4', '1/2', '3/4', 'Lleno')),
    goma_respuesta BIT NOT NULL,
    gato BIT NOT NULL,
    roturas_cristal BIT NOT NULL,
    estado_gomas NVARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    id_empleado INT NOT NULL,
    estado BIT NOT NULL,
    CreadoPor INT NOT NULL,
    FechaCreacion DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_Inspecciones_Vehiculos FOREIGN KEY (id_vehiculo) REFERENCES Vehiculos(id_vehiculo),
    CONSTRAINT FK_Inspecciones_Clientes FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente),
    CONSTRAINT FK_Inspecciones_Empleados FOREIGN KEY (id_empleado) REFERENCES Empleados(id_empleado),
    CONSTRAINT FK_Inspecciones_CreadoPor FOREIGN KEY (CreadoPor) REFERENCES USUARIO(IdUsuario)
);

-- Crear tabla RentasDevoluciones
CREATE TABLE RentasDevoluciones (
    id_renta INT PRIMARY KEY IDENTITY,
    id_empleado INT NOT NULL,
    id_vehiculo INT NOT NULL,
    id_cliente INT NOT NULL,
    fecha_renta DATE NOT NULL,
    fecha_devolucion DATE NOT NULL,
    monto_dia DECIMAL(18,2) NOT NULL,
    cantidad_dias INT NOT NULL,
    comentario NVARCHAR(MAX),
    estado BIT NOT NULL,
    CreadoPor INT NOT NULL,
    FechaCreacion DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_RentasDevoluciones_Empleados FOREIGN KEY (id_empleado) REFERENCES Empleados(id_empleado),
    CONSTRAINT FK_RentasDevoluciones_Vehiculos FOREIGN KEY (id_vehiculo) REFERENCES Vehiculos(id_vehiculo),
    CONSTRAINT FK_RentasDevoluciones_Clientes FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente),
    CONSTRAINT FK_RentasDevoluciones_CreadoPor FOREIGN KEY (CreadoPor) REFERENCES USUARIO(IdUsuario)
);
