#include <Adafruit_CC3000.h>
#include <dht11.h>      // Добавляем библиотеку DHT11(температуры)
#include <ccspi.h>
#include <SPI.h>
#include <TTP229.h>
#include <LiquidCrystal.h>  // Лобавляем необходимую библиотеку
#include <string.h>
#include "utility/debug.h"

#define ADAFRUIT_CC3000_IRQ   3
#define ADAFRUIT_CC3000_VBAT  5
#define ADAFRUIT_CC3000_CS    10
Adafruit_CC3000 cc3000 = Adafruit_CC3000(ADAFRUIT_CC3000_CS, ADAFRUIT_CC3000_IRQ, ADAFRUIT_CC3000_VBAT, SPI_CLOCK_DIVIDER);

#define WLAN_SECURITY   WLAN_SEC_WPA2 // Security can be WLAN_SEC_UNSEC, WLAN_SEC_WEP, WLAN_SEC_WPA or WLAN_SEC_WPA2
#define IDLE_TIMEOUT_MS  1000
#define WEBSITE      "www.m92910dr.bget.ru"

// Блок DEFINE
// Сигнальные
#define water_sensor 23
#define motion_sensor 24
#define fume_sensor 25
#define penetration_sensor 26
#define vibration_sensor 27

// Датчики с данными
#define temperature_sensor 22

// RGB светодиод
#define B_diod 28
#define G_diod 29
#define R_diod 30

// Пины под сенсорную панель
#define SCL_PIN 37
#define SDO_PIN 38


// прототипы функций
void send_recv();
char* connection_data();

//
dht11 DHT;
TTP229 ttp229(SCL_PIN, SDO_PIN); // TTP229(sclPin, sdoPin)
LiquidCrystal lcd(31, 32, 33, 34, 35, 36); // (RS, E, DB4, DB5, DB6, DB7)
uint32_t ip;
Adafruit_CC3000_Client www;
int counter = 0;
char parse[300];
char sensors_data[3];

void setup()
{
  // Зажигаем диод сигнализирующий о подаче питания
  digitalWrite(B_diod, HIGH);

  Serial.begin(115200);
  lcd.begin(16, 2);                  // Задаем размерность экрана
  
  // Распиновки под MEGA
  pinMode(temperature_sensor, INPUT);
  pinMode(water_sensor, INPUT);
  pinMode(motion_sensor, INPUT);
  pinMode(fume_sensor, INPUT);
  pinMode(penetration_sensor, INPUT);
  pinMode(vibration_sensor, INPUT);

  pinMode(R_diod, OUTPUT); // RGB
  pinMode(G_diod, OUTPUT); // RGB
  pinMode(B_diod, OUTPUT); // RGB


  /* Инициализация модуля */
  Serial.println();
  Serial.println(F("Initializing..."));
  if (!cc3000.begin())
  {
    Serial.println(F("Couldn't begin()! Check your wiring?"));
    while (1);
  }
  
  
  char* buf = (char*) malloc(20);
  
  char WLAN_SSID[20];
  char WLAN_PASS[20]; 
  //char WLAN_SSID[20] = "vimicher";
  //char WLAN_PASS[20] = "04051957"; 
  
  
  lcd.setCursor(0, 0);              // Устанавливаем курсор в начало 1 строки
  lcd.print("Enter SSID");       // Выводим текст
  buf = connection_data();
  strcpy(WLAN_SSID, buf);
  free(buf);
  
  buf = (char*) malloc(20);
  lcd.setCursor(0, 0);              // Устанавливаем курсор в начало 1 строки
  lcd.clear();
  lcd.print("Enter PASS");       // Выводим текст
  buf = connection_data();
  strcpy(WLAN_PASS, buf);
  free(buf);
  
  
  Serial.print("SSID = ");Serial.println(WLAN_SSID);
  Serial.print("PASS = ");Serial.println(WLAN_PASS);
  
  Serial.print(F("Attempting to connect to ")); Serial.println(WLAN_SSID);
  if (!cc3000.connectToAP(WLAN_SSID, WLAN_PASS, WLAN_SECURITY))
  {
    Serial.println(F("Failed!"));
    while (1);
  }

  Serial.println(F("Connected!"));

  /* Wait for DHCP to complete */
  Serial.println(F("Request DHCP"));
  while (!cc3000.checkDHCP())
  {
    Serial.println(F("req"));
    delay(100); // ToDo: Insert a DHCP timeout!
  }
}

void loop()
{
  send_recv();
  

}

