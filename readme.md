# Dante Web GUI
Dante-GUI - Это графический-web инструмент для комфортной работы с [Dante-Server](https://www.inet.no/dante/), главной задачей которого является управление пользователями.

Список пользователей       |  Cистема                 |  Данные
:-------------------------:|:------------------------:|:-------------------------:
![Список пользователей](https://user-images.githubusercontent.com/10038023/39105294-b90d6b26-46bd-11e8-963f-d03e5c7bcac3.png)  |  ![Cистема](https://user-images.githubusercontent.com/10038023/39105311-ccaca73c-46bd-11e8-8bc4-eaffce861200.png)  |  ![Данные](https://user-images.githubusercontent.com/10038023/39105307-c8aba5c0-46bd-11e8-8ba1-50afe639c63d.png)
[Больше скриншотов](https://github.com/JsonDeveloper/dante-gui/wiki/%D0%A1%D0%BA%D1%80%D0%B8%D0%BD%D1%88%D0%BE%D1%82%D1%8B---Screenshots)
***

## Предисловие
Пример установки и настройки написан по Debian 9 (Установка на Ubuntu не должна принципиально отличаться). 

## Установка (Debian 9) - Dante-Server
Для начала обновим репозитории и систему
```
# sudo apt-get update & sudo apt-get upgrade
```
Теперь устанавливаем сам Dante-Server: 
```
# sudo apt-get install dante-server
```
Далее нужно поправить конфиг Dante (Если вы впервые видете консоль, просто приведите содержимое файла к ниже данному виду):
```
# nano /etc/danted.conf
```
```
logoutput: stderr
# Порт Proxy сервера
# eth0 - Ваш сетевой интерфейс (Обычно eth0, но может отличатся, например ens3, см. # ip addr)
internal: eth0 port = 1080
external: eth0

method: username
user.privileged: root
user.notprivileged: nobody
user.libwrap: nobody

client pass {
        from: 0.0.0.0/0 to: 0.0.0.0/0
        log: error
}

pass {
        from: 0.0.0.0/0 to: 0.0.0.0/0
        log: error
}

```
Запускаем Dante-Server
```
# sudo systemctl restart danted
```

## Установка Dante-GUI
Для установки Dante-GUI, нам понадобится установить на сервер Apache 2 (Или например Nginx), PHP >= 7.1.3, MySQL сервер. 
Подробнее с требованиями можно ознакомиться на странице [Laravel Установка](https://laravel.com/docs/5.6/installation).

В папке где вы собираетесь устанавливать скрипт выполните команду:
```
# git clone https://github.com/JsonDeveloper/dante-gui ./
```
Теперь в этой же папке выполняем следующие команды (Не забудьте предварительно установить composer):
```
# composer update
# php artisan key:generate
```
Далее открываеем файл .env для редактирования
```
# nano .env
```
Тут изменяем следующие строки:                          
**APP_URL** - Полный URL до вашей панели                        
**APP_PASSWORD** - Пароль для входа в Админ Панель      

**PROXY_SERVER** - Адрес сервера где настроен dante (Домен/IP)
**PROXY_PORT** - Порт dante прокси (Его мы указывали выше при настройке)   

**USE_SSH** - Использовать SSH для управления демоном dante (Или потребуется запустить процесс php от root-пользователя)
**SSH_SERVER** - Адрес сервера где настроен dante (Домен/IP)
**SSH_USER** - Имя пользователя с root правами (Или что бы мог просто создавать/удалять/изменять пользоватлей)    
**SSH_PASSWORD** - Пароль от пользователя с root правами  

**MAIL\*** - Смотрим документацию к Laravel, там описано подключение сервисов отправки email (Если вам это нужно)               
**DB_DATABASE** - Название базы данных (Создайте)                               
**DB_USERNAME** - Пользователь базы данных                                      
**DB_PASSWORD** - Пароль базы данных                                    
Теперь снова выполняем следующую команду:                                       
```
# php artisan migrate
```
На этом всё. Пишите свои замечания по описанию, постараюсь раскрыть лучше.
