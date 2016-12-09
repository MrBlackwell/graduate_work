#include "config.h"
#include <dht11.h>      // Добавляем библиотеку DHT11(температуры)

  //прототипы функций
  void download_config();


  //объявление класса current_config типа configuration
  configuration current_config;




// ~~~~~~~~~~~~~~~~~~~~~~~~~~МАССИВ ДАННЫХ С ДАТЧИКОВ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    dht11 DHT_array[10];
   
// ~~~~~~~~~~~~~~~~~~~~~~~~~~МАССИВ ДАННЫХ С ДАТЧИКОВ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~


void setup() 
{
  Serial.begin(9600);   // Скорость работы serial порта
  
  download_config(); // сначала скачиваем откуда-то конфигурацию устройства.(тут узнаем что будет подключено и сколько)
  current_config.board = MEGA; // используемая плата
  current_config.number_of_temperature_sensors = 1;  // запишем в конфиг число датчиков температуры(потом это надо будет убрать) ПРОБЛЕМА!!!
  
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~УСТАНОВИМ РАСПИНОВКУ ПОД ДАТЧИКИ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  if(current_config.board == UNO)
  {
        // распиновка под уну
        #define DHT11_zero 2     // Нулевой датчик DHT11 подключен к цифровому пину номер 2
        pinMode(DHT11_zero, INPUT);
  }
  else
  {
        // распиновка под мегу
        #define DHT11_zero 22     // Нулевой датчик DHT11 подключен к цифровому пину номер 2
        pinMode(DHT11_zero, INPUT);
    
    
  }
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~УСТАНОВИМ РАСПИНОВКУ ПОД ДАТЧИКИ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  
   
  
  
}

void loop() 
{
      delay(300);
      int chk = DHT_array[0].read(DHT11_zero);    // Чтение данных
      switch (chk)
      {
      case DHTLIB_OK:  
          break;
      case DHTLIB_ERROR_CHECKSUM:
          Serial.println("Checksum error, \t");
          break;
      case DHTLIB_ERROR_TIMEOUT:
          Serial.println("Time out error, \t");
          break;
      default:
          Serial.println("Unknown error, \t");
          break;
      }
  // Выводим показания влажности и температуры
  Serial.print("Vlashnost' = ");
  Serial.print(DHT_array[0].humidity, 1);
  Serial.print(", Temperatyra = ");
  Serial.println(DHT_array[0].temperature,1);
  
  

}


void download_config()
{
  
  
  
  
  
  
}

