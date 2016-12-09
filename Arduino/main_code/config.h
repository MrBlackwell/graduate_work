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
  int board;
};

#define UNO -555
#ifdef UNO //если определена UNO,то :
      //массивы с распиновками под UNO
      int digital_pins_UNO[14] = {0,1,2,3,4,5,6,7,8,9,10,11,12,13}; 
      int analog_pins_UNO[6] = {0,1,2,3,4,5};

#else
      //массивы с распиновками под MEGA
      int digital_pins_MEGA[54] = {0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53};                                 
      int analog_pins_MEGA[16] = {0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15};
#endif
