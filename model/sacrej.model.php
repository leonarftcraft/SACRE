<?php
class sacrejmodel
{
    private $servidor = "localhost";
    private $usuario = "root";
    private $clave = "";
    private $bd = "sacrej";
    private $conexion;

    public function __CONSTRUCT()
    {
        $this->conexion = new mysqli($this->servidor, $this->usuario, $this->clave, $this->bd);
        $this->conexion->set_charset("utf8");

        if ($this->conexion->connect_errno) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }

    // 🔹 Verificar usuario y contraseña (login)
    public function verificar_usuario($usuario, $contrasena)
    {
        $sql = "SELECT * FROM usuarios WHERE Usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            return false;
        }

        // Verificar contraseña hasheada
        if (password_verify($contrasena, $user['ClaUsu'])) {
            return $user;
        } else {
            return false;
        }
    }
        public function validar_cedula($cedula)
    {
        $sql = "SELECT 1 FROM usuarios WHERE IdUsu = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $cedula);
        $stmt->execute();
        $stmt->store_result();

        $existe = $stmt->num_rows > 0;
        $stmt->close();
        return $existe;
    }


    // 🔹 Validar si existe el usuario
    public function validar_usuario($usuario)
    {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE Usuario = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['count'] > 0;
    }

    // 🔹 Registrar usuario
    public function registrar_usuario($usuario, $contrasena, $tipo_usuario, $cedula, $nombre, $apellido, $preguntas)
    {
        $this->conexion->begin_transaction();
        try {
            // ✅ Insertar usuario usando la CÉDULA como IdUsu
            $query = "INSERT INTO usuarios (IdUsu, NomUsu, ApeUsu, Usuario, ClaUsu, RolUsu) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($query);
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

            // IdUsu (INT), NomUsu, ApeUsu, Usuario, ClaUsu, RolUsu
            $stmt->bind_param("isssss",
                $cedula,        // 👉 IdUsu = cédula
                $nombre,
                $apellido,
                $usuario,
                $contrasena_hash,
                $tipo_usuario   // 100, 200, 300, 400, 500 (solicitud)
            );
            $stmt->execute();
            $stmt->close();

            // ✅ Insertar preguntas de seguridad usando la MISMA cédula como IdUsu
            $query2 = "INSERT INTO preguntas_seguridad 
                    (IdUsu, PreSeg1, ResSeg1, PreSeg2, ResSeg2, PreSeg3, ResSeg3, PreSeg4, ResSeg4) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt2 = $this->conexion->prepare($query2);
            $stmt2->bind_param("issssssss", 
                $cedula,  // 👉 mismo IdUsu (igual a la cédula)
                $preguntas[0]['pregunta'], $preguntas[0]['respuesta'],
                $preguntas[1]['pregunta'], $preguntas[1]['respuesta'],
                $preguntas[2]['pregunta'], $preguntas[2]['respuesta'],
                $preguntas[3]['pregunta'], $preguntas[3]['respuesta']
            );
            $stmt2->execute();
            $stmt2->close();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            return false;
        }
    }


    // 🔹 Obtener datos básicos de usuario
    public function obtenerUsuario($idUsu)
    {
        $sql = "SELECT NomUsu, ApeUsu, Usuario, RolUsu FROM usuarios WHERE IdUsu = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idUsu);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // 🔹 Obtener 2 preguntas aleatorias de un usuario
    public function obtener_preguntas_aleatorias($usuario, $cantidad = 2)
    {
        $sql = "SELECT u.IdUsu, 
                    p.PreSeg1, p.ResSeg1,
                    p.PreSeg2, p.ResSeg2,
                    p.PreSeg3, p.ResSeg3,
                    p.PreSeg4, p.ResSeg4
                FROM usuarios u
                INNER JOIN preguntas_seguridad p ON u.IdUsu = p.IdUsu
                WHERE u.Usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res) return false;

        // Crear array de preguntas
        $preguntas = [];
        for ($i = 1; $i <= 4; $i++) {
            $preguntas[] = [
                'id' => $i,
                'pregunta' => $res["PreSeg$i"],
                'respuesta' => $res["ResSeg$i"]
            ];
        }

        shuffle($preguntas); // Mezclar aleatoriamente
        return array_slice($preguntas, 0, $cantidad); // Tomar cantidad deseada
    }

    // 🔹 Validar respuestas de seguridad
    public function validar_respuestas($usuario, $respuestas)
    {
        // Obtener todas las preguntas del usuario
        $sql = "SELECT u.IdUsu, 
                    p.PreSeg1, p.ResSeg1,
                    p.PreSeg2, p.ResSeg2,
                    p.PreSeg3, p.ResSeg3,
                    p.PreSeg4, p.ResSeg4
                FROM usuarios u
                INNER JOIN preguntas_seguridad p ON u.IdUsu = p.IdUsu
                WHERE u.Usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$res) return false;

        // Mapear todas las respuestas correctas
        $correctas = [];
        for ($i = 1; $i <= 4; $i++) {
            $correctas[$i] = strtolower(trim($res["ResSeg$i"]));
        }

        // Validar respuestas recibidas
        foreach ($respuestas as $r) {
            $id = $r['id'];
            $respuesta = strtolower(trim($r['respuesta']));
            if (!isset($correctas[$id]) || $respuesta !== $correctas[$id]) {
                return false; // alguna respuesta incorrecta
            }
        }
        return true; // todas correctas
    }

    // 🔹 Actualizar contraseña
    public function actualizar_clave($usuario, $nuevaClave)
    {
        $hash = password_hash($nuevaClave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET ClaUsu = ? WHERE Usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ss", $hash, $usuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado; // true si se actualizó correctamente
    }
    // 🔹 Registrar Bautizo
    public function registrar_bautizo_completo($individuo, $madre, $padre, $padrinos, $celebracion, $enlace)
    {
        $this->conexion->begin_transaction();
        try {
            // 1️⃣ INDIVIDUO
            $sqlInd = "INSERT INTO individuos (IdInd, NomInd, ApeInd, LugNacInd, FecNacInd, SexInd, FilInd, IdUsu)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInd = $this->conexion->prepare($sqlInd);
            $stmtInd->bind_param(
                "sssssiis",
                $individuo['IdInd'],
                $individuo['NomInd'],
                $individuo['ApeInd'],
                $individuo['LugNacInd'],
                $individuo['FecNacInd'],
                $individuo['SexInd'],
                $individuo['FilInd'],
                $individuo['IdUsu']
            );
            if (!$stmtInd->execute()) {
                if ($this->conexion->errno == 1062) {
                    throw new Exception("El ID del bautizado ya existe en la base de datos.");
                }
                throw new Exception("Error al registrar individuo: " . $stmtInd->error);
            }

            // 2️⃣ CELEBRACIÓN
            $sqlCel = "INSERT INTO celebracion (IdCel, FechCel, TipCel, NumLib, NumFol, IdMin, Lugar)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtCel = $this->conexion->prepare($sqlCel);
            $stmtCel->bind_param(
                "isiiiss",
                $celebracion['IdCel'],
                $celebracion['FechCel'],
                $celebracion['TipCel'],
                $celebracion['NumLib'],
                $celebracion['NumFol'],
                $celebracion['IdMin'],
                $celebracion['Lugar']
            );
            if (!$stmtCel->execute()) {
                if ($this->conexion->errno == 1062) {
                    throw new Exception("El código de celebración ya existe (IdCel duplicado).");
                }
                throw new Exception("Error al registrar celebración: " . $stmtCel->error);
            }

            // 3️⃣ RELACIÓN individuo ↔ celebración
            $sqlRel = "INSERT INTO individuo_celebracion (IdInd, IdCel, RegCiv, NotMar)
                    VALUES (?, ?, ?, ?)";
            $stmtRel = $this->conexion->prepare($sqlRel);
            $stmtRel->bind_param("siss", $individuo['IdInd'], $celebracion['IdCel'], $enlace['RegCiv'], $enlace['NotMar']);
            if (!$stmtRel->execute()) {
                throw new Exception("Error al vincular individuo con celebración: " . $stmtRel->error);
            }

            // 4️⃣ MADRE
            $sqlPadres = "INSERT INTO padres (IdInd, Nom, Ape, Sex)
                        VALUES (?, ?, ?, ?)";
            $stmtPad = $this->conexion->prepare($sqlPadres);
            $stmtPad->bind_param("sssi", $individuo['IdInd'], $madre['Nom'], $madre['Ape'], $madre['Sex']);
            if (!$stmtPad->execute()) {
                throw new Exception("Error al registrar madre: " . $stmtPad->error);
            }

            // 5️⃣ PADRE (opcional)
            if ($padre && ($padre['Nom'] || $padre['Ape'])) {
                $stmtPad->bind_param("sssi", $individuo['IdInd'], $padre['Nom'], $padre['Ape'], $padre['Sex']);
                if (!$stmtPad->execute()) {
                    throw new Exception("Error al registrar padre: " . $stmtPad->error);
                }
            }

            // 6️⃣ PADRINOS
            $sqlPadrinos = "INSERT INTO padrinos (IdInd, Nom, Ape, Sex, TipCelPad)
                            VALUES (?, ?, ?, ?, ?)";
            $stmtPadr = $this->conexion->prepare($sqlPadrinos);
            foreach ($padrinos as $p) {
                $stmtPadr->bind_param("sssii", $individuo['IdInd'], $p['Nom'], $p['Ape'], $p['Sex'], $enlace['TipCelPad']);
                if (!$stmtPadr->execute()) {
                    throw new Exception("Error al registrar padrino/madrina: " . $stmtPadr->error);
                }
            }

            $this->conexion->commit();
            return ['status' => 'ok', 'msg' => 'Bautizo registrado correctamente.'];
        } catch (Throwable $e) {
            $this->conexion->rollback();
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }


    public function verificar_id_individuo($id)
    {
        $sql = "SELECT COUNT(*) AS total FROM individuos WHERE IdInd = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['total'] > 0;
    }

    public function verificar_existencia_individuo($idInd)
    {
        $sql = "SELECT IdInd FROM individuos WHERE IdInd = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $idInd);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res && $res->num_rows > 0);
    }







    // ✅ Registrar nuevo ministro
   public function registrar_ministro($nom, $ape, $codJer) {
    try {
        $sql = "INSERT INTO ministro_celebrante (Nom, Ape, CodJer) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return ['status' => 'error', 'msg' => 'Error al preparar la consulta: ' . $this->conexion->error];
        }
        $stmt->bind_param("ssi", $nom, $ape, $codJer);
        $ok = $stmt->execute();
        if (!$ok) {
            return ['status' => 'error', 'msg' => 'Error al ejecutar consulta: ' . $stmt->error];
        }
        return ['status' => 'ok', 'msg' => 'Ministro registrado correctamente.'];
    } catch (Exception $e) {
        return ['status' => 'error', 'msg' => 'Excepción: ' . $e->getMessage()];
    }
}


    // 🔹 Obtener todos los ministros con su jerarquía (JOIN)
    public function obtener_ministros_con_jerarquia()
    {
        $sql = "SELECT m.IdMinCel, m.Nom, m.Ape, j.NomJer 
                FROM ministro_celebrante m
                LEFT JOIN jerarquia_ministro j ON m.CodJer = j.CodJer
                ORDER BY m.IdMinCel ASC";

        $result = $this->conexion->query($sql);
        if (!$result) {
            error_log("Error en obtener_ministros_con_jerarquia: " . $this->conexion->error);
            return false;
        }

        return $result;
    }


    // ✅ Registrar nueva jerarquía
    public function registrar_jerarquia($NomJer, $DesJer)
    {
        file_put_contents("debug_model.txt", "Entró al modelo:\nNomJer: $NomJer\nDesJer: $DesJer\n");

        $sql = "INSERT INTO jerarquia_ministro (NomJer, DesJer) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            $error = "Error al preparar consulta: " . $this->conexion->error;
            file_put_contents("debug_model.txt", $error, FILE_APPEND);
            return ['status' => 'error', 'msg' => $error];
        }

        $stmt->bind_param("ss", $NomJer, $DesJer);
        $ok = $stmt->execute();

        if (!$ok) {
            $error = "Error al ejecutar consulta: " . $stmt->error;
            file_put_contents("debug_model.txt", $error, FILE_APPEND);
            return ['status' => 'error', 'msg' => $error];
        }

        $id = $this->conexion->insert_id; // ✅ correcto
        file_put_contents("debug_model.txt", "Inserción correcta, ID generado: $id\n", FILE_APPEND);

        return ['status' => 'ok', 'msg' => 'Jerarquía registrada con éxito.'];
    }

       // ✅ Registrar nueva celebración
    public function registrar_celebracion($desTip) 
    {
        try {
            $sql = "INSERT INTO tipo_celebracion (DesTip) VALUES (?)";
            $stmt = $this->conexion->prepare($sql);
            if (!$stmt) {
                return ['status' => 'error', 'msg' => 'Error al preparar la consulta: ' . $this->conexion->error];
            }

            $stmt->bind_param("s", $desTip);
            $ok = $stmt->execute();

            if (!$ok) {
                return ['status' => 'error', 'msg' => 'Error al ejecutar: ' . $stmt->error];
            }

            return ['status' => 'ok', 'msg' => 'Celebración registrada correctamente.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'msg' => 'Excepción: ' . $e->getMessage()];
        }
    }

        // Obtener usuario por ID (para admin)
    public function obtenerUsuarioPorId($idUsu)
    {
        $sql = "SELECT IdUsu, NomUsu, ApeUsu, Usuario, RolUsu FROM usuarios WHERE IdUsu = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idUsu);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        return $usuario;
    }

    // Actualizar rol (activar / desactivar)
    public function actualizar_rol_usuario($idUsu, $nuevoRol)
    {
        $sql = "UPDATE usuarios SET RolUsu = ? WHERE IdUsu = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $nuevoRol, $idUsu);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // Traducir código de rol a texto
    public function nombre_rol_desde_codigo($rol)
    {
        $rol = (int)$rol;
        switch ($rol) {
            case 10:
            case 100: return "Administrador(a)";
            case 20:
            case 200: return "Ministro";
            case 30:
            case 300: return "Secretario(a)";
            case 40:
            case 400: return "Coordinador(a)";
            case 50:
            case 500: return "Catequista";
            default:  return "Desconocido";
        }
    }

    // Verificar si un usuario tiene registros en el sistema
    public function usuario_tiene_registros($idUsu)
    {
        $total = 0;

        // 👉 Aquí debes sumar todas las tablas donde se use IdUsu como FK
        // Ejemplo 1: si en la tabla individuos guardas quién registró:
        $sql = "SELECT COUNT(*) AS c FROM individuos WHERE IdUsu = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idUsu);
        $stmt->execute();
        $stmt->bind_result($c1);
        $stmt->fetch();
        $stmt->close();
        $total += (int)$c1;

        // Ejemplo 2: si tuvieras otra tabla, repetirías el patrón y sumarías:
        // $sql = "SELECT COUNT(*) FROM otra_tabla WHERE IdUsu = ?";
        // ...

        return $total > 0;
    }

    // Eliminar usuario (y sus preguntas de seguridad)
    public function eliminar_usuario($idUsu)
    {
        $this->conexion->begin_transaction();
        try {
            // Primero eliminar preguntas de seguridad
            $sql1 = "DELETE FROM preguntas_seguridad WHERE IdUsu = ?";
            $stmt1 = $this->conexion->prepare($sql1);
            $stmt1->bind_param("i", $idUsu);
            $stmt1->execute();
            $stmt1->close();

            // Luego eliminar usuario
            $sql2 = "DELETE FROM usuarios WHERE IdUsu = ?";
            $stmt2 = $this->conexion->prepare($sql2);
            $stmt2->bind_param("i", $idUsu);
            $stmt2->execute();
            $stmt2->close();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            return false;
        }
    }

/* ============================================================
   📌 LISTADO RESUMIDO DE CELEBRACIONES (BAUTIZOS)
   ============================================================ */
    public function obtener_celebraciones_bautizo()
    {
        // IdCel, datos del individuo, fecha de nacimiento y fecha de celebración
        $sql = "SELECT 
                    c.IdCel,
                    i.IdInd,
                    i.NomInd,
                    i.ApeInd,
                    i.FecNacInd,
                    c.FechCel
                FROM celebracion c
                INNER JOIN individuo_celebracion ic ON ic.IdCel = c.IdCel
                INNER JOIN individuos i ON i.IdInd = ic.IdInd
                WHERE c.TipCel = 1            -- 1 = Bautizo (ajusta si usas otro código)
                ORDER BY c.FechCel ASC";

        return $this->conexion->query($sql);
    }

/* ============================================================
   📌 DETALLE COMPLETO DE UNA CELEBRACIÓN PARA LA FICHA
   ============================================================ */
    public function obtener_detalle_celebracion($idCel)
    {
        $sql = "SELECT 
                    c.IdCel,
                    c.FechCel,
                    c.NumLib,
                    c.NumFol,
                    c.Lugar,
                    tc.DesTip       AS TipoCelebracion,
                    
                    i.IdInd,
                    i.NomInd,
                    i.ApeInd,
                    i.FecNacInd,
                    i.LugNacInd,
                    i.FilInd,

                    ic.RegCiv,
                    ic.NotMar,

                    -- Padres (madre y padre en una sola cadena)
                    GROUP_CONCAT(DISTINCT CONCAT(pad.Nom, ' ', pad.Ape) SEPARATOR ' y ') AS Padres,

                    -- Padrinos en una sola cadena
                    GROUP_CONCAT(DISTINCT CONCAT(padr.Nom, ' ', padr.Ape) SEPARATOR ' y ') AS Padrinos,

                    m.Nom AS MinNom,
                    m.Ape AS MinApe
                FROM celebracion c
                INNER JOIN individuo_celebracion ic ON ic.IdCel = c.IdCel
                INNER JOIN individuos i            ON i.IdInd = ic.IdInd
                LEFT JOIN tipo_celebracion tc      ON tc.IdTip = c.TipCel
                LEFT JOIN padres pad               ON pad.IdInd = i.IdInd
                LEFT JOIN padrinos padr            ON padr.IdInd = i.IdInd
                LEFT JOIN ministro_celebrante m    ON m.IdMinCel = c.IdMin
                WHERE c.IdCel = ?
                GROUP BY 
                    c.IdCel, c.FechCel, c.NumLib, c.NumFol, c.Lugar, tc.DesTip,
                    i.IdInd, i.NomInd, i.ApeInd, i.FecNacInd, i.LugNacInd, i.FilInd,
                    ic.RegCiv, ic.NotMar, m.Nom, m.Ape";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idCel);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();

            return [
                'idCel'          => $row['IdCel'],
                'idInd'          => $row['IdInd'],
                'nombre'         => $row['NomInd'],
                'apellido'       => $row['ApeInd'],
                'nombre_completo'=> $row['NomInd'] . ' ' . $row['ApeInd'],
                'fec_nac'        => $row['FecNacInd'],
                'lugar_nac'      => $row['LugNacInd'],
                'filiacion'      => $row['FilInd'],
                'fecha_bautizo'  => $row['FechCel'],
                'lugar_bautizo'  => $row['Lugar'],
                'num_libro'      => $row['NumLib'],
                'num_folio'      => $row['NumFol'],
                'tipo_celebracion'=> $row['TipoCelebracion'],
                'registro_civil' => $row['RegCiv'],
                'observaciones'  => $row['NotMar'],
                'padres'         => $row['Padres'],
                'padrinos'       => $row['Padrinos'],
                'ministro'       => trim($row['MinNom'] . ' ' . $row['MinApe']),
            ];
        }

        return null;
    }

    public function obtener_ministros_firmantes() 
    {
        $sql = "SELECT IdUsu, NomUsu, ApeUsu
                FROM usuarios
                WHERE RolUsu IN (20, 200)";  // ministro activo / solicitud
        return $this->conexion->query($sql);
    }

    public function obtener_usuario_por_id($idUsu) 
    {
        $sql = "SELECT IdUsu, NomUsu, ApeUsu, RolUsu
                FROM usuarios
                WHERE IdUsu = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idUsu);
        $stmt->execute();
        $res = $stmt->get_result();
        $dato = $res->fetch_assoc();
        $stmt->close();
        return $dato;
    }

