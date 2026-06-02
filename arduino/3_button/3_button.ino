#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

// ======================================================
// WIFI
// ======================================================
const char* ssid = "Moo";
const char* password = "omgomgomg";

// ======================================================
// DEVICE
// ======================================================
const int idDevice = 5;

// ======================================================
// API
// ======================================================
const char* baseUrl = "http://192.168.1.12:8000";

// ======================================================
// PIN
// ======================================================
const int btnPrev   = D5;
const int btnNext   = D6;
const int btnPickup = D7;

const int ledGreen = D1;
const int ledRed   = D2;

// ======================================================
// CONFIG
// ======================================================
const unsigned long debounceDelay = 50;
const unsigned long pressCooldown = 700;

const unsigned long globalCooldown = 300;
unsigned long globalLastPress = 0;

const unsigned long statusInterval = 1500;
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

    delay(300);
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
void sendAPI(const char* type) {

  if (WiFi.status() != WL_CONNECTED)
    return;

  String url;
  url.reserve(160);

  url = String(baseUrl);
  url += "/api/queues/call";
  url += "?type=";
  url += type;
  url += "&user_id=";
  url += idDevice;

  WiFiClient client;
  client.setTimeout(2);

  HTTPClient http;

  Serial.print("SEND: ");
  Serial.println(url);

  if (!http.begin(client, url)) {
    Serial.println("HTTP begin failed");
    return;
  }

  http.setReuse(false);
  http.setTimeout(1500);

  int httpCode = http.GET();

  Serial.print("HTTP: ");
  Serial.println(httpCode);

  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println(payload);
  } else {
    Serial.println("API ERROR");
  }

  http.end();
  yield();
}

// ======================================================
// CHECK STATUS
// ======================================================
void checkQueueStatus() {

  if (WiFi.status() != WL_CONNECTED)
    return;

  String url = String(baseUrl) +
               "/api/queues/count-remaining";

  WiFiClient client;
  client.setTimeout(2);

  HTTPClient http;

  if (!http.begin(client, url))
    return;

  http.setReuse(false);
  http.setTimeout(1500);

  int httpCode = http.GET();

  if (httpCode > 0) {

    String payload = http.getString();

    StaticJsonDocument<64> doc;

    DeserializationError error =
      deserializeJson(doc, payload);

    if (!error) {

      int waiting = doc["B"] | 0;

      // Serial.print("Pickup WAITING: ");
      // Serial.println(waiting);

      digitalWrite(ledRed, waiting != 0);
      digitalWrite(ledGreen, waiting == 0);
    }

  } else {

    Serial.println("STATUS API ERROR");
  }

  http.end();
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

  connectWiFi();

  Serial.println("SYSTEM READY");
}

// ======================================================
// LOOP
// ======================================================
void loop() {

  // reconnect wifi
  if (WiFi.status() != WL_CONNECTED)
    connectWiFi();

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

            Serial.print("BUTTON: ");
            Serial.println(btn.type);

            sendAPI(btn.type);
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