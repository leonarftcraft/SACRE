<?php
require_once "model/sacrej.model.php";

class SacrejController
{
    private $model;

    public function __construct()
    {
        $this->model = new sacrejmodel();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /* ============================================================
       🏠 VISTAS PRINCIPALES Y LAYOUT
       ============================================================ */

    public function index()
    {
        if (isset($_SESSION['IdUsu'])) {
            $contenido = "view/inicio.php";
            require_once "view/layout.php";
        } else {
            require_once "view/index.php";
        }
    }

    public function iniciar()
    {
        require_once "view/iniciar_sesion.php";
    }

    public function registro()
    {
        require_once "view/registro.php";
    }

    public function recuperar_clave()
    {
        require_once "view/recuperar_clave.php";
    }

 


    /* ============================================================
       👥 VISTAS DE ADMINISTRADOR (usa layout)
       ============================================================ */
    public function vista_ministros()
    {
        $contenido = "view/ministros.php";
        require_once "view/layout.php";
    }
    public function vista_bautizos() 
    {
        $contenido = "view/bautizos.php";
        require_once "view/layout.php";
    }
    public function vista_celebraciones_registradas() 
    {
        $contenido = "view/celebraciones_registradas.php";
        require_once "view/layout.php";
    }

    public function vista_jerarquias()
    {
        $contenido = "view/jerarquias.php";
        require_once "view/layout.php";
    }

    public function vista_usuarios()
    {
        $contenido = "view/usuarios.php";
        require_once "view/layout.php";
    }

    public function vista_celebraciones()
    {
        $contenido = "view/celebraciones.php";
        require_once "view/layout.php";
    }

    /* ============================================================
       👤 USUARIOS Y SESIÓN
       ============================================================ */

    public function validar_usuario()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {
            $usuario = $_POST['usuario'];
            $existe = $this->model->validar_usuario($usuario);
            echo json_encode(['existe' => $existe]);
        }
    }

        public function registrar_usuario()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');

            // 🔹 Datos básicos
            $usuario      = trim($_POST['usuario']    ?? '');
            $contrasena   = trim($_POST['contrasena'] ?? '');
            $tipo_usuario = $_POST['tipo_usuario']    ?? '';
            $cedula       = trim($_POST['cedula']     ?? '');  // ✅ NUEVO
            $nombre       = trim($_POST['nombre']     ?? '');
            $apellido     = trim($_POST['apellido']   ?? '');

            // 🔹 Preguntas de seguridad
            $preguntas = [
                ['pregunta' => $_POST['pregunta1'] ?? '', 'respuesta' => $_POST['respuesta1'] ?? ''],
                ['pregunta' => $_POST['pregunta2'] ?? '', 'respuesta' => $_POST['respuesta2'] ?? ''],
                ['pregunta' => $_POST['pregunta3'] ?? '', 'respuesta' => $_POST['respuesta3'] ?? ''],
                ['pregunta' => $_POST['pregunta4'] ?? '', 'respuesta' => $_POST['respuesta4'] ?? ''],
            ];

            /* ============================
               ✅ VALIDACIONES BÁSICAS
               ============================ */

            // Usuario duplicado
            if ($this->model->validar_usuario($usuario)) {
                echo json_encode(['error' => 'El usuario ya está registrado.']);
                exit;
            }

            // Cédula obligatoria y formato
            if ($cedula === '' || !preg_match('/^[0-9]{6,10}$/', $cedula)) {
                echo json_encode(['error' => 'La cédula es obligatoria y debe tener entre 6 y 10 dígitos numéricos.']);
                exit;
            }

            // Cédula duplicada (ID de usuario)
            // 📌 IMPORTANTE: crea en el modelo el método validar_cedula($cedula)
            if ($this->model->validar_cedula($cedula)) {
                echo json_encode(['error' => 'La cédula ya está registrada en el sistema.']);
                exit;
            }

            // Validar tipo de usuario (rol de SOLICITUD: 100, 200, 300, 400, 500)
            if ($tipo_usuario === '') {
                echo json_encode(['error' => 'Debe seleccionar un tipo de usuario.']);
                exit;
            }

