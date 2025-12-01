-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-12-2025 a las 01:54:02
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
-- Base de datos: `sacrej`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aula`
--

CREATE TABLE `aula` (
  `IdAul` int(10) NOT NULL,
  `Num` int(2) NOT NULL,
  `CanAlu` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catequistas`
--

CREATE TABLE `catequistas` (
  `IdCat` int(10) NOT NULL,
  `NomCat` text NOT NULL,
  `ApeCat` text NOT NULL,
  `FecNacCat` date NOT NULL,
  `TlfCat` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `celebracion`
--

CREATE TABLE `celebracion` (
  `IdCel` int(15) NOT NULL,
  `FechCel` date NOT NULL,
  `TipCel` int(2) NOT NULL,
  `NumLib` int(4) NOT NULL,
  `NumFol` int(10) NOT NULL,
  `IdMin` int(5) NOT NULL,
  `Lugar` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_pago`
--

CREATE TABLE `control_pago` (
  `CodConPag` int(10) NOT NULL,
  `Mon` int(6) NOT NULL,
  `Mes` int(2) NOT NULL,
  `FecPag` date NOT NULL,
  `NumRec` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `individuos`
--

CREATE TABLE `individuos` (
  `idInd` varchar(20) NOT NULL,
  `NomInd` text NOT NULL,
  `ApeInd` text NOT NULL,
  `LugNacInd` text NOT NULL,
  `FecNacInd` date NOT NULL,
  `SexInd` text NOT NULL,
  `FilInd` text NOT NULL COMMENT 'Filiacion del individuo',
  `DirInd` text DEFAULT NULL,
  `IdUsu` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `individuo_celebracion`
--

CREATE TABLE `individuo_celebracion` (
  `IdInd` varchar(20) NOT NULL,
  `IdCel` int(15) NOT NULL,
  `RegCiv` varchar(15) NOT NULL,
  `NotMar` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripcion`
--

CREATE TABLE `inscripcion` (
  `IdInd` varchar(20) NOT NULL,
  `TipRep` text NOT NULL,
  `IdRep` int(10) NOT NULL,
  `Niv` int(1) NOT NULL,
  `Est` int(6) NOT NULL,
  `IdCad` int(10) NOT NULL,
  `CodConPag` int(10) NOT NULL,
  `LugCat` int(10) NOT NULL
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lugar_bauztizo`
--

CREATE TABLE `lugar_bauztizo` (
  `IdLugBau` int(10) NOT NULL,
  `IdInd` varchar(20) NOT NULL,
  `DesPai` text NOT NULL,
  `DesEst` text NOT NULL,
  `DesCiu` text NOT NULL,
  `FecBau` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lugar_catequesis`
--

CREATE TABLE `lugar_catequesis` (
  `IdLug` int(9) NOT NULL,
  `NomComBas` text NOT NULL,
  `DirComBas` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `padres`
--

CREATE TABLE `padres` (
  `IdPad` int(11) NOT NULL,
  `IdInd` varchar(20) NOT NULL,
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
  `IdInd` varchar(20) NOT NULL,
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
(1, '¿Cuál es el nombre de tu primera mascota?', 'mascota', '¿En qué ciudad naciste?', 'ciudad', '¿Cuál es tu comida favorita?', 'comida', '¿Cómo se llama tu mejor amigo de la infancia?', 'amigo'),
(2, '¿Cuál es el nombre de tu primera mascota?', 'mascota', '¿En qué ciudad naciste?', 'ciudad', '¿Cuál es tu comida favorita?', 'comida', '¿Cómo se llama tu mejor amigo de la infancia?', 'amigo'),
(3, '¿Cuál es el nombre de tu primera mascota?', 'mascota', '¿En qué ciudad naciste?', 'ciudad', '¿Cuál es tu comida favorita?', 'comida', '¿Cómo se llama tu mejor amigo de la infancia?', 'amigo'),
(4, '¿Cuál es el nombre de tu primera mascota?', 'mascota', '¿En qué ciudad naciste?', 'ciudad', '¿Cuál es tu comida favorita?', 'comida', '¿Cómo se llama tu mejor amigo de la infancia?', 'amigo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `relacion_catequistas_aula`
--

CREATE TABLE `relacion_catequistas_aula` (
  `IdCat` int(10) NOT NULL,
  `IdAul` int(10) NOT NULL,
  `Niv` int(1) NOT NULL,
  `HorEnt` time NOT NULL,
  `HorSal` time NOT NULL,
  `Dia` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `representante`
--

CREATE TABLE `representante` (
  `IdRep` int(10) NOT NULL,
  `Nom` text NOT NULL,
  `Ape` text NOT NULL,
  `Par` text NOT NULL,
  `Tlf` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_celebracion`
--

CREATE TABLE `tipo_celebracion` (
  `IdTip` int(2) NOT NULL,
  `DesTip` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- NOTA: El hash de la contraseña de abajo corresponde a 'Admin1234.'.
-- Ejecute el script 'tools/generar_hash.php' en su servidor
-- y reemplace el valor de 'ClaUsu' con el resultado.
--

INSERT INTO `usuarios` (`IdUsu`, `NomUsu`, `ApeUsu`, `Usuario`, `ClaUsu`, `RolUsu`) VALUES
(1, 'Administrador', 'Principal', 'Admin01', '$2y$10$FjDphbSHUuuFvHJoB3hQJu1rgmCUiPoFEHbfajQ4hPPK/M5xasdzC', '10'),
(2, 'Administrador', 'Principal', 'Admin02', '$2y$10$FjDphbSHUuuFvHJoB3hQJu1rgmCUiPoFEHbfajQ4hPPK/M5xasdzC', '10'),
(3, 'Administrador', 'Principal', 'Admin03', '$2y$10$FjDphbSHUuuFvHJoB3hQJu1rgmCUiPoFEHbfajQ4hPPK/M5xasdzC', '10'),
(4, 'Administrador', 'Principal', 'Admin04', '$2y$10$FjDphbSHUuuFvHJoB3hQJu1rgmCUiPoFEHbfajQ4hPPK/M5xasdzC', '10');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aula`
--
ALTER TABLE `aula`
  ADD PRIMARY KEY (`IdAul`);

--
-- Indices de la tabla `catequistas`
--
ALTER TABLE `catequistas`
  ADD PRIMARY KEY (`IdCat`);

--
-- Indices de la tabla `celebracion`
--
ALTER TABLE `celebracion`
  ADD PRIMARY KEY (`IdCel`),
  ADD KEY `TipCel` (`TipCel`,`IdMin`),
  ADD KEY `IdMin` (`IdMin`);

--
-- Indices de la tabla `control_pago`
--
ALTER TABLE `control_pago`
  ADD PRIMARY KEY (`CodConPag`);

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
  ADD KEY `IdInd` (`IdInd`),
  ADD KEY `IdCel` (`IdCel`);

--
-- Indices de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD PRIMARY KEY (`IdInd`),
  ADD UNIQUE KEY `LugCat_2` (`LugCat`),
  ADD KEY `IdRep` (`IdRep`,`IdCad`,`CodConPag`),
  ADD KEY `LugCat` (`LugCat`),
  ADD KEY `IdCad` (`IdCad`),
  ADD KEY `CodConPag` (`CodConPag`);

--
-- Indices de la tabla `jerarquia_ministro`
--
ALTER TABLE `jerarquia_ministro`
  ADD PRIMARY KEY (`CodJer`);

--
-- Indices de la tabla `lugar_bauztizo`
--
ALTER TABLE `lugar_bauztizo`
  ADD PRIMARY KEY (`IdLugBau`),
  ADD KEY `IdInd` (`IdInd`);

--
-- Indices de la tabla `lugar_catequesis`
--
ALTER TABLE `lugar_catequesis`
  ADD PRIMARY KEY (`IdLug`);

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
-- Indices de la tabla `relacion_catequistas_aula`
--
ALTER TABLE `relacion_catequistas_aula`
  ADD PRIMARY KEY (`IdCat`),
  ADD KEY `IdAul` (`IdAul`);

--
-- Indices de la tabla `representante`
--
ALTER TABLE `representante`
  ADD PRIMARY KEY (`IdRep`);

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
-- AUTO_INCREMENT de la tabla `jerarquia_ministro`
--
ALTER TABLE `jerarquia_ministro`
  MODIFY `CodJer` int(2) NOT NULL AUTO_INCREMENT COMMENT 'Codigo de la jerarquia del ministro';

--
-- AUTO_INCREMENT de la tabla `ministro_celebrante`
--
ALTER TABLE `ministro_celebrante`
  MODIFY `IdMinCel` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `padres`
--
ALTER TABLE `padres`
  MODIFY `IdPad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `padrinos`
--
ALTER TABLE `padrinos`
  MODIFY `IdPadri` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_celebracion`
--
ALTER TABLE `tipo_celebracion`
  MODIFY `IdTip` int(2) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `celebracion`
--
ALTER TABLE `celebracion`
  ADD CONSTRAINT `celebracion_ibfk_1` FOREIGN KEY (`IdMin`) REFERENCES `ministro_celebrante` (`IdMinCel`),
  ADD CONSTRAINT `fk_celebracion_tipo` FOREIGN KEY (`TipCel`) REFERENCES `tipo_celebracion` (`IdTip`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `individuos`
--
ALTER TABLE `individuos`
  ADD CONSTRAINT `individuos_ibfk_1` FOREIGN KEY (`IdUsu`) REFERENCES `usuarios` (`IdUsu`);

--
-- Filtros para la tabla `individuo_celebracion`
--
ALTER TABLE `individuo_celebracion`
  ADD CONSTRAINT `individuo_celebracion_ibfk_1` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`),
  ADD CONSTRAINT `individuo_celebracion_ibfk_2` FOREIGN KEY (`IdCel`) REFERENCES `celebracion` (`IdCel`);

--
-- Filtros para la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD CONSTRAINT `inscripcion_ibfk_1` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscripcion_ibfk_2` FOREIGN KEY (`IdRep`) REFERENCES `representante` (`IdRep`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscripcion_ibfk_3` FOREIGN KEY (`IdCad`) REFERENCES `catequistas` (`IdCat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscripcion_ibfk_4` FOREIGN KEY (`CodConPag`) REFERENCES `control_pago` (`CodConPag`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `lugar_bauztizo`
--
ALTER TABLE `lugar_bauztizo`
  ADD CONSTRAINT `lugar_bauztizo_ibfk_1` FOREIGN KEY (`IdInd`) REFERENCES `individuo_celebracion` (`IdInd`);

-- ESTE FILTRO QUE GENERABA CONFLICTO POSIBLE SOLUCION ADD CONSTRAINT `lugar_bauztizo_ibfk_1` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`);

--
-- Filtros para la tabla `lugar_catequesis`
--
ALTER TABLE `lugar_catequesis`
  ADD CONSTRAINT `lugar_catequesis_ibfk_1` FOREIGN KEY (`IdLug`) REFERENCES `inscripcion` (`LugCat`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ministro_celebrante`
--
ALTER TABLE `ministro_celebrante`
  ADD CONSTRAINT `ministro_celebrante_ibfk_1` FOREIGN KEY (`CodJer`) REFERENCES `jerarquia_ministro` (`CodJer`);

--
-- Filtros para la tabla `padres`
--
ALTER TABLE `padres`
  ADD CONSTRAINT `fk_padres_individuos` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `padrinos`
--
ALTER TABLE `padrinos`
  ADD CONSTRAINT `fk_padrinos_individuos` FOREIGN KEY (`IdInd`) REFERENCES `individuos` (`idInd`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD CONSTRAINT `preguntas_seguridad_ibfk_1` FOREIGN KEY (`IdUsu`) REFERENCES `usuarios` (`IdUsu`);

--
-- Filtros para la tabla `relacion_catequistas_aula`
--
ALTER TABLE `relacion_catequistas_aula`
  ADD CONSTRAINT `relacion_catequistas_aula_ibfk_1` FOREIGN KEY (`IdCat`) REFERENCES `catequistas` (`IdCat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relacion_catequistas_aula_ibfk_2` FOREIGN KEY (`IdAul`) REFERENCES `aula` (`IdAul`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
