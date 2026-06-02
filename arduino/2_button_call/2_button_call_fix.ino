
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

// ======================================================
// WIFI
// ======================================================
const char* ssid = "Moo";
const char* password = "omgomgomg";

// ======================================================
// DEVICE
// ======================================================
const int idDevice = 1;

// ======================================================
// API
// ======================================================
const char* baseUrl = "http://192.168.1.12:8000";

// ======================================================
// PIN
// ======================================================
const int redButtonPin   = D5; // call current
const int greenButtonPin = D2; // call next_order

// ======================================================
// CONFIG
// ======================================================
const unsigned long debounceDelay = 50;

const unsigned long pressCooldown = 1000; // tombol sama
const unsigned long globalCooldown = 300; // antar tombol

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

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected");
    return;
  }

  String url;
  url.reserve(160);

  url = String(baseUrl);
  url += "/api/queues/call";
  url += "?type=";
  url += type;
  url += "&user_id=";
  url += idDevice;

  WiFiClient client;

  // lebih responsif kalau API mati
  client.setTimeout(2);

  HTTPClient http;

  Serial.print("SEND: ");
  Serial.println(url);

  if (!http.begin(client, url)) {
    Serial.println("HTTP begin failed");
    return;
  }

  http.setReuse(false);

  // fail lebih cepat kalau server ngadat
  http.setTimeout(1500);

  int httpCode = http.GET();

  Serial.print("HTTP: ");
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

  yield();
}
