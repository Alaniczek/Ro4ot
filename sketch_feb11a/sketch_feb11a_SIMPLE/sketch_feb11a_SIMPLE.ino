#include <ESP8266WiFi.h>
#include <WiFiUdp.h>

const char* WIFI_SSID = "AkuKu";
const char* WIFI_PASS = "12345678";
const char* SERVER_IP = "192.168.125.112";
const int SERVER_PORT = 80;
const int LOCAL_PORT  = 4210;

const int PWMa = D1;
const int PWMb = D2;
const int WiB  = D3;
const int WiR  = D4;
const int ZiB  = D6;
const int ZiR  = D7;

int currentPower = 100; 

WiFiUDP udp;
WiFiClient client;

void wyslijDoPHP(String wiadomosc);
void DriversSettings(char cmd);

void setup() {
  Serial.begin(115200);
  pinMode(LED_BUILTIN, OUTPUT);
  
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  while (WiFi.status() != WL_CONNECTED) delay(500); //event handler , wifi.onEvent
  
  udp.begin(LOCAL_PORT);
  Serial.println("\n=== START ESP ===");
  Serial.print("Moje IP: ");
  Serial.println(WiFi.localIP());

  analogWriteRange(255);

  pinMode(PWMa, OUTPUT);
  pinMode(PWMb, OUTPUT);
  pinMode(WiB, OUTPUT);
  pinMode(WiR, OUTPUT);
  pinMode(ZiB, OUTPUT);
  pinMode(ZiR, OUTPUT);
  
  wyslijDoPHP("Start_Systemu");
}

void loop() {
  if (udp.parsePacket()) {
    char cmd = udp.read();
    udp.flush(); 
    Serial.print("[UDP] Odebrano komende: ");
    Serial.println(cmd);
    
    if(cmd == 'W') {
      DriversSettings('W');
      wyslijDoPHP("MOVE_FORWARD");
    } else if(cmd == 'S') {
      DriversSettings('S');
      wyslijDoPHP("MOVE_BACK");
    } else if(cmd == 'A') {
      DriversSettings('A');
      wyslijDoPHP("MOVE_LEFT");
    } else if(cmd == 'D') {
      DriversSettings('D');
      wyslijDoPHP("MOVE_RIGHT");
    } else if(cmd == 'X') {
      DriversSettings('X');
      wyslijDoPHP("STOP");
    } else if(cmd == '9') {
      DriversSettings('9');
      wyslijDoPHP("POWER_200");
    } else if(cmd == '8') {
      DriversSettings('8');
      wyslijDoPHP("POWER_125");
    } else if(cmd == '7') {
      DriversSettings('7');
      wyslijDoPHP("POWER_50");
    } else {
      String logMsg = "CMD_ERR_" + String(cmd); 
      wyslijDoPHP(logMsg);
    }
  }

  static unsigned long ostatniCzas = 0;
  if (millis() - ostatniCzas > 10000) {
    ostatniCzas = millis();
    Serial.println("[SYSTEM] Wysylam Ping...");
    wyslijDoPHP("PING");
  }
}

void wyslijDoPHP(String wiadomosc) {
  if (client.connect(SERVER_IP, SERVER_PORT)) {
    client.print("GET /Ro4ot-car/ro4otAPP/src/index.php?msg=" + wiadomosc + " HTTP/1.1\r\n" +
                 "Host: " + SERVER_IP + "\r\n" + 
                 "Connection: close\r\n\r\n");
    delay(30);
    client.stop();
  } else {
    Serial.println("[BLAD] Nie polaczono z PHP!");
  }
}

void DriversSettings(char cmd) {
  if(cmd == 'W') {
    digitalWrite(WiB, LOW);
    digitalWrite(WiR, HIGH);
    digitalWrite(ZiB, LOW);
    digitalWrite(ZiR, HIGH);
    analogWrite(PWMa, currentPower);
    analogWrite(PWMb, currentPower);
  } else if(cmd == 'S') {
    digitalWrite(WiB, HIGH);
    digitalWrite(WiR, LOW);
    digitalWrite(ZiB, HIGH);
    digitalWrite(ZiR, LOW);
    analogWrite(PWMa, currentPower);
    analogWrite(PWMb, currentPower);
  } else if(cmd == 'A') {
    digitalWrite(WiB, HIGH);
    digitalWrite(WiR, LOW);
    digitalWrite(ZiB, LOW);
    digitalWrite(ZiR, LOW);
    analogWrite(PWMa, currentPower);
    analogWrite(PWMb, currentPower);
  } else if(cmd == 'D') {
    digitalWrite(WiB, LOW);
    digitalWrite(WiR, LOW);
    digitalWrite(ZiB, HIGH);
    digitalWrite(ZiR, LOW);
    analogWrite(PWMa, currentPower);
    analogWrite(PWMb, currentPower);
  } else if(cmd == 'X') {
    digitalWrite(WiB, LOW);
    digitalWrite(WiR, LOW);
    digitalWrite(ZiB, LOW);
    digitalWrite(ZiR, LOW);
    analogWrite(PWMa, 0);
    analogWrite(PWMb, 0);
  } else if(cmd == '9') {
    currentPower = 200;
  } else if(cmd == '8') {
    currentPower = 125;
  } else if(cmd == '7') {
    currentPower = 50;
  }
}