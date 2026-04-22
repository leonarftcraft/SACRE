-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-04-2026 a las 04:45:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sacrej`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `celebracion`
--

CREATE TABLE `celebracion` (
  `Id` int(15) NOT NULL AUTO_INCREMENT,
  `IdCel` int(15) NOT NULL,
  `FechCel` date NOT NULL,
  `TipCel` int(2) NOT NULL,
  `NumLib` int(4) NOT NULL,
  `NumFol` int(10) NOT NULL,
  `IdMin` int(5) NOT NULL,
  `Lugar` text NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imgactas`
--

CREATE TABLE `imgactas` (
  `IdImg` int(11) NOT NULL,
  `UrlArchivo` varchar(255) NOT NULL,
  `NombreDigitalizador` varchar(100) NOT NULL,
  `FechaRegistro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `individuos`
--

CREATE TABLE `individuos` (
  `idInd` int(20) NOT NULL,
  `NomInd` text NOT NULL,
  `ApeInd` text NOT NULL,
  `LugNacInd` text NOT NULL,
  `FecNacInd` date NOT NULL,
  `SexInd` text NOT NULL,
  `FilInd` text NOT NULL COMMENT 'Filiacion del individuo',
  `DirInd` text NOT NULL,
  `IdUsu` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `individuo_celebracion`
--

CREATE TABLE `individuo_celebracion` (
  `IdInd` int(20) NOT NULL,
  `IdCel` int(15) NOT NULL,
  `RegCiv` varchar(15) NOT NULL,
  `NotMar` text NOT NULL,
  `EstCel` int(2) NOT NULL COMMENT 'Estado de la celebracion, para saber si es anulada, caso especial u otros casos',
  `IdImgActa` int(11) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jerarquia_ministro`
--

CREATE TABLE `jerarquia_ministro` (
  `CodJer` int(2) NOT NULL COMMENT 'Codigo de la jerarquia del ministro',
  `NomJer` text NOT NULL COMMENT 'Nombre de la jerarquia',
  `DesJer` text NOT NULL COMMENT 'Descripcion breve de la jerarquia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jerarquia_ministro`
--

INSERT INTO `jerarquia_ministro` (`CodJer`, `NomJer`, `DesJer`) VALUES
(1, 'SACERDOTE', 'persona consagrada a la religión para celebrar ritos y oficios, actuando a menudo como intermediario entre la comunidad religiosa y la divinidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ministro_celebrante`
--

CREATE TABLE `ministro_celebrante` (
  `IdMinCel` int(9) NOT NULL,
  `Nom` text NOT NULL,
  `Ape` text NOT NULL,
  `CodJer` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ministro_celebrante`
--

INSERT INTO `ministro_celebrante` (`IdMinCel`, `Nom`, `Ape`, `CodJer`) VALUES
(1, 'Antony', 'Lares', 1),
(2, 'Jorge', 'Perez', 1),
(3, 'Luis', 'Buitrago', 1),
(4, 'Asdrúbal a.', 'Morales P.', 1),
(5, 'Néstor P', 'Chacon', 1),
(6, 'Ramiro', 'Useche', 1),
(7, 'Jorge', 'Pérez', 1),
(8, 'Pío León', 'Hernández', 1),
(9, 'Julian', 'Olivero', 1),
(10, 'Pedro M.', 'Gárriz', 1),
(11, 'Luis', 'Buitrago', 1),
(12, 'Gonzalo', 'Armaza', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `padres`
--

CREATE TABLE `padres` (
  `IdPad` int(11) NOT NULL,
  `IdInd` int(20) NOT NULL,
  `Nom` text NOT NULL,
  `Ape` text NOT NULL,
  `Sex` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `padrinos`
--

CREATE TABLE `padrinos` (
  `IdPadri` int(11) NOT NULL,
  `IdInd` int(20) NOT NULL,
  `Nom` text NOT NULL,
  `Ape` text NOT NULL,
  `Sex` tinyint(1) NOT NULL,
  `TipCelPad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

CREATE TABLE `preguntas_seguridad` (
  `IdUsu` int(11) NOT NULL,
  `PreSeg1` text NOT NULL,
  `ResSeg1` text NOT NULL,
  `PreSeg2` text NOT NULL,
  `ResSeg2` text NOT NULL,
  `PreSeg3` text NOT NULL,
  `ResSeg3` text NOT NULL,
  `PreSeg4` text NOT NULL,
  `ResSeg4` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_seguridad`
--

INSERT INTO `preguntas_seguridad` (`IdUsu`, `PreSeg1`, `ResSeg1`, `PreSeg2`, `ResSeg2`, `PreSeg3`, `ResSeg3`, `PreSeg4`, `ResSeg4`) VALUES
(12345678, '¿Cuál es el nombre de tu primera mascota?', '1', '¿En qué ciudad naciste?', '2', '¿Cuál es tu comida favorita?', '3', '¿Cómo se llama tu mejor amigo de la infancia?', '4'),
(26015780, '¿Cuál es el nombre de tu primera mascota?', '1', '¿En qué ciudad naciste?', '2', '¿Cuál es tu comida favorita?', '3', '¿Cómo se llama tu mejor amigo de la infancia?', '4');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_celebracion`
--

CREATE TABLE `tipo_celebracion` (
  `IdTip` int(2) NOT NULL,
  `DesTip` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_celebracion`
--

INSERT INTO `tipo_celebracion` (`IdTip`, `DesTip`) VALUES
(1, 'Bautizo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `IdUsu` int(11) NOT NULL,
  `NomUsu` varchar(50) NOT NULL,
  `ApeUsu` varchar(50) NOT NULL,
  `Usuario` varchar(12) NOT NULL,
  `ClaUsu` varchar(255) NOT NULL,
  `RolUsu` varchar(10) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`IdUsu`, `NomUsu`, `ApeUsu`, `Usuario`, `ClaUsu`, `RolUsu`) VALUES
(12345678, 'Administrador', 'Principal', 'Admin01', '$2y$10$699KXbXjIoieREAPVVQAyOecyK/E5p.duntk4tkor//7MRgo9p9zu', '10'),
(26015780, 'Leo', 'Ramirez', 'Leo123', '$2y$10$/xbGIRxWC1DozwvkLkCzU.PNrnO3VoGtFz6X4PsnlWX53lGjGDkTy', '20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `celebracion`
--
ALTER TABLE `celebracion`
  ADD KEY `TipCel` (`TipCel`,`IdMin`),
  ADD KEY `IdMin` (`IdMin`);

--
-- Indices de la tabla `imgactas`
--
ALTER TABLE `imgactas`
  ADD PRIMARY KEY (`IdImg`);

--
-- Indices de la tabla `individuos`
--
ALTER TABLE `individuos`
  ADD PRIMARY KEY (`idInd`),
  ADD KEY `IdUsu` (`IdUsu`);

--
-- Indices de la tabla `individuo_celebracion`
--
ALTER TABLE `individuo_celebracion`
  ADD PRIMARY KEY (`IdInd`),
  ADD KEY `IdCel` (`IdCel`),
  ADD KEY `fk_indcel_imgactas` (`IdImgActa`);

--
-- Indices de la tabla `jerarquia_ministro`
--
ALTER TABLE `jerarquia_ministro`
  ADD PRIMARY KEY (`CodJer`);

--
-- Indices de la tabla `ministro_celebrante`
--
ALTER TABLE `ministro_celebrante`
  ADD PRIMARY KEY (`IdMinCel`),
  ADD KEY `CodGer` (`CodJer`);

--
-- Indices de la tabla `padres`
--
ALTER TABLE `padres`
  ADD PRIMARY KEY (`IdPad`),
  ADD KEY `idx_IdInd` (`IdInd`);

--
-- Indices de la tabla `padrinos`
--
ALTER TABLE `padrinos`
  ADD PRIMARY KEY (`IdPadri`),
  ADD KEY `idx_IdInd` (`IdInd`),
  ADD KEY `fk_padrinos_tipcel` (`TipCelPad`);

--
-- Indices de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD KEY `IdUsu` (`IdUsu`);

--
-- Indices de la tabla `tipo_celebracion`
--
ALTER TABLE `tipo_celebracion`
  ADD PRIMARY KEY (`IdTip`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`IdUsu`),
  ADD UNIQUE KEY `Usuario` (`Usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `imgactas`
--
ALTER TABLE `imgactas`
  MODIFY `IdImg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `individuos`
--
ALTER TABLE `individuos`
  MODIFY `idInd` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `jerarquia_ministro`
--
ALTER TABLE `jerarquia_ministro`
  MODIFY `CodJer` int(2) NOT NULL AUTO_INCREMENT COMMENT 'Codigo de la jerarquia del ministro', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ministro_celebrante`
--
ALTER TABLE `ministro_celebrante`
  MODIFY `IdMinCel` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `padres`
--
ALTER TABLE `padres`
  MODIFY `IdPad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT de la tabla `padrinos`
--
ALTER TABLE `padrinos`
  MODIFY `IdPadri` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT de la tabla `tipo_celebracion`
--
ALTER TABLE `tipo_celebracion`
  MODIFY `IdTip` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `celebracion`
--
ALTER TABLE `celebracion`
  ADD CONSTRAINT `celebracion_ibfk_1` FOREIGN KEY (`TipCel`) REFERENCES `tipo_celebracion` (`IdTip`),
  ADD CONSTRAINT `celebracion_ibfk_2` FOREIGN KEY (`IdMin`) REFERENCES `ministro_celebrante` (`IdMinCel`);

--
-- Filtros para la tabla `individuos`
--
ALTER TABLE `individuos`
  ADD CONSTRAINT `individuos_ibfk_1` FOREIGN KEY (`IdUsu`) REFERENCES `usuarios` (`IdUsu`);

--
-- Filtros para la tabla `individuo_celebracion`
--
ALTER TABLE `individuo_celebracion`
  ADD CONSTRAINT `fk_indcel_imgactas` FOREIGN KEY (`IdImgActa`) REFERENCES `imgactas` (`IdImg`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `individuo_celebracion_ibfk_2` FOREIGN KEY (`IdCel`) REFERENCES `celebracion` (`Id`),
  ADD CONSTRAINT `individuo_celebracion_ibfk_3` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`);

--
-- Filtros para la tabla `ministro_celebrante`
--
ALTER TABLE `ministro_celebrante`
  ADD CONSTRAINT `ministro_celebrante_ibfk_1` FOREIGN KEY (`CodJer`) REFERENCES `jerarquia_ministro` (`CodJer`);

--
-- Filtros para la tabla `padres`
--
ALTER TABLE `padres`
  ADD CONSTRAINT `padres_ibfk_1` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`);

--
-- Filtros para la tabla `padrinos`
--
ALTER TABLE `padrinos`
  ADD CONSTRAINT `padrinos_ibfk_1` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`);

--
-- Filtros para la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD CONSTRAINT `preguntas_seguridad_ibfk_1` FOREIGN KEY (`IdUsu`) REFERENCES `usuarios` (`IdUsu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
