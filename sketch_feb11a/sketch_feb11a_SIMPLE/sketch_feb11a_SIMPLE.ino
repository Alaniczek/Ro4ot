#include <ESP8266WiFi.h>
#include <WiFiUdp.h>

const char* WIFI_SSID = "AkuKu";
const char* WIFI_PASS = "12345678";
const int LOCAL_PORT  = 4210;

String SERVER_IP = "192.168.125.112";
uint16_t SERVER_PORT = 80;

const int PWMa = D1;
const int PWMb = D2;
const int WiB  = D3;
const int WiR  = D4;
const int ZiB  = D6;
const int ZiR  = D7;

int currentPower = 100; 

WiFiUDP udp;

void wyslijDoPHP(String Message);
void DriversSettings(char cmd);

void setup() {
  Serial.begin(115200);
  pinMode(LED_BUILTIN, OUTPUT);
  
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  while (WiFi.status() != WL_CONNECTED) delay(500);

  udp.begin(LOCAL_PORT);

  analogWriteRange(255);
  pinMode(PWMa, OUTPUT); pinMode(PWMb, OUTPUT);
  pinMode(WiB, OUTPUT); pinMode(WiR, OUTPUT);
  pinMode(ZiB, OUTPUT); pinMode(ZiR, OUTPUT);

  wyslijDoPHP("Start_Systemu");
}

void loop() {
  int packetSize = udp.parsePacket();
  if (packetSize) {
    char packetBuffer[128];
    int len = udp.read(packetBuffer, 127);
    if (len > 0) packetBuffer[len] = '\0';
    
    String cmd = String(packetBuffer);
    cmd.trim();

    if (cmd[0] == 'I') {
      String IP = "";
      String PORT = "";
      String ControllValue = "";
      bool BoolIP = true;

      for (unsigned int i = 2; i < cmd.length(); i++) {
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
      
    } else if (cmd.length() == 1) {
      char c = cmd[0];
      DriversSettings(c);
      
      if(c == 'W') wyslijDoPHP("MOVE_FORWARD");
      else if(c == 'S') wyslijDoPHP("MOVE_BACK");
      else if(c == 'A') wyslijDoPHP("MOVE_LEFT");
      else if(c == 'D') wyslijDoPHP("MOVE_RIGHT");
      else if(c == 'X') wyslijDoPHP("STOP");
      else if(c == '9') wyslijDoPHP("POWER_200");
      else if(c == '8') wyslijDoPHP("POWER_125");
      else if(c == '7') wyslijDoPHP("POWER_50");
      else wyslijDoPHP("CMD_ERR_" + String(c));
    }
  }

  static unsigned long ostatniCzas = 0;
  if (millis() - ostatniCzas > 10000) {
    ostatniCzas = millis();
    wyslijDoPHP("PING");
  }
}

void wyslijDoPHP(String Message) {
   udp.beginPacket(SERVER_IP.c_str(), SERVER_PORT);
   udp.print(Message);
   udp.endPacket();
}

void DriversSettings(char cmd) {
  if(cmd == 'W') {
    digitalWrite(WiB, LOW); digitalWrite(WiR, HIGH);
    digitalWrite(ZiB, LOW); digitalWrite(ZiR, HIGH);
    analogWrite(PWMa, currentPower); analogWrite(PWMb, currentPower);
  } else if(cmd == 'S') {
    digitalWrite(WiB, HIGH); digitalWrite(WiR, LOW);
    digitalWrite(ZiB, HIGH); digitalWrite(ZiR, LOW);
    analogWrite(PWMa, currentPower); analogWrite(PWMb, currentPower);
  } else if(cmd == 'A') {
    digitalWrite(WiB, HIGH); digitalWrite(WiR, LOW);
    digitalWrite(ZiB, LOW); digitalWrite(ZiR, LOW);
    analogWrite(PWMa, currentPower); analogWrite(PWMb, currentPower);
  } else if(cmd == 'D') {
    digitalWrite(WiB, LOW); digitalWrite(WiR, LOW);
    digitalWrite(ZiB, HIGH); digitalWrite(ZiR, LOW);
    analogWrite(PWMa, currentPower); analogWrite(PWMb, currentPower);
  } else if(cmd == 'X') {
    digitalWrite(WiB, LOW); digitalWrite(WiR, LOW);
    digitalWrite(ZiB, LOW); digitalWrite(ZiR, LOW);
    analogWrite(PWMa, 0); analogWrite(PWMb, 0);
  } else if(cmd == '9') {
    currentPower = 200;
  } else if(cmd == '8') {
    currentPower = 125;
  } else if(cmd == '7') {
    currentPower = 50;
  }
}