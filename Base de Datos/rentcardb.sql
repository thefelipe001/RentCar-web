-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 16, 2025 at 02:27 PM
-- Server version: 8.0.39
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rentcardb`
--

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `IdCliente` int NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Cedula` varchar(20) NOT NULL,
  `NumeroTarjetaCR` varchar(20) DEFAULT NULL,
  `LimiteCredito` decimal(10,2) NOT NULL,
  `TipoPersona` enum('Física','Jurídica') NOT NULL,
  `Estado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`IdCliente`, `Nombre`, `Cedula`, `NumeroTarjetaCR`, `LimiteCredito`, `TipoPersona`, `Estado`) VALUES
(26, 'Felipe Mejia', '40200492268', '14546156526689', 50000.00, 'Jurídica', 1),
(34, 'Miguel', '40233979984', '14546156526689', 2.00, 'Jurídica', 1),
(35, 'Mario', '04801029457', '4111111111111111', 4000.00, 'Física', 1),
(38, 'Jose', '09400145687', '4111111111111111', 40000.00, 'Física', 1),
(39, 'Maria', '00105999015', '6759649826438453', 20000.00, 'Jurídica', 1);

-- --------------------------------------------------------

--
-- Table structure for table `empleados`
--

CREATE TABLE `empleados` (
  `IdEmpleado` int NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Cedula` varchar(11) NOT NULL,
  `TandaLabor` enum('Matutina','Vespertina','Nocturna') NOT NULL,
  `PorcientoComision` decimal(5,2) NOT NULL,
  `FechaIngreso` date NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT '1',
  `Cargo` varchar(50) NOT NULL,
  `IdUsuario` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `empleados`
--

INSERT INTO `empleados` (`IdEmpleado`, `Nombre`, `Cedula`, `TandaLabor`, `PorcientoComision`, `FechaIngreso`, `Estado`, `Cargo`, `IdUsuario`) VALUES
(26, 'Felipe Mejia', '40200492268', 'Matutina', 2.00, '2025-01-12', 1, '232', 67),
(30, 'Wagner Matos', '00103051181', 'Vespertina', 21.20, '2025-01-13', 1, 'Programador', 69),
(35, 'Juan Valdez', '04701033385', 'Vespertina', 20.50, '2025-01-16', 1, 'Gerente de Compras', 73),
(37, 'Pedro Moya', '40221586031', 'Vespertina', 27.10, '2025-01-16', 1, 'Analista Lider', 75);

-- --------------------------------------------------------

--
-- Table structure for table `inspeccion`
--