            // Validar nombre y apellido básicos (por si llegan vacíos)
            if ($nombre === '' || $apellido === '') {
                echo json_encode(['error' => 'Debe ingresar nombre y apellido.']);
                exit;
            }

            /* ============================
               💾 REGISTRO EN EL MODELO
               ============================ */

            // Ahora el modelo debe recibir también la cédula
            // y usarla como IdUsu en la tabla de usuarios
            $resultado = $this->model->registrar_usuario(
                $usuario,
                $contrasena,
                $tipo_usuario, // aquí ya vienen 100, 200, 300, 400, 500
                $cedula,       // ✅ NUEVO PARÁMETRO
                $nombre,
                $apellido,
                $preguntas
            );

            if ($resultado) {
                echo json_encode(['success' => 'Solicitud de registro enviada. Un administrador debe aprobarla.']);
            } else {
                echo json_encode(['error' => 'Error al registrar el usuario.']);
            }
            exit;
        }
    }


    public function iniciar_sesion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_POST['usuario'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';

            $resultado = $this->model->verificar_usuario($usuario, $contrasena);

            if ($resultado) {
                $_SESSION['IdUsu'] = $resultado['IdUsu'];
                $_SESSION['Usuario'] = $resultado['Usuario'];
                $_SESSION['NomUsu'] = $resultado['NomUsu'];
                $_SESSION['ApeUsu'] = $resultado['ApeUsu'];
                $_SESSION['RolUsu'] = $resultado['RolUsu'];
                echo 1;
            } else {
                echo 0;
            }
            exit;
        }
    }

    public function cerrarsesion()
    {
        session_destroy();
        header("Location: ?controller=sacrej&action=index");
        exit;
    }

    public function obtener_usuario()
    {
        if (!isset($_SESSION['IdUsu'])) {
            echo json_encode(['success' => false, 'error' => 'No hay sesión activa.']);
            return;
        }

        $idUsu = $_SESSION['IdUsu'];
        $usuario = $this->model->obtenerUsuario($idUsu);

        echo json_encode($usuario ?
            ['success' => true, 'usuario' => $usuario] :
            ['success' => false, 'error' => 'Usuario no encontrado.']
        );
    }

    public function recuperar_usuario()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {
            $usuario = $_POST['usuario'];
            $preguntas = $this->model->obtener_preguntas_aleatorias($usuario, 2);

            echo json_encode($preguntas ?
                ['success' => true, 'preguntas' => $preguntas] :
                ['success' => false, 'error' => 'Usuario no encontrado']
            );
            exit;
        }
    }

    public function validar_preguntas()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['respuestas'])) {
            $usuario = $_POST['usuario'];
            $respuestas = $_POST['respuestas'];
            $validas = $this->model->validar_respuestas($usuario, $respuestas);

            echo json_encode($validas ?
                ['success' => true] :
                ['success' => false, 'error' => 'Respuestas incorrectas']
            );
            exit;
        }
    }

    public function actualizar_clave()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'], $_POST['nuevaClave'])) {
            $usuario = $_POST['usuario'];
            $nuevaClave = $_POST['nuevaClave'];
            $actualizado = $this->model->actualizar_clave($usuario, $nuevaClave);

            echo json_encode($actualizado ?
                ['success' => true] :
                ['success' => false, 'error' => 'No se pudo actualizar la contraseña']
            );
            exit;
        }
    }

    /* ============================================================
       💧 REGISTRO DE BAUTIZOS
       ============================================================ */

    public function agregar_bautizo()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            // ✅ Datos del individuo
            $individuo = [
                'IdInd'     => $_POST['IdInd'] ?? '',
                'NomInd'    => $_POST['NomInd'] ?? '',
                'ApeInd'    => $_POST['ApeInd'] ?? '',
                'SexInd'    => $_POST['SexInd'] ?? '',
                'FecNacInd' => $_POST['FecNacInd'] ?? '',
                'LugNacInd' => $_POST['LugNacInd'] ?? '',
                'FilInd'    => $_POST['FilInd'] ?? '',
                'IdUsu'     => $_SESSION['IdUsu'] ?? 1
            ];

            // ✅ Validar si ya existe ese ID (protección previa)
            $check = $this->model->verificar_existencia_individuo($individuo['IdInd']);
            if ($check) {
                echo json_encode(['status' => 'error', 'msg' => 'El ID del bautizado ya existe en la base de datos.']);
                return;
            }

            // ✅ Datos de la madre
            $madre = ['Nom' => $_POST['NomMad'] ?? '', 'Ape' => $_POST['ApeMad'] ?? '', 'Sex' => 2];

            // ✅ Padre opcional
            $padre = null;
            if (!empty($_POST['NomPad']) || !empty($_POST['ApePad'])) {
                $padre = ['Nom' => $_POST['NomPad'], 'Ape' => $_POST['ApePad'], 'Sex' => 1];
            }

            // ✅ Padrinos
            $padrinos = [
                ['Nom' => $_POST['Pad1Nom'], 'Ape' => $_POST['Pad1Ape'], 'Sex' => $_POST['Pad1Sex']],
                ['Nom' => $_POST['Pad2Nom'], 'Ape' => $_POST['Pad2Ape'], 'Sex' => $_POST['Pad2Sex']]
            ];

            // ✅ Celebración
            $celebracion = [
                'IdCel'   => $_POST['IdCel'],
                'FechCel' => $_POST['FechCel'],
                'TipCel'  => $_POST['TipCel'] ?? 1,
                'NumLib'  => $_POST['NumLib'],
                'NumFol'  => $_POST['NumFol'],
                'IdMin'   => $_POST['IdMin'],
                'Lugar'   => $_POST['Lugar']
            ];

            // ✅ Enlaces
            $enlace = [
                'RegCiv'    => $_POST['RegCiv'] ?? '',
                'NotMar'    => $_POST['NotMar'] ?? '',
                'TipCelPad' => $celebracion['TipCel']
            ];

            // 🔥 Registrar
            $res = $this->model->registrar_bautizo_completo(
                $individuo, $madre, $padre, $padrinos, $celebracion, $enlace
            );

            echo json_encode($res);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }









    /* ============================================================
       🧍 REGISTRO DE MINISTROS
       ============================================================ */

   public function agregar_ministro() 
   {
        ob_clean(); // 🔥 Limpia salidas previas
        header('Content-Type: application/json');

        $nom = trim($_POST['Nom'] ?? '');
        $ape = trim($_POST['Ape'] ?? '');
        $codJer = intval($_POST['CodJer'] ?? 0);

        if ($nom === '' || $ape === '' || $codJer === 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Todos los campos son obligatorios.']);
            exit;
        }

        $resultado = $this->model->registrar_ministro($nom, $ape, $codJer);
        echo json_encode($resultado);
        exit;
    }


    /* ============================================================
       📜 REGISTRO DE JERARQUÍAS
       ============================================================ */

    public function agregar_jerarquia()
    {
        file_put_contents("debug_request.txt", print_r($_SERVER, true));

        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        file_put_contents("debug_controller.txt", "Datos recibidos:\n" . print_r($_POST, true));

        $NomJer = trim($_POST['NomJer'] ?? '');
        $DesJer = trim($_POST['DesJer'] ?? '');

        if ($NomJer === '' || $DesJer === '') {
            echo json_encode(['status' => 'error', 'msg' => 'Por favor completa todos los campos.']);
            exit;
        }

        $resultado = $this->model->registrar_jerarquia($NomJer, $DesJer);
        file_put_contents("debug_controller.txt", "Resultado del modelo:\n" . print_r($resultado, true), FILE_APPEND);

        echo json_encode($resultado);
        exit;
    }

    /* ============================================================
       📜 REGISTRO DE CELEBRACIONES
       ============================================================ */


    public function agregar_celebracion() {
    ob_clean();
    header('Content-Type: application/json');

    $desTip = trim($_POST['DesTip'] ?? '');

    if ($desTip === '') {
        echo json_encode(['status' => 'error', 'msg' => 'Debe ingresar la descripción de la celebración.']);
        exit;
    }

    $resultado = $this->model->registrar_celebracion($desTip);
    echo json_encode($resultado);
    exit;
}

    /* ============================================================
       👥 GESTIÓN DE USUARIOS (activar / desactivar / eliminar)
       ============================================================ */

    public function cambiar_estado_usuario()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
            echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        $id = (int)$_POST['id'];

        // Obtener datos actuales del usuario
        $usuario = $this->model->obtenerUsuarioPorId($id);
        if (!$usuario) {
            echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            return;
        }

        $rolActual = (int)$usuario['RolUsu'];

        // Activo (2 dígitos) -> Inactivo (3 dígitos)
        // Inactivo (3 dígitos) -> Activo (2 dígitos)
        if ($rolActual >= 100) {
            $rolNuevo = (int)($rolActual / 10); // 100 -> 10, 200 -> 20, etc.
        } else {
            $rolNuevo = (int)($rolActual * 10); // 10 -> 100, 20 -> 200, etc.
        }

        $ok = $this->model->actualizar_rol_usuario($id, $rolNuevo);
        if (!$ok) {
            echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el rol']);
            return;
        }

        // Helper para nombre de rol (igual que en la vista)
        $rolTexto = $this->model->nombre_rol_desde_codigo($rolNuevo);
        $esActivo = $rolNuevo < 100;
        $estadoTexto = $esActivo ? 'Activo' : 'Inactivo';
        $botonTexto = $esActivo ? 'Desactivar' : 'Activar';
        $botonClase = $esActivo ? 'btn-warning' : 'btn-success';

        echo json_encode([
            'success'      => true,
            'message'      => 'Estado de usuario actualizado correctamente.',
            'rol_nuevo'    => $rolNuevo,
            'rol_texto'    => $rolTexto,
            'estado_texto' => $estadoTexto,
            'boton_texto'  => $botonTexto,
            'boton_clase'  => $botonClase,
        ]);
    }

    public function eliminar_usuario()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
            echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        $id = (int)$_POST['id'];

        // Obtener usuario
        $usuario = $this->model->obtenerUsuarioPorId($id);
        if (!$usuario) {
            echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            return;
        }

        $rol = (int)$usuario['RolUsu'];

        // Debe estar INACTIVO (3 dígitos)
        if ($rol < 100) {
            echo json_encode(['success' => false, 'error' => 'Debe desactivar el usuario antes de eliminarlo.']);
            return;
        }

        // Debe no tener registros en el sistema
        if ($this->model->usuario_tiene_registros($id)) {
            echo json_encode([
                'success' => false,
                'error'   => 'No se puede eliminar el usuario porque tiene registros asociados en el sistema.'
            ]);
            return;
        }

        $ok = $this->model->eliminar_usuario($id);
        if (!$ok) {
            echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el usuario.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Usuario eliminado correctamente.'
        ]);
    }

    /* ============================================================
   📋 DETALLE DE CELEBRACIÓN (AJAX PARA LA FICHA)
   ============================================================ */
    public function detalle_celebracion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idCel'])) {
            $idCel = (int) $_POST['idCel'];

            $detalle = $this->model->obtener_detalle_celebracion($idCel);

            if ($detalle) {
                echo json_encode([
                    'success' => true,
                    'data'    => $detalle
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error'   => 'No se encontró la celebración solicitada.'
                ]);
            }
            exit;
        }

        // Si llega por GET o sin id válido
        echo json_encode([
            'success' => false,
            'error'   => 'Petición no válida.'
        ]);
        exit;
    }

    public function generar_certificado()
{
    if (!isset($_GET['idCel'])) {
        echo "Falta el identificador de la celebración.";
        return;
    }

    $idCel = (int) $_GET['idCel'];
    $idUsu = isset($_GET['idUsu']) ? (int) $_GET['idUsu'] : null;

    // Datos del bautizo
    $datos = $this->model->obtener_datos_certificado_bautizo($idCel);

    if (!$datos) {
        echo "No se encontró la información del bautizo.";
        exit;
    }

    // Datos del usuario/ministro que FIRMA el certificado
    $minFirmante = null;
    if ($idUsu) {
        $minFirmante = $this->model->obtener_usuario_por_id($idUsu);
    }

    // Limpiar buffer para evitar errores de FPDF
    if (ob_get_length()) {
        ob_clean();
    }

    require "view/libs/fpdf/fpdf.php";

    // Para fechas con mes en texto
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo',      4 => 'abril',
        5 => 'mayo',  6 => 'junio',   7 => 'julio',      8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];

    // Preparar datos principales
    $nombreCompleto = trim(($datos['NomInd'] ?? '') . ' ' . ($datos['ApeInd'] ?? ''));
    $padre = trim(($datos['NomPad'] ?? '') . ' ' . ($datos['ApePad'] ?? ''));
    $madre = trim(($datos['NomMad'] ?? '') . ' ' . ($datos['ApeMad'] ?? ''));

    $padresTexto = '';
    if ($padre && $madre) {
        $padresTexto = $padre . ' – ' . $madre;
    } elseif ($padre) {
        $padresTexto = $padre;
    } elseif ($madre) {
        $padresTexto = $madre;
    } else {
        $padresTexto = '________________________';
    }

    $padrinosTexto = $datos['Padrinos'] ?? '';
    if (!$padrinosTexto) {
        $padrinosTexto = '________________________';
    }

    $fechaBautizoBD  = $datos['FechaBautizo'] ?? '';
    $fechaNacimiento = $datos['FecNacInd']    ?? '';
    $lugarBautizo    = $datos['LugarBautizo'] ?? 'esta parroquia';
    $lugarNac        = $datos['LugNacInd']    ?? '________________________';
    $libro           = $datos['NumLib']       ?? '___';
    $folio           = $datos['NumFol']       ?? '___';
    $registroCiv     = $datos['RegistroCivil'] ?? '';
    $observaciones   = $datos['Observaciones'] ?? '';
    $minCelebrante   = trim(($datos['MinNom'] ?? '') . ' ' . ($datos['MinApe'] ?? ''));

    // Formatear fechas como en el modelo (día, mes en texto, año)
    $fechaBautizoTexto = '';
    if ($fechaBautizoBD) {
        $t  = strtotime($fechaBautizoBD);
        $d  = date('d', $t);
        $m  = (int) date('n', $t);
        $a  = date('Y', $t);
        $fechaBautizoTexto = "$d de " . $meses[$m] . " de $a";
    }

    $fechaNacTexto = '';
    if ($fechaNacimiento) {
        $t  = strtotime($fechaNacimiento);
        $d  = date('d', $t);
        $m  = (int) date('n', $t);
        $a  = date('Y', $t);
        $fechaNacTexto = "$d de " . $meses[$m] . " de $a";
    }

    // Fecha de emisión (hoy)
    $tHoy  = time();
    $dHoy  = date('d', $tHoy);
    $mHoy  = (int) date('n', $tHoy);
    $aHoy  = date('Y', $tHoy);
    $fechaHoyTexto = "$dHoy Días del mes de " . ucfirst($meses[$mHoy]) . " del $aHoy";

    // ---------------------------------------------------------
    // Crear PDF
    // ---------------------------------------------------------
    $pdf = new FPDF('P', 'mm', 'Letter');
    $pdf->SetMargins(20, 15, 20);
    $pdf->AddPage();

    // Imagen de la esquina (logo parroquia)
    // Ajusta el path si tu estructura es distinta
    $pdf->Image('view/images/logo_parroquia.jpg', 18, 15, 25); // x, y, ancho

    // Encabezado centrado
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY(20, 15);
    $pdf->Cell(0, 5, utf8_decode('DIOCESIS DE SAN CRISTOBAL'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('VICARIA LA ENCARNACION DEL SEÑOR'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('PARROQUIA ECLESIASTICA SAGRADO CORAZON DE JESUS'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('LA FRIA - ESTADO TACHIRA'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('VENEZUELA'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('0277-5411823'), 0, 1, 'C');
    $pdf->Ln(3);

    // Título CERTIFICADO DE BAUTIZO
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(0, 6, utf8_decode('CERTIFICADO DE BAUTIZO'), 0, 1, 'C');
    $pdf->Ln(5);

    // Cuerpo del certificado (similar al modelo)
    $pdf->SetFont('Arial', '', 11);

    // Primer párrafo
    $texto1 = "El Suscrito, PARROCO de Sagrado Corazón de Jesús de La Fría, CERTIFICA que "
            . "en el Libro: $libro Folio $folio del archivo a su cargo, se encuentra la partida "
            . "de Bautismo de:";
    $pdf->MultiCell(0, 5, utf8_decode($texto1), 0, 'J');
    $pdf->Ln(2);

    // Nombre del bautizado subrayado
    $pdf->SetFont('Arial', 'U', 11);
    $pdf->Cell(0, 5, utf8_decode($nombreCompleto), 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 11);

    // Hijo(a) de:
    $pdf->Write(5, utf8_decode('Hijo (a) de: '));
    $pdf->SetFont('Arial', 'U', 11);
    $pdf->Write(5, utf8_decode($padresTexto));
    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 11);

    // Nacido en...
    $pdf->Write(5, utf8_decode('Que nació en: '));
    $pdf->SetFont('Arial', 'U', 11);
    $pdf->Write(5, utf8_decode($lugarNac));
    $pdf->SetFont('Arial', '', 11);
    $pdf->Write(5, utf8_decode(' el día '));
    $pdf->SetFont('Arial', 'U', 11);
    $pdf->Write(5, utf8_decode($fechaNacTexto ?: '_____________'));
    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 11);

    // Fue Bautizado el día...
    $pdf->Write(5, utf8_decode('Fue Bautizado el día '));
    $pdf->SetFont('Arial', 'U', 11);
    $pdf->Write(5, utf8_decode($fechaBautizoTexto ?: '_____________'));
    $pdf->SetFont('Arial', '', 11);
    $pdf->Write(5, utf8_decode(' en '));
    $pdf->SetFont('Arial', 'U', 11);
    $pdf->Write(5, utf8_decode($lugarBautizo));
    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 11);

    // Padrinos
    $pdf->Write(5, utf8_decode('Sus Padrinos: '));
    $pdf->SetFont('Arial', 'U', 11);
    $pdf->Write(5, utf8_decode($padrinosTexto));
    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 11);

    // Ministro celebrante
    if ($minCelebrante) {
        $pdf->Write(5, utf8_decode('Ministro celebrante: '));
        $pdf->SetFont('Arial', 'U', 11);
        $pdf->Write(5, utf8_decode($minCelebrante));
        $pdf->Ln(7);
        $pdf->SetFont('Arial', '', 11);
    }

    // Registro civil (opcional)
    if ($registroCiv) {
        $pdf->Write(5, utf8_decode('Registro Civil: '));
        $pdf->SetFont('Arial', 'U', 11);
        $pdf->Write(5, utf8_decode($registroCiv));
        $pdf->Ln(7);
        $pdf->SetFont('Arial', '', 11);
    }

    // Observaciones (para la nota marginal se usará abajo, aquí solo si quieres)
    $pdf->Ln(8);

    // NOTA MARGINAL
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 6, utf8_decode('NOTA MARGINAL'), 0, 1, 'C');
    $pdf->Ln(2);

    // Líneas horizontales (tres espacios)
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 0, '', 'T', 1, 'C'); // línea 1
    $pdf->Ln(6);
    $pdf->Cell(0, 0, '', 'T', 1, 'C'); // línea 2
    $pdf->Ln(6);
    $pdf->Cell(0, 0, '', 'T', 1, 'C'); // línea 3
    $pdf->Ln(10);

    // Texto final (emisión)
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(
        0,
        5,
        utf8_decode(
            "La presente certificación se expide a petición de parte interesada para fines Eclesiásticos. "
            . "En: La Fría. A los $fechaHoyTexto."
        ),
        0,
        'J'
    );
    $pdf->Ln(20);

    // FIRMA
    $nombreFirmante = 'PBRO. ROGER A. CACERES C.';
    $cargoFirmante  = 'PARROCO';

    if ($minFirmante) {
        $nombreFirmante = trim($minFirmante['NomUsu'] . ' ' . $minFirmante['ApeUsu']);
        $cargoFirmante  = strtoupper($minFirmante['CarUsu'] ?? 'PARROCO');
    }

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 5, utf8_decode('_________________________________'), 0, 1, 'C');
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 5, utf8_decode($nombreFirmante), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 5, utf8_decode($cargoFirmante), 0, 1, 'C');

    // Salida
    $pdf->Output("I", "certificado_$idCel.pdf");
    exit;
}










}
?>
