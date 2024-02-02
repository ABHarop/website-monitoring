$(document).ready(function () {
  // alert("page loaded successfully");
  // For loading a page before anything
  window.start_load = function () {
    $("body").prepend('<di id="preloader2"></di>');
  };
  window.end_load = function () {
    $("#preloader2").fadeOut("fast", function () {
      $(this).remove();
    });
  };

  $("#preloader").fadeOut("fast", function () {
    $(this).remove();
  });

  // Refresh tables
  setInterval(function() {
    $('.refreshList').load(location.href + ' .refreshList');
  }, 3000);


});
