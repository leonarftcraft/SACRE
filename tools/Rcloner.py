import os
import requests
import zipfile
import io
import sys
import subprocess
from flask import Flask, jsonify, request

# Forzar codificación UTF-8 para evitar errores en Windows
if sys.stdout.encoding != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

app = Flask(__name__)

# Configuración de rutas
INSTALL_PATH = "C:\\rclone"
RCLONE_EXE = os.path.join(INSTALL_PATH, "rclone.exe")
RCLONE_CONFIG = os.path.join(INSTALL_PATH, "rclone.conf")

@app.route('/status', methods=['GET'])
def rclone_status():
    """Verifica si rclone.exe existe en la ruta predeterminada."""
    exists = os.path.exists(RCLONE_EXE)
    return jsonify({
        "status": "ok", 
        "installed": exists, 
        "path": INSTALL_PATH if exists else "",
        "config": RCLONE_CONFIG
    })

@app.route('/install', methods=['POST'])
def install_rclone_api():
    """Maneja la descarga e instalación de Rclone."""
    try:
        if os.path.exists(RCLONE_EXE):
            return jsonify({"status": "ok", "message": "Rclone ya está instalado en el sistema."})

        if not os.path.exists(INSTALL_PATH):
            os.makedirs(INSTALL_PATH)

        print("Descargando rclone...")
        url = "https://downloads.rclone.org/rclone-current-windows-amd64.zip"
        r = requests.get(url, timeout=120)
        r.raise_for_status()
        
        z = zipfile.ZipFile(io.BytesIO(r.content))
        
        # Extraer el contenido
        for member in z.namelist():
            filename = os.path.basename(member)
            if not filename: continue # Ignorar directorios
            
            source = z.open(member)
            target_file_path = os.path.join(INSTALL_PATH, filename)
            with source, open(target_file_path, "wb") as target:
                target.write(source.read())
        
        return jsonify({"status": "ok", "message": f"Rclone instalado correctamente en {INSTALL_PATH}"})
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/create_remote', methods=['POST'])
def create_remote():
    """Crea la configuración del remote en rclone.conf."""
    try:
        data = request.json
        folder_id = data.get('folder_id')
        json_path = data.get('json_path')
        remote_name = data.get('remote_name', 'Mi_nube_1')

        if not os.path.exists(RCLONE_EXE):
            return jsonify({"status": "error", "message": "Rclone no está instalado en C:\\rclone"}), 400

        cmd_config = [
            RCLONE_EXE, "--config", RCLONE_CONFIG, "config", "create", remote_name, "drive",
            "service_account_file", json_path,
            "root_folder_id", folder_id,
            "scope", "drive",
            "--non-interactive"
        ]
        print(f"Ejecutando: {' '.join(cmd_config)}")
        subprocess.run(cmd_config, check=True, capture_output=True)

        return jsonify({
            "status": "ok", 
            "msg": f"Remote '{remote_name}' creado exitosamente en {RCLONE_CONFIG}."
        })
    except subprocess.CalledProcessError as e:
        error_msg = e.stderr.decode('utf-8', errors='ignore') if e.stderr else str(e)
        return jsonify({"status": "error", "message": f"Error de Rclone: {error_msg}"}), 500
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/test_upload', methods=['POST'])
def test_upload():
    """Realiza la prueba de subida con el archivo específico."""
    try:
        data = request.json
        remote_name = data.get('remote_name', 'Mi_nube_1')
        
        # 2. Intentar subir estrictamente el archivo sugerido por el usuario
        file_to_upload = r"C:\Users\USUARIO\Documents\eureka2.txt"

        if not os.path.exists(file_to_upload):
            return jsonify({
                "status": "error", 
                "message": f"No se encontró el archivo de prueba en la ruta: {file_to_upload}"
            }), 404

        if not os.access(file_to_upload, os.R_OK):
            return jsonify({
                "status": "error", 
                "message": f"Sin permisos de lectura para: {file_to_upload}"
            }), 403
        
        cmd_verify = [
            RCLONE_EXE, "--config", RCLONE_CONFIG, 
            "copy", file_to_upload, f"{remote_name}:"
        ]
        
        print(f"Probando subida con: {file_to_upload}")
        subprocess.run(cmd_verify, check=True, capture_output=True)

        return jsonify({
            "status": "ok", 
            "msg": f"Subida exitosa de {os.path.basename(file_to_upload)} a {remote_name}."
        })
    except subprocess.CalledProcessError as e:
        error_msg = e.stderr.decode('utf-8', errors='ignore') if e.stderr else str(e)
        return jsonify({"status": "error", "message": f"Error de Rclone: {error_msg}"}), 500
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

if __name__ == '__main__':
    print(f"Microservicio Rcloner activo en puerto 5001...")
    app.run(host='0.0.0.0', port=5001)