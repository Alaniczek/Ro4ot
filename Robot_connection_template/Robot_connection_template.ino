#include <ESP8266WiFi.h>
#include <WiFiUdp.h>

const char* WIFI_SSID = "AkuKu";
const char* WIFI_PASS = "12345678";
const int LOCAL_PORT  = 4210;

String SERVER_IP = "192.168.243.171";
uint16_t SERVER_PORT = 80;

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
  int packetSize = udp.parsePacket();
  if (packetSize) {
    String cmd = udp.readString();
    cmd.trim();

    Serial.print("[UDP] Odebrano: ");
    Serial.println(cmd);

    if (cmd == "A") {
      SendToPHP("test_wiadomosci");
    }else if(cmd[0] == 'I') {
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
    Serial.println("== Wynik ==");
    Serial.print("IP:   "); Serial.println(IP);
    Serial.print("PORT: "); Serial.println(PORT);
    SERVER_IP = IP;
    SERVER_PORT = PORT.toInt();
  }
  }
}

void SendToPHP(String Message) {
  if (client.connect(SERVER_IP, SERVER_PORT)) {
    client.print("GET /Ro4ot/ro4otAPP/src/index.php?msg=" + Message + " HTTP/1.1\r\n" +
                 "Host: " + String(SERVER_IP) + "\r\n" +
                 "Connection: close\r\n\r\n");

    unsigned long timeout = millis();
    while (client.connected() && millis() - timeout < 2000) {
      if (client.available()) {
        client.stop();
        return;
      }
    }
    client.stop();
  } else {
    Serial.println("[BLAD] Brak polaczenia z PHP");
  }
}