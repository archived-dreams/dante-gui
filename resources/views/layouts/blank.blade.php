<!doctype html>
<html lang="{{ config('app.locale') }}">
  {{-- Шапка --}}
  @include('layouts.elements.head')
  <body>
    <div class="container text-center">
      {{-- Контент --}}
      @yield('content')
      {{-- Ноги --}}
      @include('layouts.elements.footer')
    </div>
    {{-- Скрипты --}}
    @include('layouts.elements.scripts')
  </body>
</html>
