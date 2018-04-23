@extends('layouts.blank')

@section('title', 'Вход')

@section('content')
  <form class="form-signin" id="authForm" action="{{ route('auth') }}" method="POST">
    {{-- Логотип --}}
    <img class="mb-4" src="{{ asset('logo.png') }}" alt="" width="120" height="120">
    {{-- Заголовок --}}
    <h1 class="h3 mb-3 font-weight-normal">Пожалуйста войдите</h1>
    {{-- Плейсхолдер --}}
    <label for="inputPassword" class="sr-only">Пароль</label>
    {{-- Пароль --}}
    <input id="inputPassword" name="password" class="form-control" placeholder="Пароль от админ панели" required="" autofocus="" type="password">
    {{-- Отправить форму --}}
    <button class="btn btn-lg btn-primary btn-block mt-4" type="submit">Войти</button>
    {{-- Копирайт --}}
    <div class="border-top mt-4 pt-4">
      <small class="d-block mb-3 text-muted">&copy; {{ date('Y') }} {{ env('APP_NAME') }}</small>
    </div>
  </form>
@endsection

@section('scripts')
  @parent
  <script>
    // Авторизация
    $(() => {
      $('#authForm').ajaxForm({
        dataType: 'json',
        beforeSubmit: () => {
          $(this).find('[type="submit"]').attr('disabled', true);
        },
        success: (status) => {
          if (status === true) {
            $.notify({ message: "Вы успешно вошли!" }, { type: 'success' });
            location.reload();
          } else {
            $(this).find('[type="submit"]').removeAttr('disabled');
          }
        },
        error: () => {
          $(this).find('[type="submit"]').removeAttr('disabled');
        }
      });
    });
  </script>
@endsection


@section('styles')
  @parent
  <style>
    footer {
      display: none;
    }
    .container {
      display: -ms-flexbox;
      display: -webkit-box;
      display: flex;
      -ms-flex-align: center;
      -ms-flex-pack: center;
      -webkit-box-align: center;
      align-items: center;
      -webkit-box-pack: center;
      justify-content: center;
      min-height: 100vh;
    }
  </style>
@endsection
