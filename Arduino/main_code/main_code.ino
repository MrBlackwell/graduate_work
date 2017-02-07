#include "config.h"
#include <dht11.h>      // Добавляем библиотеку DHT11(температуры)

  //прототипы функций
  void download_config();


  //объявление класса current_config типа configuration
  configuration Current_config;
  dht11 DHT;       
void setup() 
{
  Serial.begin(9600);   // Скорость работы serial порта
  
  download_config(); // сначала скачиваем откуда-то конфигурацию устройства.(тут узнаем что будет подключено и сколько)
  Current_config.number_of_temperature_sensors = 1;  // запишем в конфиг число датчиков температуры(потом это надо будет убрать) ПРОБЛЕМА!!!
  #ifdef UNO //если определена UNO,то :
      //массивы с распиновками под UNO
      digitalPins[2] = 2;             // температура
      digitalPins[5] = 5;             // движение
      digitalPins[6] = 6;             // диод  
      pinMode(digitalPins[2],INPUT);  // температура
      pinMode(digitalPins[5],INPUT);  // движение
      pinMode(digitalPins[6],OUTPUT); // диод      
  #else
      //массивы с распиновками под MEGA
      pinMode(digitalPins[2],INPUT);  // температура
      pinMode(digitalPins[5],INPUT);  // движение
      pinMode(digitalPins[6],OUTPUT); // диод    
  #endif
}

void loop() 
{
  /*
  delay(300);
  DHT.read(digitalPins[2]);
  Serial.print("Humidity = ");
  Serial.print(DHT.humidity, 1);
  Serial.print(", Temperature = ");
  Serial.println(DHT.temperature,1); 
  */
  if(digitalRead(digitalPins[5]))
  digitalWrite(digitalPins[6],HIGH);
  else digitalWrite(digitalPins[6],LOW);
  
  
  

}


void download_config()
{
  
  
  
  
}

