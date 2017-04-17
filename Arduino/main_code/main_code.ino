#include <EEPROM.h>
#include <Adafruit_CC3000.h>
#include <dht11.h>
#include <ccspi.h>
#include <SPI.h>
#include <MFRC522.h>
#include <LiquidCrystal.h>
#include <MD5.h>
#include "utility/debug.h"

#include <Keypad.h>
const byte ROWS = 4; //four rows
const byte COLS = 4; //three columns
char keys[ROWS][COLS] =
{
  {'1', '2', '3', '4'},
  {'5', '6', '7', '8'},
  {'9', '0', '*', '#'},
  {'a', 'b', 'c', 'd'}
};
byte rowPins[ROWS] = {40, 39, 38, 37}; //connect to the row pinouts of the keypad
byte colPins[COLS] = {41, 42, 43, 44}; //connect to the column pinouts of the keypad
Keypad keypad = Keypad( makeKeymap(keys), rowPins, colPins, ROWS, COLS );



#define ADAFRUIT_CC3000_IRQ   3
#define ADAFRUIT_CC3000_VBAT  5
#define ADAFRUIT_CC3000_CS    10
Adafruit_CC3000 cc3000 = Adafruit_CC3000(ADAFRUIT_CC3000_CS, ADAFRUIT_CC3000_IRQ, ADAFRUIT_CC3000_VBAT, SPI_CLOCK_DIVIDER);

#define WLAN_SECURITY   WLAN_SEC_WPA2 // Security can be WLAN_SEC_UNSEC, WLAN_SEC_WEP, WLAN_SEC_WPA or WLAN_SEC_WPA2
#define IDLE_TIMEOUT_MS  500
#define WEBSITE      "securityathena.ru"
//#define WEBSITE      "http://securityathena.ru"

// Блок DEFINE
// Нужные DEFы
#define MAX_LENGHT 20 // максимальная длина полей SSID/PASS
#define LABEL_AMOUNT 44 // ячейка с данными о кол-ве меток
#define LAST_BUSY 45 // ячейка с данными о кол-ве меток

// Сигнальные
#define water_sensor 23
#define motion_sensor 24
#define fume_sensor 8
#define penetration_sensor 26
#define vibration_sensor 27

// Датчики с данными
#define temperature_sensor 22

// RGB светодиод
#define B_diod 28
#define G_diod 29
#define R_diod 30

#define BEEP_pin 45
#define close_diod 46
#define close_button 47
#define open_button 48
#define Cooler 25

// Пины под сенсорную панель
#define SCL_PIN 37
#define SDO_PIN 38

// Пины под метки
#define RST_PIN         5          // Configurable, see typical pin layout above
#define SS_PIN          53         // Configurable, see typical pin layout above

// Служебные пины
#define diagnostic_mode_pin 15

// прототипы функций
void send_recv();
void send_open_close(char* hash);
int check_sensors();
void activate_sensors();
char* connection_data();
char menu_choise();
void write_string_EEPROM (int Addr, char* Str);
char* read_string_EEPROM (int Addr, int lng);
void read_card();
void parser();
void write_label_EEPROM (char* label);
void delete_label_EEPROM (char* label);
int find_same_label(char* label);
void open_close();

//
dht11 DHT;
LiquidCrystal lcd(31, 32, 33, 34, 35, 36); // (RS, E, DB4, DB5, DB6, DB7)
MFRC522 rfid(SS_PIN, RST_PIN); // Instance of the class

uint8_t macAddress[6];
char parse[500];
char parse_activity[3];
int active_sensors[7];
int parse_counter = 0;
int cloze = 1;
int buffer_RFID_activity = 1;


