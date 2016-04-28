##iGOVBot
iGOVBot — открытый Telegram bot для работы с реестрами украинских компаний. Вопросы, помощь, поддержка — https://telegram.me/iva220

#требования
PHP Phalcon + MySQL

#установка
Создать базу k11, разместить в ней дамп start.sql, положить в поле bots.token положить токен тестового бота

#запуск
curl "http://k11.dev/bot?bot_id=2&password=CNeLA6B2BGdD" -d "{\"update_id\":460579724,\"message\":{\"message_id\":4,\"from\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\"},\"chat\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\",\"type\":\"private\"},\"date\":1458910208,\"text\":\"\/start\"}}"

#поиск
curl "http://k11.dev/bot?bot_id=2&password=CNeLA6B2BGdD" -d "{\"update_id\":460579724,\"message\":{\"message_id\":4,\"from\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\"},\"chat\":{\"id\":88492628,\"first_name\":\"Aleksey\",\"last_name\":\"Ivankin\",\"username\":\"iva220\",\"type\":\"private\"},\"date\":1458910208,\"text\":\"РОСТОК\"}}"