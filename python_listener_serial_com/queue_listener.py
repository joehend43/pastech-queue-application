import serial
import serial.tools.list_ports
import threading
import requests
import time
import sys
import os
import winreg

API_BASE_URL = "http://127.0.0.1:8000/api"

session = requests.Session()
devices_lock = threading.Lock()
devices = {}
WATCHDOG_TIMEOUT = 20
SCAN_INTERVAL = 3



def process_command(line, ser):
    try:
        #heartbeat
        if line.startswith("ALIVE|"):
            parts = line.split("|")
            device_id = parts[1]
            if device_id in devices:
                url = f"{API_BASE_URL}/update_last_seen"
                response= session.get(
                    url,
                    params={"user_id": device_id},
                    timeout=2
                )

                print(
                    f"Status: {response.status_code}")
                if response.status_code != 200 :
                    print( f"Resp: {response.json()}")   
                devices[device_id]["last_seen"] = time.time()
                print("[ESP]","[Device:",device_id, "][ALIVE]")
            return
        
        if line.startswith("LOG|"):
            print("[ESP]", line)
            return

        print("RX:", line)

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
            print(url)
            response = session.get(url, timeout=2)
            
            print(
                f"Status: {response.status_code}"
            )
            if response.status_code != 200 :
                print(
                 f"Resp: {response.json()}"
                )   
        #print pickup
        elif device_type == "PRINTER" and command == "print_pickup":
            url = f"{API_BASE_URL}/queues/print-new?type=B"
            print(url)
            response = session.get(url, timeout=2)

            print(
                f"Status: {response.status_code}"
            )
            if response.status_code != 200 :
                print(
                 f"Resp: {response.json()}"
                )   
        #Call Next Order
        elif device_type == "KASIR" and command == "next_order":
            url = f"{API_BASE_URL}/queues/call?type={command}&user_id={device_id}"
            print(url)
            response = session.get(url, timeout=2)
            print(
                f"Status: {response.status_code}"
            )
            if response.status_code != 200 :
                print(
                 f"Resp: {response.json()}"
                )   
        #Call Next Order
        elif device_type == "KASIR" and command == "current":
            url = f"{API_BASE_URL}/queues/call?type={command}&user_id={device_id}"
            print(url)
            response = session.get(url, timeout=2)
            print(
                f"Status: {response.status_code}"
            )
            if response.status_code != 200 :
                print(
                 f"Resp: {response.json()}"
                )   
        #Call Next Order
        elif device_type == "KASIR" and command == "next_pickup":
            url = f"{API_BASE_URL}/queues/call?type={command}&user_id={device_id}"
            print(url)
            response = session.get(url, timeout=2)
            print(
                f"Status: {response.status_code}"
            )
            if response.status_code != 200 :
                print(
                 f"Resp: {response.json()}"
                )   
        #get count queue
        elif device_type == "KASIR" and command == "get_status":
            url = f"{API_BASE_URL}/queues/count-remaining"
            response = session.get(url, timeout=2)
            if response.status_code != 200 :
                print(
                 f"Resp: {response.json()}"
                ) 

            if response.status_code == 200 :
                data = response.json()
                queue_total = data.get("B") or 0

                ser.write(
                    f"OK|B|{queue_total}\n".encode()
                )
        else:
            print(f"Unknown command: {command}")

    except requests.exceptions.Timeout:
        print("API Timeout")
        ser.write(
            b"ERR|TIMEOUT\n"
        )
    except requests.exceptions.ConnectionError:
        print("API Offline")
        ser.write(
            b"ERR|OFFLINE\n"
        )
    except requests.exceptions.HTTPError as ex:
        print(
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
        print(ex)
        ser.write(
            b"ERR|UNKNOWN\n"
        )

def serial_listener(device_id):
    with devices_lock:
        ser = devices[device_id]["serial"]

    print(f"Listening {device_id}")

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

        print(
            f"{device_id} disconnected:",
            ex
        )

    finally:

        try:
            ser.close()
        except:
            pass

        if device_id in devices:
            with devices_lock:
                devices.pop(device_id, None)

        print(
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

            print(
                f"Checking {port.device}"
            )

            ser = serial.Serial(
                port.device,
                9600,
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

            print(
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

            print(
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
            print(f"OPEN FAIL {port.device}: {ex}")
            continue

def watchdog():
    while True:
        now = time.time()
        for device_id in list(devices.keys()):
            try:
                diff = (
                    now -
                    devices[device_id]["last_seen"]
                )

                if diff > WATCHDOG_TIMEOUT:
                    print(
                        f"{device_id} timeout"
                    )

                    try:
                        devices[device_id]["serial"].close()
                    except:
                        pass

                    devices.pop(
                        device_id,
                        None
                    )
            except:
                pass

        time.sleep(5)

def register_startup():
    print("Checking Auto Startup")
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

        print("Startup already registered")
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
    print("Startup registered")

register_startup()

print("Queue Gateway Started")

threading.Thread(
    target=watchdog,
    daemon=True
).start()

while True:
    try:
        scan_devices()
    except Exception as ex:
        print(
            "MAIN LOOP ERROR:",
            ex
        )
    time.sleep(SCAN_INTERVAL)