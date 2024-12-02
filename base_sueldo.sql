-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-12-2024 a las 23:33:32
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `base_sueldo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avisos`
--

CREATE TABLE `avisos` (
  `id` int(11) NOT NULL,
  `Usuario_ID` int(11) NOT NULL,
  `usuario_nombre` varchar(255) NOT NULL,
  `aviso` text NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `avisos`
--

INSERT INTO `avisos` (`id`, `Usuario_ID`, `usuario_nombre`, `aviso`, `fecha`) VALUES
(1, 1, 'Nil_14', 'Hola', '2024-11-30 18:56:28'),
(2, 1, 'Nil_14', 'Hola', '2024-11-30 18:58:30'),
(3, 1, 'Nil_14', 'Buenas noches', '2024-11-30 19:08:02'),
(4, 1, '', 'Buen día', '2024-12-01 01:30:05'),
(5, 1, '', 'Buen día', '2024-12-01 01:31:14'),
(6, 2, '', 'Holaa', '2024-12-01 02:12:03'),
(7, 2, '', 'La sala 1 es mía', '2024-12-02 02:14:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `ID` int(11) NOT NULL,
  `DNI` varchar(8) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Fecha` date NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Descuento` decimal(5,2) DEFAULT 0.00,
  `Usuario_ID` int(11) NOT NULL,
  `Atendido` tinyint(1) NOT NULL DEFAULT 0,
  `Historial_Clinico` tinyint(1) NOT NULL DEFAULT 0,
  `Precio_Descuento` decimal(10,2) GENERATED ALWAYS AS (`Precio` * `Descuento` / 100) STORED,
  `fecha_actual` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`ID`, `DNI`, `Nombre`, `Fecha`, `Precio`, `Descuento`, `Usuario_ID`, `Atendido`, `Historial_Clinico`, `fecha_actual`) VALUES
(1, '12345678', 'Juan Perez', '2024-12-03', 25.50, 80.00, 2, 1, 1, '2024-11-27'),
(6, '98765431', 'Andres Caicedo', '2024-11-09', 89.50, 80.00, 1, 1, 1, '2024-11-27'),
(7, '45785945', 'Carla Andrea Barrios', '2024-12-25', 80.00, 60.00, 1, 1, 1, '2024-11-30'),
(8, '54648755', 'Eric Fabri', '2024-12-05', 155.00, 90.00, 2, 1, 1, '2024-11-30'),
(9, '96385274', 'Marcely Sanchez', '2024-12-25', 150.00, 60.00, 4, 1, 1, '2024-11-30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `ID` int(11) NOT NULL,
  `ID_Destinatario` int(8) NOT NULL,
  `Monto` decimal(10,2) NOT NULL,
  `Fecha_Hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`ID`, `ID_Destinatario`, `Monto`, `Fecha_Hora`) VALUES
(1, 2, 22.50, '2024-11-13 01:01:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro`
--

CREATE TABLE `registro` (
  `Nombre` text NOT NULL,
  `Apellido` text NOT NULL,
  `Usuario` varchar(10) NOT NULL,
  `Correo` varchar(50) NOT NULL,
  `Contrasena` varchar(12) NOT NULL,
  `ID` int(8) NOT NULL,
  `Ocupacion` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro`
--

INSERT INTO `registro` (`Nombre`, `Apellido`, `Usuario`, `Correo`, `Contrasena`, `ID`, `Ocupacion`) VALUES
('Nilton', 'Tolentino Rojas', 'Nil_14', 'nilton@gmail.com', 'nilton123', 1, 1),
('Angel', 'Lucas Andres', 'Angel_1', 'angel@gmail.com', 'angel123', 2, 2),
('Luis', 'Casemiro Duran', 'Luis_1', 'luis@gmail.com', 'luis123', 3, 2),
('Diego', 'Palomino Vargas', 'Diego_1', 'diego@gmail.com', 'diego123', 4, 2),
('Carlos', 'Villanueva Rojas', 'Carlos_1', 'carlos@gmail.com', 'carlos123', 5, 2),
('Mat', 'Ruben Garcia', 'Mat_1', 'mat@gmail.com', 'mat123', 6, 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `avisos`
--
ALTER TABLE `avisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Usuario_ID` (`Usuario_ID`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_usuario_id` (`Usuario_ID`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `registro`
--
ALTER TABLE `registro`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `avisos`
--
ALTER TABLE `avisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `registro`
--
ALTER TABLE `registro`
  MODIFY `ID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `avisos`
--
ALTER TABLE `avisos`
  ADD CONSTRAINT `avisos_ibfk_1` FOREIGN KEY (`Usuario_ID`) REFERENCES `registro` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `fk_usuario_id` FOREIGN KEY (`Usuario_ID`) REFERENCES `registro` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
