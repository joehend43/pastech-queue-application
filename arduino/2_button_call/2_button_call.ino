// ======================================================
// DEVICE
// ======================================================
const int idDevice = 1;
const String deviceType = "KASIR";
unsigned long lastAlive = 0;

// ======================================================
// PIN
// ======================================================
const int redButtonPin = D5;    // call current
const int greenButtonPin = D2;  // call next_order

// ======================================================
// CONFIG
// ======================================================
const unsigned long debounceDelay = 50;

const unsigned long pressCooldown = 1000;  // tombol sama
const unsigned long globalCooldown = 300;  // antar tombol

unsigned long globalLastPress = 0;

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
  { D2, "next_order" }
};

// ======================================================
// SETUP
// ======================================================
void setup() {
  Serial.begin(9600);

  pinMode(redButtonPin, INPUT_PULLUP);
  pinMode(greenButtonPin, INPUT_PULLUP);

  delay(1000);
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
    }
    // ===================
    // ERROR
    // ===================
    else if (msg.startsWith("ERR|"))
      Serial.println(
        "LOG|ERROR=" + msg);
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

  yield();
}
