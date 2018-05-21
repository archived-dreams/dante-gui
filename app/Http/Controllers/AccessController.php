<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\ExecProvider;

class AccessController extends Controller
{

  public function __construct()
  {
      //$this->middleware('guest')->except('logout');
  }

  /** Системные данные */
  public function getHome (Request $request, $user, $uuid) {
    // Выбираем пользователя, если пользователья нет - выводим ошибку
    $user = User::where('user', $user)->where('uuid', $uuid)->first();
    if (!$user) abort(403);

    return view('access', [
      'user' => $user,
      'telegram' => 'socks?server=' . urlencode(env('PROXY_SERVER')) . '&port=' . urlencode(env('PROXY_PORT')) . '&user=' . urlencode($user->user) . '&pass=' . urlencode($user->password)
    ]);
  }

  /** Смена пароля */
  public function postPassword (Request $request, $user, $uuid) {
    // Выбираем пользователя, если пользователья нет - выводим ошибку
    $user = User::where('user', $user)->where('uuid', $uuid)->first();
    if (!$user) abort(403);

    // Генерируем новый пароль и сохраняем
    $user->password = false;
    $user->update();

    // Устанавливаем пароль
    ExecProvider::run([ 'usermod --password $(echo "' . addslashes($user->password) . '" | openssl passwd -1 -stdin) "' . addslashes($user->user) . '"' ]);

    return response()->json($user->password);

  }

}
