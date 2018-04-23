<!doctype html>
<html lang="{{ config('app.locale') }}">
  {{-- Шапка --}}
  @include('layouts.elements.head')
  <body>
    {{-- Меню --}}
    @include('layouts.elements.sidebar')
    <div class="container">
      {{-- Контент --}}
      @yield('content')
      {{-- Ноги --}}
      @include('layouts.elements.footer')
    </div>
    {{-- Модальные окна --}}
    @yield('modals')
    {{-- Скрипты --}}
    @include('layouts.elements.scripts')
  </body>
</html>
