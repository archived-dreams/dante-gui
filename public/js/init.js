$(() => {
  /** CSRF */
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  /** Tooltips */
  $('[title]').tooltip();

  /** Обработчик ajax статусов */
  $(document).ajaxError(( event, jqxhr ) => {
    if (typeof jqxhr.responseJSON == 'object') {
      // Ошибки валидации
      if (typeof jqxhr.responseJSON.errors == 'object') {
        $.each(jqxhr.responseJSON.errors, function( name, errors ) {
          if (typeof errors == 'object') {
            $.each(errors, function( key, value ) {
               $.notify({ message: value }, { type: 'danger' });
            });
          }
        });
      }
      // Ручные ошибки
      if (typeof jqxhr.responseJSON.error == 'string') {
         $.notify({ message: jqxhr.responseJSON.error }, { type: 'danger' });
      }
    }
  });

  // Загрузка
  window.loading = '<i class="fas fa-sync fa-spin fa-3x"></i>';
});
