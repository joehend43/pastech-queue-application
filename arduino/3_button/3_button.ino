#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

// ======================================================
// DEVICE
// ======================================================
const int idDevice = 4;
const String deviceType = "KASIR";

unsigned long lastAlive = 0;

// ======================================================
// PIN
// ======================================================
const int btnPrev = D5;
const int btnNext = D6;
const int btnPickup = D7;

const int ledGreen = D1;
const int ledRed = D2;

// ======================================================
// CONFIG
// ======================================================
const unsigned long debounceDelay = 50;
const unsigned long pressCooldown = 700;

const unsigned long globalCooldown = 300;
unsigned long globalLastPress = 0;

const unsigned long statusInterval = 5000;
unsigned long lastStatusCheck = 0;

// ======================================================
// BUTTON STRUCT
// ======================================================
struct ButtonState {
  int pin;
  const char* type;

  bool state = HIGH;
  bool lastState = HIGH;

  unsigned long lastDebounce = 0;
  unsigned long lastPress = 0;
};

ButtonState buttons[] = {
  { D5, "current" },
  { D6, "next_order" },
  { D7, "next_pickup" }
};

// ======================================================
// CHECK STATUS
// ======================================================
void checkQueueStatus() {
  // hit to get status
  String message = "V1|" + String(idDevice) + "|" + deviceType + "|get_status";
  Serial.println(message);
}

// ======================================================
// SETUP
// ======================================================
void setup() {
  Serial.begin(9600);

  pinMode(btnPrev, INPUT_PULLUP);
  pinMode(btnNext, INPUT_PULLUP);
  pinMode(btnPickup, INPUT_PULLUP);

  pinMode(ledGreen, OUTPUT);
  pinMode(ledRed, OUTPUT);

  digitalWrite(ledGreen, LOW);
  digitalWrite(ledRed, LOW);

  delay(1000);

  Serial.println("LOG|Device= " + deviceType + " - " + String(idDevice) + " Is Ready");
}

void handleSerialCom() {
  // Heartbeat setiap 5 detik
  if (millis() - lastAlive >= 5000) {
    lastAlive = millis();

    Serial.println(
      "ALIVE|" + String(idDevice) + "|" + deviceType);
  }

  if (Serial.available()) {
    String msg =
      Serial.readStringUntil('\n');

    msg.trim();

    if (msg == "PING") {
      Serial.println("HELLO|" + String(idDevice) + "|" + deviceType);
    } else if (msg.startsWith("OK|")) {
      int p1 = msg.indexOf('|');
      int p2 = msg.indexOf('|', p1 + 1);

      String tipe =
        msg.substring(p1 + 1, p2);

      String nomor =
        msg.substring(p2 + 1);

      int waiting =
        nomor.toInt();

      Serial.println(
        "LOG|QUEUE=" + String(waiting));

      if (waiting > 0) {
        digitalWrite(ledGreen, LOW);
        digitalWrite(ledRed, HIGH);
      } else {
        digitalWrite(ledGreen, HIGH);
        digitalWrite(ledRed, LOW);
      }
    }
    // ===================
    // ERROR
    // ===================
    else if (msg.startsWith("ERR|")) {

      Serial.println(
        "LOG|ERROR=" + msg);

      digitalWrite(ledRed, LOW);
      digitalWrite(ledGreen, LOW);
    }
  }
}

// ======================================================
// LOOP
// ======================================================
void loop() {

  handleSerialCom();

  // button handler
  for (auto& btn : buttons) {
    int reading = digitalRead(btn.pin);

    if (reading != btn.lastState)
      btn.lastDebounce = millis();

    if ((millis() - btn.lastDebounce) > debounceDelay) {

      if (reading != btn.state) {

        btn.state = reading;

        if (btn.state == LOW) {

          bool buttonReady =
            millis() - btn.lastPress > pressCooldown;

          bool globalReady =
            millis() - globalLastPress > globalCooldown;

          if (buttonReady && globalReady) {
            btn.lastPress = millis();
            globalLastPress = millis();

            String message =
              "V1|" + String(idDevice) + "|" + deviceType + "|" + btn.type;

            Serial.println(message);
          }
        }
      }
    }

    btn.lastState = reading;
  }

  // status polling
  if (millis() - lastStatusCheck >= statusInterval) {
    lastStatusCheck = millis();

    checkQueueStatus();
  }

  yield();
}