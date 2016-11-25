#define UNO -1
#define MEGA -2

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
  int board; // что за плату используем
};