void send_recv()
{
  char WEBPAGE[30] = "/onoff.php";
  char added_part[25] = "?sensors=";

  ip = 0;
  // Try looking up the website's IP address
  while (ip == 0)
  {
    if (! cc3000.getHostByName(WEBSITE, &ip))
    {
      Serial.println(F("Couldn't resolve!"));
    }
    delay(500);
  }

  www = cc3000.connectTCP(ip, 80);



  // тут надо считывать значения в 5 разрядов + переводить
  char temp[2];
  sprintf(temp, "%d", check_sensors());
  Serial.print("CHECKSENSORS = "); Serial.println(temp);
  strcat(added_part, temp);

  strcat(added_part, "&wet=");
  DHT.read(temperature_sensor);
  sprintf(temp, "%d", DHT.temperature);
  strcat(added_part, temp);
  strcat(added_part, "/");
  sprintf(temp, "%d", DHT.humidity);
  strcat(added_part, temp);
  strcat(WEBPAGE, added_part);
  Serial.print("WEBPAGE = "); Serial.println(WEBPAGE);


  if (www.connected())
  {
    www.fastrprint(F("POST "));
    www.fastrprint(WEBPAGE);
    www.fastrprint(F(" HTTP/1.1\r\n"));
    www.fastrprint(F("Host: "));
    www.fastrprint(WEBSITE);
    www.fastrprint(F("\r\n"));
    www.fastrprint(F("\r\n"));
    www.println();
    digitalWrite(B_diod, LOW);
    digitalWrite(G_diod, HIGH);
    lcd.clear();
    lcd.print("Connected ^_^");       // Выводим текст
  }
  else
  {
    Serial.println(F("Connection failed"));
    return;
  }


  /* Read data until either the connection is closed, or the idle timeout is reached. */
  unsigned long lastRead = millis();
  while (www.connected() && (millis() - lastRead < IDLE_TIMEOUT_MS))
  {
    int counter_1 = 0;
    while (www.available())
    {
      char c = www.read();
      parse[counter_1] = c;
      counter_1++;
      lastRead = millis();
    }

    sensors_data[2] = parse[counter_1 - 1];
    sensors_data[1] = parse[counter_1 - 2];
    sensors_data[0] = parse[counter_1 - 3];
  }

  www.close();

}

int check_sensors()
{
    int result=0; 
    //if(digitalRead(water_sensor) == HIGH) result += 16;
    //if(digitalRead(motion_sensor) == HIGH) result += 8;
    //if(digitalRead(fume_sensor) == HIGH) result += 4;
    //if(digitalRead(penetration_sensor) == HIGH) result += 2;
    //if(digitalRead(vibration_sensor) == HIGH) result += 1;
    return result;
}

char* connection_data()
{
    char result[17] = {'\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0', '\0'};
    int cur_pos = 0;
    int pos_array[6] = {0,0,0,0,0,0};
    int i = 0;
    
    char one_button[10] = {'A','a','B','b','C','c','D','d','E','e'};
    char two_button[10] = {'F','f','G','g','H','h','I','i','J','j'};
    char three_button[10] = {'K','k','L','l','M','m','N','n','O','o'};
    char four_button[10] = {'P','p','Q','q','R','r','S','s','T','t'};
    char five_button[12] = {'U','u','V','v','W','w','X','x','Y','y','Z','z'};
    char six_button[10] = {'0','1','2','3','4','5','6','7','8','9'};
    while(1)
    {      
        switch(ttp229.ReadKey16())
        {
            case 9:
                  {                      
                        result[cur_pos] = one_button[pos_array[0]];
                        if(pos_array[0] != 9) pos_array[0]++; else pos_array[0] = 0;
                        
                        lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
                        lcd.print(result);        // Выводим текст
                        for(i=0;i<6;i++)
                        {
                            if(i != 0)
                            pos_array[i] = 0;
                        }                        
                        break;
                   }
             case 10:
                  {
                        result[cur_pos] = two_button[pos_array[1]];
                        if(pos_array[1] != 9) pos_array[1]++; else pos_array[1] = 0;
                        
                        lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
                        lcd.print(result);        // Выводим текст    
                        for(i=0;i<6;i++)
                        {
                            if(i != 1)
                            pos_array[i] = 0;
                        }                          
                        break;
                   }
             case 11:
                  {
                        result[cur_pos] = three_button[pos_array[2]];
                        if(pos_array[2] != 9) pos_array[2]++; else pos_array[2] = 0;
                        
                        lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
                        lcd.print(result);        // Выводим текст
                        for(i=0;i<6;i++)
                        {
                            if(i != 2)
                            pos_array[i] = 0;
                        }                          
                        break;
                   }
             case 12:
                  {
                        result[cur_pos] = four_button[pos_array[3]];
                        if(pos_array[3] != 9) pos_array[3]++; else pos_array[3] = 0;
                        
                        lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
                        lcd.print(result);        // Выводим текст     
                        for(i=0;i<6;i++)
                        {
                            if(i != 3)
                            pos_array[i] = 0;
                        }                          
                        break;
                   }
             case 13:
                  {
                        result[cur_pos] = five_button[pos_array[4]];
                        if(pos_array[4] != 9) pos_array[4]++; else pos_array[4] = 0;
                        
                        lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
                        lcd.print(result);        // Выводим текст    
                        for(i=0;i<6;i++)
                        {
                            if(i != 4)
                            pos_array[i] = 0;
                        }                          
                        break;
                   }
             case 14:
                  {
                        result[cur_pos] = six_button[pos_array[5]];
                        if(pos_array[5] != 9) pos_array[5]++; else pos_array[5] = 0;
                        
                        lcd.setCursor(0, 1);              // Устанавливаем курсор в начало 2 строки
                        lcd.print(result);        // Выводим текст  
                        for(i=0;i<6;i++)
                        {
                            if(i != 5)
                            pos_array[i] = 0;
                        }                          
                        break;
                   }
             case 15:
                  {
                        cur_pos++;                                                                  
                        break;
                  }
                                                    
             case 16:
                  {
                        return result;                                                                  
                        break;
                  }
        }
    }
}



