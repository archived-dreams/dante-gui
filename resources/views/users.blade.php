@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
  {{-- Информация --}}
  <div class="text-center">
    <h1 class="display-4">Пользователи</h1>
    <p class="lead">
      Список пользователей хранится в базе данных, синхронизация удаляет только несуществующих пользователей из базы данных.
    </p>
  </div>

  {{-- Кнопки --}}
  <div class="mb-2">
    {{-- Создать пользователя --}}
    <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#createModal">Создать пользователя</button>
    {{-- Синхронизировать --}}
    <button type="button" class="btn btn-outline-info mb-2 float-md-right" data-trigger="sync">Синхронизировать</button>
  </div>

  {{-- Нету пользователей --}}
  @if($users->total() == 0)
    <div class="alert alert-info" role="alert">
      В базе данных нету пользователей
    </div>
  @endif

  {{-- Таблица пользователей --}}
  @if($users->total() > 0)
    <div class="table-responsive">
      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Пользователь</th>
            <th scope="col">Пароль</th>
            <th scope="col">Почта</th>
            <th scope="col">Комментарий</th>
            <th scope="col">Действие</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
            <tr>
              <th scope="row">{{ $user->id }}</th>
              <td>{{ $user->user }}</td>
              <td>{{ $user->password }}</td>
              <td>{{ $user->email ? $user->email : '-' }}</td>
              <td>{{ $user->comment ? $user->comment : '-' }}</td>
              <td>
                {{-- Ссылка на страницу доступа --}}
                <a href="{{ route('access', [ 'user' => $user->user, 'uuid' => $user->uuid ]) }}">
                  <i class="fas fa-link" title="Страница с доступом и информацией о подключении Proxy"></i>
                </a>
                {{-- Отправить ссылку на страницу доступа --}}
                <a href="#" data-trigger="send-email" data-id="{{ $user->id }}">
                  <i class="fas fa-at" title="Отправить по почте ссылку на страницу доступа"></i>
                </a>
                {{-- Удалить --}}
                <a href="#" data-trigger="delete" data-id="{{ $user->id }}" data-user="{{ $user->user }}">
                  <i class="far fa-trash-alt" title="Удалить"></i>
                </a>
                {{-- Редактировать --}}
                <a href="#" data-trigger="edit" data-user="{{ json_encode($user) }}">
                  <i class="far fa-edit" title="Редактировать"></i>
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  {{-- Пагинация --}}
  {{ $users->links() }}

@endsection


