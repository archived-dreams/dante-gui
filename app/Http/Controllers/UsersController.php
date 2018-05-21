<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProxyAccess;
use App\User;
use App\ExecProvider;

class UsersController extends Controller
{
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }


    /** Список пользователей */
    public function getHome () {
      $users = User::paginate(7);
      return view('users', [ 'users' => $users ]);
    }

    /** Создание пользователя */
    public function postCreate (Request $request) {

      // Валидация данных
      $request->validate([
        'user' => 'required|unique:users|min:5|max:20|alpha_num',
        'email' => 'nullable|email',
        'comment' => 'max:255',
      ]);

      // Создание пользователя
      $user = User::create($request->only([ 'user', 'email', 'comment' ]));

      // Если произошла ошибка при создании пользователя
      if (!$user) {
        return response()->json([
          'error' => 'Неудалось занести пользователя в базу данных'
        ], Response::HTTP_BAD_REQUEST);
      }

      // Создание пользователя на сервере
      $this->created = true;
      ExecProvider::run([
        'sudo useradd --shell /usr/sbin/nologin --gid "' . addslashes(env('PROXY_GROUP')) . '" "' . addslashes($user->user) . '"'
      ], function ($line) {
        if (trim($line) != '') {
          $this->created = $line;
        }
      });

      // Если пользователь небыл создан
      if ($this->created !== true) {
        $user->delete();
        return response()->json([
          'error' => 'Неудалось создать пользователя (' . e($this->created) . ')'
        ], Response::HTTP_BAD_REQUEST);
      }

      // Устанавливаем пароль
      ExecProvider::run([ 'usermod --password $(echo "' . addslashes($user->password) . '" | openssl passwd -1 -stdin) "' . addslashes($user->user) . '"' ]);

      // Уведомление на почту
      if ($request->has('send')) {
        Mail::to($user->email)->send(new ProxyAccess($user));
      }

      // Возвращаем результат
      return response()->json(true);

    }

    /** Редактирование пользователя */
    public function postEdit (Request $request) {
      // Выбираем пользователя
      $user = User::where('id', $request->input('id', 0))->first();
      if (!$user) {
        return response()->json([
          'error' => 'Пользователь не найден'
        ], Response::HTTP_BAD_REQUEST);
      }

      // Валидация данных
      $request->validate([
        'email' => 'nullable|email',
        'comment' => 'max:255',
      ]);

      // Сохраняем
      $user->update($request->only([ 'email', 'comment' ]));

      // Возвращаем результат
      return response()->json(true);
    }

    /** Удаление пользователя */
    public function postRemove (Request $request, $id) {
      // Выбираем пользователя
      $user = User::where('id', $id)->first();
      if (!$user) {
        return response()->json([
          'error' => 'Пользователь не найден'
        ], Response::HTTP_BAD_REQUEST);
      }

      // Удаление с сервера
      $this->deleted = true;
      ExecProvider::run([
        'sudo userdel "' . addslashes($user->user) . '"'
      ], function ($line) {
        if (trim($line) != '') {
          $this->deleted = false;
        }
      });

      // Если не увдалось удалить
      if (!$this->deleted) {
        return response()->json([
          'error' => 'Неудалось удалить пользователя на сервере'
        ], Response::HTTP_BAD_REQUEST);
      }

      $user->delete();

      // Возвращаем результат
      return response()->json(true);
    }

    /** Отправить данные для доступа */
    public function postEmail (Request $request, $id)
    {
      // Выбираем пользователя
      $user = User::where('id', $id)->first();
      if (!$user) {
        return response()->json([
          'error' => 'Пользователь не найден'
        ], Response::HTTP_BAD_REQUEST);
      }

      // Если не указан Email
      if (is_null($user->email)) {
        return response()->json([
          'error' => 'У пользователя не задан Email'
        ], Response::HTTP_BAD_REQUEST);
      }

      // Шлем
      Mail::to($user->email)->send(new ProxyAccess($user));

      // Результат
      return response()->json(true);
    }

    /** Синхронизация :: Получаем расхождения */
    public function getSync (Request $request)
    {
      // Не найденные (Сервер)
      $this->server = [];
      // Не найденные (База)
      $this->database = [];
      // Список всех пользователей и их группы
      $this->users = [];
      ExecProvider::run([
        "cat /etc/passwd | awk -F':' '{ print $1}' | xargs -n1 groups"
      ], function ($line) {
        if (trim($line) != '') {
          $user = explode(' : ', $line);
          $this->users[$user[0]] = [
            'user' => $user[0],
            'group' => trim($user[1]),
            'id' => false
          ];
        }
      });

      // Список пользователей в базе данных
      $db_users = User::get();
      foreach ($db_users as $user) {
        // Проверяем есть ли такой пользователь
        if (isset($this->users[$user->user])) {
          // Если группа совпадает - удаляем из массива
          if ($this->users[$user->user]['group'] == env('PROXY_GROUP')) {
            unset($this->users[$user->user]);
          } else {
            // Различае в группе, задаем ID
            $this->users[$user->user]['id'] = $user->id;
          }
        } else {
          // Если не нашли запись
          $this->server[$user->user] = [
            'user' => $user->user,
            'group' => env('PROXY_GROUP'),
            'id' => $user->id
          ];
        }
      }

      // Удаляем не используемые записи из массива
      foreach ($this->users as $user) {
        if ($user['id'] == false && $user['group'] != env('PROXY_GROUP')) {
          unset($this->users[$user['user']]);
        } elseif ($user['id'] == false) {
          // Если записи нету в базе данных
          $this->database[$user['user']] = $user;
          unset($this->users[$user['user']]);
        }
      }

      // Список пользователей относящихся к нашей группе
      return response()->json([
        'group' => $this->users,
        'server' => $this->server,
        'database' => $this->database
      ]);

    }

    /** Применение синхронизации */
    public function postSync (Request $request, $action) {

      // Валидация данных
      $request->validate([ 'user' => 'required|max:20|alpha_num' ]);

      // Если можем - забираем из базы данных
      if ($action !== 'create_db') {
        $user = User::where('id', $request->input('user'))->first();
      }

      // Выполняем требуемое действие
      switch ($action) {
        // Смена группы
        case "group":
          ExecProvider::run([
            'usermod -g "' . addslashes(env('PROXY_GROUP')) . '" "' . addslashes($user->user) . '"',
            'usermod --password $(echo "' . addslashes($user->password) . '" | openssl passwd -1 -stdin) "' . addslashes($user->user) . '"'
          ]);
          break;
        // Удаление из базы данных
        case "delete_db":
          $user->delete();
          break;
        // Удалить на сервере
        case "delete_server":
          ExecProvider::run([ 'sudo userdel "' . addslashes(isset($user) ? $user->user : $request->input('user')) . '"' ]);
          break;
        // Создать на сервере и задаем пароль
        case "create_server":
          ExecProvider::run([
            'sudo useradd --shell /usr/sbin/nologin --gid "' . addslashes(env('PROXY_GROUP')) . '" "' . addslashes($user->user) . '"',
            'usermod --password $(echo "' . addslashes($user->password) . '" | openssl passwd -1 -stdin) "' . addslashes($user->user) . '"'
          ]);
          break;
        // Создание записи в базе данных
        case "create_db":
          $user = User::create([
            'user' => $request->input('user'),
            'comment' => 'Создано при синхронизации'
          ]);
          // Меняем пароль
          ExecProvider::run([ 'usermod --password $(echo "' . addslashes($user->password) . '" | openssl passwd -1 -stdin) "' . addslashes($user->user) . '"' ]);
          break;
      }

      // Результат
      return response()->json(true);
    }


}
