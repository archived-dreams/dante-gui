<body style="text-align: center; max-width: 350px; margin: 0 auto;">
  {{-- Логотип --}}
  <img src="{{ asset('logo.png') }}" width="80" height="80">

  {{-- Заголовок --}}
  <h4 style="font-size: 1.25rem; font-weight: 400; display: block; padding-top: 0.2rem; margin-top: 0;">
    {{ env('APP_NAME') }}
  </h4>

  {{-- Описание --}}
  <p>Высылаем вам доступ к Proxy серверу</p>

  {{-- Ссылка на телеграм --}}
  <div>
    <strong>Прямая ссылка для подключения в Telegram:</strong><br>
    <a href="{{ $link = 'https://t.me/socks?server=' . urlencode(env('PROXY_SERVER')) . '&port=' . urlencode(env('PROXY_PORT')) . '&user=' . urlencode($user->user) . '&pass=' . urlencode($user->password) }}">
      {{ $link }}
    </a>
  </div>

  {{-- Ссылка на полную страницу --}}
  <hr style="margin-top: 1rem; margin-bottom: 0.5rem; border: 0; border-top: 1px solid rgba(0, 0, 0, 0.1);">
  <p>
    Для получения более детальной информации или смены пароля, вы можете перейти по ссылке:<br>
    <a href="{{ $link = route('access', [ 'user' => $user->name, 'uuid' => $user->uuid ]) }}">
      {{ $link }}
    </a>
  </p>
</body>