void setup()
{
  Serial.begin(115200);
  SPI.begin(); // Init SPI bus

  digitalWrite(B_diod, HIGH);

  lcd.begin(16, 2);                  // Задаем размерность экрана


  // Распиновки под MEGA
  pinMode(temperature_sensor, INPUT);
  pinMode(water_sensor, INPUT);
  pinMode(motion_sensor, INPUT);
  pinMode(penetration_sensor, INPUT);
  pinMode(vibration_sensor, INPUT);

  pinMode(R_diod, OUTPUT); // RGB
  pinMode(G_diod, OUTPUT); // RGB
  pinMode(B_diod, OUTPUT); // RGB

  pinMode(close_diod, OUTPUT);
  pinMode(BEEP_pin, OUTPUT);
  pinMode(close_button, INPUT);
  pinMode(open_button, INPUT);
  pinMode(Cooler, OUTPUT);
  digitalWrite(Cooler, HIGH);

  /* Инициализация модуля */
  Serial.println();
  Serial.println(F("Initializing..."));
  if (!cc3000.begin())
  {
    Serial.println(F("Couldn't begin()! Check your wiring?"));
    while (1);
  }


  char WLAN_SSID[MAX_LENGHT];
  char WLAN_PASS[MAX_LENGHT];

  if (analogRead(diagnostic_mode_pin) < 50)
  { //mastermode
    lcd.print("Master mode");        // Выводим текст
    delay(1000);
    char* buf = (char*) malloc(MAX_LENGHT);
    char menu = '-';

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("1-Conn.data");       // Выводим текст
    lcd.setCursor(0, 1);
    lcd.print("2-RFID ; 3-Save");       // Выводим текст

    int set_default_conn = 1;
    while (menu != '3')
    {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("1-Conn.data");       // Выводим текст
      lcd.setCursor(0, 1);
      lcd.print("2-RFID ; 3-Exit");       // Выводим текст

      menu = menu_choise();

      switch (menu)
      { // выбор коннект инфо или рфид
        case '1':
          {
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Enter SSID");       // Выводим текст
            buf = connection_data();
            strcpy(WLAN_SSID, buf);
            free(buf);

            buf = (char*) malloc(MAX_LENGHT);
            lcd.setCursor(0, 0);
            lcd.clear();
            lcd.print("Enter PASS");       // Выводим текст
            buf = connection_data();
            strcpy(WLAN_PASS, buf);
            free(buf);
            EEPROM.write(0 , strlen(WLAN_SSID)); // в 0 ячейке длинна SSID
            EEPROM.write(1 , strlen(WLAN_PASS)); // в 1 ячейке длинна PASS
            write_string_EEPROM(2, WLAN_SSID);
            write_string_EEPROM(2 + strlen(WLAN_SSID), WLAN_PASS);
            break;
          }
        case '2':
          {
            read_card();
            break;
          }
        case '3':
          {
            if (set_default_conn == 1)
            {
              char* buf = (char*) malloc(MAX_LENGHT);
              buf = read_string_EEPROM(2 , EEPROM.read(0) );
              strcpy(WLAN_SSID, buf);

              buf = (char*) malloc(MAX_LENGHT);
              buf = read_string_EEPROM(2 + strlen(WLAN_SSID) , EEPROM.read(1) );
              strcpy(WLAN_PASS, buf);
              set_default_conn = 0;
              break;
            }
            else
            {
              break;
            }
          }
      }
    }
  }
  else
  { // usermode

    lcd.print("User mode");        // Выводим текст

    char* buf = (char*) malloc(MAX_LENGHT);
    buf = read_string_EEPROM(2 , EEPROM.read(0) );
    strcpy(WLAN_SSID, buf);

    buf = (char*) malloc(MAX_LENGHT);
    buf = read_string_EEPROM(2 + strlen(WLAN_SSID) , EEPROM.read(1) );
    strcpy(WLAN_PASS, buf);

  }

  Serial.print("SSID = "); Serial.println(WLAN_SSID);
  Serial.print("PASS = "); Serial.println(WLAN_PASS);

  Serial.print(F("Attempting to connect to ")); Serial.println(WLAN_SSID);
  if (!cc3000.connectToAP(WLAN_SSID, WLAN_PASS, WLAN_SECURITY))
  {
    Serial.println(F("Failed!"));
    while (1);
  }

  Serial.println(F("Connected!"));

  /* Wait for DHCP to complete */
  Serial.println(F("Request DHCP"));
  int reqs = 0;
  while (!cc3000.checkDHCP())
  {
    Serial.println(F("req"));
    delay(100); // ToDo: Insert a DHCP timeout!
    reqs++;
  }
  Serial.print(F("Request count = ")); Serial.print(reqs);
  Serial.println();

  /* Display the IP address DNS, Gateway, etc. */
  displayConnectionDetails();
  cc3000.getMacAddress(macAddress);

  digitalWrite(close_diod, HIGH);
  digitalWrite(BEEP_pin, HIGH);
  delay(100);
  digitalWrite(BEEP_pin, LOW);
  cloze = 1;
  char hash[33] = "00000000000000000000000000000000";
  send_open_close(hash);
  
  Serial.println("Setup finished");
}

