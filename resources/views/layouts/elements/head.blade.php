<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Управление Dante Server пользователями">
  <meta name="author" content="Ivan Danilov (https://github.com/IvanDanilov)">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <title>@yield('title') - {{ env('APP_NAME') }}</title>
  <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
  <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
  <link href="{{ asset('css/fontawesome/all.css') }}" rel="stylesheet">
  {{-- Кастомные стили --}}
  @yield('styles')
</head>
