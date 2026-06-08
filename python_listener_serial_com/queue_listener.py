import serial
import serial.tools.list_ports
import threading
import requests
import time
import winreg
from logging.handlers import TimedRotatingFileHandler
import logging
import os
import sys

#const config
API_BASE_URL = "http://192.168.100.16:8000/api"
SERIAL_BAUDRATE = 9600
API_TIMEOUT = 3
WATCHDOG_TIMEOUT = 20
SCAN_INTERVAL = 3

session = requests.Session()
devices_lock = threading.Lock()
devices = {}


def api_get(url):
    logging.info(url)

    response = session.get(url, timeout=API_TIMEOUT)

    logging.info(f"Status: {response.status_code}")

    if response.status_code != 200:
        try:
            logging.error(f"Resp: {response.json()}")
        except:
            logging.error("Resp: Invalid JSON")

    return response


def process_command(line, ser):
    try:
        #heartbeat
        if line.startswith("ALIVE|"):
            parts = line.split("|")
            device_id = parts[1]

            if device_id in devices:
                # device dianggap hidup begitu ALIVE diterima
                devices[device_id]["last_seen"] = time.time()

                try:
                    api_get(
                        f"{API_BASE_URL}/update_last_seen?user_id={device_id}"
                    )
                except Exception as ex:
                    logging.error(
                        "Heartbeat API failed for %s: %s",
                        device_id,
                        ex
                    )

                logging.info(
                    "[ESP] [Device:%s] [ALIVE]",device_id)

            return
        
        if line.startswith("LOG|"):
            logging.info("[ESP] %s", line)
            return

        logging.info("RX: %s", line)

        parts = line.strip().split("|")

        if len(parts) < 4:
            return

        version = parts[0]
        device_id = parts[1]
        device_type = parts[2]
        command = parts[3]

        #print order 
        if device_type == "PRINTER" and command == "print_order":
            url = f"{API_BASE_URL}/queues/print-new?type=A"
            api_get(url) 
        #print pickup
        elif device_type == "PRINTER" and command == "print_pickup":
            url = f"{API_BASE_URL}/queues/print-new?type=B"
            api_get(url)  
        #Call Next Order
        elif device_type == "KASIR" and command == "next_order":
            url = f"{API_BASE_URL}/queues/call?type={command}&user_id={device_id}"
            api_get(url)  
        #Call Next Order
        elif device_type == "KASIR" and command == "current":
            url = f"{API_BASE_URL}/queues/call?type={command}&user_id={device_id}"
            api_get(url)    
        #Call Next Order
        elif device_type == "KASIR" and command == "next_pickup":
            url = f"{API_BASE_URL}/queues/call?type={command}&user_id={device_id}"
            api_get(url)   
        #get count queue
        elif device_type == "KASIR" and command == "get_status":
            url = f"{API_BASE_URL}/queues/count-remaining"
            response = api_get(url)   
            if response.status_code == 200:
                data = response.json()
                queue_total = data.get("B", 0)
                ser.write(
                    f"OK|B|{queue_total}\n".encode()
                )
        else:
            logging.warning(f"Unknown command: {command}")

    except requests.exceptions.Timeout:
        logging.error("API Timeout")
        ser.write(
            b"ERR|TIMEOUT\n"
        )
    except requests.exceptions.ConnectionError:
        logging.error("API Offline")
        ser.write(
            b"ERR|OFFLINE\n"
        )
    except requests.exceptions.HTTPError as ex:
        logging.error(
            f"HTTP Error: "
            f"{response.status_code}"
            f"{response.json()}"
        )

        ser.write(
            (
                f"ERR|HTTP|"
                f"{response.status_code}\n"
            ).encode()
        )

    except Exception as ex:
        logging.error(ex)
        ser.write(
            b"ERR|UNKNOWN\n"
        )

def serial_listener(device_id):
    with devices_lock:
        ser = devices[device_id]["serial"]

    logging.info(f"Listening {device_id}")

    try:

        while True:

            line = (
                ser.readline()
                .decode(errors="ignore")
                .strip()
            )

            if not line:
                continue

            process_command(
                line,
                ser
            )

    except Exception as ex:
        logging.warning(
            f"{device_id} disconnected: {ex}"
        )

    finally:

        try:
            ser.close()
        except:
            pass

        if device_id in devices:
            with devices_lock:
                devices.pop(device_id, None)

        logging.info(
            f"{device_id} removed"
        )