void loop()
{
  send_recv();
  open_close();
}

void send_recv()
{
  Serial.println();
  Serial.println("Send_recv function");
  char WEBPAGE[100] = "/recvdata.php";
  char added_part[100] = "?id=";
  parse_counter = 0;
  char temp[5];


  // id
  sprintf(temp, "%d", macAddress[0]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[1]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[2]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[3]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[4]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[5]);
  strcat(added_part, temp);

  // температура
  strcat(added_part, "&wet=");
  DHT.read(temperature_sensor);
  sprintf(temp, "%d", DHT.temperature);
  if (active_sensors[5] == 1) strcat(added_part, temp); // если датчик температуры включен
  else strcat(added_part, "0");
  strcat(added_part, "/");
  sprintf(temp, "%d", DHT.humidity);
  if (active_sensors[5] == 1) strcat(added_part, temp);
  else strcat(added_part, "0");

  // сенсоры
  strcat(added_part, "&sensors=");
  sprintf(temp, "%d", check_sensors());
  strcat(added_part, temp);

  // собираем всё в одну строку
  strcat(WEBPAGE, added_part);


  uint32_t ip = 0;
  while (ip == 0)
  {
    cc3000.getHostByName(WEBSITE, &ip);
  }

  Adafruit_CC3000_Client www;

  while (! www.connected())
  {
    www = cc3000.connectTCP(ip, 80);
    Serial.println("Try to open TCP-connect");
  }
  Serial.println("Created TCP connect");

  // НАШ ЗАПРОС
  if (www.connected())
  {
    Serial.println();
    Serial.print(WEBSITE); Serial.println(WEBPAGE);
    digitalWrite(B_diod, LOW);
    www.fastrprint(F("POST "));
    www.fastrprint(WEBPAGE);
    www.fastrprint(F(" HTTP/1.1\r\n"));
    www.fastrprint(F("Host: "));
    www.fastrprint(WEBSITE);
    www.fastrprint(F("\r\n"));
    www.fastrprint(F("\r\n"));
    www.println();

    digitalWrite(G_diod, HIGH);
    delay(20);
    digitalWrite(G_diod, LOW);
    lcd.setCursor(0, 1);
    lcd.print("Connected");       // Выводим текст
    Serial.println("Request send");
  }
  else
  {
    Serial.println(F("Connection failed.I will reset now"));
    delay(20);
    send_recv();
    return;
  }

  // ПОЛУЧЕННЫЙ ОТВЕТ
  unsigned long lastRead = millis();
  while (www.connected() && (millis() - lastRead < IDLE_TIMEOUT_MS))
  {
    while (www.available())
    {
      char c = www.read();
      parse[parse_counter] = c;
      parse_counter++;
      lastRead = millis();
    }
  }
  Serial.println("Answer received");
  parser();
  www.close();
  Serial.println("Connection closed");
}

bool displayConnectionDetails(void)
{
  uint32_t ipAddress, netmask, gateway, dhcpserv, dnsserv;

  if (!cc3000.getIPAddress(&ipAddress, &netmask, &gateway, &dhcpserv, &dnsserv))
  {
    Serial.println(F("Unable to retrieve the IP Address! I will reset now\r\n"));
    return false;
  }
  else
  {
    Serial.print(F("\nIP Addr: ")); cc3000.printIPdotsRev(ipAddress);
    Serial.print(F("\nNetmask: ")); cc3000.printIPdotsRev(netmask);
    Serial.print(F("\nGateway: ")); cc3000.printIPdotsRev(gateway);
    Serial.print(F("\nDHCPsrv: ")); cc3000.printIPdotsRev(dhcpserv);
    Serial.print(F("\nDNSserv: ")); cc3000.printIPdotsRev(dnsserv);
    Serial.println();
    return true;
  }
}

