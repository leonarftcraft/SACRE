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

    public function vista_respaldo()
    {
        $contenido = "view/respaldo.php";
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
        $ministros = $this->model->obtener_todos("ministro_celebrante");
        
        // 🔹 Obtener IP y generar URL para móviles
        $ip_server = getHostByName(getHostName());
        $ruta_proyecto = dirname($_SERVER['SCRIPT_NAME']);
        $ruta_proyecto = str_replace('\\', '/', $ruta_proyecto); // Normalizar slashes en Windows
        $ruta_proyecto = rtrim($ruta_proyecto, '/'); // Quitar slash final si existe
        
        $url_movil = "http://{$ip_server}{$ruta_proyecto}/?controller=sacrej&action=vista_cliente_movil";

        $contenido = "view/desplegar_server.php";
        require_once "view/layout.php";
    }

    public function api_obtener_bautizos_registrados()
    {
        header('Content-Type: application/json');
        $listaBautizados = $this->model->obtener_reporte_bautizados();
        $data = [];
        if ($listaBautizados) {
            while ($row = $listaBautizados->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        exit;
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
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=$apiKey";
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
            if (ob_get_length()) ob_clean(); // 🧹 Limpiar basura previa
            header('Content-Type: application/json');

            $nuevo_estado = $_POST['estado'] ?? '0';
            
            if (file_put_contents('server_status.txt', $nuevo_estado) === false) {
                echo json_encode(['success' => false, 'mensaje' => 'Error: No se pudo guardar el estado. Verifique permisos de escritura.']);
                exit;
            }
            
            if ($nuevo_estado == '0') {
                @file_put_contents('connected_clients.json', json_encode([]));
            }

            echo json_encode([
                'success' => true, 
                'estado' => $nuevo_estado,
                'mensaje' => ($nuevo_estado == '1' ? 'SERVIDOR ACTIVO' : 'SERVIDOR DESACTIVADO')
            ]);
            exit;
        }
    }

    public function api_toggle_ia_server()
    {
        if (ob_get_length()) ob_clean(); // 🧹 Limpiar cualquier salida previa
        header('Content-Type: application/json; charset=utf-8');

        $logFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ia_toggle_debug.log';
        $pidFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'AI_ser.pid';
        $serviceAction = $_POST['service_action'] ?? '';
        @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] service_action={$serviceAction} uri={$_SERVER['REQUEST_URI']} method={$_SERVER['REQUEST_METHOD']}\n", FILE_APPEND);

        $pythonCandidate = trim(shell_exec('where python 2>NUL')) ?: trim(shell_exec('where py 2>NUL'));
        $pythonPath = '';
        if ($pythonCandidate) {
            $paths = array_filter(array_map('trim', preg_split('/\r?\n/', $pythonCandidate)));
            if (!empty($paths)) {
                $pythonPath = reset($paths);
            }
        }

        if (empty($pythonPath)) {
            $msg = 'No se encontró Python en el PATH de Windows. Instale Python o agregue python/py al PATH.';
            @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] ERROR: {$msg}\n", FILE_APPEND);
            echo json_encode(['success' => false, 'mensaje' => $msg]);
            exit;
        }

        $scriptPath = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'AI_ser.py');
        if (!$scriptPath || !file_exists($scriptPath)) {
            $msg = 'No se encontró el script tools/AI_ser.py. Verifique la ruta.';
            @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] ERROR: {$msg}\n", FILE_APPEND);
            echo json_encode(['success' => false, 'mensaje' => $msg]);
            exit;
        }

        $pythonPath = trim($pythonPath);
        $scriptPath = trim($scriptPath);
        $workingDir = dirname($scriptPath);
        $pythonPathEsc = str_replace('"', '\\"', $pythonPath);
        $scriptPathEsc = str_replace('"', '\\"', $scriptPath);
        $workingDirEsc = str_replace('"', '\\"', $workingDir);

        if ($serviceAction === 'start') {
            $powershellCandidate = trim(shell_exec('where powershell 2>NUL'));
            $powershellPath = '';
            if ($powershellCandidate) {
                $powershellEntries = array_filter(array_map('trim', preg_split('/\r?\n/', $powershellCandidate)));
                if (!empty($powershellEntries)) {
                    $powershellPath = reset($powershellEntries);
                }
            }

            if ($powershellPath) {
                $powershellPathEsc = '"' . str_replace('"', '\\"', $powershellPath) . '"';
                $psCommand = "Start-Process -FilePath '$pythonPath' -ArgumentList '$scriptPath' -WorkingDirectory '$workingDir' -WindowStyle Hidden -PassThru | Select-Object -ExpandProperty Id";
                $comando = "$powershellPathEsc -NoProfile -WindowStyle Hidden -Command \"$psCommand\"";
                $pid = trim(shell_exec($comando));

                if (is_numeric($pid)) {
                    file_put_contents($pidFile, $pid);
                    @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] START command ejecutado: {$comando} PID={$pid}\n", FILE_APPEND);
                    echo json_encode(['success' => true, 'mensaje' => 'Microservicio SACRE-IA iniciado.']);
                    exit;
                }

                @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] WARNING: PowerShell start no devolvió PID. comando={$comando} salida={$pid}\n", FILE_APPEND);
            }

            // Fallback: usar start /B con cmd.exe
            $pythonPathEsc = '"' . $pythonPath . '"';
            $scriptPathEsc = '"' . $scriptPath . '"';
            $comando = "start /B \"\" cmd /C \"cd /d \"$workingDir\" && $pythonPathEsc $scriptPathEsc\" > NUL 2>&1";
            $process = @popen($comando, 'r');

            if ($process === false) {
                $msg = 'No se pudo iniciar el microservicio. Verifique permisos y que cmd.exe esté disponible.';
                @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] ERROR: {$msg} comando={$comando}\n", FILE_APPEND);
                echo json_encode(['success' => false, 'mensaje' => $msg]);
                exit;
            }

            pclose($process);
            @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] START command ejecutado: {$comando}\n", FILE_APPEND);
            echo json_encode(['success' => true, 'mensaje' => 'Microservicio SACRE-IA iniciado.']);
        } else if ($serviceAction === 'stop') {
            $stopped = false;
            if (file_exists($pidFile)) {
                $pid = trim(file_get_contents($pidFile));
                if (is_numeric($pid)) {
                    @shell_exec("taskkill /F /PID $pid /T > NUL 2>&1");
                    @unlink($pidFile);
                    $stopped = true;
                    @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] STOP command ejecutado: PID={$pid}\n", FILE_APPEND);
                }
            }

            if (!$stopped) {
                $port = 5000;
                $output = @shell_exec("netstat -ano | findstr :$port | findstr LISTENING");
                if ($output) {
                    $lines = explode("\n", trim($output));
                    foreach ($lines as $line) {
                        $parts = preg_split('/\s+/', trim($line), -1, PREG_SPLIT_NO_EMPTY);
                        $pid = end($parts);
                        if (!empty($pid) && is_numeric($pid)) {
                            @shell_exec("taskkill /F /PID $pid /T > NUL 2>&1");
                            $stopped = true;
                            @file_put_contents($logFile, date('Y-m-d H:i:s') . " [api_toggle_ia_server] STOP command ejecutado: PID={$pid}\n", FILE_APPEND);
                        }
                    }
                }
            }

            if ($stopped) {
                echo json_encode(['success' => true, 'mensaje' => 'Servicio IA detenido correctamente.']);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'El microservicio no está corriendo.']);
            }
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
        }
        exit;
    }

    public function api_verificar_estado_ia()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $pidFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'AI_ser.pid';
        $activo = false;

        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            if (is_numeric($pid)) {
                $processList = trim(shell_exec("tasklist /FI \"PID eq $pid\" 2>NUL"));
                if (preg_match('/\b' . preg_quote($pid, '/') . '\b/', $processList)) {
                    $activo = true;
                }
            }
        }

        if (!$activo) {
            $port = 5000;
            $output = @shell_exec("netstat -ano | findstr :$port | findstr LISTENING");
            $activo = !empty($output);
        }

        echo json_encode(['activo' => $activo]);
        exit;
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
        session_write_close(); // 🚀 Liberar sesión para evitar bloqueos
        
        // 1. Verificar si el servidor está ACTIVO
        $estado_server = file_exists('server_status.txt') ? trim(file_get_contents('server_status.txt')) : '0';
        if ($estado_server !== '1') {
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'message' => 'El servidor está desactivado.']);
            exit;
        }

        $nombre = $_POST['nombre'] ?? '';
        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            exit;
        }

        $archivo = 'connected_clients.json';
        clearstatcache(true, $archivo); // 🛡️ Limpiar cache de estado de archivo
        // 🔒 Bloqueo de archivo para evitar corrupción
        $fp = fopen($archivo, 'c+');
        if (flock($fp, LOCK_EX)) {
            rewind($fp); // 🛡️ Asegurar lectura desde el principio
            // Leer todo el contenido sin depender de filesize() cacheado
            $content = stream_get_contents($fp);
            $content = ($content === false || trim($content) === '') ? '{}' : $content;
            $clientes = json_decode($content, true) ?: [];

            // Migración segura
            if (isset($clientes[0])) { $clientes = []; }
            
            // 🧹 Limpieza previa: Eliminar clientes que ya expiraron antes de verificar el nombre
            $ahora = time();
            $limite = 300; // 5 minutos de tolerancia
            foreach ($clientes as $n => $d) {
                $ls = is_array($d) ? ($d['last_seen'] ?? 0) : $d;
                if (($ahora - $ls) > $limite) {
                    unset($clientes[$n]);
                }
            }

            // 🔑 LÓGICA DE ASIGNACIÓN DE LLAVES API ÚNICAS
            $apiKeys = $this->_leer_api_keys();
            $emailsEnUso = array_column($clientes, 'api_email');
            $emailAsignado = '';

            foreach ($apiKeys as $keyData) {
                if (!in_array($keyData['email'], $emailsEnUso)) {
                    $emailAsignado = $keyData['email'];
                    break;
                }
            }

            // 🔄 LÓGICA DE RE-CONEXIÓN: Si el nombre ya existe, permitimos la entrada
            // Esto evita que el usuario quede bloqueado al refrescar la página.
            if (isset($clientes[$nombre])) {
                $clientes[$nombre]['last_seen'] = time();
                $clientes[$nombre]['last_activity'] = time();
                // 🛡️ Resetear estados operativos al reconectar para evitar estados heredados
                $clientes[$nombre]['is_processing'] = false;
                $clientes[$nombre]['is_verifying'] = false;
                // Mantenemos el status y api_email previos
                
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($clientes));
                fflush($fp);
                flock($fp, LOCK_UN);
                fclose($fp);
                echo json_encode(['success' => true, 'reconnected' => true]);
                exit;
            }

            // 🚫 VALIDACIÓN: Verificar disponibilidad de llaves
            if (!$emailAsignado) {
                flock($fp, LOCK_UN);
                fclose($fp);
                if (ob_get_length()) ob_clean();
                echo json_encode(['success' => false, 'message' => 'No hay licencias de IA disponibles. Máximo de conexiones alcanzado.']);
                exit;
            }

            // Inicializamos con todos los campos necesarios para evitar el "reset" a 180
            $clientes[$nombre] = [
                'last_seen'     => time(), 
                'last_activity' => time(), 
                'status'        => 'pending',
                'is_processing' => false,
                'is_verifying'  => false, // 🔍 Inicializar nuevo estado
                'api_email'     => $emailAsignado // 📌 Guardar llave asignada
            ];
            
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($clientes));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);

        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => true]);
        exit;
    }

    public function api_desconectar_cliente()
    {
        session_write_close();
        $nombre = $_POST['nombre'] ?? '';
        
        if (!empty($nombre)) {
            $archivo = 'connected_clients.json';
            clearstatcache(true, $archivo);
            $fp = fopen($archivo, 'c+');
            if (flock($fp, LOCK_EX)) {
                rewind($fp);
                $content = stream_get_contents($fp) ?: '{}';
                $clientes = json_decode($content, true) ?: [];

                if (isset($clientes[$nombre])) {
                    unset($clientes[$nombre]);
                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, json_encode($clientes));
                    fflush($fp);
                }
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // 📊 Gestión de cuotas de IA (18 peticiones, reset 3:00 AM) - Solo para flash-preview
    private function _gestionar_uso_api($email, $model, $incrementar = false, $agotar = false) {
        $model = trim($model);
        if (empty($model) || $model !== 'gemini-3-flash-preview') return null;

        $archivo = 'api_usage.json';
        $limite = 18;
        $uso = 0;

        if (!file_exists($archivo)) file_put_contents($archivo, json_encode([]));

        $fp = fopen($archivo, 'c+');
        if (flock($fp, LOCK_EX)) {
            $content = stream_get_contents($fp);
            $data = json_decode($content, true) ?: [];

            if (!isset($data[$email])) {
                $data[$email] = ['count' => 0, 'last_reset' => 0];
            }

            // 🕒 Lógica de reset a las 3:00 AM
            $ahora = time();
            $hoy_3am = strtotime('today 03:00:00');
            $umbral = ($ahora >= $hoy_3am) ? $hoy_3am : strtotime('yesterday 03:00:00');

            if ($data[$email]['last_reset'] < $umbral) {
                $data[$email]['count'] = 0;
                $data[$email]['last_reset'] = $ahora;
            }

            if ($agotar) {
                $data[$email]['count'] = $limite;
            } elseif ($incrementar) {
                $data[$email]['count']++;
            }
            $uso = $data[$email]['count'];

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);

        return ['restante' => max(0, $limite - $uso), 'limite' => $limite];
    }

    // ✅ Nueva función para que el Admin permita un dispositivo
    public function api_permitir_cliente()
    {
        session_write_close();
        $nombre = $_POST['nombre'] ?? '';
        $archivo = 'connected_clients.json';
        clearstatcache(true, $archivo);
        $success = false;

        $fp = fopen($archivo, 'c+');
        if (flock($fp, LOCK_EX)) {
            rewind($fp);
            $content = stream_get_contents($fp) ?: '{}';
            $clientes = json_decode($content, true) ?: [];
            
            if (isset($clientes[$nombre])) {
                $clientes[$nombre]['status'] = 'allowed';
                $clientes[$nombre]['is_processing'] = false; // 🛡️ Forzar booleano false
                $clientes[$nombre]['last_activity'] = time(); // 🚀 Reiniciar timer al permitir
                
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($clientes));
                fflush($fp);
                $success = true;
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        }
        exit;
    }

    // 💓 NUEVA FUNCIÓN: Recibe el latido del cliente
    public function api_heartbeat()
    {
        session_write_close(); // 🚀 Liberar sesión para no bloquear otros procesos
        header('Content-Type: application/json');
        $nombre = $_POST['nombre'] ?? '';
        // 🛡️ Detección estricta: solo es true si el móvil envía literalmente la cadena "true"
        $is_processing = isset($_POST['processing']) && $_POST['processing'] === 'true';
        $is_verifying = isset($_POST['verifying']) && $_POST['verifying'] === 'true';
        $user_active = isset($_POST['active']) && $_POST['active'] === 'true';
        // Asegurar que si llega vacío use el valor por defecto
        $modeloIA = !empty($_POST['modelo_ia']) ? trim($_POST['modelo_ia']) : 'gemini-3.1-flash-lite-preview';
        
        $apiEmail = 'No asignada';
        $conectado = false;
        $status = 'pending';
        $inactividad = 0;
        $ia_info = null;

        if (!empty($nombre)) {
            $archivo = 'connected_clients.json';
            clearstatcache(true, $archivo);
            $fp = fopen($archivo, 'c+');
            if (flock($fp, LOCK_EX)) {
                rewind($fp);
                $content = stream_get_contents($fp) ?: '{}';
                $clientes = json_decode($content, true) ?: [];

                if (isset($clientes[$nombre])) {
                    $clientes[$nombre]['last_seen'] = time();
                    $clientes[$nombre]['is_processing'] = (bool)$is_processing; // 🛡️ Guardar como booleano real
                    $clientes[$nombre]['is_verifying'] = (bool)$is_verifying;
                    $status = $clientes[$nombre]['status'] ?? 'pending';
                    $apiEmail = $clientes[$nombre]['api_email'] ?? 'No asignada'; // 📌 Obtener llave del cliente

                    // Consultar cuota si el modelo lo requiere
                    $ia_info = $this->_gestionar_uso_api($apiEmail, $modeloIA);

                    // 🚀 Reiniciar el timer si está cargando IA, está en espera, o si el usuario interactuó
                    if ($is_processing || $status === 'pending' || $user_active) { // 🔍 'is_verifying' ya no reinicia el timer de inactividad por sí solo
                        $clientes[$nombre]['last_activity'] = time();
                    }
                    
                    // 🕒 Calcular inactividad acumulada para informar al móvil
                    $ls_fallback = $clientes[$nombre]['last_seen'];
                    $inactividad = time() - ($clientes[$nombre]['last_activity'] ?? $ls_fallback);
                    
                    $conectado = true;

                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, json_encode($clientes));
                    fflush($fp);
                }
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }

        // Devolvemos el estado del server para que el móvil sepa si debe salir
        $estado = file_exists('server_status.txt') ? trim(file_get_contents('server_status.txt')) : '0';
        echo json_encode([
            'estado' => $estado, 
            'conectado' => $conectado, 
            'status' => $status, 
            'inactividad' => $inactividad, 
            'api_email' => $apiEmail,
            'ia_quota' => $ia_info
        ]);
        exit;
    }

    public function api_obtener_clientes()
    {
        header('Content-Type: application/json');
        $archivo = 'connected_clients.json';
        clearstatcache(true, $archivo);
        $clientes = [];
        $ahora = time();
        $limite = 300; // 5 minutos

        // 🔒 Lectura segura con bloqueo
        if (file_exists($archivo)) {
            $fp = fopen($archivo, 'c+');
            if (flock($fp, LOCK_EX)) {
                rewind($fp);
                $content = stream_get_contents($fp) ?: '{}';
                $clientes = json_decode($content, true) ?: [];

                $cambios = false;
                foreach ($clientes as $nombre => $data) {
                    // 🛡️ Limpiar basado en INACTIVIDAD (last_activity) y no solo en conexión
                    $last_seen = is_array($data) ? ($data['last_seen'] ?? 0) : 0;
                    $last_act  = is_array($data) ? ($data['last_activity'] ?? $last_seen) : $last_seen;
                    if (($ahora - $last_act) >= $limite) {
                        unset($clientes[$nombre]);
                        $cambios = true;
                    }
                }

                if ($cambios) {
                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, json_encode($clientes));
                }
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }

        // Devolver nombres y estados
        $lista = [];
        foreach($clientes as $n => $d) {
            // Importante: si no hay last_activity, usamos last_seen en vez de 'ahora' para evitar el reset a 180
            $ls_fallback = is_array($d) ? ($d['last_seen'] ?? $ahora) : $d;
            $last_act = is_array($d) ? ($d['last_activity'] ?? $ls_fallback) : $ls_fallback;
            $is_proc = is_array($d) ? ($d['is_processing'] ?? false) : false;
            $is_veri = is_array($d) ? ($d['is_verifying'] ?? false) : false;
            $lista[] = [
                'nombre' => $n, 
                'status' => is_array($d) ? ($d['status'] ?? 'pending') : 'allowed',
                'inactividad' => $ahora - $last_act, // Tiempo transcurrido (aumentando)
                'processing' => $is_proc,
                'verifying' => $is_veri
            ];
        }
        echo json_encode($lista);
        exit;
    }

    // 🛠️ Función auxiliar para actualizar el estado de procesamiento de un cliente
    private function _actualizar_cliente_state($nombre, $is_processing) {
        $archivo = 'connected_clients.json';
        if (!file_exists($archivo)) return;
        $fp = fopen($archivo, 'c+');
        if (flock($fp, LOCK_EX)) {
            rewind($fp);
            $content = stream_get_contents($fp);
            $clientes = json_decode($content, true) ?: [];
            if (isset($clientes[$nombre])) {
                $clientes[$nombre]['is_processing'] = (bool)$is_processing;
                // 🔥 Mantener al cliente "vivo" y activo mientras la IA trabaja
                $clientes[$nombre]['last_activity'] = time();
                $clientes[$nombre]['last_seen'] = time();
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($clientes));
                fflush($fp);
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    public function api_procesar_imagen()
    {
        session_write_close(); // 🚀 Liberar sesión de inmediato para permitir heartbeats

        if (ob_get_length()) ob_clean(); // 🔹 Limpiar buffer para evitar JSON corrupto
        header('Content-Type: application/json');

        $nombreCliente = $_POST['usuario_envio'] ?? '';
        $rutaExistente = $_POST['image_path'] ?? '';
        // Validación más robusta del modelo recibido
        $modeloIA = (!empty($_POST['modelo_ia'])) ? trim($_POST['modelo_ia']) : 'gemini-3.1-flash-lite-preview';
        $archivoClientes = 'connected_clients.json';
        $clientes = json_decode(file_exists($archivoClientes) ? file_get_contents($archivoClientes) : '{}', true);

        // 1. Verificar estado del servidor
        $estado_server = file_exists('server_status.txt') ? trim(file_get_contents('server_status.txt')) : '0';
        if ($estado_server !== '1') {
            echo json_encode(['error' => 'El sistema está desactivado por el administrador.']);
            exit;
        }
        if (!isset($clientes[$nombreCliente]) || ($clientes[$nombreCliente]['status'] ?? 'pending') !== 'allowed') {
            echo json_encode(['error' => 'No tiene permisos para procesar imágenes. Espere la autorización del administrador.']);
            exit;
        }

        // 🛡️ ACTUALIZAR ESTADO A "PROCESANDO" EN EL SERVIDOR
        // Esto evita que el contador de inactividad suba durante la carga de la imagen
        $this->_actualizar_cliente_state($nombreCliente, true);

        // 🚫 Verificar límite de cuota para el modelo restringido
        $userApiEmail = $clientes[$nombreCliente]['api_email'] ?? '';
        $cuota = $this->_gestionar_uso_api($userApiEmail, $modeloIA);
        if ($cuota && $cuota['restante'] <= 0) {
            $this->_actualizar_cliente_state($nombreCliente, false);
            echo json_encode(['error' => 'Límite de 18 peticiones alcanzado para el modelo gemini-3-flash-preview. Use Gemini Lite o espere a las 3:00 AM.']);
            exit;
        }

        $rutaFinal = '';
        $data = '';

        // 🔹 Si ya se subió en un intento previo, usar esa ruta física
        if (!empty($rutaExistente)) {
            $realPath = realpath($rutaExistente);
            $baseDir = realpath('view/images/actas/');
            if ($realPath && strpos($realPath, $baseDir) === 0 && file_exists($realPath)) {
                $rutaFinal = $rutaExistente;
                $data = file_get_contents($rutaFinal);
            }
        }

        // 🔹 Si no hay ruta previa o la lectura falló, procesar la nueva subida
        if (empty($data)) {
            if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                $this->_actualizar_cliente_state($nombreCliente, false);
                echo json_encode(['error' => 'No se recibió ninguna imagen válida.']);
                exit;
            }

            $path = $_FILES['imagen']['tmp_name'];
            $data = file_get_contents($path);
        
            // 🔹 GUARDAR IMAGEN EN CARPETA TEMPORAL (PENDIENTE DE IDENTIFICAR LIBRO/FOLIO)
            $bloqueDir = 'view/images/actas/temp/';
            if (!file_exists($bloqueDir)) {
                mkdir($bloqueDir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION) ?: 'jpg';
            $nombreArchivo = 'acta_' . time() . '_' . uniqid() . '.' . $extension;
            $rutaFinal = $bloqueDir . $nombreArchivo;
            
            // Guardamos la copia física
            file_put_contents($rutaFinal, $data);
        }

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

        $instruccionesOCR = 'Actúa como un experto en paleografía y sistemas avanzados de reconocimiento óptico de caracteres (OCR). Tu objetivo es realizar una extracción de texto de la imagen proporcionada mediante un análisis exhaustivo carácter por carácter. La imagen contiene una o más actas de Fe de Bautismo.
Antes de comenzar, lee estas instrucciones y aplícalas rigurosamente:
1. REGLAS DE FIDELIDAD Y ESTRUCTURA DE FORMULARIO
•	Detección de Formulario Mixto: El documento contiene texto impreso (fijo) y texto manuscrito (variable). Debes priorizar la extracción de la información manuscrita para llenar las claves del JSON.
•	Transcripción Exacta: Mantén la ortografía, acentuación y abreviaturas exactas del manuscrito original.
•	Valores Nulos: Si una clave no tiene información escrita en el espacio correspondiente o el dato no existe, usa estrictamente null (sin comillas).
•	Validación contextual: Usa el texto impreso como guía para identificar qué dato sigue (ej. después de "Nació en:" busca el lugar de nacimiento).
2. REGLAS DE EXTRACCIÓN ESPECÍFICA (LÓGICA DE NEGOCIO)
•	Fechas: Localiza las fechas de nacimiento y bautizo. Debes interpretarlas y convertirlas al formato estándar DD/MM/YYYY. Si solo aparece el año y mes, completa lo que sea legible.
•	Número de Acta (N°): Identifica el número correlativo del acta. Extrae solo los dígitos, eliminando puntos (".") o comas (",") que puedan aparecer como separadores de miles.
•	Filiación: Clasifica según el contenido del acta en una de estas categorías: "Reconocido", "Legítimo", "Natural", "Ilegítimo" o "No reconocido".
•	Sexo del Bautizado: Determina el sexo analizando el nombre (ej. "María" vs "Juan") o artículos en el texto impreso/manuscrito (ej. "hijo" vs "hija"). Asigna: "Masculino" o "Femenino".
•	Folio N°: Busca el número en la parte superior derecha o izquierda de la página, o donde se indique "Folio".
•	Observaciones (Lógica de consolidación): Esta clave debe incluir información de dos posibles fuentes:
1.	El texto manuscrito que sigue a la palabra impresa "Observación" u "Observaciones".
2.	Cualquier Nota Marginal escrita en el margen izquierdo del acta (usualmente situada debajo del número de registro).
o	Regla de aplicación: Si existen ambas (texto en campo de observación y nota marginal), concaténalas en un solo párrafo. Si no existe la palabra impresa "Observación", utiliza la nota marginal como contenido principal de esta clave.
•	Registro Civil: Extrae anotaciones sobre Tomo, Acta y Año de la inscripción civil si están presentes.
3. FORMATO DE SALIDA (JSON ÚNICAMENTE)
Genera un arreglo de objetos JSON con la siguiente estructura exacta. No añadas comentarios ni texto fuera del bloque de código.
JSON
[
  {
    "Nombre del Bautizado": "",
    "Apellido del Bautizado": "",
    "Sexo del Bautizado": "",
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
    "Registro Civil": ""
  }
]
•	Ministro: si el nombre de esta clave se parece aunque sea un poco con uno 
de esta lista usa el nombre de la lista en lugar del que estragiste: (' . $ministrosString . ').';

        // 4. Obtener la API Key específicamente asignada a este usuario
        $apiKeys = $this->_leer_api_keys();
        $apiKey = $apiKeys[$userApiEmail]['key'] ?? '';

        if (empty($apiKey)) { echo json_encode(['error' => 'No hay llaves API disponibles.']); exit; }

        // 🐍 LLAMADA AL MICROSERVICIO PYTHON (Usamos 127.0.0.1 para evitar fallos de resolución de DNS local)
        $url = "http://127.0.0.1:5000/process";
        $payload = [
            "client_id" => $nombreCliente,
            "api_key"   => $apiKey,
            "prompt"    => $instruccionesOCR,
            "image"     => $base64,
            "model"     => $modeloIA
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_errno($ch) ? curl_error($ch) : '';
        curl_close($ch);

        if ($curlError) {
            $msg = (strpos($curlError, '5000') !== false) 
                ? "Error: El microservicio SACRE-IA no está respondiendo en el puerto 5000."
                : "Error de comunicación con la IA: $curlError";
            echo json_encode(['error' => $msg]);
            exit;
        }

        $jsonRes = json_decode($response, true);

        // Si el código HTTP es 503, lo reportamos explícitamente para que el móvil reintente
        if ($httpCode === 503) {
            http_response_code(503); // Informamos al navegador que es un error 503
            // No quitamos el flag de procesamiento aquí porque el cliente reintentará en breve
            echo json_encode([
                'error' => 'Servidor de IA sobrecargado. Iniciando reintento automático.',
                'image_path' => $rutaFinal // 👈 Devolvemos la ruta para el reintento
            ]);
            exit;
        }

        // 🚫 MANEJO DEL ERROR 429: Cuota de Google excedida
        if ($httpCode === 429 && $modeloIA === 'gemini-3-flash-preview') {
            $this->_gestionar_uso_api($userApiEmail, $modeloIA, false, true); // Marcar como agotado
            $this->_actualizar_cliente_state($nombreCliente, false);
            echo json_encode(['error' => 'Gemini reportó que la cuota de esta llave se ha agotado (Error 429). Se han bloqueado los 18 intentos del modelo por hoy. Use Gemini Lite o espere a las 3:00 AM.']);
            exit;
        }

        if (!$jsonRes || (isset($jsonRes['status']) && $jsonRes['status'] === 'error')) {
            $this->_actualizar_cliente_state($nombreCliente, false);
            $msg = $jsonRes['message'] ?? 'Error de conexión con el servicio Python.';
            echo json_encode(['error' => $msg]);
            exit;
        }

        // Adaptar la respuesta de Python para que sea compatible con el frontend móvil
        $geminiData = [
            'candidates' => [['content' => ['parts' => [['text' => $jsonRes['response']]]]]]
        ];

        // 🏁 RESTAURAR ESTADO AL FINALIZAR CON ÉXITO
        $this->_actualizar_cliente_state($nombreCliente, false);

        // 📈 Incrementar contador de uso solo si es el modelo Pro
        $this->_gestionar_uso_api($userApiEmail, $modeloIA, true);

        // Éxito: Enviar respuesta de Gemini y la ruta de la imagen guardada
        echo json_encode([
            'gemini_data' => $geminiData,
            'image_path' => $rutaFinal
        ]);
        exit;
    }

    public function validar_clave_exportacion()
    {
        header('Content-Type: application/json');
        if (session_status() == PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['IdUsu'])) {
            echo json_encode(['success' => false, 'msg' => 'Sesión expirada.']);
            exit;
        }

        $pass = $_POST['password'] ?? '';
        $usuario = $_SESSION['Usuario'];

        // Verificar usuario y contraseña usando el modelo existente
        $user = $this->model->verificar_usuario($usuario, $pass);

        echo json_encode(['success' => (bool)$user]);
        exit;
    }

    public function descargar_reporte_bautizos_completo()
    {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        // Verificar sesión
        if (!isset($_SESSION['IdUsu'])) {
            header("Location: index.php");
            exit;
        }

        $filename = "Reporte_Bautizos_Completo_" . date('Ymd_His') . ".xls";
        
        // Headers para forzar descarga como Excel
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $datos = $this->model->obtener_bautizos_completo();

        // Generar tabla HTML (Excel la interpreta)
        echo "<meta charset='UTF-8'>";
        echo "<table border='1'>";
        echo "<thead>
                <tr style='background-color: #f2f2f2;'>
                    <th>N°</th>
                    <th>Libro</th>
                    <th>Folio</th>
                    <th>Fecha Bautizo</th>
                    <th>Lugar Bautizo</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Sexo</th>
                    <th>Fecha Nac.</th>
                    <th>Lugar Nac.</th>
                    <th>Filiación</th>
                    <th>Padres</th>
                    <th>Padrinos</th>
                    <th>Ministro</th>
                    <th>Registro Civil</th>
                    <th>Observaciones</th>
                    <th>Digitalizado Por</th>
                    <th>Fecha Digitalización</th>
                </tr>
              </thead>";
        echo "<tbody>";

        if ($datos) {
            while ($row = $datos->fetch_assoc()) {
                $sexo = ($row['SexInd'] == 1) ? 'Masculino' : 'Femenino';
                $filiaciones = ['0'=>'No reconocido', '1'=>'Reconocido', '2'=>'Legítimo', '3'=>'Natural', '4'=>'Ilegítimo'];
                $fil = $filiaciones[$row['FilInd']] ?? $row['FilInd'];
                
                $fecCel = ($row['FechCel'] && $row['FechCel'] !== '0000-00-00') ? date('d/m/Y', strtotime($row['FechCel'])) : '';
                $fecNac = ($row['FecNacInd'] && $row['FecNacInd'] !== '0000-00-00') ? date('d/m/Y', strtotime($row['FecNacInd'])) : '';
                $fecDig = (!empty($row['FechaRegistro'])) ? date('d/m/Y H:i:s', strtotime($row['FechaRegistro'])) : '';

                echo "<tr>";
                echo "<td>" . $row['IdCel'] . "</td>";
                echo "<td>" . $row['NumLib'] . "</td>";
                echo "<td>" . $row['NumFol'] . "</td>";
                echo "<td>" . $fecCel . "</td>";
                echo "<td>" . $row['LugarBautizo'] . "</td>";
                echo "<td>" . $row['NomInd'] . "</td>";
                echo "<td>" . $row['ApeInd'] . "</td>";
                echo "<td>" . $sexo . "</td>";
                echo "<td>" . $fecNac . "</td>";
                echo "<td>" . $row['LugNacInd'] . "</td>";
                echo "<td>" . $fil . "</td>";
                echo "<td>" . $row['Padres'] . "</td>";
                echo "<td>" . $row['Padrinos'] . "</td>";
                echo "<td>" . $row['MinNom'] . ' ' . $row['MinApe'] . "</td>";
                echo "<td>" . $row['RegCiv'] . "</td>";
                echo "<td>" . $row['NotMar'] . "</td>";
                echo "<td>" . ($row['NombreDigitalizador'] ?? '') . "</td>";
                echo "<td>" . $fecDig . "</td>";
                echo "</tr>";
            }
        }
        echo "</tbody></table>";
        exit;
    }

    /* ============================================================
       📥 RECEPCIÓN TEMPORAL DE BAUTIZOS (DESDE MÓVIL)
       ============================================================ */

    public function api_enviar_bautizos_temporal()
    {
        session_write_close(); // 🚀 Liberar sesión para evitar bloqueos
        if (ob_get_length()) ob_clean(); // 🧹 Limpiar buffer para evitar JSON corrupto
        header('Content-Type: application/json; charset=utf-8');
        
        // Debug: Log received data
        file_put_contents('debug_api_enviar.txt', date('Y-m-d H:i:s') . ' - POST: ' . json_encode($_POST) . "\n", FILE_APPEND);
        
        $datos = $_POST;
        $datos['timestamp'] = time();
        
        // 📂 LÓGICA DE ALMACENAMIENTO ESTRUCTURADO (LIBRO/FOLIOS EN BLOQUES DE 30)
        $rutaImagen = $datos['RutaImagen'] ?? '';
        $libro = (int)($datos['NumLib'] ?? 0);
        $folio = (int)($datos['NumFol'] ?? 0);

        if (!empty($rutaImagen) && $libro > 0 && $folio > 0) {
            // 1. Calcular rango de folios para la subcarpeta
            $inicio = (floor(($folio - 1) / 30) * 30) + 1;
            $fin = $inicio + 29;
            $targetDir = "view/images/actas/Libro_$libro/Folios_$inicio-$fin/";
            
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $nombreArchivo = basename($rutaImagen);
            $nuevaRuta = $targetDir . $nombreArchivo;

            // 2. Mover archivo de temp a su ubicación final organizada
            if (file_exists($rutaImagen)) {
                rename($rutaImagen, $nuevaRuta);
            }
            // Actualizar la ruta en el registro para el JSON de pendientes
            $datos['RutaImagen'] = $nuevaRuta;
        }

        $archivo = 'pending_bautizos.json';
        $pendientes = [];
        
        if (file_exists($archivo)) {
            $content = file_get_contents($archivo);
            $pendientes = json_decode($content, true) ?? [];
        }

        $pendientes[] = $datos;

        if (file_put_contents($archivo, json_encode($pendientes))) {
            echo json_encode(['status' => 'ok', 'msg' => 'Enviado al servidor para revisión.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar en el servidor.']);
        }
        exit;
    }

    public function api_obtener_bautizos_temporales()
    {
        header('Content-Type: application/json');
        $archivo = 'pending_bautizos.json';
        if (file_exists($archivo)) {
            echo file_get_contents($archivo);
        } else {
            echo json_encode([]);
        }
        exit;
    }

    public function api_eliminar_bautizo_temporal()
    {
        header('Content-Type: application/json');
        $index = $_POST['index'] ?? null;
        
        if ($index === null) {
            echo json_encode(['status' => 'error', 'msg' => 'Índice no proporcionado.']);
            exit;
        }

        $archivo = 'pending_bautizos.json';
        if (!file_exists($archivo)) {
            echo json_encode(['status' => 'error', 'msg' => 'No hay datos.']);
            exit;
        }

        $pendientes = json_decode(file_get_contents($archivo), true) ?? [];
        
        if (!isset($pendientes[$index])) {
            echo json_encode(['status' => 'error', 'msg' => 'Registro no encontrado.']);
            exit;
        }

        $registro = $pendientes[$index];
        $rutaImagen = $registro['RutaImagen'] ?? '';
        $eliminados = 0;

        if (!empty($rutaImagen)) {
            // 1. Eliminar archivo de imagen físico si existe
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }

            // 2. Eliminar TODOS los registros que compartan esa misma imagen
            $nuevosPendientes = [];
            foreach ($pendientes as $p) {
                if (isset($p['RutaImagen']) && $p['RutaImagen'] === $rutaImagen) {
                    $eliminados++; // No lo agregamos al nuevo array (se elimina)
                } else {
                    $nuevosPendientes[] = $p; // Se conserva
                }
            }
            $pendientes = $nuevosPendientes;
        } else {
            // Si no tiene imagen (registro manual puro), eliminar solo ese índice
            array_splice($pendientes, $index, 1);
            $eliminados = 1;
        }
        
        file_put_contents($archivo, json_encode($pendientes));
        echo json_encode(['status' => 'ok', 'msg' => "Se eliminaron $eliminados registros y la imagen asociada."]);
        exit;
    }

    public function api_editar_bautizo_temporal()
    {
        header('Content-Type: application/json');
        $index = $_POST['index'] ?? null;
        $archivo = 'pending_bautizos.json';
        $pendientes = json_decode(file_get_contents($archivo), true) ?? [];

        if ($index !== null && isset($pendientes[$index])) {
            $nuevosDatos = $_POST;
            unset($nuevosDatos['index']); // No guardar el índice en el JSON
            // Fusionar datos nuevos con los existentes (para mantener timestamp, usuario, etc.)
            $pendientes[$index] = array_merge($pendientes[$index], $nuevosDatos);
            file_put_contents($archivo, json_encode($pendientes));
            echo json_encode(['status' => 'ok', 'msg' => 'Registro actualizado.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar.']);
        }
        exit;
    }

    // 🔹 Función auxiliar para procesar un array de datos y guardarlo en BD
    private function _procesar_datos_bautizo($data) {
        
        $individuo = [
            'NomInd'    => $data['NomInd'] ?? '',
            'ApeInd'    => $data['ApeInd'] ?? '',
            'SexInd'    => $data['SexInd'] ?? '',
            'FecNacInd' => $data['FecNacInd'] ?? '',
            'LugNacInd' => $data['LugNacInd'] ?? '',
            'FilInd'    => $data['FilInd'] ?? '',
            'IdUsu'     => $_SESSION['IdUsu'] ?? 1
        ];

        $madre = ['Nom' => $data['NomMad'] ?? '', 'Ape' => $data['ApeMad'] ?? '', 'Sex' => 2];
        
        $padre = null;
        if (!empty($data['NomPad']) || !empty($data['ApePad'])) {
            $padre = ['Nom' => $data['NomPad'], 'Ape' => $data['ApePad'], 'Sex' => 1];
        }

        $padrinos = [
            ['Nom' => $data['Pad1Nom'], 'Ape' => $data['Pad1Ape'], 'Sex' => $data['Pad1Sex']],
            ['Nom' => $data['Pad2Nom'], 'Ape' => $data['Pad2Ape'], 'Sex' => $data['Pad2Sex']]
        ];

        $celebracion = [
            'IdCel'   => $data['IdCel'],
            'FechCel' => $data['FechCel'],
            'TipCel'  => $data['TipCel'] ?? 1,
            'NumLib'  => $data['NumLib'],
            'NumFol'  => $data['NumFol'],
            'IdMin'   => $data['IdMin'],
            'Lugar'   => $data['Lugar']
        ];

        $enlace = [
            'RegCiv'    => $data['RegCiv'] ?? '',
            'NotMar'    => $data['NotMar'] ?? '',
            'TipCelPad' => $celebracion['TipCel'],
            'EstCel'    => $data['EstCel'] ?? 1
        ];

        // Datos de la imagen
        $datosImagen = null;
        if (!empty($data['RutaImagen'])) {
            $datosImagen = [
                'UrlArchivo' => $data['RutaImagen'],
                'NombreDigitalizador' => $data['usuario_envio'] ?? 'Desconocido'
            ];
        }

        return $this->model->registrar_bautizo_completo(
            $individuo, $madre, $padre, $padrinos, $celebracion, $enlace, $datosImagen
        );
    }

    public function api_aprobar_bautizo_temporal()
    {
        header('Content-Type: application/json');
        $index = $_POST['index'] ?? null;
        $archivo = 'pending_bautizos.json';
        
        if ($index === null || !file_exists($archivo)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos no válidos.']);
            exit;
        }

        $pendientes = json_decode(file_get_contents($archivo), true) ?? [];
        if (!isset($pendientes[$index])) {
            echo json_encode(['status' => 'error', 'msg' => 'Registro no encontrado.']);
            exit;
        }

        try {
            $res = $this->_procesar_datos_bautizo($pendientes[$index]);
            if ($res['status'] === 'ok') {
                // Eliminar del JSON si se guardó en BD
                array_splice($pendientes, $index, 1);
                file_put_contents($archivo, json_encode($pendientes));
            }
            echo json_encode($res);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
        exit;
    }

    public function api_aprobar_grupo_temporal()
    {
        header('Content-Type: application/json');
        $ruta = $_POST['ruta'] ?? '';
        $archivo = 'pending_bautizos.json';

        if (empty($ruta) || !file_exists($archivo)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos no válidos o archivo inexistente.']);
            exit;
        }

        $pendientes = json_decode(file_get_contents($archivo), true) ?? [];
        $guardados = 0;
        $errores = 0;
        $nuevosPendientes = [];

        foreach ($pendientes as $p) {
            // Si coincide la ruta de imagen, intentamos guardarlo en BD
            if (isset($p['RutaImagen']) && $p['RutaImagen'] === $ruta) {
                try {
                    $res = $this->_procesar_datos_bautizo($p);
                    if ($res['status'] === 'ok') {
                        $guardados++;
                    } else {
                        $nuevosPendientes[] = $p; // Mantener si hay error
                        $errores++;
                    }
                } catch (Exception $e) {
                    $nuevosPendientes[] = $p;
                    $errores++;
                }
            } else {
                $nuevosPendientes[] = $p; // Mantener registros de otros grupos
            }
        }

        file_put_contents($archivo, json_encode($nuevosPendientes));
        echo json_encode(['status' => 'ok', 'msg' => "Se guardaron $guardados registros correctamente. Errores: $errores"]);
        exit;
    }

    public function api_borrar_imagen_cancelada()
    {
        header('Content-Type: application/json');
        $ruta = $_POST['ruta'] ?? '';

        if (empty($ruta)) {
            echo json_encode(['status' => 'error', 'msg' => 'No se proporcionó la ruta.']);
            exit;
        }

        // 🛡️ Seguridad: Solo permitir borrar si está dentro de la carpeta de actas
        $realPath = realpath($ruta);
        $baseDir = realpath('view/images/actas/');

        if ($realPath && strpos($realPath, $baseDir) === 0 && file_exists($realPath)) {
            if (unlink($realPath)) {
                echo json_encode(['status' => 'ok', 'msg' => 'Imagen borrada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'No se pudo borrar el archivo físico.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Ruta no válida o el archivo no existe.']);
        }
        exit;
    }

    public function api_aprobar_todos_bautizos_temporales()
    {
        header('Content-Type: application/json');
        $archivo = 'pending_bautizos.json';
        $pendientes = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];
        $guardados = 0;
        $errores = 0;
        $nuevosPendientes = [];

        foreach ($pendientes as $p) {
            try {
                $res = $this->_procesar_datos_bautizo($p);
                if ($res['status'] === 'ok') {
                    $guardados++;
                } else {
                    $nuevosPendientes[] = $p; // Mantener si falló (ej. ID duplicado)
                    $errores++;
                }
            } catch (Exception $e) {
                $nuevosPendientes[] = $p;
                $errores++;
            }
        }

        file_put_contents($archivo, json_encode($nuevosPendientes));
        echo json_encode(['status' => 'ok', 'guardados' => $guardados, 'errores' => $errores]);
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
            if (ob_get_length()) ob_clean(); // 🧹 Limpiar cualquier salida accidental previa
            header('Content-Type: text/plain');

            $usuario = $_POST['usuario'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';

            $resultado = $this->model->verificar_usuario($usuario, $contrasena);

            if ($resultado) {
                // 🧹 Limpiar carpeta temporal de imágenes al iniciar sesión
                $this->_limpiar_carpeta_temporal();

                // 🔒 Resetear servidor por seguridad al loguearse
                @file_put_contents('server_status.txt', '0');

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

    /* 🧹 Función privada para limpiar archivos huérfanos en la carpeta temporal */
    private function _limpiar_carpeta_temporal()
    {
        $tempDir = 'view/images/actas/temp/';
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
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
                'NomInd'    => $_POST['NomInd'] ?? '',
                'ApeInd'    => $_POST['ApeInd'] ?? '',
                'SexInd'    => $_POST['SexInd'] ?? '',
                'FecNacInd' => $_POST['FecNacInd'] ?? '',
                'LugNacInd' => $_POST['LugNacInd'] ?? '',
                'FilInd'    => $_POST['FilInd'] ?? '',
                'IdUsu'     => $_SESSION['IdUsu'] ?? 1
            ];

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
                'TipCelPad' => $celebracion['TipCel'],
                'EstCel'    => $_POST['EstCel'] ?? 1
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

    public function api_obtener_ministros() 
    {
        header('Content-Type: application/json');
        $res = $this->model->obtener_todos("ministro_celebrante");
        $ministros = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $ministros[] = $row;
            }
        }
        echo json_encode($ministros);
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
       💾 RESPALDO Y COPIAS DE SEGURIDAD
       ============================================================ */

    public function descargar_backup_imagenes()
    {
        // Verificar sesión y permisos (solo admin)
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['RolUsu']) || ($_SESSION['RolUsu'] != 10 && $_SESSION['RolUsu'] != 100)) {
            die("Acceso denegado.");
        }

        // 🛡️ Validación: Verificar si la extensión ZIP está habilitada
        if (!class_exists('ZipArchive')) {
            die("Error crítico: La extensión <b>php_zip</b> no está habilitada en el servidor. <br>Por favor, edite el archivo <i>php.ini</i>, descomente la línea <code>extension=zip</code> y reinicie Apache.");
        }

        // Ruta de la carpeta de imágenes (relativa al index.php)
        $source = 'view/images/actas';
        
        if (!file_exists($source)) {
            die("La carpeta de imágenes no existe o está vacía.");
        }

        // 🔹 Obtener nombre personalizado o usar default
        $customName = isset($_GET['name']) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $_GET['name']) : 'respaldo_sacrej';
        $zipName = $customName . '_' . date('Ymd_His') . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipName;
        
        // 1. Generar SQL temporal
        $sqlName = 'base_datos.sql'; // Nombre fijo dentro del ZIP para facilitar la restauración
        $sqlPath = sys_get_temp_dir() . '/' . $sqlName;
        $this->model->generar_backup_sql($sqlPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // 2. Agregar SQL al ZIP
            if (file_exists($sqlPath)) {
                $zip->addFile($sqlPath, 'base_datos/' . $sqlName);
            }

            // 3. Agregar Imágenes al ZIP
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    // Ruta relativa dentro del ZIP (carpeta imagenes/)
                    $relativePath = 'imagenes/' . substr($filePath, strlen(realpath($source)) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            
            // Borrar SQL temporal
            if (file_exists($sqlPath)) unlink($sqlPath);
        } else {
            die("Error al crear el archivo ZIP. Verifique que la extensión ZipArchive esté habilitada en PHP.");
        }

        // Forzar descarga
        if (file_exists($zipPath)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($zipPath));
            header('Pragma: no-cache');
            readfile($zipPath);
            unlink($zipPath); // Borrar temporal
            exit;
        } else {
            die("Error al generar la descarga.");
        }
    }

    public function api_obtener_tamano_respaldo()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        // 1. Calcular tamaño de imágenes
        $source = 'view/images/actas';
        $size = 0;
        if (file_exists($source)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
        }

        // 2. Calcular tamaño BD
        $sqlSize = $this->model->obtener_tamano_bd();
        
        $total = $size + $sqlSize;
        
        // Formatear a MB/GB
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $total > 0 ? floor(log($total, 1024)) : 0;
        $formatted = number_format($total / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];

        echo json_encode(['status' => 'ok', 'size' => $formatted]);
        exit;
    }

    public function api_generar_respaldo_local()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        // Validar permisos (Admin)
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['RolUsu']) || ($_SESSION['RolUsu'] != 10 && $_SESSION['RolUsu'] != 100)) {
            echo json_encode(['status' => 'error', 'msg' => 'Acceso denegado.']);
            exit;
        }

        $nombre = $_POST['nombre'] ?? 'respaldo';
        $rutaDestino = $_POST['ruta'] ?? '';

        // Normalizar ruta (Windows usa \, pero PHP maneja / bien)
        $rutaDestino = rtrim(str_replace('\\', '/', $rutaDestino), '/');

        // Validar o crear carpeta
        if (!is_dir($rutaDestino)) {
            if (!mkdir($rutaDestino, 0777, true)) {
                echo json_encode(['status' => 'error', 'msg' => "La ruta '$rutaDestino' no existe y no se pudo crear."]);
                exit;
            }
        }

        // Generar ZIP en temporal primero
        $zipName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre) . '.zip';
        $zipPathTemp = sys_get_temp_dir() . '/' . $zipName;
        $source = 'view/images/actas';

        // 1. Generar SQL
        $sqlName = 'base_datos.sql';
        $sqlPath = sys_get_temp_dir() . '/' . $sqlName;
        $this->model->generar_backup_sql($sqlPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPathTemp, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            if (file_exists($sqlPath)) $zip->addFile($sqlPath, 'base_datos/' . $sqlName);
            
            if (file_exists($source)) {
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'imagenes/' . substr($filePath, strlen(realpath($source)) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
            $zip->close();
            if (file_exists($sqlPath)) unlink($sqlPath);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Error al crear el ZIP.']);
            exit;
        }

        // Mover a destino final
        $destinoFinal = $rutaDestino . '/' . $zipName;
        if (copy($zipPathTemp, $destinoFinal)) {
            unlink($zipPathTemp);
            echo json_encode(['status' => 'ok', 'msg' => "Respaldo guardado exitosamente en:<br><b>$destinoFinal</b>"]);
        } else {
            unlink($zipPathTemp);
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar el archivo en la ruta destino. Verifique permisos.']);
        }
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
       ✏️ EDICIÓN DE BAUTIZOS
       ============================================================ */

    public function api_obtener_bautizo_edicion()
    {
        error_reporting(0); // 🛡️ Evitar que warnings de PHP rompan el JSON
        if (ob_get_length()) ob_clean(); // Limpiar buffer para evitar basura en el JSON
        header('Content-Type: application/json');
        
        try {
            $idCel = $_POST['idCel'] ?? 0;
            if (!$idCel) {
                throw new Exception('ID no válido');
            }
            $data = $this->model->obtener_datos_bautizo_por_id($idCel);
            
            echo json_encode($data ? ['status' => 'ok', 'data' => $data] : ['status' => 'error', 'msg' => 'Registro no encontrado']);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error del servidor: ' . $e->getMessage()]);
        }
        exit;
    }

    public function api_guardar_edicion_bautizo()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $res = $this->model->actualizar_bautizo($_POST);
                echo json_encode($res);
            }
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error: ' . $e->getMessage()]);
        }
        exit;
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
                // Formatear fechas para evitar mostrar '0000-00-00'
                if (!empty($detalle['fec_nac']) && $detalle['fec_nac'] !== '0000-00-00') {
                    $detalle['fec_nac'] = date('d/m/Y', strtotime($detalle['fec_nac']));
                } else {
                    $detalle['fec_nac'] = '';
                }

                if (!empty($detalle['fecha_bautizo']) && $detalle['fecha_bautizo'] !== '0000-00-00') {
                    $detalle['fecha_bautizo'] = date('d/m/Y', strtotime($detalle['fecha_bautizo']));
                } else {
                    $detalle['fecha_bautizo'] = '';
                }

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


public function generar_constancia_no_asentamiento()
{
    require "view/libs/fpdf/fpdf.php";

    // Evitar errores de salida previa
    if (ob_get_length()) {
        ob_clean();
    }

    // =========================
    // 📌 DATOS
    // =========================
    $nombre = $_POST['nombre'] ?? '';
    $idUsu  = $_POST['idUsu'] ?? null;

    // Limpiar + convertir a MAYÚSCULAS (UTF-8)
    $nombre = mb_strtoupper(trim($nombre), 'UTF-8');

    if (empty($nombre)) {
        echo "Datos incompletos";
        exit;
    }

    // =========================
    // 👤 FIRMANTE
    // =========================
    $firmante = $this->model->obtener_usuario_por_id($idUsu);

    $nombreFirmante = "PBRO. ROGER A. CACERES C.";
    if ($firmante) {
        $nombreFirmante = mb_strtoupper(
            trim($firmante['NomUsu'] . " " . $firmante['ApeUsu']),
            'UTF-8'
        );
    }

    // =========================
    // 📅 FECHA
    // =========================
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];

    $tHoy = time();
    $dHoy = date('d', $tHoy);
    $mHoy = (int) date('n', $tHoy);
    $aHoy = date('Y', $tHoy);

    $fechaTexto = "$dHoy días del mes de " . ucfirst($meses[$mHoy]) . " del $aHoy";

    // =========================
    // 📄 PDF
    // =========================
    $pdf = new FPDF('P', 'mm', 'Letter');
    $pdf->SetMargins(20, 15, 20);
    $pdf->AddPage();

    // 🖼️ Logo
    $pdf->Image('view/images/logo_parroquia.jpg', 18, 15, 25);

    // =========================
    // 🏛️ ENCABEZADO
    // =========================
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 5, utf8_decode('DIOCESIS DE SAN CRISTOBAL'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('VICARIA LA ENCARNACION DEL SEÑOR'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('PARROQUIA ECLESIASTICA "SAGRADO CORAZON DE JESUS"'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('LA FRIA - ESTADO TACHIRA'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('VENEZUELA'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('(0277-5411823)'), 0, 1, 'C');

    $pdf->Ln(20);

    // =========================
    // 🧾 TÍTULO
    // =========================
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, utf8_decode('CONSTANCIA DE NO ASENTAMIENTO PARROQUIAL'), 0, 1, 'C');

    // Línea decorativa
    $pdf->Cell(0, 0, '', 'T', 1, 'C');
    $pdf->Ln(40);

    // =========================
    // 📜 CUERPO
    // =========================
    $pdf->SetFont('Arial', '', 12);

    $texto = "Quien suscribe el Pbro. $nombreFirmante, Párroco del SAGRADO CORAZÓN DE JESÚS de La Fría, Municipio García de Hevia del Estado Táchira, CERTIFICA que previa búsqueda minuciosa en los libros parroquiales no se encontró asentada en ningún libro la Fe de Bautismo de la persona: $nombre.";

    $pdf->MultiCell(0, 6, utf8_decode($texto), 0, 'J');

    $pdf->Ln(20);

    $pdf->MultiCell(
        0,
        6,
        utf8_decode("Constancia que se expide a solicitud del interesado a los $fechaTexto."),
        0,
        'J'
    );

    $pdf->Ln(40);

    // =========================
    // ✍️ FIRMA
    // =========================
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 5, utf8_decode($nombreFirmante), 0, 1, 'C');

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 5, utf8_decode('PARROCO'), 0, 1, 'C');

    // =========================
    // 📤 SALIDA
    // =========================
    $pdf->Output("I", "constancia_no_asentamiento.pdf");
    exit;
}










}
?>
