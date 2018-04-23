<?php
Route::pattern('id', '\d+');
Route::pattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
Route::pattern('user', '[A-Za-z0-9]+');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/** Главная страница */
Route::get('/', ['uses' => 'HomeController@getHome', 'as' => 'home']);
Route::post('/auth', ['uses' => 'HomeController@postAuth', 'as' => 'auth']);
Route::get('/logout', ['uses' => 'HomeController@getLogout', 'middleware' => 'admin.auth', 'as' => 'auth.logout']);

Route::group(['middleware' => [ 'admin.auth' ]], function () {
  /** Пользователи */
  Route::get('/users', ['uses' => 'UsersController@getHome', 'as' => 'users']);
  Route::post('/users/create', ['uses' => 'UsersController@postCreate', 'as' => 'users.create']);
  Route::post('/users/edit', ['uses' => 'UsersController@postEdit', 'as' => 'users.edit']);
  Route::post('/users/{id}/remove', ['uses' => 'UsersController@postRemove', 'as' => 'users.remove']);
  Route::post('/users/{id}/email', ['uses' => 'UsersController@postEmail', 'as' => 'users.email']);
  Route::get('/users/sync', ['uses' => 'UsersController@getSync', 'as' => 'users.sync']);
  Route::post('/users/sync/{action}', ['uses' => 'UsersController@postSync', 'as' => 'users.sync.apply'])->where([ 'action' => 'group|delete_db|delete_server|create_server|create_db' ]);


  /** Система */
  Route::get('/system', ['uses' => 'SystemController@getHome', 'as' => 'system']);
  Route::get('/system/env', ['uses' => 'SystemController@getEnv', 'as' => 'system.env']);
  Route::get('/system/group', ['uses' => 'SystemController@getGroup', 'as' => 'system.group']);
  Route::post('/system/group/create', ['uses' => 'SystemController@postGroup', 'as' => 'system.group.create']);
  Route::get('/system/server', ['uses' => 'SystemController@getServer', 'as' => 'system.server']);
  Route::post('/system/restart', ['uses' => 'SystemController@postRestart', 'as' => 'system.restart']);
  Route::post('/system/reboot', ['uses' => 'SystemController@postReboot', 'as' => 'system.reboot']);
});

/** Доступы */
Route::get('/access/{user}/{uuid}', ['uses' => 'AccessController@getHome', 'as' => 'access']);
Route::post('/access/{user}/{uuid}/password', ['uses' => 'AccessController@postPassword', 'as' => 'access.password']);