int check_sensors()
{
  int result = 0;
  //if ((digitalRead(motion_sensor) == HIGH) && (active_sensors[4] == 1) && (cloze == 1)) result += 16;
  if ((analogRead(fume_sensor) > 850) && (active_sensors[3] == 1) && (cloze == 1)) result += 8;
  if ((digitalRead(water_sensor) == HIGH) && (active_sensors[2] == 1) && (cloze == 1)) result += 4;
  if ((digitalRead(vibration_sensor) == HIGH) && (active_sensors[1] == 1) && (cloze == 1)) result += 2;
  if ((digitalRead(penetration_sensor) == HIGH) && (active_sensors[0] == 1) && (cloze == 1)) result += 1;
  return result;
}

void activate_sensors()
{
  int num = atoi(parse_activity);

  for (int j = 0; j < 7; j++)
    active_sensors[j] = 0;

  int i = 0;
  while (num != 0)
  {
    int mod = num % 2;
    active_sensors[i] = mod;
    num = num / 2;
    i++;
  }

  if ((active_sensors[6] == 0) && (buffer_RFID_activity == 1))
  {
    digitalWrite(close_diod, HIGH);
    digitalWrite(BEEP_pin, HIGH);
    delay(100);
    digitalWrite(BEEP_pin, LOW);
    cloze = 1;
    char hash[33] = "00000000000000000000000000000000";
    send_open_close(hash);
  }

  buffer_RFID_activity = active_sensors[6];
}

