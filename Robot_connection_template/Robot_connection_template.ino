#include <ESP8266WiFi.h>
#include <WiFiUdp.h>

const char* WIFI_SSID = "AkuKu";
const char* WIFI_PASS = "12345678";
const int LOCAL_PORT  = 4210;

String SERVER_IP = "192.168.243.171";
uint16_t SERVER_PORT = 4040;

unsigned long previousMillis = 0;
const long interval = 15000;

WiFiUDP udp;
WiFiClient client;

void SendToPHP(String Message);

void setup() {
  Serial.begin(115200);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  while (WiFi.status() != WL_CONNECTED) delay(500);

  udp.begin(LOCAL_PORT);
  Serial.println("\n=== START ESP ===");
}

void loop() {
    unsigned long currentMillis = millis();

     if (currentMillis - previousMillis >= interval) {
       previousMillis = currentMillis;
        SendToPHP("$E8A3");
      }

  int packetSize = udp.parsePacket();
  if (packetSize) {
    String cmd = udp.readString();
    cmd.trim();

    Serial.print("[UDP] Odebrano: ");
    Serial.println(cmd);

    if (cmd == "A") {
      SendToPHP("test_wiadomosci A");
    }else if(cmd == "D")
    {
      SendToPHP("TEST D");
    } 
    else if(cmd[0] == 'I') {
      String IP = "";
      String PORT = "";
      String ControllValue = "";
      bool BoolIP = true;

      for (int i = 2; i < cmd.length(); i++) {
        if (cmd[i] == ' ' && BoolIP) {
          IP = ControllValue;
          ControllValue = "";
          BoolIP = false;
        } else if (cmd[i] == 'X') {
          PORT = ControllValue;
        } else if (cmd[i] != ' ') {
          ControllValue += cmd[i];
        }
      }
      
      IP.trim();
      PORT.trim();
      
      SERVER_IP = IP;
      SERVER_PORT = PORT.toInt();

      Serial.println("== Wynik ==");
      Serial.print("IP:   "); Serial.println(SERVER_IP);
      Serial.print("PORT: "); Serial.println(SERVER_PORT);
      SendToPHP("$E8A3 IS CONNECTED");
    }
  }
}

void SendToPHP(String Message) {
  udp.beginPacket(SERVER_IP.c_str(), SERVER_PORT);
  udp.print(Message);
  udp.endPacket();
}