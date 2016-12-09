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
  //Current_config.board = UNO;
  
  #ifdef UNO //если определена UNO,то :
      //распиновки под UNO
      pinMode(digital_pins_UNO[2],INPUT);  // температура
      pinMode(digital_pins_UNO[5],INPUT);  // движение
      pinMode(digital_pins_UNO[6],OUTPUT); // диод
  #else
      //распиновки под MEGA
      pinMode(digital_pins_MEGA[2],INPUT);  // температура
      pinMode(digital_pins_MEGA[5],INPUT);  // движение
      pinMode(digital_pins_MEGA[6],OUTPUT); // диод          
  #endif
  
}

void loop() 
{
  //delay(300);
  DHT.read(digital_pins_UNO[2]);
  Serial.print("Humidity = ");
  Serial.print(DHT.humidity, 1);
  Serial.print(", Temperature = ");
  Serial.println(DHT.temperature,1); 
  
  if(digitalRead(digital_pins_UNO[5]))
  digitalWrite(digital_pins_UNO[6],HIGH);
  else digitalWrite(digital_pins_UNO[6],LOW);
  
  
  

}


void download_config()
{
  
  
  
  
}

