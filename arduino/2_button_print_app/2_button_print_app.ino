#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

// ======================================================
// WIFI
// ======================================================
const char* ssid = "Moo";
const char* password = "omgomgomg";

// ======================================================
// API
// ======================================================
const char* apiOrderUrl =
  "http://192.168.1.12:8000/api/queues/print-new?type=A";

const char* apiPickupUrl =
  "http://192.168.1.12:8000/api/queues/print-new?type=B";

// ======================================================
// PIN
// ======================================================
const int redButtonPin = D5;   // print order
const int greenButtonPin = D2; // print pickup

// ======================================================
// CONFIG
// ======================================================
const unsigned long debounceDelay = 50;
const unsigned long pressCooldown = 1000; // cooldown per button

// global anti spam
const unsigned long globalCooldown = 1000; // cooldown between button red and green
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

// ======================================================
// WIFI
// ======================================================
void connectWiFi() {

  if (WiFi.status() == WL_CONNECTED)
    return;

  Serial.println("Connecting WiFi...");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  unsigned long startAttempt = millis();

  while (WiFi.status() != WL_CONNECTED &&
         millis() - startAttempt < 15000) {

    delay(500);
    Serial.print(".");
  }

  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {

    Serial.println("WiFi Connected");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());

  } else {

    Serial.println("WiFi Failed");
  }
}

// ======================================================
// SEND API
// ======================================================
void sendAPI(const char* url) {

  if (WiFi.status() != WL_CONNECTED) {

    Serial.println("WiFi disconnected");
    return;
  }

  WiFiClient client;
  client.setTimeout(2);

  HTTPClient http;

  Serial.print("SEND API: ");
  Serial.println(url);

  if (!http.begin(client, url)) {

    Serial.println("HTTP begin failed");
    return;
  }

  http.setReuse(false);
  http.setTimeout(1500);

  int httpCode = http.GET();

  Serial.print("HTTP Code: ");
  Serial.println(httpCode);

  if (httpCode > 0) {

    String payload = http.getString();
    Serial.println(payload);

  } else {

    Serial.print("HTTP ERROR: ");
    Serial.println(http.errorToString(httpCode));
  }

  http.end();
  yield();
}

// ======================================================
// SETUP
// ======================================================
void setup() {

  Serial.begin(9600);

  pinMode(redButtonPin, INPUT_PULLUP);
  pinMode(greenButtonPin, INPUT_PULLUP);

  delay(1000);

  connectWiFi();

  Serial.println("System Ready");
}

// ======================================================
// LOOP
// ======================================================
void loop() {

  // reconnect wifi
  if (WiFi.status() != WL_CONNECTED)
    connectWiFi();

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

          Serial.println("BUTTON 1");

          sendAPI(apiOrderUrl);
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

          Serial.println("BUTTON 2");

          sendAPI(apiPickupUrl);
        }
      }
    }
  }

  lastButton2State = reading2;

  yield();
}