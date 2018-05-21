<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\ExecProvider;

class SystemController extends Controller
{
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }


    /** Системные данные */
    public function getHome () {
      return view('system');
    }

    /** Env данные */
    public function getEnv () {
      return response()->json([
        'PROXY_SERVER' => env('PROXY_SERVER'),
        'PROXY_PORT' => env('PROXY_PORT'),
        'PROXY_GROUP' => env('PROXY_GROUP'),
        'PROXY_USER' => env('SSH_USER'),
        'PROXY_PASSWORD' => env('SSH_PASSWORD'),
        'APP_PASSWORD' => env('APP_PASSWORD')
      ]);
    }

    /** Статус группы */
    public function getGroup () {
      // Статус (Есть или нет пользователь)
      $this->status = false;
      // Проверяем наличае группы
      ExecProvider::run([ 'grep "' . addslashes(env('PROXY_GROUP')) . ':*" /etc/group' ], function ($line) {
        if (trim($line) != '') {
          $this->status = true;
        }
      });
      // Возвращаем результат
      return response()->json($this->status);
    }

    /** Создание группы если её нету */
    public function postGroup () {
      // Создался ли пользователь
      $this->status = false;
      // Пробуем создать и проверяем создалась ли группа
      ExecProvider::run([
        'groupadd "' . addslashes(env('PROXY_GROUP')) . '" >/dev/null 2>/dev/null',
        'grep "' . addslashes(env('PROXY_GROUP')) . ':*" /etc/group'
      ], function ($line) {
        if (trim($line) != '') {
          $this->status = true;
        }
      });
      // Возвращаем результат
      return response()->json($this->status);
    }

    /** Статус подключения к серверу */
    public function getServer () {
      // Аптайм
      $this->uptime = false;
      // Получение аптайма сервера

      ExecProvider::run([ 'uptime' ], function ($line) {
        $this->uptime = $line;
      });
      // Возвращаем результат
      return response()->json($this->uptime);
    }

    /** Перезапустить Dante */
    public function postRestart () {
      ExecProvider::run([
        'sudo systemctl restart danted',
        '/etc/init.d/danted restart',
        'systemctl start sockd.service'
      ]);
    }

    /** Перезагрузка */
    public function postReboot () {
      ExecProvider::run([
        'sudo reboot'
      ]);
    }


}
