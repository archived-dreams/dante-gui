@extends('layouts.blank')

@section('title', 'Аккаунт ' . $user->user)

@section('content')

  {{-- Заголовок --}}
  <h1 class="display-4 mt-2">Настройки Proxy</h1>
  <p class="lead">На данной странице предоставлены данные для доступа к Proxy аккаунту <u>{{ $user->user }}</u>. Также вы можете сгенерировать новый пароль.</p>

  {{-- Ссылка для Telegram --}}
  <div class="row mb-1">
    <div class="col-6 text-right">
      <strong>Ссылка для авто-настройки Telegram:</strong>
    </div>
    <div class="col-6 text-left">
      <a href="https://t.me/{{ $telegram }}">
        <i class="fas fa-link fa-fw"></i>
        <span class="d-none d-sm-inline d-md-none">Ссылка</span>
        <span class="d-none d-md-inline">https://t.me/{{ $telegram }}</span>
      </a>
    </div>
  </div>

  {{-- Сервер --}}
  <div class="row mb-1">
    <div class="col-6 text-right">
      <strong>Сервер:</strong>
    </div>
    <div class="col-6 text-left">
      <i class="fas fa-globe fa-fw d-none d-md-inline"></i>
      <span title="Сервер">{{ env('PROXY_SERVER') }}</span>:<span title="Порт">{{ env('PROXY_PORT') }}</span>
    </div>
  </div>

  {{-- Пользователь --}}
  <div class="row mb-1">
    <div class="col-6 text-right">
      <strong>Пользователь:</strong>
    </div>
    <div class="col-6 text-left">
      <i class="fas fa-user fa-fw d-none d-md-inline"></i>
      {{ $user->user }}
    </div>
  </div>

  {{-- Пароль --}}
  <div class="row mb-1">
    <div class="col-6 text-right">
      <strong>Пароль:</strong>
    </div>
    <div class="col-6 text-left">
      <i class="fas fa-key fa-fw d-none d-md-inline"></i>
      <span data-user="password">{{ $user->password }}</span>
    </div>
  </div>

  {{-- Внутренний идентификатор --}}
  <div class="row mb-1">
    <div class="col-6 text-right">
      <strong>Внутренний идентификатор:</strong>
    </div>
    <div class="col-6 text-left">
      <i class="fas fa-id-card-alt fa-fw d-none d-md-inline"></i>
      {{ $user->uuid }}
    </div>
  </div>

  {{-- Сгенерировать новый пароль --}}
  <div class="mt-3 mb-5">
    <button type="button" class="btn btn-outline-primary" data-button="reset-password">
      Сгенерировать новый пароль
    </button>
  </div>

@endsection

@section('scripts')
  @parent
  <script>
    // Авто подключение Telegram
    var protoUrl = "tg:\/\/{{ $telegram }}";
    if (true) {
      var iframeContEl = document.body;
      var iframeEl = document.createElement('iframe');
      iframeContEl.appendChild(iframeEl);
      var pageHidden = false;
      window.addEventListener('pagehide', function () {
        pageHidden = true;
      }, false);
      window.addEventListener('blur', function () {
        pageHidden = true;
      }, false);
      if (iframeEl !== null) {
        iframeEl.src = protoUrl;
      }
      !true && setTimeout(function() {
        if (!pageHidden) {
          window.location = protoUrl;
        }
      }, 2000);
    }
    else if (protoUrl) {
      setTimeout(function() {
        window.location = protoUrl;
      }, 100);
    }

    // Генерация нового пароля
    $(() => {
      $('[data-button="reset-password"]').on('click', () => {
        var button = $(this).attr('disabled', true);
        // Включаем кнопку
        setTimeout(() => {
          button.removeAttr('disabled');
        }, 9000);
        // Меняем пароль
        $.ajax({
          type: 'POST',
          url: "{{ route('access.password', [ 'user' => $user->user, 'uuid' => $user->uuid ]) }}",
          dataType: 'json',
          success: (password) => {
            if (password !== false) {
              $('[data-user="password"]').text(password);
              $.notify({ message: "Новый пароль успешно сгенерирован!" }, { type: 'success' });
            } else {
              $.notify({ message: "При смене пароля возникла ошибка" }, { type: 'danger' });
            }
          },
          error: () => {
            button.removeAttr('disabled');
            $.notify({ message: "При смене пароля возникла ошибка" }, { type: 'danger' });
          }
        });

      });
    });
  </script>
@endsection


@section('styles')
  @parent
  <style>
    iframe { display: none !important; }
  </style>
@endsection