CREATE TABLE `inspeccion` (
  `IdTransaccion` int NOT NULL,
  `IdVehiculo` int NOT NULL,
  `IdCliente` int NOT NULL,
  `TieneRalladuras` tinyint(1) NOT NULL,
  `CantidadCombustible` varchar(10) NOT NULL,
  `TieneGomaRespaldo` tinyint(1) NOT NULL,
  `TieneGato` tinyint(1) NOT NULL,
  `TieneRoturasCristal` tinyint(1) NOT NULL,
  `EstadoGomas` varchar(255) NOT NULL,
  `Observaciones` varchar(255) DEFAULT NULL,
  `Fecha` date NOT NULL,
  `EmpleadoInspeccion` int NOT NULL,
  `Estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inspeccion`
--

INSERT INTO `inspeccion` (`IdTransaccion`, `IdVehiculo`, `IdCliente`, `TieneRalladuras`, `CantidadCombustible`, `TieneGomaRespaldo`, `TieneGato`, `TieneRoturasCristal`, `EstadoGomas`, `Observaciones`, `Fecha`, `EmpleadoInspeccion`, `Estado`) VALUES
(7, 28, 34, 0, '3/4', 1, 1, 1, 'Más o menos', 'activo', '2025-01-16', 26, 1),
(9, 20, 34, 1, '1/4', 1, 1, 1, 'Excelente', 'el vehiculo  bueno', '2025-01-23', 30, 1),
(11, 22, 34, 0, '1/4', 1, 1, 1, 'Más o menos', 'prueba', '2025-01-09', 26, 1),
(12, 22, 34, 0, '3/4', 1, 1, 1, 'Malo', 'el vehiculo malo felipe', '2025-01-15', 26, 1),
(13, 22, 26, 0, '3/4', 1, 1, 1, 'Malo', 'el vehiculo malo hansel', '2025-01-15', 26, 1),
(14, 20, 26, 0, '3/4', 0, 0, 0, 'Más o menos', 'Tiene algun defecto', '2025-01-15', 26, 1);

-- --------------------------------------------------------

--
-- Table structure for table `marcas`
--

CREATE TABLE `marcas` (
  `IdMarca` int NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Estado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `marcas`
--

INSERT INTO `marcas` (`IdMarca`, `Descripcion`, `Estado`) VALUES
(1, 'Corolla', 1),
(2, 'Sedán1', 1),
(3, 'Ford', 1),
(4, 'Chevrolet', 1),
(5, 'BMW', 1),
(7, 'Sedán1', 1),
(8, 'Ferraris Supre', 1),
(9, 'agua', 0),
(10, 'Motor', 1),
(13, 'Honda Civic 2018', 1),
(14, 'Sedán', 0),
(15, 'Chevrolet', 1);

-- --------------------------------------------------------

--
-- Table structure for table `modelos`
--

CREATE TABLE `modelos` (
  `IdModelo` int NOT NULL,
  `IdMarca` int NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Estado` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `modelos`
--

INSERT INTO `modelos` (`IdModelo`, `IdMarca`, `Descripcion`, `Estado`) VALUES
(1, 1, 'Corolla', 1),
(2, 1, 'Camry', 1),
(3, 2, 'Civic', 1),
(4, 2, 'Accord', 1),
(5, 3, 'Mustang', 1),
(6, 3, 'Explorer', 1),
(7, 2, 'Model M', 1),
(14, 8, '12', 1),
(16, 1, 'Motor', 1),
(17, 3, 'Motor', 1),
(18, 5, 'Motor', 1),
(19, 3, 'Motor', 1),
(20, 4, 'Motor', 1),
(21, 13, 'Civic', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rentadevolucion`
--

CREATE TABLE `rentadevolucion` (
  `IdRenta` int NOT NULL,
  `IdEmpleado` int NOT NULL,
  `IdVehiculo` int NOT NULL,
  `IdCliente` int NOT NULL,
  `FechaRenta` date NOT NULL,
  `FechaDevolucion` date NOT NULL,
  `MontoPorDia` decimal(10,2) NOT NULL,
  `CantidadDias` int NOT NULL,
  `Comentario` varchar(255) DEFAULT NULL,
  `Estado` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rentadevolucion`
--

INSERT INTO `rentadevolucion` (`IdRenta`, `IdEmpleado`, `IdVehiculo`, `IdCliente`, `FechaRenta`, `FechaDevolucion`, `MontoPorDia`, `CantidadDias`, `Comentario`, `Estado`) VALUES
(1, 30, 20, 26, '2025-01-01', '2025-01-05', 1500.00, 4, 'Vehículo devuelto en perfectas condiciones', 1),
(2, 26, 20, 34, '2025-01-01', '2025-01-24', 5.00, 3, '8', 1),
(4, 30, 24, 26, '2025-01-01', '2025-01-24', 5000.00, 9, 'prueba editar', 1),
(5, 35, 24, 35, '2024-08-01', '2025-01-15', 400.00, 30, 'No se aceptan rayaduras', 1),
(6, 35, 20, 34, '2025-01-15', '2025-01-19', 600.00, 6, 'prueba', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tiposcombustible`
--

CREATE TABLE `tiposcombustible` (
  `IdCombustible` int NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `Estado` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tiposcombustible`
--

INSERT INTO `tiposcombustible` (`IdCombustible`, `Descripcion`, `Estado`) VALUES
(1, 'Gasolina', 1),
(2, 'Diesel', 1),
(3, 'Gas Natural', 1),
(4, 'Eléctrico', 1),
(5, 'Híbrido', 1),
(15, 'Gas propano', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tiposvehiculos`
--

CREATE TABLE `tiposvehiculos` (
  `IdTipoVehiculo` int NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `Estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tiposvehiculos`
--

INSERT INTO `tiposvehiculos` (`IdTipoVehiculo`, `Descripcion`, `Estado`) VALUES
(1, 'Sedán', 1),
(3, 'Camioneta', 1),
(4, 'Deportivo', 1),
(7, 'Sedán5', 1),
(17, 'Motor Alto', 1),
(21, 'Motor', 1),
(22, 'Sedán', 1);

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `IdUsuario` int NOT NULL,
  `Nombres` varchar(100) NOT NULL,
  `Apellidos` varchar(100) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `Contrasena` varchar(100) NOT NULL,
  `EsAdministrador` tinyint(1) NOT NULL,
  `Activo` tinyint(1) DEFAULT '1',
  `FechaRegistro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`IdUsuario`, `Nombres`, `Apellidos`, `Correo`, `Contrasena`, `EsAdministrador`, `Activo`, `FechaRegistro`) VALUES
(67, 'Felipe', 'Mejia', 'mejiafelipe200@gmail.com', '123456', 1, 1, '2025-01-12 14:21:42'),
(69, 'Wagner', 'Matos', 'Wagner20110@gmail.com', '123456', 1, 1, '2025-01-12 14:37:20'),
(71, 'Jose', 'mejia', 'jose@gmail.com', '123456', 1, 0, '2025-01-12 14:44:10'),
(73, 'Juan', 'Valdez', 'JuanValdez@gmail.com', '123456', 0, 0, '2025-01-15 19:19:48'),
(75, 'Pedro', 'Moya', 'MoyaPedro@gmail.com', '123456', 1, 1, '2025-01-15 20:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `vehiculos`
--

CREATE TABLE `vehiculos` (
  `IdVehiculo` int NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `NumeroChasis` varchar(50) NOT NULL,
  `NumeroMotor` varchar(50) NOT NULL,
  `NumeroPlaca` varchar(50) NOT NULL,
  `IdTipoVehiculo` int NOT NULL,
  `IdMarca` int NOT NULL,
  `IdModelo` int NOT NULL,
  `IdCombustible` int NOT NULL,
  `Estado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vehiculos`
--

INSERT INTO `vehiculos` (`IdVehiculo`, `Descripcion`, `NumeroChasis`, `NumeroMotor`, `NumeroPlaca`, `IdTipoVehiculo`, `IdMarca`, `IdModelo`, `IdCombustible`, `Estado`) VALUES
(20, 'Honda Civic 2018', 'CHS98765432109', 'MTR98765432109', 'PLT-5678', 4, 1, 2, 2, 1),
(22, 'Ford F-150 2019', 'CHS23456789012', 'MTR23456789012', 'PLT-3456', 7, 4, 4, 2, 1),
(23, 'Chevrolet Tahoe 2022', 'CHS67890123456', 'MTR67890123456', 'PLT-7890', 17, 5, 5, 3, 1),
(24, 'Ferraris Supre', '1212', '212', '212', 3, 3, 3, 3, 1),
(27, 'prueba', '1212', 'MTR98765432109', '212', 1, 2, 16, 2, 1),
(28, 'Motor', '1212', 'MTR98765432109', '212', 1, 1, 16, 3, 1),
(34, 'Honda Civic azul 2020', 'CHS63881234', 'm14322', 'm8766372', 4, 13, 21, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`IdCliente`),
  ADD UNIQUE KEY `Cedula` (`Cedula`);

--
-- Indexes for table `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`IdEmpleado`),
  ADD UNIQUE KEY `Cedula` (`Cedula`),
  ADD KEY `IdUsuario` (`IdUsuario`);

--
-- Indexes for table `inspeccion`
--
ALTER TABLE `inspeccion`
  ADD PRIMARY KEY (`IdTransaccion`),
  ADD KEY `IdCliente` (`IdCliente`),
  ADD KEY `EmpleadoInspeccion` (`EmpleadoInspeccion`);

--
-- Indexes for table `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`IdMarca`);

--
-- Indexes for table `modelos`
--
ALTER TABLE `modelos`
  ADD PRIMARY KEY (`IdModelo`),
  ADD KEY `IdMarca` (`IdMarca`);

--
-- Indexes for table `rentadevolucion`
--
ALTER TABLE `rentadevolucion`
  ADD PRIMARY KEY (`IdRenta`),
  ADD KEY `IdEmpleado` (`IdEmpleado`),
  ADD KEY `IdVehiculo` (`IdVehiculo`),
  ADD KEY `IdCliente` (`IdCliente`);

--
-- Indexes for table `tiposcombustible`
--
ALTER TABLE `tiposcombustible`
  ADD PRIMARY KEY (`IdCombustible`);

--
-- Indexes for table `tiposvehiculos`
--
ALTER TABLE `tiposvehiculos`
  ADD PRIMARY KEY (`IdTipoVehiculo`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IdUsuario`),
  ADD UNIQUE KEY `Correo` (`Correo`);

--
-- Indexes for table `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`IdVehiculo`),
  ADD KEY `IdTipoVehiculo` (`IdTipoVehiculo`),
  ADD KEY `IdMarca` (`IdMarca`),
  ADD KEY `IdModelo` (`IdModelo`),
  ADD KEY `IdCombustible` (`IdCombustible`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `IdCliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `empleados`
--
ALTER TABLE `empleados`
  MODIFY `IdEmpleado` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `inspeccion`
--
ALTER TABLE `inspeccion`
  MODIFY `IdTransaccion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `marcas`
--
ALTER TABLE `marcas`
  MODIFY `IdMarca` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `modelos`
--
ALTER TABLE `modelos`
  MODIFY `IdModelo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `rentadevolucion`
--
ALTER TABLE `rentadevolucion`
  MODIFY `IdRenta` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tiposcombustible`
--
ALTER TABLE `tiposcombustible`
  MODIFY `IdCombustible` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tiposvehiculos`
--
ALTER TABLE `tiposvehiculos`
  MODIFY `IdTipoVehiculo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IdUsuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `IdVehiculo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE;

--
-- Constraints for table `inspeccion`
--
ALTER TABLE `inspeccion`
  ADD CONSTRAINT `inspeccion_ibfk_1` FOREIGN KEY (`IdCliente`) REFERENCES `clientes` (`IdCliente`),
  ADD CONSTRAINT `inspeccion_ibfk_2` FOREIGN KEY (`EmpleadoInspeccion`) REFERENCES `empleados` (`IdEmpleado`);

--
-- Constraints for table `modelos`
--
ALTER TABLE `modelos`
  ADD CONSTRAINT `modelos_ibfk_1` FOREIGN KEY (`IdMarca`) REFERENCES `marcas` (`IdMarca`) ON DELETE CASCADE;

--
-- Constraints for table `rentadevolucion`
--
ALTER TABLE `rentadevolucion`
  ADD CONSTRAINT `rentadevolucion_ibfk_1` FOREIGN KEY (`IdEmpleado`) REFERENCES `empleados` (`IdEmpleado`),
  ADD CONSTRAINT `rentadevolucion_ibfk_2` FOREIGN KEY (`IdVehiculo`) REFERENCES `vehiculos` (`IdVehiculo`),
  ADD CONSTRAINT `rentadevolucion_ibfk_3` FOREIGN KEY (`IdCliente`) REFERENCES `clientes` (`IdCliente`);

--
-- Constraints for table `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`IdTipoVehiculo`) REFERENCES `tiposvehiculos` (`IdTipoVehiculo`),
  ADD CONSTRAINT `vehiculos_ibfk_2` FOREIGN KEY (`IdMarca`) REFERENCES `marcas` (`IdMarca`),
  ADD CONSTRAINT `vehiculos_ibfk_3` FOREIGN KEY (`IdModelo`) REFERENCES `modelos` (`IdModelo`),
  ADD CONSTRAINT `vehiculos_ibfk_4` FOREIGN KEY (`IdCombustible`) REFERENCES `tiposcombustible` (`IdCombustible`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
