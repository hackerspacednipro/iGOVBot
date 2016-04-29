##iGOVBot
iGOVBot — открытый Telegram bot для работы с реестрами украинских компаний. Наш беклог —  https://trello.com/b/7Ge4firz/igovbot

Хотите помочь нам реализовать наш беклог — пишите нам на https://telegram.me/iva220


######Требования
PHP Phalcon + MySQL

######Установка
Создать базу k11, разместить в ней дамп start.sql, положить в поле bots.token токен тестового бота

######Запуск
curl "http://k11.dev/bot?bot_id=2&password=CNeLA6B2BGdD" -d "{\"update_id\":460579724,\"message\":{\"message_id\":4,\"from\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\"},\"chat\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\",\"type\":\"private\"},\"date\":1458910208,\"text\":\"\/start\"}}"

######Поиск
curl "http://k11.dev/bot?bot_id=2&password=CNeLA6B2BGdD" -d "{\"update_id\":460579724,\"message\":{\"message_id\":4,\"from\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\"},\"chat\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\",\"type\":\"private\"},\"date\":1458910208,\"text\":\"РОСТОК\"}}"

в результате ваш тестовый бот выведет список компаний
