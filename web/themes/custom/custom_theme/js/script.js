(function ($) {
  $(document).ready(function () {
    $('.read-more').click(function (e) {
      e.preventDefault();
      $(this).hide();
      $(this).siblings('.show-less').show();
      $(this).siblings('.content').addClass('expanded');
    });

    $('.show-less').click(function (e) {
      e.preventDefault();
      $(this).hide();
      $(this).siblings('.read-more').show();
      $(this).siblings('.content').removeClass('expanded');
    });
  });
})(jQuery);

console.log('working');