@section('scripts')
  @parent
  <script>
    $(() => {
      // Создание/редактирование пользователя
      $('#createModal form, #editModal form').ajaxForm({
        dataType: 'json',
        beforeSubmit: () => {
          $(this).find('[type="submit"]').attr('disabled', true);
        },
        success: (status) => {
          if (status === true) {
            $.notify({ message: "Пользователь успешно сохранен!" }, { type: 'success' });
            location.reload();
          } else {
            $(this).find('[type="submit"]').removeAttr('disabled');
          }
        },
        error: () => {
          $(this).find('[type="submit"]').removeAttr('disabled');
        }
      });
      // Удаление пользователя
      $('[data-trigger="delete"]').on('click', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var user = $(this).attr('data-user');
        // Подтверждение
        if (!confirm(`Вы уверены что хотите удалить пользователя "${ user }" (ID: ${ id })?`)) {
          return;
        }
        // Удаляем
        $.ajax({
          type: 'POST',
          url: "{{ route('users.remove', [ 'id' => '0000' ]) }}".replace('0000', id),
          dataType: 'json',
          success: (status) => {
            if (status) {
              $.notify({ message: "Пользователь успешно удален" }, { type: 'success' });
              location.reload();
            } else {
              $.notify({ message: "Неудалось удалить пользователя" }, { type: 'danger' });
            }
          }
        });
      });
      // Отправляем данные по Email
      $('[data-trigger="send-email"]').on('click', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        // Отправляем
        $.ajax({
          type: 'POST',
          url: "{{ route('users.email', [ 'id' => '0000' ]) }}".replace('0000', id),
          dataType: 'json',
          success: (status) => {
            if (status) {
              $.notify({ message: "Данные для доступа к Proxy высланы" }, { type: 'success' });
            }
          }
        });
      });
      // Редактирование пользователя
      $('[data-trigger="edit"]').on('click', function (e) {
        e.preventDefault();
        var user = jQuery.parseJSON($(this).attr('data-user'));
        var model = $('#editModal');
        // Подставляем значения
        $.each(user, (key, value) => {
          model.find(`[data-value="${ key }"]`).val(value);
          model.modal('show');
        })

      });
      // Синхронизация аккаунтов
      $('[data-trigger="sync"]').on('click', () => {
        var modal = $('#syncModal');
        var block = modal.find('[data-block="body"]').html(window.loading);
        var _table = (table) => {
          block.append(`
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Пользователь</th>
                    <th scope="col">Группа</th>
                    <th scope="col">Действие</th>
                  </tr>
                </thead>
                <tbody>
                  ${table}
                </tbody>
              </table>
            </div>
          `);
        };
        modal.modal('show');

        // Получаем список проблемных аккаунтов
        $.getJSON("{{ route('users.sync') }}", ( users ) => {

          // Ошибка с группой
          block.html('<h5 class="font-weight-light">Неверная группа</h5>');
          if (users.group.length == 0) {
            block.append(`
              <div class="alert alert-success" role="alert">
                Проблем с группами пользовелей не обнаружено
              </div>
            `);
          } else {
            var table = '';
            $.each(users.group, (name, user) => {
              table += `
                <tr>
                  <td scope="row">${ user.id }</td>
                  <td>${ user.user }</td>
                  <td class="text-danger">${ user.group }</td>
                  <td>
                    <button type="button" data-action="sync" data-type="group" data-user="${ user.id }" class="btn btn-sm btn-primary" title="Будет заменена группа на {{ env('PROXY_GROUP') }}">Заменить</button>
                    <button type="button" data-action="sync" data-type="delete_db" data-user="${ user.user }" class="btn btn-sm btn-danger" title="Будет удален аккаунт на сервере">Удалить</button>
                  </td>
                </tr>
              `;
            });
            _table(table);
          }

          // Ненайденные пользователи (Сервер)
          block.append('<h5 class="font-weight-light">Ненайденные пользователи (Сервер)</h5>');
          if (users.server.length == 0) {
            block.append(`
              <div class="alert alert-success" role="alert">
                Проблем с отсутствующими пользовелями на Сервере не обнаружено
              </div>
            `);
          } else {
            var table = '';
            $.each(users.server, (name, user) => {
              table += `
                <tr>
                  <td scope="row">${ user.id }</td>
                  <td class="text-danger">${ user.user }</td>
                  <td></td>
                  <td>
                    <button type="button" data-action="sync" data-type="create_server" data-user="${ user.id }" class="btn btn-sm btn-primary" title="Будет создана запись на сервере">Создать</button>
                    <button type="button" data-action="sync" data-type="delete_db" data-user="${ user.id }" class="btn btn-sm btn-danger" title="Будет удалена запись из базы данных">Удалить</button>
                  </td>
                </tr>
              `;
            });
            _table(table);
          }

          // Ненайденные пользователи (База данных)
          block.append('<h5 class="font-weight-light">Ненайденные пользователи (База данных)</h5>');
          if (users.database.length == 0) {
            block.append(`
              <div class="alert alert-success" role="alert">
                Проблем с отсутствующими пользовелями в Базе данных не обнаружено
              </div>
            `);
          } else {
            var table = '';
            $.each(users.database, (name, user) => {
              table += `
                <tr>
                  <td scope="row" class="text-danger">--</td>
                  <td>${ user.user }</td>
                  <td>${ user.group }</td>
                  <td>
                    <button type="button" data-action="sync" data-type="create_db" data-user="${ user.user }" class="btn btn-sm btn-primary" title="Будет создана запись в базе данных и изменен пароль">Создать</button>
                    <button type="button" data-action="sync" data-type="delete_server" data-user="${ user.user }" class="btn btn-sm btn-danger" title="Будет удален аккаунт на сервере">Удалить</button>
                  </td>
                </tr>
              `;
            });
            _table(table);
          }

          modal.find('[title]').tooltip({ placement: 'left' });
        });
      });
      // Синхронизация, действия
      $('#syncModal').on('click', '[data-action="sync"]', function () {
        var modal = $('#syncModal');
        var action = $(this).attr('data-type');
        var user = $(this).attr('data-user');
        var block = modal.find('[data-block="body"]').html(window.loading);
        // Производим действие
        $.ajax({
          type: 'POST',
          url: "{{ route('users.sync.apply', [ 'action' => '0000' ]) }}".replace('0000', action),
          data: { user },
          dataType: 'json',
          success: (status) => {
            $.notify({ message: "Действие выполнено" }, { type: 'success' });
            $(".tooltip").tooltip("hide");
            $('[data-trigger="sync"]').trigger('click');
          }
        });
      });
      // Синхронизация, закрытие модального окна
      $('#syncModal').on('hidden.bs.modal', function () {
        location.reload();
      })
    });
  </script>