/**
 * Devuelve todos los datos necesarios para el certificado de bautizo
 * a partir del IdCel. Básicamente lo mismo que usas en detalle_celebracion,
 * pero del lado del modelo.
 */
     /**
     * Datos para el certificado de bautizo (FPDF)
     */
    public function obtener_datos_certificado_bautizo($idCel)
    {
        $sql = "
            SELECT 
                c.IdCel,
                c.FechCel AS FechaBautizo,
                c.NumLib,
                c.NumFol,
                c.Lugar AS LugarBautizo,

                ind.IdInd,
                ind.NomInd,
                ind.ApeInd,
                ind.LugNacInd,
                ind.FecNacInd,
                ind.SexInd,
                ind.FilInd,

                -- Madre (Sex = 2)
                m.Nom  AS NomMad,
                m.Ape  AS ApeMad,

                -- Padre (Sex = 1)
                p.Nom  AS NomPad,
                p.Ape  AS ApePad,

                -- Registro civil y observaciones
                ic.RegCiv AS RegistroCivil,
                ic.NotMar AS Observaciones,

                -- Todos los padres (por si se usa como cadena)
                GROUP_CONCAT(DISTINCT CONCAT(pad.Nom, ' ', pad.Ape) SEPARATOR ' y ') AS Padres,

                -- Padrinos (si existen)
                GROUP_CONCAT(DISTINCT CONCAT(padr.Nom, ' ', padr.Ape) SEPARATOR ' y ') AS Padrinos,

                -- Ministro celebrante
                min.Nom AS MinNom,
                min.Ape AS MinApe

            FROM celebracion c
            INNER JOIN individuo_celebracion ic ON ic.IdCel = c.IdCel
            INNER JOIN individuos ind           ON ind.IdInd = ic.IdInd

            LEFT JOIN padres m   ON m.IdInd   = ind.IdInd AND m.Sex = 2  -- Madre
            LEFT JOIN padres p   ON p.IdInd   = ind.IdInd AND p.Sex = 1  -- Padre

            LEFT JOIN padres   pad  ON pad.IdInd  = ind.IdInd
            LEFT JOIN padrinos padr ON padr.IdInd = ind.IdInd

            LEFT JOIN ministro_celebrante min ON min.IdMinCel = c.IdMin

            WHERE c.IdCel = ?
            GROUP BY
                c.IdCel, c.FechCel, c.NumLib, c.NumFol, c.Lugar,
                ind.IdInd, ind.NomInd, ind.ApeInd, ind.LugNacInd, ind.FecNacInd, ind.SexInd, ind.FilInd,
                m.Nom, m.Ape, p.Nom, p.Ape,
                ic.RegCiv, ic.NotMar,
                min.Nom, min.Ape
            LIMIT 1;
        ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $idCel);
        $stmt->execute();
        $res = $stmt->get_result();
        $dato = $res->fetch_assoc();
        $stmt->close();
        return $dato;
    }












    // ✅ Consultar registros genéricos
    public function obtener_todos($tabla) {
        $sql = "SELECT * FROM $tabla";
        return $this->conexion->query($sql);
    }



}
?>
