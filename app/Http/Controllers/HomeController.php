<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class HomeController extends Controller
{

  public function __construct()
  {
      //$this->middleware('guest')->except('logout');
  }

  /** Авторизация */
  public function getHome (Request $request) {
    // Если вошли - идем на страницу системных настроек
    if ($request->cookie('password') == env('APP_PASSWORD')) {
      return redirect()->route('system');
    }
    // Форма авторизации
    return view('auth');
  }

  // Подтверждение авторизации
  public function postAuth (Request $request) {
    // Если пароль указан не верно
    if ($request->input('password', '') !== env('APP_PASSWORD')) {
      return response()->json([
        'error' => 'Пароль указан неверно'
      ], Response::HTTP_BAD_REQUEST);
    }
    // Авторизируемся
    return response()->json(true)->cookie(
      cookie('password', $request->input('password', ''), 240)
    );
  }

  // Выход из аккаунта
  public function getLogout (Request $request) {
    return redirect()->route('home')->cookie(cookie('password', '', 1));
  }

}
