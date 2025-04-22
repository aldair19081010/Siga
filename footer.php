<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/DataTables/datatables.min.js"></script>
<script src="assets/js/select2.min.js"></script>
<script src="assets/js/jquery.datetimepicker.full.min.js"></script>
<script src="assets/font-awesome/js/all.min.js"></script>
<script>
  function start_load() {
    $('body').prepend('<div id="preloader2"></div>');
  }
  function end_load() {
    $('#preloader2').fadeOut('fast', function() {
      $(this).remove();
    });
  }
  function alert_toast(msg, bg) {
    $('#alert_toast').removeClass('bg-success bg-danger bg-info bg-warning').addClass('bg-' + bg);
    $('#alert_toast .toast-body').html(msg);
    $('#alert_toast').toast({ delay: 3000 }).toast('show');
  }
</script>
<script>
  function onReady(callback) {
    var intervalID = window.setInterval(checkReady, 1000);

    function checkReady() {
      if (document.getElementsByTagName('body')[0] !== undefined) {
        window.clearInterval(intervalID);
        callback.call(this);
      }
    }
  }

  function show(id, value) {
    document.getElementById(id).style.display = value ? 'block' : 'none';
  }

  onReady(function() {
    show('page', true);
    show('loading', false);
  });
</script>
<script>
    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        console.error("Error en la solicitud AJAX:", jqxhr);
        alert_toast("Error en la solicitud. Verifique la consola para más detalles.", 'danger');
    });
</script>
<footer class="bg-primary text-white text-center text-lg-start fixed-bottom">
  <!-- Grid container -->

  <!-- Grid container -->

  <!-- Copyright -->
  <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2)">
    Para más informacion: 
    <a class="text-white" href="https://www.facebook.com/aldairalberto.cherovelasquez/">"@AldairChero"</a>
  </div>
  <!-- Copyright -->
</footer>
<!--/ Copy this code to have a working example -->
<?php
?>