@endsection


@section('modals')
  @parent
  {{-- Создание пользователя --}}
  <div class="modal fade" id="createModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <form class="modal-content" action="{{ route('users.create') }}" method="POST" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Создание пользователя</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          {{-- Пользователь --}}
          <div class="form-group">
            <label for="userInput">Пользователь</label>
            <input type="text" class="form-control" name="user" id="userInput" aria-describedby="userHelp" placeholder="Staff14">
            <small class="form-text text-muted" id="userHelp">Может содержать только буквы латинского алфавита и цифры.</small>
          </div>
          {{-- Почта --}}
          <div class="form-group">
            <label for="emailInput">Почта</label>
            <input type="text" class="form-control" name="email" id="emailInput" aria-describedby="emailHelp" placeholder="staff14{{ '@' . Request::server('SERVER_NAME') }}">
            <small class="form-text text-muted" id="emailHelp">На эту почту в последствии можно выслать данные для доступа</small>
          </div>
          {{-- Комментарий --}}
          <div class="form-group">
            <label for="commentInput">Комментарий</label>
            <input type="text" class="form-control" name="comment" id="commentInput" aria-describedby="commentHelp" placeholder="Иван, Программист">
            <small class="form-text text-muted" id="commentHelp">Служит для дополнительной идентификации пользователя</small>
          </div>
          {{-- Отправить данные на почту --}}
          <div class="form-check">
            <input type="checkbox" name="send" class="form-check-input" id="sendCheck">
            <label class="form-check-label" for="sendCheck">Отправить данные на почту</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
          <button type="submit" class="btn btn-primary">Создать</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Редактирование пользователя --}}
  <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <form class="modal-content" action="{{ route('users.edit') }}" method="POST" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title">Редактирование пользователя</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          {{-- ID --}}
          <div class="form-group">
            <label>ID</label>
            <input type="text" class="form-control" name="id" data-value="id" readonly>
          </div>
          {{-- Пользователь --}}
          <div class="form-group">
            <label>Пользователь</label>
            <input type="text" class="form-control" data-value="user" readonly>
          </div>
          {{-- Внутренний идентификатор --}}
          <div class="form-group">
            <label>Внутренний идентификатор</label>
            <input type="text" class="form-control" data-value="uuid" readonly>
          </div>
          {{-- Почта --}}
          <div class="form-group">
            <label for="editEmailInput">Почта</label>
            <input type="text" class="form-control" name="email" data-value="email" id="editEmailInput" aria-describedby="editEmailHelp" placeholder="staff14{{ '@' . Request::server('SERVER_NAME') }}">
            <small class="form-text text-muted" id="editEmailHelp">На эту почту можно выслать данные для доступа</small>
          </div>
          {{-- Комментарий --}}
          <div class="form-group">
            <label for="editCommentInput">Комментарий</label>
            <input type="text" class="form-control" name="comment" data-value="comment" id="editCommentInput" aria-describedby="editCommentHelp" placeholder="Иван, Программист">
            <small class="form-text text-muted" id="editCommentHelp">Служит для дополнительной идентификации пользователя</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
          <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Синхронизация аккаунтов --}}
  <div class="modal fade" id="syncModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Синхронизация аккаунтов</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center" data-block="body"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        </div>
      </div>
    </div>
  </div>
@endsection
