import os
import requests
import zipfile
import io
import sys
import subprocess
import py7zr
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
            RCLONE_EXE, 
            "copy", file_to_upload, f"{remote_name}:", "-P"
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

@app.route('/create_7z', methods=['POST'])
def create_7z():
    """Genera un archivo comprimido 7z usando py7zr."""
    try:
        data = request.json
        sources = data.get('sources', [])
        output_path = data.get('output_path')

        if not output_path:
            return jsonify({"status": "error", "message": "Ruta de salida no definida."}), 400

        with py7zr.SevenZipFile(output_path, 'w') as archive:
            for source in sources:
                if os.path.exists(source):
                    if os.path.isdir(source):
                        archive.writeall(source, os.path.basename(source))
                    else:
                        archive.write(source, os.path.basename(source))
        
        return jsonify({"status": "ok", "message": "Respaldo 7z generado exitosamente."})
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/split_and_upload', methods=['POST'])
def split_and_upload():
    """Divide el archivo 7z en partes iguales y sube cada parte a remotes de Google Drive."""
    try:
        data = request.json
        file_path = data.get('file_path')

        if not file_path or not os.path.exists(file_path):
            return jsonify({"status": "error", "message": "Archivo no encontrado."}), 400

        remote_names = data.get('remote_names', [])
        if not remote_names:
            return jsonify({"status": "error", "message": "No se recibieron nombres de remotes para subir."}), 400

        # Verificar que los remotes realmente funcionan antes de subir
        valid_remotes = []
        invalid_remotes = []
        for remote in remote_names:
            try:
                cmd_check = [
                    RCLONE_EXE,
                    "about", f"{remote}:"
                ]
                subprocess.run(cmd_check, check=True, capture_output=True)
                valid_remotes.append(remote)
            except subprocess.CalledProcessError as e:
                error_msg = e.stderr.decode('utf-8', errors='ignore') if e.stderr else str(e)
                invalid_remotes.append(f"{remote}: {error_msg}")

        if not valid_remotes:
            return jsonify({
                "status": "error",
                "message": "Ningún remote válido disponible. Verifique las credenciales y permisos de Google Drive.",
                "invalid_remotes": invalid_remotes
            }), 400

        num_parts = len(valid_remotes)
        file_size = os.path.getsize(file_path)
        part_size = file_size // num_parts
        remainder = file_size % num_parts

        parts = []
        with open(file_path, 'rb') as f:
            for i in range(num_parts):
                part_path = f"{file_path}.part{i+1}"
                size = part_size + (1 if i < remainder else 0)
                with open(part_path, 'wb') as pf:
                    pf.write(f.read(size))
                parts.append(part_path)

        # Subir cada parte a su remote correspondiente
        upload_results = []
        for i, (remote, part_path) in enumerate(zip(valid_remotes, parts)):
            try:
                cmd_upload = [
                    RCLONE_EXE,
                    "copy", part_path, f"{remote}:", "-P"
                ]
                print(f"Ejecutando: {' '.join(cmd_upload)}")
                subprocess.run(cmd_upload, check=True, capture_output=True)
                upload_results.append(f"Parte {i+1} subida a {remote}")
                os.unlink(part_path)  # Eliminar parte después de subir
            except subprocess.CalledProcessError as e:
                error_msg = e.stderr.decode('utf-8', errors='ignore') if e.stderr else str(e)
                upload_results.append(f"Error en parte {i+1} a {remote}: {error_msg}")

        # Limpiar archivo original si todas las partes se subieron
        if all("Error" not in result for result in upload_results):
            os.unlink(file_path)

        response = {
            "status": "ok",
            "message": f"Archivo dividido en {num_parts} partes y subido a {len(valid_remotes)} remotes.",
            "results": upload_results
        }
        if invalid_remotes:
            response['invalid_remotes'] = invalid_remotes
        return jsonify(response)
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

if __name__ == '__main__':
    print(f"Microservicio Rcloner activo en puerto 5001...")
    app.run(host='0.0.0.0', port=5001)