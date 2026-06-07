// ======================================================
// PIN
// ======================================================
const int redButtonPin = D5;    // print order
const int greenButtonPin = D2;  // print pickup

// ======================================================
// CONFIG
// ======================================================
const unsigned long debounceDelay = 50;
const unsigned long pressCooldown = 1000;  // cooldown per button

// global anti spam
const unsigned long globalCooldown = 1000;  // cooldown between button red and green
unsigned long globalLastPress = 0;

// ======================================================
// BUTTON STATE
// ======================================================
bool button1State = HIGH;
bool lastButton1State = HIGH;
unsigned long lastDebounce1 = 0;
unsigned long lastPress1 = 0;

bool button2State = HIGH;
bool lastButton2State = HIGH;
unsigned long lastDebounce2 = 0;
unsigned long lastPress2 = 0;

const String DEVICE_ID = "6";
const String DEVICE_TYPE = "PRINTER";

unsigned long lastAlive = 0;


// ======================================================
// SETUP
// ======================================================
void setup() {
  Serial.begin(9600);

  pinMode(redButtonPin, INPUT_PULLUP);
  pinMode(greenButtonPin, INPUT_PULLUP);

  delay(1000);
}

void handleSerialCom(){
  // Heartbeat setiap 5 detik
  if (millis() - lastAlive >= 5000) {
    lastAlive = millis();

    Serial.println(
      "ALIVE|" + DEVICE_ID + "|" + DEVICE_TYPE);
  }

  if (Serial.available()) {
    String cmd =
      Serial.readStringUntil('\n');

    cmd.trim();

    if (cmd == "PING") {
      Serial.println("HELLO|" + DEVICE_ID + "|" + DEVICE_TYPE);
    }
  }
}

// ======================================================
// LOOP
// ======================================================
void loop() {
  handleSerialCom();

  // ==========================
  // BUTTON 1 (RED)
  // ==========================
  int reading1 = digitalRead(redButtonPin);

  if (reading1 != lastButton1State)
    lastDebounce1 = millis();

  if ((millis() - lastDebounce1) > debounceDelay) {

    if (reading1 != button1State) {

      button1State = reading1;

      if (button1State == LOW) {

        bool buttonReady =
          millis() - lastPress1 > pressCooldown;

        bool globalReady =
          millis() - globalLastPress > globalCooldown;

        if (buttonReady && globalReady) {

          lastPress1 = millis();
          globalLastPress = millis();

          Serial.println("V1|_|PRINTER|print_order");
        }
      }
    }
  }

  lastButton1State = reading1;

  // ==========================
  // BUTTON 2 (GREEN)
  // ==========================
  int reading2 = digitalRead(greenButtonPin);

  if (reading2 != lastButton2State)
    lastDebounce2 = millis();

  if ((millis() - lastDebounce2) > debounceDelay) {

    if (reading2 != button2State) {

      button2State = reading2;

      if (button2State == LOW) {

        bool buttonReady =
          millis() - lastPress2 > pressCooldown;

        bool globalReady =
          millis() - globalLastPress > globalCooldown;

        if (buttonReady && globalReady) {

          lastPress2 = millis();
          globalLastPress = millis();

          Serial.println("V1|_|PRINTER|print_pickup");
        }
      }
    }
  }

  lastButton2State = reading2;

  yield();
}