char* connection_data()
{
  int cur_pos = 0; // позиция внутри массива result
  int pos_array[14] = {0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0}; // позиция внутри массива текущей буквы
  int i = 0;
  char result[17] = {'\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0'};

  char one_button[4] = {'A', 'a', 'B', 'b'};
  char two_button[6] = {'C', 'c', 'D', 'd', 'E', 'e'};
  char three_button[6] = {'F', 'f', 'G', 'g', 'H', 'h'};
  char four_button[4] = {'I', 'i', 'J', 'j'};
  char five_button[4] = {'K', 'k', 'L', 'l'};
  char six_button[4] = {'M', 'm', 'N', 'n'};
  char seven_button[4] = {'O', 'o', 'P', 'p'};
  char eight_button[4] = {'Q', 'q', 'R', 'r'};
  char nine_button[4] = {'S', 's', 'T', 't'};
  char ten_button[6] = {'U', 'u', 'V', 'v', 'W', 'w'};
  char eleven_button[6] = {'X', 'x', 'Y', 'y', 'Z', 'z'};
  char twelwe_button[8] = {'!', '?', '@', '#', '$', '%', '&', '_'};
  char thirteen_button[9] = {'(', ')', '-', '+', '=', '|', '^', ':', ';'};
  char fourteen_button[10] = {'1', '2', '3', '4', '5', '6', '7', '8', '9', '0'};


  int flg = 1; // чтобы 2 нажатия перехода на след. букву не работали.
  while (1)
  {
    switch (keypad.getKey())
    {
      case '1':
        {
          result[cur_pos] = one_button[pos_array[0]];
          if (pos_array[0] != 3)
            pos_array[0]++;
          else pos_array[0] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 0)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '2':
        {
          result[cur_pos] = two_button[pos_array[1]];
          if (pos_array[1] != 5)
            pos_array[1]++;
          else pos_array[1] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 1)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '3':
        {
          result[cur_pos] = three_button[pos_array[2]];
          if (pos_array[2] != 5)
            pos_array[2]++;
          else pos_array[2] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 2)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '4':
        {
          result[cur_pos] = four_button[pos_array[3]];
          if (pos_array[3] != 3)
            pos_array[3]++;
          else pos_array[3] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 3)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '5':
        {
          result[cur_pos] = five_button[pos_array[4]];
          if (pos_array[4] != 3)
            pos_array[4]++;
          else pos_array[4] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 4)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '6':
        {
          result[cur_pos] = six_button[pos_array[5]];
          if (pos_array[5] != 3)
            pos_array[5]++;
          else pos_array[5] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 5)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '7':
        {
          result[cur_pos] = seven_button[pos_array[6]];
          if (pos_array[6] != 3)
            pos_array[6]++;
          else pos_array[6] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 6)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '8':
        {
          result[cur_pos] = eight_button[pos_array[7]];
          if (pos_array[7] != 3)
            pos_array[7]++;
          else pos_array[7] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 7)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }

      case '9':
        {
          result[cur_pos] = nine_button[pos_array[8]];
          if (pos_array[8] != 3)
            pos_array[8]++;
          else pos_array[8] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 8)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '0':
        {
          result[cur_pos] = ten_button[pos_array[9]];
          if (pos_array[9] != 5)
            pos_array[9]++;
          else pos_array[9] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 9)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '*':
        {
          result[cur_pos] = eleven_button[pos_array[10]];
          if (pos_array[10] != 5)
            pos_array[10]++;
          else pos_array[10] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 10)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case '#':
        {
          result[cur_pos] = twelwe_button[pos_array[11]];
          if (pos_array[11] != 7)
            pos_array[11]++;
          else pos_array[11] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 11)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case 'a':
        {
          result[cur_pos] = thirteen_button[pos_array[12]];
          if (pos_array[12] != 8)
            pos_array[12]++;
          else pos_array[12] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 12)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case 'b':
        {
          result[cur_pos] = fourteen_button[pos_array[13]];
          if (pos_array[13] != 9)
            pos_array[13]++;
          else pos_array[13] = 0;

          lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
          lcd.print(result);        // Выводим текст

          for (i = 0; i < 14; i++)
          {
            if (i != 13)
              pos_array[i] = 0;
          }
          flg = 0;
          break;
        }
      case 'c':
        {
          if (flg == 0)
          {
            cur_pos++;
            flg++;
          }
          break;
        }

      case 'd':
        {
          return result;
          break;
        }
    }
  }
}

char menu_choise()
{
  while (1)
  {
    char key = keypad.getKey();
    if (key) return key;
  }
}


void write_string_EEPROM (int Addr, char* Str)
{
  int lng = strlen(Str);
  int i;
  for (i = 0; i < lng; i++)
  {
    EEPROM.write(Addr + i, Str[i]);
    delay(10);
  }
}


char* read_string_EEPROM (int Addr, int lng)
{
  char* buf = (char*) malloc(lng);

  int i;
  for (i = 0; i < lng; i++)
  {
    buf[i] = char(EEPROM.read(Addr + i));
  }
  buf[i] = '\0';
  return buf;
}

void read_card()
{
  rfid.PCD_Init(); // Init MFRC522
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Attach card");       // Выводим текст

  for (int i = 0; i < 250; i++)
  {
    // Look for new cards
    if ( ! rfid.PICC_IsNewCardPresent())
      continue;
    // Verify if the NUID has been readed
    if ( ! rfid.PICC_ReadCardSerial())
      continue;
    else
    {
      char card_num[13];
      char buf[3];
      sprintf(buf, "%d", rfid.uid.uidByte[0]);
      strcpy(card_num, buf);
      sprintf(buf, "%d", rfid.uid.uidByte[1]);
      strcat(card_num, buf);
      sprintf(buf, "%d", rfid.uid.uidByte[2]);
      strcat(card_num, buf);
      sprintf(buf, "%d", rfid.uid.uidByte[3]);
      strcat(card_num, buf);

      digitalWrite(BEEP_pin, HIGH);
      delay(100);
      digitalWrite(BEEP_pin, LOW);

      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print(card_num);       // Выводим текст
      lcd.setCursor(0, 1);
      lcd.print("1-Exit");       // Выводим текст
      while (menu_choise() != '1')
      {
      }
      break;
    }
  }
}

