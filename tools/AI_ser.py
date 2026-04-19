import sys
import base64
import io
import os
from flask import Flask, request, jsonify
import logging
import traceback
import threading
from google import genai
from google.genai import types
from PIL import Image

# 🌐 Forzar codificación UTF-8 para evitar errores en Windows al escribir logs
if sys.stdout.encoding != 'utf-8':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

app = Flask(__name__)

# Configuración de logs para ver exactamente qué pasa en la consola
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# 🧠 Diccionarios globales para mantener estado por cliente.
# Esto asegura que cada cliente tenga su chat persistente y podamos resetearlo si se cierra.
clients = {}
chat_sessions = {}
chat_history = {}
client_api_keys = {}
state_lock = threading.Lock() # 🔒 Evitar colisiones en peticiones simultáneas

def get_chat_session(client_id, api_key):
    """Obtiene la sesión activa o crea una nueva para el cliente."""
    with state_lock:
        # Si el api_key cambia, forzamos reset
        if client_id in client_api_keys and client_api_keys[client_id] != api_key:
            _internal_reset(client_id)

        client_api_keys[client_id] = api_key

        if client_id not in clients:
            logger.info(f"Creando nuevo cliente para {client_id}")
            clients[client_id] = genai.Client(api_key=api_key)

        if client_id not in chat_sessions:
            # Configuración de seguridad: BLOCK_NONE para evitar censura en actas antiguas
            config = types.GenerateContentConfig(
                safety_settings=[
                    types.SafetySetting(category='HARM_CATEGORY_HARASSMENT', threshold='BLOCK_NONE'),
                    types.SafetySetting(category='HARM_CATEGORY_HATE_SPEECH', threshold='BLOCK_NONE'),
                    types.SafetySetting(category='HARM_CATEGORY_SEXUALLY_EXPLICIT', threshold='BLOCK_NONE'),
                    types.SafetySetting(category='HARM_CATEGORY_DANGEROUS_CONTENT', threshold='BLOCK_NONE'),
                ]
            )

            # Usamos el modelo gemini-3-flash-preview
            chat = clients[client_id].chats.create(model='gemini-3-flash-preview', config=config)
            chat_sessions[client_id] = chat
            chat_history[client_id] = []

        return chat_sessions[client_id]


def reset_chat_session(client_id):
    """Elimina la sesión, el cliente y el historial del usuario."""
    with state_lock:
        _internal_reset(client_id)

def _internal_reset(client_id):
    logger.warning(f"Reseteando sesión del cliente {client_id} por error o cambio de llave.")
    clients.pop(client_id, None)
    chat_sessions.pop(client_id, None)
    chat_history.pop(client_id, None)
    client_api_keys.pop(client_id, None)


@app.route('/process', methods=['POST'])
def process_request():
    data = request.json
    client_id = data.get('client_id')
    api_key = data.get('api_key')
    prompt = data.get('prompt')
    image_b64 = data.get('image') # Imagen opcional (solo para extracción)

    if not client_id or not api_key or not prompt:
        return jsonify({"status": "error", "message": "Faltan parámetros: client_id, api_key o prompt"}), 400

    try:
        logger.info(f"Recibida petición de {client_id}")
        chat = get_chat_session(client_id, api_key)
        
        message_parts = [prompt]
        if image_b64:
            # Decodificar imagen Base64 para Gemini
            img_bytes = base64.b64decode(image_b64)
            img = Image.open(io.BytesIO(img_bytes))
            message_parts.append(img)

        # Intento de envío con recuperación automática
        try:
            response = chat.send_message(message_parts)
        except Exception as inner_e:
            err_msg = str(inner_e).lower()
            # Si el cliente está cerrado o hay error de red, recreamos TODO
            if any(k in err_msg for k in ['closed', 'http', 'reconnection', 'dead', 'inactive']):
                logger.warning("Cliente cerrado detectado. Reintentando con nueva sesión...")
                reset_chat_session(client_id)
                chat = get_chat_session(client_id, api_key)
                response = chat.send_message(message_parts)
            else:
                raise

        if client_id not in chat_history:
            chat_history[client_id] = []
        chat_history[client_id].append({
            'prompt': prompt,
            'response': response.text,
            'image': bool(image_b64)
        })
        
        return jsonify({
            "status": "ok",
            "response": response.text,
            "client_id": client_id
        })
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

if __name__ == '__main__':
    print("LISTO: Microservicio de IA activo en puerto 5000")
    app.run(host='0.0.0.0', port=5000)
