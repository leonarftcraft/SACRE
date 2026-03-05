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

    public function vista_administrar_api()
    {
        $apiKeys = $this->_leer_api_keys();
        $contenido = "view/administrar_api.php";
        require_once "view/layout.php";
    }

    public function vista_celebraciones()
    {
        $contenido = "view/celebraciones.php";
        require_once "view/layout.php";
    }

    public function vista_desplegar_server()
    {
        // Obtener estado actual del server (simulado en archivo o sesión)
        $archivo_estado = 'server_status.txt';
        $estado_server = file_exists($archivo_estado) ? file_get_contents($archivo_estado) : '0';
        
        // Obtener datos para las tablas
        $listaBautizados = $this->model->obtener_reporte_bautizados();
        
        // 🔹 Obtener IP y generar URL para móviles
        $ip_server = getHostByName(getHostName());
        $ruta_proyecto = dirname($_SERVER['SCRIPT_NAME']);
        $ruta_proyecto = str_replace('\\', '/', $ruta_proyecto); // Normalizar slashes en Windows
        $ruta_proyecto = rtrim($ruta_proyecto, '/'); // Quitar slash final si existe
        
        $url_movil = "http://{$ip_server}{$ruta_proyecto}/?controller=sacrej&action=vista_cliente_movil";

        $contenido = "view/desplegar_server.php";
        require_once "view/layout.php";
    }

    /* ============================================================
       🔑 GESTIÓN DE API KEYS (ARCHIVO ENCRIPTADO)
       ============================================================ */

    private function _get_api_file_path() {
        return 'api_keys.enc';
    }
    
    private function _get_enc_key() {
        // En producción, esto debería estar en una variable de entorno
        return 'CLAVE_SECRETA_SACREJ_2025_SEGURA'; 
    }

    private function _leer_api_keys() {
        $file = $this->_get_api_file_path();
        if (!file_exists($file)) return [];
        
        $content = file_get_contents($file);
        if (empty($content)) return [];

        // Formato esperado: IV::DatosEncriptados
        $parts = explode('::', $content);
        if (count($parts) !== 2) return [];
        
        $iv = base64_decode($parts[0]);
        $encrypted = base64_decode($parts[1]);
        $key = $this->_get_enc_key();
        
        $json = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        return json_decode($json, true) ?? [];
    }

    private function _guardar_api_keys_file($data) {
        $key = $this->_get_enc_key();
        $json = json_encode($data);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $encrypted = openssl_encrypt($json, 'AES-256-CBC', $key, 0, $iv);
        
        $content = base64_encode($iv) . '::' . base64_encode($encrypted);
        file_put_contents($this->_get_api_file_path(), $content);
    }

    public function guardar_api_key() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $email = trim($_POST['email'] ?? '');
        $apiKey = trim($_POST['apiKey'] ?? '');

        if (!$email || !$apiKey) {
            echo json_encode(['status' => 'error', 'msg' => 'Correo y Llave son obligatorios.']);
            exit;
        }

        // 🔹 Verificar si la API Key ya existe (evitar duplicados)
        $keys = $this->_leer_api_keys();
        foreach ($keys as $storedData) {
            if ($storedData['key'] === $apiKey) {
                echo json_encode(['status' => 'error', 'msg' => 'Esta API Key ya se encuentra registrada en el sistema.']);
                exit;
            }
        }

        // 🔹 VALIDAR LA LLAVE CON GEMINI ANTES DE GUARDAR
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=$apiKey";
        $payload = [
            "contents" => [
                ["parts" => [["text" => "Test"]]]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        $jsonRes = json_decode($response, true);

        if ($curlError || isset($jsonRes['error'])) {
            $msg = $jsonRes['error']['message'] ?? ($curlError ?: 'Error desconocido al validar la llave.');
            echo json_encode(['status' => 'error', 'msg' => 'Gemini rechazó la llave: ' . $msg]);
            exit;
        }

        // Usamos el email como índice para evitar duplicados
        $keys[$email] = [
            'email' => $email,
            'key' => $apiKey,
            'fecha' => date('Y-m-d H:i:s')
        ];

        $this->_guardar_api_keys_file($keys);
        echo json_encode(['status' => 'ok', 'msg' => 'API Key guardada correctamente.']);
        exit;
    }

    public function eliminar_api_key() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $email = trim($_POST['email'] ?? '');
        $keys = $this->_leer_api_keys();

        if (isset($keys[$email])) {
            unset($keys[$email]);
            $this->_guardar_api_keys_file($keys);
        }
        echo json_encode(['status' => 'ok', 'msg' => 'API Key eliminada.']);
        exit;
    }

    /* ============================================================
       📡📡 API Y LÓGICA DEL SERVIDOR IA (LOCAL)
       ============================================================ */

    public function cambiar_estado_server()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuevo_estado = $_POST['estado'] ?? '0';
            
            if (file_put_contents('server_status.txt', $nuevo_estado) === false) {
                echo json_encode(['success' => false, 'mensaje' => 'Error: No se pudo guardar el estado. Verifique permisos de escritura.']);
                exit;
            }
            
            // Opcional: Limpiar lista de clientes al desactivar/activar
            if ($nuevo_estado == '0') {
                file_put_contents('connected_clients.json', json_encode([]));
            }

            echo json_encode([
                'success' => true, 
                'estado' => $nuevo_estado,
                'mensaje' => $nuevo_estado == '1' ? 'El servidor está ACTIVO' : 'El servidor está DESACTIVADO'
            ]);
            exit;
        }
    }

    public function api_verificar_estado()
    {
        header('Content-Type: application/json');
        $estado = file_exists('server_status.txt') ? trim(file_get_contents('server_status.txt')) : '0';
        echo json_encode(['estado' => $estado]);
        exit;
    }

    // 📱 VISTA PARA EL TELÉFONO (Asegúrate de tener esta función)
    public function vista_cliente_movil()
    {
        // Cargar datos para el formulario de bautizo
        $tipos_celebracion = $this->model->obtener_todos("tipo_celebracion");
        $ministros = $this->model->obtener_todos("ministro_celebrante");
        $jerarquias = $this->model->obtener_todos("jerarquia_ministro");
        // Esta vista no usa el layout principal para ser más ligera en el móvil
        require_once "view/cliente_movil.php";
    }

    public function api_registrar_cliente()
    {
        header('Content-Type: application/json');
        
        // 1. Verificar si el servidor está ACTIVO
        $estado_server = file_exists('server_status.txt') ? trim(file_get_contents('server_status.txt')) : '0';
        if ($estado_server !== '1') {
            echo json_encode(['success' => false, 'message' => 'El servidor está cerrado. Pida al administrador que lo active.']);
            exit;
        }

        $nombre = $_POST['nombre'] ?? '';
        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            exit;
        }

        $archivo = 'connected_clients.json';
        $content = file_exists($archivo) ? file_get_contents($archivo) : '{}';
        $clientes = json_decode($content, true);
        if (!is_array($clientes)) $clientes = [];

        // Migración: Si el archivo tiene el formato antiguo (lista simple), lo convertimos
        if (isset($clientes[0])) {
            $temp = [];
            foreach($clientes as $c) $temp[$c] = time();
            $clientes = $temp;
        }
        
        // Guardamos nombre y HORA actual (timestamp)
        $clientes[$nombre] = time();
        file_put_contents($archivo, json_encode($clientes));

        echo json_encode(['success' => true]);
        exit;
    }

    public function api_desconectar_cliente()
    {
        header('Content-Type: application/json');
        $nombre = $_POST['nombre'] ?? '';
        
        if (!empty($nombre)) {
            $archivo = 'connected_clients.json';
            $content = file_exists($archivo) ? file_get_contents($archivo) : '{}';
            $clientes = json_decode($content, true);
            
            // Manejo formato antiguo
            if (is_array($clientes) && isset($clientes[0])) {
                if (($key = array_search($nombre, $clientes)) !== false) {
                    unset($clientes[$key]);
                    $clientes = array_values($clientes);
                }
            } elseif (is_array($clientes)) {
                // Formato nuevo (asociativo)
                if (isset($clientes[$nombre])) {
                    unset($clientes[$nombre]);
                }
            }
            file_put_contents($archivo, json_encode($clientes));
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // 💓 NUEVA FUNCIÓN: Recibe el latido del cliente
    public function api_heartbeat()
    {
        header('Content-Type: application/json');
        $nombre = $_POST['nombre'] ?? '';

        if (!empty($nombre)) {
            $archivo = 'connected_clients.json';
            $content = file_exists($archivo) ? file_get_contents($archivo) : '{}';
            $clientes = json_decode($content, true);
            if (!is_array($clientes)) $clientes = [];

            // Si es formato antiguo, convertir
            if (isset($clientes[0])) {
                $temp = [];
                foreach($clientes as $c) $temp[$c] = time();
                $clientes = $temp;
            }

            // Actualizar la hora de "última vez visto"
            $clientes[$nombre] = time();
            file_put_contents($archivo, json_encode($clientes));
        }

        // Devolvemos el estado del server para que el móvil sepa si debe salir
        $estado = file_exists('server_status.txt') ? trim(file_get_contents('server_status.txt')) : '0';
        echo json_encode(['estado' => $estado]);
        exit;
    }

    public function api_obtener_clientes()
    {
        header('Content-Type: application/json');
        $archivo = 'connected_clients.json';
        $content = file_exists($archivo) ? file_get_contents($archivo) : '{}';
        $clientes = json_decode($content, true);
        if (!is_array($clientes)) $clientes = [];

        // Si es formato antiguo, devolverlo tal cual (se arreglará en el próximo registro/heartbeat)
        if (isset($clientes[0])) {
            echo json_encode($clientes);
            exit;
        }

        // 🧹 LIMPIEZA AUTOMÁTICA
        // Si un cliente no ha dado señal en 5 segundos, lo borramos
        $ahora = time();
        $limite = 5; // segundos de tolerancia
        $cambios = false;

        foreach ($clientes as $nombre => $ultimoVisto) {
            if (($ahora - $ultimoVisto) > $limite) {
                unset($clientes[$nombre]);
                $cambios = true;
            }
        }

        if ($cambios) {
            file_put_contents($archivo, json_encode($clientes));
        }

        // Devolver solo los nombres (las claves del array)
        echo json_encode(array_keys($clientes));
        exit;
    }

    public function api_procesar_imagen()
    {
        header('Content-Type: application/json');

        // 1. Verificar estado del servidor
        $estado_server = file_exists('server_status.txt') ? trim(file_get_contents('server_status.txt')) : '0';
        if ($estado_server !== '1') {
            echo json_encode(['error' => 'El sistema está desactivado por el administrador.']);
            exit;
        }

        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'No se recibió ninguna imagen válida.']);
            exit;
        }

        // 2. Preparar imagen para Gemini
        $path = $_FILES['imagen']['tmp_name'];
        $type = $_FILES['imagen']['type'];
        $data = file_get_contents($path);
        $base64 = base64_encode($data);

        // 🔹 Obtener lista de ministros de la BD para el prompt
        $resMinistros = $this->model->obtener_todos("ministro_celebrante");
        $listaMinistros = [];
        if ($resMinistros) {
            while ($row = $resMinistros->fetch_assoc()) {
                $listaMinistros[] = $row['Nom'] . ' ' . $row['Ape'];
            }
        }
        $ministrosString = implode(", ", $listaMinistros);

        $prompt = 'Realiza una extracción de texto de la imagen proporcionada mediante un análisis exhaustivo carácter por carácter (OCR de alta precisión). La imagen contiene una o más actas de Fe de Bautismo. Debes identificar cada letra, número y carácter especial, considerando caligrafía antigua o manuscrita.