void parser()
{
  char hash[33];
  for (int i = 0; i < 32; i++)
  {
    hash[i] = parse[parse_counter - 35 + i];
  }
  hash[32] = '\0';
  //Serial.println("Hash = "); Serial.println(hash);

  char controlbit = parse[parse_counter - 36];

  //Serial.println("Control bit = "); Serial.println(controlbit);

  switch (controlbit)
  {
    case '0':
      { // ничего не делаем
        break;
      }
    case '1':
      { // записываем в EEPROM
        write_label_EEPROM(hash);
        break;
      }
    case '2':
      { // удаляем из EEPROM
        delete_label_EEPROM(hash);
        break;
      }
  }

  // блок который отвечает за активацию сенсоров
  parse_activity[2] = parse[parse_counter - 1];
  parse_activity[1] = parse[parse_counter - 2];
  parse_activity[0] = parse[parse_counter - 3];
  //Serial.println("Pars act = "); Serial.println(parse_activity);
  activate_sensors();
  //
}

void write_label_EEPROM(char* label)
{
  if (find_same_label(label) == 0) {

    if (EEPROM.read(LABEL_AMOUNT) == 0)
    { // если в начало пишем
      write_string_EEPROM (LAST_BUSY + 1, label);
      EEPROM.write(LABEL_AMOUNT, 1);
      EEPROM.write(LAST_BUSY, LAST_BUSY + 32);
    }
    else
    { // не в начало
      int writed_labels;
      writed_labels = (EEPROM.read(LAST_BUSY) - LAST_BUSY) / 32;

      if (writed_labels > EEPROM.read(LABEL_AMOUNT))
      { // если есть пустое место в середине
        for (int i = LAST_BUSY + 1; i < EEPROM.read(LAST_BUSY); i += 32)
        {
          if (EEPROM.read(i) == 0)
          {
            write_string_EEPROM (i, label);
            EEPROM.write(LABEL_AMOUNT, EEPROM.read(LABEL_AMOUNT) + 1);
            break;
          }
        }
      }
      else
      { // пишем в конец
        write_string_EEPROM (EEPROM.read(LAST_BUSY) + 1, label);
        EEPROM.write(LABEL_AMOUNT, EEPROM.read(LABEL_AMOUNT) + 1);
        EEPROM.write(LAST_BUSY, EEPROM.read(LAST_BUSY) + 32);
      }
    }

  }
  else return;
}

void delete_label_EEPROM (char* label)
{
  for (int i = LAST_BUSY + 1; i < EEPROM.read(LAST_BUSY); i += 32)
  {
    if (EEPROM.read(i) == label[0])
      if (EEPROM.read(i + 1) == label[1])
        if (EEPROM.read(i + 2) == label[2])
          if (EEPROM.read(i + 3) == label[3])
          {
            for (int j = i; j < i + 32; j++)
            {
              EEPROM.write(j, 0);
            }
            EEPROM.write(LABEL_AMOUNT, EEPROM.read(LABEL_AMOUNT) - 1);

            if ((i + 31) == EEPROM.read(LAST_BUSY))
            {
              EEPROM.write((LAST_BUSY), i - 1);
            }
          }
  }
}

int find_same_label(char* label)
{
  int reply = 0;
  for (int i = LAST_BUSY + 1; i < EEPROM.read(LAST_BUSY); i += 32)
  {
    if (EEPROM.read(i) == label[0])
      if (EEPROM.read(i + 1) == label[1])
        if (EEPROM.read(i + 2) == label[2])
          if (EEPROM.read(i + 3) == label[3])
          {
            reply = 1;
            return reply;
          }
  }
  return reply;
}