def scan_devices():

    ports = list(serial.tools.list_ports.comports())

    for port in ports:
        if (
            (port.vid, port.pid)
            not in
            [
                (6790, 29987),   # CH340
                (4292, 60000),   # CP2102
            ]
        ):
            continue

        try:
            # cek apakah COM sudah dipakai
            already_connected = False

            for d in devices.values():
                if d["port"] == port.device:
                    already_connected = True
                    break

            if already_connected:
                continue

            logging.info(
                f"Checking {port.device}"
            )

            ser = serial.Serial(
                port.device,
                SERIAL_BAUDRATE,
                timeout=0.5,
                write_timeout=0.5
            )

            time.sleep(1)

            ser.reset_input_buffer()

            ser.write(
                b"PING\n"
            )

            response = (
                ser.readline()
                .decode()
                .strip()
            )

            logging.info(
                f"{port.device} -> {response}"
            )

            if not response.startswith(
                "HELLO|"
            ):
                ser.close()
                continue

            parts = response.split("|")

            device_id = parts[1]
            device_type = parts[2]

            if device_id in devices:

                ser.close()
                continue
            with devices_lock:
                devices[device_id] = {
                    "port": port.device,
                    "type": device_type,
                    "serial": ser,
                    "last_seen": time.time()
            }

            logging.info(
                f"CONNECTED "
                f"{device_id} "
                f"({device_type}) "
                f"on {port.device}"
            )

            threading.Thread(
                target=serial_listener,
                args=(device_id,),
                daemon=True
            ).start()

        except Exception as ex:
            logging.error(f"OPEN FAIL {port.device}: {ex}")
            continue

def watchdog():
    while True:
        now = time.time()
        with devices_lock:
            device_ids = list(devices.keys())
        for device_id in device_ids:
            try:
                with devices_lock:
                    device = devices.get(device_id)

                if not device:
                    continue

                diff = now - device["last_seen"]

                if diff > WATCHDOG_TIMEOUT:
                    logging.info(
                        f"{device_id} timeout"
                    )

                    try:
                        devices[device_id]["serial"].close()
                    except:
                        pass
                    with devices_lock:
                        devices.pop( device_id, None )
            except:
                pass

        time.sleep(5)

def register_startup():
    logging.info("Checking Auto Startup")
    app_name = "Listener"
    app_path = r"C:\Queue_Listener\run_listener.bat"

    try:
        key = winreg.OpenKey(
            winreg.HKEY_CURRENT_USER,
            r"Software\Microsoft\Windows\CurrentVersion\Run",
            0,
            winreg.KEY_READ
        )

        winreg.QueryValueEx(key, app_name)
        winreg.CloseKey(key)

        logging.info("Startup already registered")
        return

    except FileNotFoundError:
        pass

    key = winreg.OpenKey(
        winreg.HKEY_CURRENT_USER,
        r"Software\Microsoft\Windows\CurrentVersion\Run",
        0,
        winreg.KEY_SET_VALUE
    )

    winreg.SetValueEx(
        key,
        app_name,
        0,
        winreg.REG_SZ,
        app_path
    )
    winreg.CloseKey(key)
    logging.info("Startup registered")

def get_base_path():
    # kalau running dari exe (PyInstaller)
    if getattr(sys, 'frozen', False):
        return os.path.dirname(sys.executable)
    # kalau run .py biasa
    return os.path.dirname(os.path.abspath(__file__))

def register_logger():
    base_path = get_base_path()
    log_dir = os.path.join(base_path, "logs")

    os.makedirs(log_dir, exist_ok=True)

    log_file = os.path.join(log_dir, "app.log")

    logger = logging.getLogger()
    logger.setLevel(logging.INFO)

    handler = TimedRotatingFileHandler(
        log_file,
        when="midnight",
        interval=1,
        backupCount=30,
        encoding="utf-8"
    )

    handler.suffix = "%Y-%m-%d"
    formatter = logging.Formatter(
        "%(asctime)s - %(levelname)s - %(message)s"
    )

    console_handler = logging.StreamHandler()
    console_handler.setFormatter(formatter)

    logger.addHandler(console_handler)

    handler.setFormatter(formatter)
    logger.addHandler(handler)
    logging.info("Enabled Logger")



#main
register_logger()
register_startup()

logging.info("Queue Gateway Started")

threading.Thread(
    target=watchdog,
    daemon=True
).start()

while True:
    try:
        scan_devices()
    except Exception as ex:
        logging.info(
            "MAIN LOOP ERROR: %s",
            ex
        )
    time.sleep(SCAN_INTERVAL)