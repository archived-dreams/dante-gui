@extends('layouts.app')

@section('title', 'Система')

@section('content')
  {{-- Информация --}}
  <div class="text-center">
    <h1 class="display-4">Система</h1>
    <p class="lead">
      На этой странице размещена информация о настройках скрипта, сервера и Dante.<br>
      Все настройки осуществляются в <u>.env</u> файле
    </p>
  </div>

  {{-- Кнопки --}}
  <div class="mb-2">
    {{-- Обновить данные --}}
    <button type="button" class="btn btn-primary mb-2" data-button="reload-data">Обновить данные</button>
    {{-- Перезапустить Dante --}}
    <button type="button" class="btn btn-outline-info mb-2" data-button="restart-dante">Перезапустить Dante</button>
    {{-- Перезагрузить сервер --}}
    <button type="button" class="btn btn-outline-danger float-md-right mb-2" data-button="reboot-server">Перезагрузить сервер</button>
  </div>

  {{-- Пункты --}}
  <div class="card-columns mb-3 text-center">

    {{-- Настройки --}}
    <div class="card mb-4 box-shadow">
      <div class="card-header">
        <h4 class="my-0 font-weight-normal">Настройки</h4>
      </div>
      <div class="card-body text-left">
        {{-- Сервер --}}
        <div class="row">
          <strong class="col-6" title="PROXY_SERVER">Сервер:</strong>
          <span class="col-6" title="Домен или IP адрес Dante сервера" data-env="PROXY_SERVER"></span>
        </div>
        {{-- Порт --}}
        <div class="row">
          <strong class="col-6" title="PROXY_PORT">Порт:</strong>
          <span class="col-6" title="Порт Dante сервера" data-env="PROXY_PORT"></span>
        </div>
        {{-- Группа --}}
        <div class="row">
          <strong class="col-6" title="PROXY_GROUP">Группа:</strong>
          <span class="col-6" title="Группа пользователей" data-env="PROXY_GROUP"></span>
        </div>
        {{-- Пользователь --}}
        <div class="row">
          <strong class="col-6" title="PROXY_USER">Пользователь:</strong>
          <span class="col-6" title="Пользователь создающий аккаунты на сервере" data-env="PROXY_USER"></span>
        </div>
        {{-- Пароль --}}
        <div class="row">
          <strong class="col-6" title="PROXY_PASSWORD">Пароль:</strong>
          <span class="col-6" title="Пароль от пользователя" data-env="PROXY_PASSWORD"></span>
        </div>
      </div>
    </div>

    {{-- Подключение к серверу --}}
    <div class="card mb-4 box-shadow">
      <div class="card-header">
        <h4 class="my-0 font-weight-normal">Подключение к серверу</h4>
      </div>
      <div class="card-body">
        Статус подключения к серверу <u>{{ env('PROXY_SERVER') }}</u>:
        <div data-element="server" class="mt-2"></div>
      </div>
    </div>

    {{-- Группа --}}
    <div class="card mb-4 box-shadow">
      <div class="card-header">
        <h4 class="my-0 font-weight-normal">Группа пользователей</h4>
      </div>
      <div class="card-body">
        Статус группы <u>{{ env('PROXY_GROUP') }}</u> на сервере:
        <div data-element="group" class="mt-2"></div>
      </div>
    </div>

    {{-- Пароль доступа к панели --}}
    <div class="card mb-4 box-shadow">
      <div class="card-header">
        <h4 class="my-0 font-weight-normal">Пароль доступа к панели</h4>
      </div>
      <div class="card-body">
        <h4 data-env="APP_PASSWORD"></h4>
      </div>
    </div>

  </div>

@endsection


@section('scripts')
  @parent
  <script>
    window.elements = {};

    // Настройки
    window.elements.env = {
      render: () => {
        $(`[data-env]`).html(window.loading.replace('fa-3x', ''));
        $.getJSON("{{ route('system.env') }}", ( env ) => {
          $.each(env, ( name, value ) => {
            $(`[data-env="${ name }"]`).text(value);
          })
        });
      }
    };

    // Подключение к серверу
    window.elements.server = {
      element: $('[data-element="server"]').first(),
      render: () => {
        window.elements.server.element.html(window.loading);
        $.getJSON("{{ route('system.server') }}", ( uptime ) => {
          if (uptime) {
            window.elements.server.element.html(`
              <span class="text-success">Соединение с сервером установлено.</span> <strong>Uptime:</strong>
              <pre>${ uptime }</pre>
            `);
          } else {
            window.elements.server.element.html(`
              <div class="alert alert-danger mb-0" role="alert">
                Неудалось установить соединение с сервером!
              </div>
            `);
          }
        });
      }
    };

    // Группа пользователей
    window.elements.group = {
      element: $('[data-element="group"]').first(),
      // Иницилизация
      init: () => {
        window.elements.group.element.on('click', '[data-button="create"]', window.elements.group._create);
      },
      // Получить данные о группе пользователей
      render: () => {
        window.elements.group.element.html(window.loading);
        $.getJSON("{{ route('system.group') }}", ( status ) => {
          if (status) {
            window.elements.group.element.html(`
              <div class="alert alert-success" role="alert">
                Группа пользователей на сервере обнаружена
              </div>
            `);
          } else {
            window.elements.group.element.html(`
              <div class="alert alert-danger mb-0" role="alert">
                Группа не обнаружена на сервере
                <button type="button" class="btn btn-outline-danger btn-block" data-button="create">
                  Создать
                </button>
              </div>
            `);
          }
        });
      },
      // Создание группы
      _create: () => {
        window.elements.group.element.html(window.loading);
        $.ajax({
          type: 'POST',
          url: "{{ route('system.group.create') }}",
          dataType: 'json',
          success: (status) => {
            if (status) {
              window.elements.group.element.html(`
                <div class="alert alert-success" role="alert">
                  Группа пользователей успешно создана
                </div>
              `);
            } else {
              window.elements.group.element.html(`
                <div class="alert alert-danger mb-0" role="alert">
                  Возникла ошибка при создании группы
                </div>
              `);
            }
          }
        });
      }
    };

    // Перезапуск Dante
    window.elements.restart_dante = {
      element: $('[data-button="restart-dante"]'),
      init: () => {
        window.elements.restart_dante.element.on('click', window.elements.restart_dante._action);
      },
      _action: () => {
        $.ajax({
          type: 'POST',
          url: "{{ route('system.restart') }}",
          success: () => {
            $.notify({ message: "Команда для перезапуска Dante отправлена!" }, { type: 'success' });
          }
        });
      }
    };

    // Перезапуск Dante
    window.elements.reboot_server = {
      element: $('[data-button="reboot-server"]'),
      init: () => {
        window.elements.reboot_server.element.on('click', window.elements.reboot_server._action);
      },
      _action: () => {
        $.ajax({
          type: 'POST',
          url: "{{ route('system.reboot') }}",
          success: () => {
            $.notify({ message: "Команда для перезапуска Dante отправлена!" }, { type: 'success' });
          }
        });
      }
    };


    $(() => {
      // Иницилизация
      $.each(window.elements, ( module ) => {
        if (typeof window.elements[module].init == 'function') {
          window.elements[module].init();
        }
      });
      // Обновление данных
      $('[data-button="reload-data"]').on('click', function () {
        $.each(window.elements, ( module ) => {
          if (typeof window.elements[module].render == 'function') {
            window.elements[module].render();
          }
        });
      }).trigger('click');
    });
  </script>
@endsection