void open_close()
{
  if ((digitalRead(close_button) == HIGH) && (active_sensors[6] == 1))
  {
    digitalWrite(close_diod, HIGH);
    digitalWrite(BEEP_pin, HIGH);
    delay(100);
    digitalWrite(BEEP_pin, LOW);
    cloze = 1;
    char hash[33] = "00000000000000000000000000000000";
    send_open_close(hash);
  }

  if ((digitalRead(open_button) == HIGH) && (active_sensors[6] == 1))
  {
    digitalWrite(BEEP_pin, HIGH);
    delay(100);
    digitalWrite(BEEP_pin, LOW);
    rfid.PCD_Init(); // Init MFRC522
    for (int i = 0; i < 250; i++)
    {
      if ( ! rfid.PICC_IsNewCardPresent())
        continue;
      // Verify if the NUID has been readed
      if ( ! rfid.PICC_ReadCardSerial())
        continue;
      else
      {
        char card_num[13];
        char buf[3];
        sprintf(buf, "%d", rfid.uid.uidByte[0]);
        strcpy(card_num, buf);
        sprintf(buf, "%d", rfid.uid.uidByte[1]);
        strcat(card_num, buf);
        sprintf(buf, "%d", rfid.uid.uidByte[2]);
        strcat(card_num, buf);
        sprintf(buf, "%d", rfid.uid.uidByte[3]);
        strcat(card_num, buf);

        unsigned char* hash = MD5::make_hash(card_num);
        char* md5str = MD5::make_digest(hash, 16);
        if (find_same_label(md5str) == 1)
        {
          digitalWrite(close_diod, LOW);
          digitalWrite(BEEP_pin, HIGH);
          delay(100);
          digitalWrite(BEEP_pin, LOW);
          delay(100);
          cloze = 0;
          send_open_close(md5str);
        }
        break;
      }
    }
    digitalWrite(BEEP_pin, HIGH);
    delay(100);
    digitalWrite(BEEP_pin, LOW);
  }
}

void send_open_close(char* hash)
{
  Serial.println();
  Serial.println("Send open_close function");
  char WEBPAGE[100] = "/recvdata.php";
  char added_part[100] = "?id=";
  parse_counter = 0;
  char temp[5];

  // id
  sprintf(temp, "%d", macAddress[0]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[1]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[2]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[3]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[4]);
  strcat(added_part, temp);
  sprintf(temp, "%d", macAddress[5]);
  strcat(added_part, temp);

  // open
  strcat(added_part, "&close=");
  sprintf(temp, "%d", cloze);
  strcat(added_part, temp);

  // hash
  strcat(added_part, "&hash=");
  strcat(added_part, hash);

  // собираем всё в одну строку
  strcat(WEBPAGE, added_part);

  uint32_t ip = 0;
  while (ip == 0)
  {
    cc3000.getHostByName(WEBSITE, &ip);
  }

  Adafruit_CC3000_Client www;

  while (! www.connected())
  {
    www = cc3000.connectTCP(ip, 80);
    Serial.println("Try to open TCP-connect");
  }
  Serial.println("Created TCP connect");

  // НАШ ЗАПРОС
  if (www.connected())
  {
    Serial.println();
    Serial.print(WEBSITE); Serial.println(WEBPAGE);
    digitalWrite(B_diod, LOW);
    www.fastrprint(F("POST "));
    www.fastrprint(WEBPAGE);
    www.fastrprint(F(" HTTP/1.1\r\n"));
    www.fastrprint(F("Host: "));
    www.fastrprint(WEBSITE);
    www.fastrprint(F("\r\n"));
    www.fastrprint(F("\r\n"));
    www.println();

    digitalWrite(G_diod, HIGH);
    delay(20);
    digitalWrite(G_diod, LOW);
    lcd.setCursor(0, 1);
    lcd.print("Connected");       // Выводим текст
    Serial.println("Request send");
  }
  else
  {
    Serial.println(F("Connection failed.I will reset now"));
    delay(20);
    send_recv();
    return;
  }
  // ПОЛУЧЕННЫЙ ОТВЕТ
  unsigned long lastRead = millis();
  while (www.connected() && (millis() - lastRead < IDLE_TIMEOUT_MS))
  {
    while (www.available())
    {
      char c = www.read();
      lastRead = millis();
    }
  }
  Serial.println("Answer received");
  www.close();
  Serial.println("Connection closed");
}




