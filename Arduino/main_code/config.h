#include <stdlib.h>
class configuration // класс описывающий количество подключенных датчиков различных типов
{
  public:
  int number_of_motion_sensors; // кол-во датчиков движения
  int number_of_fume_sensors; // кол-во датчиков дыма/газа
  int number_of_temperature_sensors; // кол-во датчиков температуры/влажности
  int number_of_gerkon_sensors; // кол-во датчиков открывания дверей
  int number_of_vibration_sensors; // кол-во датчиков вибрации
  int number_of_water_sensors; // кол-во датчиков воды
  int number_of_cams; // кол-во камер
};

#define UNO -555
#ifdef UNO //если определена UNO,то :
      //массивы с распиновками под UNO
      int *digitalPins=(int*)malloc(14 * sizeof(int));
      int *analogPins=(int*)malloc(6 * sizeof(int));
#else
      //массивы с распиновками под MEGA
      int *digitalPins=(int*)malloc(54 * sizeof(int));
      int *analogPins=(int*)malloc(16 * sizeof(int));
#endif