Antes de comenzar la transcripción, lee todas las instrucciones a continuación y aplícalas rigurosamente.

## Instrucciones paso a paso:

    - **Diferenciación de letras confusas:** Presta especial atención a la distinción entre \'B\' y \'D\' mayúsculas. La \'B\' suele tener un trazo inicial con base más ancha o una conexión específica con la siguiente letra, mientras que la \'D\' tiene un bucle superior único.
    - **Regla para la \'S\' sola:** Ignora cualquier \'S\' que se aparezca a un & y que este sola no la incluyas en la transcripción.
    - **Fidelidad de datos:** Mantén la ortografía, acentuación y abreviaturas exactas del documento original (ej. si dice "Bautizé", no lo corrijas a "Bauticé"). Si un dato no es legible o no existe, usa `null`.
**Control sobre Fechas:**
Las fechas extraidas deben de ser interpretadas en formato DD/MM/YYYY. 
**Control Folio N°:**
El folio N. lo puedes encontrar como folio o como un numero en la parte superio de la imagen.
- **instrucciones para extraer la clave: Observaciones:** para esta clave: limitate a extraer exactamente lo que te digo, debajo del codigo de celebracion que en la imagen se identifica con "N°" puedes encontrar un texto puede ser un nombre y apellido  puedes tomarlo como Observaciones, ese texto es el que debes colocar en la clave Observaciones y si en la imagen en la clave observaciones hay algo concatena las dos observaciones.
**Estructura de los datos:**
    La imagen puede contener una o más Fe de Bautismo. Devuelve SIEMPRE un arreglo JSON `[...]` que contenga un objeto por cada acta encontrada. No devuelvas un objeto raíz, sino la lista directamente. Devuelve únicamente el JSON, sin explicaciones ni bloques de código markdown.

    ```json
    [
      {
      "Nombre del Bautizado": "",
      "Apellido del Bautizado": "",
      "Nombre del Padre": "",
      "Apellido del Padre": "",
      "Nombre de la Madre": "",
      "Apellido de la Madre": "",
      "Filiacion": "",
      "Lugar de nacimiento": "",
      "Fecha de nacimiento": "",
      "Fecha de bautizo": "",
      "Nombre de la Madrina": "",
      "Apellido de la madrina": "",
      "Nombre del Padrino": "",
      "Apellido del Padrino": "",
      "Ministro": "",
      "N°": "",
      "Folio N°": "",
      "Observaciones": "",
      "Observaciones2": "",
      "Registro Civil": ""
      }
    ]
    ```

    (' . $ministrosString . ') la clave ministro la vas a comparar con estos nombres si considera que es el mismo nombre o mismo ministro de esta lista vas a usar tal cual el nombre que esta en la lista. si no esta en la lista vas a ponerlo tal cual lo extragistes.';

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inline_data" => [
                                "mime_type" => $type,
                                "data" => $base64
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // 3. Obtener llaves y preparar ciclo de intentos (Fallback)
        $keysGuardadas = $this->_leer_api_keys();
        $keysToTry = [];

        if (!empty($keysGuardadas)) {
            $keysToTry = array_values($keysGuardadas);
            shuffle($keysToTry); // Mezclar para balancear carga
        } else {
            // Llave por defecto si no hay registradas
            $keysToTry[] = [
                'email' => 'Sistema (Default)',
                'key' => 'TU_API_KEY_DE_RESPALDO_AQUI'
            ];
        }

        $lastError = "No se pudo procesar la solicitud.";
        $lastEmail = "";
        $success = false;
        $finalResponse = "";

        foreach ($keysToTry as $kData) {
            $apiKey = $kData['key'];
            $email = $kData['email'];
            
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=$apiKey";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $curlErrno = curl_errno($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlErrno) {
                $lastError = "Error de conexión: " . $curlError;
                $lastEmail = $email;
                continue; // Intentar siguiente llave
            }

            $jsonRes = json_decode($response, true);

            // Si hay error explícito en el JSON (ej. quota exceeded, key invalid)
            if (isset($jsonRes['error'])) {
                $lastError = $jsonRes['error']['message'] ?? "Error desconocido de API";
                $lastEmail = $email;
                continue; // Intentar siguiente llave
            }

            // Éxito
            $success = true;
            $finalResponse = $response;
            break; // Salir del ciclo
        }

        if ($success) {
            echo $finalResponse;
        } else {
            // Fallaron todas
            echo json_encode([
                'error' => "Fallo con todas las llaves. Último intento ($lastEmail): $lastError"
            ]);
        }
        exit;
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
