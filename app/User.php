<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'uuid', 'user', 'password', 'email', 'comment', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Триггеры
     */
    public static function boot() {
      parent::boot();
      // При создании генерируем UUID
      self::creating(function ($model) {
        $model->uuid = (string) Str::uuid();
        if ($model->password !== false) {
          $model->password = str_random(23);
        }
      });
      // При генерации изменении генерируем пароль
      self::updating(function ($model) {
        if ($model->password == false) {
          $model->password = str_random(23);
        }
      });
    }

}
